<?php

include '/var/www/html/actions/database/functions.php';
/* include '/var/www/html/functions.php';
include '/var/www/html/actions/mail/send_mail.php'; */
// Настройки
$apiBaseUrl = 'https://enter.tochka.com/uapi/';
$sbpApiBaseUrl = 'https://enter.tochka.com/uapi/sbp/v1.0/';
$authToken    = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJ6ekhySFVta1lUU3ZuMERVdGdnVnNMRHF0TnBYaGVrUiJ9.jUpIG6PtuYA7jG59dzw-9r6mwPWvJGyTI-rDI_Sx4EIhakXYt48wy4UHavP9UR5Yy_U3LJDVFN0w7Bimrd3B8aRAvI4yDt1ZneMwfkOiYLato9ZLkKrZqHQjgty8kKEuCKf1-gEKcSYFor9sdFP4XBjRIBenxDKYjjI9Lb6UMFXUr_ET9QsIySvE8Boo6MSIneucWuXRIQnQRcLDwbde7UmfmQpK7HzG4dlNU1Y-6TSxqdbCnM8YQ90Hh-2R0stR4oNGbWxlhvaIIEPDsdmQXyHWJvzYzzktRn5uI51rOHs02j9uIXEcnHyn3Ctp7Du8FagdppCz-KJmflkxXejNON7nHUJaTOpbRjRQAZdw_YV2hZwxZd4NxiuNr4dizH6rbVkevs748SkXZzCPvMvKUnUZPciylsvByjTRJLyets01Ix6PRM695J5bchWCmlzi6uGamIm-sGSFtNjq6JvdNkKgGQbx5f34oeY5Owpb7MhWVh1gfNxRH9UIDCqN-UM_';
$qrcId = 'BD20002ST53T34EC9JCQ67G753GLCJF1';

function apiGetRequest(string $url, string $authToken): array {
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "Authorization: Bearer $authToken\r\nAccept: application/json\r\n",
            "ignore_errors" => true
        ]
    ];
    $context = stream_context_create($opts);
    $respJson = @file_get_contents($url, false, $context);
    if ($respJson === false) {
        return ['error' => "GET вернул false для URL $url"];
    }
    $data = json_decode($respJson, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => "Невалидный JSON: $respJson"];
    }
    return $data;
}

function processPaymentStatus($qrcId, $data_base) {
    global $sbpApiBaseUrl, $authToken;

    // Формируем URL для получения статуса платежа
    $paymentStatusUrl = sprintf(
        "%sqr-codes/%s/payment-status",
        rtrim($sbpApiBaseUrl, '/').'/',
        urlencode($qrcId)
    );

    // Делаем запрос
    $response = apiGetRequest($paymentStatusUrl, $authToken);

    // Логируем для отладки
    /* file_put_contents('/var/www/html/actions/sbp_error_log.txt', print_r([
        'qrcId' => $qrcId,
        'request_url' => $paymentStatusUrl,
        'response' => $response
    ], true), FILE_APPEND); */

    // Проверяем статус платежа
    if (!isset($response['error']) && isset($response['Data']['paymentList'][0]['status'])) {
        $status = $response['Data']['paymentList'][0]['status'];
        
        // Если статус не NotStarted, обновляем moderation
        if ($status !== 'NotStarted') {
            try {
                $result = $data_base->selectRow(
                    "SELECT orderId, tableType FROM dt_payments WHERE qrcId = ?",
                    [$qrcId]
                );
                
                if (!$result) {
                    file_put_contents('/var/www/html/actions/sbp_error_log.txt', "qrcId $qrcId не найден в dt_payments\n", FILE_APPEND);
                } else {
                    $orderId = $result['orderId'];
                    $tableType = $result['tableType'];

                    // Определяем таблицу
                    $tableMap = [
                        'tariff' => 'dt_tariff_list',
                        'promo' => 'dt_promo_list',
                        'option' => 'dt_options_list'
                    ];
                    $table = $tableMap[$tableType] ?? null;

                    if ($table) {
                        // Обновляем moderation
                        $data_base->update(
                            "UPDATE $table SET moderation = 0 WHERE orderId = ?",
                            [$orderId]
                        );
                        
                        // Обновляем статус в dt_payments
                        $data_base->update(
                            "UPDATE dt_payments SET status = ?, updatedAt = NOW() WHERE qrcId = ?",
                            ['payment_paid', $qrcId]
                        );
                    } else {
                        file_put_contents('/var/www/html/actions/sbp_error_log.txt', "Недопустимый tableType: $tableType для qrcId $qrcId\n", FILE_APPEND);
                    }
                }
            } catch (Exception $e) {
                file_put_contents('/var/www/html/actions/sbp_error_log.txt', "Ошибка обновления moderation для qrcId $qrcId: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }
        return $response;
    } else {
        return $response;
    }
}

global $data_base;
$qrcId = 'BD20002ST53T34EC9JCQ67G753GLCJF1';

if ($qrcId) {
    // Клиентский режим: проверка одного qrcId
    if (empty($qrcId)) {
        http_response_code(400);
        echo json_encode(['error' => 'qrcId не указан']);
        exit;
    }
    $response = processPaymentStatus($qrcId, $data_base);

    // Выводим результат для клиентского запроса
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => !isset($response['error']),
        'request_url' => sprintf("%sqr-codes/%s/payment-status", rtrim($sbpApiBaseUrl, '/').'/', urlencode($qrcId)),
        'response' => $response
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    // Cron-режим: проверка всех незавершённых платежей
    $pendingPayments = $data_base->selectAll(
        "SELECT qrcId FROM dt_payments WHERE status = 'NotStarted' AND createdAt > NOW() - INTERVAL 20 MINUTE LIMIT 100"
    );

    foreach ($pendingPayments as $payment) {
        processPaymentStatus($payment['qrcId'], $data_base);
    }
}
exit;
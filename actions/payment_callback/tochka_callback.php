<?php
file_put_contents('/var/www/html/actions/payment_error_log.txt', date('Y-m-d H:i:s') . " Крон вызвал скрипт проверки счетов\n", FILE_APPEND);

include '/var/www/html/actions/database/functions.php';
/* include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php'; */

$apiBaseUrl   = 'https://enter.tochka.com/uapi/';
$authToken    = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJ6ekhySFVta1lUU3ZuMERVdGdnVnNMRHF0TnBYaGVrUiJ9.jUpIG6PtuYA7jG59dzw-9r6mwPWvJGyTI-rDI_Sx4EIhakXYt48wy4UHavP9UR5Yy_U3LJDVFN0w7Bimrd3B8aRAvI4yDt1ZneMwfkOiYLato9ZLkKrZqHQjgty8kKEuCKf1-gEKcSYFor9sdFP4XBjRIBenxDKYjjI9Lb6UMFXUr_ET9QsIySvE8Boo6MSIneucWuXRIQnQRcLDwbde7UmfmQpK7HzG4dlNU1Y-6TSxqdbCnM8YQ90Hh-2R0stR4oNGbWxlhvaIIEPDsdmQXyHWJvzYzzktRn5uI51rOHs02j9uIXEcnHyn3Ctp7Du8FagdppCz-KJmflkxXejNON7nHUJaTOpbRjRQAZdw_YV2hZwxZd4NxiuNr4dizH6rbVkevs748SkXZzCPvMvKUnUZPciylsvByjTRJLyets01Ix6PRM695J5bchWCmlzi6uGamIm-sGSFtNjq6JvdNkKgGQbx5f34oeY5Owpb7MhWVh1gfNxRH9UIDCqN-UM_';

/* function logError($message) {
    file_put_contents(
        '/var/www/html/actions/payment_error_log.txt',
        date('Y-m-d H:i:s') . " $message\n",
        FILE_APPEND
    );
} */

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

function getCustomerCode($apiBaseUrl, $authToken): ?string {
    $customersUrl = rtrim($apiBaseUrl, '/') . '/open-banking/v1.0/customers';
    $customersResponse = apiGetRequest($customersUrl, $authToken);

    if (isset($customersResponse['error'])) {
        return null;
    }

    if (!isset($customersResponse['Data']['Customer']) || !is_array($customersResponse['Data']['Customer'])) {
        return null;
    }

    foreach ($customersResponse['Data']['Customer'] as $cust) {
        if (in_array($cust['customerType'], ['Business', 'IP'], true)) {
            return $cust['customerCode'];
        }
    }

    return null;
}

function processPaymentStatus($payment, $data_base, $apiBaseUrl, $authToken) {
    $documentId = $payment['documentId'];
    $orderId = $payment['orderId'];
    $tableType = $payment['tableType'];

    // Получаем customerCode
    $customerCode = getCustomerCode($apiBaseUrl, $authToken);
    if (!$customerCode) {
        return ['error' => 'Не удалось получить customerCode'];
    }

    // Формируем URL для получения статуса оплаты
    $paymentStatusUrl = sprintf(
        "%sinvoice/v1.0/bills/%s/%s/payment-status",
        rtrim($apiBaseUrl, '/').'/',
        urlencode($customerCode),
        urlencode($documentId)
    );

    // Делаем запрос
    $response = apiGetRequest($paymentStatusUrl, $authToken);

    // Проверяем статус
    if (!isset($response['error']) && isset($response['Data']['paymentStatus']) && $response['Data']['paymentStatus'] == 'payment_paid') {
        try {
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
                    "UPDATE dt_payments SET status = ? WHERE documentId = ?",
                    ['payment_paid', $documentId]
                );
            } else {
            }
        } catch (Exception $e) {
        }
    } else {
        $error = $response['error'] ?? 'Неизвестная ошибка или статус не payment_paid';
    }
    return $response;
}

if (!$data_base) {
    exit;
}

// Клиентский режим: проверка одного documentId

if ($documentId) {
    $result = $data_base->selectRow(
        "SELECT documentId, orderId, tableType FROM dt_payments WHERE documentId = ? AND paymentType = 'invoice'",
        [$documentId]
    );
    if (!$result) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => "Счет с documentId $documentId не найден"
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    $response = processPaymentStatus($result, $data_base, $apiBaseUrl, $authToken);
    var_dump($response);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => !isset($response['error']),
        'request_url' => sprintf("%sinvoice/v1.0/bills/[customerCode]/%s/payment-status", rtrim($apiBaseUrl, '/').'/', urlencode($documentId)),
        'response' => $response
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    // Cron-режим: проверка всех неоплаченных счетов
    $pendingPayments = $data_base->selectAll(
        "SELECT documentId, orderId, tableType FROM dt_payments WHERE paymentType = 'invoice' AND status = 'Pending' AND createdAt > NOW() - INTERVAL 24 HOUR LIMIT 100"
    );

    foreach ($pendingPayments as $payment) {
        processPaymentStatus($payment, $data_base, $apiBaseUrl, $authToken);
    }
}
exit;
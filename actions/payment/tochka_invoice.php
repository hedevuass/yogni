<?php

require_once __DIR__ . '/tochka_functions.php';

include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';

$inn          = trim($_POST['tax_code']     ?? '');
$type         = trim($_POST['customer_type'] ?? ''); // ожидаем "ip" или "company"
$service_name = trim($_POST['service_name']  ?? '');
$price        = trim($_POST['pay_amount']    ?? '');
$promoData    = $_POST['promos']             ?? [];   // массив с промо-данными (если есть)
$serviceName  = isset($_POST['service_name']) ? trim($_POST['service_name']) : null;
$orderId      = trim($_POST['orderId']);

$serviceId  = $_POST['service_id']  ?? null;
$dateStart  = $_POST['date_start']  ?? null;
$dateEnd    = $_POST['date_end']    ?? null;

// Флаг из запроса: если установлен, НЕ менять moderation (оставить 0)
$dontChangeModeration = !empty($_POST['dont_change_moderation']);

// Простая валидация
if (!in_array($type, ['ip', 'company'], true) || !isValidInn($inn, $type)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'error'   => 'ИНН не существует. Проверьте правильность ввода.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$serviceToTable = [
    'Подключение тарифа' => 'tariff',
    'Размещение карточки в промо-блоках' => 'promo',
    'Подключение опции' => 'option'
];
$tableType = $serviceToTable[$serviceName] ?? null;

// Форматируем сумму
$floatPrice     = floatval(str_replace(',', '.', $price));
$formattedPrice = number_format($floatPrice, 2, '.', '');

$apiBaseUrl = 'https://enter.tochka.com/uapi/';
$authToken = TOCHKA_TOKEN;

// Получаем из API Точки customerCode и accountId

// Получаем customerCode
$customersResponse = apiGetRequest($apiBaseUrl . "open-banking/v1.0/customers", $authToken);
if (!isset($customersResponse['Data']['Customer']) || !is_array($customersResponse['Data']['Customer'])) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Не удалось получить список customers.']);
    exit;
}
$customerCode = null;
foreach ($customersResponse['Data']['Customer'] as $customer) {
    // Берём первый попавшийся тип "Business" (юрлицо) или "IP"
    if ($customer['customerType'] === 'Business' || $customer['customerType'] === 'IP') {
        $customerCode = $customer['customerCode'];
        break;
    }
}
if (!$customerCode) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Ошибка: Не найден ни один customerCode.']);
    exit;
}

// Получаем accountId
$accountsResponse = apiGetRequest($apiBaseUrl . "open-banking/v1.0/accounts", $authToken);
if (!isset($accountsResponse['Data']['Account'][0]['accountId'])) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Не найден accountId.']);
    exit;
}
$accountId = $accountsResponse['Data']['Account'][0]['accountId'];

$secondSide = [
    "taxCode" => $inn,
    "type"    => $type
];

$invoiceNumber = uniqid();

$invoiceData = [
    "Data" => [
        "customerCode" => $customerCode,
        "accountId"    => $accountId,
        "comment"      => json_encode($promoData, JSON_UNESCAPED_UNICODE),
        "Content"      => [
            "Invoice" => [
                "number"            => $invoiceNumber,
                "basedOn"           => "Оплата по счёту №$invoiceNumber",       // описание основание
                "comment"           => "Оплата по счёту №$invoiceNumber",       // назначение платежа
                "paymentExpiryDate" => date("Y-m-d", strtotime("+7 days")),
                "date"              => date("Y-m-d"),
                "totalAmount"       => $formattedPrice,
                "totalNds"          => "0",
                "Positions"         => [
                    [
                        "positionName" => "Счёт №$invoiceNumber",               // наименование строки в счёте
                        "unitCode"     => "шт.",
                        "ndsKind"      => "nds_0",
                        "price"        => $formattedPrice,
                        "quantity"     => "1",
                        "totalAmount"  => $formattedPrice,
                        "totalNds"     => "0"
                    ]
                ]
            ]
        ],
        "SecondSide" => $secondSide
    ]
];

// Отправляем запрос на создание счёта
$invoiceResponse = apiPostRequest($apiBaseUrl . "invoice/v1.0/bills", $authToken, $invoiceData);

// Если bill создался, в ответе должен быть documentId
$documentId = $invoiceResponse['Data']['documentId'] ?? null;
if ($documentId) {
    $tableMap = [
        'tariff' => 'dt_tariff_list',
        'promo'  => 'dt_promo_list',
        'option' => 'dt_options_list'
    ];
    $targetTable = $tableMap[$tableType] ?? null;

    if ($targetTable && $orderId) {
        // 1) Обновляем запись для модерации
        $sql = "UPDATE `$targetTable` SET documentId = ?";
        $params = [$documentId];
        if (!$dontChangeModeration) {
            // По умолчанию продолжаем ставить moderation = 4
            $sql .= ", moderation = 4";
        }
        $sql .= " WHERE orderId = ?";
        $params[] = $orderId;

        $data_base->update($sql, $params);

        // 2) Получаем specialist_id
        $row = $data_base->selectRow(
            "SELECT specialist_id 
               FROM `$targetTable` 
              WHERE orderId = ? 
              LIMIT 1",
            [$orderId]
        );
        $specialist_id = $row['specialist_id'] ?? null;

        // 3) Формируем человекочитаемое название услуги
        switch ($serviceName) {
            case 'Подключение тарифа':
                $r = $data_base->selectRow(
                    "SELECT name 
                       FROM dt_tariff 
                      WHERE id = ? 
                      LIMIT 1",
                    [$serviceId]
                );
                $displayName = 'Тариф "' . ($r['name'] ?? '') . '"';
                break;

            case 'Подключение опции':
                $r = $data_base->selectRow(
                    "SELECT name 
                       FROM dt_option
                      WHERE id = ? 
                      LIMIT 1",
                    [$serviceId]
                );
                $displayName = 'Опция "' . ($r['name'] ?? '') . '"';
                break;

            case 'Размещение карточки в промо-блоках':
                // Для promo в serviceId передавали title
                $displayName = $serviceId;
                break;

            default:
                $displayName = $serviceName;
        }

        // 4) Вставляем платёж вместе с датами
        if (!$dontChangeModeration) {
            $data_base->add(
                "INSERT INTO dt_payments 
                (documentId, orderId, tableType, paymentType, status, serviceName, amount, specialist_id, date_start, date_end) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $documentId,
                    $orderId,
                    $tableType,
                    'invoice',
                    'Pending',
                    $displayName,
                    $formattedPrice,
                    $specialist_id,
                    $dateStart,
                    $dateEnd
                ]
            );
        }
    }

    // 5) Скачиваем PDF счёта
    $invoicePdfUrl = $apiBaseUrl . "invoice/v1.0/bills/$customerCode/$documentId/file";
    $opts = [
        "http" => [
            "method"  => "GET",
            "header"  => "Authorization: Bearer $authToken\r\n"
        ]
    ];
    $pdf = @file_get_contents($invoicePdfUrl, false, stream_context_create($opts));

    if ($pdf !== false) {
        // Сохраняем PDF в папку /downloads/
        $publicDir = realpath(__DIR__ . '/../../') . '/downloads/';
        if (!is_dir($publicDir)) {
            mkdir($publicDir, 0755, true);
        }
        $fileName = "invoice_{$documentId}.pdf";
        $filePath = $publicDir . $fileName;
        file_put_contents($filePath, $pdf);

        $downloadUrl = '/downloads/' . $fileName;
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success'      => true,
            'invoice_id'   => $documentId,
            'download_url' => $downloadUrl
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error'   => 'Не удалось получить PDF.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
} else {
    http_response_code(500);

    $errorCode = $invoiceResponse['Errors'][0]['errorCode'] ?? null;
    $userMessage = 'Не удалось выставить счёт. Попробуйте ещё раз или обратитесь в поддержку.';

    if ($errorCode === 'Something going wrong'
        && str_contains($invoiceResponse['Errors'][0]['message'] ?? '', 'tax code')) {
        $userMessage = 'ИНН не существует. Проверьте правильность ввода.';
    }

    // подробности — в лог
    error_log('Tochka invoice error: ' . json_encode($invoiceResponse, JSON_UNESCAPED_UNICODE));

    echo json_encode(['success' => false, 'error' => $userMessage], JSON_UNESCAPED_UNICODE);
    exit;
}

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/send_mail.php';
// Настройки
$apiBaseUrl      = 'https://enter.tochka.com/uapi/';
$sbpApiBaseUrl   = 'https://enter.tochka.com/uapi/sbp/v1.0/';
$authToken     = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJ6ekhySFVta1lUU3ZuMERVdGdnVnNMRHF0TnBYaGVrUiJ9.jUpIG6PtuYA7jG59dzw-9r6mwPWvJGyTI-rDI_Sx4EIhakXYt48wy4UHavP9UR5Yy_U3LJDVFN0w7Bimrd3B8aRAvI4yDt1ZneMwfkOiYLato9ZLkKrZqHQjgty8kKEuCKf1-gEKcSYFor9sdFP4XBjRIBenxDKYjjI9Lb6UMFXUr_ET9QsIySvE8Boo6MSIneucWuXRIQnQRcLDwbde7UmfmQpK7HzG4dlNU1Y-6TSxqdbCnM8YQ90Hh-2R0stR4oNGbWxlhvaIIEPDsdmQXyHWJvzYzzktRn5uI51rOHs02j9uIXEcnHyn3Ctp7Du8FagdppCz-KJmflkxXejNON7nHUJaTOpbRjRQAZdw_YV2hZwxZd4NxiuNr4dizH6rbVkevs748SkXZzCPvMvKUnUZPciylsvByjTRJLyets01Ix6PRM695J5bchWCmlzi6uGamIm-sGSFtNjq6JvdNkKgGQbx5f34oeY5Owpb7MhWVh1gfNxRH9UIDCqN-UM_';
$sumKopek = isset($_GET['sumKopek']) ? (int)$_GET['sumKopek'] : 0;
$orderId  = isset($_GET['orderId']) ? trim($_GET['orderId']) : null;
$serviceName = isset($_GET['serviceName']) ? trim($_GET['serviceName']) : null;

$serviceId   = isset($_GET['service_id'])  ? trim(   $_GET['service_id']  ) : null;
$dateStart   = isset($_GET['date_start'])  ? trim(   $_GET['date_start']  ) : null;
$dateEnd     = isset($_GET['date_end'])    ? trim(   $_GET['date_end']    ) : null;

if (!$sumKopek || $sumKopek <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Неверная сумма']);
    exit;
}

if (!$serviceName) {
    http_response_code(400);
    echo json_encode(['error' => 'Не указано service_name']);
    exit;
}

// Определяем tableType на основе service_name
$serviceToTable = [
    'Подключение тарифа' => 'tariff',
    'Размещение карточки в промо-блоках' => 'promo',
    'Подключение опции' => 'option'
];
$tableType = $serviceToTable[$serviceName] ?? null;

if (!$tableType) {
    http_response_code(400);
    echo json_encode(['error' => 'Недопустимое значение service_name']);
    exit;
}

// --- Универсальные функции запросов ---

function apiGetRequest(string $url, string $authToken): array
{
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "Authorization: Bearer $authToken\r\nAccept: application/json\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $respJson = @file_get_contents($url, false, $context);
    if ($respJson === false) {
        return ['error' => 'GET request вернул false'];
    }
    $data = json_decode($respJson, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Невалидный JSON в GET response'];
    }
    return $data;
}

function apiPostRequest(string $url, string $authToken, array $postData): array
{
    /* file_put_contents('post_request_log.txt', print_r(['url' => $url, 'data' => $postData], true), FILE_APPEND); */ // Логирование запроса
    $data = json_encode($postData, JSON_UNESCAPED_UNICODE);
    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Authorization: Bearer $authToken\r\n" .
                "Content-Type: application/json\r\n" .
                "Content-Length: " . strlen($data) . "\r\n" .
                "Accept: application/json\r\n",
            'content' => $data,
            'ignore_errors' => true
        ]
    ];
    $context = stream_context_create($opts);
    $respJson = @file_get_contents($url, false, $context);
    if ($respJson === false) {
        return ['error' => 'POST request вернул false'];
    }
    $data = json_decode($respJson, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Невалидный JSON в POST response'];
    }
    return $data;
}

// --- Получаем данные из Get Customer Info ---

$customerCode = '300007369';
$bankCode = '044525104';
$customerInfoUrl = $sbpApiBaseUrl . "customer/{$customerCode}/{$bankCode}";
$customerInfo = apiGetRequest($customerInfoUrl, $authToken);

if (!empty($customerInfo['error']) || !isset($customerInfo['Data']['MerchantList']) || !isset($customerInfo['Data']['AccountList'])) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Не удалось получить данные клиента', 'details' => $customerInfo]);
    exit;
}

// Извлекаем merchantId активного мерчанта
$merchantId = null;
foreach ($customerInfo['Data']['MerchantList'] as $merchant) {
    if ($merchant['status'] === 'Active') {
        $merchantId = $merchant['merchantId'];
        break;
    }
}

if (!$merchantId) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Не найден активный merchantId']);
    exit;
}

// Извлекаем accountId (полное значение)
$accountId = null;
if (!empty($customerInfo['Data']['AccountList'])) {
    $accountId = $customerInfo['Data']['AccountList'][0]['accountId']; // Используем полное значение
}

if (!$accountId) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Не найден accountId']);
    exit;
}

$legalId = $customerInfo['Data']['legalId'] ?? null;

// --- Создаём QR-код ---

$postData = [
    'Data' => [
        'amount'   => $sumKopek,
        'currency' => 'RUB',
        'qrcType'  => '02', // Динамический QR-код
        'ttl'      => 10, // 10 минут
        'paymentPurpose' => 'Оплата заказа',
        'sourceName' => 'URFAQ.RU',
        'redirectUrl' => 'https://urfaq.ru/cabinet/page_promo',
        'callbackUrl' => 'https://urfaq.ru/actions/payment_callback/sbp_callback.php',
        'imageParams' => [
            'width' => 200,
            'height' => 200,
            'mediaType' => 'image/png'
        ]
    ]
];

if ($orderId) {
    $postData['orderId'] = $orderId;
}

$qrPath = "qr-code/merchant/{$merchantId}/{$accountId}";
$qr = apiPostRequest($sbpApiBaseUrl . $qrPath, $authToken, $postData);

if (!empty($qr['error']) || !isset($qr['Data']['payload'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка регистрации QR', 'details' => $qr]);
    exit;
}

$paymentUrl = $qr['Data']['payload'];
$qrcId = $qr['Data']['qrcId'] ?? null;

// Сохраняем qrcId в dt_payments
$tableMap    = [
    'tariff' => 'dt_tariff_list',
    'promo'  => 'dt_promo_list',
    'option' => 'dt_options_list'
];
$targetTable = $tableMap[$tableType] ?? null;

// Если есть QR‑код и заказ
if ($qrcId && $orderId && $targetTable) {
    try {
        // 1) Получаем specialist_id из целевой таблицы
        $row = $data_base->selectRow(
            "SELECT specialist_id 
               FROM `$targetTable` 
              WHERE orderId = ? 
              LIMIT 1",
            [$orderId]
        );
        $specialist_id = $row['specialist_id'] ?? null;

        // 2) Преобразуем сумму в рубли
        $amountRub = $sumKopek / 100;

        // 3) Собираем читабельное название услуги
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
                // Для промо мы передали title в service_id
                $displayName = $serviceId;
                break;

            default:
                $displayName = $serviceName;
        }

        // 4) Вставляем запись в dt_payments с датами
        $data_base->add(
            "INSERT INTO dt_payments 
                (qrcId, orderId, tableType, status, specialist_id, serviceName, amount, date_start, date_end) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $qrcId,
                $orderId,
                $tableType,
                'NotStarted',
                $specialist_id,
                $displayName,
                $amountRub,
                $dateStart,
                $dateEnd
            ]
        );

        // 5) Для промо — сразу переводим в модерацию
        if ($tableType === 'promo') {
            $data_base->update(
                "UPDATE dt_promo_list 
                    SET qrcId = ?, moderation = 4 
                  WHERE orderId = ?",
                [$qrcId, $orderId]
            );
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Ошибка сохранения данных: ' . $e->getMessage()]);
        exit;
    }
}

// --- Возвращаем JSON с URL QR-кода ---
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'payload' => $paymentUrl,
    'qrcId' => $qrcId
]);
exit;

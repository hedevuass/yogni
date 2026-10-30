<?php
session_start(); // Запускаем сессию

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/send_mail.php';

// Получаем данные из POST (с формы оплаты для физического лица)
$client_name  = $_POST['client_name']  ?? 'Client Name';
$reg_email = $_POST['reg_email'] ?? '';
$client_email = !empty($_POST['client_email']) ? $_POST['client_email'] : $reg_email;
$client_phone = $_POST['client_phone'];
$pay_amount   = isset($_POST['pay_amount']) ? (float) $_POST['pay_amount'] : 1000;
$service_type = $_POST['service_type'] ?? 'default';

$serviceId  = $_POST['service_id']    ?? null;
$dateStart  = $_POST['date_start']    ?? null;
$dateEnd    = $_POST['date_end']      ?? null;

$service_type = $_POST['service_type'];

// Дополнительные данные
$service_name = $_POST['service_name'] ?? 'Test Service';
// Забираем orderId, переданный из формы
$orderId      = $_POST['orderId'] ?? '';

// Данные для авторизации в Paykeeper
$user = $_ENV['PAYKEEPER_USER'];
$password = $_ENV['PAYKEEPER_PASSWORD'];
$server_paykeeper = $_ENV['PAYKEEPER_SERVER'];

// Определяем tableType на основе service_name
$serviceToTable = [
    'Подключение тарифа' => 'tariff',
    'Размещение карточки в промо-блоках' => 'promo',
    'Подключение опции' => 'option'
];
$tableType = $serviceToTable[$service_name] ?? null;

if (!$tableType) {
    http_response_code(400);
    echo json_encode(['error' => 'Недопустимое значение service_name']);
    exit;
}

// Проверяем, есть ли в сессии свежий токен
$token_ttl = 600; // время жизни токена
if (
    empty($_SESSION['paykeeper_token']) ||
    empty($_SESSION['paykeeper_token_time']) ||
    $_SESSION['paykeeper_token_time'] < time() - $token_ttl
) {
    // Нужно получить новый токен
    $base64 = base64_encode("$user:$password");
    $headers = [
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Basic ' . $base64
    ];

    $token_response = file_get_contents(
        $server_paykeeper . '/info/settings/token/',
        false,
        stream_context_create([
            'http' => [
                'method'       => 'GET',
                'header'       => implode("\r\n", $headers),
                'ignore_errors' => true
            ]
        ])
    );
    $token_data = json_decode($token_response, true);
    $token = $token_data['token'] ?? null;
    if (!$token) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Не удалось получить токен']);
        exit;
    }

    // Кэшируем токен в сессии
    $_SESSION['paykeeper_token']      = $token;
    $_SESSION['paykeeper_token_time'] = time();
} else {
    // Берём токен из сессии
    $token = $_SESSION['paykeeper_token'];
}

// Формируем success_url и данные для preview
$success_url = 'https://yogoort.ru/cabinet/page_promo?orderId=' . urlencode($orderId);
$payment_data = [
    'orderid'      => $orderId,
    'pay_amount'   => $pay_amount,
    'clientid'     => $client_name,
    'client_email' => $client_email,
    'service_name' => $service_name,
    'client_phone' => $client_phone,
    'token'        => $token,
    'success_url'  => $success_url
];

// Делаем preview-запрос
$base64 = base64_encode("$user:$password");
$headers = [
    'Content-Type: application/x-www-form-urlencoded',
    'Authorization: Basic ' . $base64
];
$preview_response = file_get_contents(
    $server_paykeeper . '/change/invoice/preview/',
    false,
    stream_context_create([
        'http' => [
            'method'       => 'POST',
            'header'       => implode("\r\n", $headers),
            'content'      => http_build_query($payment_data),
            'ignore_errors' => true
        ]
    ])
);
$preview = json_decode($preview_response, true);

if (!empty($preview['invoice_id']) && !empty($preview['invoice_url'])) {
    // Таблицы, в которых хранятся данные перед модерацией
    $tableMap = [
        'tariff' => 'dt_tariff_list',
        'promo'  => 'dt_promo_list',
        'option' => 'dt_options_list'
    ];
    $targetTable = $tableMap[$tableType] ?? null;

    if ($targetTable && $orderId) {
        // Берём часть orderId без последнего символа
        $orderIdPrefix = substr($orderId, 0, -1);

        // Находим все записи с совпадением по префиксу
        $promoRows = $data_base->selectAll(
            "SELECT * FROM `$targetTable` WHERE LEFT(orderId, LENGTH(orderId)-1) = ?",
            [$orderIdPrefix]
        );

        foreach ($promoRows as $row) {
            $promoOrderId = $row['orderId'];
            $specialist_id = $row['specialist_id'];

            // Обновляем moderation
            $data_base->update(
                "UPDATE `$targetTable` SET moderation = 4 WHERE orderId = ?",
                [$promoOrderId]
            );

            // Проверяем, есть ли уже запись в dt_payments
            $exists = $data_base->selectRow(
                "SELECT 1 FROM dt_payments WHERE orderId = ? LIMIT 1",
                [$promoOrderId]
            );

            if (!$exists) {
                // Формируем displayName
                switch ($service_name) {
                    case 'Подключение тарифа':
                        $r = $data_base->selectRow(
                            "SELECT name FROM dt_tariff WHERE id = ? LIMIT 1",
                            [$serviceId]
                        );
                        $displayName = 'Тариф "' . ($r['name'] ?? '') . '"';
                        $amount = $pay_amount; // берём из формы
                        break;

                    case 'Подключение опции':
                        $r = $data_base->selectRow(
                            "SELECT name FROM dt_option WHERE id = ? LIMIT 1",
                            [$serviceId]
                        );
                        $displayName = 'Опция "' . ($r['name'] ?? '') . '"';
                        $amount = $pay_amount; // берём из формы
                        break;

                    case 'Размещение карточки в промо-блоках':
                        $displayName = $row['service_name'];
                        $amount = $row['amount']; // берём цену из dt_promo_list
                        $sort = $row['sort'];
                        break;

                    default:
                        $displayName = $service_name;
                        $amount = $pay_amount;
                }

                // Вставка в dt_payments
                $data_base->add(
                    "INSERT INTO dt_payments 
                        (orderId, tableType, paymentType, status, serviceName, amount, specialist_id, date_start, date_end, sort, paymentDate) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)",
                    [
                        $promoOrderId,
                        $tableType,
                        'card',
                        'Pending',
                        $displayName,
                        $amount,
                        $specialist_id,
                        $row['date_start'] ?? $dateStart,
                        $row['date_expires'] ?? $dateEnd,
                        $sort ?? null
                    ]
                );
            }
        }
    }

    // Добавляем параметр pstype к ссылке счета для карт
    $invoice_url_with_pstype = $preview['invoice_url'];
    if (strpos($invoice_url_with_pstype, '?') !== false) {
        $invoice_url_with_pstype .= '&pstype=' . urlencode($service_type);
    } else {
        $invoice_url_with_pstype .= '?pstype=' . urlencode($service_type);
    }

    // Возвращаем клиенту JSON с invoice_id и orderId
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success'     => true,
        'invoice_id'  => $preview['invoice_id'],
        'invoice_url' => $invoice_url_with_pstype,
        'orderId'     => $orderId
    ]);
    exit;
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Не удалось получить ссылку на оплату.'
    ]);
    exit;
}

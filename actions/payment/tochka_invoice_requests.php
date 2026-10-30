<?php
// actions/payment/tochka_invoice_requests.php
// Счёт на оплату заявок специалиста: создаёт счёт в Точке, ставит заявкам статус «Отправлен счет», отдаёт ссылку на PDF.
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
require_once __DIR__ . '/tochka_functions.php';

header('Content-Type: application/json; charset=utf-8');

function invoice_fail(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// TODO: проверка прав администратора, если она есть в других actions

$specialistId = (int)($_POST['specialist_id'] ?? 0);
$ids = array_values(array_unique(array_filter(array_map('intval', (array)($_POST['ids'] ?? [])))));
$inn = trim($_POST['tax_code'] ?? '');
$type = strlen($inn) === 12 ? 'ip' : (strlen($inn) === 10 ? 'company' : '');

if (!$specialistId || !$ids) {
    invoice_fail(422, 'Не выбраны заявки');
}
if (!$type || !ctype_digit($inn) || !isValidInn($inn, $type)) {
    invoice_fail(422, 'ИНН не существует. Проверьте правильность ввода.');
}

// Заявки берём из БД, а не с клиента: только этого специалиста, не новые и не спам
$codes = request_status_codes();
$editable = [$codes['sent'], $codes['invoice_sent'], $codes['paid'], $codes['unpaid']];
$editablePh = implode(',', array_fill(0, count($editable), '?'));
$idsPh = implode(',', array_fill(0, count($ids), '?'));

$rows = $data_base->selectAll(
    "SELECT id, created_at FROM dt_user_request
     WHERE specialist_id = ? AND moderation IN ($editablePh) AND id IN ($idsPh)",
    array_merge([$specialistId], $editable, $ids)
);
if (!$rows) {
    invoice_fail(422, 'Нет заявок, по которым можно выставить счёт');
}

$validIds = array_map('intval', array_column($rows, 'id'));
$count = count($validIds);

// Сумма считается на сервере
$unitPrice = get_request_price();
if ($unitPrice <= 0) {
    invoice_fail(500, 'Не удалось определить стоимость заявки');
}
$unit = number_format($unitPrice, 2, '.', '');
$total = number_format($unitPrice * $count, 2, '.', '');

$timestamps = array_map(fn($r) => strtotime($r['created_at']), $rows);
$periodStart = date('d.m.Y', min($timestamps));
$periodEnd = date('d.m.Y', max($timestamps));
$period = $periodStart === $periodEnd ? $periodStart : "$periodStart - $periodEnd";

$apiBaseUrl = 'https://enter.tochka.com/uapi/';
$authToken = TOCHKA_TOKEN;

// customerCode
$customers = apiGetRequest($apiBaseUrl . 'open-banking/v1.0/customers', $authToken);
$customerCode = null;
foreach ($customers['Data']['Customer'] ?? [] as $customer) {
    if (in_array($customer['customerType'] ?? '', ['Business', 'IP'], true)) {
        $customerCode = $customer['customerCode'];
        break;
    }
}
if (!$customerCode) {
    invoice_fail(500, 'Не найден customerCode.');
}

// accountId
$accounts = apiGetRequest($apiBaseUrl . 'open-banking/v1.0/accounts', $authToken);
$accountId = $accounts['Data']['Account'][0]['accountId'] ?? null;
if (!$accountId) {
    invoice_fail(500, 'Не найден accountId.');
}

$invoiceNumber = uniqid();

$invoiceData = [
    'Data' => [
        'customerCode' => $customerCode,
        'accountId'    => $accountId,
        'comment'      => json_encode(['type' => 'requests', 'specialist_id' => $specialistId, 'request_ids' => $validIds], JSON_UNESCAPED_UNICODE),
        'Content'      => [
            'Invoice' => [
                'number'            => $invoiceNumber,
                'basedOn'           => "Оплата по счёту №$invoiceNumber",
                'comment'           => "Оплата по счёту №$invoiceNumber",
                'paymentExpiryDate' => date('Y-m-d', strtotime('+1 day')), // в письме: оплата в течение 24 часов
                'date'              => date('Y-m-d'),
                'totalAmount'       => $total,
                'totalNds'          => '0',
                'Positions'         => [
                    [
                        'positionName' => "Заявки за период $period",
                        'unitCode'     => 'шт.',
                        'ndsKind'      => 'nds_0',
                        'price'        => $unit,
                        'quantity'     => (string)$count,
                        'totalAmount'  => $total,
                        'totalNds'     => '0'
                    ]
                ]
            ]
        ],
        'SecondSide' => ['taxCode' => $inn, 'type' => $type]
    ]
];

$invoiceResponse = apiPostRequest($apiBaseUrl . 'invoice/v1.0/bills', $authToken, $invoiceData);
$documentId = $invoiceResponse['Data']['documentId'] ?? null;

if (!$documentId) {
    $userMessage = 'Не удалось выставить счёт. Попробуйте ещё раз или обратитесь в поддержку.';
    if (($invoiceResponse['Errors'][0]['errorCode'] ?? null) === 'Something going wrong'
        && str_contains($invoiceResponse['Errors'][0]['message'] ?? '', 'tax code')) {
        $userMessage = 'ИНН не существует. Проверьте правильность ввода.';
    }
    error_log('Tochka requests invoice error: ' . json_encode($invoiceResponse, JSON_UNESCAPED_UNICODE));
    invoice_fail(500, $userMessage);
}

// Счёт создан: ставим заявкам статус «Отправлен счет»
$updatePh = implode(',', array_fill(0, $count, '?'));
$data_base->update(
    "UPDATE dt_user_request SET moderation = ? WHERE specialist_id = ? AND id IN ($updatePh)",
    array_merge([$codes['invoice_sent']], [$specialistId], $validIds)
);

// Скачиваем PDF счёта
$invoicePdfUrl = $apiBaseUrl . "invoice/v1.0/bills/$customerCode/$documentId/file";
$opts = ['http' => ['method' => 'GET', 'header' => "Authorization: Bearer $authToken\r\n"]];
$pdf = @file_get_contents($invoicePdfUrl, false, stream_context_create($opts));

if ($pdf === false) {
    invoice_fail(500, 'Не удалось получить PDF.');
}

$publicDir = realpath(__DIR__ . '/../../') . '/downloads/';
if (!is_dir($publicDir)) {
    mkdir($publicDir, 0755, true);
}
$fileName = "invoice_{$documentId}.pdf";
file_put_contents($publicDir . $fileName, $pdf);

echo json_encode([
    'success'      => true,
    'invoice_id'   => $documentId,
    'download_url' => '/downloads/' . $fileName,
    'count'        => $count,
    'period'       => $period,
    'updated'      => $validIds,
], JSON_UNESCAPED_UNICODE);
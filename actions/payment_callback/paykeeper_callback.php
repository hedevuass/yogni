<?php
$logPath = __DIR__ . '/paykeeper_callback.log';
$input   = file_get_contents('php://input');
$time    = date('Y-m-d H:i:s');

file_put_contents($logPath, "[$time] Received callback:\n$input\n\n", FILE_APPEND|LOCK_EX);

include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

$secret = ']4eHBw)H=N)=gT7';

/**
 * Функция-обёртка: логирует успешную оплату
 */
function log_payment(string $orderId): void {
    global $data_base;
    $data_base->update(
        "UPDATE dt_payments SET status = ?, paymentDate = NOW() WHERE orderId = ?",
        ['payment_paid', $orderId]
    );
}

/**
 * Обновляет тарифы и логирует оплату
 */
function update_tariff_moderation(string $orderId): int {
    global $data_base, $logPath;
    $time = date('Y-m-d H:i:s');
    file_put_contents($logPath, "[$time] update_tariff_moderation for orderId=$orderId\n", FILE_APPEND|LOCK_EX);

    $tariff = $data_base->selectRow(
        "SELECT specialist_id FROM dt_tariff_list WHERE orderId = ?",
        [$orderId]
    );
    if (!$tariff) return 0;

    $specialist_id = $tariff['specialist_id'];

    // Деактивируем старые и активируем новый
    $data_base->update(
        "DELETE FROM dt_tariff_list WHERE specialist_id = ? AND moderation = 0 AND (orderId != ? OR orderId IS NULL OR ? IS NULL)",
        [$specialist_id, $orderId, $orderId]
    );
    $res = $data_base->update(
        "UPDATE dt_tariff_list SET moderation = 0 WHERE orderId = ?",
        [$orderId]
    );

    if ($res) {
        log_payment($orderId);
    }
    return $res;
}

/**
 * Обновляет промо и логирует оплату
 */
function update_promo_moderation(string $orderId): int {
    global $data_base, $logPath;
    $time = date('Y-m-d H:i:s');
    file_put_contents($logPath, "[$time] update_promo_moderation for orderId=$orderId\n", FILE_APPEND|LOCK_EX);

    $row = $data_base->selectRow("SELECT id, specialist_id FROM dt_promo_list WHERE orderId = ?", [$orderId]);
    if (!$row) return 0;

    $res = $data_base->update(
        "UPDATE dt_promo_list SET moderation = 0 WHERE orderId = ?",
        [$orderId]
    );

    if ($res) {
        log_payment($orderId);
    }
    return $res;
}

/**
 * Обновляет опции и логирует оплату
 */
function update_options_moderation(string $orderId): int {
    global $data_base, $logPath;
    $time = date('Y-m-d H:i:s');
    file_put_contents($logPath, "[$time] update_options_moderation for orderId=$orderId\n", FILE_APPEND|LOCK_EX);

    $row = $data_base->selectRow("SELECT id, specialist_id FROM dt_options_list WHERE orderId = ?", [$orderId]);
    if (!$row) return 0;

    $res = $data_base->update(
        "UPDATE dt_options_list SET moderation = 0 WHERE orderId = ?",
        [$orderId]
    );

    if ($res) {
        log_payment($orderId);
    }
    return $res;
}

// Парсим входные данные
parse_str($input, $data);
$orderId = $data['orderid'] ?? '';
$result  = 0;

if ($orderId) {
    $result = update_tariff_moderation($orderId);
    if (!$result) $result = update_promo_moderation($orderId);
    if (!$result) $result = update_options_moderation($orderId);
}

file_put_contents($logPath, "[$time] Final result: $result\n", FILE_APPEND|LOCK_EX);
file_put_contents($logPath, "Parsed data: " . print_r($data, true) . "\n", FILE_APPEND|LOCK_EX);

// Проверка подписи
$signature = $data['key'] ?? '';
$signString = ($data['orderId'] ?? '') . ($data['id'] ?? '') . $secret;
$calcSign   = strtoupper(md5($signString));

if (empty($signature) || $calcSign !== $calcSign) {
    http_response_code(400);
    exit('Bad signature');
}

http_response_code(200);
echo "OK";
<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

// Получаем данные из POST-запроса
$specialist_id = (int) ($_POST['specialist_id'] ?? 0);
$sort          = (int) ($_POST['sort'] ?? 0);
$date_start    = trim($_POST['date_start'] ?? '') ?: null;
$date_expires  = trim($_POST['date_expires'] ?? '') ?: null;
$service_name  = trim($_POST['service_name'] ?? '');
$service_slug  = trim($_POST['service_slug'] ?? '');
$block_id      = isset($_POST['block_id']) && $_POST['block_id'] !== ''
    ? (int) $_POST['block_id']
    : null;
$amount        = $_POST['amount'] ?? '';
$orderId       = $_POST['order_id'] ?? null;
$id            = (int) ($_POST['id'] ?? 0);

// **Новый параметр**: moderation
$moderation    = isset($_POST['moderation'])
    ? (int) $_POST['moderation']
    : 0;

// Валидация
if ($specialist_id <= 0 || $service_name === '') {
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

$table = "dt_promo_list";

if ($id > 0) {
    // Обновление
    $sql = "UPDATE $table SET 
                specialist_id = ?, 
                sort = ?, 
                date_start = ?, 
                date_expires = ?, 
                service_name = ?, 
                service_slug = ?, 
                block_id = ?,
                orderId = ?,
                moderation = ?
            WHERE id = ?";
    $result = $data_base->update($sql, [
        $specialist_id,
        $sort,
        $date_start,
        $date_expires,
        $service_name,
        $service_slug,
        $block_id,
        $orderId,
        $moderation,   // ← здесь
        $id
    ]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Connection updated']);
    } else {
        echo json_encode(['error' => 'Could not update connection']);
    }
} else {
    // Новая запись — сначала платеж
    $data_base->add(
        "INSERT INTO dt_payments 
            (qrcId, orderId, tableType, status, specialist_id, serviceName, amount, sort, date_start, date_end, paymentDate)
         VALUES (?,?,?,?,?,?,?,?,?,?,NOW())",
        [
            null,
            $orderId,
            'promo',
            'payment_paid',
            $specialist_id,
            $service_name,
            $amount,
            $sort,
            $date_start,
            $date_expires
        ]
    );

    // Потом сам promo-list с нужным moderation
    $sql = "INSERT INTO $table
                (specialist_id, sort, date_start, date_expires, service_name, service_slug, block_id, orderId, moderation)
            VALUES (?,?,?,?,?,?,?,?,?)";
    $result = $data_base->add($sql, [
        $specialist_id,
        $sort,
        $date_start,
        $date_expires,
        $service_name,
        $service_slug,
        $block_id,
        $orderId,
        $moderation   // ← и здесь
    ]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Connection added', 'id' => $result]);
        clearCache('specialists_lawyers');
        clearCache('specialists_experts');
        clearCache('specialists_notaries');
        clearCache('promo_block_top_specialists');

        clearCache('catalog_by_service');
        clearCache('catalog_by_classification');
    } else {
        echo json_encode(['error' => 'Could not insert connection']);
    }
}

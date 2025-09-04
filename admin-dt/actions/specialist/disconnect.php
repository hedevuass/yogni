<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$specialistId = $_POST['specialist_id'] ?? null;
$id = $_POST['id'] ?? null;
$type = $_POST['type'] ?? null;

if (!$specialistId || !$id || !$type) {
    echo json_encode(['success' => false, 'error' => 'Недостаточно данных']);
    exit;
}
try {
    if ($type == "tariff") {
        // Получаем orderId перед удалением
        $sqlGetOrderId = "SELECT orderId FROM dt_tariff_list WHERE specialist_id = ? AND id = ?";
        $orderIdRow = $data_base->selectRow($sqlGetOrderId, [$specialistId, $id]);
        $orderId = $orderIdRow['orderId'] ?? null;

        $sql = "DELETE FROM dt_tariff_list WHERE specialist_id = ? AND id = ?";
        $params = [$specialistId, $id];
    } elseif ($type == "option") {
        $sqlGetOrderId = "SELECT orderId FROM dt_options_list WHERE specialist_id = ? AND id = ?";
        $orderIdRow = $data_base->selectRow($sqlGetOrderId, [$specialistId, $id]);
        $orderId = $orderIdRow['orderId'] ?? null;

        $sql = "DELETE FROM dt_options_list WHERE specialist_id = ? AND id = ?";
        $params = [$specialistId, $id];
    } elseif ($type == "promo") {
        $sqlGetOrderId = "SELECT orderId FROM dt_promo_list WHERE id = ?";
        $orderIdRow = $data_base->selectRow($sqlGetOrderId, [$id]);
        $orderId = $orderIdRow['orderId'] ?? null;

        $sql = "DELETE FROM dt_promo_list WHERE id = ?";
        $params = [$id];
    } else {
        echo json_encode(['success' => false, 'error' => 'Некорректный тип']);
        exit;
    }

    $result = $data_base->delete($sql, $params);

    if ($result) {
        // Обновляем статус в dt_payments, если orderId найден
        if (!empty($orderId)) {
            $sqlUpdatePayment = "UPDATE dt_payments SET status = 'cancelled' WHERE orderId = ?";
            $data_base->update($sqlUpdatePayment, [$orderId]);
        }

        echo json_encode(['success' => true, 'message' => ucfirst($type) . ' успешно удален']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Ошибка при удалении']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}



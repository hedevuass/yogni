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
        $sql = "DELETE FROM dt_tariff_list WHERE specialist_id = ? AND id = ?";
        $params = [$specialistId, $id];
    } elseif ($type == "option") {
        $sql = "DELETE FROM dt_options_list WHERE specialist_id = ? AND id = ?";
        $params = [$specialistId, $id];
    } elseif ($type == "promo") {
        $sql = "DELETE FROM dt_promo_list WHERE id = ?";
        $params = [$id]; // Тут specialist_id не нужен
    } else {
        echo json_encode(['success' => false, 'error' => 'Некорректный тип']);
        exit;
    }

    $result = $data_base->delete($sql, $params);

    if ($result) {
        echo json_encode(['success' => true, 'message' => ucfirst($type) . ' успешно удален']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Ошибка при удалении']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных: ' . $e->getMessage()]);
}


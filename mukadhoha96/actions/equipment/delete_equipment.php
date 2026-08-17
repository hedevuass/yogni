<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';

if (!$id || $id === 'new') {
    echo json_encode(['error' => 'Неверный ID элемента']);
    exit;
}

$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/equipment/';

$item = $data_base->selectRow("SELECT img, img_pink FROM dt_equipment WHERE id = ?", [$id]);

if ($item) {
    // Удаляем обычную иконку физически
    if (!empty($item['img']) && file_exists($uploadDir . $item['img'])) {
        @unlink($uploadDir . $item['img']);
    }
    
    // Удаляем розовую иконку физически
    if (!empty($item['img_pink']) && file_exists($uploadDir . $item['img_pink'])) {
        @unlink($uploadDir . $item['img_pink']);
    }

    // Удаляем из БД
    $data_base->delete("DELETE FROM dt_equipment WHERE id = ?", [$id]);

    // Успех
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Элемент не найден в базе данных']);
}
exit;
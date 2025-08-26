<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$action = $_POST['action'] ?? '';
$type = $_POST['type'] ?? '';
$id = $_POST['id'] ?? 0;

// Валидация параметров
if (!in_array($action, ['publish', 'delete']) || !in_array($type, ['banner', 'journal']) || !is_numeric($id)) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

$id = (int) $id;
// Определяем таблицу для работы
$table = ($type === 'banner') ? 'dt_banners_list' : 'dt_journal_list';

if ($action === 'publish') {
    // Получаем дополнительные поля
    $ad_line1 = $_POST['ad_line1'] ?? '';
    $ad_line2 = $_POST['ad_line2'] ?? '';
    $ad_line3 = $_POST['ad_line3'] ?? '';
    // Если поле пустое, устанавливаем NULL
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

    // Кодируем описание в JSON
    $description_json = json_encode([
        'ad_line1' => $ad_line1,
        'ad_line2' => $ad_line2,
        'ad_line3' => $ad_line3,
    ]);

    // Обновляем запись: меняем moderation с 1 на 0 и обновляем поля description, start_date, end_date
    $sql = "UPDATE $table 
            SET moderation = 0, description = ?, start_date = ?, end_date = ?
            WHERE id = ?";
    $result = $data_base->update($sql, [$description_json, $start_date, $end_date, $id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Record published']);
    } else {
        echo json_encode(['error' => 'Could not update record']);
    }
} elseif ($action === 'delete') {
    // Обновляем поле rejected вместо удаления записи
    $sql = "UPDATE $table SET rejected = 1 WHERE id = ?";
    $result = $data_base->update($sql, [$id]);
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Запись отклонена']);
    } else {
        echo json_encode(['error' => 'Could not mark record as rejected']);
    }
}
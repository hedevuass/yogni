<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['table'], $_POST['id'], $_POST['status'])) {
    // Очистка значений
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['table']);
    $id = intval($_POST['id']);
    $status = intval($_POST['status']);

    // Проверка допустимых таблиц
    $allowedTables = ['dt_post', 'dt_journal_list', 'dt_banners_list', 'dt_pro_photo_list']; 
    if (!in_array($table, $allowedTables)) {
        echo json_encode(["success" => false, "error" => "Invalid table"]);
        exit;
    }

    // Запрос для обновления поля moderation
    $query = "UPDATE $table SET moderation = ? WHERE id = ? AND moderation != ?";
    
    // Выполнение запроса
    try {
        $data_base->update($query, [$status, $id, $status]); // Вызываем метод update с параметрами
        echo json_encode(["success" => true]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "error" => "Ошибка базы данных: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Missing parameters"]);
}

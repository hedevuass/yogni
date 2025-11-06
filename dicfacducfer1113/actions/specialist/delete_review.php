<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $data_base;
    $review_id = $_POST['id'] ?? null;

    if (!$review_id) {
        echo json_encode(["success" => false, "error" => "Не передан ID отзыва"]);
        exit;
    }

    // Удаляем отзыв по ID
    $deleted = $data_base->delete("DELETE FROM dt_specialist_review WHERE id = ?", [$review_id]);

    if ($deleted) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Ошибка удаления из базы"]);
    }
}
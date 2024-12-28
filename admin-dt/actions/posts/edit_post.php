<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$response = [];

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);

    try {
        update_post($id);
        $response['success'] = 'Пост обновлён';
    } catch (Exception $e) {
        $response['fatal'] = 'Редактирование временно недоступно. Ошибка: ' . $e->getMessage();
    }

    responseJson($response);
}

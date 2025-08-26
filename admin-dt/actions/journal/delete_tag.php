<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$response = [];

if (isset($_POST['id'])) {
    $id = (int) $_POST['id'];
    try {
        $data_base->delete('DELETE FROM dt_tags WHERE id = ?', [ $id ]);

        $response['success'] = true;
    } catch (Exception $e) {
        $response['fatal'] = 'Ошибка удаления: ' . $e->getMessage();
    }
} else {
    $response['error'] = 'Нет ID';
}

responseJson($response);

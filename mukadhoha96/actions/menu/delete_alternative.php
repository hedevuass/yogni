<?php

include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$response = [];

if (isset($_POST['id'], $_POST['type'])) {
    $id = (int)$_POST['id'];
    $type = clean_elem_form($_POST['type']);

    try {
        if ($type === 'page') {
            $result = delete_alternative_page($id);
        } else {
            $result = delete_service($id);
        }

        if ($result) {
            $response['success'] = 'Элемент успешно удалён';
        } else {
            $response['error'] = 'Не удалось удалить элемент';
        }
    } catch (Exception $e) {
        $response['fatal'] = 'Ошибка удаления: ' . $e->getMessage();
    }
} else {
    $response['error'] = 'Некорректные входные данные';
}

responseJson($response);

?>
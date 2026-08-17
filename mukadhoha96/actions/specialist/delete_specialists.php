<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . '/actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);

    if (delete_specialist($id)) {
        $json['success'] = 'Специалист удалён';
    } else {
        $json['error'] = 'Удаление специалистов временно недоступно';
    }

    responseJson($json);
}
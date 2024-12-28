<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . '/actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);

    if (delete_option($id)) {
        $json['success'] = 'Раздел удалён';
    } else {
        $json['error'] = 'Удаление раздела временно недоступно';
    }

    responseJson($json);
}
<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . '/actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $status = (int)$_POST['status'];
    
    if(update_request($id, $status)) {
        $json['success'] = 'Запрос обновлен';
    } else {
        $json['error'] = 'Произошла ошибка';
    }

    responseJson($json);
}




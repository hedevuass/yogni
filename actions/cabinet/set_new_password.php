<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

if (isset($_POST['new_password'])) {
    $data['password'] = clean_elem_form($_POST['new_password']);
    if (mb_strlen($data['password'], 'UTF-8') < 6) {
        $json['error'] = 'Минимальная длина пароля 6 символов';
        responseJson($json);
    } else {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
    }
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $user_id = intval($_POST['id']);
    } else {
        $user_id = $_SESSION['user']['id'];
    }
    if (set_new_password($data['password'], $user_id)) {
        $json['success'] = true;
    } else {
        $json['error'] = 'Произошла ошибка. Повторите попытку позже.';
    }
    responseJson($json);
}

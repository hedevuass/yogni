<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

if (isset($_POST['password'])) {
    $data['password'] = clean_elem_form($_POST['password']);
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $specialist_id = intval($_POST['id']);
        $user_pass = get_user_by_id($specialist_id)['password'];
    } else {
        $user_pass = get_user_by_id($_SESSION['user']['id'])['password'];
    }

    if (password_verify($data['password'], $user_pass)) {
        $json['success'] = true;
    } else {
        $json['error'] = true;
        $json['id'] = $specialist_id;
    }
    responseJson($json);
}


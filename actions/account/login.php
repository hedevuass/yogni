<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

if (isset($_POST['email'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    if (!$data['email']) {
        $json['error']['email'] = 'Укажите номер телефона или email';
    } else {
        $pattern = '/^((\+?7|8)(?!95[4-7]|99[08]|907|94[^09]|336)([348]\d|9[0-6789]|7[0247])\d{8}|\+?(99[^4568]\d{7,11}|994\d{9}|9955\d{8}|996[57]\d{8}|9989\d{8}|380[34569]\d{8}|375[234]\d{8}|372\d{7,8}|37[0-4]\d{8}))$/';
        $tel = preg_replace("/[^0-9]/", '', $data['email']);
        if (substr($tel, 0, 1) == 8) $tel[0] = 7;
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) && !preg_match($pattern, $tel)) $json['error']['email'] = 'Некорректный номер телефона или email';
    }

    //if (strlen($data['password']) < 6) $json['error']['password'] = 'Минимальная длина пароля 6 символов';

    if (isset($json['error'])) responseJson($json);

    $user = get_user_by_email($data['email']);

    if (!$user) {
        $json['error']['auth'] = 'Неправильный логин или пароль';
        responseJson($json);
    }
    if ($user['active'] == 0) {
        $json['error']['email_resend'] = true;
        responseJson($json);
    }

    if (password_verify($data['password'], $user['password'])) {
        if(!is_null($user['deleted_at'])) {
            if ($user['deleted'] == 1) {
                if (strtotime('now') - strtotime('30 days') > strtotime($user['deleted_at'])) {
                    $json['error']['auth'] = 'Аккаунт был удален. Для восстановления данных свяжитесь с поддержкой.';
                    responseJson($json);
                } else {
                    recover_user($user['id']);
                }
            }
        }
        $json['success'] = '/cabinet';

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name']
        ];
    } else {
        $json['error']['auth'] = 'Неправильный логин или пароль';
    }

    responseJson($json);
}

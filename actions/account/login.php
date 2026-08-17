<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

if (isset($_POST['email'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    if (!$data['email']) {
        $json['error']['email'] = 'Укажите email';
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
        // email не найден
        $json['error']['auth'] = 'Неправильный логин или пароль';
        responseJson($json);
    }

    // проверка активности
    if ($user['active'] == 0) {
        $json['error']['email_resend'] = true;
        responseJson($json);
    }

    // проверка пароля
    if (!password_verify($data['password'], $user['password'])) {
        $json['error']['auth'] = 'Неправильный логин или пароль';
        responseJson($json);
    }

    // проверка удалённого аккаунта (только если пароль правильный)
    if ($user['deleted'] == 1 && !is_null($user['deleted_at'])) {
        $deletedTs = strtotime($user['deleted_at']);
        if (time() - $deletedTs > 30 * 24 * 60 * 60) {
            $json['error']['auth'] = 'Неправильный логин или пароль';
            responseJson($json);
        } else {
            recover_user($user['id']); // восстановление, если прошло <= 30 дней
        }
    }

    // логиним пользователя
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name']
    ];
    $json['success'] = '/cabinet';
    responseJson($json);
}

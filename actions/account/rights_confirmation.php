<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

if (isset($_POST['password'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    if (strlen($data['password']) < 6) $json['error']['password'] = 'Минимальная длинна пароля 6 символов';

    if (empty($data['email'])) $json['error']['email'] = 'Email не указан';

    if (isset($json['error'])) responseJson($json);

    $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

    $data['specialist_id'] = clean_elem_form($_POST['specialist_id']);

    $data['new_hash'] = md5(bin2hex(random_bytes(10)));

    if (confirmation_user($data)) {
        $json['success'] = 'Ваш аккаунт успешно подтверждён';
    } else {
        $json['error']['recovery'] = 'Восстановление пароля временно недоступно';
    }

    responseJson($json);
}
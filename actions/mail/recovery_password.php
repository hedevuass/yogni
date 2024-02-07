<?php

include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/send_mail.php';

if (isset($_POST['email'])) {
    $email = clean_elem_form($_POST['email']);

    if (!$email) $json['error']['email'] = 'Укажите email';

    if ($email) {
        if (!filter_var($email, FILTER_SANITIZE_EMAIL)) $json['error']['email'] = 'Некорректный email';
        if (!$specialist = get_user_by_email($email)) $json['error']['email'] = 'Email не зарегистрирован';
    }

    if (isset($json['error'])) responseJson($json);

    $title = 'ЮРФАК - восстановление пароля';
    $body = 'Для восстановления пароля перейдите по ссылке: ' . ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/action?recovery&hash=' . $specialist['hash'] . '&id=' . $specialist['id'];

    if (send_mail_smtp($email, $title, $body)) {
        $json['success'] = 'Для восстановления пароля мы направили на указанную вами электронную почту письмо с инструкциями.';
    }

    responseJson($json);
}
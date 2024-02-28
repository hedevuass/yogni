<?php

include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/send_mail.php';

if (isset($_POST['email'])) {
    $email = clean_elem_form($_POST['email']);
    $id = clean_elem_form($_POST['user_id']);

    if (!$email) $json['error']['email'] = 'Укажите email';

    if ($email) {
        if (!filter_var($email, FILTER_SANITIZE_EMAIL)) $json['error']['email'] = 'Некорректный email';
    }

    if (isset($json['error'])) responseJson($json);
    $user_id = $_POST['user_id'];

    $title = 'ЮРФАК - подтверждение прав';
    $body = 'Для подтверждения прав перейдите по ссылке: ' . ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/action?confirmation&id=' . urlencode($id) . '&email=' . urlencode($email);

    if (send_mail_smtp($email, $title, $body)) {
        $json['success'] = 'Для подтверждения прав мы направили на указанную вами электронную почту письмо с инструкциями.';
    }

    responseJson($json);
}
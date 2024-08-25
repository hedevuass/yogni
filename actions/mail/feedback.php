<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/send_mail.php';

if (isset($_POST['message']) && isset($_POST['email']) && isset($_POST['message_type']) && isset($_POST['user_name'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }
    $user_email = $data['email'];
    $user_name = $data['user_name'];
    $user_message = $data['message'];
    $message_type = $data['message_type'];

    if (isset($json['error'])) responseJson($json);

    $title = 'ЮРФАК - обратная связь';
    $body = <<<EOF
        <div>
            <b>Имя:</b> {$user_name}
        </div>
        <div>
            <b>Почта:</b> <a href="mailto:{$user_email}">{$user_email}</a>
        </div>
        <div>
            <b>Тип сообщения:</b> {$message_type}
        </div>
        <div>
            <b>Сообщение:</b> {$user_message}
        </div>
    EOF;

    if (send_mail_smtp(EMAIL_FEEDBACK, $title, $body)) {
        $json['success'] = true;
    } else {
        $json['fatal_error'] = 'Сбой при отправке данных. Повторите попытку позже.';
    }

    responseJson($json);
}
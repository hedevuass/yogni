<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/send_mail.php';

if (isset($_POST['contact'])) {
    $contact = clean_elem_form($_POST['contact']);
    $message = clean_elem_form($_POST['message']);

    if (!$contact) $json['error']['contact'] = 'Укажите номер телефона или email';
    if (!$message) $json['error']['message'] = 'Введите сообщение';

    if (isset($json['error'])) responseJson($json);

    $title = 'ЮРФАК - обратная связь';
    $body = <<<EOF
        <div>
            <b>Связаться с:</b> {$contact}
        </div>
        <div>
            <b>Сообщение:</b> {$message}
        </div>
    EOF;

    if (send_mail_smtp(EMAIL_FEEDBACK, $title, $body)) {
        $json['success'] = 'Заявка на обратную связь принята';
    }

    responseJson($json);
}
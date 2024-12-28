<?php

include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/send_mail.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $contactInfo = htmlspecialchars($_POST['contactInfo']);
    $message = htmlspecialchars($_POST['message']);

    if (empty($contactInfo) || empty($message)) {
        http_response_code(400);
        echo json_encode(['error' => 'Заполните все поля.']);
        exit;
    }

    // email или телефон
    if (filter_var($contactInfo, FILTER_VALIDATE_EMAIL)) {
        $contactDetail = "Email: $contactInfo";
    } else {
        $contactDetail = "Телефон: $contactInfo";
    }

    $body = <<<EOD
Детали запроса:<br><br>

$contactDetail
<br>Сообщение: $message
EOD;

    // Заголовок письма
    $subject = 'Запрос клиента с сайта urfaq.ru';

    // Отправляем письмо
    if (send_mail_smtp($email, $subject, $body)) {
        echo json_encode(['success' => 'Сообщение успешно отправлено.']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Не удалось отправить сообщение.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Неверный метод запроса.']);
}
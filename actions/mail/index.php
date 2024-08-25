<?php
use PHPMailer\PHPMailer\PHPMailer;

// Подключаем файлы библиотеки PHPMailer
require 'phpMailer/PHPMailer.php';
require 'phpMailer/SMTP.php';
require 'phpMailer/Exception.php';

// Создаем экземпляр объекта PHPMailer
$mail = new PHPMailer();

try {
    // Настройки SMTP сервера
    $mail->isSMTP();
    $mail->CharSet = 'UTF-8';
    $mail->Host = 'smtp.beget.com';  // Замените на адрес вашего SMTP сервера
    $mail->SMTPAuth = true;
    $mail->Username = 'testnoreply@urfaq.ru';  // Замените на ваше имя пользователя SMTP сервера
    $mail->Password = '&yMUy77w';  // Замените на ваш пароль SMTP сервера
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Используйте 'tls' или 'ssl' в зависимости от настроек вашего SMTP сервера
    $mail->Port = 587;  // Замените на порт вашего SMTP сервера

    // Настройки отправителя и получателя
    $mail->setFrom('noreply@dev-a-tech.com', 'No Reply');  // Замените на ваш адрес электронной почты и имя отправителя
    $mail->addAddress('nikitamin07@gmail.com', 'Lord Kabalius');  // Замените на адрес электронной почты и имя получателя

    // Содержимое письма
    $mail->isHTML(false);  // Установите значение true, если ваше сообщение содержит HTML-код
    $mail->Subject = 'Тестовое сообщение )';  // Заголовок письма
    $mail->Body = 'Привет, это тестовое сообщение с сервера!';  // Текст письма

    // Отправка письма
    if ($mail->send()) {
        echo 'Письмо успешно отправлено!';
    } else {
        echo 'Ошибка при отправке письма: ' . $mail->ErrorInfo;
    }
} catch (Exception $e) {
    echo 'Произошла ошибка: ' . $e->getMessage();
}

?>
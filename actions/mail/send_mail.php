<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/phpMailer/src/Exception.php';
require $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/phpMailer/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/phpMailer/src/SMTP.php';

function send_mail_smtp($email, $title, $body)
{
	$mail = new PHPMailer(true);
	$mail->isSMTP();
	$mail->SMTPDebug  = 0;
	$mail->CharSet    = 'UTF-8';
	$mail->Host 	  = 'smtp.beget.com';  // Замените на адрес вашего SMTP сервера
    $mail->SMTPAuth   = true;
    $mail->Username   = 'test-noreply@urfaq.ru';  // Замените на ваше имя пользователя SMTP сервера
	$mail->Password   = '&yMUy77w';
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
	$mail->Port       = 465;
	$mail->setFrom('test-noreply@urfaq.ru', 'ЮРФАК');
	$mail->addAddress($email);
	$mail->isHTML(true);
	$mail->Subject = $title;

	$mail->Body = '<html>
					<head>
						<title>' . $title . '</title>
					</head>
					<body>
						<p>' . $body . '</p>
					</body>
				</html>';


	return ($mail->send()) ? true : false;
}

function send_mail_server($email, $title, $body)
{
	$mail = new PHPMailer();
	$mail->CharSet = 'UTF-8';
	$mail->setFrom('noreply@dev-a-tech.com', 'ЮРФАК');
	$mail->addAddress($email);
	$mail->Subject = $title;
	$mail->msgHTML("<html><body>" . $body . "</html></body>");

	return ($mail->send()) ? true : false;
}

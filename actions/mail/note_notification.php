<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/send_mail.php';

$recipient_by_type = [
    'post' => ['hedevuass@gmail.com', 'ignat94140@gmail.com'],
    'photo' => ['hedevuass@gmail.com'],
    'banner' => ['hedevuass@gmail.com'],
];

// Данные из POST
$type = $_POST['type'] ?? null;
$date = $_POST['date'] ?? date('d.m.Y');
$period = $_POST['period'] ?? null;
$name = $_POST['name'] ?? null;
$title = $_POST['title'] ?? 'Без заголовка';
$link = $_POST['link'] ?? '';
$text = $_POST['text'] ?? '';
$user_email = $_POST['user_email'] ?? '';
$phone = $_POST['phone'] ?? '';
$employees = $_POST['employees'] ?? '';

// Проверка типа
if (!$type || !isset($recipient_by_type[$type])) {
    responseJson(['error' => 'Тип сообщения не распознан или отсутствует.']);
}

$recipients = $recipient_by_type[$type] ?? [];

// Собираем HTML письма
switch ($type) {
    case 'post':
        $email_subject = 'Запрос на размещение поста в блоге';
        break;
    case 'photo':
        $email_subject = 'Запрос на фотосъемку';
        break;
    case 'banner':
        $email_subject = 'Запрос на размещение баннера';
        break;
    default:
        $email_subject = 'Новое уведомление';
        break;
}

ob_start();
?>
<html>
<?php if ($type === 'post'): ?>

    <head>
        <meta charset="UTF-8">
        <title>Запрос на размещение поста в блоге</title>
    </head>

    <body style="font-family: Arial, sans-serif; line-height: 1.6;"></body>
    <p><strong>Перейти в административную панель:</strong> <a href="https://urfaq.ru/admin-dt/posts"
            target="_blank">https://urfaq.ru/admin-dt/posts</a>
    <p><strong>Дата:</strong> <?= $date ?></p>
    <p><strong>Имя автора:</strong> <?= htmlspecialchars($name) ?></p>
    <p><strong>Заголовок:</strong> <?= htmlspecialchars($title) ?></p>
    </p>
    <p><strong>Текст поста:</strong><br><?= nl2br(htmlspecialchars($text)) ?></p>

<?php elseif ($type === 'photo'): ?>

    <head>
        <meta charset="UTF-8">
        <title>Запрос на фотосъемку</title>
    </head>

    <body style="font-family: Arial, sans-serif; line-height: 1.6;"></body>
    <p><strong>Перейти в административную панель:</strong> <a href="https://urfaq.ru/admin-dt/photo"
            target="_blank">https://urfaq.ru/admin-dt/photo</a>
    <p><strong>Дата:</strong> <?= $date ?></p>
    <p><strong>Имя:</strong> <?= htmlspecialchars($name) ?></p>
    <p><strong>Почта:</strong> <?= htmlspecialchars($user_email) ?></p>
    <p><strong>Телефон:</strong> <?= htmlspecialchars($phone) ?></p>
    <p><strong>Вид съёмки:</strong> <?= htmlspecialchars($title) ?></p>
    <?php if ((int) $employees > 0): ?>
        <p><strong>Съёмка сотрудников:</strong> <?= htmlspecialchars($employees) ?></p>
    <?php endif; ?>

<?php elseif ($type === 'banner'): ?>

    <head>
        <meta charset="UTF-8">
        <title>Запрос на размещение баннера</title>
    </head>

    <body style="font-family: Arial, sans-serif; line-height: 1.6;"></body>
    <p><strong>Перейти в административную панель:</strong> <a href="https://urfaq.ru/admin-dt/banners"
            target="_blank">https://urfaq.ru/admin-dt/banners</a>
    <p><strong>Дата:</strong> <?= $date ?></p>
    <p><strong>Имя:</strong> <?= htmlspecialchars($name) ?></p>
    <p><strong>Текст:</strong><br><?= nl2br(htmlspecialchars($text)) ?></p>
    <p><strong>Период размещения:</strong> <?= htmlspecialchars($period) ?></p>
    <p><strong>Вид баннера:</strong> <?= htmlspecialchars($title) ?></p>
<?php endif; ?>
</body>

</html>
<?php
$body = ob_get_clean();

// Отправка
$all_success = true;
foreach ($recipients as $recipient_email) {
    $success = send_mail_notification($recipient_email, $email_subject, $body);
    if (!$success) {
        $all_success = false;
    }
}

if ($all_success) {
    responseJson(['success' => 'Письма отправлены.']);
} else {
    responseJson(['error' => 'Ошибка при отправке одного или нескольких писем.']);
}
<?php

include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/send_mail.php';
require_once __DIR__ . '/../../libs/securimage/securimage.php';

$recipient_by_type = [
    'post' => ['yogoor.ru@mail.ru'],
    'photo' => ['yogoor.ru@mail.ru'],
    'banner' => ['yogoor.ru@mail.ru'],
    'request' => ['yogoor.ru@mail.ru'],
    'support' => ['yogoor.ru@mail.ru'],
    'support_user' => ['yogoor.ru@mail.ru'],
];

// Данные из POST
$type = $_POST['type'] ?? null;
$date = $_POST['date'] ?? date('d.m.Y');
$period = $_POST['period'] ?? null;
$name = $_POST['name'] ?? null;
$title = $_POST['title'] ?? 'Без заголовка';
$link = $_POST['link'] ?? '';
$text = $_POST['text'] ?? '';
$user_email = $_POST['user_email'] ?? $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$employees = $_POST['employees'] ?? '';
$specialist_name  = $_POST['specialist_name'] ?? '';
$place = $_POST['place'] ?? '';
$fio_or_company = $_POST['fio_or_company'] ?? '';
$message = $_POST['message'] ?? '';

// Проверка типа
if (!$type || !isset($recipient_by_type[$type])) {
    responseJson(['error' => 'Тип сообщения не распознан или отсутствует.']);
}

if ($type === 'support') {
    $json = [];

    if (!trim($fio_or_company)) {
        $json['error']['fio_or_company'] = 'Укажите ФИО или название компании';
    }
    if (!trim($phone)) {
        $json['error']['phone'] = 'Укажите контактный номер телефона';
    }
    if (!trim($user_email)) {
        $json['error']['email'] = 'Укажите адрес электронной почты';
    } elseif (!filter_var(trim($user_email), FILTER_VALIDATE_EMAIL)) {
        $json['error']['email'] = 'Некорректный email';
    }
    if (!trim($message)) {
        $json['error']['message'] = 'Введите сообщение';
    }

    $forbidden_words = getForbiddenWords();
    $forbiddenWordsFound = [];
    $fields_to_check = [
        'fio_or_company' => $fio_or_company,
        'phone' => $phone,
        'email' => $user_email,
        'message' => $message,
    ];

    foreach ($fields_to_check as $key => $value) {
        if (empty(trim($value))) {
            continue;
        }
        $foundItems = containsForbiddenWords($value, $forbidden_words);
        if ($foundItems) {
            foreach ($foundItems as $item) {
                $forbiddenWordsFound[] = $item['value'];
            }
        }
    }

    if (!empty($forbiddenWordsFound)) {
        $json['error']['forbidden_words'] = array_unique($forbiddenWordsFound);
    }

    if (!empty($json['error'])) {
        responseJson($json);
    }
}

if ($type === 'support_user') {
    $json = [];

    $fields_required = [
        'fio_or_company' => 'Укажите ваше имя',
        'phone' => 'Укажите контактный данные',
        'email' => $_POST['email'] ?? '', // подставим ниже правильно
        'message' => 'Введите сообщение',
    ];

    if (!trim($fio_or_company)) {
        $json['error']['fio_or_company'] = 'Укажите ваше имя';
    }
    if (!trim($phone)) {
        $json['error']['phone'] = 'Укажите контактный данные';
    }
    if (!trim($user_email)) {
        $json['error']['email'] = 'Укажите адрес электронной почты';
    }
    if (!trim($message)) {
        $json['error']['message'] = 'Введите сообщение';
    }

    if (!trim($user_email)) {
        $json['error']['email'] = 'Укажите адрес электронной почты';
    } elseif (!filter_var(trim($user_email), FILTER_VALIDATE_EMAIL)) {
        $json['error']['email'] = 'Некорректный email';
    }

    $forbidden_words = getForbiddenWords();
    $fields_to_check = [
        'fio_or_company' => $fio_or_company,
        'phone' => $phone,
        'email' => $user_email,
        'message' => $message,
    ];

    foreach ($fields_to_check as $key => $value) {
        if (empty(trim($value))) {
            continue; // уже есть ошибка "поле пустое", не проверяем слова в пустой строке
        }
        $foundItems = containsForbiddenWords($value, $forbidden_words);
        if ($foundItems) {
            $elements = [];
            $words = [];
            foreach ($foundItems as $item) {
                if ($item['type'] === 'element') {
                    $elements[] = '"' . $item['value'] . '"';
                } elseif ($item['type'] === 'word') {
                    $words[] = '"' . $item['value'] . '"';
                }
            }
            if (!empty($elements) && !empty($words)) {
                $allItems = array_unique(array_merge($elements, $words));
                $json['error'][$key] = 'Обнаружены запрещённые слова и элементы: ' . implode(', ', $allItems) . '.';
            } elseif (!empty($elements)) {
                $json['error'][$key] = 'Обнаружены запрещённые элементы: ' . implode(', ', array_unique($elements)) . '.';
            } elseif (!empty($words)) {
                $json['error'][$key] = 'Обнаружены запрещённые слова: ' . implode(', ', array_unique($words)) . '.';
            }
        }
    }

    $captchaCode = $_POST['captcha_code'] ?? '';
    $captchaId = $_POST['captcha_id'] ?? null;

    $securimage = new Securimage();
    try {
        if (!$captchaCode) {
            $json['error']['captcha_error'] = 'Введите цифры с картинки';
        } elseif (!$securimage->check($captchaCode, $captchaId)) {
            $json['error']['captcha_error'] = 'Неправильный код каптчи. Попробуйте снова.';
        }
    } catch (\Throwable $e) {
        error_log('Securimage error: ' . $e->getMessage());
        $json['error']['captcha_error'] = 'Ошибка проверки каптчи. Попробуйте снова.';
    }

    if (!empty($json['error'])) {
        responseJson($json);
    }
}

$recipients = $recipient_by_type[$type] ?? [];

// Собираем HTML письма
switch ($type) {
    case 'post':
        $email_subject = 'Запрос на размещение поста в блоге';
        break;
    case 'request':
        $email_subject = 'Клиентский запрос';
        break;
    case 'photo':
        $email_subject = 'Запрос на фотосъемку';
        break;
    case 'banner':
        $email_subject = 'Запрос на размещение баннера';
        break;
    case 'support':
        $email_subject = 'Обращение в поддержку из личного кабинета';
        break;
    case 'support_user':
        $email_subject = 'Обращение в поддержку из пользовательской части';
        break;
    default:
        $email_subject = 'Новое уведомление';
        break;
}

ob_start();
?>
<html lang="ru">
<?php if ($type === 'post'): ?>

    <head>
        <meta charset="UTF-8">
        <title>Запрос на размещение поста в блоге</title>
    </head>

    <body style="font-family: Arial, sans-serif; line-height: 1.6;"></body>

    <p><strong>Дата:</strong> <?= $date ?></p>
    <p><strong>Имя автора:</strong> <?= htmlspecialchars($name) ?></p>
    <p><strong>Заголовок:</strong> <?= htmlspecialchars($title) ?></p>
    </p>
    <p><strong>Текст поста:</strong><br><?= nl2br(htmlspecialchars($text)) ?></p>

<?php elseif ($type === 'request'): ?>

    <head>
        <meta charset="UTF-8">
        <title>Клиентский запрос</title>
    </head>

    <body style="font-family: Arial, sans-serif; line-height: 1.6;"></body>

    <p><strong>Дата заявки:</strong> <?= $date ?></p>
    <p><strong>Имя в заявке:</strong> <?= htmlspecialchars($name) ?></p>
    <p><strong>Наименование специалиста:</strong> <?= htmlspecialchars($specialist_name) ?></p>

<?php elseif ($type === 'photo'): ?>

    <head>
        <meta charset="UTF-8">
        <title>Запрос на фотосъемку</title>
    </head>

    <body style="font-family: Arial, sans-serif; line-height: 1.6;"></body>

    <p><strong>Дата:</strong> <?= $date ?></p>
    <p><strong>Имя:</strong> <?= htmlspecialchars($name) ?></p>
    <p><strong>Почта:</strong> <?= htmlspecialchars($user_email) ?></p>
    <p><strong>Телефон:</strong> +7 <?= htmlspecialchars($phone) ?></p>
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

    <p><strong>Дата:</strong> <?= $date ?></p>
    <p><strong>Имя:</strong> <?= htmlspecialchars($name) ?></p>
    <p><strong>Текст:</strong><br><?= nl2br(htmlspecialchars($text)) ?></p>
    <p><strong>Период размещения:</strong> <?= htmlspecialchars($period) ?></p>
    <p><strong>Вид баннера:</strong> <?= htmlspecialchars($title) ?></p>

<?php elseif ($type === 'support'): ?>

    <head>
        <meta charset="UTF-8">
        <title>Обращение в поддержку из личного кабинета</title>
    </head>

    <body style="font-family: Arial, sans-serif; line-height: 1.6;"></body>

    <p><strong>Дата:</strong> <?= htmlspecialchars($date) ?></p>
    <p><strong>Место отправки:</strong> <?= htmlspecialchars($place) ?></p>
    <p><strong>ФИО или название компании:</strong> <?= htmlspecialchars($fio_or_company) ?></p>
    <p><strong>Телефон:</strong> +7 <?= htmlspecialchars($phone) ?></p>
    <p><strong>Почта:</strong> <?= htmlspecialchars($user_email) ?></p>
    <p><strong>Ссылка на страницу:</strong> <?= htmlspecialchars($link) ?></p>
    <p><strong>Сообщение:</strong><br><?= nl2br(htmlspecialchars($message)) ?></p>

<?php elseif ($type === 'support_user'): ?>
    <head>
        <meta charset="UTF-8">
        <title>Обращение в поддержку из пользовательской части</title>
    </head>
    <body style="font-family: Arial, sans-serif; line-height: 1.6;"></body>
    <p><strong>Дата:</strong> <?= htmlspecialchars($date) ?></p>
    <p><strong>Место отправки:</strong> <?= htmlspecialchars($place) ?></p>
    <p><strong>ФИО или название компании:</strong> <?= htmlspecialchars($fio_or_company) ?></p>
    <p><strong>Телефон:</strong> <?= htmlspecialchars($phone) ?></p>
    <p><strong>Почта:</strong> <?= htmlspecialchars($user_email) ?></p>
    <p><strong>Сообщение:</strong><br><?= nl2br(htmlspecialchars($message)) ?></p>

<?php endif; ?>
</body>

</html>
<?php
$body = ob_get_clean();

// Отправка
$all_success = true;
foreach ($recipients as $recipient_email) {
    $success = send_mail_notification($recipient_email, $email_subject, $body);
    error_log("Send mail to $recipient_email => " . ($success ? "OK" : "FAIL"));
    if (!$success) {
        $all_success = false;
    }
}

ob_end_clean();

if ($all_success) {
    responseJson(['success' => 'Письма отправлены.']);
} else {
    responseJson(['error' => 'Ошибка при отправке одного или нескольких писем.']);
}

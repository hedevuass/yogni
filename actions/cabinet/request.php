<?php

include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/send_mail.php';
require_once __DIR__ . '/../../libs/securimage/securimage.php';

$forbidden_words = getForbiddenWords();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $securimage = new Securimage();

    $fields_to_check = ['name', 'contactInfo', 'message'];

    $errors = [];

    foreach ($_POST as $key => $value) {
        if (in_array($key, $fields_to_check)) {
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
                    $errors[$key][] = 'Обнаружены запрещённые слова и элементы: ' . implode(', ', $allItems) . '.';
                } elseif (!empty($elements)) {
                    $uniqueElements = array_unique($elements);
                    $errors[$key][] = 'Обнаружены запрещённые элементы: ' . implode(', ', $uniqueElements) . '.';
                } elseif (!empty($words)) {
                    $uniqueWords = array_unique($words);
                    $errors[$key][] = 'Обнаружены запрещённые слова: ' . implode(', ', $uniqueWords) . '.';
                }
            }
        }

        $data[$key] = clean_elem_form($value);
    }

    $captchaCode = $_POST['captcha_code'] ?? '';
    $captchaId   = $_POST['captcha_id'] ?? null;

    // Проверка капчи
    if (!$captchaCode) {
        $errors['captcha_error'] = 'Введите цифры с картинки';
    } elseif (!$captchaId || !$securimage->check($captchaCode, $captchaId)) {
        $errors['captcha_error'] = 'Неправильный код каптчи. Попробуйте снова.';
    }

    // Проверка остальных полей
    $email       = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $name        = htmlspecialchars($_POST['name'] ?? '');
    $contactInfo = htmlspecialchars($_POST['contactInfo'] ?? '');
    $message     = htmlspecialchars($_POST['message'] ?? '');
    $specialist_id = htmlspecialchars($_POST['specialist_id'] ?? '');

    $specialist = get_specialist($specialist_id);
    if ($specialist['company']) {
        $specialist_name = $specialist['company'];
    } else {
        $specialist_name = $specialist['name'] . ' ' . $specialist['lastname'];
    }

    if (empty($name)) {
        $errors['name'] = 'Укажите ваше имя';
    }

    if (empty($email)) {
        $errors['email'] = 'Укажите email';
    }

    if (empty($contactInfo)) {
        $errors['contactInfo'] = 'Укажите контактные данные';
    }

    if (empty($message)) {
        /* $errors['message'] = 'Введите сообщение'; */
    }

    // Если есть ошибки, возвращаем их все сразу
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'error' => $errors]);
        exit;
    }

    $result = add_user_request(
        (int) $_POST['specialist_id'],
        $_POST['name'],
        $_POST['contactInfo'],
        $_POST['message']
    );

    $notify_data = [
        'type'  => 'request',
        'date'  => date('d.m.Y'),
        'name'  => $name,
        'specialist_name' => $specialist_name,
    ];
    
    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($notify_data),
            'timeout' => 5 // сек
        ]
    ];
    
    $context  = stream_context_create($opts);
    $response = file_get_contents($_SERVER['HTTP_ORIGIN'] . '/actions/mail/note_notification.php', false, $context);

    if ($result) {
        $json['success'] = 'Мы направили на указанную вами электронную почту письмо с инструкциями.';
    } else {
        $json['error'] = 'Ошибка при отправке письма. Повторите попытку позже или обратитесь в поддержку.';
    }

    responseJson($json);
}

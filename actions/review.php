<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/send_mail.php';
require_once __DIR__ . '/../libs/securimage/securimage.php';

$forbidden_words = getForbiddenWords();

if (isset($_POST['action']) && $_POST['action'] === 'verify_code') {
    $review_id = (int)$_POST['review_id'];
    $user_code = trim($_POST['verification_code']);

    // Проверяем код в базе данных
    // Тебе нужно будет создать простую функцию check_review_code($id, $code)
    if (check_review_code($review_id, $user_code)) {
        // Если код верный, активируем отзыв (ставим is_active = 1)
        activate_review($review_id);
        $json['success'] = 'Спасибо! Ваш отзыв успешно подтвержден и опубликован.';
    } else {
        $json['error']['verification_code'] = 'Проверочный код введен неверно';
    }
    responseJson($json);
}

if (isset($_POST['specialist'])) {
    $fields_to_check = ['name', 'contact', 'review'];

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
                    $json['error'][$key][] = 'Обнаружены запрещённые слова и элементы: ' . implode(', ', $allItems) . '.';
                } elseif (!empty($elements)) {
                    $uniqueElements = array_unique($elements);
                    $json['error'][$key][] = 'Обнаружены запрещённые элементы: ' . implode(', ', $uniqueElements) . '.';
                } elseif (!empty($words)) {
                    $uniqueWords = array_unique($words);
                    $json['error'][$key][] = 'Обнаружены запрещённые слова: ' . implode(', ', $uniqueWords) . '.';
                }
            }
        }

        $data[$key] = clean_elem_form($value);
    }

    $captchaCode = $_POST['captcha_code_review'] ?? '';
    $captchaId = $_POST['captcha_id_review'] ?? null;

    if (!$data['contact']) $json['error']['contact'] = 'Укажите email';
    if (!empty($data['contact'])) {
        if (!filter_var($data['contact'], FILTER_VALIDATE_EMAIL)) {
            $json['error']['contact'] = 'Некорректный email';
        }
    }
    if (!$data['name']) $json['error']['name'] = 'Укажите ваше имя';
    if (!$data['rating']) $json['error']['rating'] = 'Поставьте оценку';
    if (!$data['review']) {
        $json['error']['review'] = 'Введите текст отзыва';
    }

    // Проверка капчи
    $securimage = new Securimage();
    if (!$captchaCode) {
        $json['error']['captcha_error'] = 'Введите цифры с картинки';
    } elseif (!$securimage->check($captchaCode, $captchaId)) {
        $json['error']['captcha_error'] = 'Неправильный код каптчи. Попробуйте снова.';
    }

    if (isset($json['error'])) responseJson($json);

    $code = (string)rand(10000, 99999);
    $data['v_code'] = $code;

    // Записываем в базу (функция set_review должна возвращать ID или "duplicate")
    $review_result = set_review($data);

    if ($review_result === "duplicate") {
        $json['error']['fatal_error'] = 'Вы уже оставляли отзыв об этом специалисте';
        responseJson($json);
    }

    if ($review_result) {
        $title = 'Ваш код: ' . $code;
        $body = "
            <p>Ваш код: <b>$code</b></p>
            <p>Для публикации отзыва введите одноразовый код в поле попапа \"Подтверждение почты\" и нажмите кнопку \"Подтвердить\". При случайном закрытии попапа запросите код повторно.</p>
            <p>Если Вы не пытались опубликовать отзыв на сайте ЮРФАК.РУ, проигнорируйте его.</p>
            <p>С уважением, команда ЮРФАК.РУ.</p>
        ";

        if (send_mail_smtp($data['contact'], $title, $body)) {
            $json['step'] = 'verify';
            $json['review_id'] = $review_result; // ID из базы для JS
            $json['message'] = 'Код отправлен на вашу почту';
        } else {
            $json['error']['fatal_error'] = 'Не удалось отправить письмо, но отзыв сохранен как черновик.';
        }
    } else {
        $json['error']['fatal_error'] = 'Ошибка при сохранении отзыва.';
    }

    responseJson($json);

    /* if ($review_status === "duplicate") {
        $json['error']['fatal_error'] = 'Вы уже оставляли отзыв для этого специалиста.';
    } elseif ($review_status) {
        $json['success'] = 'Спасибо за отзыв!';
    } else {
        $json['error']['fatal_error'] = 'Добавление отзывов временно не работает';
    }

    responseJson($json); */
}

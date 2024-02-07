<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

if (isset($_POST['specialist'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    if (!$data['contact']) $json['error']['contact'] = 'Укажите номер телефона или email';
    if (!$data['name']) $json['error']['name'] = 'Укажите ваше имя';
    if (!$data['rating']) $json['error']['rating'] = 'Поставьте оценку';
    if (!$data['review']) $json['error']['review'] = 'Введите текст отзыва';

    if (isset($json['error'])) responseJson($json);

    if (set_review($data)) {
        $json['success'] = 'Спасибо за отзыв!';
    } else {
        $json['error']['fatal_error'] = 'Добавление отзывов временно не работает';
    }

    responseJson($json);
}
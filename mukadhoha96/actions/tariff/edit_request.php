<?php
// actions/tariff/edit_request.php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    foreach (['description', 'price', 'promo_description'] as $name) {
        $data[$name] = strip_tags(trim($_POST[$name] ?? ''), '<span>');
    }

    $fields = [
        'title'             => ['Заголовок', 255],
        'description'       => ['Описание', 65535],
        'price'             => ['Цена', 100],
        'promo_description' => ['Описание акции', 255],
        'comment_text'      => ['Текст комментария', 255],
    ];

    $errors = [];
    foreach ($fields as $name => [$label, $maxLength]) {
        $value = trim($data[$name] ?? '');

        if ($value === '') {
            $errors[$name] = "Заполните поле «{$label}»";
        } elseif (mb_strlen($value) > $maxLength) {
            $errors[$name] = "Поле «{$label}» не должно быть длиннее {$maxLength} символов";
        }
    }

    if ($errors) {
        $json['error'] = $errors;
        responseJson($json);
    }

    try {
        update_request_promo($data);
        $json['success'] = 'Заявки обновлены';
    } catch (Exception $e) {
        $json['fatal'] = 'Редактирование заявок. Возникла ошибка: ' . $e->getMessage();
    }

    responseJson($json);
}
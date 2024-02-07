<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    if (!isset($data['price']) || !$data['price']) {
        $json['error']['title'] = 'Укажите цену';
        responseJson($json);
    }

    $function = 'update_' . $data['type'];

    try {
        $function($data);
        $json['success'] = 'Цена обновлена';
    } catch (Exception $e) {
        $json['fatal'] = 'Редактирование тарифов или услуг. Возникла ошибка: ' . $e->getMessage();
    }

    responseJson($json);
}
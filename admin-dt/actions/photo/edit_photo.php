<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$response = [];

if (isset($_POST['id'])) {
    $data = [];

    foreach ($_POST as $key => $value) {
        if ($key === 'content') {
            $data[$key] = $value;
        } else {
            $data[$key] = clean_elem_form($value);
        }
    }

    $response['debug'] = [
        'received_data' => $data
    ];

    if (!isset($data['title']) || !$data['title']) {
        $response['error']['title'] = 'Укажите название';
    }

    if (isset($response['error'])) {
        responseJson($response);
        exit;
    }

    try {
        update_pro_photo($data);
        $response['success'] = 'Тариф обновлён';
    } catch (Exception $e) {
        $response['fatal'] = 'Редактирование тарифа недоступно. Ошибка: ' . $e->getMessage();
    }

    responseJson($response);
}

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    if (!isset($data['title']) || !$data['title']) {
        $json['error']['title'] = 'Укажите название блока';
        responseJson($json);
    }

    $content = [];

    foreach ($data['services'] as $key => $service) {
        $content[++$key] = $service;
    }

    $data['content'] = json_encode($content);
//    var_dump($data);die();
    try {
        update_block($data);
        $json['success'] = 'Блок обновлён';
    } catch (Exception $e) {
        $json['fatal'] = 'Редактирование блоков временно недоступно. Возникла ошибка: ' . $e->getMessage();
    }

    responseJson($json);
}
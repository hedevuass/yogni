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

    // Преобразуем массив options обратно в JSON, сохраняем изображение и новый текст
    $options = [];
    foreach ($data['options'] as $key => $option) {
        $options[$key] = [
            'image' => $option['image'], // Сохраняем старое изображение
            'text' => $option['text'],   // Обновляем текст
        ];
    }
    
    $data['options'] = json_encode($options, JSON_UNESCAPED_UNICODE);

    $function = 'update_' . $data['type'];

    try {
        if (isset($data['id']) && $data['id'] !== '') {
            $function($data);
        } else {
            add_option($data);
        }
        $json['success'] = 'Тариф обновлен';
    } catch (Exception $e) {
        $json['fatal'] = 'Редактирование тарифов. Возникла ошибка: ' . $e->getMessage();
    }

    responseJson($json);
}


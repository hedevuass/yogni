<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

if (isset($_POST['name'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_blog_title($value);
    }

    foreach ($data['name'] as $key => $name) {
        if (!$name && !$data['price'][$key]) continue;
        else if(!$name && $data['price'][$key]) {
            $json['error']['name['.$key.']'] = 'Укажите название услуги';
        } else if ($name && !$data['price'][$key]) {
            $json['error']['name['.$key.']'] = 'Укажите цену';
        }
    }

    if (isset($json['error'])) responseJson($json);

    $json['success'] = true;
    unset_specialist_services();

    foreach ($data['name'] as $key => $name) {
        // Пропускаем итерацию, если нет имени или цены
        if (!$name || !$data['price'][$key]) continue;
    
        // Удаляем апострофы из цены
        $price = $data['price'][$key] ?? 0;
        if ($price != 0) {
            $price = intval(preg_replace('/[^\d]/', '', $price)); // Удаление апострофа
        }
        
        // Устанавливаем значение для 'of'
        $of = $data['of'][$key] ? 1 : 0;
    
        // Функция для сохранения данных
        set_specialist_service($name, $price, $of);
    }

    responseJson($json);
}
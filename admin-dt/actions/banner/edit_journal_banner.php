<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    $data = [];

    // Очистка и сбор данных из POST
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    // Добавляем данные в ответ для отладки
    $response['debug'] = [
        'received_data' => $data
    ];

    // Проверки обязательных полей
    if (!isset($data['title']) || !$data['title']) {
        $response['error']['title'] = 'Укажите название';
    }

    if (!isset($data['alias']) || !$data['alias']) {
        $response['error']['alias'] = 'Укажите ссылку';
    }

    if (!isset($data['content']) || !$data['content']) {
        $response['error']['content'] = 'Укажите контент';
    }

    if (isset($data['id'])) {
        $banner = get_journal_banner($data['id']);
        $existingImage = $banner['image'];
    }

    // Обработка загрузки изображения
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/';
        $fileName = basename($_FILES['photo']['name']);
        $filePath = $uploadDir . $fileName;
    
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
            $data['photo'] = $fileName;
        } else {
            $response['error']['photo'] = 'Ошибка при загрузке изображения';
        }
    } else {
        if (isset($existingImage)) {
            $data['photo'] = $existingImage; 
        }
    }

    if (isset($response['error'])) {
        responseJson($response);
        exit;
    }

    try {
        update_journal_banner($data);
        $response['success'] = 'Баннер успешно обновлён';
    } catch (Exception $e) {
        $response['fatal'] = 'Редактирование временно недоступно. Ошибка: ' . $e->getMessage();
    }

    responseJson($response);
}


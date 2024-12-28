<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$response = []; // Используем $response для ответа

if (isset($_POST['id'])) {
    $data = [];

    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    // Добавляем данные в ответ для отладки
    $response['debug'] = [
        'received_data' => $data
    ];

    if (!isset($data['title']) || !$data['title']) {
        $response['error']['title'] = 'Укажите название';
    }

    if (!isset($data['slug']) || !$data['slug']) {
        $response['error']['slug'] = 'Укажите ссылку';
    }

    if (isset($data['id'])) {
        $service = get_service($data['id']);
        $service_img = $service['img'];
    }
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
        if (isset($service_img)) {
            $data['photo'] = $service_img;
        }
    }

    if (isset($response['error'])) {
        responseJson($response);
        exit;
    }

    try {
        // Обновляем сервис
        update_actual_service($data);
        $response['success'] = 'Сервис обновлён';
    } catch (Exception $e) {
        $response['fatal'] = 'Редактирование временно недоступно. Ошибка: ' . $e->getMessage();
    }

    responseJson($response);
}

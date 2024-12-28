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

    if (empty($data['alias']) && !empty($data['title'])) {
        $data['alias'] = generateAlias($data['title']);
    }

    if ($data['id'] !== '') {
        $service = get_menu_item($data['id']);
        $service_img = $service['img'];
        $service_icon = $service['icon']; // Получаем текущее значение иконки
    }

    // Обработка изображения photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/articles/';
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
        if (isset($data['id']) && $data['id'] !== '') {
            update_journal($data);
            $response['success'] = 'Сервис обновлён';
        } else {
            add_journal($data);
            $response['success'] = 'Сервис добавлен';
        }
    } catch (Exception $e) {
        $response['fatal'] = 'Редактирование временно недоступно. Ошибка: ' . $e->getMessage();
    }

    responseJson($response);
}

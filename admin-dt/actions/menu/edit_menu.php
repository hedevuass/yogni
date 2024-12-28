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

    if ($data['id'] !== '') {
        $service = get_menu_item($data['id']);
        $service_img = $service['img'];
        $service_icon = $service['icon']; // Получаем текущее значение иконки
    }

    // Обработка изображения photo
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

    // Обработка иконки icon
    if (isset($_FILES['icon']) && $_FILES['icon']['error'] === 0) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/'; // Убедитесь, что папка для иконок существует
        $fileName = basename($_FILES['icon']['name']);
        $filePath = $uploadDir . $fileName;
    
        if (move_uploaded_file($_FILES['icon']['tmp_name'], $filePath)) {
            $data['icon'] = $fileName;
        } else {
            $response['error']['icon'] = 'Ошибка при загрузке иконки';
        }
    } else {
        if (isset($service_icon)) {
            $data['icon'] = $service_icon;
        }
    }

    if (isset($response['error'])) {
        responseJson($response);
        exit;
    }

    try {
        if (isset($data['id']) && $data['id'] !== '') {
            update_menu($data);
            $response['success'] = 'Сервис обновлён';
        } else {
            add_service($data);
            $response['success'] = 'Сервис добавлен';
        }
    } catch (Exception $e) {
        $response['fatal'] = 'Редактирование временно недоступно. Ошибка: ' . $e->getMessage();
    }

    responseJson($response);
}

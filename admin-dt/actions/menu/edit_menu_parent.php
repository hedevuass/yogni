<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$response = []; // Используем $response для ответа

if (isset($_POST['slug'])) {  // Проверяем, передан ли slug
    $data = [];

    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    if ($data['id'] !== '') {
        $service = get_menu_parent_item($data['id']);
        $service_icon = $service['icon']; // Получаем текущее значение иконки
    }

    // Обработка иконки icon
    if (isset($_FILES['icon']) && $_FILES['icon']['error'] === 0) {
        // Загружаем новую иконку
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/';
        $fileName = basename($_FILES['icon']['name']);
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['icon']['tmp_name'], $filePath)) {
            $data['icon'] = $fileName;
        } else {
            $response['error']['icon'] = 'Ошибка при загрузке иконки';
        }
    } elseif (!empty($_POST['remove_icon'])) {
        $data['icon'] = '';
    } else {
        if (isset($service_icon)) {
            $data['icon'] = $service_icon;
        }
    }

    try {
        if (isset($data['slug']) && $data['slug'] !== '') {
            update_service_parent($data);
            $response['success'] = 'Сервис обновлён';
        }
    } catch (Exception $e) {
        $response['fatal'] = 'Редактирование временно недоступно. Ошибка: ' . $e->getMessage();  // Обработка ошибок
    }

    responseJson($response);
}

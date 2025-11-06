<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$response = []; // Массив для ответа

if (isset($_POST['id'])) {
    $data = [];

    // Очищаем все полученные поля
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    // Для отладки можно вернуть полученные данные
    $response['debug'] = [
        'received_data' => $data
    ];

    // Проверка обязательного поля title
    if (!isset($data['title']) || !$data['title']) {
        $response['error']['title'] = 'Укажите название';
    }

    if (isset($response['error'])) {
        responseJson($response);
        exit;
    }

    try {
        if ($data['type'] == 'page') {
            // Для альтернативной страницы, относящейся к dt_pages
            if (isset($data['id']) && $data['id'] !== '' && $data['id'] !== 'new') {
                update_alternative_page($data);
                $response['success'] = 'Альтернативная страница (page) обновлена';
            } else {
                add_alternative_page($data);
                $response['success'] = 'Альтернативная страница (page) добавлена';
            }
        } else {
            // По умолчанию работаем с dt_service
            if (isset($data['id']) && $data['id'] !== '' && $data['id'] !== 'new') {
                update_menu($data);
                $response['success'] = 'Альтернативная страница (service) обновлена';
            } else {
                add_service($data);
                $response['success'] = 'Альтернативная страница (service) добавлена';
            }
        }
    } catch (Exception $e) {
        $response['fatal'] = 'Редактирование временно недоступно. Ошибка: ' . $e->getMessage();
    }

    responseJson($response);
}

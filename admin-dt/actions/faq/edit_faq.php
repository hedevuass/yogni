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


    if (isset($response['error'])) {
        responseJson($response);
        exit;
    }

    // Проверка типа и вызов соответствующей функции
    try {
        if ($data['type'] === 'promo') {
            if (isset($data['id']) && $data['id'] !== '') {
                update_faq_promo($data); // Обновление для ПРОМО
            } else {
                add_faq_promo($data);
            }
        } elseif ($data['type'] === 'pro_photo') {
            if (isset($data['id']) && $data['id'] !== '') {
                update_faq_pro_photo($data); // Обновление для ПРО-фото
            } else {
                add_faq_pro_photo($data);
            }
        } else {
            if (isset($data['id']) && $data['id'] !== '') {
                update_faq_page($data); // Обновление для ЛК
            } else {
                add_faq_page($data);
            }
        }
        $response['success'] = 'Вопросы обновлён';
    } catch (Exception $e) {
        $response['fatal'] = 'Редактирование временно недоступно. Ошибка: ' . $e->getMessage();
    }

    responseJson($response);
}

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Очистка и подготовка данных из формы
    foreach ($_POST as $key => $value) {
        if ($key === 'content') {
            $val = is_string($value) ? stripslashes(trim($value)) : '';
            $data[$key] = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
        } else {
            $data[$key] = clean_elem_form($value);
        }
    }

    if (!isset($data['title']) || !$data['title']) {
        $json['error']['title'] = 'Укажите название акции.';
        responseJson($json);
    }

    if (!isset($data['content']) || !$data['content']) {
        $json['error']['content'] = 'Укажите контент акции.';
        responseJson($json);
    }

    try {
        if (!isset($data['id']) || !$data['id']) {
            add_document_o_proekte($data);
            $json['success'] = 'Акция успешно добавлена.';
        } else {
            update_document_o_proekte($data);
            $json['success'] = 'Акция успешно обновлена.';
        }
    } catch (Exception $e) {
        $json['fatal'] = 'Ошибка обработки акции: ' . $e->getMessage();
    }

    responseJson($json);
}

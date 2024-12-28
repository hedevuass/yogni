<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Очистка и подготовка данных из формы
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    if (!isset($data['title']) || !$data['title']) {
        $json['error']['title'] = 'Укажите название документа.';
        responseJson($json);
    }

    if (!isset($data['content']) || !$data['content']) {
        $json['error']['content'] = 'Укажите контент документа.';
        responseJson($json);
    }

    try {
        if (!isset($data['id']) || !$data['id']) {
            add_document_o_proekte($data);
            $json['success'] = 'Документ успешно добавлен.';
        } else {
            update_document_o_proekte($data);
            $json['success'] = 'Документ успешно обновлён.';
        }
    } catch (Exception $e) {
        $json['fatal'] = 'Ошибка обработки документа: ' . $e->getMessage();
    }

    responseJson($json);
}

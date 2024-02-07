<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

if (isset($_POST['name'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    $bd_documents = get_specialist_documents();

    $documents = [];
    foreach ($bd_documents as $key => $doc) {
        if ((isset($data['name'][$key]) && $data['is_mobile'] == "false") || 
        (isset($data['name'][$key]) && !isset($_FILES[$key]) && $data['is_mobile'] == "true")) {
            $documents[$key] = $doc;
        } 
    }

    foreach ($data['name'] as $key => $name) {
        if (isset($documents[$key])) {
            if (!$name) {
                $json['error']["$key"] = 'Укажите описание';
                continue;
            }

            $documents[$key]['name'] = $name;
        }


        if (!isset($documents[$key])) {
            if (!$name && !isset($_FILES[$key])) continue;

            if ($name && !isset($_FILES[$key])) {
                $json['error'][$key] = 'Загрузите изображение';
                continue;
            }

            if (!$name && isset($_FILES[$key])) {
                $json['error']["$key"] = 'Укажите описание';
                continue;
            }

            $image = load_image($_FILES[$key], 'documents');

            if (isset($image['error'])) {
                $json['error'][$key] = $image['error'];
                continue;
            }

            array_push($documents, [
                'name' => $name,
                'file' => $image['name']
            ]);
        }
    }

    if (isset($json['error'])) responseJson($json);

    $documents = json_encode($documents, JSON_FORCE_OBJECT);

    if (update_specialist_documents($documents)) {
        $json['success'] = 'Документ загружен';
    } else {
        $json['fatal_error'] = 'Загрузка документов временно недоступна';
    }

    responseJson($json);
}
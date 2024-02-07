<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

if (isset($_POST['name'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    foreach ($data['name'] as $key => $name) {
        if (!$name && !$data['price'][$key]) continue;
        else if(!$name && $data['price'][$key]) {
            $json['error']['name['.$key.']'] = 'Укажите название услуги';
        } else if ($name && !$data['price'][$key]) {
            $json['error']['name['.$key.']'] = 'Укажите цену';
        }
    }

    if (isset($json['error'])) responseJson($json);

    $json['success'] = true;
    unset_specialist_services();

    foreach ($data['name'] as $key => $name) {
        if (!$name || !$data['price'][$key]) continue;

        $price = $data['price'][$key] ?? 0;
        if ($price != 0) {
            $price = intval(str_replace('&#039;', '', $price));
        }
        $of = $data['of'][$key] ? 1 : 0;

        set_specialist_service($name, $price, $of);
    }

    responseJson($json);
}
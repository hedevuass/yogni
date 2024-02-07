<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

if (isset($_POST['name'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    if (!$data['name']) $json['error']['name'] = 'Укажите ваше имя';
    if (!$data['lastname']) $json['error']['lastname'] = 'Укажите вашу фамилию';
    if (!$data['classification']) $json['error']['classification'] = 'Укажите ваш статус';

    // Оставляем так, потом будем проверять с Яндекс api
    if (!$data['city_id']) $json['error']['city'] = 'Укажите ваш город';

    if (isset($data['company_id'])) {
        if (!$data['company']) $json['error']['company'] = 'Укажите название компании';

        if (check_company($data['company_id']) && $data['company']) {
            update_company($data['company'], $data['company_id']);
        }
    }

    if (!is_null($data['foundation'])) {
        if (!is_numeric($data['foundation'])) $json['error']['foundation'] = 'Укажите корректный год';

        if (is_numeric($data['foundation'])) {
            if (isset($data['company_id']) && $data['foundation'] > date('Y', strtotime('NOW'))) $json['error']['foundation'] = 'Укажите корректный год';
            else if (!isset($data['company_id']) && ($data['foundation'] < 0 || $data['foundation'] > 50)) $json['error']['foundation'] = 'Укажите корректный год';
        }
        $data['foundation'] = intval($data['foundation']);
    }

    $clear_university = [];
    foreach ($data['university'] as $university) {
        if (!$university) continue;
        $clear_university[] = $university;
    }

    if (!empty($clear_university)) {
        $data['university'] = json_encode($clear_university);
    } else {
        $data['university'] = '';
    }

        $json['photo'] = $_FILES;

    if (isset($json['error'])) responseJson($json);

    if (update_specialist($data) && update_specialist_description($data)) {
        $_SESSION['user']['name'] = $data['name'];
        $json['success'] = 'Информация обновлена';
    } else {
        $json['error']['fatal_error'] = 'Обновление информации временно не работает';
    }

    responseJson($json);
}

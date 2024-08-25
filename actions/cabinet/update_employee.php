<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();
if (isset($_POST['company_id'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }
    $bd_employees = get_company_employees($data['company_id']);

    $employees = [];
    foreach ($bd_employees as $key => $emp) {
        $employees[] = $emp;
    }
    $new_employees = [];
    foreach ($data['name'] as $key => $name) {
        if (!$name && !$data['lastname'][$key] && !$data['post'][$key] && !isset($_FILES[$key]) && !isset($employees[$key])) continue;
        if (!$name) {
            $json['error'][$key] = 'Укажите имя.';
            continue;
        }
        if (!$data['lastname'][$key]) {
            $json['error'][$key] = 'Укажите фамилию.';
            continue;
        }
        if (!$data['post'][$key]) {
            $json['error'][$key] = 'Укажите должность.';
            continue;
        }
        if (!isset($_FILES[$key]) && !isset($employees[$key])) {
            $json['error'][$key] = 'Загрузите изображение';
            continue;
        } else if (!isset($_FILES[$key]) && isset($employees[$key])){
            $img_name = $employees[$key]['photo'];
        } else {
            if($data['is_mobile'] == "true" && isset($data['mobile_loaded'])){
                if (isset($data['mobile_loaded'][$key]) && $data['mobile_loaded'][$key] != '') {
                    $img_name = $data['mobile_loaded'][$key];
                }
            } else {
                $image = load_image($_FILES[$key], 'employee', 'employee_' . $_SESSION['user']['id'] . '_'. date('d-m-y_H-i-s'));
                if (isset($image['error'])) {
                    $json['error'][$key] = $image['error'];
                    continue;
                } else {
                    $img_name = $image['name'];
                }
            }
        }
        $new_employees[$key] = [
            'name' => $name,
            'lastname' => $data['lastname'][$key],
            'post' => $data['post'][$key],
            'file' => $img_name
        ];
    }

    if (isset($json['error'])) responseJson($json);

    foreach ($bd_employees as $emp) {
        unset_company_employee($emp['id']);
    }
    $employee_photos = array();
    foreach ($new_employees as $employee_key => $employee) {
        if (!set_company_employee($employee, $data['company_id'])) {
            $json['fatal_error'] = 'Загрузка сотрудников временно недоступна';
            responseJson($json);
        } else {
            $employee_photos[$employee_key] = $employee['file'];
        }
    }
    $json['img_info'] = $employee_photos;
    $json['success'] = 'Сотрудники загружены';

    responseJson($json);
}
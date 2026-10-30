<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();
if (isset($_POST['company_id'])) {
    foreach ($_POST as $key => $value) {
        if (in_array($key, ['name', 'lastname', 'post'])) {
            $data[$key] = clean_elem_blog_title($value);
        } else {
            $data[$key] = clean_elem_form($value);
        }
    }
    $bd_employees = get_company_employees($data['company_id']);

    // сотрудники этой компании по id
    $bd_by_id = [];
    foreach ($bd_employees as $emp) {
        $bd_by_id[(int)$emp['id']] = $emp;
    }

    $new_employees = [];
    foreach ($data['name'] as $key => $name) {
        $emp_id   = (int)($data['employee_id'][$key] ?? 0);
        $existing = $bd_by_id[$emp_id] ?? null;      // чужой или несуществующий id даст null

        if (!$name && !$data['lastname'][$key] && !$data['post'][$key] && !isset($_FILES[$key]) && !$existing) continue;
        if (!$name) {
            $json['error'][$key] = 'Укажите имя';
            continue;
        }
        if (!$data['lastname'][$key]) {
            $json['error'][$key] = 'Укажите фамилию';
            continue;
        }
        if (!$data['post'][$key]) {
            $json['error'][$key] = 'Укажите должность';
            continue;
        }
        if ($data['is_mobile'] == "true" && isset($data['mobile_loaded'][$key]) && $data['mobile_loaded'][$key] != '') {
            $img_name = $data['mobile_loaded'][$key];
        } else if (!isset($_FILES[$key]) && !$existing) {
            $json['error'][$key] = 'Загрузите изображение';
            continue;
        } else if (!isset($_FILES[$key]) && $existing) {
            $img_name = $existing['photo'];
        } else {
            $uniq = uniqid();
            $image = load_image_webp(
                $_FILES[$key],
                'employee',
                "employee_{$_SESSION['user']['id']}_{$uniq}"
            );
            if (isset($image['error'])) {
                $json['error'][$key] = $image['error'];
                continue;
            } else {
                $img_name = $image['name'];
            }
        }
        $new_employees[$key] = [
            'id'       => $existing ? $emp_id : 0,
            'name'     => $name,
            'lastname' => $data['lastname'][$key],
            'post'     => $data['post'][$key],
            'file'     => $img_name,
        ];
    }

    if (isset($json['error'])) responseJson($json);

    $company_id      = (int)$data['company_id'];
    $employee_photos = [];
    $employee_ids    = [];
    $keep_ids        = [];

    foreach ($new_employees as $employee_key => $employee) {
        if ($employee['id']) {
            if (!update_company_employee($employee['id'], $company_id, $employee)) {
                $json['fatal_error'] = 'Загрузка сотрудников временно недоступна';
                responseJson($json);
            }
            $saved_id = (int)$employee['id'];
        } else {
            $saved_id = (int)set_company_employee($employee, $company_id);
            if (!$saved_id) {
                $json['fatal_error'] = 'Загрузка сотрудников временно недоступна';
                responseJson($json);
            }
        }
        $keep_ids[]                     = $saved_id;
        $employee_photos[$employee_key] = $employee['file'];
        $employee_ids[$employee_key]    = $saved_id;
    }

    // удаляем только тех, кого убрали из формы (и только после успешной записи остальных)
    foreach ($bd_by_id as $id => $emp) {
        if (!in_array($id, $keep_ids, true)) {
            unset_company_employee($id, $company_id);
        }
    }

    $json['img_info'] = $employee_photos;
    $json['success']  = 'Сотрудники загружены';
    responseJson($json);
}

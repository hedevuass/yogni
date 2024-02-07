<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['name'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    // Проверка имени
    if (!isset($data['name']) || !$data['name']) $json['error']['name'] = 'Укажите имя';
    // Проверка фамилии
    if (!isset($data['lastname']) || !$data['lastname']) $json['error']['lastname'] = 'Укажите фамилию';
    // Проверка email
    if (!isset($data['email']) || !$data['email'] || !($data['email'] = validation_email($data['email']))) $json['error']['email'] = 'Укажите корректный Email';
    if (!isset($data['id']) && isset($data['email']) && !isset($json['error']['email']) && check_email($data['email'])) $json['error']['email'] = 'Email занят другим пользователем';
    // Проверка телефона
    if (!isset($data['phone']) || !$data['phone'] || !($data['phone'] = validation_phone($data['phone']))) $json['error']['phone'] = 'Укажите корректный номер телефона';
    // Проверка пароля
    if (!isset($data['id']) && isset($data['password']) && strlen($data['password'] < 6)) $json['error']['password'] = 'Укажите корректный пароль';
    // Проверка компании
    if ((isset($data['type']) && $data['type'] == 2) && (!isset($data['company']) || !$data['company']['name'])) $json['error']['company_name'] = 'Укажите название компании';

    // Если есть ошибки в полях возвращаем
    if (isset($json['error'])) responseJson($json);

    // Шифрование пароля
    if (isset($data['password'])) $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

    // Хеш
    try {
        $hash = md5($data['phone'] . bin2hex(random_bytes(10)));
    } catch (Exception $e) {
        $json['fatal'] = $e->getMessage();
        responseJson($json);
    }

    if (isset($data['id'])) {
        $specialist = get_specialist($data['id']);
    }

    // Загрузка фотографии
    if ($_FILES) {
        $image = load_image($_FILES['photo'], 'specialists');

        if (isset($image['error'])) responseJson($json['error']['photo'] = $image['error']);

        if (isset($specialist) && isset($specialist['photo']) && $specialist['photo']) {
            unlink($_SERVER['DOCUMENT_ROOT'] . '/assets/img/specialists/' . $specialist['photo']);
        }
    }

    if (isset($specialist)) {
        if (!isset($image)) $image = [
            'name' => $specialist['photo']
        ];
    }

    // Название компании
    if (isset($data['type']) && $data['type'] == 2 && (!isset($data['company']['id']) || !$data['company']['id'])) {
        $company = set_company($data['company']['name'], $data['classification_id']);
    }

    if (isset($data['company']['id']) && $data['company']['id']) {
        $company = $data['company']['id'];
        try {
            update_company($data['company']['name'], $data['classification_id'], $company);
        } catch (Exception $e) {
            $json['fatal'] = $e->getMessage();
            responseJson($json);
        }
    }

    // Проверка активации пользователя
    if (!isset($data['active'])) $data['active'] = 0;

    $data = [
        'name' => $data['name'],
        'lastname' => $data['lastname'],
        'email' => $data['email'],
        'password' => $data['password'],
        'hash' => $hash ?? '',
        'photo' => $image['name'] ?? '',
        'classification_id' => $data['classification_id'],
        'city_id' => $data['city_id'],
        'company_id' => $company ?? null,
        'active' => $data['active'],
        'phone' => $data['phone'],
        'id' => $data['id'] ?? ''
    ];

    try {
        $specialist = $data['id'] ? update_specialist($data) : set_specialist($data);
    } catch (Exception $e) {
        $json['fatal'] = $e->getMessage();
        responseJson($json);
    }

    if (!$specialist) {
        $json['fatal'] = 'Добавление или редактирование специалистов временно недоступно';
    } else {
        $json['success'] = 'Специалист добавлен';
    }

    responseJson($json);
}
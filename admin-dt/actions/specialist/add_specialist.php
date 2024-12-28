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
    if (!isset($data['id']) && isset($data['password']) && strlen($data['password']) < 6) $json['error']['password'] = 'Укажите корректный пароль';
    // Проверка компании
    if ((isset($data['type']) && $data['type'] == 2) && (!isset($data['company']) || !$data['company']['name'])) $json['error']['company_name'] = 'Укажите название компании';

    // Проверка контактных данных из поля `other`
    $otherContacts = [];
    if (isset($data['contacts_email']) && !validation_email($data['contacts_email'])) {
        $json['error']['contacts_email'] = 'Укажите корректный Email';
    } else {
        $otherContacts['email'] = $data['contacts_email'] ?? '';
    }

    $otherContacts['site'] = $data['contacts_site'] ?? '';
    $otherContacts['tg'] = $data['contacts_tg'] ?? '';
    $otherContacts['vk'] = $data['contacts_vk'] ?? '';
    $otherContacts['dzen'] = $data['contacts_dzen'] ?? '';

    // Преобразуем в JSON
    $data['other'] = '{"email":"' . addslashes($otherContacts['email']) . '",' .
        '"site":"' . addslashes($otherContacts['site']) . '",' .
        '"tg":"' . addslashes($otherContacts['tg']) . '",' .
        '"vk":"' . addslashes($otherContacts['vk']) . '",' .
        '"dzen":"' . addslashes($otherContacts['dzen']) . '"}';

    // Преобразуем в JSON вручную для 'other'
    $data['other'] = '{"email":"' . addslashes($otherContacts['email']) . '",' .
        '"site":"' . addslashes($otherContacts['site']) . '",' .
        '"tg":"' . addslashes($otherContacts['tg']) . '",' .
        '"vk":"' . addslashes($otherContacts['vk']) . '",' .
        '"dzen":"' . addslashes($otherContacts['dzen']) . '"}';


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

    if ($_FILES) {
        // Загружаем каждый файл документа
        foreach ($_FILES['documents']['tmp_name'] as $docKey => $tmpFile) {
            if (!empty($tmpFile)) {
                $docName = $_POST['documents'][$docKey]['name'];

                // Загружаем файл
                $directory = 'documents';
                $imgName = '';
                $result = load_image($_FILES['documents'][$docKey], $directory, $imgName);

                // Если есть ошибка, выводим ее
                if (isset($result['error'])) {
                    responseJson(['error' => $result['error']]);
                }

                $fileName = $result['name'];

                $documents[$docKey] = [
                    'name' => $docName,
                    'file' => $fileName,
                ];
            }
        }

        // Сохраняем информацию о документах в базе данных
        if (!empty($documents)) {
            $specialist['documents'] = $documents;
        }
    }

    // Компания
    if (isset($data['company_name']) && isset($data['company_classification_id'])) {
        // Если компания существует (передан ID), обновляем её
        if (isset($data['company_id']) && $data['company_id']) {
            try {
                update_company($data['company_name'], $data['company_classification_id'], $data['company_id']);
            } catch (Exception $e) {
                $json['fatal'] = $e->getMessage();
                responseJson($json);
            }
            $company_id = $data['company_id'];
        } else {
            // Если ID компании нет, создаем новую компанию
            $company_id = set_company($data['company_name'], $data['company_classification_id']);
        }
    }

    // Обработка сотрудников и их фото
    if ($_FILES || (isset($data['employees']) && !empty($data['employees']))) {
        foreach ($data['employees'] as $employeeId => $employee) {
            if (!empty($_FILES['employees']['tmp_name'][$employeeId])) {
                $image = load_image($_FILES['employees']['tmp_name'][$employeeId], 'employee');
                if (isset($image['error'])) responseJson(['error' => $image['error']]);

                if (!empty($employee['photo'])) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . '/uploads/employee/' . $employee['photo']);
                }

                $data['employees'][$employeeId]['photo'] = $image['name'];
            }

            if (empty($employee['name']) || empty($employee['post'])) {
                $json['error'][] = "У сотрудника с ID {$employeeId} отсутствуют обязательные поля!";
            }
        }

        if (!empty($json['error'])) responseJson($json);

        if ($company_id) {
            try {
                update_employees($data['employees'], $company_id);
            } catch (Exception $e) {
                responseJson(['fatal' => $e->getMessage()]);
            }
        }
    }



    // Проверка активации пользователя
    if (!isset($data['active'])) $data['active'] = 0;

    $data['schedule'] = htmlspecialchars_decode($data['schedule']);
    $data['documents'] = htmlspecialchars_decode($data['documents']);

    // Обрабатываем данные об услугах
    $services = [];
    if (!empty($_POST['services'])) {
        foreach ($_POST['services'] as $key => $service) {
            // Учитываем только услуги с заполненным названием и ценой
            if (!empty($service['name']) && isset($service['price'])) {
                $services[] = [
                    'name' => clean_elem_form($service['name']),
                    'price' => intval(str_replace("'", "", $service['price'])), // Удаляем лишние символы и преобразуем в число
                    'is_active' => !empty($service['is_active']) ? 1 : 0, // Приводим чекбокс к числу
                ];
            }
        }
    }

    // Добавляем услуги в данные
    $data['services'] = $services;

    // Массив данных для записи в базу
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
        'id' => $data['id'] ?? '',
        'other' => $data['other'] ?? '',
        'schedule' => $data['schedule'] ?? '',
        'documents' => $data['documents'] ?? '',
        'services' => $data['services'] ?? '',
        'specializations' => $data['specializations'] ?? '',
    ];

    $setData = [
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
        'id' => $data['id'] ?? '',
        'other' => $data['other'] ?? '',
        'schedule' => $data['schedule'] ?? '',
        'documents' => $data['documents'] ?? '',
        'services' => $data['services'] ?? '',
        'specializations' => $data['specializations'] ?? '',
    ];

    try {
        // Пробуем добавить или обновить специалиста
        $specialist = $data['id'] ? update_specialist($data) : set_specialist($setData);

        // Добавляем специализации, если они есть
        if (!empty($data['specializations'])) {
            try {
                update_specialist_specializations($data['id'], $data['specializations']);
            } catch (Exception $e) {
                // Логируем ошибку добавления специализаций, но не прерываем выполнение
                error_log('Error adding specializations: ' . $e->getMessage());
            }
        }
    } catch (Exception $e) {
        // Обработка ошибок добавления/обновления специалиста
        $json['fatal'] = $e->getMessage();
        responseJson($json);
    }

    // Ответ на запрос
    if (!$specialist) {
        $json['fatal'] = 'Добавление или редактирование специалистов временно недоступно';
    } else {
        $json['success'] = 'Специалист добавлен';
    }

    responseJson($json);
}

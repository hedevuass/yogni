<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['name'])) {

    if (!empty($_POST['remove_photo']) && $_POST['remove_photo'] == '1') {
        // Удаление файла с сервера
        if ($currentSpecialistPhoto) {
            $oldPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/' . $currentSpecialistPhoto;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // Очистка поля photo в БД
        $specialist_id = (int) ($_POST['id'] ?? 0);
        if ($specialist_id > 0) {
            $deleteQuery = "UPDATE dt_specialist SET photo = NULL WHERE id = ?";
            $deleteResult = $data_base->update($deleteQuery, [$specialist_id]);

            if (!$deleteResult) {
                responseJson(['error' => 'Ошибка при удалении фото в базе данных.']);
            }

            $data['photo'] = ''; // Очистка значения для дальнейшего сохранения
        } else {
            responseJson(['error' => 'ID специалиста не передан для удаления фото.']);
        }
    }

    foreach ($_POST as $key => $value) {
        if ($key === 'university') {
            $data[$key] = $value; // НЕ чистим JSON!
        } else {
            $data[$key] = clean_elem_form($value);
        }
    }

    $data['metro']     = trim($_POST['metro'] ?? '') === '' ? null : (int) $_POST['metro'];
    $data['city_id']   = (int)   ($_POST['city_id'] ?? 0);
    $data['street']    = trim($_POST['street']    ?? '');
    $data['house']     = trim($_POST['house']     ?? '');
    $data['how_to_get']     = trim($_POST['how_to_get']     ?? '');

    // 2) Получаем из БД названия:
    $city_row  = $data_base->selectRow(
        "SELECT name FROM dt_city WHERE id = ?",
        [$data['city_id']]
    );
    $city_name = $city_row['name'] ?? '';

    // 3) Формируем строку адреса для геокодера:
    $main_address = '';
    if ($city_name !== '') {
        $main_address .= $city_name;
    }
    if ($data['street'] !== '') {
        $main_address .= ', ' . $data['street'];
    }
    if ($data['house'] !== '') {
        $main_address .= ', ' . $data['house'];
    }

    // 4) Запрашиваем координаты у Яндекс:
    $data['coordinates'] = ''; // по умолчанию пусто
    if (!empty($main_address)) {
        $geocode_response = @file_get_contents('https://geocode-maps.yandex.ru/1.x/?format=json&apikey=b0c770d0-eb80-4f0e-bb33-f3d1bb289c95&geocode=' . urlencode($main_address));
        if ($geocode_response) {
            $geocode_data = json_decode($geocode_response, true);
            if (!empty($geocode_data['response']['GeoObjectCollection']['featureMember'][0])) {
                $data['coordinates'] = $geocode_data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];
            }
        }
    }

    // Проверка имени
    if (!isset($data['name']) || !$data['name'])
        $json['error']['name'] = 'Укажите имя';
    if (!isset($data['city_id']) || !$data['city_id']) {
        $json['error']['city'] = 'Укажите город';
    }
    // Проверка фамилии
    /* if (!isset($data['lastname']) || !$data['lastname'])
        $json['error']['lastname'] = 'Укажите фамилию'; */
    // Проверка фамилии
    if (!isset($data['slug']) || !$data['slug'])
        $data['slug'] = null;
    // Проверка email
    /* if (!isset($data['email']) || !$data['email'] || !($data['email'] = validation_email($data['email'])))
        $json['error']['email'] = 'Укажите корректный Email';
    if (!isset($data['id']) && isset($data['email']) && !isset($json['error']['email']) && check_email($data['email']))
        $json['error']['email'] = 'Email занят другим пользователем';
    // Проверка телефона
    if (!isset($data['phone']) || !$data['phone'] || !($data['phone'] = validation_phone($data['phone'])))
        $json['error']['phone'] = 'Укажите корректный номер телефона'; */
    // Проверка пароля
    if (!isset($data['id']) && isset($data['password']) && strlen($data['password']) < 6)
        $json['error']['password'] = 'Укажите корректный пароль';
    // Проверка компании
    if (!isset($data['remove_company']) && empty($data['company_name']) && $data['company_name'] == '') {
        $json['error']['company_name'] = 'Укажите название компании';
    }

    $universityJson = $data['university'] ?? '[]';

    // Проверка контактных данных из поля `other`
    $otherContacts = [];
    /* if (isset($data['contacts_email']) && !validation_email($data['contacts_email'])) {
        $json['error']['contacts_email'] = 'Укажите корректный контактный Email';
    } else {
        $otherContacts['email'] = $data['contacts_email'] ?? '';
    } */

    $otherContacts['email'] = $data['contacts_email'] ?? '';
    $otherContacts['site'] = $data['contacts_site'] ?? '';
    $otherContacts['tg'] = $data['contacts_tg'] ?? '';
    $otherContacts['vk'] = $data['contacts_vk'] ?? '';
    $otherContacts['dzen'] = $data['contacts_dzen'] ?? '';

    $data['other'] = json_encode([
        'email' => $otherContacts['email'] ?? '',
        'site' => $otherContacts['site'] ?? '',
        'tg' => $otherContacts['tg'] ?? '',
        'vk' => $otherContacts['vk'] ?? '',
        'dzen' => $otherContacts['dzen'] ?? ''
    ], JSON_UNESCAPED_UNICODE);

    // Если есть ошибки в полях возвращаем
    if (isset($json['error']))
        responseJson($json);

    // Шифрование пароля
    if (!empty($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
    } elseif (isset($data['id'])) {
        // Если редактируем и пароль не введён — оставляем старый
        $old = $data_base->selectRow("SELECT password FROM dt_specialist WHERE id = ?", [$data['id']]);
        $data['password'] = $old['password'];
    }

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

    if (isset($data['id'])) {
        $specialist_id = (int) $data['id'];
        try {
            update_specialist_contacts($data, $specialist_id);
        } catch (Exception $e) {
            $json['fatal'] = $e->getMessage();
            responseJson($json);
        }
    }

    $currentSpecialist = $data_base->selectRow("SELECT photo FROM dt_specialist WHERE id = ?", [(int) $data['id']]);
    $currentSpecialistPhoto = $currentSpecialist ? $currentSpecialist['photo'] : null;

    // Загрузка нового фото для специалиста, если оно выбрано
    if (isset($_FILES['photo']) && $_FILES['photo']['size'] > 0) {
        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            responseJson(['error' => 'Ошибка загрузки файла: код ' . $_FILES['photo']['error']]);
        }

        // Загрузка нового фото
        $image = load_image($_FILES['photo'], 'avatars');

        if (isset($image['error'])) {
            responseJson(['error' => $image['error']]);
        }

        // Если старое фото существует и новое отличается, удаляем старое фото
        if ($currentSpecialistPhoto && $image['name'] !== $currentSpecialistPhoto) {
            unlink($_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/' . $currentSpecialistPhoto);
        }

        $data['photo'] = $image['name'];
    } else {
        $data['photo'] = $currentSpecialistPhoto;
    }

    // Компания
    // Удаление компании, если нужно
    if ($data['id'] > 0) {
        if (isset($data['remove_company']) && $data['remove_company']) {
            if (!empty($data['id'])) {
                $data_base->update(
                    "UPDATE dt_specialist SET company_id = NULL WHERE id = ?",
                    [(int) $data['id']]
                );
            }
        }

        if (!isset($data['remove_company']) && isset($data['company_name']) && isset($data['company_classification_id']) && !empty($data['company_classification_id'])) {
            try {
                if (!empty($data['company_id'])) {
                    update_company(
                        $data['company_name'],
                        (int) $data['company_classification_id'],
                        (int) $data['company_id']
                    );
                    $company_id = (int) $data['company_id'];
                } else {
                    // Создаем новую компанию
                    $company_id = set_company(
                        $data['company_name'],
                        (int) $data['company_classification_id']
                    );

                    // Привязываем компанию к специалисту
                    if (!empty($data['id'])) {
                        $data_base->update(
                            "UPDATE dt_specialist SET company_id = ? WHERE id = ?",
                            [$company_id, (int) $data['id']]
                        );
                    } else {
                        throw new Exception("Не передан ID специалиста для привязки новой компании");
                    }
                }
            } catch (Exception $e) {
                $json['fatal'] = $e->getMessage();
                responseJson($json);
            }
        }
    }


    // Получаем исходный список сотрудников компании (до обновления)
    $originalEmployees = $data_base->selectAll("SELECT id FROM dt_company_employee WHERE company_id = ?", [$company_id]);

    $hasEmployeePhotos = !empty($_FILES['employees']);
    $hasEmployeesData = isset($data['employees']) && !empty($data['employees']);

    if (!isset($data['remove_company']) && $hasEmployeePhotos || $hasEmployeesData && !empty($hasEmployeesData)) {
        foreach ($data['employees'] as $employeeId => $employee) {
            if (empty($employee['name']) || empty($employee['post'])) {
                continue;
            }

            $currentEmployee = $data_base->selectRow(
                "SELECT id, photo FROM dt_company_employee WHERE id = ? AND company_id = ?",
                [$employeeId, $company_id]
            );
            $currentPhoto = $currentEmployee ? $currentEmployee['photo'] : null;

            if (!empty($_FILES['employees']['tmp_name'][$employeeId]['photo'])) {
                $file = [
                    'name' => $_FILES['employees']['name'][$employeeId]['photo'],
                    'type' => $_FILES['employees']['type'][$employeeId]['photo'],
                    'tmp_name' => $_FILES['employees']['tmp_name'][$employeeId]['photo'],
                    'error' => $_FILES['employees']['error'][$employeeId]['photo'],
                    'size' => $_FILES['employees']['size'][$employeeId]['photo'],
                ];

                $image_employee = load_image($file, 'employee');
                if (isset($image_employee['error'])) {
                    responseJson(['error' => $image_employee['error']]);
                }

                // Если фото изменилось — удалить старое
                if ($currentPhoto && $image_employee['name'] !== $currentPhoto) {
                    @unlink($_SERVER['DOCUMENT_ROOT'] . '/uploads/employee/' . $currentPhoto);
                }

                $data['employees'][$employeeId]['photo'] = $image_employee['name'];
            } elseif (!empty($employee['photo_existing'])) {
                $data['employees'][$employeeId]['photo'] = $employee['photo_existing'];
            } else {
                $data['employees'][$employeeId]['photo'] = $currentPhoto;
            }
        }

        if (!empty($data['employees'])) {
            try {
                update_employees($data['employees'], $company_id);
            } catch (Exception $e) {
                responseJson(['fatal' => $e->getMessage()]);
            }
        }
    }

    // Собираем ID сотрудников из формы, которые были исходными (числовые ключи)
    $submittedEmployeesIds = [];
    if (isset($data['employees'])) {
        foreach ($data['employees'] as $key => $employee) {
            if (is_numeric($key)) {
                $submittedEmployeesIds[] = (int) $key;
            }
        }
    }

    // Удаляем только тех сотрудников, которые были изначально, но не отправлены в форме
    foreach ($originalEmployees as $emp) {
        if (!in_array($emp['id'], $submittedEmployeesIds)) {
            $data_base->delete("DELETE FROM dt_company_employee WHERE id = ? AND company_id = ?", [$emp['id'], $company_id]);
        }
    }

    $data['schedule'] = htmlspecialchars_decode($data['schedule']);

    $existing = $data_base->selectRow(
        "SELECT document FROM dt_specialist_description WHERE specialist_id = ?",
        [$data['id']]
    );
    $existingDocs = json_decode($existing['document'], true) ?: [];

    // Составляем массив документов из POST
    $docsArr = [];
    if (!empty($_POST['documents']) && is_array($_POST['documents'])) {
        foreach ($_POST['documents'] as $docKey => $docData) {
            $file = $docData['file'] ?? ($docData['file_existing'] ?? null);

            // Если ничего нет — пробуем взять из существующих (для надёжности)
            if ($file === null && empty($_FILES['documents']['tmp_name'][$docKey]['file'])) {
                $file = $existingDocs[$docKey]['file'] ?? null;
            }
            $docsArr[$docKey] = [
                'name' => $docData['name'],
                'file' => $file,
            ];
        }
    }

    // Если появился ключ 'new', заменяем его на следующий числовой индекс
    if (isset($docsArr['new'])) {
        // Определяем текущие числовые ключи
        $numKeys = array_filter(array_keys($docsArr), fn($k) => is_numeric($k) && ctype_digit((string) $k));
        $maxKey = $numKeys ? max($numKeys) : -1;
        $newKey = $maxKey + 1;
        // Переносим запись
        $docsArr[$newKey] = $docsArr['new'];
        unset($docsArr['new']);
        // Также переносим файлы из $_FILES под новым индексом, если есть
        if (!empty($_FILES['documents'])) {
            foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $prop) {
                if (isset($_FILES['documents'][$prop]['new']['file'])) {
                    $_FILES['documents'][$prop][$newKey]['file'] = $_FILES['documents'][$prop]['new']['file'];
                    unset($_FILES['documents'][$prop]['new']);
                }
            }
        }
    }

    // Обработка файлов загрузки
    if (!empty($_FILES['documents'])) {
        foreach ($docsArr as $docKey => &$doc) {
            if (!empty($_FILES['documents']['tmp_name'][$docKey]['file'])) {
                $fileArr = [
                    'name' => $_FILES['documents']['name'][$docKey]['file'],
                    'type' => $_FILES['documents']['type'][$docKey]['file'],
                    'tmp_name' => $_FILES['documents']['tmp_name'][$docKey]['file'],
                    'error' => $_FILES['documents']['error'][$docKey]['file'],
                    'size' => $_FILES['documents']['size'][$docKey]['file'],
                ];
                $uploaded = load_image($fileArr, 'documents');
                if (isset($uploaded['error'])) {
                    responseJson(['error' => $uploaded['error']]);
                }
                if (!empty($existingDocs[$docKey]['file']) && $uploaded['name'] !== $existingDocs[$docKey]['file']) {
                    @unlink($_SERVER['DOCUMENT_ROOT'] . '/uploads/documents/' . $existingDocs[$docKey]['file']);
                }
                $doc['file'] = $uploaded['name'];
            }
        }
        unset($doc);
    }

    // Сохраняем документы в JSON, сохраняя числовые ключи
    $data['documents'] = json_encode($docsArr, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT);
    $data['foundation'] = isset($data['foundation']) && $data['foundation'] !== ''
        ? (int) $data['foundation']
        : null;

    // Обрабатываем данные об услугах
    $services = [];
    if (!empty($_POST['services'])) {
        foreach ($_POST['services'] as $key => $service) {
            // Учитываем только услуги с заполненным названием и ценой
            if (!empty($service['name']) && isset($service['price'])) {
                $services[] = [
                    'name' => clean_elem_form($service['name']),
                    'price' => intval(str_replace("'", "", $service['price'])),
                    'is_active' => !empty($service['is_active']) ? 1 : 0,
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
        'slug' => $data['slug'],
        'email' => $data['email'],
        'password' => $data['password'],
        'hash' => $hash ?? '',
        'universityJson' => $universityJson ?? '',
        'photo' => $image['name'] ?? '',
        'classification_id' => $data['classification_id'],
        'city_id' => $data['city_id'],
        'metro' => $data['metro'],
        'meta_title' => $data['meta_title'] ?? null,
        'meta_description' => $data['meta_description'] ?? null,
        'meta_keywords' => $data['meta_keywords'] ?? null,
        'company_id' => $company_id ?? null,
        'company_classification_id' => $data['company_classification_id'],
        'company_name' => $data['company_name'],
        'active' => $data['active'],
        'phone' => $data['phone'],
        'id' => $data['id'] ?? '',
        'other' => $data['other'] ?? '',
        'schedule' => $data['schedule'] ?? '',
        'documents' => $data['documents'] ?? '',
        'foundation' => $data['foundation'] ?? '',
        'page-discription' => $data['page-discription'] ?? '',
        'services' => $data['services'] ?? '',
        'specializations' => $data['specializations'] ?? '',
        'city' => $data['city'] ?? '',
        'street' => $data['street'] ?? '',
        'house' => $data['house'] ?? '',
        'coordinates' => $data['coordinates'] ?? '',
        'how_to_get' => $data['how_to_get'] ?? '',
    ];

    // Добавили данные компании для создания компании, при создании нового пользователя
    $setData = [
        'name' => $data['name'],
        'lastname' => $data['lastname'],
        'slug' => $data['slug'],
        'email' => $data['email'],
        'password' => $data['password'],
        'hash' => $hash ?? '',
        'universityJson' => $universityJson ?? '',
        'photo' => $image['name'] ?? '',
        'classification_id' => $data['classification_id'],
        'city_id' => $data['city_id'],
        'metro' => $data['metro'],
        'meta_title' => $data['meta_title'] ?? null,
        'meta_description' => $data['meta_description'] ?? null,
        'meta_keywords' => $data['meta_keywords'] ?? null,
        'company_id' => $company_id ?? null,
        'company_classification_id' => $data['company_classification_id'],
        'company_name' => $data['company_name'],
        'active' => $data['active'],
        'phone' => $data['phone'],
        'id' => $data['id'] ?? '',
        'other' => $data['other'] ?? '',
        'schedule' => $data['schedule'] ?? '',
        'documents' => $data['documents'] ?? '',
        'foundation' => $data['foundation'] ?? '',
        'page-discription' => $data['page-discription'] ?? '',
        'services' => $data['services'] ?? '',
        'specializations' => $data['specializations'] ?? '',
        'city' => $data['city'] ?? '',
        'street' => $data['street'] ?? '',
        'house' => $data['house'] ?? '',
        'coordinates' => $data['coordinates'] ?? '',
        'how_to_get' => $data['how_to_get'] ?? '',
    ];

    try {
        // Пробуем добавить или обновить специалиста
        $specialist = $data['id'] ? update_specialist($data) : set_specialist($setData);

        if (!empty($data['specializations'])) {
            try {
                update_specialist_specializations($data['id'], $data['specializations']);
            } catch (Exception $e) {
                error_log('Error adding specializations: ' . $e->getMessage());
            }
        } else {
            unset_specialist_specializations($data['id']);
        }
    } catch (Exception $e) {
        $json['fatal'] = $e->getMessage();
        responseJson($json);
    }

    if (!$specialist) {
        $json['fatal'] = 'Добавление или редактирование специалистов временно недоступно';
    } else {
        $json['success'] = 'Специалист добавлен';
    }

    clearCache('specialists_lawyers');
    clearCache('specialists_experts');
    clearCache('specialists_notaries');
    clearCache('promo_block_top_specialists');

    clearCache('catalog_by_service');
    clearCache('catalog_by_classification');

    responseJson($json);
}

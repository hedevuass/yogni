<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

if (isset($_POST['name'])) {
    // 1. Собираем и очищаем данные из POST
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    // 2. Достаём документы из БД
    $bd_documents = get_specialist_documents();

    // 3. Фильтруем их по вашим условиям mobile/desktop
    $documents = [];
    foreach ($bd_documents as $key => $doc) {
        if (isset($data['name'][$key])) {
            $documents[$key] = $doc;
        }
    }

    // 4. Проходим по всем переданным name[]
    foreach ($data['name'] as $key => $name) {
        // 4.1. Если запись уже есть в $documents
        if (isset($documents[$key])) {
            // — проверяем, что описание не пустое
            if (!$name) {
                $json['error']["$key"] = 'Укажите описание';
                continue;
            }

            // — если пришёл новый файл, заменяем
            if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
                if ($_FILES[$key]['size'] > 5 * 1024 * 1024) {
                    $json['error'][$key] = 'Файл слишком большой (максимальный размер - до 5 МБ).';
                    continue;
                }

                $image = load_image_webp($_FILES[$key], 'documents');
                if (isset($image['error'])) {
                    $json['error'][$key] = $image['error'];
                    continue;
                }
                $documents[$key]['file'] = $image['name'];
            }

            // — обновляем/ставим имя
            $documents[$key]['name'] = $name;

            continue;
        }

        // 4.2. Если записи ещё нет — обрабатываем как новый документ
        // пропускаем совсем пустые строки
        if (!$name && !isset($_FILES[$key])) {
            continue;
        }
        // если есть имя, но нет файла — ошибка
        if ($name && !isset($_FILES[$key])) {
            $json['error'][$key] = 'Загрузите изображение';
            continue;
        }
        // если есть файл, но пустое имя — ошибка
        if (!$name && isset($_FILES[$key])) {
            $json['error']["$key"] = 'Укажите описание';
            continue;
        }

        // загружаем файл
        $image = load_image_webp($_FILES[$key], 'documents');
        if (isset($image['error'])) {
            $json['error'][$key] = $image['error'];
            continue;
        }

        // добавляем новый элемент в конец списка
        $documents[] = [
            'name' => $name,
            'file' => $image['name']
        ];
    }

    // 5. Если были ошибки — отдаем их
    if (isset($json['error'])) {
        responseJson($json);
    }

    // 6. Кодируем в JSON и сохраняем
    $documents = json_encode($documents, JSON_FORCE_OBJECT);
    if (update_specialist_documents($documents)) {
        $json['success'] = 'Документ загружен';
    } else {
        $json['fatal_error'] = 'Загрузка документов временно недоступна';
    }
    responseJson($json);
}

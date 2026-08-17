<?php
session_start();
$userRole = $_SESSION['user_role'] ?? 'editor';

include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (empty($userRole) || $userRole === 'editor') {
    $json['error'] = 'Недостаточно прав для выполнения этого действия';
    responseJson($json);
    exit;
}

if (isset($_POST['name'])) {
    foreach ($_POST as $key => $value) {
        if ($key === 'university') {
            $data[$key] = $value;  // НЕ чистим JSON!
        } else {
            $data[$key] = clean_elem_form($value);
        }
    }

    $data['metro'] = trim($_POST['metro'] ?? '') === '' ? null : (int) $_POST['metro'];
    $data['city_id'] = (int) ($_POST['city_id'] ?? 0);
    $data['street'] = trim($_POST['street'] ?? '');
    $data['house'] = trim($_POST['house'] ?? '');
    $data['how_to_get'] = trim($_POST['how_to_get'] ?? '');

    // 2) Получаем из БД названия:
    $city_row = $data_base->selectRow(
        'SELECT name FROM dt_city WHERE id = ?',
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
    $data['coordinates'] = '';
    if (!empty($main_address)) {
        $params = [
            'apikey' => 'a078c003-a00e-4d49-aed7-b3b61be29fd7',
            'geocode' => $main_address,
            'format' => 'json',
            'lang' => 'ru_RU'
        ];

        $geocode_url = 'https://geocode-maps.yandex.ru/1.x/?' . http_build_query($params);

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: PHP-Backend\r\n"
                    . "Referer: https://yogni.ru/\r\n"
            ]
        ]);

        $geocode_response = @file_get_contents($geocode_url, false, $context);

        if ($geocode_response) {
            $geocode_data = json_decode($geocode_response, true);

            if (!empty($geocode_data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'])) {
                $data['coordinates'] = $geocode_data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];

                $coords_array = explode(' ', $data['coordinates']);
                if (count($coords_array) === 2) {
                    $longitude = (float) $coords_array[0];
                    $latitude = (float) $coords_array[1];
                }
            }
        }
    }

    if (isset($data['id']) && $data['id'] > 0) {
        // Передаем массив выбранных ID, если ничего не выбрано — передаем пустой массив
        $equipment_list = isset($_POST['equipment']) && is_array($_POST['equipment']) ? $_POST['equipment'] : [];
        update_specialist_equipment_admin((int)$data['id'], $equipment_list);
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
    $otherContacts['max'] = $data['contacts_max'] ?? '';

    $data['other'] = json_encode([
        'email' => $otherContacts['email'] ?? '',
        'site' => $otherContacts['site'] ?? '',
        'tg' => $otherContacts['tg'] ?? '',
        'vk' => $otherContacts['vk'] ?? '',
        'dzen' => $otherContacts['dzen'] ?? '',
        'max' => $otherContacts['max'] ?? ''
    ], JSON_UNESCAPED_UNICODE);

    // Если есть ошибки в полях возвращаем
    if (isset($json['error']))
        responseJson($json);

    // Шифрование пароля
    if (!empty($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
    } elseif (isset($data['id'])) {
        // Если редактируем и пароль не введён — оставляем старый
        $old = $data_base->selectRow('SELECT password FROM dt_specialist WHERE id = ?', [$data['id']]);
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

    // Вспомогательная функция авторазворота (на случай, если она не объявлена глобально)
    if (!function_exists('fix_image_orientation')) {
        function fix_image_orientation($file_path)
        {
            if (!function_exists('exif_read_data')) return;
            $mime = @mime_content_type($file_path);
            if ($mime !== 'image/jpeg') return;
            $exif = @exif_read_data($file_path);
            if (empty($exif['Orientation']) || $exif['Orientation'] <= 1) return;
            $image = @imagecreatefromjpeg($file_path);
            if (!$image) return;
            switch ($exif['Orientation']) {
                case 3:
                    $image = imagerotate($image, 180, 0);
                    break;
                case 6:
                    $image = imagerotate($image, -90, 0);
                    break;
                case 8:
                    $image = imagerotate($image, 90, 0);
                    break;
            }
            imagejpeg($image, $file_path, 95);
            imagedestroy($image);
        }
    }

    // Шаг 0. Получаем текущие данные из базы (чтобы знать, что удалять)
    $currentSpecialistPhoto = null;
    $currentGallery = [];
    if (!empty($data['id'])) {
        $currentSpecialist = $data_base->selectRow('SELECT photo, gallery FROM dt_specialist WHERE id = ?', [(int)$data['id']]);
        if ($currentSpecialist) {
            $currentSpecialistPhoto = $currentSpecialist['photo'];
            $currentGallery = json_decode($currentSpecialist['gallery'] ?? '[]', true) ?: [];
        }
    }

    $uploadDirAvatars = $_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/';
    $uploadDirGallery = $_SERVER['DOCUMENT_ROOT'] . '/uploads/gallery/';


    if (isset($_FILES['photo']) && $_FILES['photo']['size'] > 0) {
        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            responseJson(['error' => 'Ошибка загрузки аватара: код ' . $_FILES['photo']['error']]);
        }

        fix_image_orientation($_FILES['photo']['tmp_name']);

        // Сохранение оригинала
        $image = load_image_webp($_FILES['photo'], 'avatars', 'avatar_' . ($data['id'] ?? uniqid()) . '_' . date('d-m-y_H-i-s'), 60);

        if (isset($image['error'])) {
            responseJson(['error' => $image['error']]);
        }

        $fullPath = $uploadDirAvatars . $image['name'];
        $src = @imagecreatefromwebp($fullPath);

        if ($src) {
            $width = imagesx($src);
            $height = imagesy($src);

            // Квадратная миниатюра (thumb_)
            $thumbSize = 250;
            $thumb = imagecreatetruecolor($thumbSize, $thumbSize);
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);

            $minSide = min($width, $height);
            $srcX = (int) (($width - $minSide) / 2);
            $srcY = (int) (($height - $minSide) / 2);

            imagecopyresampled($thumb, $src, 0, 0, $srcX, $srcY, $thumbSize, $thumbSize, $minSide, $minSide);
            imagewebp($thumb, $uploadDirAvatars . 'thumb_' . $image['name'], 85);
            imagedestroy($thumb);

            // Миниатюра для каталога 16:9 (catalog_)
            $catW = 640;
            $catH = 360;
            $catalog = imagecreatetruecolor($catW, $catH);
            imagealphablending($catalog, false);
            imagesavealpha($catalog, true);

            if (($width / $height) > ($catW / $catH)) {
                $srcH = $height;
                $srcW = (int)($height * ($catW / $catH));
                $srcX = (int)(($width - $srcW) / 2);
                $srcY = 0;
            } else {
                $srcW = $width;
                $srcH = (int)($width / ($catW / $catH));
                $srcX = 0;
                $srcY = (int)(($height - $srcH) / 2);
            }

            imagecopyresampled($catalog, $src, 0, 0, $srcX, $srcY, $catW, $catH, $srcW, $srcH);
            imagewebp($catalog, $uploadDirAvatars . 'catalog_' . $image['name'], 85);
            imagedestroy($catalog);
            imagedestroy($src);
        }

        // Очистка старых файлов
        if ($currentSpecialistPhoto) {
            $cleanOldName = str_replace(['thumb_', 'catalog_'], '', $currentSpecialistPhoto);
            if (file_exists($uploadDirAvatars . $cleanOldName)) @unlink($uploadDirAvatars . $cleanOldName);
            if (file_exists($uploadDirAvatars . 'thumb_' . $cleanOldName)) @unlink($uploadDirAvatars . 'thumb_' . $cleanOldName);
            if (file_exists($uploadDirAvatars . 'catalog_' . $cleanOldName)) @unlink($uploadDirAvatars . 'catalog_' . $cleanOldName);
        }

        $final_photo = $image['name'];
        $data['photo'] = $image['name'];
    } else {
        $incomingAvatar = !empty($_POST['existing_photos'][0]) ? clean_elem_form($_POST['existing_photos'][0]) : '';

        // Рокировка из галереи в аватар
        if ($incomingAvatar && strpos($incomingAvatar, 'gallery_') !== false) {
            $sourceGalleryFile = $uploadDirGallery . $incomingAvatar;

            if (file_exists($sourceGalleryFile)) {
                $targetAvatarFullPath = $uploadDirAvatars . $incomingAvatar;
                copy($sourceGalleryFile, $targetAvatarFullPath);

                $src = @imagecreatefromwebp($targetAvatarFullPath);
                if ($src) {
                    $width = imagesx($src);
                    $height = imagesy($src);

                    // Квадратная миниатюра при рокировке
                    $thumbSize = 250;
                    $thumb = imagecreatetruecolor($thumbSize, $thumbSize);
                    imagealphablending($thumb, false);
                    imagesavealpha($thumb, true);
                    $minSide = min($width, $height);
                    $srcX = (int)(($width - $minSide) / 2);
                    $srcY = (int)(($height - $minSide) / 2);
                    imagecopyresampled($thumb, $src, 0, 0, $srcX, $srcY, $thumbSize, $thumbSize, $minSide, $minSide);
                    imagewebp($thumb, $uploadDirAvatars . 'thumb_' . $incomingAvatar, 85);
                    imagedestroy($thumb);

                    // Миниатюра 16:9 при рокировке
                    $catW = 640;
                    $catH = 360;
                    $catalog = imagecreatetruecolor($catW, $catH);
                    imagealphablending($catalog, false);
                    imagesavealpha($catalog, true);

                    if (($width / $height) > ($catW / $catH)) {
                        $srcH = $height;
                        $srcW = (int)($height * ($catW / $catH));
                        $srcX = (int)(($width - $srcW) / 2);
                        $srcY = 0;
                    } else {
                        $srcW = $width;
                        $srcH = (int)($width / ($catW / $catH));
                        $srcX = 0;
                        $srcY = (int)(($height - $srcH) / 2);
                    }

                    imagecopyresampled($catalog, $src, 0, 0, $srcX, $srcY, $catW, $catH, $srcW, $srcH);
                    imagewebp($catalog, $uploadDirAvatars . 'catalog_' . $incomingAvatar, 85);
                    imagedestroy($catalog);
                    imagedestroy($src);
                }

                if ($currentSpecialistPhoto) {
                    $cleanOldName = str_replace(['thumb_', 'catalog_'], '', $currentSpecialistPhoto);
                    if (file_exists($uploadDirAvatars . $cleanOldName)) @unlink($uploadDirAvatars . $cleanOldName);
                    if (file_exists($uploadDirAvatars . 'thumb_' . $cleanOldName)) @unlink($uploadDirAvatars . 'thumb_' . $cleanOldName);
                    if (file_exists($uploadDirAvatars . 'catalog_' . $cleanOldName)) @unlink($uploadDirAvatars . 'catalog_' . $cleanOldName);
                }

                $final_photo = $incomingAvatar;
                $data['photo'] = $incomingAvatar;
            } else {
                $final_photo = $currentSpecialistPhoto ?: '';
            }
        }
        // Удаление аватара по флагу
        else if (isset($_POST['remove_photo']) && $_POST['remove_photo'] == '1') {
            if ($currentSpecialistPhoto) {
                $cleanOldName = str_replace(['thumb_', 'catalog_'], '', $currentSpecialistPhoto);
                if (file_exists($uploadDirAvatars . $cleanOldName)) @unlink($uploadDirAvatars . $cleanOldName);
                if (file_exists($uploadDirAvatars . 'thumb_' . $cleanOldName)) @unlink($uploadDirAvatars . 'thumb_' . $cleanOldName);
                if (file_exists($uploadDirAvatars . 'catalog_' . $cleanOldName)) @unlink($uploadDirAvatars . 'catalog_' . $cleanOldName);
            }
            $final_photo = '';
        } else {
            $final_photo = $currentSpecialistPhoto ?: '';
        }
    }

    $newGallery = [];

    for ($i = 1; $i < 12; $i++) {
        $galleryIndex = $i - 1;

        if (isset($_FILES['gallery_files']['tmp_name'][$galleryIndex]) && $_FILES['gallery_files']['size'][$galleryIndex] > 0) {

            $fileArray = [
                'name'     => $_FILES['gallery_files']['name'][$galleryIndex],
                'type'     => $_FILES['gallery_files']['type'][$galleryIndex],
                'tmp_name' => $_FILES['gallery_files']['tmp_name'][$galleryIndex],
                'error'    => $_FILES['gallery_files']['error'][$galleryIndex],
                'size'     => $_FILES['gallery_files']['size'][$galleryIndex]
            ];

            fix_image_orientation($fileArray['tmp_name']);

            $uploadedGalleryImage = load_image_webp(
                $fileArray,
                'gallery',
                'gallery_' . $galleryIndex . '_' . ($data['id'] ?? uniqid()) . '_' . date('d-m-y_H-i-s'),
                60
            );

            if (isset($uploadedGalleryImage['error'])) {
                responseJson(['error' => 'Ошибка в галерее (слот ' . $i . '): ' . $uploadedGalleryImage['error']]);
            }

            $fullPath = $uploadDirGallery . $uploadedGalleryImage['name'];
            $src = @imagecreatefromwebp($fullPath);

            if ($src) {
                $width = imagesx($src);
                $height = imagesy($src);

                // Миниатюра для каталога 16:9 (catalog_)
                $catW = 640;
                $catH = 360;
                $catalog = imagecreatetruecolor($catW, $catH);
                imagealphablending($catalog, false);
                imagesavealpha($catalog, true);

                if (($width / $height) > ($catW / $catH)) {
                    $srcH = $height;
                    $srcW = (int)($height * ($catW / $catH));
                    $srcX = (int)(($width - $srcW) / 2);
                    $srcY = 0;
                } else {
                    $srcW = $width;
                    $srcH = (int)($width / ($catW / $catH));
                    $srcX = 0;
                    $srcY = (int)(($height - $srcH) / 2);
                }

                imagecopyresampled($catalog, $src, 0, 0, $srcX, $srcY, $catW, $catH, $srcW, $srcH);
                imagewebp($catalog, $uploadDirGallery . 'catalog_' . $uploadedGalleryImage['name'], 85);
                imagedestroy($catalog);
                imagedestroy($src);
            }

            $newGallery[] = $uploadedGalleryImage['name'];
        } else if (!empty($_POST['existing_photos'][$i])) {
            $newGallery[] = clean_elem_form($_POST['existing_photos'][$i]);
        }
    }

    // Удаление старых файлов галереи и их миниатюр
    foreach ($currentGallery as $oldGalleryPhoto) {
        if (!in_array($oldGalleryPhoto, $newGallery)) {
            $cleanOldName = str_replace('catalog_', '', $oldGalleryPhoto);

            if (file_exists($uploadDirGallery . $cleanOldName)) @unlink($uploadDirGallery . $cleanOldName);
            if (file_exists($uploadDirGallery . 'catalog_' . $cleanOldName)) @unlink($uploadDirGallery . 'catalog_' . $cleanOldName);
        }
    }

    $final_gallery = !empty($newGallery) ? json_encode($newGallery, JSON_UNESCAPED_UNICODE) : '';

    // Шаг 0.5: получаем текущие залы из базы (чтобы знать, какие файлы потом можно удалить)
    $currentRooms = [];
    if (!empty($data['id'])) {
        // Переиспользуем $currentSpecialist, если он уже был получен выше для аватара/галереи —
        // тогда просто добавьте 'rooms' в SELECT там, чтобы не делать второй запрос
        if (empty($currentSpecialist)) {
            $currentSpecialist = $data_base->selectRow('SELECT rooms FROM dt_specialist WHERE id = ?', [(int)$data['id']]);
        }
        if ($currentSpecialist) {
            $currentRooms = json_decode($currentSpecialist['rooms'] ?? '[]', true) ?: [];
        }
    }

    $uploadDirRooms = $_SERVER['DOCUMENT_ROOT'] . '/uploads/rooms/';
    $newRooms = [];

    if (!empty($_POST['rooms']) && is_array($_POST['rooms'])) {
        foreach ($_POST['rooms'] as $roomKey => $roomData) {
            $roomName = clean_elem_form($roomData['name'] ?? '');
            $existingPhotos = $roomData['existing_photos'] ?? [];
            $roomPhotos = [];

            for ($i = 0; $i < 4; $i++) {
                $hasNewFile = isset($_FILES['rooms']['tmp_name'][$roomKey]['photos'][$i])
                    && $_FILES['rooms']['size'][$roomKey]['photos'][$i] > 0;

                if ($hasNewFile) {
                    $fileArray = [
                        'name'     => $_FILES['rooms']['name'][$roomKey]['photos'][$i],
                        'type'     => $_FILES['rooms']['type'][$roomKey]['photos'][$i],
                        'tmp_name' => $_FILES['rooms']['tmp_name'][$roomKey]['photos'][$i],
                        'error'    => $_FILES['rooms']['error'][$roomKey]['photos'][$i],
                        'size'     => $_FILES['rooms']['size'][$roomKey]['photos'][$i],
                    ];

                    fix_image_orientation($fileArray['tmp_name']);

                    $uploadedRoomImage = load_image_webp(
                        $fileArray,
                        'rooms',
                        'room_' . $roomKey . '_' . $i . '_' . ($data['id'] ?? uniqid()) . '_' . date('d-m-y_H-i-s'),
                        60
                    );

                    if (isset($uploadedRoomImage['error'])) {
                        responseJson(['error' => 'Ошибка в зале "' . $roomName . '" (фото ' . ($i + 1) . '): ' . $uploadedRoomImage['error']]);
                    }

                    // Миниатюра 16:9 — по аналогии с галереей
                    $fullPath = $uploadDirRooms . $uploadedRoomImage['name'];
                    $src = @imagecreatefromwebp($fullPath);

                    if ($src) {
                        $width = imagesx($src);
                        $height = imagesy($src);

                        $catW = 640;
                        $catH = 360;
                        $catalog = imagecreatetruecolor($catW, $catH);
                        imagealphablending($catalog, false);
                        imagesavealpha($catalog, true);

                        if (($width / $height) > ($catW / $catH)) {
                            $srcH = $height;
                            $srcW = (int)($height * ($catW / $catH));
                            $srcX = (int)(($width - $srcW) / 2);
                            $srcY = 0;
                        } else {
                            $srcW = $width;
                            $srcH = (int)($width / ($catW / $catH));
                            $srcX = 0;
                            $srcY = (int)(($height - $srcH) / 2);
                        }

                        imagecopyresampled($catalog, $src, 0, 0, $srcX, $srcY, $catW, $catH, $srcW, $srcH);
                        imagewebp($catalog, $uploadDirRooms . 'catalog_' . $uploadedRoomImage['name'], 85);
                        imagedestroy($catalog);
                        imagedestroy($src);
                    }

                    $roomPhotos[] = $uploadedRoomImage['name'];
                } else if (!empty($existingPhotos[$i])) {
                    $roomPhotos[] = clean_elem_form($existingPhotos[$i]);
                }
            }

            // Полностью пустой зал (без имени и без единой фотографии) не сохраняем
            if ($roomName === '' && empty($roomPhotos)) {
                continue;
            }

            $newRooms[] = [
                'name'   => $roomName,
                'photos' => $roomPhotos,
            ];
        }
    }

    // Удаление файлов тех фото залов, которых больше нет ни в одном зале
    $oldRoomPhotosFlat = [];
    foreach ($currentRooms as $oldRoom) {
        foreach (($oldRoom['photos'] ?? []) as $oldPhoto) {
            $oldRoomPhotosFlat[] = $oldPhoto;
        }
    }

    $newRoomPhotosFlat = [];
    foreach ($newRooms as $room) {
        foreach ($room['photos'] as $photo) {
            $newRoomPhotosFlat[] = $photo;
        }
    }

    foreach ($oldRoomPhotosFlat as $oldPhoto) {
        if (!in_array($oldPhoto, $newRoomPhotosFlat)) {
            if (file_exists($uploadDirRooms . $oldPhoto)) @unlink($uploadDirRooms . $oldPhoto);
            if (file_exists($uploadDirRooms . 'catalog_' . $oldPhoto)) @unlink($uploadDirRooms . 'catalog_' . $oldPhoto);
        }
    }

    $final_rooms = !empty($newRooms) ? json_encode($newRooms, JSON_UNESCAPED_UNICODE) : '';

    // Компания
    $company_name = !empty($data['company_name'])
        ? html_entity_decode($data['company_name'], ENT_QUOTES)
        : null;

    // Удаление компании, если нужно
    if (isset($data['id']) && (int)$data['id'] > 0) {

        $specialist_id = (int)$data['id'];

        if (isset($data['remove_company']) && $data['remove_company']) {
            $data_base->update('UPDATE dt_specialist SET company_id = NULL WHERE id = ?', [$specialist_id]);
        }

        if (!isset($data['remove_company']) && isset($data['company_name']) && isset($data['company_classification_id']) && !empty($data['company_classification_id'])) {
            try {
                if (!empty($data['company_id'])) {
                    // Если имя компании изменилось, update_company вернет true
                    $isNameChanged = update_company($company_name, (int) $data['company_classification_id'], (int) $data['company_id']);
                    if ($isNameChanged) {
                        $data['_company_name_changed'] = true; // Запоминаем факт изменения названия
                    }
                    $company_id = (int) $data['company_id'];
                } else {
                    // Если привязывается новая компания, слаг железно должен перегенерироваться под неё
                    $company_id = set_company($company_name, (int) $data['company_classification_id'], $specialist_id);
                    $data['_company_name_changed'] = true;
                }
            } catch (Exception $e) {
                $json['fatal'] = $e->getMessage();
                responseJson($json);
            }
        }
    }

    // Получаем исходный список сотрудников компании (до обновления)
    $originalEmployees = $data_base->selectAll('SELECT id FROM dt_company_employee WHERE company_id = ?', [$company_id]);

    $hasEmployeePhotos = !empty($_FILES['employees']);
    $hasEmployeesData = isset($data['employees']) && !empty($data['employees']);

    if (!isset($data['remove_company']) && $hasEmployeePhotos || $hasEmployeesData && !empty($hasEmployeesData)) {
        foreach ($data['employees'] as $employeeId => $employee) {
            if (empty($employee['name']) || empty($employee['post'])) {
                continue;
            }

            $currentEmployee = $data_base->selectRow(
                'SELECT id, photo FROM dt_company_employee WHERE id = ? AND company_id = ?',
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
            $data_base->delete('DELETE FROM dt_company_employee WHERE id = ? AND company_id = ?', [$emp['id'], $company_id]);
        }
    }

    $data['schedule'] = htmlspecialchars_decode($data['schedule']);

    $existing = $data_base->selectRow(
        'SELECT document FROM dt_specialist_description WHERE specialist_id = ?',
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
        // Декодируем JSON
        $postedServices = json_decode($_POST['services'], true);

        if (is_array($postedServices)) {
            foreach ($postedServices as $service) {
                if (!empty($service['name']) && isset($service['price'])) {
                    $services[] = [
                        'name' => clean_elem_form($service['name']),
                        'price' => intval(str_replace("'", '', $service['price'])),
                        'is_active' => !empty($service['is_active']) ? 1 : 0,
                    ];
                }
            }
        }
    }

    // Если форма пришла, но услуг нет — массив пустой, это корректно для удаления всех услуг
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
        'classification_id' => $data['classification_id'],
        'city_id' => $data['city_id'],
        'metro' => $data['metro'],
        'meta_title' => $data['meta_title'] ?? null,
        'meta_description' => $data['meta_description'] ?? null,
        'meta_keywords' => $data['meta_keywords'] ?? null,
        'company_id' => $company_id ?? null,
        'company_classification_id' => $data['company_classification_id'],
        'company_name' => $company_name,
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
        '_company_name_changed' => $data['_company_name_changed'] ?? false,
        'remove_company' => $data['remove_company'] ?? 0,
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
        'classification_id' => $data['classification_id'],
        'city_id' => $data['city_id'],
        'metro' => $data['metro'],
        'meta_title' => $data['meta_title'] ?? null,
        'meta_description' => $data['meta_description'] ?? null,
        'meta_keywords' => $data['meta_keywords'] ?? null,
        'company_id' => $company_id ?? null,
        'company_classification_id' => $data['company_classification_id'],
        'company_name' => $company_name,
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
        '_company_name_changed' => $data['_company_name_changed'] ?? false,
        'remove_company' => $data['remove_company'] ?? 0,
    ];

    try {
        // Пробуем добавить или обновить специалиста
        $specialist = $data['id'] ? update_specialist($data) : set_specialist($setData);

        $specialistId = $data['id'] ? (int)$data['id'] : (int)$specialist;

        if ($specialistId) {
            try {
                set_user_avatar($final_photo ?? '', $specialistId);
                set_user_gallery($final_gallery ?? '', $specialistId);
                set_user_rooms($final_rooms ?? '', $specialistId);
            } catch (Exception $e) {
                error_log('Error updating avatar, gallery or rooms in admin: ' . $e->getMessage());
            }
        }

        if (!empty($data['specializations'])) {
            try {
                update_specialist_specializations($data['id'], $data['specializations']);
            } catch (Exception $e) {
                error_log('Error adding specializations: ' . $e->getMessage());
            }
        } else {
            unset_specialist_specializations($data['id']);
        }

        if (!empty($data['services'])) {
            try {
                update_specialist_services($data['id'], $data['services']);
            } catch (Exception $e) {
                error_log('Error updating services: ' . $e->getMessage());
            }
        } else {
            unset_specialist_services($data['id']);
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

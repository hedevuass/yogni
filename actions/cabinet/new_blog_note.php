<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

$forbidden_words = getForbiddenWords();
$specialist = get_specialist($_SESSION['user']['id']);

function fix_image_orientation($file_path) {
    if (!function_exists('exif_read_data')) {
        return; // Если на сервере не включено расширение EXIF, пропускаем
    }

    // Читаем метаданные только у файлов JPG/JPEG
    $mime = mime_content_type($file_path);
    if ($mime !== 'image/jpeg') {
        return; 
    }

    $exif = @exif_read_data($file_path);
    if (empty($exif['Orientation'])) {
        return; // Ориентация нормальная или тега нет
    }

    $orientation = $exif['Orientation'];
    if ($orientation <= 1) {
        return; // Поворот не требуется
    }

    // Загружаем исходник в память для вращения
    $image = imagecreatefromjpeg($file_path);
    if (!$image) return;

    $degrees = 0;
    switch ($orientation) {
        case 3: // Перевернуто вверх ногами (180°)
            $degrees = 180;
            break;
        case 6: // Повернуто на 90° по часовой стрелке
            $degrees = -90; // или 270
            break;
        case 8: // Повернуто на 90° против часовой стрелки
            $degrees = 90;
            break;
    }

    if ($degrees !== 0) {
        // Физически вращаем картинку
        $rotated = imagerotate($image, $degrees, 0);
        if ($rotated) {
            // Перезаписываем временный файл уже в правильном положении
            imagejpeg($rotated, $file_path, 95); 
            imagedestroy($rotated);
        }
    }
    
    imagedestroy($image);
}

if (isset($_POST['blog_id']) && isset($_POST['is_edit'])) {

    // Переопределяем статью, если это редактирование, чтобы управлять файлами
    $article = null;
    if ($_POST['is_edit'] !== 'false') {
        $article = get_article_cabinet(intval($_POST['is_edit']));
    }

    // Сохраняем оригинальные данные для чистой валидации (до экранирования)
    $raw_content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $raw_title = isset($_POST['title']) ? trim($_POST['title']) : '';

    $normalized_content = html_entity_decode($raw_content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $normalized_title   = html_entity_decode($raw_title,   ENT_QUOTES | ENT_HTML5, 'UTF-8');

    $data = [];
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    $blog = get_blog_by_id(intval($data['blog_id']))['specialist_id'];

    if (intval($blog) == intval($_SESSION['user']['id'])) {
        $json = [];

        // --- ВАЛИДАЦИЯ КОНТЕНТА (проверяем по $raw_content) ---
        if (!$raw_content) {
            $json['error']['content'] = 'Введите текст записи.';
        } else {
            $security_check_content = preg_replace('/[\t\r\n]+/', '', $normalized_content);
            // 1. Проверка на опасный HTML (XSS)
            $dangerous_html = '/<(script|iframe|object|embed|form|meta|link|a)\b[^>]*>|on\w+\s*=|javascript:/i';
            if (preg_match($dangerous_html, $security_check_content)) {
                $json['error']['content'] = 'Текст содержит недопустимые или опасные HTML-теги (скрипты, фреймы).';
            }

            // 2. Проверка на любые ссылки (http/https)
            if (preg_match('/https?:\/\/[^\s"\'<>]+/i', $security_check_content)) {
                $json['error']['content'] = 'Вставка ссылок в текст записи запрещена.';
            }

            // 3. Проверка на запрещенные слова (ищем в тексте без тегов)
            $clean_content_text = mb_strtolower(strip_tags($normalized_content), 'UTF-8');
            foreach ($forbidden_words as $word) {
                $trimmed_word = trim($word);
                if (empty($trimmed_word)) continue;

                if (in_array($trimmed_word, ['<', '>', '&lt;', '&gt;'], true)) {
                    continue;
                }

                $quoted_word = preg_quote(mb_strtolower($word, 'UTF-8'), '/');
                if (preg_match('/(?<!\p{L})' . $quoted_word . '(?!\p{L})/iu', $clean_content_text)) {
                    $json['error']['content'] = 'Текст содержит запрещенное слово: "' . htmlspecialchars($word) . '".';
                    break;
                }
            }

            // 4. Проверка длины строки (по исходному тексту, чтобы сущности &lt; не раздували длину)
            if (strlen(str_replace(' ', '', $raw_content)) > 25000) {
                $json['error']['content'] = 'Текст записи слишком большой.';
            }
        }

        // --- ВАЛИДАЦИЯ ЗАГОЛОВКА (проверяем по $raw_title) ---
        if (empty($raw_title)) {
            $json['error']['title'] = 'Введите заголовок записи.';
        } else {
            $security_check_title = preg_replace('/[\t\r\n]+/', '', $normalized_title);
            // 1. В заголовке строго запрещен ЛЮБОЙ HTML
            if (preg_match('/<[^>]+>/', $normalized_title)) {
                $json['error']['title'] = 'Заголовок не должен содержать HTML-теги.';
            }
            
            // 2. В заголовке запрещены ссылки
            if (preg_match('/https?:\/\/[^\s"\'<>]+/i', $security_check_title)) {
                $json['error']['title'] = 'Заголовок не должен содержать ссылки.';
            }

            // 3. Проверка заголовка на запрещенные слова
            $clean_title = mb_strtolower($normalized_title, 'UTF-8');
            foreach ($forbidden_words as $word) {
                if (empty(trim($word))) continue;
                $quoted_word = preg_quote(mb_strtolower($word, 'UTF-8'), '/');
                if (preg_match('/(?<!\p{L})' . $quoted_word . '(?!\p{L})/iu', $clean_title)) {
                    $json['error']['title'] = 'Заголовок содержит запрещенное слово: "' . htmlspecialchars($word) . '".';
                    break;
                }
            }

            // Генерируем алиас (используем уже безопасный $data['title'])
            if (!isset($json['error']['title'])) {
                $data['alias'] = generateAlias($data['title']);
                if ($data['alias'] == '') {
                    $json['error']['title'] = 'Заголовок записи не может быть конвертирован в алиас. Придумайте другой заголовок.';
                }
            }
        }

        $data['theme_id'] = intval($data['theme']);
        if ($data['theme'] == 0) {
            $json['error']['theme'] = 'Выберите раздел записи.';
        }

        if (isset($json['error'])) {
            responseJson($json);
            exit;
        }

        $data['blog_id'] = intval($data['blog_id']);
        $data['post_type'] = 'blogs';
        $data['author_id'] = intval(get_blog_by_id($data['blog_id'])['specialist_id']);
        $data['keywords'] = '';

        /* ==========================================================================
           ЛОГИКА ОБРАБОТКИ ОБЛОЖКИ ЗАПИСИ (ФОТОГРАФИИ)
           ========================================================================== */

        $allow = array('jpg', 'jpeg', 'png', 'heic', 'jfif');
        $deny = array('phtml', 'php', 'php3', 'php4', 'php5', 'php6', 'php7', 'phps', 'cgi', 'pl', 'asp', 'aspx', 'shtml', 'shtm', 'htaccess', 'htpasswd', 'ini', 'log', 'sh', 'js', 'html', 'htm', 'css', 'sql', 'spl', 'scgi', 'fcgi', 'exe');

        $data['photo'] = '';
        $folder = 'articles';
        $uploadFileDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $folder . '/';

        // 1. Проверяем скрытое поле старой картинки (если редактируем и не меняли её)
        if (!empty($_POST['existing_main_image'])) {
            $data['photo'] = clean_elem_form($_POST['existing_main_image']);
        }

        // 2. Если загружен новый файл обложки через input
        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['main_image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            // Валидация расширения файла по вашим глобам
            if (!in_array($ext, $allow) || in_array($ext, $deny)) {
                $json['error']['main_image'] = 'Допустимы только jpeg, jpg, png, heic, jfif.'; // выведем ошибку
                responseJson($json);
            }

            // Валидация веса (до 5 МБ)
            if ($file['size'] > 5 * 1024 * 1024) {
                $json['error']['main_image'] = 'Файл обложки слишком большой (максимум 5 МБ).';
                responseJson($json);
            }

            // Генерируем базовое имя для файла (load_image_webp сам добавит .webp в конец)
            $userId = intval($_SESSION['user']['id']);
            $name_prefix = 'article_' . $userId . '_' . date('d-m-y_H-i-s');
            
            $imageResult = false;

            // Обработка формата HEIC (как в вашем примере)
            if (class_exists('HeicToJpg') && HeicToJpg::isHeic($file['tmp_name'])) {
                $jpgName = $name_prefix . '.jpg';
                $jpgPath = $uploadFileDir . $jpgName;

                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }

                if (HeicToJpg::convert($file['tmp_name'])->saveAs($jpgPath)) {
                    fix_image_orientation($jpgPath);

                    // Вызываем загрузчик для временного конвертированного JPG с качеством 75
                    $imageResult = load_image_webp([
                        'name' => $jpgName,
                        'tmp_name' => $jpgPath,
                        'size' => filesize($jpgPath),
                        'type' => 'image/jpeg'
                    ], $folder, $name_prefix, 5, [], [], 75, 1200);

                    @unlink($jpgPath); // Чистим временный JPG
                } else {
                    $json['error']['main_image'] = 'Не удалось конвертировать HEIC → JPG.';
                    responseJson($json);
                }
            } else {
                // Стандартный файл (jpeg, png, jfif)
                fix_image_orientation($file['tmp_name']);
                
                // Вызываем ваш загрузчик с качеством 75
                $imageResult = load_image_webp($file, $folder, $name_prefix, 5, [], [], 75, 1200);
            }

            // Проверяем статус ответа от load_image_webp
            if (isset($imageResult['error'])) {
                $json['error']['main_image'] = $imageResult['error'];
                responseJson($json);
            }

            if (isset($imageResult['name'])) {
                // Удаляем старую картинку с диска, если она заменяется новой при редактировании
                if ($article && !empty($article['photo']) && file_exists($uploadFileDir . $article['photo'])) {
                    @unlink($uploadFileDir . $article['photo']);
                }
                // Записываем новое имя .webp файла для сохранения в базу данных
                $data['photo'] = $imageResult['name'];
            }

        } else {
            // 3. Если файл не прислан и скрытое поле пустое — пользователь нажал «Удалить фото»
            if (empty($_POST['existing_main_image']) && $article && !empty($article['photo'])) {
                if (file_exists($uploadFileDir . $article['photo'])) {
                    @unlink($uploadFileDir . $article['photo']);
                }
                $data['photo'] = ''; // В БД запишется пустая строка
            }
        }

        // Сохраняем чистый HTML-текст для email уведомлений (без html_entities)
        $original_content = strip_tags($data['content']);

        // Запись или обновление данных в БД (ключ 'photo' будет передан внутри $data)
        if (empty($json['error'])) {
            $data['content'] = htmlspecialchars($normalized_content, ENT_QUOTES, 'UTF-8');
            $data['title']   = $normalized_title; // в заголовке HTML запрещён полностью, кодировать не нужно
        
            if ($data['is_edit'] == 'false') {
                $article_id = intval(create_article($data));
            } else {
                $article_id = intval($data['is_edit']);
                update_article($article_id, $data);
            }
        }

        $json['success'] = $article_id;

        // Отправка уведомления
        $notify_data = [
            'type'  => 'post',
            'date'  => date('d.m.Y'),
            'name'  => !empty($specialist['company']) ? $specialist['company'] : trim(($specialist['name'] ?? '') . ' ' . ($specialist['lastname'] ?? '')),
            'title' => $data['title'],
            'link'  => 'https://yogni.ru/mukadhoha96/posts',
            'text'  => $original_content
        ];

        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($notify_data),
                'timeout' => 5
            ]
        ];

        $context  = stream_context_create($opts);
        @file_get_contents($_SERVER['HTTP_ORIGIN'] . '/actions/mail/note_notification.php', false, $context);

        responseJson($json);
    }
}

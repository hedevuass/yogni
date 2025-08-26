<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$response = []; // Используем $response для ответа

if (isset($_POST['id'])) {
    $data = [];

    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    // Добавляем данные в ответ для отладки
    $response['debug'] = [
        'received_data' => $data
    ];

    if (!isset($data['title']) || !$data['title']) {
        $response['error']['title'] = 'Укажите название';
    }

    if (empty($data['alias']) && !empty($data['title'])) {
        $cleanedTitle = strip_tags(htmlspecialchars_decode($data['title']));
        $data['alias'] = generateAlias($cleanedTitle);
    }

    if (empty($data['meta-title']) && !empty($data['title'])) {
        $data['meta-title'] = strip_tags(htmlspecialchars_decode($data['title']));
    }

    // Если meta-description пустой, берем значение из content
    if (empty($data['meta-description']) && !empty($data['content'])) {
        $data['meta-description'] = strip_tags(htmlspecialchars_decode($data['content']));
    }

    if ($data['id'] !== '') {
        $service = get_post($data['id']);
        $service_img = $service['photo'];
        $service_icon = $service['icon']; // Получаем текущее значение иконки
    }

    // Обработка изображения photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/articles/';
        $fileName = basename($_FILES['photo']['name']);
        $filePath = $uploadDir . $fileName;
    
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
            $data['photo'] = $fileName;
        } else {
            $response['error']['photo'] = 'Ошибка при загрузке изображения';
        }
    } else {
        if (isset($service_img)) {
            $data['photo'] = $service_img;
        }
    }

    if (isset($response['error'])) {
        responseJson($response);
        exit;
    }

    try {
        if (isset($data['id']) && $data['id'] !== '') {
            update_journal($data);
            $article_id = $data['id'];
            $response['success'] = 'Сервис обновлён';
        } else {
            $article_id = add_journal($data);
            $data['id'] = $article_id;
            $response['success'] = 'Сервис добавлен';
        }

        // Обработка тега, если поле tags_title передано
        if (isset($data['tags_title']) && !empty($data['tags_title'])) {
            $tags_title = $data['tags_title'];

            // Проверяем наличие тега с таким title в таблице dt_tags
            $tag = $data_base->selectRow("SELECT * FROM dt_tags WHERE title = ?", [$tags_title]);
            if (!$tag) {
                // Если тега нет, генерируем alias и добавляем новую запись
                $tag_alias = generateAlias($tags_title);
                $data_base->add("INSERT INTO dt_tags (title, alias) VALUES (?, ?)", [$tags_title, $tag_alias]);
                // Получаем добавленный тег
                $tag = $data_base->selectRow("SELECT * FROM dt_tags WHERE title = ?", [$tags_title]);
            }
            $tag_id = $tag['id'];

            // Обновляем связи: удаляем старые записи для этой статьи, затем вставляем новую связь
            $data_base->delete("DELETE FROM dt_post_tags WHERE article_id = ?", [$article_id]);
            $data_base->add("INSERT INTO dt_post_tags (tag_id, article_id) VALUES (?, ?)", [$tag_id, $article_id]);
        }
    } catch (Exception $e) {
        $response['fatal'] = 'Редактирование временно недоступно. Ошибка: ' . $e->getMessage();
    }

    responseJson($response);
}

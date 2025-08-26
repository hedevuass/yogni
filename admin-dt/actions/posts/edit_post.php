<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

$response = []; // Используем $response для ответа

if (isset($_POST['id'])) {
    global $data_base;
    $data = [];

    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    if (!isset($data['title']) || !$data['title']) {
        $response['error']['title'] = 'Укажите название';
    }

    if (empty($data['meta-title']) && !empty($data['title'])) {
        $data['meta-title'] = strip_tags(htmlspecialchars_decode($data['title']));
    }

    // Если meta-description пустой, берем значение из content
    if (empty($data['meta-description']) && !empty($data['content'])) {
        $data['meta-description'] = strip_tags(htmlspecialchars_decode($data['content']));
    }

    if (empty($data['alias']) && !empty($data['title'])) {
        $cleanedTitle = strip_tags(htmlspecialchars_decode($data['title']));
        $data['alias'] = generateAlias($cleanedTitle);
    }

    // Устанавливаем ID поста (для обновления используем переданный ID, для добавления будет присвоен позже)
    $article_id = isset($data['id']) && $data['id'] !== '' ? $data['id'] : null;

    if (isset($response['error'])) {
        responseJson($response);
        exit;
    }

    try {
        if ($article_id) {
            update_post($data);
            $response['success'] = 'Сервис обновлён';
        } else {
            $article_id = add_journal($data);
            if (!$article_id) {
                throw new Exception('Не удалось добавить пост');
            }
            $data['id'] = $article_id;
            $response['success'] = 'Сервис добавлен';
        }

        // Удаляем старые связи тегов для статьи
        $data_base->delete("DELETE FROM dt_post_tags WHERE article_id = ?", [$article_id]);

        // Обработка тегов, если поле hashtag передано
        if (isset($data['tags_title']) && !empty($data['tags_title'])) {
            $tags = array_map('trim', explode(',', $data['tags_title']));
            foreach ($tags as $tag_title) {
                if (empty($tag_title)) {
                    continue;
                }

                // Проверяем наличие тега с таким названием
                $tag = $data_base->selectRow("SELECT id FROM dt_tags WHERE title = ?", [$tag_title]);
                if (!$tag) {
                    // Если тега нет, генерируем alias и добавляем новую запись
                    $tag_alias = generateAlias($tag_title);
                    $data_base->add("INSERT INTO dt_tags (title, alias) VALUES (?, ?)", [$tag_title, $tag_alias]);
                    // Получаем добавленный тег
                    $tag = $data_base->selectRow("SELECT id FROM dt_tags WHERE title = ?", [$tag_title]);
                }

                // Связываем тег с постом
                $data_base->add("INSERT INTO dt_post_tags (tag_id, article_id) VALUES (?, ?)", [$tag['id'], $article_id]);
            }
        }

        responseJson($response);
    } catch (Exception $e) {
        $response['fatal'] = 'Редактирование временно недоступно. Ошибка: ' . $e->getMessage();
        responseJson($response);
    }
}

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();

$forbidden_words = getForbiddenWords();

$specialist = get_specialist($_SESSION['user']['id']);

if (isset($_POST['blog_id']) && isset($_POST['is_edit'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }
    $blog = get_blog_by_id(intval($data['blog_id']))['specialist_id'];
    if (intval($blog) == intval($_SESSION['user']['id'])) {
        if (!$data['content']) {
            $json['error']['content'] = 'Введите текст записи.';
        } else if (strlen(str_replace(' ', '', $data['content'])) > 15000) {
            $json['error']['content'] = 'Текст записи слишком большой. Максимально допустимое кол-во символов: 15000.';
        }

        if (empty($data['title'])) {
            $json['error']['title'] = 'Введите заголовок записи.';
        }
        if (!empty($data['title'])) {
            $data['alias'] = generateAlias($data['title']);
            if ($data['alias'] == '') {
                $json['error']['title'] = 'Заголовок записи не может быть конвертирован в алиас. Придумайте другой заголовок.';
            }
        }

        /* if ($found = containsForbiddenWords($data['content'], $forbidden_words)) {
            $json['error']['content'] = "Текст записи содержит запрещённое слово: \"$found\".";
        }
        if ($found = containsForbiddenWords($data['title'], $forbidden_words)) {
            $json['error']['title'] = "Заголовок содержит запрещённое слово: \"$found\".";
        }
        if ($found = containsForbiddenWords($data['keywords'], $forbidden_words)) {
            $json['error']['keywords'] = "Ключевые слова содержат запрещённое слово: \"$found\".";
        } */

        // if(!$data['keywords']) {
        //     $json['error']['keywords'] = 'Введите ключевые слова записи.';
        // } else if (str_word_count($data['keywords']) > 4) {
        //     $json['error']['keywords'] = 'Ключевых слов не может быть больше 4.';
        // }
        $data['theme_id'] = intval($data['theme']);
        if ($data['theme'] == 0) {
            $json['error']['theme'] = 'Выберите раздел записи.';
        }

        if (isset($json['error']))
            responseJson($json);

        $data['blog_id'] = intval($data['blog_id']);
        $data['post_type'] = 'blogs';
        $data['author_id'] = intval(get_blog_by_id($data['blog_id'])['specialist_id']);
        // $keywords = str_word_count($data['keywords'], 2);
        // $data['keywords'] = implode(', ', $keywords);
        $data['keywords'] = '';

        if ($data['is_edit'] == 'false') {
            $article_id = intval(create_article($data));
        } else {
            $article_id = intval($data['is_edit']);
            update_article($article_id, $data);
        }
        $json['success'] = $article_id;

        $notify_data = [
            'type'  => 'post',
            'date'  => date('d.m.Y'),
            'name'  => !empty($specialist['company']) ? $specialist['company'] : trim(($specialist['name'] ?? '') . ' ' . ($specialist['lastname'] ?? '')),
            'title' => $data['title'],
            'link'  => 'https://urfaq.ru/admin-dt/posts ',
            'text'  => $data['content']
        ];
        
        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($notify_data),
                'timeout' => 5 // сек
            ]
        ];
        
        $context  = stream_context_create($opts);
        $response = file_get_contents($_SERVER['HTTP_ORIGIN'] . '/actions/mail/note_notification.php', false, $context);

        responseJson($json);
    }
}
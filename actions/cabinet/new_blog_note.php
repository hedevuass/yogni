<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();
if (isset($_POST['blog_id']) && isset($_POST['is_edit'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }
    $blog = get_blog_by_id(intval($data['blog_id']))['specialist_id'];
    if (intval($blog) == intval($_SESSION['user']['id'])) {
        if(!$data['content']) {
            $json['error']['content'] = 'Введите текст записи.';
        } else if (strlen(str_replace(' ', '', $data['content'])) > 15000) {
            $json['error']['content'] = 'Текст записи слишком большой. Максимально допустимое кол-во символов: 15000.';
        }

        if(!$data['title']) {
            $json['error']['title'] = 'Введите Заголовок записи.';
        } else if (strlen($data['title']) > 128) {
            $json['error']['title'] = 'Заголовок записи слишком большой.';
        }

        // if(!$data['keywords']) {
        //     $json['error']['keywords'] = 'Введите ключевые слова записи.';
        // } else if (str_word_count($data['keywords']) > 4) {
        //     $json['error']['keywords'] = 'Ключевых слов не может быть больше 4.';
        // }
        $data['theme'] = intval($data['theme']);
        if($data['theme'] == 0) {
            $json['error']['theme'] = 'Выберите раздел записи.';
        }

        if (isset($json['error'])) responseJson($json);

        $data['blog_id'] = intval($data['blog_id']);
        // $keywords = str_word_count($data['keywords'], 2);
        // $data['keywords'] = implode(', ', $keywords);
        $data['keywords'] = '';

        if($data['is_edit'] == 'false') {
            $article_id = intval(create_article($data));
            if ($article_id) {
                set_article_theme($article_id, $data['theme']);
            }
        } else {
            $article_id = intval($data['is_edit']);
            unset_article_themes($article_id);
            set_article_theme($article_id, $data['theme']);
            update_article($article_id, $data);
        }
        $json['success'] = $article_id;

        responseJson($json);
    }
}
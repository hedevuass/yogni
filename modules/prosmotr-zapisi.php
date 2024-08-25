<?php
$article_id = end(explode('/', $_SERVER['REQUEST_URI']));

// Получаем поста
$article = get_article_cabinet($article_id);

// Если поста нет делаем редирект на главную
if (!$article) responseRedirect('/');

$blog_id = intval($article['blog_id']);
$blog = get_blog_by_id($blog_id);

// Описание
$description = nl2br($blog['description']);

$data = array();

$data['article'] = block_article_prosmotr($article);

// Устанавливаем title
$HTML = html_replace([
    'title' => $article['title'] . ' - Просмотр записи ЮРФАК'
], $HTML);
$HTML = html_explode($data, $HTML);
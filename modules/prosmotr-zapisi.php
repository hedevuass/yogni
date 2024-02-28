<?php

$article_id = end(explode('/', $_SERVER['REQUEST_URI']));

// Получаем поста
$article = get_article($article_id);

// Если поста нет делаем редирект на главную
if (!$article) responseRedirect('/');

$blog_id = intval($article['blog_id']);
$blog = get_blog_by_id($blog_id);

//Добавляем просмотр
add_article_views($article['id']);

$specialist = get_specialist($blog['specialist_id']);

$title = 'Блог ';

// классификация специалиста и хлебные крошки
if ($specialist['classification_id'] == 1) {
    $title .= 'юриста ';
} else if ($specialist['classification_id'] == 2) {
    $title .= 'адвоката ';
} else if ($specialist['classification_id'] == 3) {
    $title .= 'эксперта ';
} else if ($specialist['classification_id'] == 4) {
    $title .= 'нотариуса ';
} else if ($specialist['classification_id'] == 5) {
    $title .= 'юридической компании ';
} else if ($specialist['classification_id'] == 6) {
    $title .= 'коллегии адвокатов ';
} else if ($specialist['classification_id'] == 7) {
    $title .= 'адвокатского бюро ';
} else if ($specialist['classification_id'] == 8) {
    $title .= 'экспертного учреждения ';
}
// Если компания
if ($specialist['company']) {
    $name = $specialist['company'];
}

// Если частное лицо
if (!$specialist['company']) {
    $name = $specialist['name'] . ' ' . $specialist['lastname'];
}
$title .= '"' . $name . '"';
$data['breadcrumbs'] .= '<span>/</span><a href="/journal/id' . $specialist['id'] . '">' . $title . '</a><span>/</span><span class="curr-page">' . $article['title'] . '</span>';

// Описание
$description = nl2br($blog['description']);

$data['blog'] = '<div class="top-text">
<h1>' . $title . '</h1>
<p class="blog-page">' . $description . '</p>
</div>
<a href="/journal/id' . $specialist['id'] . '" class="articles__back top">ВСЕ ЗАПИСИ АВТОРА</a>';

$data['article'] = block_articles_prosmotr([$article], true, $blog_id);

// Устанавливаем title
$HTML = html_replace([
    'title' => $article['title'] . ' - Блог ЮРФАК'
], $HTML);

$HTML = html_explode($data, $HTML);
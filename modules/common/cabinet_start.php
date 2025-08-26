<?php

$uploadDirIcon = $_SERVER['DOCUMENT_ROOT'] . '/uploads/fav-icon/';
$faviconPath = '';
$faviconType = '';

if (file_exists($uploadDirIcon . 'favicon.svg')) {
    $faviconPath = '/uploads/fav-icon/favicon.svg';
    $faviconType = 'image/svg+xml';
} elseif (file_exists($uploadDirIcon . 'favicon.png')) {
    $faviconPath = '/uploads/fav-icon/favicon.png';
    $faviconType = 'image/png';
}

$requestion = explode('/', $_SERVER['REQUEST_URI'])[2] ?? '';
$alias = strtok($requestion, '?') ?: '';

$url_parts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$last_part = strtok(end($url_parts), '?') ?: '';

$page = get_cabinet_page($alias);

// Если страница найдена и её title — "Часто задаваемые вопросы"
if ($page['title'] === 'Часто задаваемые вопросы') {
    $faq_meta = get_faq_meta($last_part);
    // Если в БД есть мета-данные для FAQ, используем их, иначе оставляем стандартные
    if (!empty($faq_meta['meta_title'])) {
        $page['title'] = $faq_meta['meta_title'];
        $page['description'] = $faq_meta['meta_description'];
    }
}

// Заменяем мета-теги в HTML
$HTML = str_replace('<!--url_title-->', htmlspecialchars($page['title']), $HTML);
$HTML = str_replace('<!--description-->', htmlspecialchars($page['description']), $HTML);
$HTML = str_replace('<!--keywords-->', htmlspecialchars($page['keywords']), $HTML);
$HTML = str_replace('<!--favicon-->', htmlspecialchars($faviconPath), $HTML);
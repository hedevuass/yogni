<?php

$data['blog_id_input'] = '';
$data['articles_more_btn'] = '';
$data['articles'] = '';
$data['info_blog'] = '';

// Получаем блог и выводим последние записи
$blog = get_specialist_blog($_SESSION['user']['id']);
if (!$blog) {
    // Если блога нет — создаём его
    add_blog($_SESSION['user']['id']);
    $blog = get_specialist_blog($_SESSION['user']['id']); // Получаем снова после добавления
}
if ($blog) {
    $blog_id = $blog['id'];
    $data['blog_id_input'] = '<input type="hidden" name="blog_id" value="' . $blog_id . '">';
    $articles = get_blog_articles($blog_id, 0, 5, 0, [0, 1, 2]);
    $data['articles'] = block_articles_cabinet($articles, true);
    $articles_count_all = count_blog_articles($blog_id, [0, 1, 2]);
    if ($articles_count_all > 5) {
        $data['articles_more_btn'] = '<button class="more-articles">Больше записей</button>';
    }

    $articles_count = count_blog_articles($blog_id);

    $views_db = count_blog_views($blog_id);
    $views_count = $views_db[0]['SUM(views)'] ?? '0';

    $user = get_specialist($_SESSION['user']['id']);
    $rating = is_null($user['rating']) ? '0' : intval($user['rating']);

    $data['info_blog'] .= <<<EOF
<!-- <div>
    <div class="stat-info">{$rating}<span class="red">-0</span></div>
    <div class="stat-descr">В рейтинге</div>
</div>
<div>
    <div class="stat-info">{$articles_count}<span class="green">+0</span></div>
    <div class="stat-descr">Записей</div>
</div>
<div>
    <div class="stat-info">{$views_count}<span class="green">+0</span></div>
    <div class="stat-descr">Просмотров</div>
</div> -->
EOF;

$data['info_blog_mobile'] .= <<<EOF
<!-- <div>
    <div class="stat-info">{$rating}<span class="red">-0</span></div>
    <div class="stat-descr">В рейтинге</div>
</div>
<div class="middle-block">
    <div class="stat-info">{$articles_count}<span class="green">+0</span></div>
    <div class="stat-descr">Записей</div>
</div>
<div>
    <div class="stat-info">{$views_count}<span class="green">+0</span></div>
    <div class="stat-descr">Просмотров</div>
</div> -->
EOF;
}



$HTML = html_explode([
    'articles' => $data['articles'],
    'blog_id_input' => $data['blog_id_input'],
    'articles_more_btn' => $data['articles_more_btn'],
    'info_blog' => $data['info_blog'],
    'info_blog_mobile' => $data['info_blog_mobile']
], $HTML);


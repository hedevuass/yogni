<?php

$data = [];

// Обрабатываем список актуальных разделов
$data = get_actual_services();

// Обрабатываем список юристов и адвокатов
$lawyers = get_specialists_by_classification([1, 2, 5, 6], 8);
$data['index_lawyers'] = block($lawyers);


// Обрабатываем список экспертов
$experts = get_specialists_by_classification([3, 7], 8);
$data['index_experts'] = block($experts);


// Обрабатываем список нотариусов
$notaries = get_specialists_by_classification([4, 8], 8);
$data['index_notaries'] = block($notaries);

// Обрабатываем список блогов
/* $articles = get_main_blog_articles(0, 6);
$data['articles'] = block_articles($articles); */

//Список новостей
$news_titles = fillNewsList();
$data['news_list'] = '';
if (is_array($news_titles)) {
    $data['news_list'] .= '<div class="list">
    <div class="name">
        Лента новостей
    </div>';
    foreach ($news_titles as $title_array) {
        $data['news_list'] .= '<a href="/journal/' . $title_array['alias'] . '" class="link">' . $title_array['title'] . '</a>';
    }
    $data['news_list'] .= '<a href="/journal/news" class="bottom-link">Смотреть все новости</a>
    </div>';
}

$data['posts'] = '';
$posts = get_actual_posts_main();
foreach ($posts as $post) {
    $post_alias = '/journal/'.$post['alias'];
    $data['posts'] .= '<div class="item2">
    <div class="flex post-data">
        <a href="/journal/'.$post['post_type'].'" class="cat_'.$post['post_type'].'"><span>'.$post['service_title'].'</span></a>
        <div class="date">'.date('d.m.Y', strtotime($post['date'])).'</div>
    </div>
    <a href="'.$post_alias.'" class="name">'.$post['title'].'</a>';
    if ($post['photo'] != '' && !is_null($post['photo'])) {
    $data['posts'] .= '<a href="'.$post_alias.'" class="image" style="background-image: url(/uploads/articles/'.$post['photo'].');">
    </a>';
    }
    $data['posts'] .= '<a href="'.$post_alias.'">'.cut_text($post['content'], 300).'</a>';
    $article_tags = get_article_tags($post['id']);
    if ($post['post_type'] != 'blogs' && $article_tags) {
        $data['posts'] .= '<div class="tags">';
        foreach ($article_tags as $tag) {
            $data['posts'] .= '<a href="/journal/'.$tag['alias'].'">#'.$tag['title'].'</a>';
        }
        $data['posts'] .= '</div>';
    }
    $data['posts'] .= '</div>';
}

$data['actual_articles_list'] = '';
$actual_articles = get_actual_posts('articles', 0, 0, 4);
foreach ($actual_articles as $article) {
    $data['actual_articles_list'] .= '<a href="/journal/'.$article['alias'].'" class="item"><div class="item-img_wrapper" style="background-image:url(/uploads/articles/'.$article['photo'].');"></div><div><span>'.$article['title'].'</span></div></a>';
}

$HTML = html_explode($data, $HTML);

/**
 * @return string[]
 */
function get_actual_services(): array
{
    $code = [
        'actual_service' => '<div class="index-sections-block">',
        'more_actual_service' => '<div class="index-sections-block last">',
    ];

    $services = get_services();
    $blocks = get_blocks();

    foreach ($blocks as $block) {
        if ($block['id'] > 2) continue;

        $content = json_decode($block['content'], true);

        $array_key = ($block['id'] == 1) ? 'actual_service' : 'more_actual_service';

        $code[$array_key] .= <<<EOF
            <div class="block-name">{$block['title']}</div>
                <div class="flex">
        EOF;

        foreach ($content as $value) {
            $service = $services[$value];

            $code[$array_key] .= <<<EOF
                <div class="item">
                    <img src="/assets/img/{$service['img']}">
                    <p><span>{$service['title']}</span></p>
                    <a href="/yuristy-i-advokaty/{$service['slug']}"></a>
                </div>
            EOF;

        }

        $code[$array_key] .= <<<EOF
            </div>
            <a class="bottom-link show-catalog">Показать все разделы</a>
        </div>
        EOF;
    }

    return $code;
}
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

// Обрабатываем список баннеров

// Обрабатываем список блогов
/* $articles = get_main_blog_articles(0, 6);
$data['articles'] = block_articles($articles); */

$first_banners = get_banner(1);

$data['first_slider'] .= '
<div class="banner__wrapper main-banner">
    <div class="swiper" id="banner1">
        <div class="swiper-wrapper">';

foreach ($first_banners as $slide) {
    $image = $slide['image']; 

    $description = json_decode($slide['description'], true);

    if (!empty($image)) {

        $current_description = isset($description) ? $description : ['line1' => '', 'line2' => '', 'line3' => ''];

        $data['first_slider'] .= '
            <div class="swiper-slide banner-slide">
                <div class="banner-slide__wrapper">
                    <div class="background_img">
                        <img src="assets/img/' . $image . '" alt="' . $current_description['line1'] . '">
                        <div class="background_fade"></div>
                    </div>
                    <div class="main-slide__left-text">
                        ' . $current_description['line1'] . ',
                        <br>
                        ' . $current_description['line2'] . '
                        <div class="orange-text main">' . $current_description['line3'] . '</div>
                    </div>
                    <div class="title main-title">
                        ' . $slide['title'] . '
                        <div class="main-title__after-text">
                            И еще <a href="' . $slide['link'] . '">88 направлений</a>
                        </div>
                    </div>
                    <a href="' . $slide['button_link'] . '" class="btn btn__main-slide">' . $slide['button_text'] . '</a>
                </div>
            </div>';
    }
}

$data['first_slider'] .= '
        </div>
        <div class="banner-btn__prev" id="banner1__prev">
            <img src="assets/img/banner-prev.svg" alt="previous">
        </div>
        <div class="banner-btn__next" id="banner1__next">
            <img src="assets/img/banner-next.svg" alt="next">
        </div>
    </div>
</div>';

$second_banners = get_banner(2);

$data['second_slider'] = '
<div class="banner__wrapper">
    <div class="swiper" id="banner2">
        <div class="swiper-wrapper">';

foreach ($second_banners as $slide) {
    $description = json_decode($slide['description'], true);
    $image = $slide['image'];

    $data['second_slider'] .= '
            <div class="swiper-slide banner-slide promo-slide">
                <div class="banner-slide__wrapper">
                    <div class="background_img">
                        <img src="assets/img/' . $image . '">
                        <div class="background_fade"></div>
                    </div>
                    <div class="text">
                        <p>' . $description['line1'] . '</p>
                        <p>' . $description['line2'] . '</p>
                        <p>' . $description['line3'] . '</p>
                    </div>
                    <div class="title">
                        ' . $slide['specialist_name'] . '
                        <div class="orange-text large-padding right-side">
                            ' . $slide['classification'] . '
                        </div>
                    </div>
                </div>
            </div>';
}

$data['second_slider'] .= '
        </div>
        <div class="banner-btn__prev" id="banner2__prev">
            <img src="assets/img/banner-prev.svg" alt="previous">
        </div>
        <div class="banner-btn__next" id="banner2__next">
            <img src="assets/img/banner-next.svg" alt="next">
        </div>
    </div>
</div>';

$third_banners = get_banner(3);

$data['third_slider'] = '
<div class="banner__wrapper">
    <div class="swiper" id="banner3">
        <div class="swiper-wrapper">';

foreach ($third_banners as $slide) {

    $description = json_decode($slide['description'], true);
    
    $image = $slide['image'];

    $data['third_slider'] .= '
        <div class="swiper-slide banner-slide promo-slide">
            <div class="banner-slide__wrapper">
                <div class="background_img">
                    <img src="assets/img/' . $image . '">
                    <div class="background_fade"></div>
                </div>
                <div class="text">
                    <p>' . $description['line1'] . '</p>
                    <p>' . $description['line2'] . '</p>
                    <p>' . $description['line3'] . '</p>
                </div>
                <div class="title">
                    ' . $slide['specialist_name'] . '
                    <div class="orange-text right-side">
                        ' . $slide['classification'] . '
                    </div>
                </div>
            </div>
        </div>';
}

$data['third_slider'] .= '
        </div>
        <div class="banner-btn__prev" id="banner3__prev">
            <img src="assets/img/banner-prev.svg" alt="previous">
        </div>
        <div class="banner-btn__next" id="banner3__next">
            <img src="assets/img/banner-next.svg" alt="next">
        </div>
    </div>
</div>';

$fourth_banners = get_banner(4);

$data['fourth_slider'] = '
<div class="banner__wrapper">
    <div class="swiper" id="banner4">
        <div class="swiper-wrapper">';

// Проходим по каждому слайду баннера
foreach ($fourth_banners as $slide) {
    // Декодируем описание баннера
    $description = json_decode($slide['description'], true);
    
    // Получаем имя файла изображения
    $image = $slide['image'];

    // Создаем HTML для каждого слайда
    $data['fourth_slider'] .= '
        <div class="swiper-slide banner-slide promo-slide">
            <div class="banner-slide__wrapper">
                <div class="background_img">
                    <img src="assets/img/' . $image . '">
                    <div class="background_fade"></div>
                </div>
                <div class="text">
                    <p>' . $description['line1'] . '</p>
                    <p>' . $description['line2'] . '</p>
                </div>
                <div class="title">
                    ' . $slide['specialist_name'] . '
                    <div class="orange-text right-side">
                        ' . $slide['classification'] . '
                    </div>
                </div>
            </div>
        </div>';
}

// Завершаем структуру слайдера
$data['fourth_slider'] .= '
        </div>
        <div class="banner-btn__prev" id="banner4__prev">
            <img src="assets/img/banner-prev.svg" alt="previous">
        </div>
        <div class="banner-btn__next" id="banner4__next">
            <img src="assets/img/banner-next.svg" alt="next">
        </div>
    </div>
</div>';

//Список новостей
$news_titles = fillNewsList();
$data['news_list'] = '';
if (is_array($news_titles)) {
    $data['news_list'] .= '<div class="list">
    <div class="name">
        Лента новостей
    </div>';
    foreach ($news_titles as $title_array) {
        $data['news_list'] .= '<a href="/journal/' . $title_array['alias'] . '" class="link">' . htmlspecialchars_decode($title_array['title']) . '</a>';
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

    $services = get_actual_services_from_db();
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
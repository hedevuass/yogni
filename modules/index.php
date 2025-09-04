<?php

$data = [];

// Обрабатываем список актуальных разделов
$data = get_actual_services();

// Обрабатываем список юристов и адвокатов
$lawyers = get_specialists_by_classification_n_block([1, 2, 5, 6, 7], 8);
$data['index_lawyers'] = block($lawyers);


// Обрабатываем список экспертов
$experts = get_specialists_by_classification_n_block([3, 8], 8);
$data['index_experts'] = block($experts);


// Обрабатываем список нотариусов
$notaries = get_specialists_by_classification_n_block([4], 8);
$data['index_notaries'] = block($notaries);

$cityGenitive = $cityGenitive ?: ($_SESSION['city_genitive'] ?? 'Москвы');

$data['city_genitive'] = $_SESSION['city_genitive'];

$topLinks = [
    'top_link_lawyer' => 'yuristy-i-advokaty',
    'top_link_expert' => 'experty',
    'top_link_notarius' => 'notariusy',
];

$h2TopLinks = [
    'top_link_lawyer_h2' => 'yuristy-i-advokaty',
    'top_link_expert_h2' => 'experty',
    'top_link_notarius_h2' => 'notariusy',
];


foreach ($topLinks as $key => $slug) {
    $service = get_service_parent_catalog($slug);
    if ($service) {
        $title = htmlspecialchars($service['title']);
        $data[$key] = '<div class="top-block-link"><a href="/' . $slug . '">' . $title . ' ' .$cityGenitive . '</a></div>';
    }
}


foreach ($h2TopLinks as $key => $slug) {
    $service = get_service_parent_catalog($slug);
    if ($service) {
        $title = htmlspecialchars($service['title']);
        $tag = ($slug === 'yuristy-i-advokaty') ? 'h1' : 'h2';

        $data[$key] = '<' . $tag . ' class="dn top-block-link-h2"><a href="/' . $slug . '">' . 
                      mb_strtoupper($title, 'UTF-8') . ' ' . 
                      mb_strtoupper($cityGenitive, 'UTF-8') . 
                      '</a></' . $tag . '>';
    }
}

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
    $current_description = $description ?: ['line1' => '', 'line2' => '', 'line3' => ''];

    $displayName = !empty($slide['specialist_name'])
        ? $slide['specialist_name']
        : $slide['title'];

    $hasClassification = !empty($slide['specialist_classification']);
    $displayClassif = $hasClassification
        ? $slide['specialist_classification']
        : $slide['subtitle'];

    $specId = isset($slide['specialist_id']) ? (int) $slide['specialist_id'] : null;

    if (!empty($image)) {
        $data['first_slider'] .= '
                <div class="swiper-slide banner-slide">
                    <div class="banner-slide__wrapper">
                        <div class="background_img">
                            <img src="assets/img/' . htmlspecialchars($image) . '" alt="' . htmlspecialchars($current_description['line1']) . '">
                            <div class="background_fade"></div>
                        </div>
                        <div class="main-slide__left-text">
                            ' . htmlspecialchars($current_description['line1']) . ',
                            <br>
                            ' . htmlspecialchars($current_description['line2']) . '
                            <div class="orange-text main">' . htmlspecialchars($current_description['line3']) . '</div>
                        </div>
                        <div class="title main-title">
                            ' . htmlspecialchars($displayName) . '
                            <div class="main-title__after-text">';

        if ($hasClassification) {
            $data['first_slider'] .= '<a href="#" class="subtitle-link">' . htmlspecialchars($displayClassif) . '</a>';
        } else {
            $data['first_slider'] .= '<a href="' . htmlspecialchars($slide['link']) . '" class="subtitle-link">' . htmlspecialchars($displayClassif) . '</a>';
        }

        $data['first_slider'] .= '
                            </div>
                        </div>
                        <a href="'
            . ($specId
                ? '/specialist/id' . $specId
                : htmlspecialchars($slide['button_link'])
            )
            . '" class="btn btn__main-slide">'
            . htmlspecialchars($slide['button_text']) .
            '</a>
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
    $current_description = $description ?: ['line1' => '', 'line2' => '', 'line3' => ''];

    // 1) Название: либо specialist_name, либо title (fallback)
    $displayName = !empty($slide['specialist_name'])
        ? $slide['specialist_name']
        : $slide['title'];

    // 2) Категория (classification)
    $category = !empty($slide['specialist_classification'])
        ? $slide['specialist_classification']
        : $slide['classification'];

    // 3) ID специалиста (для ссылки)
    $specId = isset($slide['specialist_id']) ? (int) $slide['specialist_id'] : null;
    // URL для перехода — на страницу специалиста, если есть ID, иначе на общий link
    $targetLink = $specId
        ? '/specialist/id' . $specId
        : ($slide['link'] ?: '#');

    $data['second_slider'] .= '
        <div class="swiper-slide banner-slide promo-slide">
            <div class="banner-slide__wrapper">
                <a href="' . htmlspecialchars($targetLink) . '" class="banner-full-link" aria-label="Открыть баннер"></a>
                <div class="background_img">
                    <img src="assets/img/' . htmlspecialchars($slide['image']) . '" alt="' . htmlspecialchars($current_description['line1']) . '">
                    <div class="background_fade"></div>
                </div>
                <div class="text">
                    <p>' . htmlspecialchars($current_description['line1']) . '</p>
                    <p>' . htmlspecialchars($current_description['line2']) . '</p>
                    <p>' . htmlspecialchars($current_description['line3']) . '</p>
                </div>
                <div class="title">
                    ' . htmlspecialchars($displayName) . '
                    <div class="orange-text large-padding right-side">'
        . htmlspecialchars($category) . '
                    </div>
                </div>';

    if (!empty($slide['button_text'])) {
        // Кнопка тоже ведёт на страницу специалиста (или fallback)
        $data['second_slider'] .= '
                <a href="' . htmlspecialchars($targetLink) . '" class="btn btn__main-slide">'
            . htmlspecialchars($slide['button_text']) . '
                </a>';
    }

    $data['second_slider'] .= '
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
    $current_description = $description ?: ['line1' => '', 'line2' => '', 'line3' => ''];

    $displayName = !empty($slide['specialist_name'])
        ? $slide['specialist_name']
        : $slide['title'];

    $category = !empty($slide['specialist_classification'])
        ? $slide['specialist_classification']
        : $slide['classification'];

    $specId = isset($slide['specialist_id']) ? (int) $slide['specialist_id'] : null;
    $targetLink = $specId
        ? '/specialist/id' . $specId
        : ($slide['link'] ?: '#');

    $data['third_slider'] .= '
    <div class="swiper-slide banner-slide promo-slide">
        <div class="banner-slide__wrapper">
            <a href="' . htmlspecialchars($targetLink) . '" class="banner-full-link" aria-label="Открыть баннер"></a>
            <div class="background_img">
                <img src="assets/img/' . htmlspecialchars($slide['image']) . '" alt="' . htmlspecialchars($current_description['line1']) . '">
                <div class="background_fade"></div>
            </div>
            <div class="text">
                <p>' . htmlspecialchars($current_description['line1']) . '</p>
                <p>' . htmlspecialchars($current_description['line2']) . '</p>
                <p>' . htmlspecialchars($current_description['line3']) . '</p>
            </div>
            <div class="title">
                ' . htmlspecialchars($displayName) . '
                <div class="orange-text right-side">'
        . htmlspecialchars($category) . '
                </div>
            </div>';

    if (!empty($slide['button_text'])) {
        $data['third_slider'] .= '
            <a href="' . htmlspecialchars($targetLink) . '" class="btn btn__main-slide">'
            . htmlspecialchars($slide['button_text']) . '
            </a>';
    }

    $data['third_slider'] .= '
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

foreach ($fourth_banners as $slide) {
    $description = json_decode($slide['description'], true);
    $current_description = is_array($description)
        ? $description
        : ['line1' => '', 'line2' => '', 'line3' => ''];

    if (!empty($slide['specialist_title'])) {
        $displayName = 'Нотариус ' . $slide['specialist_title'];
    } else {
        $displayName = $slide['specialist_name'];
    }

    $metro = !empty($slide['metro'])
        ? $slide['metro']
        : $slide['classification'];

    $specId = isset($slide['specialist_id']) ? (int) $slide['specialist_id'] : null;
    $targetLink = $specId
        ? '/specialist/id' . $specId
        : ($slide['link'] ?: '#');

    $data['fourth_slider'] .= '
        <div class="swiper-slide banner-slide promo-slide">
            <div class="banner-slide__wrapper">
                <a href="' . htmlspecialchars($targetLink) . '" class="banner-full-link" aria-label="Открыть баннер"></a>
                <div class="background_img">
                    <img src="assets/img/' . htmlspecialchars($slide['image']) . '" alt="' . htmlspecialchars($current_description['line1']) . '">
                    <div class="background_fade"></div>
                </div>
                <div class="text">
                    <p>' . htmlspecialchars($current_description['line1']) . '</p>
                    <p>' . htmlspecialchars($current_description['line2']) . '</p>
                    <p>' . htmlspecialchars($current_description['line3']) . '</p>
                </div>
                <div class="title">
                    ' . htmlspecialchars($displayName) . '
                    <div class="orange-text right-side">'
        . htmlspecialchars($metro) . '
                    </div>
                </div>';

    if (!empty($slide['button_text'])) {
        $data['fourth_slider'] .= '
                <a href="' . htmlspecialchars($targetLink) . '" class="btn btn__main-slide">'
            . htmlspecialchars($slide['button_text']) . '
                </a>';
    }

    $data['fourth_slider'] .= '
            </div>
        </div>';
}

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

$fifth_banner = get_banner(5);

foreach ($fifth_banner as $slide) {
    $title = htmlspecialchars_decode($slide['title']);
    $button_text = htmlspecialchars_decode($slide['button_text']);
    $button_link = htmlspecialchars_decode($slide['button_link']);
    $description = json_decode($slide['description'], true);
    $image = $slide['image'];

    // Создаем HTML для каждого слайда
    $data['fifth_banner'] .= '
        <div class="top-banner">
				<span class="name">
					' . $title . '
				</span>
				<span class="p">
                    <p>' . $description['line1'] . '</p>
                    <p>' . $description['line2'] . '</p>
                    <p>' . $description['line3'] . '</p>
                </span>
				<a href="' . $button_link . '">' . $button_text . '</a>
			</div>';
}

//Список новостей
$news_titles = fillNewsList();
$data['news_list'] = '';
if (is_array($news_titles)) {
    $data['news_list'] .= '<div class="list">
    <div class="name">
        Лента новостей
    </div>';
    foreach ($news_titles as $title_array) {
        $data['news_list'] .= '<a href="/journal/' . $title_array['alias'] . '" class="link">' . strip_tags(html_entity_decode($title_array['title'])) . '</a>';
    }
    $data['news_list'] .= '<a href="/journal/news" class="bottom-link">Смотреть все новости</a>
    </div>';
}

$data['posts'] = '';
$posts = get_actual_posts_main();
foreach ($posts as $post) {
    $post_alias = '/journal/' . $post['alias'];
    $data['posts'] .= '<div class="item2">
    <div class="flex post-data">
        <a href="/journal/' . $post['post_type'] . '" class="cat_' . $post['post_type'] . '"><span>' . $post['service_title'] . '</span></a>
        <div class="date">' . date('d.m.Y', strtotime($post['date'])) . '</div>
    </div>
    <a href="' . $post_alias . '" class="name">' . strip_tags(html_entity_decode($post['title'])) . '</a>';
    if ($post['photo'] != '' && !is_null($post['photo'])) {
        $data['posts'] .= '<a href="' . $post_alias . '" class="image" style="background-image: url(/uploads/articles/' . $post['photo'] . ');">
    </a>';
    }
    $data['posts'] .= '<a class="content cut-text" href="' . $post_alias . '">' . strip_tags(html_entity_decode($post['content'])) . '</a>';
    $article_tags = get_article_tags($post['id']);
    if ($post['post_type'] != 'blogs' && $article_tags) {
        $data['posts'] .= '<div class="tags">';
        foreach ($article_tags as $tag) {
            $data['posts'] .= '<a href="/journal/' . $tag['alias'] . '">#' . $tag['title'] . '</a>';
        }
        $data['posts'] .= '</div>';
    }
    $data['posts'] .= '</div>';
}

$data['actual_articles_list'] = '';
$actual_articles = get_actual_posts('articles', 0, 0, 4);
foreach ($actual_articles as $article) {
    $data['actual_articles_list'] .= '<a href="/journal/' . $article['alias'] . '" class="item"><div class="item-img_wrapper" style="background-image:url(/uploads/articles/' . $article['photo'] . ');"></div><div><span>' . strip_tags(html_entity_decode($article['title'])) . '</span></div></a>';
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
        if ($block['id'] > 2)
            continue;

        $content = json_decode($block['content'], true);

        $array_key = ($block['id'] == 1) ? 'actual_service' : 'more_actual_service';

        $code[$array_key] .= <<<EOF
            <div class="block-name">{$block['title']}</div>
                <div class="flex">
        EOF;

        foreach ($services as $service) {
            if (!in_array($service['id'], $content))
                continue;

            $code[$array_key] .= <<<EOF
                <div class="item">
                    <img src="/assets/img/{$service['img']}" alt="Раздел &quot;{$service['title']}&quot;">
                    <p><span>{$service['title']}</span></p>
                    <a href="{$service['slug']}"></a>
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

$HTML = str_replace(
    '<!--data:city_genitive--><!--data:city_genitive-->',
    '<!--data:city_genitive-->' . $data['city_genitive'] . '<!--data:city_genitive-->',
    $HTML
);
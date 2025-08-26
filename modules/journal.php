<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/S.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Cases.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/CasesHelper.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Gender.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/BaseInflection.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/NamesInflection.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/RussianLanguage.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/Cases.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/RussianCasesHelper.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/FirstNamesInflection.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/MiddleNamesInflection.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/LastNamesInflection.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/functions.php';

$requestion_arr = explode('/', $_SERVER['REQUEST_URI']);
$include_ads = $main_page = true;
$posts = get_actual_posts('', 0, 0, 5, 0);
$data['breadcrumbs'] = '<span class="curr-page">Журнал</span>';
$tag_id = $theme_id = 0;
if (isset($requestion_arr[2])) {
    $requestion_str = $requestion_arr[2];
    if ($requestion_str != '') {
        $include_ads = $main_page = false;
        $data['breadcrumbs'] = '<a href="/journal">Журнал</a>';
        if (get_article_by_alias($requestion_str)) {
            $single_post = get_article_by_alias($requestion_str);
            $posts = get_actual_posts('', 0, 0, 5, 0, 0, $single_post['date']);
            $include_ads = $main_page = false;
            /* if ($single_post['post_type'] == 'news') {
                $breadcrumbs_type = 'Новости';
            } else if ($single_post['post_type'] == 'articles') {
                $breadcrumbs_type = 'Статьи';
            } else if ($single_post['post_type'] == 'blogs') {
                $breadcrumbs_type = 'Блоги';
            } */
            $data['breadcrumbs'] .= '<a href="/journal/' . $single_post['post_type'] . '">' . $breadcrumbs_type . '</a><span>/</span><span class="curr-page">' . strip_tags(html_entity_decode($single_post['title'])) . '</span>';
        } else if (get_service_by_slug($requestion_str)) {
            $include_ads = $main_page = true;
            $service_by_slug = get_service_by_slug($requestion_str);
            $theme_id = $service_by_slug['id'];
            $posts = get_actual_posts('', $theme_id);
            $data['breadcrumbs'] .= '<span>/</span><span class="curr-page">' . strip_tags(html_entity_decode($service_by_slug['title'])) . '</span>';
        } else if (get_tag_by_alias($requestion_str)) {
            $include_ads = $main_page = true;
            $tag_by_alias = get_tag_by_alias($requestion_str);
            $tag_id = $tag_by_alias['id'];
            $posts = get_actual_posts('', 0, $tag_id);
            $data['breadcrumbs'] .= '<span>/</span><span class="curr-page">' . strip_tags(html_entity_decode($tag_by_alias['title'])) . '</span>';
        } else if (strpos($requestion_str, 'id') !== false) {
            $journal_page = 'blogs';
            $blog_id = intval(str_replace('id', '', strtok($requestion_str, '?')));
            $blog = get_blog_by_id($blog_id);
            if (!$blog) responseRedirect('/');

            $specialist = get_specialist($blog['specialist_id']);
            // заголовок
            $breadcrumbs_title = 'Блог ';
            // Если компания
            if ($specialist['company']) {
                $name = '"' . $specialist['company'] . '"';

                if ($specialist['classification_id'] == 5) {
                    $breadcrumbs_title .= "юридической компании";
                } else if ($specialist['classification_id'] == 6) {
                    $breadcrumbs_title .= "коллегии адвокатов";
                } else if ($specialist['classification_id'] == 8) {
                    $breadcrumbs_title .= "экспертного учреждения";
                } else if ($specialist['classification_id'] == 7) {
                    $breadcrumbs_title .= "адвокатского бюро";
                }
            }
            // Если частное лицо
            if (!$specialist['company']) {
                $name = morphos\Russian\inflectName($specialist['lastname'] . ' ' . $specialist['name'], 'родительный');
                $name = implode(' ', array_reverse(explode(' ', $name)));

                if ($specialist['classification_id'] == 1) {
                    $breadcrumbs_title .= "юриста";
                } else if ($specialist['classification_id'] == 2) {
                    $breadcrumbs_title .= "адвоката";
                } else if ($specialist['classification_id'] == 3) {
                    $breadcrumbs_title .= "эксперта";
                } else if ($specialist['classification_id'] == 4) {
                    $breadcrumbs_title .= "нотариуса";
                }
            }
            $breadcrumbs_title .= ' ' . $name;
            $data['breadcrumbs'] .= '<span>/</span>';
            if (isset($requestion_arr[3])) {
                // Проверяем на существование и выводим конкретный пост блога
                $single_post = get_article_by_alias($requestion_arr[3]);
                if (!$single_post) responseRedirect('/');
                // Загружаем посты, начиная с даты текущей записи
                $posts = get_actual_posts('', 0, 0, 5, 0, 0, $single_post['date']);
                $data['breadcrumbs'] .= '<a href="/journal/id' . $blog['id'] . '">' . $breadcrumbs_title . '</a><span>/</span><span class="curr-page">' . strip_tags(html_entity_decode($single_post['title'])) . '</span>';
            } else {
                $data['breadcrumbs'] .= '<span class="curr-page">' . strip_tags(html_entity_decode($breadcrumbs_title)) . '</span>';
            }
        } else if ($requestion_str == 'news' || $requestion_str == 'blogs' || $requestion_str == 'articles') {
            $journal_page = $requestion_str;
            if ($requestion_str == 'news') {
                $breadcrumbs_type = 'Новости';
            } else if ($requestion_str == 'articles') {
                $breadcrumbs_type = 'Статьи';
            } else if ($requestion_str == 'blogs') {
                $breadcrumbs_type = 'Блоги';
            }
            $include_ads = true;
            $data['breadcrumbs'] .= '<span>/</span><span class="curr-page">' . strip_tags(html_entity_decode($breadcrumbs_type)) . '</span>';
        } else responseRedirect('/'); // Если раздела\записи нет делаем редирект на главную
    }
}


$menu_page = '';
if (isset($journal_page)) {
    $menu_page = $journal_page;
} else if (isset($single_post)) {
    $menu_page = $single_post['post_type'];
}
$data['journal_menu_mobile'] = $data['journal_menu'] = '
    <li><a href="/journal" ' . ($main_page ? 'class="active"' : '') . '><img src="/assets/img/blog-menu1' . ($main_page ? '-active' : '') . '.svg"> Актуальное</a></li>
    <li><a href="/journal/news" ' . ($menu_page == 'news' ? 'class="active"' : '') . '><img src="/assets/img/blog-menu2' . ($menu_page == 'news' ? '-active' : '') . '.svg"> Новости</a></li>
    <li><a href="/journal/articles" ' . ($menu_page == 'articles' ? 'class="active"' : '') . '><img src="/assets/img/blog-menu3' . ($menu_page == 'articles' ? '-active' : '') . '.svg"> Статьи</a></li>
    <li><a href="/journal/blogs" ' . ($menu_page == 'blogs' ? 'class="active"' : '') . '><img src="/assets/img/blog-menu4' . ($menu_page == 'blogs' ? '-active' : '') . '.svg"> Блоги</a></li>
';

$themes = get_blog_filter_themes();
$tags = get_blog_filter_tags();
$data['filter_journal_theme'] = '';
$data['mobile_filter_journal'] = '<ul class="menu-links-mobile blog-links-mobile" data-themes-count="' . (count($themes) + 1) . '">
<li class="find_lawyer_journal"><a href="/journal">Все темы</a></li>';
//разделы юристов
foreach ($themes as $theme) {
    if ($theme['classification'] == 3) {
        continue;
    }
    if (mb_stripos($theme['title'], 'прочие') !== false) {
        //раздел "прочие" в конец
        $last_theme = $theme;
    } else {
        $selected = ($theme_id == $theme['id']) ? 'selected' : '';
        $data['filter_journal_theme'] .= "<option value='{$theme['slug']}' $selected>{$theme['title']}</option>";
        $data['mobile_filter_journal'] .= "<li class='{$theme['slug']}'" .
            (!empty($theme['icon']) ? " style='background-image: url(../assets/img/{$theme['icon']});'" : "") . ">
    <a href='/journal/{$theme['slug']}'>{$theme['title']}</a>
</li>";
    }
}
if (isset($last_theme) && $last_theme != '') {
    $selected = ($theme_id == $last_theme['id']) ? 'selected' : '';
    $data['filter_journal_theme'] .= "<option value='{$last_theme['slug']}' $selected>{$last_theme['title']}</option>";
    $data['mobile_filter_journal'] .= "<li class='{$last_theme['slug']}'" .
        (!empty($last_theme['icon']) ? " style='background-image: url(../assets/img/{$last_theme['icon']});'" : "") . ">
    <a href='/journal/{$last_theme['slug']}'>{$last_theme['title']}</a>
</li>";
}
//разделы экспертиз
foreach ($themes as $theme) {
    if ($theme['classification'] == 1 || $theme['classification'] == 2) {
        continue;
    }
    if (mb_stripos($theme['title'], 'прочие') !== false) {
        //раздел "прочие" в конец
        $last_theme_exp = $theme;
    } else {
        $selected = ($theme_id == $theme['id']) ? 'selected' : '';
        $data['filter_journal_theme'] .= "<option value='{$theme['slug']}' $selected>{$theme['title']}</option>";
        $data['mobile_filter_journal'] .= "<li class='{$theme['slug']}'" .
            (!empty($theme['icon']) ? " style='background-image: url(../assets/img/{$theme['icon']});'" : "") . ">
        <a href='/journal/{$theme['slug']}'>{$theme['title']}</a>
    </li>";
    }
}
if (isset($last_theme_exp) && $last_theme_exp != '') {
    $selected = ($theme_id == $last_theme_exp['id']) ? 'selected' : '';
    $data['filter_journal_theme'] .= "<option value='{$last_theme_exp['slug']}' $selected>{$last_theme_exp['title']}</option>";
    $data['mobile_filter_journal'] .= "<li class='{$last_theme_exp['slug']}'" .
        (!empty($last_theme_exp['icon']) ? " style='background-image: url(../assets/img/{$last_theme_exp['icon']});'" : "") . ">
    <a href='/journal/{$last_theme_exp['slug']}'>{$last_theme_exp['title']}</a>
</li>";
}

//теги
// $data['filter_journal_tag'] = '';
// foreach ($tags as $tag) {
//     $selected = ($tag_id == $tag['id']) ? 'selected' : '';
//     $data['filter_journal_tag'] .= "<option value='{$tag['alias']}' $selected>{$tag['title']}</option>";
// }

$data['mobile_filter_journal'] .= '</ul>';


$data['news_list'] = $data['blog_info'] = $data['top_ads'] = $data['actual_articles_list'] = $data['single_post'] = $data['posts'] = '';
$news_titles = fillNewsList();

$journal_banners = get_journal_banners();

$filteredBanners = array_filter($journal_banners, function ($banner) {
    return in_array($banner['id'], [3, 4, 5]);
});

foreach ($filteredBanners as $index => $banner) {
    if ($banner['id'] == 3) {
        // HTML для первого баннера
        $first_banner = '
        <a href="' . htmlspecialchars($banner['link']) . '" class="item">
            <img src="/assets/img/' . htmlspecialchars($banner['image']) . '">
            <div class="text">
                <p>' . htmlspecialchars_decode($banner['title']) . '</p>
                <button>' . htmlspecialchars_decode($banner['button_text']) . '</button>
            </div>
        </a>';
    } elseif ($banner['id'] == 4) {
        // HTML для второго баннера
        $second_banner = '
        <a href="' . htmlspecialchars($banner['link']) . '" class="item">
            <img src="/assets/img/' . htmlspecialchars($banner['image']) . '">
            <span class="link">' . htmlspecialchars_decode($banner['title']) . '</span>
        </a>';
    } elseif ($banner['id'] == 5) {
        // HTML для третьего баннера
        $third_banner = '
				<div class="image">
					<img src="/assets/img/' . htmlspecialchars($banner['image']) . '">
				</div>
				<span class="name">' . htmlspecialchars_decode($banner['title']) . '</span>
				<p>' . htmlspecialchars_decode($banner['description']) . '</p>
				<a href="' . htmlspecialchars($banner['button_link']) . '" class="button">' . htmlspecialchars_decode($banner['button_text']) . '</a>';
    }
}

$data['sticky'] = '<div class="sticky">' . $first_banner . $second_banner . '</div>';
/* $data['item4'] = '<div class="item4">' . $third_banner . '</div>'; */

if ($include_ads) {
    $topAd = array_filter($journal_banners, function ($banner) {
        return $banner['id'] == 1;
    });

    $topAd = reset($topAd);

    if ($topAd) {
        $activeDescription = get_active_journal_ad(1); // journal_id = 1

        $adLine1 = htmlspecialchars($topAd['title']);
        $adLine2 = htmlspecialchars($topAd['description']);
        $link = htmlspecialchars($topAd['link']);
        $buttonLink = htmlspecialchars($topAd['button_link'] ?? $topAd['link']);
        $displayName = htmlspecialchars($topAd['service_type']);

        if ($activeDescription) {
            $adLine1 = htmlspecialchars($activeDescription['ad_line1']);
            $adLine2 = htmlspecialchars($activeDescription['ad_line2']);
            $displayName = htmlspecialchars($activeDescription['display_name']);
            $specialistId = (int) $activeDescription['specialist_id'];
            $link = "specialist/id{$specialistId}";
            $buttonLink = $link;
        }
        $data['h1_hidden_title'] = '<h1 class="dn">' . $adLine1 . '</h1>';
        $data['top_ads'] = '<div class="top-text"><div class="item1">
            <img src="/assets/img/' . htmlspecialchars($topAd['image']) . '" class="image">
            <div class="name">' . $adLine1 . '</div>
            <p>' . $adLine2 . '</p>
            <div class="cat">
                <img src="/assets/img/cat1.svg">  
                <a href="' . $link . '" class="link">' . $displayName . '</a>
            </div>
            <a href="' . $buttonLink . '" class="button">' . htmlspecialchars($topAd['button_text']) . '</a>
        </div></div>';
    }
}

if ($main_page && is_array($news_titles) && $theme_id == 0 && $tag_id == 0) {
    //главная
    //Список новостей
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

$actual_articles = get_actual_posts('articles', 0, 0, 4);
foreach ($actual_articles as $article) {
    $data['actual_articles_list'] .= '<a href="/journal/' . $article['alias'] . '" class="item"><div class="item-img_wrapper" style="background-image:url(/uploads/articles/' . $article['photo'] . ');"></div><div><span>' . strip_tags(html_entity_decode($article['title'])) . '</span></div></a>';
}

if (isset($single_post) && isset($journal_page) && str_starts_with($_SERVER['REQUEST_URI'], '/journal/id')) {
    $journal_page = ''; // чтобы вызов get_actual_posts('') сработал ниже

    $posts = get_actual_posts('', 0, 0, 5, 0, 0, $single_post['date']);
}
if (isset($journal_page) && !isset($single_post)) {
    $posts = get_actual_posts($journal_page);
}

if (isset($blog)) {
    $blog_name = $specialist['company'] ?? $specialist['name'] . " " . $specialist['lastname'];

    if (!empty($specialist['foundation'])) {
        $foundation = (int)$specialist['foundation'];

        // Если это стаж (меньше 1000 — явно не год)
        if ($foundation > 0 && $foundation < 1000) {
            $insert = num_decline($foundation, 'год, года, лет');
            if (empty($specialist['company'])) {
                $experience_code = "Опыт работы: {$insert}";
            }

            // Если это год основания (например: 2010)
        } elseif ($foundation >= 1000 && $foundation <= (int)date('Y')) {
            if (!empty($specialist['company'])) {
                $experience_code = "Год основания: {$foundation}";
            } else {
                $experience = date('Y') - $foundation;
                $insert = ($experience <= 0)
                    ? 'менее года'
                    : num_decline($experience, 'год, года, лет');
                $experience_code = "Опыт работы: {$insert}";
            }
        }
    }


    //адрес
    $adress = [];

    if (!is_null($specialist['city']) && $specialist['city']) {
        $adress[] = 'г. ' . $specialist['city'];
    }
    if (!is_null($specialist['metro']) && $specialist['metro']) {
        $adress[] = 'м. ' . $specialist['metro'];
    }

   /*  if (!is_null($specialist['street']) && $specialist['street']) {
        $adress[] = $specialist['street'];
    }

    if (!is_null($specialist['house']) && $specialist['house']) {
        $adress[] = $specialist['house'];
    } */

    $adress = implode(', ', $adress);

    $rating_code = '';
    // Рейтинг
    if ($specialist['count_rating']) {
        $rating_code = '<span>';
        $rating = intval(round($specialist['rating'], 0));
        for ($i = 0; $i < $rating; $i++) {
            $rating_code .= '<i class="fa fa-star"></i>';
        }
        for ($i = 5; $i > $rating; $i--) {
            $rating_code .= '<i class="fa fa-star-o"></i>';
        }
        $rating_code .= '</span>';
    }

    if (!isset($single_post)) {
        $data['h1_hidden_title'] = '<h1 class="dn">' . $blog_name . '</h1>';
        $specialist['photo'] = !empty($specialist['photo'])
            ? $specialist['photo']
            : (!empty($specialist['company']) ? '022.jpg' : '02.jpg');

        $data['blog_info'] = '<div class="author-block">
            <a href="/specialist/id' . $specialist['id'] . '" class="avatar" style="background-image:url(/uploads/avatars/' . $specialist['photo'] . ');"></a>
            <div class="info">
                <div class="name">
                    <a class="blog_name blog_name_author_page" href="/specialist/id' . $specialist['id'] . '">' . $blog_name . '</a>
                </div>
                <div class="prof">
                    ' . $specialist['classification'] . '
                </div>
                <p class="rating_code">' . $rating_code . '</p>
                ' . ($adress ? ('<div class="adress">' . $adress . '</div>') : '') . '
            </div>
            <a href="/specialist/id' . $specialist['id'] . '" class="link">Перейти на страницу</a>
        </div>';
    }
    if (!isset($requestion_arr[3]) && isset($blog)) {
        $limit = 5;
        $posts_blog = get_blog_posts($blog['id'], $limit);

        if (count($posts_blog) < $limit) {
            $remaining = $limit - count($posts_blog);

            $posts_actual = get_actual_posts(
                '',     // post_type — НЕ передаем
                0,      // post_theme
                0,      // post_tag
                $remaining,  // limit
                0,      // start
                0,      // blog_id — НЕ передаем
                '',     // max_date — НЕ ограничиваем
                $blog['id']  // exclude_blog_id — исключаем уже показанный блог
            );

            $posts = array_merge($posts_blog, $posts_actual);
        } else {
            $posts = $posts_blog;
        }
    }
}

if (isset($single_post)) {
    $tags = get_article_tags($single_post['id']);
    $post_blog_info = '';
    $specialist['photo'] = !empty($specialist['photo'])
        ? $specialist['photo']
        : (!empty($specialist['company']) ? '022.jpg' : '02.jpg');
    if (isset($blog) && $single_post['post_type'] == 'blogs') {
        $post_blog_info = '<div class="bottom-block flex">
            <div class="author author-desktop">
                <div class="author__img-wrapper" style="background-image:url(/uploads/avatars/' . $specialist['photo'] . ');"></div>
                <div>
                    <p><a class="blog_name" href="/specialist/id' . $specialist['id'] . '">' . $blog_name . '</a></p>
                    <p class="classification">' . $specialist['classification'] . '</p>
                    <p class="rating_code">' . $rating_code . '</p>
                </div>
            </div>

            <!-- Мобильная версия -->
            <div class="author author-mobile">
                <div class="author__img-wrapper" style="background-image:url(/uploads/avatars/' . $specialist['photo'] . ');"></div>
                <div>
                    <p class="blog_name_mobile"><a class="blog_name" href="/specialist/id' . $specialist['id'] . '">' . $blog_name . '</a></p>
                    <p class="classification">' . $specialist['classification'] . '</p>
                    <p class="rating_code">' . $rating_code . '</p>
                </div>
            </div>
            <a href="/journal/id' . $blog['id'] . '" class="link">Все записи автора</a>
        </div>';
    }
    $title_clean = preg_replace('#<\/?p[^>]*>#i', '', htmlspecialchars_decode($single_post['title']));
    $data['single_post'] = '<div class="item5' . ($single_post['photo'] != '' && !is_null($single_post['photo']) ? '' : ' no_image') . '">
        <div class="flex post-data">
            <a href="/journal/' . $single_post['service_slug'] . '" class="cat cat_' . $single_post['post_type'] . '"><span>' . $single_post['service_title'] . '</span></a>
            <div class="date">' . date('d.m.Y', strtotime($single_post['date'])) . '</div>
        </div>
        <div style="margin-bottom: 32px; font-size: 18px; font-weight: 500;">' . $title_clean . '</div>';
    if ($single_post['photo'] != '' && !is_null($single_post['photo'])) {
        $data['single_post'] .= '<div class="image" style="background: url(/uploads/articles/' . $single_post['photo'] . ') center/cover;"></div>';
    }
    if ($single_post['content'] != '' && !is_null($single_post['content'])) {
        $data['single_post'] .= '<p>' . (htmlspecialchars_decode($single_post['content'])) . '</p>';
    }
    if ($single_post['site_from_link'] != '' && $single_post['site_from_name'] != '') {
        $data['single_post'] .= '<div class="site">
            Источник <a href="' . $single_post['site_from_link'] . '">“' . $single_post['site_from_name'] . '”</a>
        </div>';
    }
    if ($tags) {
        $data['single_post'] .= '<div class="tags">';
        foreach ($tags as $tag) {
            $data['single_post'] .= '<a href="/journal/' . $tag['alias'] . '">#' . $tag['title'] . '</a>';
        }
        $data['single_post'] .= '</div>';
    }
    $data['single_post'] .= $post_blog_info;
    $data['single_post'] .= '</div>';
    $data['h1_hidden_title'] = '<h1 class="dn">' . htmlspecialchars_decode($single_post['title']) . '</h1>';
}


$post_number = 1;
foreach ($posts as $post) {
    if (isset($single_post)) {
        if ($single_post['id'] == $post['id']) continue;
    }
    $post_alias = '/journal';
    //$show_blog_info = false;
    $author_name = $author_blog_link = '';
    if ($post['post_type'] == 'blogs') {
        $post_alias .= '/id' . $post['blog_id'];
        //if (!isset($blog)) {
        //$show_blog_info = true;
        $specialist = get_specialist($post['author_id']);
        $author_name = $specialist['company'] ? $specialist['company'] : $specialist['name'] . ' ' . $specialist['lastname'];
        //}
        $author_blog_link = $post_alias;
    }

    $post_alias .= '/' . $post['alias'];
    $data['posts'] .= '<div class="item2">
    <div class="flex post-data">
        <a href="/journal/' . $post['service_slug'] . '" class="cat_' . $post['post_type'] . '"><span>' . $post['service_title'] . '</span></a>
        <div class="date">' . date('d.m.Y', strtotime($post['date'])) . '</div>
    </div>
    <a href="' . $post_alias . '" class="name">' . strip_tags(html_entity_decode($post['title'])) . '</a>';
    if ($post['photo'] != '' && !is_null($post['photo'])) {
        $data['posts'] .= '<a href="' . $post_alias . '" class="image" style="background-image: url(/uploads/articles/' . $post['photo'] . ');">
        </a>';
    }
    if ($post['post_type'] == 'blogs') {
        $data['posts'] .= '<a class="content cut-text-blogs" href="' . $post_alias . '">' . strip_tags(html_entity_decode($post['content'])) . '</a>';
    } else {
        $data['posts'] .= '<a class="content cut-text" href="' . $post_alias . '">' . strip_tags(html_entity_decode($post['content'])) . '</a>';
    }
    if ($author_blog_link && $author_name) {
        $data['posts'] .=
            //($show_blog_info ? ('<a href="'.$author_blog_link.'" class="author">'.$author_name.'</a>') : '') .
            '<a href="' . $author_blog_link . '" class="author">' . $author_name . '</a>';
    }
    $article_tags = get_article_tags($post['id']);
    if ($post['post_type'] != 'blogs' && $article_tags) {
        $data['posts'] .= '<div class="tags">';
        foreach ($article_tags as $tag) {
            $data['posts'] .= '<a href="/journal/' . $tag['alias'] . '">#' . $tag['title'] . '</a>';
        }
        $data['posts'] .= '</div>';
    }
    $data['posts'] .= '</div>';
    if ($post_number == 2) {
        $postAd = array_filter($journal_banners, function ($banner) {
            return $banner['id'] == 2;
        });

        $postAd = reset($postAd);

        if ($postAd) {
            $activeDescription = get_active_journal_ad(2); // journal_id = 2

            $adLine1 = htmlspecialchars($postAd['title']);
            $adLine2 = htmlspecialchars($postAd['description']);
            $link = htmlspecialchars($postAd['link']);
            $buttonLink = htmlspecialchars($postAd['button_link'] ?? $postAd['link']);
            $displayName = htmlspecialchars($postAd['service_type']);

            if ($activeDescription) {
                $adLine1 = htmlspecialchars($activeDescription['ad_line1']);
                $adLine2 = htmlspecialchars($activeDescription['ad_line2']);
                $displayName = htmlspecialchars($activeDescription['display_name']);
                $specialistId = (int) $activeDescription['specialist_id'];
                $link = "specialist/id{$specialistId}";
                $buttonLink = $link;
            }

            /* $data['posts'] .= '<div class="item3">
                <div class="cat">
                    <div>
                        <img src="/assets/img/cat4.svg"> <a href="' . $link . '" class="link">' . $displayName . '</a>
                    </div>
                </div>
                <div class="image">
                    <img src="/assets/img/' . htmlspecialchars($postAd['image']) . '">
                </div>
                <div class="name">' . $adLine1 . '</div>
                <p>' . $adLine2 . '</p>
                <div class="button-wrapper">
                    <a href="' . $buttonLink . '" class="button">' . htmlspecialchars($postAd['button_text']) . '</a>
                </div>
            </div>'; */
        }
    }
    $post_number++;
}

// Устанавливаем title
$HTML = html_replace([
    'title' => $title . ' - ЮРФАК'
], $HTML);

$HTML = html_explode($data, $HTML);

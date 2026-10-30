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
?>
<style>
    #theme {
        padding: 0 30px 0 4px;
        border-radius: 4px;
        height: 32px;
        margin-bottom: 24px;
        font-size: 16px;
        max-width: 100%;
    }
</style>
<?php
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // убираем ?utm и прочее
$requestPath = preg_replace('#/+#', '/', $requestPath);          // убираем повторные слэши
$requestPath = rtrim($requestPath, '/');                         // убираем конечный слэш (но не первый)
if ($requestPath === '') $requestPath = '/';

// возвращаем первый слэш, если он был
if ($requestPath[0] !== '/') {
    $requestPath = '/' . $requestPath;
}

// теперь делаем explode, как раньше
$requestion_arr = explode('/', $requestPath);
$include_ads = $main_page = true;
$posts = get_actual_posts('', 0, 0, 5, 0);
$data['breadcrumbs'] = '<span class="curr-page">Журнал</span>';
$tag_id = $theme_id = 0;

if (!isset($requestion_arr[2]) || $requestion_arr[2] === '') {
    // Главная /journal
    $posts = get_actual_posts('', 0, 0, 5, 0);
    $count_posts = get_actual_posts('', 0, 0, 200, 0);
    echo generateJournalPostsJsonLd($posts, $count_posts);
    echo generateJournalBreadcrumbsJsonLd($requestion_str ?? '');
    echo generateJournalCollectionPageJsonLd('');
}
function generateJournalPostsJsonLd(
    array $posts,
    array $count_posts,
    string $pageType = '',
    string $themeTitle = '',
    string $tagTitle = ''
): string {
    // Ограничение количества постов
    $limit = 10; // 10 для главной, 5 для разделов
    $posts = array_slice($posts, 0, $limit);

    $workExamples = [];

    foreach ($posts as $post) {
        $articleType = ($post['post_type'] === 'news') ? 'NewsArticle' : 'Article';

        // URL
        if ($post['post_type'] === 'blogs') {
            $url = full_url('/journal/id' . ($post['blog_id'] ?? '') . '/' . ($post['alias'] ?? ''));
        } else {
            $url = full_url('/journal/' . ($post['alias'] ?? ''));
        }

        // Теги
        $keywords = [];
        $article_tags = get_article_tags($post['id']);
        if ($article_tags && is_array($article_tags)) {
            foreach ($article_tags as $tag) {
                if (!empty($tag['title'])) {
                    $keywords[] = $tag['title'];
                }
            }
        }

        // Автор
        if ($post['post_type'] === 'blogs') {
            $specialist = get_specialist($post['author_id'] ?? 0);
            if (!empty($specialist)) {
                $authorType = !empty($specialist['company']) ? 'Organization' : 'Person';
                $authorName = !empty($specialist['company'])
                    ? $specialist['company']
                    : trim(($specialist['name'] ?? '') . ' ' . ($specialist['lastname'] ?? ''));
                $authorUrl = full_url('/journal/id' . ($post['blog_id'] ?? ''));
            } else {
                $authorType = 'Person';
                $authorName = 'Автор';
                $authorUrl = full_url('/');
            }
        } else {
            $authorType = 'Organization';
            $authorName = 'ЙОГУРТ';
            $authorUrl = full_url('/');
        }

        // Image
        $image = !empty($post['photo'])
            ? full_url('/uploads/articles/' . $post['photo'])
            : null;

        $work = [
            "@type" => $articleType,
            "url" => $url,
            "datePublished" => date('Y-m-d', strtotime($post['date'] ?? 'now')),
            "articleSection" => $post['service_title'] ?? '',
            "headline" => strip_tags(htmlspecialchars_decode($post['title'] ?? '')),
            "description" => strip_tags(htmlspecialchars_decode($post['content'] ?? '')),
            "author" => [
                "@type" => $authorType,
                "name"  => $authorName,
                "url"   => $authorUrl
            ]
        ];

        if (!empty($keywords)) {
            $work['keywords'] = $keywords;
        }

        if ($image) {
            $work['image'] = $image;
        }

        $workExamples[] = $work;
    }

    // Заголовки и описание
    if (!empty($themeTitle)) {
        $listName = "Статьи, новости и блоги журнала по теме «{$themeTitle}»";
        $listDescription = "Все статьи, новости и блоги журнала по теме «{$themeTitle}» — " . (count($count_posts)) . " публикаций";
    } elseif (!empty($tagTitle)) {
        $listName = "Статьи, новости и блоги журнала с тегом «{$tagTitle}»";
        $listDescription = "Все статьи, новости и блоги журнала с тегом «{$tagTitle}» — " . (count($count_posts)) . " публикаций";
    } else {
        switch ($pageType) {
            case 'news':
                $listName = "Новости журнала";
                $listDescription = "Все новости журнала — " . (count($count_posts)) . " публикаций";
                break;
            case 'articles':
                $listName = "Статьи журнала";
                $listDescription = "Все статьи журнала — " . (count($count_posts)) . " публикаций";
                break;
            case 'blogs':
                $listName = "Блоги журнала";
                $listDescription = "Все блоги журнала — " . (count($count_posts)) . " публикаций";
                break;
            default:
                $listName = "Статьи, новости и блоги журнала";
                $listDescription = "Все статьи, новости и блоги журнала по темам и категориям — " . (count($count_posts) ?? count($posts)) . " публикаций";
                break;
        }
    }

    $jsonLd = [
        "@context" => "https://schema.org",
        "@type" => "CreativeWorkSeries",
        "name" => $listName,
        "description" => $listDescription,
        "workExample" => $workExamples
    ];

    return '<script type="application/ld+json">' . PHP_EOL
        . json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        . PHP_EOL . '</script>';
}

function generateJournalCollectionPageJsonLd(
    string $pageType = '',
    string $themeTitle = '',
    string $tagTitle = ''
): string {
    $url = 'https://yogoort.ru/journal';
    $name = "ЙОГУРТ-журнал: статьи, новости и блоги.";

    if (!empty($pageType)) {
        switch ($pageType) {
            case 'news':
                $url .= '/news';
                $name = "ЙОГУРТ-журнал: новости.";
                break;
            case 'articles':
                $url .= '/articles';
                $name = "ЙОГУРТ-журнал: статьи.";
                break;
            case 'blogs':
                $url .= '/blogs';
                $name = "ЙОГУРТ-журнал: блоги.";
                break;
            default:
                // Если это не категория, значит тема или тег
                $url .= '/' . $pageType;

                if (!empty($themeTitle)) {
                    $decodedTheme = strip_tags(html_entity_decode($themeTitle));
                    $name = "ЙОГУРТ-журнал: статьи, новости и блоги по теме «{$decodedTheme}»";
                } elseif (!empty($tagTitle)) {
                    $decodedTag = strip_tags(html_entity_decode($tagTitle));
                    $name = "ЙОГУРТ-журнал: статьи, новости и блоги с тегом «{$decodedTag}»";
                }
                break;
        }
    }

    $jsonLd = [
        "@context" => "https://schema.org",
        "@type" => "CollectionPage",
        "url" => $url,
        "name" => $name
    ];

    return '<script type="application/ld+json">' . PHP_EOL .
        json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
        PHP_EOL . '</script>';
}

function generateJournalBreadcrumbsJsonLd(string $pageType = ''): string
{
    $items = [];

    // Главная
    $items[] = [
        "@type" => "ListItem",
        "position" => 1,
        "name" => "Главная",
        "item" => "https://yogoort.ru/"
    ];

    // Если не главная страница журнала, то добавляем "Журнал"
    if (!empty($pageType)) {
        $items[] = [
            "@type" => "ListItem",
            "position" => 2,
            "name" => "Журнал",
            "item" => "https://yogoort.ru/journal"
        ];
    }

    $jsonLd = [
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => $items
    ];

    return '<script type="application/ld+json">' . PHP_EOL .
        json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
        PHP_EOL . '</script>';
}


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
            $posts_for_jsonld = get_actual_posts('', $theme_id, 0, 10, 0);
            $count_posts = get_actual_posts('', $theme_id, 0, 200, 0);
            echo generateJournalPostsJsonLd($posts_for_jsonld, $count_posts, $requestion_str ?? '', $service_by_slug['title'] ?? '');
            echo generateJournalBreadcrumbsJsonLd($requestion_str ?? '');
            echo generateJournalCollectionPageJsonLd(
                $requestion_str ?? '',
                $service_by_slug['title'] ?? '',
                ''
            );
            $data['breadcrumbs'] .= '<span>/</span><span class="curr-page">' . strip_tags(html_entity_decode($service_by_slug['title'])) . '</span>';
        } else if (get_tag_by_alias($requestion_str)) {
            $include_ads = $main_page = true;
            $tag_by_alias = get_tag_by_alias($requestion_str);
            $tag_id = $tag_by_alias['id'];
            $posts = get_actual_posts('', 0, $tag_id);
            $posts_for_jsonld = get_actual_posts('', 0, $tag_id, 10, 0);
            $count_posts = get_actual_posts('', 0, $tag_id, 200, 0);
            echo generateJournalPostsJsonLd($posts_for_jsonld, $count_posts, $requestion_str ?? '', '', $tag_by_alias['title'] ?? '');
            echo generateJournalBreadcrumbsJsonLd($requestion_str ?? '');
            echo generateJournalCollectionPageJsonLd(
                $requestion_str ?? '',
                '',
                $tag_by_alias['title'] ?? ''
            );
            $data['breadcrumbs'] .= '<span>/</span><span class="curr-page">' . strip_tags(html_entity_decode($tag_by_alias['title'])) . '</span>';
        } else if (strpos($requestion_str, 'id') !== false) {
            $journal_page = 'blogs';
            $blog_id = intval(str_replace('id', '', strtok($requestion_str, '?')));
            $blog = get_blog_by_id($blog_id);
            if (!$blog) responseRedirect('/');

            $specialist = get_specialist($blog['specialist_id']);

            // Проверка: если специалист удалён или не найден — редиректим
            if (!$specialist || $specialist['deleted'] == 1) {
                responseRedirect('/');
            }

            $posts_nolimit = get_blog_posts($blog['id'], 999);

            // Проверка: если нет постов — редиректим
            if (empty($posts_nolimit)) {
                responseRedirect('/');
            }

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
                    $breadcrumbs_title .= "студии йоги";
                } else if ($specialist['classification_id'] == 7) {
                    $breadcrumbs_title .= "адвокатского бюро";
                }
            }
            // Если частное лицо
            if (!$specialist['company']) {
                $lastnameClear = str_replace(' ', '', $specialist['lastname']);
                $nameClear = str_replace(' ', '', $specialist['name']);
                $name = morphos\Russian\inflectName($lastnameClear . ' ' . $nameClear, 'родительный');
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
                // Убираем GET-параметры из алиаса (всё после '?')
                $alias = strtok($requestion_arr[3], '?');

                // Проверяем на существование и выводим конкретный пост блога
                $single_post = get_article_by_alias($alias);

                // Если пост не найден — проверяем параметры
                if (!$single_post) {
                    $hasMetricsParams = false;
                    foreach ($_GET as $key => $val) {
                        if (preg_match('/^(_ym|utm|fbclid|gclid)/', $key)) {
                            $hasMetricsParams = true;
                            break;
                        }
                    }

                    // Редиректим только если это не метрика или аналитика
                    if (!$hasMetricsParams) {
                        responseRedirect('/');
                    }
                } else {
                    // Загружаем посты, начиная с даты текущей записи
                    $posts = get_actual_posts('', 0, 0, 5, 0, 0, $single_post['date']);
                    $data['breadcrumbs'] .= '<a href="/journal/id' . $blog['id'] . '">' . $breadcrumbs_title . '</a><span>/</span><span class="curr-page">' . strip_tags(html_entity_decode($single_post['title'])) . '</span>';
                }
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

            echo generateJournalBreadcrumbsJsonLd($requestion_str ?? '');
        } else {
            // --- добавлено: разрешаем URL с параметрами от метрик/аналитики ---
            $hasMetricsParams = false;
            foreach ($_GET as $key => $val) {
                if (preg_match('/^(_ym|utm|fbclid|gclid)/', $key)) {
                    $hasMetricsParams = true;
                    break;
                }
            }

            // если нет параметров аналитики — редиректим как обычно
            if (!$hasMetricsParams) {
                responseRedirect('/');
            }
        }
    }
}


$menu_page = '';
if (isset($journal_page)) {
    $menu_page = $journal_page;
} else if (isset($single_post)) {
    $menu_page = $single_post['post_type'];
}
$data['journal_menu_mobile'] = $data['journal_menu'] = '
    <li><a href="/journal" class="' . ($main_page ? 'active' : '') . ' icon-1">Актуальное</a></li>
    <li><a href="/journal/news" class="' . ($menu_page == 'news' ? 'active' : '') . ' icon-2">Новости</a></li>
    <li><a href="/journal/articles" class="' . ($menu_page == 'articles' ? 'active' : '') . ' icon-3">Статьи</a></li>
    <li><a href="/journal/blogs" class="' . ($menu_page == 'blogs' ? 'active' : '') . ' icon-4">Блоги</a></li>
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
            (!empty($theme['icon']) ? " style='background-image: url(/assets/img/{$theme['icon']});'" : "") . ">
    <a href='/journal/{$theme['slug']}'>{$theme['title']}</a>
</li>";
    }
}
if (isset($last_theme) && $last_theme != '') {
    $selected = ($theme_id == $last_theme['id']) ? 'selected' : '';
    $data['filter_journal_theme'] .= "<option value='{$last_theme['slug']}' $selected>{$last_theme['title']}</option>";
    $data['mobile_filter_journal'] .= "<li class='{$last_theme['slug']}'" .
        (!empty($last_theme['icon']) ? " style='background-image: url(/assets/img/{$last_theme['icon']});'" : "") . ">
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
            (!empty($theme['icon']) ? " style='background-image: url(/assets/img/{$theme['icon']});'" : "") . ">
        <a href='/journal/{$theme['slug']}'>{$theme['title']}</a>
    </li>";
    }
}
if (isset($last_theme_exp) && $last_theme_exp != '') {
    $selected = ($theme_id == $last_theme_exp['id']) ? 'selected' : '';
    $data['filter_journal_theme'] .= "<option value='{$last_theme_exp['slug']}' $selected>{$last_theme_exp['title']}</option>";
    $data['mobile_filter_journal'] .= "<li class='{$last_theme_exp['slug']}'" .
        (!empty($last_theme_exp['icon']) ? " style='background-image: url(/assets/img/{$last_theme_exp['icon']});'" : "") . ">
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
            <img src="/assets/img/' . htmlspecialchars($banner['image']) . '" alt="Первый боковой баннер журнала">
            <div class="text">
                <p>' . htmlspecialchars_decode($banner['title']) . '</p>
                <button>' . htmlspecialchars_decode($banner['button_text']) . '</button>
            </div>
        </a>';
    } elseif ($banner['id'] == 4) {
        // HTML для второго баннера
        $second_banner = '
        <a href="' . htmlspecialchars($banner['link']) . '" class="item">
            <img src="/assets/img/' . htmlspecialchars($banner['image']) . '" alt="Второй боковой баннер журнала">
            <span class="link">' . htmlspecialchars_decode($banner['title']) . '</span>
        </a>';
    } elseif ($banner['id'] == 5) {
        // HTML для третьего баннера
        $third_banner = '
				<div class="image">
					<img src="/assets/img/' . htmlspecialchars($banner['image']) . '" alt="Третий боковой баннер журнала">
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
            <img src="/assets/img/' . htmlspecialchars($topAd['image']) . '" alt="' . $adLine1 . '" class="image">
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
    $article_title_clean = strip_tags(html_entity_decode($article['title']));
    $alt_text = htmlspecialchars($article_title_clean, ENT_QUOTES, 'UTF-8');

    $data['actual_articles_list'] .= '<a href="/journal/' . $article['alias'] . '" class="item">
        <div class="item-img_wrapper" style="position: relative; overflow: hidden;">
            <img src="/uploads/articles/' . $article['photo'] . '" 
                 alt="' . $alt_text . '" 
                 loading="lazy" 
                 style="display: block; width: 100%; height: 100%; object-fit: cover; object-position: center; pointer-events: none;">
        </div>
        <div>
            <span>' . $article_title_clean . '</span>
        </div>
    </a>';
}

if (isset($single_post) && isset($journal_page) && str_starts_with($_SERVER['REQUEST_URI'], '/journal/id')) {
    $journal_page = ''; // чтобы вызов get_actual_posts('') сработал ниже

    $posts = get_actual_posts('', 0, 0, 5, 0, 0, $single_post['date']);
}
if (isset($journal_page) && !isset($single_post)) {
    $posts = get_actual_posts($journal_page);
    $posts_for_jsonld = get_actual_posts($journal_page, 0, 0, 10, 0);
    $count_posts = get_actual_posts($journal_page, 0, 0, 200, 0);
    if (!isset($blog)) {
        echo generateJournalPostsJsonLd($posts_for_jsonld, $count_posts, $requestion_str);
        echo generateJournalCollectionPageJsonLd($requestion_str ?? '');
    }
}

if (isset($blog)) {
    $lastnameClear = str_replace(' ', '', $specialist['lastname']);
    $nameClear = str_replace(' ', '', $specialist['name']);
    $blog_name = $specialist['company'] ?? $nameClear . " " . $lastnameClear;

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
    $author_posts = get_blog_posts($blog['id'], 999);
    if (!isset($single_post) && !empty($specialist) && $specialist['deleted'] == 0 && !empty($author_posts)) {
        $data['h1_hidden_title'] = '<h1 class="dn">' . $blog_name . '</h1>';
        if (!empty($specialist['photo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/thumb_' . $specialist['photo'])) {
            $specialist['photo_path'] = '/uploads/avatars/thumb_' . $specialist['photo'];
        } elseif (!empty($specialist['photo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/' . $specialist['photo'])) {
            $specialist['photo_path'] = '/uploads/avatars/' . $specialist['photo'];
        } else {
            $specialist['photo_path'] = !empty($specialist['company']) ? '/uploads/avatars/022.webp' : '/uploads/avatars/02.webp';
        }

        $alt_text = htmlspecialchars($blog_name, ENT_QUOTES, 'UTF-8');

        $studio_slug = !empty($specialist['slug']) ? $specialist['slug'] : 'id' . $specialist['id'];
        $studio_url  = '/studio/' . $studio_slug;

        $data['blog_info'] = '<div class="author-block">
            <a href="' . $studio_url . '" class="avatar" style="position: relative; overflow: hidden; display: block;">
                <img src="' . $specialist['photo_path'] . '" 
                     alt="Аватар ' . $alt_text . '" 
                     loading="lazy" 
                     style="display: block; width: 100%; height: 100%; object-fit: cover; object-position: center; pointer-events: none;">
            </a>
            <div class="info">
                <div class="name">
                    <a class="blog_name blog_name_author_page" href="' . $studio_url . '">' . $blog_name . '</a>
                </div>
                <div class="prof">
                    ' . $specialist['classification'] . '
                </div>
                <p class="rating_code"></p>
                ' . ($adress ? ('<div class="adress">' . $adress . '</div>') : '') . '
            </div>
            <a href="' . $studio_url . '" class="link">Перейти на страницу</a>
        </div>';

        function generateAuthorProfileJsonLd(array $specialist, array $posts = [], array $posts_nolimit): string
        {
            if (empty($specialist)) return '';

            // Имя автора
            $authorName = !empty($specialist['company'])
                ? $specialist['company']
                : trim(($specialist['name'] ?? '') . ' ' . ($specialist['lastname'] ?? ''));

            // Тип специалиста
            $additionalType = $specialist['classification'] ?? '';

            // Фото
            $imageUrl = !empty($specialist['photo'])
                ? 'https://yogoort.ru/uploads/avatars/' . $specialist['photo']
                : '';

            // Адрес
            $streetAddress = [];
            if (!empty($specialist['metro'])) $streetAddress[] = 'м. ' . $specialist['metro'];
            if (!empty($specialist['street'])) $streetAddress[] = $specialist['street'];
            if (!empty($specialist['house'])) $streetAddress[] = $specialist['house'];
            $streetAddressStr = implode(', ', $streetAddress);


            // Ограничение постов до 10 последних
            $posts = array_slice($posts, 0, 10);

            $itemListElements = [];
            $position = 1;
            foreach ($posts as $post) {
                // URL страницы специалиста
                $profileUrl = $specialist['url'] ?? 'https://yogoort.ru/journal/id' . $post['blog_id'];
                $itemListElements[] = [
                    "@type" => "ListItem",
                    "position" => $position,
                    "item" => [
                        "@type" => "Article",
                        "url" => full_url($post['url'] ?? ''),
                        "datePublished" => date('Y-m-d', strtotime($post['date'] ?? 'now')),
                        "articleSection" => $post['service_title'] ?? '',
                        "headline" => strip_tags(htmlspecialchars_decode($post['title']) ?? ''),
                        "description" => strip_tags(htmlspecialchars_decode($post['content']) ?? ''),
                        "author" => [
                            "@type" => !empty($specialist['company']) ? "Organization" : "Person",
                            "name" => $authorName
                        ]
                    ]
                ];
                $position++;
            }

            // JSON-LD для профиля
            $profileJsonLd = [
                "@context" => "https://schema.org",
                "@type" => "ProfilePage",
                "url" => $profileUrl,
                "name" => $authorName,
                "mainEntity" => [
                    "@type" => !empty($specialist['company']) ? "Organization" : "Person",
                    "name" => $authorName,
                    "additionalType" => $additionalType,
                    "image" => $imageUrl,
                    "address" => [
                        "@type" => "PostalAddress",
                        "streetAddress" => $streetAddressStr,
                        "addressLocality" => $specialist['city'] ?? '',
                        "addressCountry" => "RU"
                    ]
                ]
            ];

            $postsJsonLd = [
                "@context" => "https://schema.org",
                "@type" => "CreativeWorkSeries",
                "name" => "Публикации автора",
                "description" => "Все опубликованные статьи автора — " . count($posts_nolimit) . " публикаций",
                "workExample" => array_map(function ($post) use ($specialist) {
                    $authorType = !empty($specialist['company']) ? "Organization" : "Person";
                    $authorName = !empty($specialist['company'])
                        ? $specialist['company']
                        : trim(($specialist['name'] ?? '') . ' ' . ($specialist['lastname'] ?? ''));

                    return [
                        "@type" => "Article",
                        "url" => full_url($post['url'] ?? ''),
                        "datePublished" => date('Y-m-d', strtotime($post['date'] ?? 'now')),
                        "articleSection" => $post['service_title'] ?? '',
                        "headline" => strip_tags(htmlspecialchars_decode($post['title']) ?? ''),
                        "description" => strip_tags(htmlspecialchars_decode($post['content']) ?? ''),
                        "author" => [
                            "@type" => $authorType,
                            "name" => $authorName
                        ]
                    ];
                }, $posts)
            ];

            // Объединяем два объекта и выводим
            return '<script type="application/ld+json">' . PHP_EOL .
                json_encode([$profileJsonLd, $postsJsonLd], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
                PHP_EOL . '</script>';
        }


        $posts = get_blog_posts($blog['id'], 10); // последние 10 постов
        $posts_nolimit = get_blog_posts($blog['id'], 999); // последние 10 постов
        echo generateAuthorProfileJsonLd($specialist, $posts, $posts_nolimit);

        /**
         * Генерация JSON-LD для хлебных крошек журнала
         *
         * @param array $single_post Данные поста
         * @param array $specialist Данные специалиста (для блогов)
         * @return string
         */
        function generateJournalBreadcrumbJsonLd(array $specialist = []): string
        {
            // Базовые уровни
            $breadcrumbs = [
                ['name' => 'Главная', 'url' => '/'],
                ['name' => 'Журнал', 'url' => '/journal']
            ];


            // Собираем JSON-LD
            $itemListElements = [];
            foreach ($breadcrumbs as $i => $crumb) {
                $itemListElements[] = [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => strip_tags(htmlspecialchars_decode($crumb['name'])),
                    'item' => full_url($crumb['url'])
                ];
            }

            $breadcrumbLd = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $itemListElements
            ];

            return '<script type="application/ld+json">' . PHP_EOL .
                json_encode($breadcrumbLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
                PHP_EOL . '</script>';
        }
        // Генерация JSON-LD
        echo generateJournalBreadcrumbJsonLd($specialist);
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
    if (!empty($specialist['photo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/thumb_' . $specialist['photo'])) {
        $specialist['photo_path'] = '/uploads/avatars/thumb_' . $specialist['photo'];
    } elseif (!empty($specialist['photo']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/avatars/' . $specialist['photo'])) {
        $specialist['photo_path'] = '/uploads/avatars/' . $specialist['photo'];
    } else {
        $specialist['photo_path'] = !empty($specialist['company']) ? '/uploads/avatars/022.webp' : '/uploads/avatars/02.webp';
    }

    $studio_slug = !empty($specialist['slug']) ? $specialist['slug'] : 'id' . $specialist['id'];
    $studio_url  = '/studio/' . $studio_slug;

    if (isset($blog) && $single_post['post_type'] == 'blogs') {
        $alt_text = htmlspecialchars($blog_name, ENT_QUOTES, 'UTF-8');

        $post_blog_info = '<div class="bottom-block flex">
            <div class="author author-desktop">
                <div class="author__img-wrapper" style="position: relative; overflow: hidden;">
                    <img src="' . $specialist['photo_path'] . '" 
                         alt="Аватар автора ' . $alt_text . '" 
                         loading="lazy" 
                         style="display: block; width: 100%; height: 100%; object-fit: cover; object-position: center; pointer-events: none;">
                </div>
                <div>
                    <p><a class="blog_name" href="' . $studio_url . '">' . $blog_name . '</a></p>
                    <p class="classification">' . $specialist['classification'] . '</p>
                    <p class="rating_code"></p>
                </div>
            </div>
    
            <!-- Мобильная версия -->
            <div class="author author-mobile">
                <div class="author__img-wrapper" style="position: relative; overflow: hidden;">
                    <img src="' . $specialist['photo_path'] . '" 
                         alt="Аватар автора ' . $alt_text . '" 
                         loading="lazy" 
                         style="display: block; width: 100%; height: 100%; object-fit: cover; object-position: center; pointer-events: none;">
                </div>
                <div>
                    <p class="blog_name_mobile"><a class="blog_name" href="' . $studio_url . '">' . $blog_name . '</a></p>
                    <p class="classification">' . $specialist['classification'] . '</p>
                    <p class="rating_code"></p>
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
        <div class="single_post_title">' . $title_clean . '</div>';

    if ($single_post['photo'] != '' && !is_null($single_post['photo'])) {
        $alt_text = htmlspecialchars($title_clean, ENT_QUOTES, 'UTF-8');

        // Убираем lazy-bg и вставляем чистый тег img со всеми стилями
        $data['single_post'] .= '<div class="image" style="position: absolute; overflow: hidden;">
        <img src="/uploads/articles/' . $single_post['photo'] . '" 
             alt="' . $alt_text . '" 
             style="display: block; width: 100%; height: 100%; object-fit: cover; object-position: center; pointer-events: none;">
    </div>';
    }
    if (!empty($single_post['content'])) {
        $html_content = htmlspecialchars_decode($single_post['content']);
    
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
    
        $wrapped_html = '<div>' . mb_convert_encoding($html_content, 'HTML-ENTITIES', 'UTF-8') . '</div>';
        $dom->loadHTML($wrapped_html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
    
        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img) {
            if (!$img->hasAttribute('alt') || trim($img->getAttribute('alt')) === '') {
                $img->setAttribute('alt', 'Изображение к посту журнала');
            }
        }
    
        $container = $dom->getElementsByTagName('div')->item(0);
        $clean_html = '';
        if ($container) {
            foreach ($container->childNodes as $child) {
                $clean_html .= $dom->saveHTML($child);
            }
        } else {
            $clean_html = $html_content;
        }
    
        $data['single_post'] .= $clean_html;
    }
    if ($single_post['site_from_link'] != '' && $single_post['site_from_name'] != '') {
        $data['single_post'] .= '<div class="site">
            Источник <a href="' . $single_post['site_from_link'] . '" rel="nofollow">“' . $single_post['site_from_name'] . '”</a>
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

    /**
     * Генерация JSON-LD для открытой страницы: блог / статья / новость.
     *
     * @param array $post         Массив записи: ['post_type','alias','photo','date','service_title','title','description','content','site_from_name','site_from_link']
     * @param array $tags         Массив тегов: каждый тег ['title', ...]
     * @param array|null $blog    Инфо о блоге/авторе (опционально): ['title', 'url'] или ['name','url']
     * @param array|null $specialist  Инфо о специалисте/авторе (опционально): ['company','name','lastname','id','alias']
     * @param array $siteInfo     Опции издателя: ['name'=>'Сайт', 'url'=>'https://...'] (опционально)
     * @return string             <script type="application/ld+json">...</script>
     */
    function generateBlogJsonLd(array $post, array $tags = [], ?array $blog = null, ?array $specialist = null, array $siteInfo = []): string
    {
        // Выбираем тип: NewsArticle для news, иначе Article
        $type = 'Article';
        if (!empty($post['post_type']) && mb_strtolower($post['post_type']) === 'news') {
            $type = 'NewsArticle';
        }

        // URL поста: используем alias если есть, иначе текущий REQUEST_URI
        $postUrl = null;
        if (!empty($post['alias'])) {
            // предполагаем маршрут /journal/{alias} — при необходимости подкорректируйте
            $postUrl = full_url('/journal/' . ltrim($post['alias'], '/'));
        }
        if (!$postUrl) {
            $postUrl = (isset($_SERVER['REQUEST_URI']) ? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'example.com') . $_SERVER['REQUEST_URI'] : null);
        }

        // Image: ожидаем $post['photo'] — храним в /uploads/articles/ если это относительный файл
        $image = null;
        if (!empty($post['photo'])) {
            if (preg_match('#^/uploads/#', $post['photo']) || preg_match('#^https?://#i', $post['photo'])) {
                $image = full_url($post['photo']);
            } else {
                $image = full_url('/uploads/articles/' . ltrim($post['photo'], '/'));
            }
        }

        // datePublished: приводим к YYYY-MM-DD (если указана дата)
        $datePublished = null;
        if (!empty($post['date'])) {
            $ts = strtotime($post['date']);
            if ($ts !== false) $datePublished = date('Y-m-d', $ts);
        }

        // articleSection
        $articleSection = !empty($post['service_title']) ? strip_tags(html_entity_decode($post['service_title'])) : null;

        // headline
        $headline = !empty($post['title']) ? trim(strip_tags(htmlspecialchars_decode($post['title']))) : null;

        // description (берём explicit description либо весь content)
        $description = null;
        if (!empty($post['description'])) {
            $description = trim(strip_tags(htmlspecialchars_decode($post['description'])));
        } elseif (!empty($post['content'])) {
            $description = trim(strip_tags(htmlspecialchars_decode($post['content'])));
        }

        // keywords из тегов
        $keywords = null;
        if (!empty($tags) && is_array($tags)) {
            $kw = [];
            foreach ($tags as $t) {
                if (!empty($t['title'])) $kw[] = trim(strip_tags($t['title']));
            }
            if (!empty($kw)) $keywords = implode(', ', $kw);
        }

        $author = null;
        if ($post['post_type'] === 'blogs' && !empty($specialist)) {
            if (!empty($specialist['company'])) {
                $author = [
                    "@type" => "Organization",
                    "name"  => strip_tags($specialist['company'])
                ];
            } else {
                $fio = trim(
                    (!empty($specialist['lastname']) ? $specialist['lastname'] . ' ' : '') .
                        (!empty($specialist['name']) ? $specialist['name'] . ' ' : '') .
                        (!empty($specialist['patronymic']) ? $specialist['patronymic'] : '')
                );
                if (!empty($fio)) {
                    $author = [
                        "@type" => "Person",
                        "name"  => $fio
                    ];
                }
            }
        }

        // publisher: только для статей/новостей
        $publisher = null;
        if ($post['post_type'] !== 'blogs') {
            $publisher = [
                "@type" => "Organization",
                "name"  => "ЙОГУРТ.РУ"
            ];
        }


        $articleLd = ['@context' => 'https://schema.org', '@type' => $type];
        if ($postUrl) $articleLd['url'] = $postUrl;
        if ($image) $articleLd['image'] = $image;
        if ($datePublished) $articleLd['datePublished'] = $datePublished;
        if ($articleSection) $articleLd['articleSection'] = $articleSection;
        if ($headline) $articleLd['headline'] = $headline;
        if ($description) $articleLd['description'] = $description;

        // **Логика author/publisher остаётся без изменений**
        if (!empty($author)) {
            $articleLd['author'] = $author;
        }
        if (!empty($publisher)) {
            $articleLd['author'] = $publisher;
        }

        if (!empty($keywords)) $articleLd['keywords'] = $keywords;

        // Возвращаем корректный JSON-LD
        $json = json_encode($articleLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        return "<script type=\"application/ld+json\">\n" . $json . "\n</script>\n";
    }

    /**
     * Генерация JSON-LD для хлебных крошек журнала
     *
     * @param array $single_post Данные поста
     * @param array $specialist Данные специалиста (для блогов)
     * @return string
     */
    function generateJournalBreadcrumbJsonLd(array $single_post, array $specialist = []): string
    {
        if (empty($single_post)) return '';

        // Базовые уровни
        $breadcrumbs = [
            ['name' => 'Главная', 'url' => '/'],
            ['name' => 'Журнал', 'url' => '/journal']
        ];

        // Если блог
        if ($single_post['post_type'] === 'blogs' && !empty($specialist)) {
            $breadcrumbs_title = 'Блог ';

            if ($specialist['company']) {
                $name = '"' . $specialist['company'] . '"';
                $classificationMap = [
                    '5' => 'юридической компании',
                    '6' => 'коллегии адвокатов',
                    '7' => 'адвокатского бюро',
                    '8' => 'студии йоги'
                ];
            } else {
                $name = morphos\Russian\inflectName($specialist['lastname'] . ' ' . $specialist['name'], 'родительный');
                $name = implode(' ', array_reverse(explode(' ', $name)));
                $classificationMap = [
                    '1' => 'юриста',
                    '2' => 'адвоката',
                    '3' => 'эксперта',
                    '4' => 'нотариуса'
                ];
            }

            $breadcrumbs_title .= ($classificationMap[$specialist['classification_id']] ?? 'специалиста') . ' ' . $name;

            // Третий уровень — URL блога без alias поста
            $currentUrl = full_url($_SERVER['REQUEST_URI']);
            $baseBlogUrl = preg_replace('#/[^/]*$#', '', $currentUrl);

            $breadcrumbs[] = [
                'name' => $breadcrumbs_title,
                'url' => $baseBlogUrl
            ];
        }

        // Собираем JSON-LD
        $itemListElements = [];
        foreach ($breadcrumbs as $i => $crumb) {
            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => strip_tags(htmlspecialchars_decode($crumb['name'])),
                'item' => full_url($crumb['url'])
            ];
        }

        $breadcrumbLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElements
        ];

        return '<script type="application/ld+json">' . PHP_EOL .
            json_encode($breadcrumbLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
            PHP_EOL . '</script>';
    }
    // Генерация JSON-LD
    echo generateJournalBreadcrumbJsonLd($single_post, $specialist);

    // Пример использования
    echo generateBlogJsonLd($single_post, $tags, $blog, $specialist, ['name' => 'ЙОГУРТ.РУ', 'url' => 'https://yogoort.ru']);
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

    $post_title_clean = strip_tags(html_entity_decode($post['title']));
    $alt_text = htmlspecialchars($post_title_clean, ENT_QUOTES, 'UTF-8');

    $data['posts'] .= '<div class="item2">
    <div class="flex post-data">
        <a href="/journal/' . $post['service_slug'] . '" class="cat_' . $post['post_type'] . '"><span>' . $post['service_title'] . '</span></a>
        <div class="date">' . date('d.m.Y', strtotime($post['date'])) . '</div>
    </div>
    <a href="' . $post_alias . '" class="stretched-link"></a>
    <a href="' . $post_alias . '" class="name">' . $post_title_clean . '</a>';

    if ($post['photo'] != '' && !is_null($post['photo'])) {
        // Убрали lazy-bg и добавили хардкорный img
        $data['posts'] .= '<a href="' . $post_alias . '" class="image" style="display: block; overflow: hidden; position: relative;">
            <img src="/uploads/articles/' . $post['photo'] . '" 
                 alt="' . $alt_text . '" 
                 loading="lazy" 
                 style="display: block; width: 100%; height: 100%; object-fit: cover; object-position: center; pointer-events: none;">
        </a>';
    }
    if ($post['post_type'] == 'blogs') {
        $content = html_entity_decode($post['content'], ENT_QUOTES, 'UTF-8');
        $content = preg_replace('/<\s*br\s*\/?>/i', ' ', $content); // заменяем <br> на пробел
        $content = strip_tags($content); // убираем остальные теги
        $content = preg_replace('/\s{2,}/u', ' ', trim($content)); // убираем лишние пробелы

        $data['posts'] .= '<a class="content cut-text-blogs" href="' . $post_alias . '">' . $content . '</a>';
    } else {
        $content = html_entity_decode($post['content'], ENT_QUOTES, 'UTF-8');
        $content = preg_replace('/<\s*br\s*\/?>/i', ' ', $content);
        $content = preg_replace('/<\/(p|div|li|h[1-6])>/i', ' ', $content);
        $content = preg_replace('/\r?\n+/u', ' ', $content);
        $content = strip_tags($content);
        $content = preg_replace('/\s{2,}/u', ' ', trim($content));

        $data['posts'] .= '<a class="content cut-text" href="' . $post_alias . '">' . $content . '</a>';
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
    'title' => $title . ' - ЙОГУРТ'
], $HTML);

$HTML = html_explode($data, $HTML);

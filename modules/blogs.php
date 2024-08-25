<?php

include $_SERVER['DOCUMENT_ROOT'] . '/morphos/S.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Cases.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/CasesHelper.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Gender.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/BaseInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/NamesInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/RussianLanguage.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/Cases.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/RussianCasesHelper.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/FirstNamesInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/MiddleNamesInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/LastNamesInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/functions.php';

$requestion_arr = explode('/', $_SERVER['REQUEST_URI']);
if(isset($requestion_arr[2])) {
    $requestion_str = $requestion_arr[2];
    if ($requestion_str != '') {
        $blog_user_id = intval(str_replace('id', '', strtok($requestion_str, '?')));
        $blog_id = get_specialist_blog($blog_user_id)['id'];
    }
} else {
    $blog_id = NULL;
}

// Данные пагинации
$start = 0;
$limit = 6;
$page = intval($_GET['page']) ?: 1;
if ($page) $start = ($page - 1) * $limit;
$data['pagination'] = '';

$theme_id = 0;
if (isset($_GET['theme'])) {
    $theme_id = get_service_by_slug($_GET['theme'])['id'];
}

if ($blog_id == NULL) {
    // Если главная страница блогов
    // получаем блоги главной
    $articles = get_main_blog_articles($theme_id, $limit, $start);
    $count = count_main_blog_articles();
    $blog_id = -1;
    $data['breadcrumbs'] = '<span class="curr-page">Блоги</span>';
    $title = 'Блоги';
    $description = 'На данной странице собраны блоги специалистов и компаний, практикующих в области юриспруденции и занимающихся проведением различного рода экспертных исследований.';
} else {
    // Если страница блога специалиста/компании
    $blog = get_blog_by_id($blog_id);
    // Получаем записи блога специалиста/компании
    $articles = get_blog_articles($blog_id, $theme_id, $limit, $start);
    $count = count_blog_articles($blog_id);

    // Если блога нет делаем редирект на главную
    if (!$blog) {
        responseRedirect('/');
    } else {
        $specialist = get_specialist($blog['specialist_id']);

        // заголовок
        $title = 'Блог ';

        // Если компания
        if ($specialist['company']) {
            $name = '"' . $specialist['company'] . '"';

            if ($specialist['classification_id'] == 5) {
                $title .= "юридической компании";
            } else if ($specialist['classification_id'] == 6) {
                $title .= "коллегии адвокатов";
            } else if ($specialist['classification_id'] == 8) {
                $title .= "экспертного учреждения";
            } else if ($specialist['classification_id'] == 7) {
                $title .= "адвокатского бюро";
            }
        }
        
        // Если частное лицо
        if (!$specialist['company']) {
            $name = morphos\Russian\inflectName($specialist['lastname'] . ' ' . $specialist['name'], 'родительный');
            $name = implode(' ', array_reverse(explode(' ', $name)));

            if ($specialist['classification_id'] == 1) {
                $title .= "юриста";
            } else if ($specialist['classification_id'] == 2) {
                $title .= "адвоката";
            } else if ($specialist['classification_id'] == 3) {
                $title .= "эксперта";
            } else if ($specialist['classification_id'] == 4) {
                $title .= "нотариуса";
            }
        }

        $title .= ' ' . $name;
        // хлебные крошки
        $data['breadcrumbs'] .= '<a href="/journal">Блоги</a><span>/</span><span class="curr-page">' . $title . '</span>';

        // Описание
        $description = nl2br($blog['description']);
    }
}
// получаем темы блога для фильтра 
$themes = get_blog_filter_themes();
$data['mobile_filter_blogs'] = '';

// ссылка блога для фильтров
if($blog_id == NULL || !$blog_id || $blog_id == -1) {
    $blog_link = "";
} else {
    $blog_link = "/id".$specialist['id'];
}

//разделы юристов
foreach ($themes as $theme) {
    if ($theme['classification'] == 3) {
        continue;
    }
    if (mb_stripos($theme['title'], 'прочие') !== false) {
        //раздел "прочие" в конец
        $last_theme = $theme;
    } else {
        $data['mobile_filter_blogs'] .= "<li class='{$theme['slug']}'><a href='/journal{$blog_link}?theme={$theme['slug']}'>{$theme['title']}</a></li>";
    }
}
//раздел "прочие"
if (isset($last_theme) && $last_theme != '') {
    $data['mobile_filter_blogs'] .= "<li class='{$last_theme['slug']}'><a href='/journal{$blog_link}?theme={$last_theme['slug']}'>{$last_theme['title']}</a></li>";
}

//разделы экспертиз
foreach ($themes as $theme) {
    if ($theme['classification'] == 1 || $theme['classification'] == 2) {
        continue;
    }
    if (mb_stripos($theme['title'], 'прочие') !== false) {
        //раздел "прочие" в конец
        $last_theme = $theme;
    } else {
        $data['mobile_filter_blogs'] .= "<li class='{$theme['slug']}'><a href='/journal{$blog_link}?theme={$theme['slug']}'>{$theme['title']}</a></li>";
    }
}
//раздел "прочие"
if (isset($last_theme) && $last_theme != '') {
    $data['mobile_filter_blogs'] .= "<li class='{$last_theme['slug']}'><a href='/journal{$blog_link}?theme={$last_theme['slug']}'>{$last_theme['title']}</a></li>";
}

$data['blog'] = '<div class="top-text">
<h1>' . $title . '</h1>
<p class="blog-page">' . $description . '</p>
</div>';

$data['articles'] = block_articles($articles);

// Пагинация
$data['pagination'] = block_pagination_blog($count, $limit, $page, $blog_id);

// Устанавливаем title
$HTML = html_replace([
    'title' => $title . ' - ЮРФАК'
], $HTML);

$HTML = html_explode($data, $HTML);
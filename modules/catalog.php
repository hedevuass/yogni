<?php

$data = [];

// определяем страницу (главная, категория или подкатегория)
$uri_arr = explode('/', $_SERVER['REQUEST_URI']);
$requestion_str = end($uri_arr);
if ($requestion_str != '') {
$requestion = strtok($requestion_str, '?');
} else if ($requestion_str == ''){
    $requestion = 'yuristy-i-advokaty';
}

$category = get_service_by_slug($requestion);
if (!$category && $requestion!='yuristy-i-advokaty' && $requestion!='experty' && $requestion!='notariusy') responseRedirect('/');
// Данные пагинации
$start = 0;
$limit = 16;
$page = intval($_GET['page']) ?: 1;
if ($page) $start = ($page - 1) * $limit;

$mobile_btn_title = "Выбрать подраздел";
if (!$category) {
    $mobile_btn_title = "Выбрать раздел";
}
$mobile_btn_class = "";

if ($category) {
    // Хлебные крошки
    $vars = vars(classification: $category['classification']);
    $link_for_pagination = $vars['slug'].($category['p_id'] == 0 ? '' : '/' . $category['p_slug']).'/'.$category['slug'];

    $data['breadcrumbs'] = breadcrumbs($vars, $category);

    // Заголовок, меню ссылок, описание категории
    $links = get_links($category);
    $data['top_text'] = top_text($vars, (array)$links, $category);

    $pre_list_text = $vars['pre_list'] . " по направлению";

    // меню-фильтр для мобилки
    $category_mob = $category;
    $mobile = get_mobile($category);
    $mobile_filter_title = $title = $mobile['filter_title'];
    $mobile_filter_class = $mobile['filter_class'];
    $first_cat = $mobile['first_cat'];
    $mobile_btn_class = $mobile['btn_class'];

    // Список специалистов
    $mobile_links = $links;
    if(!get_services_by_parrent_id($category['id'])) {
        $mobile_links = get_services_by_classification($category['classification'], 0);
        $mobile_btn_class = " hidden";
    }
    $services = $list = implode(',', array_keys((array)$links)) . ',' . $category['id'];
    $count = count_specialists_by_service($services);

    $specialists = get_specialists_by_service($services, $limit, $start);
}

if (!$category) {
    $vars = vars(requestion: $requestion);

    $link_for_pagination = $vars['slug'];

    $classification = get_classification($vars['classification'][0]);

    $first_cat = '<li class="find_lawyer"><a href="/yuristy-i-advokaty">Найти всех юристов и адвокатов</a></li>';
    if($classification['id'] == 3) {
        $mobile_btn_title = "Вид экспертизы";
        $first_cat = '<li class="find_expert"><a href="/experty">Найти всех экспертов</a></li>';
    }
    if($classification['id'] == 4) {
        $mobile_btn_title = "Выбрать метро";
        $mobile_btn_class = " metro";
    }

    $pre_list_text = $vars['pre_list'];

    $mobile_filter_title = $title = $vars['title'];

    $data['breadcrumbs'] = breadcrumbs($vars);

    $mobile_links = $links = get_services_by_classification($vars['classification'], 0);

    $data['top_text'] = top_text($vars, $links, classification: $classification);

    $category_mob = false;
    $mobile_filter_class = "";

    $category = [
        'classification' => $vars['classification']
    ];

    $count = count_specialists_by_classification($vars['classification']);
    $specialists = get_specialists_by_classification($vars['classification'], $limit, $start);
}

// меню-фильтр для мобилки
$data['catalog_mobile_filter'] = '<div class="big-mobile-popup mobile-subcat-block'.$mobile_filter_class.'">
                                        <a class="close"></a>
                                        <div class="subcat__section-title">'.$mobile_filter_title.'</div>
                                        <ul class="menu-links-mobile">' . $first_cat;
foreach ($mobile_links as $link) {
    $class_link = get_classification_link($link['classification']);
    if (mb_stripos($link['title'], 'прочие') !== false) {
        $last_link = '<li class="'.$link["slug"].'"><a href="/'.$class_link.'/'.($category_mob ? $category_mob['slug'].'/': '').$link["slug"].'">'.$link["title"].'</a></li>';
    } else {
        $data['catalog_mobile_filter'] .= '<li class="'.$link["slug"].'"><a href="/'.$class_link.'/'.($category_mob ? $category_mob['slug'].'/': '').$link["slug"].'">'.$link["title"].'</a></li>';
    }
}
if (isset($last_link) && $last_link != '') {
    $data['catalog_mobile_filter'] .= $last_link;
}
$data['catalog_mobile_filter'] .= '</ul></div>';

// кнопкa для мобилки
$data['mobile_filter_btn'] = '<button class="btn btn__mobile-filters'.$mobile_btn_class.'">'.
$mobile_btn_title.'<i><img src="/assets/img/mobile-filters-btn.svg" alt=""></i>
</button>';

// Промоблок
$data['promo'] = block_promo($vars['block'], $category['classification']);

$data['pre_list'] = pre_list($pre_list_text);

// Список специалистов
$data['catalog_specialists'] = block($specialists);

// Пагинация
$data['pagination'] = block_pagination($count, $limit, $page, $link_for_pagination, $list ?? $vars);

// Filter
$category['classification'] = is_numeric($category['classification']) ? $category['classification'] : $category['classification'][0];

$filter = match ((int)$category['classification']) {
    1, 2, [1, 2, 5, 6]  => 'lawyer',
    3, [3, 7]           => 'expert',
    4, [4, 8]           => 'notary',
};

// Устанавливаем title
$HTML = html_replace([
    'title' => $title
], $HTML);


$HTML = html_explode($data, $HTML);

// Подставляем меню-фильтр для мобилки
$HTML_ = explode('<!--data:catalog_mobile_filter-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:catalog_mobile_filter-->{$data['catalog_mobile_filter']}<!--data:catalog_mobile_filter-->" . $HTML_[2];

// Подставляем кнопку для мобилки
$HTML_ = explode('<!--data:mobile_filter_btn-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:mobile_filter_btn-->{$data['mobile_filter_btn']}<!--data:mobile_filter_btn-->" . $HTML_[2];

// Подставляем фильтр
$HTML_ = explode('<!--data:filter-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:filter--><!--module:blocks/filter_$filter--><!--module:blocks/filter_$filter--><!--data:filter-->" . $HTML_[2];

// Подставляем фильтр
$HTML_ = explode('<!--data:filter_checkbox_mobile-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--module:blocks/filter_$filter--><!--data:filter_checkbox_mobile-->
<!--data:filter_checkbox_mobile--><!--module:blocks/filter_$filter-->" . $HTML_[2];

$HTML = modules_include($HTML);


/**
 * @param array $vars
 * @param array $category
 * @return string
 */
function breadcrumbs(array $vars, array $category = null): string
{
    $code = '<span>/</span><span class="curr-page">'.$vars['title'].'</a>';

    if ($category) {
        $code = '<span>/</span><a href="/'.$vars["slug"].'">'.$vars['title'].'</a>';
        if ($category['p_id']) $code .= "<span>/</span><a href='/{$vars['slug']}/{$category['p_slug']}'>{$category['p_title']}</a>";
        $code .= '<span>/</span><span class="curr-page">'.$category['title'].'</span>';
    }

    return $code;
}

/**
 * @param array $vars
 * @param array $category
 * @param array $links
 * @return string
 */
function top_text(array $vars, array $links, array $category = [], array $classification = []): string
{
    
    $h1 = ($category ? '' : $vars['h1']) . $category['title'] ?? '';
    //$subtitle = $category['title'] ? $vars['h1'] . '"' . mb_strtolower(($category['title'])) . '"' : '';

    $code = <<<EOF
        <div class="top-text">
            <h1 class="desctop">{$h1}</h1>
            <div class="links">
    EOF;
    foreach ($links as $link) {
        $class_link = get_classification_link($link['classification']);
        if (mb_stripos($link['title'], 'прочие') !== false) {
            $last_link = "<a href='/".$class_link."/".($link['p_id'] === 0 ? '' : $link['p_slug'] . '/')."{$link['slug']}' " . ($link['id'] == $category['id'] ? 'class="active"' : '').">{$link['title']}</a>";
        } else {
            $code .= "<a href='/".$class_link."/".($link['p_id'] === 0 ? '' : $link['p_slug'] . '/')."{$link['slug']}' " . ($link['id'] == $category['id'] ? 'class="active"' : '').">{$link['title']}</a>";
        }
    }
    if (isset($last_link) && $last_link != '') {
        $code .= $last_link;
    }

    $description = $category['description'] ?? $classification['description'];

    $code .= <<<EOF
            </div>
            <p>{$description}</p>
        </div>
    EOF;

    return $code;
}

/**
 * @param array $category
 * @return string
 */
function get_links(array $category): array
{
    $array = get_services_by_parrent_id($category['id']);
    if (!$array) {
        if (in_array($category['classification'], [1,2,3,5,6,7]) && !$category['p_id']) {
            $array = get_services_by_classification($category['classification'], 0);
        } else {
            $array = get_services_by_parrent_id($category['p_id']);
        }
    }

    return $array;
}

function get_mobile(array $category): array
{
    $mobile = [];
    $mobile['first_cat'] = '<li><a href="'.$category['slug'].'">Все подразделы</a></li>';
    $mobile['filter_class'] = ' noimage';
    $mobile['filter_title'] = $category['title'];
    $array = get_services_by_parrent_id($category['id']);
    if (!$array) {
        $mobile['first_cat'] = '';
        $mobile['filter_class'] = '';
        $mobile['btn_class'] = '';
        if (in_array($category['classification'], [1,2,3,5,6,7,8]) && !$category['p_id']) {
            $array = get_services_by_classification($category['classification'], 0);
            if ($category['classification'] == 3 || $category['classification'] == 8) {
                $mobile['first_cat'] = '<li class="find_expert"><a href="/experty">Найти всех экспертов</a></li>';
            } else {
                $mobile['first_cat'] = '<li class="find_lawyer"><a href="/yuristy-i-advokaty">Найти всех юристов и адвокатов</a></li>';
            }
            $mobile['btn_class'] = ' hidden';
        } else {
            $array = get_services_by_parrent_id($category['p_id']);
        }
    }

    return $mobile;
}

/**
 * @param int $classification
 * @param string $requestion
 * @return array
 */
function vars(int $classification = null, string $requestion = null): array
{
    if ($classification) {
        $vars = match ($classification) {
            1, 2 => [
                'title'     => 'Юристы и адвокаты',
                'slug'      => 'yuristy-i-advokaty',
                'h1'        => 'Все юристы и адвокаты по направлению ',
                'block'     => 1,
                'pre_list'  => 'Топ юристы и адвокаты'
            ],
            3 => [
                'title'     => 'Эксперты',
                'slug'      => 'experty',
                'h1'        => 'Все эксперты по направлению ',
                'block'     => 2,
                'pre_list'  => 'Топ эксперты'
            ],
            4 => [
                'title'     => 'Нотариусы',
                'slug'      => 'notariusy',
                'h1'        => '',
                'block'     => 3,
                'pre_list'  => 'Топ нотариусы'
            ]
        };
    }

    if ($requestion) {
        $vars = match ($requestion) {
            'experty' => [
                'title'             => 'Эксперты',
                'slug'              => 'experty',
                'h1'                => 'Все эксперты',
                'classification'    => [3, 7],
                'block'             => 2,
                'pre_list'          => 'Топ эксперты'
            ],
            'notariusy' => [
                'title'             => 'Нотариусы',
                'slug'              => 'notariusy',
                'h1'                => 'Все нотариусы',
                'classification'    => [4, 8],
                'block'             => 3,
                'pre_list'          => 'Топ нотариусы'
            ],
            default => [
                'title'             => 'Юристы и адвокаты',
                'slug'              => 'yuristy-i-advokaty',
                'h1'                => 'Все юристы и адвокаты',
                'classification'    => [1, 2, 5, 6],
                'block'             => 1,
                'pre_list'          => 'Топ юристы и адвокаты'
            ]
        };
    }

    return $vars;
}

/**
 * @param int $block_id
 * @param int|array $classification
 * @return string
 */
function block_promo(int $block_id, int|array $classification): string
{
    $specialists = get_promo_list($block_id);

    $count = count($specialists);

    if ($count < 8) {
        $blocks_limit = 8 - $count;
        $supplement = get_specialists_by_classification($classification, $blocks_limit);
        $specialists = array_merge($specialists, $supplement);
    }

    return block($specialists, 'important');
}

/**
 * @param string $text
 * @return string
 */
function pre_list(string $text): string
{
    return <<<EOF
    <div class="pre-list-text">
        {$text}
    </div>
    EOF;
}

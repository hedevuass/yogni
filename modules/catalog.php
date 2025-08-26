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
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/GeographicalNamesInflection.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/morphos/NounDeclension.php';

$data = [];

// Определяем текущий путь без query-параметров
$clean_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_arr = explode('/', trim($clean_path, '/'));

// Определяем фильтр, если он есть в конце URL
$filter = null;
$last_segment = end($uri_arr);
if (in_array($last_segment, ['private', 'company'])) {
    $filter = $last_segment;
    $filter_type_url = $last_segment;
    array_pop($uri_arr); // удаляем фильтр из массива
}

// Теперь последний сегмент — это категория или подкатегория
$requestion_str = end($uri_arr);
$requestion = $requestion_str !== '' ? strtok($requestion_str, '?') : 'yuristy-i-advokaty';

// Первый сегмент — это родительская категория
$service_parent_slug = $uri_arr[0] ?? 'yuristy-i-advokaty';

// Получаем данные из БД
$service_parent = get_service_parent_catalog($service_parent_slug);

// Проверка существования категории
$category = get_service_by_slug($requestion);
if (!$category && !in_array($requestion, ['yuristy-i-advokaty', 'experty', 'notariusy'])) {
    responseRedirect('/');
}

// Данные пагинации
$start = 0;

$mobile_btn_title = "Выбрать подраздел";
if (!$category) {
    $mobile_btn_title = "Выбрать раздел";
}
$mobile_btn_class = "";

$cityGenitive = $cityGenitive ?: ($_SESSION['city_genitive'] ?? 'Москвы');

if ($category) {
    // Хлебные крошки
    $vars = vars(
        classification: $category['classification'],
        service_parent: $service_parent
    );
    $link_for_pagination = $vars['slug'] . ($category['p_id'] == 0 ? '' : '/' . $category['p_slug']) . '/' . $category['slug'];

    $page = intval($_GET['page']) ?: 1;
    /* $promo_count = count(get_promo_list($vars['block']));  */

    $limit = 24;

    $limit_for_pagination = 24;
    $start = ($page - 1) * $limit;

    $data['breadcrumbs'] = breadcrumbs($vars, $category);

    // Заголовок, меню ссылок, описание категории
    $links = get_links($category);
    $data['top_text'] = top_text(
        vars: $vars,
        links: (array)$links,
        category: $category,
        service_parent: $service_parent,
        filter: $filter,
        cityGenitive: $cityGenitive,
    );

    $pre_list_text = $vars['pre_list'] . " по направлению";

    // меню-фильтр для мобилки
    $category_mob = $category;
    $mobile = get_mobile($category);
    $mobile_filter_title = $title = $mobile['filter_title'];
    $description = $mobile['filter_title'];
    $keywords = $mobile['filter_title'];
    $mobile_filter_class = $mobile['filter_class'];
    $first_cat = $mobile['first_cat'];
    $mobile_btn_class = $mobile['btn_class'];

    // Список специалистов
    $mobile_links = $links;
    if (!get_services_by_parrent_id($category['id'])) {
        // Если подкатегорий нет, формируем строку с только текущими услугами
        $services = $category['id'];
        $mobile_links = get_services_by_classification($category['classification'], 0);
        $mobile_btn_class = " hidden";
    } else {
        // Если есть подкатегории, добавляем их в список услуг
        /* $services = $list = implode(',', array_keys((array)$links)) . ',' . $category['id']; */
        $services = $category['id'];
    }

    $count = count_specialists_by_service($category['classification_array'], $services);

    $specialists = get_specialists_by_service($services, $limit, $start);
    $specialists_with_tarrifs = get_specialists_by_service_with_tariffs($category['classification_array'], $services, $limit, $start);
}

if (!$category) {
    $vars = vars(
        requestion: $requestion,
        service_parent: $service_parent
    );

    $page = intval($_GET['page']) ?: 1;
    /* $promo_count = count(get_promo_list($vars['block']));  */

    $limit = 24;

    $limit_for_pagination = 24;
    $start = ($page - 1) * $limit;

    $link_for_pagination = $vars['slug'];

    $classification = get_classification($vars['classification'][0]);

    $slugs = [
        3 => [
            'slug'  => 'experty',
            'class' => 'find_expert',
            'text'  => 'Все ' . mb_strtolower($service_parent['title'] ?? 'эксперты')
        ],
        'default' => [
            'slug'  => 'yuristy-i-advokaty',
            'class' => 'find_lawyer',
            'text'  => 'Все ' . mb_strtolower($service_parent['title'] ?? 'юристы и адвокаты')
        ],
    ];

    // Определяем нужную категорию
    $key = ($classification['id'] == 3) ? 3 : 'default';
    $item = $slugs[$key];

    // Загружаем иконку
    $icon_url = '';
    $service = get_service_parent_catalog($item['slug']);
    if (!empty($service['icon'])) {
        $icon_url = htmlspecialchars($service['icon']);
    }

    // Формируем style, если есть иконка
    $style = $icon_url
        ? ' style="background-image: url(../assets/img/' . $icon_url . '); background-size: 16px 14px;"'
        : '';

    // Заголовок кнопки (если нужно)
    if ($classification['id'] == 3) {
        $mobile_btn_title = "Вид экспертизы";
    }

    // Собираем <li>
    $first_cat = '<li class="' . $item['class'] . '"' . $style . '><a href="/' . $item['slug'] . '">' . $item['text'] . '</a></li>';

    if ($classification['id'] == 4) {
        if (isset($_SESSION['metro']) && $_SESSION['metro']) {
            $metro = get_metro_by_ids($_SESSION['metro']);
            $count = count($metro);
        
            if ($count > 0) $string = "Выбрано ($count)";
        }
        
        
        $metro_text = $string ?? 'Выбрать метро';
        $mobile_btn_title = "<span class='metro_text'>{$metro_text}</span>";
        $mobile_btn_class = " metro";
    }

    $pre_list_text = $vars['pre_list'];

    $mobile_filter_title = $title = $vars['title'];
    $description = $vars['description'];
    $keywords = $vars['keywords'];

    $data['breadcrumbs'] = breadcrumbs($vars);

    $mobile_links = $links = get_services_by_classification($vars['classification'], 0);

    $data['top_text'] = top_text(
        vars: $vars,
        links: $links,
        classification: $classification,
        service_parent: $service_parent,
        filter: $filter,
        cityGenitive: $cityGenitive
    );

    $category_mob = false;
    $mobile_filter_class = "";

    $category = [
        'classification' => $vars['classification']
    ];

    $count = count_specialists_by_classification($vars['classification']);
    $specialists = get_specialists_by_classification($vars['classification'], $limit, $start);
    $specialists_with_tarrifs = get_specialists_by_classification_with_tariffs($vars['classification'], $limit, $start);
}


$data['catalog_mobile_filter'] = '<div class="big-mobile-popup mobile-subcat-block' . $mobile_filter_class . '">
                                        <a class="close"></a>
                                        <div class="subcat__section-title">' . $mobile_filter_title . '</div>
                                        <ul class="menu-links-mobile">' . $first_cat;

// Получаем текущие параметры URL
$current_params = $_SERVER['QUERY_STRING']; // Получаем строку параметров, например, "specialist=true&company=true"

foreach ($mobile_links as $link) {
    $class_link = get_classification_link($link['classification']);
    // Формируем базовую ссылку для подраздела
    $url = '/' . $class_link . '/' . ($category_mob ? $category_mob['slug'] . '/' : '') . $link["slug"];

    // Если есть параметры в текущем URL, добавляем их в ссылку
    /* if (!empty($current_params)) {
        $url .= '?' . $current_params;
    } */

    // Если в названии ссылки есть слово 'прочие', мы добавляем её отдельно
    if (mb_stripos($link['title'], 'прочие') !== false) {
        $last_link = '<li class="' . $link["slug"] . '"><a href="' . $url . '">' . $link["title"] . '</a></li>';
    } else {
        $data['catalog_mobile_filter'] .= '<li class="' . $link["slug"] . '"' .
            (!empty($link['icon']) ? ' style="background-image: url(../assets/img/' . $link['icon'] . ');"' : '') . '>
    <a href="' . $url . '">' . $link["title"] . '</a>
</li>';
    }
}

if (isset($last_link) && $last_link != '') {
    $data['catalog_mobile_filter'] .= $last_link;
}

$data['catalog_mobile_filter'] .= '</ul></div>';


// кнопкa для мобилки
$data['mobile_filter_btn'] = '<button class="btn btn__mobile-filters' . $mobile_btn_class . '">' .
    $mobile_btn_title . '<i><img src="/assets/img/mobile-filters-btn.svg" alt=""></i>
</button>';

// Промоблок
$filter_type = 'all'; // По умолчанию показываем всех
if (isset($_GET['company']) && $_GET['company'] === 'true') {
    $filter_type = 'companies'; // Фильтр компаний
} elseif (isset($_GET['specialist']) && $_GET['specialist'] === 'true') {
    $filter_type = 'specialists'; // Фильтр частных специалистов
}

// Промо-блок с учётом фильтра
/* if ($page === 1) {
    $data['promo'] = block_promo($vars['block'], $category['classification'], $filter_type);
} */

$data['pre_list'] = pre_list($pre_list_text);

// Список специалистов
$data['catalog_specialists'] = block($specialists_with_tarrifs);

// Пагинация
/* $data['pagination'] = block_pagination($count, $limit_for_pagination, $page, $link_for_pagination, $list ?? $vars); */

// Filter
$category['classification'] = is_numeric($category['classification']) ? $category['classification'] : $category['classification'][0];

$filter = match ((int)$category['classification']) {
    1, 2, [1, 2, 5, 6, 7]  => 'lawyer',
    3, [3, 8]           => 'expert',
    4, [4]           => 'notary',
};

// Устанавливаем title
$HTML = html_replace([
    'title' => $title,
    'description' => $description,
    'keywords' => $keywords
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
    $code = '<span>/</span><span class="curr-page">' . $vars['title'] . '</a>';

    if ($category) {
        $code = '<span>/</span><a href="/' . $vars["slug"] . '">' . $vars['title'] . '</a>';
        if ($category['p_id']) $code .= "<span>/</span><a href='/{$vars['slug']}/{$category['p_slug']}'>{$category['p_title']}</a>";
        $code .= '<span>/</span><span class="curr-page">' . $category['title'] . '</span>';
    }

    return $code;
}

/**
 * @param array $vars
 * @param array $category
 * @param array $links
 * @return string
 */
function top_text(
    array $vars,
    array $links,
    array $category = [],
    array $classification = [],
    array $service_parent = [],
    ?string $filter = null,
    ?string $cityGenitive = null
): string {
    // Метки фильтров
    $filter_labels = [
        'private' => 'Частные',
        'company' => 'Юридические компании'
    ];

    // Собираем h1
    $h1 = $category
        ? $vars['h1'] . ' «' . $category['title'] . '»'
        : $vars['h1'];

    // Если есть фильтр — модифицируем h1
    if ($filter && isset($filter_labels[$filter])) {
        if ($filter === 'private') {
            // ваша существующая логика
            $h1 = $filter_labels[$filter]
                . ' '
                . mb_strtolower(mb_substr($h1, 0, 1))
                . mb_substr($h1, 1);
        } else { // company
            // 1) Юристы и адвокаты
            if (mb_stripos($h1, 'Юристы и адвокаты') === 0) {
                $h1 = preg_replace(
                    '/^Юристы и адвокаты/ui',
                    $filter_labels[$filter],
                    $h1
                );
            }
            // 2) Эксперты
            elseif (mb_stripos($h1, 'Эксперты') === 0) {
                $h1 = preg_replace(
                    '/^Эксперты/ui',
                    'Экспертные учреждения',
                    $h1
                );
            }
            // (при необходимости можно добавить здесь и другие секции)
        }
    }

    // Собираем описание
    $description = htmlspecialchars_decode(
        $category['description']
            ?? $service_parent['description']
    );

    // Если есть фильтр — модифицируем описание теми же правилами
    if ($filter && isset($filter_labels[$filter])) {
        if ($filter === 'private') {
            // Частные юристы
            $description = preg_replace(
                '/\bюристов\b/ui',
                'частных юристов',
                $description
            );

            // Частные эксперты
            $description = preg_replace(
                '/\bэкспертов\b/ui',
                'частных экспертов',
                $description,
                1
            );
        } else { // company
            // Юридические компании
            $description = preg_replace(
                '/\b(лучших\s+)?юристов\s+и\s+адвокатов\b/ui',
                'юридические компании',
                $description
            );

            // Каталог экспертных учреждений
            $description = preg_replace(
                '/\bэкспертов\b/ui',
                'экспертные учреждения',
                $description
            );
        }
    }

    if ($cityGenitive) {
        $basePhrases = [
            'Юристы и адвокаты',
            'Эксперты',
            'Нотариусы',
            'Юридические компании',
            'Экспертные учреждения'
        ];
        $regex = '/^(?:Частные\s+)?(' . implode('|', array_map('preg_quote', $basePhrases)) . ')/iu';

        if (preg_match($regex, $h1, $m)) {
            $full = $m[0];
            $rest = mb_substr($h1, mb_strlen($full));
            $h1   = $full . ' ' . $cityGenitive . $rest;
        } else {
            // Если ни одна ключевая фраза не найдена, добавляем город в конец
            $h1 .= ' ' . $cityGenitive;
        }
    }

    // Строим HTML
    $code = "<div class=\"top-text\">\n"
        . "<h1 class=\"desctop\">{$h1}</h1>\n";

    if (!empty($links)) {
        $code .= "<div class=\"links\">\n";
        foreach ($links as $link) {
            $class_link = get_classification_link($link['classification']);
            $active = ($link['id'] ?? null) == ($category['id'] ?? null) ? ' class="active"' : '';
            $url = "/{$class_link}/"
                . (($link['p_id'] ?? 0) === 0 ? '' : ($link['p_slug'] ?? '') . '/')
                . ($link['slug'] ?? '');
            $code .= "<a href='{$url}'{$active}>{$link['title']}</a>\n";
        }
        $code .= "</div>\n";
    }

    $code .= "<div>{$description}</div>\n</div>";

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
        if (in_array($category['classification'], [1, 2, 3, 5, 6, 7]) && !$category['p_id']) {
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
    $mobile['first_cat'] = '<li><a href="' . $category['slug'] . '">Все подразделы</a></li>';
    $mobile['filter_class'] = ' noimage';
    $mobile['filter_title'] = $category['title'];
    $array = get_services_by_parrent_id($category['id']);
    if (!$array) {
        $mobile['first_cat'] = '';
        $mobile['filter_class'] = '';
        $mobile['btn_class'] = '';
        if (in_array($category['classification'], [1, 2, 3, 5, 6, 7, 8]) && !$category['p_id']) {
            $array = get_services_by_classification($category['classification'], 0);
            if ($category['classification'] == 3 || $category['classification'] == 8) {
                $mobile['first_cat'] = '<li class="find_expert"><a href="/experty">Все эксперты</a></li>';
            } else {
                $mobile['first_cat'] = '<li class="find_lawyer"><a href="/yuristy-i-advokaty">Все юристы и адвокаты</a></li>';
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
function vars(int $classification = null, string $requestion = null, ?array $service_parent = null): array
{
    $vars = [];

    if ($classification) {
        $vars = match ($classification) {
            1, 2 => [
                'title'       => $service_parent['title'],
                'slug'        => 'yuristy-i-advokaty',
                'description' => 'Найдите лучших юристов и адвокатов по всем направлениям. Мы поможем вам решить любые юридические вопросы.',
                'keywords'    => 'юристы, адвокаты, юридическая помощь, консультации',
                'h1'          => 'Юристы и адвокаты по направлению ',
                'block'       => 1,
                'pre_list'    => 'Топ ' . mb_strtolower($service_parent['title']),
            ],
            3 => [
                'title'       => $service_parent['title'],
                'slug'        => 'experty',
                'description' => 'Каталог экспертов для профессиональных консультаций и помощи. Найдите подходящего эксперта прямо сейчас.',
                'keywords'    => 'эксперты, профессиональные консультации, помощь специалистов',
                'h1'          => 'Эксперты по направлению ',
                'block'       => 2,
                'pre_list'    => 'Топ ' . mb_strtolower($service_parent['title']),
            ],
            4 => [
                'title'       => $service_parent['title'],
                'slug'        => 'notariusy',
                'description' => 'Список нотариусов для заверения документов и юридических услуг. Найдите надежного нотариуса.',
                'keywords'    => 'нотариусы, заверение документов, юридические услуги',
                'h1'          => 'Все нотариусы по направлению ',
                'block'       => 3,
                'pre_list'    => 'Топ ' . mb_strtolower($service_parent['title']),
            ]
        };
    }

    if ($requestion) {
        $vars = match ($requestion) {
            'experty' => [
                'title'             => $service_parent['title'],
                'slug'              => 'experty',
                'description'       => 'Каталог экспертов из различных областей. Мы собрали лучших специалистов для ваших нужд.',
                'keywords'          => 'эксперты, профессионалы, услуги экспертов',
                'h1'                => $service_parent['title'],
                'classification'    => [3, 8],
                'block'             => 2,
                'pre_list'          => 'Топ ' . mb_strtolower($service_parent['title']),
            ],
            'notariusy' => [
                'title'             => $service_parent['title'],
                'slug'              => 'notariusy',
                'description'       => 'Лучшие нотариусы для заверения документов и юридических услуг. Ознакомьтесь с нашим каталогом.',
                'keywords'          => 'нотариусы, услуги нотариуса, заверение документов',
                'h1'                => $service_parent['title'],
                'classification'    => [4],
                'block'             => 3,
                'pre_list'          => 'Топ ' . mb_strtolower($service_parent['title']),
            ],
            default => [
                'title'             => $service_parent['title'],
                'slug'              => 'yuristy-i-advokaty',
                'description'       => 'Лучшие юристы и адвокаты для ваших юридических вопросов. Найдите подходящего специалиста прямо сейчас.',
                'keywords'          => 'юристы, адвокаты, консультации, юридическая помощь',
                'h1'                => $service_parent['title'],
                'classification'    => [1, 2, 5, 6, 7],
                'block'             => 1,
                'pre_list'          => 'Топ ' . mb_strtolower($service_parent['title']),
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
/* function block_promo(int $block_id, int|array $classification, string $filter_type = 'all'): string
{
    // Получаем список специалистов для промо с фильтрацией по alias, если он есть
    $specialists = get_promo_list($block_id);

    // Фильтруем специалистов, если выбран фильтр "Частные" или "Компании"
    if ($filter_type === 'specialists') {
        $specialists = array_filter($specialists, function ($specialist) {
            return empty($specialist['company_name']); // Частные лица: нет названия компании
        });
    } elseif ($filter_type === 'companies') {
        $specialists = array_filter($specialists, function ($specialist) {
            return !empty($specialist['company_name']); // Компании: есть название компании
        });
    }

    return block($specialists, 'important');
} */


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

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        let limit = 24;
        let start = <?= $limit ?>; // из PHP
        let loading = false;
        let hadError = false; // флаг «ошибка уже была»
        let categorySlug = '<?= addslashes($requestion) ?>';
        let metro_ids = <?= json_encode($_SESSION['metro'] ?? []) ?>;
        let filter = <?= $filter !== null ? "'" . addslashes($filter) . "'" : 'null' ?>;
        let filter_type_url = <?= $filter_type_url !== null ? "'" . addslashes($filter_type_url) . "'" : 'null' ?>;

        function checkAndLoad() {
            if (loading || hadError) return;

            const triggerEl = document.getElementById('load-trigger');
            if (!triggerEl) return;

            const rect = triggerEl.getBoundingClientRect();
            const triggerTop = rect.top;
            const viewportHeight = window.innerHeight;

            // Если #load-trigger в зоне [низа экрана - 800px, низа экрана + 200px]
            if (triggerTop <= viewportHeight + 800 && triggerTop >= viewportHeight - 800) {
                loading = true;

                $.ajax({
                    url: '/actions/infinitload/load_more_specialists.php',
                    type: 'POST',
                    data: {
                        limit: limit,
                        start: start,
                        slug: categorySlug,
                        filter: filter,
                        filter_type_url: filter_type_url,
                        metro_ids: metro_ids
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.html && response.html.trim() !== '') {
                            $('.grid_flex').append(response.html);
                            start += limit;
                            loading = false;
                        } else {
                            // Больше специалистов нет — отключаем дальнейшие проверки
                            $(window).off('scroll', onScrollHandler);
                        }
                    },
                    error: function() {
                        // 1) Устанавливаем флаг, чтобы больше не пытаться
                        hadError = true;
                        // 2) Отключаем обработчик scroll, чтобы не было новых запросов
                        $(window).off('scroll', onScrollHandler);
                        // 3) Показываем alert только один раз
                        alert('Ошибка загрузки, проверьте подключение к интернету.');
                        loading = false;
                    }
                });
            }
        }

        function onScrollHandler() {
            checkAndLoad();
        }

        $(window).on('scroll', onScrollHandler);

        // Однократная проверка, если при загрузке страницы пользователь уже внизу
        setTimeout(function() {
            checkAndLoad();
        }, 100);
    });
</script>
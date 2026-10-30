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
$uri_arr = array_values(array_filter($uri_arr, 'strlen')); // удаляем пустые и переиндексируем

// Фильтр private/company (если есть в конце)
$filter = null;
$last_segment = end($uri_arr);
if (in_array($last_segment, ['private', 'company'])) {
    $filter = $last_segment;
    $filter_type_url = $last_segment;
    array_pop($uri_arr); // удаляем фильтр из массива
}
// Если путь пустой — ставим корень
if (empty($uri_arr)) {
    $uri_arr = ['yoga-studii'];
}

$first = $uri_arr[0];

// Получаем данные из БД по первому сегменту
$service_parent = get_service_parent_by_slug($first);
$first_category = get_service_by_slug($first);

// Если только один сегмент в URL
if (count($uri_arr) === 1) {
    if (!$service_parent && !$first_category && !in_array($first, get_catalog_slugs(), true)) {
        responseRedirect('/404');
    }

    if ($service_parent) {
        $category = null;
    } else {
        $category = $first_category;
        $service_parent = null;
    }
} else {
    // Многосегментный URL — проверяем вложенность
    if ($service_parent) {
        $previous_type = 'parent';
        $category = null;
    } else {
        responseRedirect('/404');
    }

    // Проходим по остальным сегментам
    foreach (array_slice($uri_arr, 1) as $slug) {
        $item = get_service_by_slug($slug);
        if (!$item) {
            responseRedirect('/404');
        }

        if ($previous_type === 'parent') {
            // Для верхнего уровня p_id должен быть 0
            if ((int)$item['p_id'] !== 0) {
                responseRedirect('/404');
            }
        } else { // предыдущий — категория
            if ((int)$item['p_id'] !== $previous_id) {
                responseRedirect('/404');
            }
        }

        $category = $item;
        $previous_type = 'category';
        $previous_id = (int)$item['id'];
    }
}

// Определяем target_category_id
if (!empty($category)) {
    $target_category_id = (int)$category['id'];
} elseif (!empty($service_parent)) {
    $target_category_id = (int)$service_parent['id'];
} else {
    responseRedirect('/404');
}

$selected_amenities = !empty($_GET['amenities']) ? array_filter(array_map('intval', explode(',', $_GET['amenities']))) : [];
$selected_equipment = !empty($_GET['equipment']) ? array_filter(array_map('intval', explode(',', $_GET['equipment']))) : [];
$selected_price = !empty($_GET['price']) ? array_filter(explode(',', $_GET['price'])) : [];

// Объединяем всё в единый массив ID, которые должны быть у студии
$all_required_filters = array_merge($selected_amenities, $selected_equipment);

// --- Настройка для функции получения специалистов ---
// Формируем строгий $services и $classification_array
if ($category) {
    $services = (string)$category['id'];
    $classification_array = $category['classification_array'] ?? [$category['classification']];
} elseif ($service_parent) {
    $child_services = get_services_by_parrent_id((int)$service_parent['id']);
    if ($child_services) {
        $services = implode(',', array_column($child_services, 'id'));
        $classification_array = [];
        foreach ($child_services as $c) {
            $classification_array[] = $c['classification'];
        }
    } else {
        $services = (string)$service_parent['id'];
        $classification_array = [$service_parent['classification']];
    }
} else {
    $services = '';
    $classification_array = [];
}

// Получаем специалистов строго по выбранной категории
$specialists_with_tarrifs = get_specialists_by_service_with_tariffs(
    $classification_array,
    $services,
    $limit ?? 24,
    $start ?? 0
);

// Если специалистов нет — массив пустой
if (!$specialists_with_tarrifs) {
    $specialists_with_tarrifs = [];
}

// Данные пагинации
$start = 0;

$total_filters_count = count($selected_amenities) + count($selected_equipment) + count($selected_price);

$mobile_btn_title = "Фильтры";
// Если выбрано больше 0, добавляем общее число в скобках
if ($total_filters_count > 0) {
    $mobile_btn_title = "Фильтры ($total_filters_count)";
}
$mobile_btn_class = "";

$cityGenitive = $cityGenitive ?: ($_SESSION['city_genitive'] ?? 'Москвы');

if ($category) {
    // Хлебные крошки
    $vars = vars(
        classification: $category['classification'],
        service_parent: $service_parent,
        cityGenitive: $cityGenitive
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
            // 2) Студии йоги
            elseif (mb_stripos($h1, 'Студии йоги') === 0) {
                $h1 = preg_replace(
                    '/^Студии йоги/ui',
                    'Студии йоги',
                    $h1
                );
            }
            // (при необходимости можно добавить здесь и другие секции)
        }
    }
    if ($cityGenitive) {
        $basePhrases = [
            'Юристы и адвокаты',
            'Студии йоги',
            'Нотариусы',
            'Юридические компании',
            'Студии йоги'
        ];
        $escapedPhrases = array_map(function ($phrase) {
            return preg_replace('/\s+/', '\s+', preg_quote($phrase));
        }, $basePhrases);

        // Убираем ^, чтобы искать фразу в любом месте строки, и оборачиваем всё в общую группу
        $regex = '/((?:Частные\s+)?(?:' . implode('|', $escapedPhrases) . '))/iu';

        if (preg_match($regex, $h1, $m)) {
            $full = $m[0];
            $rest = mb_substr($h1, mb_strlen($full));
            $h1   = $full . ' ' . $cityGenitive . $rest;
        } else {
            // Если ни одна ключевая фраза не найдена, добавляем город в конец
            $h1 .= ' ' . $cityGenitive;
        }
    }

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
        $mobile_btn_class = "";
    } else {
        // Если есть подкатегории, добавляем их в список услуг
        /* $services = $list = implode(',', array_keys((array)$links)) . ',' . $category['id']; */
        $services = $category['id'];
    }

    $count = count_specialists_by_service($category['classification_array'], $services);

    $specialists = get_specialists_by_service($services, $limit, $start);
    $specialists_with_tarrifs = get_specialists_by_service_with_tariffs($category['classification_array'], $services, $limit, $start, null, false, $selected_amenities, $selected_equipment, $selected_price);
    $specialists_with_tarrifs = loadSpecializationList($specialists_with_tarrifs);

    // Формирование JSON LD
    $jsonld_limit = 20;
    $jsonld_start = ($page - 1) * $jsonld_limit;

    // Получаем специалистов для JSON-LD
    $specialists_jsonld = get_specialists_by_service_with_tariffs($category['classification_array'], $services, $jsonld_limit, $jsonld_start);
    $specialists_without_limit = get_specialists_by_service_with_tariffs($category['classification_array'], $services, 9999, $jsonld_start);
    $specialists_to_map = get_specialists_by_service_with_tariffs($category['classification_array'], $services, 9999, $jsonld_start, null, true, $selected_amenities, $selected_equipment, $selected_price);

    $data['catalog_specialists'] = block_catalog($specialists_with_tarrifs);
    $data['catalog_specialists_mob'] = block_mobile($specialists_with_tarrifs);

    /* $specialists_with_tarrifs = getCachedData(
        'catalog_by_service', 
        600,
        function () use ($category, $services, $limit, $start) {
            return get_specialists_by_service_with_tariffs(
                $category['classification_array'],
                $services,
                $limit,
                $start
            );
        }
    ); */

    // Формируем заголовок с фильтром, если есть
    $categoryTitle = $category['title'];
    if (!empty($filter)) {
        $categoryTitle .= $filter === 'private' ? ' (частные)' : ' (компании)';
    }

    // Формируем заголовок категории с учетом фильтра
    $categoryTitle = $category['title'];
    if (!empty($filter)) {
        $categoryTitle .= $filter === 'private' ? ' (частные)' : ' (компании)';
    }

    // CollectionPage JSON-LD
    echo generateSpecialistsCollectionPageJsonLd(
        vars: $vars,
        category: $category,
        service_parent: $service_parent,
        filter: $filter,
        cityGenitive: $cityGenitive,
    );

    // Breadcrumbs JSON-LD
    echo generateCatalogBreadcrumbsJsonLd($vars, $category, $filter ?? '');

    // ItemList JSON-LD
    echo generateSpecialistsItemListJsonLd(
        specialists: $specialists_jsonld,
        specialist_wihout_limit: $specialists_without_limit,
        vars: $vars,
        category: $category,
        service_parent: $service_parent,
        filter: $filter ?? null,
        cityGenitive: $cityGenitive,
        categoryDescription: $category['description'] ?? ''
    );
}

if (!$category) {
    $requestion_for_vars = $service_parent['base_slug'] ?? $service_parent['slug'] ?? $requestion;

    $vars = vars(
        requestion: $requestion_for_vars,
        service_parent: $service_parent,
        cityGenitive: $cityGenitive
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
            'slug'  => 'yoga-studii',
            'class' => 'find_expert',
            'text'  => 'Все ' . mb_strtolower($service_parent['title'] ?? 'Студии йоги')
        ],
        /* 'default' => [
            'slug'  => 'yuristy-i-advokaty',
            'class' => 'find_lawyer',
            'text'  => 'Все ' . mb_strtolower($service_parent['title'] ?? 'юристы и адвокаты')
        ], */
        'default' => [
            'slug'  => 'yoga-studii',
            'class' => 'find_expert',
            'text'  => 'Все ' . mb_strtolower($service_parent['title'] ?? 'Студии йоги')
        ],
    ];

    // Определяем нужную категорию
    $key = ($classification['id'] == 3) ? 3 : 'default';
    $item = $slugs[$key];

    // Загружаем иконку
    $icon_url = '';
    $service = get_service_parent_by_slug($item['slug']);
    if (!empty($service['icon'])) {
        $icon_url = htmlspecialchars($service['icon']);
    }

    // Формируем style, если есть иконка
    $style = $icon_url
        ? ' style="background-image: url(../assets/img/' . $icon_url . '); background-size: 16px 14px;"'
        : '';

    // Заголовок кнопки (если нужно)
    if ($classification['id'] == 3) {
        $mobile_btn_title = "Фильтры";
        if ($total_filters_count > 0) {
            $mobile_btn_title = "Фильтры ($total_filters_count)";
        }
        $mobile_btn_class = "";
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
            // 2) Студии йоги
            elseif (mb_stripos($h1, 'Студии йоги') === 0) {
                $h1 = preg_replace(
                    '/^Студии йоги/ui',
                    'Студии йоги',
                    $h1
                );
            }
            // (при необходимости можно добавить здесь и другие секции)
        }
    }
    if ($cityGenitive) {
        $basePhrases = [
            'Студии йоги',
            'Занятия по йоге'
        ];
        $escapedPhrases = array_map(function ($phrase) {
            return preg_replace('/\s+/', '\s+', preg_quote($phrase));
        }, $basePhrases);

        // Убираем ^, чтобы искать фразу в любом месте строки, и оборачиваем всё в общую группу
        $regex = '/((?:Частные\s+)?(?:' . implode('|', $escapedPhrases) . '))/iu';

        if (preg_match($regex, $h1, $m)) {
            $full = $m[0];
            $rest = mb_substr($h1, mb_strlen($full));
            $h1   = $full . ' ' . $cityGenitive . $rest;
        } else {
            // Если ни одна ключевая фраза не найдена, добавляем город в конец
            $h1 .= ' ' . $cityGenitive;
        }
    }

    $category_mob = false;
    $mobile_filter_class = "";

    $category = [
        'classification' => $vars['classification']
    ];

    $count = count_specialists_by_classification($vars['classification']);
    $specialists = get_specialists_by_classification($vars['classification'], $limit, $start);
    $specialists_with_tarrifs = get_specialists_by_classification_with_tariffs($vars['classification'], $limit, $start, null, [], false, $selected_amenities, $selected_equipment, $selected_price);
    $specialists_with_tarrifs = loadSpecializationList($specialists_with_tarrifs);
    // Формирование JSON LD
    $jsonld_limit = 20;
    $jsonld_start = ($page - 1) * $jsonld_limit;

    // Получаем специалистов для JSON-LD
    $specialists_jsonld = get_specialists_by_classification_with_tariffs($vars['classification'], $jsonld_limit, $jsonld_start);
    $specialists_without_limit = get_specialists_by_classification_with_tariffs($vars['classification'], 9999, $jsonld_start);
    $specialists_to_map = get_specialists_by_classification_with_tariffs($vars['classification'], 9999, $jsonld_start, null, [], true, $selected_amenities, $selected_equipment, $selected_price);

    $data['catalog_specialists'] = block_catalog($specialists_with_tarrifs, false, true);
    $data['catalog_specialists_mob'] = block_mobile($specialists_with_tarrifs, false, true);

    /* $specialists_with_tarrifs = getCachedData(
        'catalog_by_classification', 
        600,
        function () use ($vars, $limit, $start) {
            return get_specialists_by_classification_with_tariffs(
                $vars['classification'],
                $limit,
                $start
            );
        }
    ); */

    // CollectionPage JSON-LD
    echo generateSpecialistsCollectionPageJsonLd(
        vars: $vars,
        classification: $classification,
        service_parent: $service_parent,
        filter: $filter,
        cityGenitive: $cityGenitive
    );

    // Breadcrumbs JSON-LD
    echo generateCatalogBreadcrumbsJsonLd([
        'slug'  => $vars['slug'],
        'title' => $vars['title']
    ], null, $filter ?? '');

    // ItemList JSON-LD
    echo generateSpecialistsItemListJsonLd(
        specialists: $specialists_jsonld,
        specialist_wihout_limit: $specialists_without_limit,
        vars: $vars,
        category: [],
        classification: $classification,
        service_parent: $service_parent,
        filter: $filter ?? null,
        cityGenitive: $cityGenitive,
        categoryDescription: $service_parent['description'] ?? ''
    );
}

$get_amenities = !empty($_GET['amenities']) ? explode(',', $_GET['amenities']) : [];
$get_equipment = !empty($_GET['equipment']) ? explode(',', $_GET['equipment']) : [];
$get_price = !empty($_GET['price']) ? explode(',', $_GET['price']) : [];

$all_amenities = get_equipment_by_category('amenity');
$all_equipment = get_equipment_by_category('equipment');

$price_options = [
    ['id' => 'under_1000', 'name' => "Менее 1000 ₽"],
    ['id' => '1000_5000',  'name' => "1000 ₽ - 5000 ₽"],
    ['id' => 'over_5000',  'name' => "Более 5000 ₽"]
];

$data['catalog_mobile_filter'] = '
<div class="big-mobile-popup mobile-subcat-block filter-popup-flex ' . $mobile_filter_class . '">
    <div class="mobile-filter-main-body">
        <a class="close"></a>

        <div class="mobile-filter-section mobile-filter-section-amenity">
            <div class="mobile-filter-title">Удобства в студии</div>
            <div class="mobile-filter-list custom-select-scroll">';
foreach ($all_amenities as $item) {
    $checked = in_array($item['id'], $get_amenities) ? 'checked' : '';
    $data['catalog_mobile_filter'] .= '
                <label class="dropdown-item">
                    <span>' . htmlspecialchars($item['name']) . '</span>
                    <input type="checkbox" class="mob-amenity-ch" value="' . $item['id'] . '" ' . $checked . '>
                </label>';
}
$data['catalog_mobile_filter'] .= '
            </div>
        </div>

        <div class="mobile-filter-section mobile-filter-section-equipment">
            <div class="mobile-filter-title">Оборудование в студии</div>
            <div class="mobile-filter-list custom-select-scroll">';
foreach ($all_equipment as $item) {
    $checked = in_array($item['id'], $get_equipment) ? 'checked' : '';
    $data['catalog_mobile_filter'] .= '
                <label class="dropdown-item">
                    <span>' . htmlspecialchars($item['name']) . '</span>
                    <input type="checkbox" class="mob-equipment-ch" value="' . $item['id'] . '" ' . $checked . '>
                </label>';
}
$data['catalog_mobile_filter'] .= '
            </div>
        </div>

        <div class="mobile-filter-section mobile-filter-section-price">
            <div class="mobile-filter-title">Стоимость занятия</div>
            <div class="mobile-filter-list custom-select-scroll">';
foreach ($price_options as $item) {
    $checked = in_array($item['id'], $get_price) ? 'checked' : '';
    $data['catalog_mobile_filter'] .= '
                <label class="dropdown-item">
                    <span>' . htmlspecialchars($item['name']) . '</span>
                    <input type="checkbox" class="mob-price-ch" value="' . $item['id'] . '" ' . $checked . '>
                </label>';
}
$data['catalog_mobile_filter'] .= '
            </div>
        </div>
        
    </div>

    <div class="dropdown-divider"></div>
    <div class="mobile-filter-footer">
        <button type="button" class="btn-confirm-mobile">Подтвердить</button>
        <button type="button" class="btn-reset-mobile">Сбросить</button>
    </div>
</div>';

$map_ids_json = "'" . implode(',', array_column($specialists_to_map, 'id')) . "'";

$h1_map = $h1 . ' с учетом заданных фильтров';

$data['popup_map'] = '
    <div class="popup map">
        <div class="bg"></div>
        <div class="window">
            <div class="close"></div>
            <div class="reg">
                <div class="name">' . $h1_map . '</div>
                <div class="mmap">
                    <div id="catalog_map" style="width: 100%; height: 60vh; min-height: 350px; max-height: 700px;"></div>
                    
                    <script src="https://api-maps.yandex.ru/2.1/?apikey=129a1c0b-f07a-49fa-96f8-abd7d687e8e9&lang=ru_RU" type="text/javascript"></script>
                   <script type="text/javascript">
                        var myMap; 
                        var allPoints = [];
                        var allPlacemarks = {};
                        var isMapDataLoaded = false;
                        var updateTimer = null;

                        ymaps.ready(function () {
                            myMap = new ymaps.Map("catalog_map", {
                                center: [55.751244, 37.618423],
                                zoom: 10,
                                controls: ["zoomControl", "fullscreenControl"]
                            }, {
                                searchControlProvider: "yandex#search",
                                balloonPanelMaxMapArea: 0
                            });

                            var clusterer = new ymaps.Clusterer({
                                preset: "islands#invertedOrangeClusterIcons",
                                clusterIconColor: "#F2606C",
                                groupByCoordinates: false
                            });
                            myMap.geoObjects.add(clusterer);

                            function groupPoints(points) {
                                var grouped = {};
                                points.forEach(function(p) {
                                    var k = p.lat + "_" + p.lng;
                                    if (!grouped[k]) grouped[k] = { lat: p.lat, lng: p.lng, items: [] };
                                    grouped[k].items.push(p);
                                });
                                return grouped;
                            }

                            // Привязываем функции к window, чтобы они точно были доступны везде
                            window.updateVisible = function() {
                                var bounds = myMap.getBounds();
                                var latMin = bounds[0][0], latMax = bounds[1][0];
                                var lngMin = bounds[0][1], lngMax = bounds[1][1];

                                var visible = allPoints.filter(function(p) {
                                    return p.lat >= latMin && p.lat <= latMax
                                        && p.lng >= lngMin && p.lng <= lngMax;
                                });

                                var grouped = groupPoints(visible);
                                var toAdd = [];

                                Object.keys(grouped).forEach(function(k) {
                                    if (allPlacemarks[k]) return;
                                    var g = grouped[k];
                                    var content = g.items.map(function(p) {
                                        return "<div style=\"margin-bottom:8px;border-bottom:1px solid #eee;padding-bottom:4px;\">" +
                                            "<a href=\"" + p.url + "\" style=\"color:#000;text-decoration:none;font-weight:500;\">" + p.balloon + "</a>" +
                                            "</div>";
                                    }).join("");

                                    var pm = new ymaps.Placemark([g.lat, g.lng], {
                                        balloonContent: "<div style=\"max-height:150px;min-width:200px;overflow-y:auto;padding-right:10px;\">" + content + "</div>"
                                    }, {
                                        preset: "islands#icon",
                                        iconColor: "#F2606C"
                                    });

                                    allPlacemarks[k] = pm;
                                    toAdd.push(pm);
                                });

                                if (toAdd.length) clusterer.add(toAdd);
                            };

                            window.loadMapData = function() {
                                if (isMapDataLoaded) return;

                                $.post("/actions/blocks/get_map_points.php", {
                                    ids: ' . $map_ids_json . '
                                }, function(points) {
                                    if (!points || !points.length) return;
                                    allPoints = points;
                                    isMapDataLoaded = true;
                                    window.updateVisible();

                                    myMap.events.add("boundschange", function() {
                                        clearTimeout(updateTimer);
                                        updateTimer = setTimeout(window.updateVisible, 300);
                                    });
                                }, "json");
                            };
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
';


// кнопкa для мобилки
$data['mobile_filter_btn'] = '<div class="mobile-controls-wrapper">
<button class="btn btn__mobile-filters' . $mobile_btn_class . '">' .
    $mobile_btn_title . '<i><img src="/assets/img/mobile-filters-btn.svg" alt=""></i>
</button>
<button class="map-button map-button-catalog"><img class="map-image-default" src="/assets/img/map.svg" alt=""></button>
</div>';

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

$journal_banners = get_journal_banners();

$filteredBanners = array_filter($journal_banners, function ($banner) {
    return in_array($banner['id'], [6]);
});

foreach ($filteredBanners as $index => $banner) {
    if ($banner['id'] == 6) {
        // HTML для первого баннера
        $sixth_banner = '
        <a href="' . htmlspecialchars($banner['link']) . '" class="banner">
            <img src="/assets/img/' . htmlspecialchars($banner['image']) . '" alt="Первый боковой баннер журнала">
            <div class="text">
                <p class="title">' . htmlspecialchars_decode($banner['title']) . '</p>
                <p class="subtitle">' . htmlspecialchars_decode($banner['description']) . '</p>
                <button>' . htmlspecialchars_decode($banner['button_text']) . '</button>
            </div>
        </a>';
    }
}

$data['sixth_banner'] = '<div class="sticky-banner">' . $sixth_banner . '</div>';

// Пагинация
/* $data['pagination'] = block_pagination($count, $limit_for_pagination, $page, $link_for_pagination, $list ?? $vars); */

// Filter
$category['classification'] = is_numeric($category['classification']) ? $category['classification'] : $category['classification'][0];

$filter = match ((int)$category['classification']) {
    1, 2, [1, 2, 5, 6, 7]  => 'lawyer',
    3, [3, 8]           => 'expert',
    4, [4]           => 'notary',
};

$randId = 'id' . bin2hex(random_bytes(6));

$data['specialist_popup'] .= <<<EOF
<div class="popup write">
  <div class="bg"></div>
  <div class="window middle">
      <div class="close"></div>
      <div class="reg">
          <div class="name">Форма записи</div>
          <div class="form">
                <div class="field">
                    <input class="inp" name="name" maxlength="30" type="text" placeholder="Ваше имя (не более 30 символов)" />
                    <div class="invalid-feedback"></div>
                </div>
              <div class="field">
                  <input type="text" class="phone-input-reg" name="contactInfo" placeholder="Номер вашего телефона для связи" />
                  <div class="invalid-feedback"></div>
                  <input type="hidden" id="specialist_mail" value="{$email}">
                  <input type="hidden" name="specialist_id" id="specialist_id" value="{$specialist_id}">
              </div>
              <div class="field captcha-field">
                <div class="captcha-row">
                    <div class="captcha-left">
                    <img id="captcha" src="/libs/securimage/securimage_show.php?id={$randId}" alt="CAPTCHA" aria-hidden="false" />
                    <button type="button" id="captcha_refresh" class="captcha-refresh" title="Обновить каптчу" aria-label="Обновить каптчу">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M21 12a9 9 0 10-3.2 6.6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M21 3v6h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    </div>

                    <div class="captcha-right">
                    <label class="captcha-label" for="captcha_code">Подтвердите, что вы не робот</label>
                    <input class="captcha-input" id="captcha_code" name="captcha_code" type="text" placeholder="Введите цифры" autocomplete="off" />
                    <input type="hidden" id="captcha_captcha_id" name="captcha_id" value="{$randId}" />
                    </div>
                </div>
                <div class="captcha-error"></div>
                </div>

              <div class="button">
                  <p>
                      Нажимая “Отправить”, я принимаю изложенное в документах,
                      представленных в разделе сайта “О проекте”.
                  </p>
                  <button id="send_message_to_specialist" class="popup-btn">Отправить</button>
              </div>
          </div>
      </div>
  </div>
</div>
EOF;

// Устанавливаем title
$HTML = html_replace([
    'title' => $title,
    'description' => $description,
    'keywords' => $keywords
], $HTML);


$HTML = html_explode($data, $HTML);

// Подставляем попап карты
$HTML_ = explode('<!--data:popup_map-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:popup_map-->{$data['popup_map']}<!--data:popup_map-->" . $HTML_[2];

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
    // Если передан breadcrumbs_title — используем его, иначе обычный title
    $title = $vars['breadcrumbs_title'] ?? $vars['title'];

    $code = '<span>/</span><span class="curr-page">' . htmlspecialchars($title) . '</span>';

    if ($category) {
        $code = '<span>/</span><a href="/' . htmlspecialchars($vars["slug"]) . '">' . htmlspecialchars($title) . '</a>';

        if (!empty($category['p_id'])) {
            $code .= "<span>/</span><a href='/" . htmlspecialchars($vars['slug']) . "/" . htmlspecialchars($category['p_slug']) . "'>" . htmlspecialchars($category['p_title']) . "</a>";
        }

        $code .= '<span>/</span><span class="curr-page">' . htmlspecialchars($category['title']) . '</span>';
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
            // 2) Студии йоги
            elseif (mb_stripos($h1, 'Студии йоги') === 0) {
                $h1 = preg_replace(
                    '/^Студии йоги/ui',
                    'Студии йоги',
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

            // Частные Студии йоги
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
                'Студии йоги',
                $description
            );
        }
    }

    if ($cityGenitive) {
        $basePhrases = [
            'Студии йоги',
            'Занятия по йоге'
        ];
        $escapedPhrases = array_map(function ($phrase) {
            return preg_replace('/\s+/', '\s+', preg_quote($phrase));
        }, $basePhrases);

        // Убираем ^, чтобы искать фразу в любом месте строки, и оборачиваем всё в общую группу
        $regex = '/((?:Частные\s+)?(?:' . implode('|', $escapedPhrases) . '))/iu';

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
            $active = ($link['id'] ?? null) == ($category['base_id'] ?? $category['id'] ?? null)
                ? ' class="active"'
                : '';
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
 * @return array
 */
function get_links(array $category): array
{
    // Если категория альтернатива — используем original_id, иначе обычный id
    $base_id = (!empty($category['original_id']) && $category['original_id'] != 0)
        ? (int)$category['original_id']
        : (int)$category['id'];

    $array = get_services_by_parrent_id($base_id);

    if (!$array) {
        if (in_array($category['classification'], [1, 2, 3, 5, 6, 7]) && !$category['p_id']) {
            $array = get_services_by_classification($category['classification'], 0);
        } else {
            $array = get_services_by_parrent_id($category['p_id']);
        }
    }

    return (array)$array;
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
                $mobile['first_cat'] = '<li class="find_expert"><a href="/yoga-studii">Все Студии йоги</a></li>';
            } else {
                /*  $mobile['first_cat'] = '<li class="find_lawyer"><a href="/yuristy-i-advokaty">Все юристы и адвокаты</a></li>'; */
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
function vars(
    ?int $classification = null,
    ?string $requestion = null,
    ?array $service_parent = null,
    ?string $cityGenitive = null
): array {
    $vars = [];

    function add_city_to_text(string $text, string $cityGenitive): string
    {
        if (trim($text) === '' || trim($cityGenitive) === '') {
            return $text;
        }

        // Список ключевых профессий/слов
        $specialists = [
            'юристы и адвокаты',
            'юристы',
            'адвокаты',
            'нотариусы',
            'Студии йоги',
            'юридические компании',
            'Студии йоги',
            'специалисты',
            'компании',
            'организации',
        ];

        // === 1. Если строка начинается с профессии ===
        $patternStart = '/^(?:Каталог\s+)?(?:Частные\s+)?(' . implode('|', array_map('preg_quote', $specialists)) . ')\b/iu';
        if (preg_match($patternStart, $text, $m)) {
            $phrase = $m[0];
            return preg_replace(
                $patternStart,
                $phrase . ' ' . $cityGenitive,
                $text,
                1
            );
        }

        // === 2. Если есть "Каталог <профессии>" внутри текста ===
        $patternCatalog = '/Каталог\s+(' . implode('|', array_map('preg_quote', $specialists)) . ')\b/iu';
        if (preg_match($patternCatalog, $text, $m)) {
            $word = $m[1];
            return preg_replace(
                $patternCatalog,
                'Каталог ' . $word . ' ' . $cityGenitive,
                $text,
                1
            );
        }

        // === 3. В остальных случаях — вставляем после первого встреченного ключевого слова ===
        $patternAny = '/\b(' . implode('|', array_map('preg_quote', $specialists)) . ')\b/iu';
        if (preg_match($patternAny, $text, $m)) {
            $word = $m[1];
            return preg_replace(
                $patternAny,
                $word . ' ' . $cityGenitive,
                $text,
                1
            );
        }

        // === 4. Если ничего не найдено — добавляем город в конец ===
        return rtrim($text, " .,") . ' ' . $cityGenitive;
    }

    if ($classification) {
        $vars = match ($classification) {
            /* 1, 2 => [
                'title'       => $service_parent['title'],
                'meta_title'  => add_city_to_text($service_parent['meta_title'], $cityGenitive),
                'slug'        => 'yuristy-i-advokaty',
                'description' => $service_parent['meta_description'],
                'keywords'    => $service_parent['meta_keywords'],
                'h1'          => 'Юристы и адвокаты по направлению ',
                'block'       => 1,
                'pre_list'    => 'Топ ' . mb_strtolower($service_parent['title']),
            ], */
            3 => [
                'title'       => $service_parent['title'],
                'meta_title'  => add_city_to_text($service_parent['meta_title'], $cityGenitive),
                'slug'        => 'yoga-studii',
                'description' => $service_parent['meta_description'],
                'keywords'    => $service_parent['meta_keywords'],
                'h1'          => 'Студии йоги по направлению ',
                'block'       => 2,
                'pre_list'    => 'Топ ' . mb_strtolower($service_parent['title']),
            ],
            /* 4 => [
                'title'       => $service_parent['title'],
                'meta_title'  => add_city_to_text($service_parent['meta_title'], $cityGenitive),
                'slug'        => 'notariusy',
                'description' => $service_parent['meta_description'],
                'keywords'    => $service_parent['meta_keywords'],
                'h1'          => 'Все нотариусы по направлению ',
                'block'       => 3,
                'pre_list'    => 'Топ ' . mb_strtolower($service_parent['title']),
            ] */
        };
    }

    if ($requestion) {
        $vars = match ($requestion) {
            'yoga-studii' => [
                'title'             => $service_parent['title'],
                'meta_title'        => add_city_to_text($service_parent['meta_title'], $cityGenitive),
                'slug'              => 'yoga-studii',
                'description'       => $service_parent['meta_description'],
                'keywords'          => $service_parent['meta_keywords'],
                'h1'                => $service_parent['title'],
                'classification'    => [3, 8],
                'block'             => 2,
                'pre_list'          => 'Топ ' . mb_strtolower($service_parent['title']),
            ],
            'notariusy' => [
                'title'             => add_city_to_text($service_parent['meta_title'], $cityGenitive),
                'breadcrumbs_title'        => $service_parent['title'],
                'slug'              => 'notariusy',
                'description'       => $service_parent['meta_description'],
                'keywords'          => $service_parent['meta_keywords'],
                'h1'                => $service_parent['title'],
                'classification'    => [4],
                'block'             => 3,
                'pre_list'          => 'Топ ' . mb_strtolower($service_parent['title']),
            ],
            default => [
                'title'             => $service_parent['title'],
                'meta_title'        => add_city_to_text($service_parent['meta_title'], $cityGenitive),
                'slug'              => 'yoga-studii',
                'description'       => $service_parent['meta_description'],
                'keywords'          => $service_parent['meta_keywords'],
                'h1'                => $service_parent['title'],
                'classification'    => [3, 8],
                'block'             => 2,
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


/**
 * Генерация JSON-LD для CollectionPage (раздел/подраздел/фильтр)
 */
function generateSpecialistsCollectionPageJsonLd(
    array $vars,
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

    // Собираем h1 (точно как в top_text)
    $h1 = $category
        ? $vars['h1'] . ' «' . $category['title'] . '»'
        : $vars['h1'];

    if ($filter && isset($filter_labels[$filter])) {
        if ($filter === 'private') {
            $h1 = $filter_labels[$filter]
                . ' '
                . mb_strtolower(mb_substr($h1, 0, 1))
                . mb_substr($h1, 1);
        } else { // company
            if (mb_stripos($h1, 'Юристы и адвокаты') === 0) {
                $h1 = preg_replace(
                    '/^Юристы и адвокаты/ui',
                    $filter_labels[$filter],
                    $h1
                );
            } elseif (mb_stripos($h1, 'Студии йоги') === 0) {
                $h1 = preg_replace(
                    '/^Студии йоги/ui',
                    'Студии йоги',
                    $h1
                );
            }
        }
    }

    if ($cityGenitive) {
        $basePhrases = [
            'Студии йоги',
            'Занятий по йоге'
        ];
        $escapedPhrases = array_map(function ($phrase) {
            return preg_replace('/\s+/', '\s+', preg_quote($phrase));
        }, $basePhrases);

        // Убираем ^, чтобы искать фразу в любом месте строки, и оборачиваем всё в общую группу
        $regex = '/((?:Частные\s+)?(?:' . implode('|', $escapedPhrases) . '))/iu';

        if (preg_match($regex, $h1, $m)) {
            $full = mb_strtolower($m[0]);
            $rest = mb_substr($h1, mb_strlen($full));
            $h1   = $full . ' ' . $cityGenitive . $rest;
        } else {
            $h1 = mb_strtolower($h1) . ' ' . $cityGenitive;
        }
    }

    // Собираем URL
    $parentSlug = $service_parent['slug'] ?? $service_parent['base_slug'] ?? $vars['slug'] ?? '';
    $url = 'https://yogoort.ru/' . trim($parentSlug, '/');

    if (!empty($category['slug'])) {
        $url .= '/' . ltrim($category['slug'], '/');
    }

    // JSON-LD
    $jsonLd = [
        "@context" => "https://schema.org",
        "@type"    => "CollectionPage",
        "url"      => $url,
        "name"     => "ЙОГУРТ-каталог: " . $h1
    ];

    return '<script type="application/ld+json">' . PHP_EOL .
        json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
        PHP_EOL . '</script>';
}


/**
 * Генерация JSON-LD для хлебных крошек
 */
function generateCatalogBreadcrumbsJsonLd(array $vars, array $category = null, ?string $filter = ''): string
{
    $items = [];
    $position = 1;

    // 1. Главная
    $items[] = [
        "@type" => "ListItem",
        "position" => $position++,
        "name" => "Главная",
        "item" => "https://yogoort.ru/"
    ];

    // Формируем путь
    $urlPath = '/' . ($vars['slug'] ?? '');
    if (!empty($category['p_slug'])) {
        $urlPath .= '/' . $category['p_slug'];
    }
    if (!empty($category['slug'])) {
        $urlPath .= '/' . $category['slug'];
    }
    if (!empty($filter)) {
        $urlPath .= '/' . $filter;
    }

    $segments = explode('/', trim($urlPath, '/'));

    // Убираем последний сегмент только если их больше 1
    if (empty($category)) {
        if (!empty($filter)) {
            $segments = [$vars['slug']]; // показываем родительскую категорию при фильтре
        } else {
            $segments = []; // ничего кроме "Главная"
        }
    } else {
        if (count($segments) > 0) {
            array_pop($segments); // убираем последний сегмент (категория или фильтр)
        }
    }

    $currentPath = '';
    foreach ($segments as $seg) {
        $currentPath .= '/' . $seg;

        // Определяем читаемое название
        if ($seg === ($category['slug'] ?? '')) {
            $name = $category['title'] ?? ucfirst(str_replace('-', ' ', $seg));
        } elseif ($seg === ($category['p_slug'] ?? '')) {
            $name = $category['p_title'] ?? ucfirst(str_replace('-', ' ', $seg));
        } elseif ($seg === ($vars['slug'] ?? '')) {
            $name = $vars['title'] ?? ucfirst(str_replace('-', ' ', $seg));
        } else {
            $name = ucfirst(str_replace('-', ' ', $seg));
        }

        $items[] = [
            "@type" => "ListItem",
            "position" => $position++,
            "name" => $name,
            "item" => "https://yogoort.ru" . $currentPath
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

function buildH1Title(
    ?array $category,
    array $vars,
    ?string $filter = null,
    ?string $cityGenitive = null
): string {
    $filter_labels = [
        'private' => 'Частные',
        'company' => 'Юридические компании'
    ];

    $h1 = $category
        ? $vars['h1'] . ' «' . $category['title'] . '»'
        : $vars['h1'];

    if ($filter && isset($filter_labels[$filter])) {
        if ($filter === 'private') {
            $h1 = $filter_labels[$filter]
                . ' '
                . mb_strtolower(mb_substr($h1, 0, 1))
                . mb_substr($h1, 1);
        } else {
            if (mb_stripos($h1, 'Юристы и адвокаты') === 0) {
                $h1 = preg_replace('/^Юристы и адвокаты/ui', $filter_labels[$filter], $h1);
            } elseif (mb_stripos($h1, 'Студии йоги') === 0) {
                $h1 = preg_replace('/^Студии йоги/ui', 'Студии йоги', $h1);
            }
        }
    }

    if ($cityGenitive) {
        $basePhrases = ['Студии йоги', 'Занятия по йоге'];
        $escapedPhrases = array_map(function ($phrase) {
            return preg_replace('/\s+/', '\s+', preg_quote($phrase));
        }, $basePhrases);

        // Убираем ^, чтобы искать фразу в любом месте строки, и оборачиваем всё в общую группу
        $regex = '/((?:Частные\s+)?(?:' . implode('|', $escapedPhrases) . '))/iu';
        if (preg_match($regex, $h1, $m)) {
            $full = $m[0];
            $rest = mb_substr($h1, mb_strlen($full));
            $h1 = $full . ' ' . $cityGenitive . $rest;
        } else {
            $h1 .= ' ' . $cityGenitive;
        }
    }

    return $h1;
}

/**
 * Генерация JSON-LD для списка специалистов (ItemList)
 */
function generateSpecialistsItemListJsonLd(
    array $specialists,
    array $specialist_wihout_limit,
    array $vars = [],
    array $category = [],
    array $classification = [],
    array $service_parent = [],
    ?string $filter = null,
    ?string $cityGenitive = null,
    string $categoryDescription = '',
    int $startPosition = 1
): string {
    $filter_labels = [
        'private' => 'Частные',
        'company' => 'Юридические компании'
    ];

    // Заголовок категории (аналогично top_text / CollectionPage)
    $categoryTitle = $category
        ? $vars['h1'] . ' «' . $category['title'] . '»'
        : $vars['h1'];

    if ($filter && isset($filter_labels[$filter])) {
        if ($filter === 'private') {
            $categoryTitle = $filter_labels[$filter]
                . ' '
                . mb_strtolower(mb_substr($categoryTitle, 0, 1))
                . mb_substr($categoryTitle, 1);
        } else {
            if (mb_stripos($categoryTitle, 'Юристы и адвокаты') === 0) {
                $categoryTitle = preg_replace(
                    '/^Юристы и адвокаты/ui',
                    $filter_labels[$filter],
                    $categoryTitle
                );
            } elseif (mb_stripos($categoryTitle, 'Студии йоги') === 0) {
                $categoryTitle = preg_replace(
                    '/^Студии йоги/ui',
                    'Студии йоги',
                    $categoryTitle
                );
            }
        }
    }

    if ($cityGenitive) {
        $basePhrases = [
            'Студии йоги',
            'Занятия по йоге'
        ];
        $escapedPhrases = array_map(function ($phrase) {
            return preg_replace('/\s+/', '\s+', preg_quote($phrase));
        }, $basePhrases);

        // Убираем ^, чтобы искать фразу в любом месте строки, и оборачиваем всё в общую группу
        $regex = '/((?:Частные\s+)?(?:' . implode('|', $escapedPhrases) . '))/iu';
        if (preg_match($regex, $categoryTitle, $m)) {
            $full = $m[0];
            $rest = mb_substr($categoryTitle, mb_strlen($full));
            $categoryTitle = $full . ' ' . $cityGenitive . $rest;
        } else {
            $categoryTitle .= ' ' . $cityGenitive;
        }
    }

    // Формируем описание (как в top_text)
    $description = htmlspecialchars_decode(
        $categoryDescription
            ?: $category['description']
            ?? $service_parent['description']
            ?? ''
    );

    if ($filter && isset($filter_labels[$filter])) {
        if ($filter === 'private') {
            $description = preg_replace('/\bюристов\b/ui', 'частных юристов', $description);
            $description = preg_replace('/\bэкспертов\b/ui', 'частных экспертов', $description, 1);
        } else {
            $description = preg_replace('/\b(лучших\s+)?юристов\s+и\s+адвокатов\b/ui', 'юридические компании', $description);
            $description = preg_replace('/\bэкспертов\b/ui', 'Студии йоги', $description);
        }
    }

    // Элементы списка специалистов
    $all_equipments = get_all_equipment();

    // Элементы списка специалистов
    $itemListElements = [];
    foreach ($specialists as $i => $spec) {
        $knowsAbout = '';
        if (!empty($spec['specialization'])) {
            if (!empty($spec['specialization_count']) && $spec['specialization_count'] > 1) {
                $knowsAbout = mb_strimwidth($spec['specialization'], 0, 21, '...', 'UTF-8');
                $specializationText = num_decline($spec['specialization_count'] - 1, 'специализация, специализации, специализаций');
                $knowsAbout .= " и ещё {$specializationText}";
            } else {
                $knowsAbout = mb_strimwidth($spec['specialization'], 0, 42, '...', 'UTF-8');
            }
        }

        // --- Галерея ---
        $gallery_photos = json_decode($spec['gallery'] ?? '[]', true) ?: [];
        $imageUrls = [];
        if (!empty($spec['photo'])) {
            $imageUrls[] = full_url('/uploads/avatars/' . $spec['photo']);
        }
        foreach ($gallery_photos as $photo) {
            $imageUrls[] = full_url('/uploads/gallery/' . $photo);
        }

        // --- Рейтинг ---
        $ratingClamped = min(max((float)($spec['rating'] ?? 0), 0), 5);
        $ratingValue = number_format($ratingClamped, 1, '.', '');

        // --- Цена (минимальная) ---
        $services = get_specialist_service($spec['id']);
        $minPrice = null;
        if ($services && is_array($services)) {
            foreach ($services as $svc) {
                if (preg_match('/\d+/', trim($svc['price'] ?? ''), $matches)) {
                    $priceNum = (int)$matches[0];
                    if ($minPrice === null || $priceNum < $minPrice) $minPrice = $priceNum;
                }
            }
        }

        // --- Удобства и оборудование ---
        $selected_equipment = get_specialist_equipment($spec['id']);
        $amenityFeature = [];
        foreach ($all_equipments as $item) {
            if (array_key_exists($item['id'], $selected_equipment)) {
                $category = trim($item['category']);
                if (in_array($category, ['equipment', 'equipments', 'amenity', 'amenities'])) {
                    $amenityFeature[] = [
                        "@type" => "LocationFeatureSpecification",
                        "name" => trim($item['name']),
                        "value" => "True"
                    ];
                }
            }
        }
        usort($amenityFeature, fn($a, $b) => mb_strtolower($a['name']) <=> mb_strtolower($b['name']));

        $itemListElements[] = [
            "@type" => "ListItem",
            "position" => $startPosition + $i,
            "item" => array_filter([
                "@type" => "SportsActivityLocation",
                "url" => full_url('studio/' . ($spec['slug'] ?? '')),
                "image" => !empty($imageUrls) ? (count($imageUrls) === 1 ? $imageUrls[0] : $imageUrls) : null,
                "name" => !empty($spec['company_name'])
                    ? $spec['company_name']
                    : trim(($spec['name'] ?? '') . ' ' . ($spec['lastname'] ?? '')),
                "additionalType" => $spec['classification_title'] ?? '',
                "address" => [
                    "@type" => "PostalAddress",
                    "streetAddress" => $spec['metro_name'] ?? '',
                    "addressLocality" => $spec['city_name'] ?? ''
                ],
                "aggregateRating" => [
                    "@type" => "AggregateRating",
                    "ratingValue" => $ratingValue
                ],
                "priceRange" => $minPrice !== null ? 'от ' . number_format($minPrice, 0, '.', "'") . ' ₽' : null,
                "amenityFeature" => !empty($amenityFeature) ? $amenityFeature : null
            ], fn($v) => !is_null($v) && $v !== '')
        ];
    }

    // Фикс для альтернативных slug: используем текущий slug родителя, а не base_slug
    $parentSlug = $service_parent['slug'] ?? $service_parent['base_slug'] ?? $vars['slug'] ?? '';
    $parentSlug = trim($parentSlug, '/');

    if (!empty($category['slug'])) {
        $parentSlug .= '/' . ltrim($category['slug'], '/');
    }

    $jsonLd = [
        "@context" => "https://schema.org",
        "@type" => "ItemList",
        "name" => $categoryTitle ?: "Специалисты",
        "description" => strip_tags($description),
        "url" => "https://yogoort.ru/" . $parentSlug,   // <-- ключевой фикс
        "numberOfItems" => count($specialist_wihout_limit),
        "itemListOrder" => "Descending",
        "itemListElement" => $itemListElements
    ];

    return '<script type="application/ld+json">' . PHP_EOL .
        json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
        PHP_EOL . '</script>';
}

if (!empty($category) && !empty($category['slug'])) {
    $categorySlug = $category['slug'];
} elseif (!empty($service_parent) && !empty($service_parent['slug'])) {
    // Если категории нет — берём родителя
    $categorySlug = $service_parent['slug'];
} else {
    $categorySlug = ''; // на всякий случай
}

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        let limit = 24;
        let start = <?= $limit ?>; // из PHP
        let loading = false;
        let hadError = false; // флаг «ошибка уже была»
        let categorySlug = '<?= addslashes($categorySlug) ?>';
        let metro_ids = <?= json_encode($_SESSION['metro'] ?? []) ?>;
        let filter = <?= $filter !== null ? "'" . addslashes($filter) . "'" : 'null' ?>;
        let filter_type_url = <?= $filter_type_url !== null ? "'" . addslashes($filter_type_url) . "'" : 'null' ?>;

        let selected_amenities = <?= json_encode($selected_amenities ?? []) ?>;
        let selected_equipment = <?= json_encode($selected_equipment ?? []) ?>;
        let selected_price = <?= json_encode($selected_price ?? []) ?>;

        let hasMore = true; // флаг, что есть ещё специалисты

        function checkAndLoad() {
            if (loading || hadError || !hasMore) return;

            const triggerEl = document.getElementById('load-trigger');
            if (!triggerEl) return;

            const rect = triggerEl.getBoundingClientRect();
            const triggerTop = rect.top;
            const viewportHeight = window.innerHeight;

            if (triggerTop <= viewportHeight + 3200 && triggerTop >= viewportHeight - 800) {
                const lazyBgs = document.querySelectorAll('.lazy-bg');
                if ('IntersectionObserver' in window) {
                    const observer = new IntersectionObserver((entries, obs) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const el = entry.target;
                                const bg = el.dataset.bg;
                                if (bg) {
                                    el.style.backgroundImage = `url('${bg}')`;
                                    obs.unobserve(el);
                                }
                            }
                        });
                    }, {
                        rootMargin: "0px 0px 200px 0px"
                    });
                    lazyBgs.forEach(el => observer.observe(el));
                } else {
                    lazyBgs.forEach(el => {
                        const bg = el.dataset.bg;
                        if (bg) el.style.backgroundImage = `url('${bg}')`;
                    });
                }

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
                        metro_ids: metro_ids,
                        selected_amenities: selected_amenities,
                        selected_equipment: selected_equipment,
                        selected_price: selected_price
                    },
                    dataType: 'json',
                    success: function(response) {
                        // создаём временный контейнер
                        let temp = document.createElement('div');
                        if (response.html_desktop) temp.innerHTML = response.html_desktop;
                        const hasDesktopItems = temp.querySelectorAll('.item').length > 0;

                        temp.innerHTML = ''; // очистим
                        if (response.html_mobile) temp.innerHTML = response.html_mobile;
                        const hasMobileItems = temp.querySelectorAll('.item').length > 0;

                        if (hasDesktopItems || hasMobileItems) {
                            if (hasDesktopItems) $('.comp-catalog').last().after(response.html_desktop);
                            if (hasMobileItems) $('.mobile-catalog').last().after(response.html_mobile);
                            start += limit;
                            loading = false;
                        } else {
                            hasMore = false;
                            $(window).off('scroll', onScrollHandler);
                        }

                        initSpecSliders();
                    },
                    error: function() {
                        hadError = true;
                        $(window).off('scroll', onScrollHandler);
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
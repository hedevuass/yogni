<?php
//страница результатов поиска

$data = [];

if (isset($_GET['text'])) {
    $requestion = htmlentities(strip_tags($_GET['text']));
} else {
    responseRedirect('/');
}
// Данные пагинации
$start = 0;
$limit = 16;
$page = intval($_GET['page']) ?: 1;
if ($page) $start = ($page - 1) * $limit;

//получаем результаты
$companies = full_search_companies($requestion, $start, $limit);
$specialists = full_search_specialists($requestion, $start, $limit);

//кол-во результатов
$total_companies = count_full_search_companies($requestion);
$total_specialists = count_full_search_specialists($requestion);
$count = $total_companies + $total_specialists;

if (!empty($specialists) || !empty($companies)) {
    // Заголовок
    $data['top_text'] = <<<EOF
    <div class="top-text">
        <h1 class="desctop">Найдено на юрфак.ру</h1>
        <p class="favorite-page">Результаты поиска по запросу “{$requestion}”</p>
    </div>
    EOF;
    $title = "Результаты поиска по запросу “{$requestion}” — Юрфак.ру";
    $classification = $specialists[0]['classification_id'] ?? $companies[0]['classification_id'];

    $vars = vars($classification);

    $pre_list_text = 'Топ результаты';

    // Промоблок
    /* $data['promo'] = block_promo($vars['block'], $classification); */

    // Выводим список специалистов
    $data['catalog_specialists'] = block($specialists);

    // Выводим список компаний
    $data['catalog_companies'] = block($companies);
} else {
    $data['top_text'] = <<<EOF
    <div class="top-text">
        <h1 class="desctop">Ничего не найдено.</h1>
        <p class="favorite-page">Результатов поиска по запросу “{$requestion}” не найдено на юрфак.ру</p>
    </div>
    EOF;
    $title = "Ничего не найдено — Юрфак.ру";
}

    if (isset($_GET['filter'])) {
        $filter_req = htmlentities(strip_tags($_GET['filter']));
    }

    //фильтр
    $lawyers_class = $experts_class = $notaries_class = '';
    if (isset($filter_req)) {
        if ($filter_req == 'lawyers') {
            $lawyers_class = 'active';
        } else if ($filter_req == 'experts') {
            $experts_class = 'active';
        } else if ($filter_req == 'notaries') {
            $notaries_class = 'active';
        }
        // Пагинация с фильтрами
        $data['pagination'] = block_pagination_search($count, $limit, $page, $requestion, $filter_req);
    } else {
        /* $lawyers_class = 'active'; */
        // Пагинация
        $data['pagination'] = block_pagination_search($count, $limit, $page, $requestion);
    }
//выводим фильтр
/* $data['search_filter'] = '<div class="search-filter">
    <a href="/poisk?zapros=' . $requestion . '&filter=lawyers" class="checkbox ' . $lawyers_class . '">Юристы</a>
    <a href="/poisk?zapros=' . $requestion . '&filter=experts" class="checkbox ' . $experts_class . '">Эксперты</a>
    <a href="/poisk?zapros=' . $requestion . '&filter=notaries" class="checkbox ' . $notaries_class . '">Нотариусы</a>
    </div>'; */

// Устанавливаем title
$HTML = html_replace([
    'title' => $title
], $HTML);


$HTML = html_explode($data, $HTML);
$HTML = modules_include($HTML);


/**
 * @param int $classification
 * @param string $requestion
 * @return array
 */
function vars(int $classification = null, string $requestion = null): array
{
    if ($classification) {
        $vars = match ($classification) {
            1, 2, 5, 6 => [
                'title'     => 'Юристы и адвокаты',
                'slug'      => 'yuristy-i-advokaty',
                'h1'        => 'Все юристы и адвокаты по направлению ',
                'block'     => 1
            ],
            3, 7 => [
                'title'     => 'Эксперты',
                'slug'      => 'experty',
                'h1'        => 'Все эксперты по направлению ',
                'block'     => 2
            ],
            4, 8 => [
                'title'     => 'Нотариусы',
                'slug'      => 'notariusy',
                'h1'        => '',
                'block'     => 3
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

    return block($specialists, 'pro');
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

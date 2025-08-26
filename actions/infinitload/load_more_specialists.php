<?php
// load_more_specialists.php
header('Content-Type: application/json');

include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

$limit = isset($_POST['limit']) ? (int) $_POST['limit'] : 24;
$start = isset($_POST['start']) ? (int) $_POST['start'] : 0;
$slug  = $_POST['slug']  ?? '';
$filter = $_POST['filter_type_url'] ?? '';
$metro_ids = $_POST['metro_ids'] ?? [];
if (!is_array($metro_ids)) {
    $metro_ids = $metro_ids !== '' ? explode(',', $metro_ids) : [];
    $metro_ids = array_map('intval', $metro_ids);
}


// Нам нужно понять: это «категория» ($category) или «главная/фильтр по классификации».
$service_parent = get_service_parent_catalog($slug);
$category       = get_service_by_slug($slug);

$html = '';

// Если категория найдена (например, в разделе «юристы» → «консультации» и т.п.)
if ($category) {
    // Определяем ID → services
    if (! get_services_by_parrent_id($category['id'])) {
        $services = $category['id'];
    } else {
        $services = $category['id'];
    }

    // Достаём специалистов именно для категории → с тарифами
    $specialists_with_tarrifs = get_specialists_by_service_with_tariffs($category['classification_array'], $services, $limit, $start, $filter);

    if ($specialists_with_tarrifs) {
        // block() возвращает HTML разметку «штук по 24»
        $html = block($specialists_with_tarrifs);
    }
} else {
    // Если категории нет → значит это «главный» раздел (юристы/эксперты/нотариусы и т.п.)
    $vars = vars(requestion: $slug, service_parent: $service_parent);

    $classification = $vars['classification'];

    // Достаём специалистов по классификации
    $specialists_with_tarrifs = get_specialists_by_classification_with_tariffs($classification, $limit, $start, $filter, $metro_ids);

    if ($specialists_with_tarrifs) {
        $html = block($specialists_with_tarrifs);
    }
}

/**
 * @param int $classification
 * @param string $requestion
 * @return array
 */
function vars(int $classification = null, string $requestion = null, array $service_parent = null): array
{
    $vars = [];

    if ($classification) {
        $vars = match ($classification) {
            1, 2 => [
                'title'       => 'Юристы и адвокаты',
                'slug'        => 'yuristy-i-advokaty',
                'description' => 'Найдите лучших юристов и адвокатов по всем направлениям. Мы поможем вам решить любые юридические вопросы.',
                'keywords'    => 'юристы, адвокаты, юридическая помощь, консультации',
                'h1'          => 'Юристы и адвокаты по направлению ',
                'block'       => 1,
                'pre_list'    => 'Топ юристы и адвокаты'
            ],
            3 => [
                'title'       => 'Эксперты',
                'slug'        => 'experty',
                'description' => 'Каталог экспертов для профессиональных консультаций и помощи. Найдите подходящего эксперта прямо сейчас.',
                'keywords'    => 'эксперты, профессиональные консультации, помощь специалистов',
                'h1'          => 'Эксперты по направлению ',
                'block'       => 2,
                'pre_list'    => 'Топ эксперты'
            ],
            4 => [
                'title'       => 'Нотариусы',
                'slug'        => 'notariusy',
                'description' => 'Список нотариусов для заверения документов и юридических услуг. Найдите надежного нотариуса.',
                'keywords'    => 'нотариусы, заверение документов, юридические услуги',
                'h1'          => 'Все нотариусы по направлению ',
                'block'       => 3,
                'pre_list'    => 'Топ нотариусы'
            ]
        };
    }

    if ($requestion) {
        $vars = match ($requestion) {
            'experty' => [
                'title'             => 'Эксперты',
                'slug'              => 'experty',
                'description'       => 'Каталог экспертов из различных областей. Мы собрали лучших специалистов для ваших нужд.',
                'keywords'          => 'эксперты, профессионалы, услуги экспертов',
                'h1'                => $service_parent['title'],
                'classification'    => [3, 8],
                'block'             => 2,
                'pre_list'          => 'Топ эксперты'
            ],
            'notariusy' => [
                'title'             => 'Нотариусы',
                'slug'              => 'notariusy',
                'description'       => 'Лучшие нотариусы для заверения документов и юридических услуг. Ознакомьтесь с нашим каталогом.',
                'keywords'          => 'нотариусы, услуги нотариуса, заверение документов',
                'h1'                => $service_parent['title'],
                'classification'    => [4],
                'block'             => 3,
                'pre_list'          => 'Топ нотариусы'
            ],
            default => [
                'title'             => 'Юристы и адвокаты',
                'slug'              => 'yuristy-i-advokaty',
                'description'       => 'Лучшие юристы и адвокаты для ваших юридических вопросов. Найдите подходящего специалиста прямо сейчас.',
                'keywords'          => 'юристы, адвокаты, консультации, юридическая помощь',
                'h1'                => $service_parent['title'],
                'classification'    => [1, 2, 5, 6, 7],
                'block'             => 1,
                'pre_list'          => 'Топ юристы и адвокаты'
            ]
        };
    }

    return $vars;
}

echo json_encode(['html' => $html]);
exit;
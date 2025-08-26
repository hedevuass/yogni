<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

//Для promo
$all_specializations = get_services_with_prefix();
$promo_blocks = get_promo_blocks_with_prefix();
$merged_services = array_merge($promo_blocks, $all_specializations);

// Сортируем по типу и алфавиту
usort($merged_services, function ($a, $b) {
    if ($a['type'] === $b['type']) {
        return strcmp($a['title'], $b['title']);
    }
    return ($a['type'] === 'promo') ? -1 : 1;
});

// Категории
$categories = [
    'Юристы и адвокаты' => [1, 2, 5, 6, 7],
    'Эксперты' => [3, 8],
    'Нотариусы' => [4],
];

// Группируем по классификациям
$sorted_services = [];
foreach ($merged_services as $service) {
    if (!isset($service['classification'])) {
        continue;
    }
    foreach ($categories as $catName => $catIds) {
        if (in_array($service['classification'], $catIds)) {
            $sorted_services[$catName][] = $service;
            break;
        }
    }
}

$script = <<<JS
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.toggle-children').forEach(button => {
                button.addEventListener('click', () => {
                    const target = document.querySelector(button.dataset.target);
                    if (target) {
                        target.classList.toggle('d-none');
                        button.textContent = target.classList.contains('d-none') ? '+' : '-';
                    }
                });
            });
        });
    </script>
    JS;

$specialization_html = '';
// Здесь убираем проверку $price_id, так как редактирование метаданных всегда доступно
foreach ($categories as $categoryName => $catIds) {
    if (!isset($sorted_services[$categoryName])) {
        continue; // Если в категории нет данных, пропускаем
    }
    $specialization_html .= "<h3>{$categoryName}</h3>";
    $specialization_html .= renderSpecializationsWithMetaJournal($sorted_services[$categoryName]);
}
$specialization_html .= $script;

// Если параметр 'price_id' больше не используется, можно его удалить из передаваемых данных:
$json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/journal_menu.php', [
    'specialization_html' => $specialization_html
]);

responseJson($json);

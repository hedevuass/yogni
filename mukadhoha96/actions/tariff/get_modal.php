<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = ($_POST['id']);
    $type = ($_POST['type']);
    $price_id = isset($_POST['price_id']) ? $_POST['price_id'] : null;

    $function = 'get_' . $type;

    $$type = $function((int) $id);

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
        /* 'Юристы и адвокаты' => [1, 2, 5, 6, 7], */
        'Студии йоги' => [3, 8],
        /* 'Нотариусы' => [4], */
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

    foreach ($sorted_services as &$group) {
        usort($group, function ($a, $b) {
            $aIsMisc = (stripos($a['title'], 'прочие') !== false) ? 1 : 0;
            $bIsMisc = (stripos($b['title'], 'прочие') !== false) ? 1 : 0;
    
            // Сначала элементы, у которых в названии нет "прочие" (0), потом с "прочие" (1)
            if ($aIsMisc !== $bIsMisc) {
                return $aIsMisc - $bIsMisc;
            }
            // Если одинаково, сортируем по названию
            return strcmp($a['title'], $b['title']);
        });
    }
    unset($group);
    

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
    if ($price_id) {

        // <<< Добавляем promo-блоки сюда >>>
        if (!empty($promo_blocks)) {
            $specialization_html .= "<h3>Промо-блоки</h3>";
            $specialization_html .= renderSpecializationsWithPrice($promo_blocks, $price_id);
        }
        
        foreach ($categories as $categoryName => $catIds) {
            if (!isset($sorted_services[$categoryName])) {
                continue; // Если в категории нет данных, пропускаем
            }

            $specialization_html .= "<h3>{$categoryName}</h3>";
            $specialization_html .= renderSpecializationsWithPrice($sorted_services[$categoryName], $price_id);
        }

        $specialization_html .= $script;
    }


    $json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/' . $type . '.php', [
        $type => $$type,
        'type' => $type,
        'price_id' => $price_id,
        'specialization_html' => $specialization_html
    ]);

    responseJson($json);
}
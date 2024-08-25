<?php

$data = [];

// Данные пагинации
$start = 0;
$limit = 16;
$page = intval($_GET['page']) ?: 1;
if ($page) $start = ($page - 1) * $limit;

//специализация
$specialization = 'lawyers';
if (isset($_GET['specialization'])) {
	if ($_GET['specialization'] == 'experts' || $_GET['specialization'] == 'notaries') {
		$specialization = $_GET['specialization'];
	}
}
$data['favorite_items'] = '';

// Пагинация
$data['pagination'] = '';

if (!isset($_GET['page'])) {
    // скрипт js, отправляющий запрос на получение избранных 
    $data['script'] = '
    <script>
        window.addEventListener("DOMContentLoaded", (event) => {
            let favorite = { lawyers: [], experts: [], notaries: [] };
            if (localStorage.getItem("favorite")) {
                favorite = localStorage.getItem("favorite");
            }
            $(".index-catalog-block .grid_flex").addClass("preload");
            $.ajax({
                type: "POST",
                url: "/actions/set_favorite.php",
                dataType: "json",
                data: {favorite: favorite, specialization: "'.$_GET['specialization'].'"},
                success: function (json) {
                    if (json) {
                        $(".index-catalog-block .grid_flex").append(json.specialists)
                        if (json.pagination) {
                            $(".index-catalog-block .grid_flex").after(json.pagination)
                        }
                        $(".index-catalog-block .grid_flex").removeClass("preload");
                    }
                }
            })
        });
    </script>';

} else {
    $data['script'] = '';

    // определяем специализацию (для фильтров)
    if ($specialization == 'lawyers') {
        $ids = $_SESSION['favorite']->lawyers;
    } else if ($specialization == 'experts') {
        $ids = $_SESSION['favorite']->experts;
    }
    if ($specialization == 'notaries') {
        $ids = $_SESSION['favorite']->notaries;
    }
    if(empty($ids)) {
        $data['favorite_items'] = "Не найдено избранных специалистов/компаний.";
        $count = 0;
    } else {
        // получаем избранных специалистов
        $favorites = get_specialists_by_ids($ids, $limit, $start);
        $count = count_specialists_by_ids($ids); 
        $data['favorite_items'] = block($favorites);
    }

    // Пагинация
    $data['pagination'] = block_pagination_fav($count, $limit, $page, $specialization);
}

//фильтр
$lawyers_class=$experts_class=$notaries_class='';
if ($specialization == 'lawyers') {
    $lawyers_class = 'active';
} else if ($specialization == 'experts') {
    $experts_class = 'active';
} else if ($specialization == 'notaries') {
    $notaries_class = 'active';
}

$data['favorite_filter'] = '<div class="favorite-filter">
<a href="/izbrannoe?specialization=lawyers" class="checkbox '.$lawyers_class.'">Юристы</a>
<a href="/izbrannoe?specialization=experts" class="checkbox '.$experts_class.'">Эксперты</a>
<a href="/izbrannoe?specialization=notaries" class="checkbox '.$notaries_class.'">Нотариусы</a>
</div>';


// Устанавливаем title
$HTML = html_replace([
    'title' => 'Избранное - ЮРФАК'
], $HTML);


$HTML = html_explode($data, $HTML);
<?php

$data = [];

// Данные пагинации
$start = 0;
$limit = 16;
$page = intval($_GET['page']) ?: 1;
if ($page) $start = ($page - 1) * $limit;

//специализация
/* $specialization = 'lawyers';
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
} */

$specialization = ['lawyers', 'experts', 'notaries'];

$data['favorite_items'] = '';
$data['pagination'] = '';

if (!isset($_GET['page'])) {
    $data['script'] = '
<script>
window.addEventListener("DOMContentLoaded", (event) => {
    // Сначала проверяем, есть ли page > 1 в URL
    const url = new URL(window.location.href);
    if (url.searchParams.get("page") && url.searchParams.get("page") !== "1") {
        url.searchParams.set("page", "1");
        window.location.replace(url.toString());
        return; // останавливаем дальнейшее выполнение скрипта
    }

    // Рабочий AJAX для подгрузки favorite
    let favorite = { lawyers: [], experts: [], notaries: [] };
    if (localStorage.getItem("favorite")) {
        favorite = JSON.parse(localStorage.getItem("favorite"));
    }

    $(".index-catalog-block .grid_flex").addClass("preload");
    $.ajax({
        type: "POST",
        url: "/actions/set_favorite.php",
        dataType: "json",
        data: {favorite: favorite, specialization: ["lawyers", "experts", "notaries"]},
        success: function (json) {
            if (json && json.specialists) {
                $(".index-catalog-block .grid_flex").append(json.specialists);
                if (json.pagination) {
                    $(".index-catalog-block .grid_flex").after(json.pagination);
                }
            }
            $(".index-catalog-block .grid_flex").removeClass("preload");

            // оставляем редирект на page=1, чтобы на первой загрузке URL стал корректным
            window.location.href = window.location.pathname + "?specialization=lawyers,experts,notaries&page=1";
        },
        error: function () {
            $(".index-catalog-block .grid_flex").removeClass("preload");
        }
    });
});
</script>';
} else {
    $data['script'] = '';

    // собираем все id из сессии для нужных специализаций
    $ids_all = [];
    if (isset($_SESSION['favorite']['all']) && is_array($_SESSION['favorite']['all'])) {
        $ids_all = $_SESSION['favorite']['all']; // порядок сохраняется
    }

    // приводим к int, убираем дубли и сохраняем порядок
    $all_ids = array_values(array_unique(array_map('intval', $ids_all)));

    if (empty($all_ids)) {
        $data['favorite_items'] = "Не найдено избранных специалистов/компаний.";
        $count = 0;
    } else {
        // общее количество для пагинации
        $count = count($all_ids);

        // старт позиции
        $start = ($page - 1) * $limit;
        // защищаем от выхода за границы
        if ($start < 0) $start = 0;
        if ($start >= $count) {
            $data['favorite_items'] = "Не найдено избранных специалистов/компаний.";
            $data['pagination'] = block_pagination_fav($count, $limit, $page, implode(',', $specialization));
        } else {
            // собираем id для текущей страницы, но с учётом того, что некоторые id в БД могут отсутствовать
            $favorites = [];
            $currentPos = $start;

            // пока не набрали limit и не кончились id
            while (count($favorites) < $limit && $currentPos < $count) {
                // берём батч id — размер батча = оставшееся место (чтобы не запрашивать лишнее)
                $need = $limit - count($favorites);
                $batch = array_slice($all_ids, $currentPos, $need);

                // запрос к БД для этого батча
                $found = get_specialists_by_ids($batch); // ожидаем массив специалистов с ключом 'id' внутри

                if ($found) {
                    // индексируем найденных по id, чтобы восстановить исходный порядок batch'а
                    $foundById = [];
                    foreach ($found as $s) {
                        // убедимся, что id есть и приводим к int
                        $foundById[intval($s['id'])] = $s;
                    }

                    // добавляем в $favorites тех, кто есть в $foundById, строго в порядке $batch
                    foreach ($batch as $id) {
                        $id = intval($id);
                        if (isset($foundById[$id])) {
                            $favorites[] = $foundById[$id];
                            if (count($favorites) >= $limit) break;
                        }
                    }
                }

                // двигаем указатель на обработанные id (даже если ничего не найдено — пропускаем эти id)
                $currentPos += count($batch);

                // safety: если get_specialists_by_ids вернул null/false и batch пуст (маловероятно), прерываем
                if (empty($batch)) break;
            }

            // если всё же не нашлось ничего — показываем сообщение
            $data['favorite_items'] = !empty($favorites) ? block($favorites) : "Не найдено избранных специалистов/компаний.";
        }
    }

    // пагинация: передаём строку специализаций как раньше
    $data['pagination'] = block_pagination_fav($count, $limit, $page, implode(',', $specialization));
}
$data['favorite_filter'] = '';

//фильтр
$lawyers_class = $experts_class = $notaries_class = '';
if ($specialization == 'lawyers') {
    $lawyers_class = 'active';
} else if ($specialization == 'experts') {
    $experts_class = 'active';
} else if ($specialization == 'notaries') {
    $notaries_class = 'active';
}

/* $data['favorite_filter'] = '<div class="favorite-filter">
<a href="/izbrannoe?specialization=lawyers" class="checkbox '.$lawyers_class.'">Юристы</a>
<a href="/izbrannoe?specialization=experts" class="checkbox '.$experts_class.'">Эксперты</a>
<a href="/izbrannoe?specialization=notaries" class="checkbox '.$notaries_class.'">Нотариусы</a>
</div>'; */


// Устанавливаем title
$HTML = html_replace([
    'title' => 'Избранное - ЮРФАК'
], $HTML);


$HTML = html_explode($data, $HTML);

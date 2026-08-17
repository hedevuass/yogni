<?php
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/functions.php';

update_last_modified();

session_start();
setcookie('fav_updated', '1', 0, '/', '', false, false);

function normalizeFavorite($fav) {
    $out = [
        'lawyers'  => [],
        'experts'  => [],
        'notaries' => [],
        'all'      => []
    ];

    if (!is_array($fav)) {
        return $out;
    }

    foreach ($out as $k => $v) {
        if (isset($fav[$k]) && is_array($fav[$k])) {
            $out[$k] = array_values(array_unique(array_map('intval', $fav[$k])));
        }
    }

    return $out;
}

if (isset($_POST['favorite'])) {
    $_SESSION['favorite'] = normalizeFavorite($_POST['favorite']);
}

if (!empty($_POST['company_page'])) {
    responseJson(true);
}

if (!empty($_POST['specialization'])) {

    $specializations = $_POST['specialization'];
    if (!is_array($specializations)) {
        $specializations = [$specializations];
    }

    $ids = $_SESSION['favorite']['all'] ?? [];
    $ids = array_reverse($ids);
    $count = count($ids);

    if ($count === 0) {
        responseJson([
            'specialists' => 'Не найдено избранных студий.',
            'page' => 2
        ]);
    }

    $specialists = get_specialists_by_ids($ids);
    $found = [];

    foreach ($specialists as $s) {
        $found[(int)$s['id']] = $s;
    }

    $ordered = [];
    foreach ($ids as $id) {
        $id = (int)$id;
        if (isset($found[$id])) {
            $ordered[] = $found[$id];
            if (count($ordered) >= 16) break;
        }
    }

    $json = [
        'specialists' => block($ordered),
        'page' => 2
    ];

    if ($count > 16) {
        $json['pagination'] = block_pagination_fav(
            $count,
            16,
            1,
            implode(',', $specializations)
        );
    }

    responseJson($json);
}

responseJson(false);
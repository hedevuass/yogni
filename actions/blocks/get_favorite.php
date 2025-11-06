<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

if (isset($_GET['page'])) {
    // собираем данные из GET, но не "чистим" favorite JSON
    foreach ($_GET as $key => $value) {
        if ($key === 'favorite') {
            $data[$key] = $value;
        } else {
            $data[$key] = clean_elem_form($value);
        }
    }

    // defaults
    $data['limit'] = !empty($data['limit']) ? intval($data['limit']) : 16;
    $data['page']  = !empty($data['page']) ? intval($data['page']) : 1;

    $start = ($data['page'] - 1) * $data['limit'];

    // декодирование
    $favorite = isset($data['favorite']) ? json_decode($data['favorite'], true) : [];
    if (!is_array($favorite)) $favorite = [];

    $spec_input = $data['specialization'] ?? 'lawyers';

    // если пришла строка с запятыми — разобрать в массив
    if (is_string($spec_input) && strpos($spec_input, ',') !== false) {
        $specs = array_map('trim', explode(',', $spec_input));
    } elseif (is_array($spec_input)) {
        $specs = $spec_input;
    } else {
        $specs = [$spec_input];
    }

    if (isset($favorite['all']) && is_array($favorite['all'])) {
        $all_ids = array_map('intval', $favorite['all']);
    } else {
        // fallback: собрать из переданных спецов
        $all_ids = [];
        foreach ($specs as $s) {
            if (!isset($favorite[$s]) || !is_array($favorite[$s])) continue;
            foreach ($favorite[$s] as $id) {
                $all_ids[] = intval($id);
            }
        }
    }

    // Убираем дубли, сохраняя порядок
    $all_ids = array_map('intval', $all_ids);
    $all_ids = array_unique($all_ids);
    $all_ids = array_values($all_ids);
    $all_ids = array_reverse($all_ids);
    
    $total_count = count($all_ids);

    // Получаем нужную "страницу" id
    $ids = array_slice($all_ids, $start, $data['limit']);

    if (empty($ids)) {
        $json['specialists'] = false;
        $json['pagination'] = false;
        $json['page'] = $data['page'];
        responseJson($json);
    }

    // Передаём ОДНОМЕРНЫЙ массив id в функцию, она вернёт ровно эти записи
    $specialists = get_specialists_by_ids($ids);

    $json['specialists'] = $specialists ? block($specialists) : false;

    // пагинация (считаем по общему количеству id в выбранных специализациях)
    if ($total_count > $start + count($ids)) {
        $json['pagination'] = block_pagination_fav($total_count, $data['limit'], $data['page'], implode(',', $specs));
    } else {
        $json['pagination'] = false;
    }

    $json['page'] = $data['page'];
    responseJson($json);
}

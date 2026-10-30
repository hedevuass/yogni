<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';

$ids_raw = $_POST['ids'] ?? '';
if (empty($ids_raw)) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$ids = array_map('intval', explode(',', $ids_raw));
$specialists = get_specialists_coordinates_by_ids($ids);

$points = [];
foreach ($specialists as $s) {
    $map_adress = [];
    if (!empty($s['city']))   $map_adress[] = 'г. ' . $s['city'];
    if (!empty($s['street'])) $map_adress[] = $s['street'];
    if (!empty($s['house']))  $map_adress[] = $s['house'];
    $map_adress = implode(', ', $map_adress);

    list($lng, $lat) = explode(' ', $s['coordinates']);
    $display_name = !empty($s['company']) ? '"' . $s['company'] . '"' : $s['name'] . ' ' . $s['lastname'];
    $star = $s['count_rating'] > 0 ? 'fa fa-star' : 'fa fa-star-o';
    $rating_f = number_format((float)$s['rating'], 1);

    $map_adress = [];
    if (!empty($s['city']))   $map_adress[] = 'г. ' . $s['city'];
    if (!empty($s['street'])) $map_adress[] = $s['street'];
    if (!empty($s['house']))  $map_adress[] = $s['house'];
    $map_adress = implode(', ', $map_adress);

    $points[] = [
        'lat'     => (float)$lat,
        'lng'     => (float)$lng,
        'balloon' => '<div style="font-weight: 600;">'
           . '<a target="_blank" href="/studio/' . ($s['slug'] ?: 'id' . $s['id']) . '" '
           . 'style="color: #222222; text-decoration: none;" '
           . 'onmouseover="this.style.color=\'#0f86f3\'" '
           . 'onmouseout="this.style.color=\'inherit\'">'
           . $s['classification'] . ' ' . $display_name
           . '</a>'
           . ' <span><i style="color: #F2606C;" class="' . $star . '"></i> '
           . '<span style="color: #F2606C;">' . $rating_f . '</span></span></div>'
           . $map_adress,
        'url'     => $s['slug'] ? '/studio/' . $s['slug'] : '/studio/id' . $s['id'],
    ];
}

header('Content-Type: application/json');
echo json_encode($points, JSON_UNESCAPED_UNICODE);

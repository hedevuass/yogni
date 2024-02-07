<?php

$data = [];

if (isset($_SESSION['metro']) && $_SESSION['metro']) {
	$metro = get_metro_by_ids($_SESSION['metro']);
    $count = count($metro);

	if ($count == 1) $string = $metro[0]['name'];
    if ($count > 1) $string = "Выбрано ($count)";
} else {
	$metro = get_metro_in_city($_SESSION['city']['id']);
}


$metro_text = $string ?? 'Метро';
$data['filter_notaries'] = "<a class='metro-link'>{$metro_text}</a>";


// Подставляем фильтр метро
$HTML_ = explode( '<!--data:filter_notaries-->' , $HTML , 3 );
$HTML = $HTML_[0] . "<!--data:filter_notaries-->{$data['filter_notaries']}<!--data:filter_notaries-->" . $HTML_[2];
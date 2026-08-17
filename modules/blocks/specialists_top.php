<?php

$data = [];

$lastModifiedTopRow = get_last_modified();
$lastModifiedTop = $lastModifiedTopRow ? strtotime($lastModifiedTopRow['last_modified']) : time();

$specialists = getCachedData('promo_block_top_specialists_' . $lastModifiedTop, 600, function() {
    return get_promo_list('block_top_specialists');
});

$specialists = loadSpecializationList($specialists);

$data['specialists_top'] = block($specialists, false, true);


$HTML_ = explode( '<!--data:specialists_top-->' , $HTML , 3 );
$HTML = $HTML_[0] . "<!--data:specialists_top-->{$data['specialists_top']}<!--data:specialists_top-->" . $HTML_[2];
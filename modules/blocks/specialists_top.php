<?php

$data = [];

$specialists = get_promo_list('block_top_specialists');

$data['specialists_top'] = block($specialists);


$HTML_ = explode( '<!--data:specialists_top-->' , $HTML , 3 );
$HTML = $HTML_[0] . "<!--data:specialists_top-->{$data['specialists_top']}<!--data:specialists_top-->" . $HTML_[2];
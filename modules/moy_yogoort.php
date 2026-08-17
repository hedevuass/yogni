<?php
$data = [];
// Устанавливаем title
$HTML = html_replace([
    'title' => $title . 'Мой ЙОГУРТ'
], $HTML);

$HTML = html_explode($data, $HTML);
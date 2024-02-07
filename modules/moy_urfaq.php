<?php
$data = [];
// Устанавливаем title
$HTML = html_replace([
    'title' => $title . 'Мой ЮРФАК'
], $HTML);

$HTML = html_explode($data, $HTML);
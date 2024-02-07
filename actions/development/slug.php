<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

$services = get_services();

foreach ($services as $service) {
    $slug = stringToSlug($service['title']);
    update_service_slug($service['id'], $slug);
}

function stringToSlug(string $string)
{
    $replace = [
        'а' => 'a',   'б' => 'b',
        'в' => 'v',   'г' => 'g',
        'д' => 'd',   'е' => 'e',
        'ё' => 'yo',  'ж' => 'j',
        'з' => 'z',   'и' => 'i',
        'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',
        'н' => 'n',   'о' => 'o',
        'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',
        'у' => 'u',   'ф' => 'f',
        'х' => 'h',   'ц' => 'ts',
        'ч' => 'ch',  'ш' => 'sh',
        'щ' => 'sch', 'ъ' => '',
        'ы' => 'i',   'ь' => '',
        'э' => 'e',   'ю' => 'ju',
        'я' => 'ja',  ' ' => '-'
    ];

    $string = mb_strtolower($string, 'utf-8');
    $string = strtr($string, $replace);

    return preg_replace('~[^a-z\-.]~', '', $string);
}
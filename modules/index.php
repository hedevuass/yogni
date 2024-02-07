<?php

$data = [];

// Обрабатываем список актуальных разделов
$data = get_actual_services();

// Обрабатываем список юристов и адвокатов
$lawyers = get_specialists_by_classification([1, 2, 5, 6], 8);
$data['index_lawyers'] = block($lawyers);


// Обрабатываем список экспертов
$experts = get_specialists_by_classification([3, 7], 8);
$data['index_experts'] = block($experts);


// Обрабатываем список нотариусов
$notaries = get_specialists_by_classification([4, 8], 8);
$data['index_notaries'] = block($notaries);

// Обрабатываем список блогов
$articles = get_main_blog_articles(0, 6);
$data['articles'] = block_articles($articles);

$HTML = html_explode($data, $HTML);

/**
 * @return string[]
 */
function get_actual_services(): array
{
    $code = [
        'actual_service' => '<div class="index-sections-block">',
        'more_actual_service' => '<div class="index-sections-block last">',
    ];

    $services = get_services();
    $blocks = get_blocks();

    foreach ($blocks as $block) {
        if ($block['id'] > 2) continue;

        $content = json_decode($block['content'], true);

        $array_key = ($block['id'] == 1) ? 'actual_service' : 'more_actual_service';

        $code[$array_key] .= <<<EOF
            <div class="block-name">{$block['title']}</div>
                <div class="flex">
        EOF;

        foreach ($content as $value) {
            $service = $services[$value];

            $code[$array_key] .= <<<EOF
                <div class="item">
                    <img src="/assets/img/{$service['img']}">
                    <p><span>{$service['title']}</span></p>
                    <a href="/yuristy-i-advokaty/{$service['slug']}"></a>
                </div>
            EOF;

        }

        $code[$array_key] .= <<<EOF
            </div>
            <a class="bottom-link show-catalog">Показать все разделы</a>
        </div>
        EOF;
    }

    return $code;
}
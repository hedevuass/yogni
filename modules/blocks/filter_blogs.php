<?php

$data = [];

$main_theme = 'Тема блогов';
$data['filter_blog_id'] = '<input type="hidden" id="spec_id" value="0">';
if ($_SERVER['REQUEST_URI'] != '/') {
    $main_theme = 'Все темы';
    $requestion_arr = explode('/', $_SERVER['REQUEST_URI']);
    if(isset($requestion_arr[2])) {
        $requestion_str = $requestion_arr[2];
        if ($requestion_str != '') {
            $filt_spec_id = intval(str_replace('id', '', strtok($requestion_str, '?')));
        }
    } else {
        $filt_spec_id = 0;
    }
    $data['filter_blog_id'] = '<input type="hidden" id="spec_id" value="'.$filt_spec_id.'">';
}
// Подставляем фильтр темы блогов
$HTML_ = explode( '<!--data:filter_blog_id-->' , $HTML , 3 );
$HTML = $HTML_[0] . "<!--data:filter_blog_id-->{$data['filter_blog_id']}<!--data:filter_blog_id-->" . $HTML_[2];

$theme_id = 0;
if (isset($_GET['theme'])) {
    $theme_id = get_service_by_slug($_GET['theme'])['id'];
}
$themes = get_blog_filter_themes();

$data['filter_blog'] = <<<EOF
    <option value="0" class="hidden">{$main_theme}</option>
    <option value=".s0">Все темы</option>
EOF;

//разделы юристов
foreach ($themes as $theme) {
    if ($theme['classification'] == 3) {
        continue;
    }
    if (mb_stripos($theme['title'], 'прочие') !== false) {
        //раздел "прочие" в конец
        $last_theme = $theme;
    } else {
        $selected = ($theme_id == $theme['id']) ? 'selected' : '';
        $data['filter_blog'] .= "<option value='.s{$theme['id']}' data-slug='{$theme['slug']}' $selected>{$theme['title']}</option>";
    }
}
if (isset($last_theme) && $last_theme != '') {
    $selected = ($theme_id == $last_theme['id']) ? 'selected' : '';
    $data['filter_blog'] .= "<option value='.s{$last_theme['id']}' data-slug='{$last_theme['slug']}' $selected>{$last_theme['title']}</option>";
}

//разделы экспертиз
foreach ($themes as $theme) {
    if ($theme['classification'] == 1 || $theme['classification'] == 2) {
        continue;
    }
    if (mb_stripos($theme['title'], 'прочие') !== false) {
        //раздел "прочие" в конец
        $last_theme = $theme;
    } else {
        $selected = ($theme_id == $theme['id']) ? 'selected' : '';
        $data['filter_blog'] .= "<option value='.s{$theme['id']}' data-slug='{$theme['slug']}' $selected>{$theme['title']}</option>";
    }
}
if (isset($last_theme) && $last_theme != '') {
    $selected = ($theme_id == $last_theme['id']) ? 'selected' : '';
    $data['filter_blog'] .= "<option value='.s{$last_theme['id']}' data-slug='{$last_theme['slug']}' $selected>{$last_theme['title']}</option>";
}


// Подставляем фильтр темы блогов
$HTML_ = explode( '<!--data:filter_blogi-->' , $HTML , 3 );
$HTML = $HTML_[0] . "<!--data:filter_blogi-->{$data['filter_blog']}<!--data:filter_blogi-->" . $HTML_[2];

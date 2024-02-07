<?php

$data = [];
$is_edit = isset(explode('/', $_SERVER['REQUEST_URI'])[3]);
$article_id = false;
if($is_edit) {
    $article_id = intval(explode('/', $_SERVER['REQUEST_URI'])[3]);
    $article = get_article($article_id);
}
$data['edit_input'] = '<input type="hidden" name="is_edit" value="'.($is_edit ? $article_id : 'false').'">';

$title_input = '';
$content = '';
$keywords = '';

if (isset($article)) {
    $title_input = $article['title'];
    $content = $article['content'];
    $keywords = $article['keywords'];
}
//вывод полей к заполнению
$data['title_input'] = '<input type="text" name="title" placeholder="Название записи" value="'.$title_input.'">';
$data['content'] = '<textarea name="content" placeholder="Текст поста" maxlength="15200">'.$content.'</textarea>';
$data['keywords'] = '<input type="text" name="keywords" class="keywords-input" placeholder="До 4 слов" maxlength="60" value="'.$keywords.'">';

//скрытое поле айди блога для js
$blog_id = get_specialist_blog($_SESSION['user']['id'])['id'];
$data['blog_id_input'] = '<input type="hidden" name="blog_id" value="'.$blog_id.'">';

//вывод разделов в выбор раздела
$themes = get_blog_filter_themes();
$data['themes'] = "<option value='0' class='hidden'>Выбрать раздел</option>";

if ($article_id) {
    $request = get_article_themes($article_id);
    if ($request) {
        $theme_id = intval($request[array_key_first($request)]['id']);
        $theme_name = get_service($theme_id)['title'];
    }
}
$themes_btn_class = 'not-chosen';
$themes_btn_text = 'Раздел не выбран';
if(isset($theme_name)) {
    $themes_btn_class = '';
    $themes_btn_text = $theme_name;
}
$data['themes_mobile'] = '<div class="mobile-theme-btn '.$themes_btn_class.'"><span class="theme-btn-text">'.$themes_btn_text.'</span> <div class="delete-theme"></div></div>';

$data['themes_list_mobile'] = '';
//разделы юристов
foreach ($themes as $theme) {
    if ($theme['classification'] == 3) {
        continue;
    }
    if (mb_stripos($theme['title'], 'прочие') !== false) {
        //раздел "прочие" в конец
        $last_theme = $theme;
    } else {
        $selected = '';
        if(isset($theme_id)) {
            if ($theme_id == $theme['id']) $selected = 'selected';
        }
        $data['themes'] .= "<option value='{$theme['id']}' {$selected}>{$theme['title']}</option>";
        $data['themes_list_mobile'] .= '<li class="'.$theme['slug'].'" data-art-id="'.$theme['id'].'">'.$theme['title'].'</li>';
    }
}
if (isset($last_theme) && $last_theme != '') {
    $selected = '';
    if(isset($theme_id)) {
        if ($theme_id == $last_theme['id']) $selected = 'selected';
    }
    $data['themes'] .= "<option value='{$last_theme['id']}' {$selected}>{$last_theme['title']}</option>";
    $data['themes_list_mobile'] .= '<li class="'.$last_theme['slug'].'" data-art-id="'.$theme['id'].'">'.$last_theme['title'].'</li>';
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
        $selected = '';
        if(isset($theme_id)) {
            if ($theme_id == $theme['id']) $selected = 'selected';
        }
        $data['themes'] .= "<option value='{$theme['id']}' {$selected}>{$theme['title']}</option>";
        $data['themes_list_mobile'] .= '<li class="'.$theme['slug'].'" data-art-id="'.$theme['id'].'">'.$theme['title'].'</li>';
    }
}
if (isset($last_theme) && $last_theme != '') {
    $selected = '';
    if(isset($theme_id)) {
        if ($theme_id == $last_theme['id']) $selected = 'selected';
    }
    $data['themes'] .= "<option value='{$last_theme['id']}' {$selected}>{$last_theme['title']}</option>";
    $data['themes_list_mobile'] .= '<li class="'.$last_theme['slug'].'" data-art-id="'.$last_theme['id'].'">'.$last_theme['title'].'</li>';
}

$HTML = html_explode($data, $HTML);

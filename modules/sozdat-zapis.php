<?php

$data = [];

$forbidden_words = getForbiddenWords();

$is_edit = isset(explode('/', $_SERVER['REQUEST_URI'])[3]);
$article_id = false;
if($is_edit) {
    $article_id = intval(explode('/', $_SERVER['REQUEST_URI'])[3]);
    $article = get_article_cabinet($article_id);
}
$data['edit_input'] = '<input type="hidden" name="is_edit" value="'.($is_edit ? $article_id : 'false').'">
<input type="hidden" id="popup-forbidden-words" value="' . htmlspecialchars(json_encode($forbidden_words), ENT_QUOTES, "UTF-8") . '">';

$title_input = '';
$content = '';
$keywords = '';

if (isset($article)) {
    $title_input = $article['title'];
    $content = $article['content'];
    $keywords = $article['keywords'];
    $theme_id = intval($article['service_id']);
    $theme_name = $article['service_title'];
}

function html_to_textarea($html) {
    // Декодируем HTML-сущности (иногда нужно 2 раза, если они были вложены)
    $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
    $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8'); // второй проход

    // Заменяем <br> и закрывающие теги на перенос строки
    $html = preg_replace('/<\s*br\s*\/?>/i', "\n", $html);
    $html = preg_replace('/<\/(div|p|li|h[1-6])>/i', "\n", $html);

    // Убираем остальные HTML-теги
    $html = strip_tags($html);

    // Убираем лишние переносы
    $html = preg_replace("/\n{3,}/", "\n\n", $html);

    return trim($html);
}

// Вывод полей
$data['title_input'] = '
<input type="text" name="title" placeholder="Название записи (не более 65 символов)" maxlength="65" 
value="'.htmlspecialchars(html_to_textarea($title_input), ENT_QUOTES, 'UTF-8').'">
<div class="invalid-feedback" style="display:none;">Превышено допустимое количество символов</div>';

$data['content'] = '
<textarea name="content" placeholder="Текст поста (не более 7500 символов)" maxlength="7500">'.
htmlspecialchars(html_to_textarea($content), ENT_QUOTES, 'UTF-8').
'</textarea>
<div class="invalid-feedback" style="display:none;">Превышено допустимое количество символов</div>';

/* $data['keywords'] = '<input type="text" name="keywords" class="keywords-input" placeholder="До 4 слов" maxlength="60" value="'.$keywords.'">'; */

//скрытое поле айди блога для js
$blog_id = get_specialist_blog($_SESSION['user']['id'])['id'];
$data['blog_id_input'] = '<input type="hidden" name="blog_id" value="'.$blog_id.'">';

//вывод разделов в выбор раздела
$themes = get_blog_filter_themes();
$data['themes'] = "<option value='0' class='hidden'>Выбрать раздел</option>";

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
        $data['themes_list_mobile'] .= '<li class=" service-icon ' . $theme['slug'] . '" data-art-id="' . $theme['id'] . '"'
        . (!empty($theme['icon']) ? ' style="background-image: url(../assets/img/' . $theme['icon'] . ');"' : '')
        . '>' . $theme['title'] . '</li>';    
    }
}
if (isset($last_theme) && $last_theme != '') {
    $selected = '';
    if(isset($theme_id)) {
        if ($theme_id == $last_theme['id']) $selected = 'selected';
    }
    $data['themes'] .= "<option value='{$last_theme['id']}' {$selected}>{$last_theme['title']}</option>";
    $data['themes_list_mobile'] .= '<li class="service-icon ' . $last_theme['slug'] . '" data-art-id="' . $last_theme['id'] . '"'
    . (!empty($last_theme['icon']) ? ' style="background-image: url(../assets/img/' . $last_theme['icon'] . ');"' : '')
    . '>' . $last_theme['title'] . '</li>';
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
        $data['themes_list_mobile'] .= '<li class="service-icon ' . $theme['slug'] . '" data-art-id="' . $theme['id'] . '"'
        . (!empty($theme['icon']) ? ' style="background-image: url(../assets/img/' . $theme['icon'] . ');"' : '')
        . '>' . $theme['title'] . '</li>';    
    }
}
if (isset($last_theme) && $last_theme != '') {
    $selected = '';
    if(isset($theme_id)) {
        if ($theme_id == $last_theme['id']) $selected = 'selected';
    }
    $data['themes'] .= "<option value='{$last_theme['id']}' {$selected}>{$last_theme['title']}</option>";
    $data['themes_list_mobile'] .= '<li class="service-icon ' . $last_theme['slug'] . '" data-art-id="' . $last_theme['id'] . '"'
    . (!empty($last_theme['icon']) ? ' style="background-image: url(../assets/img/' . $last_theme['icon'] . ');"' : '')
    . '>' . $last_theme['title'] . '</li>';
}

$HTML = html_explode($data, $HTML);
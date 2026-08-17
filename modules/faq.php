<?php

$main_page = true;

//роутинг (определяем страницу - дочерняя или главная)
$requested_vars = explode('/', $_SERVER['REQUEST_URI']);
if (isset($requested_vars[3])) {
    $main_page = false;
    $faq_sub_page = get_faq_page($requested_vars[3]);
    if (!$faq_sub_page) {
        responseRedirect('/cabinet/');
    }
} else {
    $faq_sub_page = false;
}


$alias_back = '';
$name = 'Часто задаваемые вопросы';
$main_svg = 'faq-mobile.svg';
//вывод кнопки "назад" при необходимости
if ($faq_sub_page) {
    $alias_back = 'faq';
    $name = $faq_sub_page['name'];
    $main_svg = is_null($faq_sub_page['main_svg']) ? '' : $faq_sub_page['main_svg'];
}

$data['heading_faq'] = '<div class="heading__mobile faq-heading"><a href="/cabinet/'.$alias_back.'"></a><div class="text">'.$name.'</div></div>';

if ($faq_sub_page) {
    $data['heading_faq'] .= '<div class="heading__mobile faq-heading desctop"><a href="/cabinet/faq"></a><div class="text">'.$name.'</div></div>';
    $data['faq_content'] = is_null($faq_sub_page['content']) ? '' : $faq_sub_page['content'];
}
//вывод для главной faq
if (!$faq_sub_page) {
    $faq_pages = faq_pages();
    $data['faq_content'] = '
        <div class="support-top-text">В этом разделе мы постарались собрать самые популярные вопросы. Если это не помогло, вы можете обратиться в поддержку.</div>
        <div class="faq-mobile-title">Популярные вопросы</div>
        <ul>';
    foreach ($faq_pages as $faq_page){
        $descr = is_null($faq_page['short_descr']) ? '' : '<span class="faq-mobile-descr">'.$faq_page['short_descr'].'</span>';
        $alias = $faq_page['alias'] ? 'faq/'.$faq_page['alias'] : 'podderzhka';
        $data['faq_content'] .= '<li><a href="/cabinet/'.$alias.'" class="faq-link">
                ' . $faq_page['name'] . $descr .
                '<div class="faq-mobile-arrow"></div>
            </a></li>';
    }
    $data['faq_content'] .='</ul>
        <div class="support-top-text after-links">
            Не нашли нужный вопрос или требуется помощь в решении проблемы?
            <a href="/cabinet/podderzhka">Перейти в поддержку</a>
        </div>';
}

$HTML = html_explode([
	'heading_faq' => $data['heading_faq'],
    'faq_content' => $data['faq_content'],
], $HTML);

$HTML = html_replace([
    'title' => $title . ' - ЮРФАК'
], $HTML);

<?php

$data = [];

// обрабатывает год в футере
$data['year'] = date('Y', strtotime('NOW'));

// Социальные сети
$social_links = get_all_social();
$data['social_links'] = '<ul class="footer__social">';
foreach ($social_links as $social) {
    $data['social_links'] .= '<li><a href="' . htmlspecialchars($social['link']) . '" class="' . htmlspecialchars($social['alt']) . '"></a></li>';
}
$data['social_links'] .= '</ul>';

$footer_slugs = ['yuristy-i-advokaty', 'experty', 'notariusy'];
$data['footer_menu_top'] = '<ul>';

foreach ($footer_slugs as $slug) {
    $service = get_service_parent_catalog($slug);
    if ($service) {
        $title = htmlspecialchars($service['title']);
        $data['footer_menu_top'] .= '<li><a href="/' . $slug . '">' . $title . '</a></li>';
    }
}

// Журнал — вручную
$data['footer_menu_top'] .= '<li><a href="/journal">Журнал</a></li>';
$data['footer_menu_top'] .= '</ul>';

// Вставка в HTML
$HTML_ = explode('<!--data:footer_menu_top-->', $HTML, 3);
if (count($HTML_) === 3) {
    $HTML = $HTML_[0] . '<!--data:footer_menu_top-->' . $data['footer_menu_top'] . '<!--data:footer_menu_top-->' . $HTML_[2];
}

// Подставляем год
$HTML_ = explode( '<!--data:year-->' , $HTML , 3 );
$HTML = $HTML_[0] . "<!--data:year-->{$data['year']}<!--data:year-->" . $HTML_[2];

//получаем id специалиста для сообщения в поддержку
$data['input_user_id'] = "<input type='hidden' name='user' value='{$user['id']}'>";
$data['feedback_email'] = '<a href="mailto:' . EMAIL_FEEDBACK . '" class="blue-link">' . EMAIL_FEEDBACK . '</a>';

// Подставляем год
$HTML_ = explode( '<!--data:year-->' , $HTML , 3 );
$HTML = $HTML_[0] . "<!--data:year-->{$data['year']}<!--data:year-->" . $HTML_[2];
$HTML_ = explode('<!--data:feedback_email-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:feedback_email-->{$data['feedback_email']}<!--data:feedback_email-->" . $HTML_[2];
$HTML_ = explode('<!--data:social_links-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:social_links-->{$data['social_links']}<!--data:social_links-->" . $HTML_[2];
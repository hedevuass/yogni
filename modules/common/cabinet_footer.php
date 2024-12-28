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

// Подставляем год
$HTML_ = explode( '<!--data:year-->' , $HTML , 3 );
$HTML = $HTML_[0] . "<!--data:year-->{$data['year']}<!--data:year-->" . $HTML_[2];

//получаем id специалиста для сообщения в поддержку
$data['input_user_id'] = "<input type='hidden' name='user' value='{$user['id']}'>";

// Подставляем год
$HTML_ = explode( '<!--data:year-->' , $HTML , 3 );
$HTML = $HTML_[0] . "<!--data:year-->{$data['year']}<!--data:year-->" . $HTML_[2];
$HTML_ = explode('<!--data:social_links-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:social_links-->{$data['social_links']}<!--data:social_links-->" . $HTML_[2];
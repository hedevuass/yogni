<?php

$data = [];

// обрабатывает год в футере
$data['year'] = date('Y', strtotime('NOW'));

// Подставляем год
$HTML_ = explode( '<!--data:year-->' , $HTML , 3 );
$HTML = $HTML_[0] . "<!--data:year-->{$data['year']}<!--data:year-->" . $HTML_[2];

//получаем id специалиста для сообщения в поддержку
$data['input_user_id'] = "<input type='hidden' name='user' value='{$user['id']}'>";

// Подставляем год
$HTML_ = explode( '<!--data:year-->' , $HTML , 3 );
$HTML = $HTML_[0] . "<!--data:year-->{$data['year']}<!--data:year-->" . $HTML_[2];
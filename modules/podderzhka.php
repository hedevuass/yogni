<?php
$data = [];

$forbidden_words = getForbiddenWords();

$escaped_forbidden_words = htmlspecialchars(json_encode($forbidden_words), ENT_QUOTES, "UTF-8");

$data['popup_forbidden_words'] = '<input type="hidden" id="popup-forbidden-words" value="'. $escaped_forbidden_words . '">';

$specialist = get_specialist($_SESSION['user']['id']);
$data['input_specialist_email'] = '<input type="hidden" name="user_email" value="'.$specialist['email'].'"><input type="hidden" name="user_name" value="'.$specialist['name']. ' ' . $specialist['lastname'] .'">';

$phone = $specialist['phone'] ? str_replace('+7 ', '', format_phone($specialist['phone'])) : '';
$data['popup_tel_field'] = '<span class="phone-mask">+7</span>
<input type="text" name="phone" class="phone-input-support" placeholder="(926) 000-00-00" value="' . $phone . '">';

$data['popup_email_field'] = '<input type="email" name="email" class="field-input" placeholder="Электронная почта" value="' . htmlspecialchars($specialist['email']) . '">';

$name = $specialist['company'] ?? $specialist['name'] . " " . $specialist['lastname'];
$data['popup_name_field'] = '<input type="text" name="fio_or_company" class="field-input" placeholder="ФИО или название компании" value="' . htmlspecialchars($name) . '">';

$specialist_identifier = !empty($specialist['slug']) ? $specialist['slug'] : 'id' . $specialist['id'];
$specialist_link = 'https://' . $_SERVER['HTTP_HOST'] . '/specialist/' . $specialist_identifier;
$data['popup_page_link'] = '<input type="hidden" name="link" value="' . htmlspecialchars($specialist_link) . '">';

$data['date'] = '<input type="hidden" name="date" value="' . date('d.m.Y') .'" />';

$HTML = html_explode($data, $HTML);
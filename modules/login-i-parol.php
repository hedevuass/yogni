<?php

$user_id = $_SESSION['user']['id'];
$email = get_email_by_id($user_id);

$data['login_field'] = '<input type="text" id="user-email" disabled value="'.$email.'">';
$data['login_field2'] = '<input type="text" disabled value="'.$email.'">';

$HTML = html_explode([
	'login_field' => $data['login_field'],
	'login_field2' => $data['login_field2']
], $HTML);
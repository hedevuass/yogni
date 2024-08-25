<?php
$data = [];
$specialist = get_specialist($_SESSION['user']['id']);
$data['input_specialist_email'] = '<input type="hidden" name="user_email" value="'.$specialist['email'].'"><input type="hidden" name="user_name" value="'.$specialist['name']. ' ' . $specialist['lastname'] .'">';

$HTML = html_explode($data, $HTML);
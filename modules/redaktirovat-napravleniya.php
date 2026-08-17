<?php

$data = [];

// Получаем информацию о пользователе
$user = get_specialist($_SESSION['user']['id']);

// Получаем все специализации пользователя
$specializations = get_specialist_specialization($user['id']);

$selected_count = count($specializations);

$has_specializations = false;
if($selected_count > 0) {
	$has_specializations = true;
}

// Подставляем специализации
$data['cabinet_specialization_title'] = '<div class="link">Выбор направлений ('. ($selected_count) .') </div>';
$data['cabinet_specialization'] = <<<EOF
		<div class="select">
			<ul>
EOF;

// Подставляем кол-во выбранных специализаций
if($has_specializations) {
	$data['cabinet_quantity_specialization'] = <<<EOF
		<div class="specialization-quantity active">Направления ({$selected_count})</div>
	EOF;
} else {
	$data['cabinet_quantity_specialization'] = <<<EOF
		<div class="specialization-quantity">Направления не выбраны</div>
	EOF;
}
$data['confirm_ways_mob'] = <<<EOF
<div class="confirm-ways-mob disabled">Подтвердить ({$selected_count})</div>
EOF;

// Получаем все специализации по классификации пользователя
$all_specializations = get_services_by_classification($user['classification_id']);

$data['classification_text'] = '';
$data['classification_text_mob'] = '';

if (in_array($user['classification_id'], [3, 8])) {
	$data['classification_text'] = 'Выбранные направления будут отображаться на вашей личной странице, а также повлияют на добавление профиля в каталоги направлений йоги.';
	$data['classification_text_mob'] = 'Выбранные направления будут отображаться на вашей личной странице, а также повлияют на добавление профиля в каталоги направлений йоги.';
} else {
	$data['classification_text'] = 'Выбор подразделов права повлияет на добавление профиля в каталоги специалистов, но на личной странице будут
			отображаться только выбранные разделы права.';
	$data['classification_text_mob'] = 'Выбор подразделов права повлияет на добавление профиля в каталоги специалистов, но на личной странице будут
			отображаться только выбранные разделы права.';
}

$data['cabinet_specialization'] .= list_specialization($all_specializations, $specializations);

$data['cabinet_specialization'] .= <<<EOF
			</ul>
		</div>
EOF;

// Подставляем специализации в попап для мобилок
$data['cabinet_specialization_mobile'] = str_replace("class='checkbox' id='specialization", "class='checkbox' id='mob_specialization",$data['cabinet_specialization']);
$data['cabinet_specialization_mobile'] = str_replace("class='checkbox child-checkbox' id='specialization", "class='checkbox child-checkbox' id='mob_specialization",$data['cabinet_specialization_mobile']);
$data['cabinet_specialization_mobile'] = str_replace("<label for='specialization", "<label for='mob_specialization",$data['cabinet_specialization_mobile']);

$HTML = html_explode($data, $HTML);


/**
 * @param array $specializations
 * @param array $active
 * @return string
 */
function list_specialization(array $specializations, array $active): string
{
	$code = '';

	foreach ($specializations as $specialization) {
		if ($specialization['p_id'] != 0) continue;
		$checked = '';
		if(array_key_exists($specialization['id'], $active)) {
			$checked = 'checked';
		}
		$children = get_services_by_parrent_id($specialization['id']);
		$class = $children ? 'has-children' : '';
		$code .= "<li>
			<p class='{$class} {$checked}'>{$specialization['title']}</p>
			<input type='checkbox' name='specializations[{$specialization['id']}]' class='checkbox' id='specialization{$specialization['id']}' {$checked}>
			<label for='specialization{$specialization['id']}'></label>";
			if($children) {
				$code .="<ul class='child-ul'>";
				foreach ($children as $child) {
					$checked_child = '';
					if(array_key_exists($child['id'], $active)) {
						$checked_child = 'checked';
					}
					$code .= "<li>
						<p>{$child['title']}</p>
						<input type='checkbox' name='specializations[{$child['id']}]' class='checkbox child-checkbox' id='specialization{$child['id']}' {$checked_child}>
						<label for='specialization{$child['id']}'></label>
					</li>";
				}
				$code .= "</ul>";
			}
		$code .= "</li>";
	}

	return $code;
}
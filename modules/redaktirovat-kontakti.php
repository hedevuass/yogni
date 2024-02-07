<?php

$data = [];

// Получаем специалиста
$user = get_specialist($_SESSION['user']['id']);
// Подставляем телефон
if (!is_null($user['phone'])) {
	$user['phone'] = format_phone($user['phone']);
	$input_phone = str_replace('+7 ','',format_phone($user['phone']));
}
//телефон в попапе с контактами
$data['cabinet_contacts'] = <<<EOF
	<div class="item">
		<p>Телефон для звонков</p>
		<span class="phone-mask">+7</span>
		<input type="text" name="phone" placeholder="926 000-00-00" value="{$input_phone}">
	</div>
EOF;

if (!is_null($user['other'])) {
	$other_contacts = json_decode($user['other'], true);
}

//попап с контактами
$data['cabinet_contacts'] .= <<<EOF
	<div class="item">
		<p>Email</p>
		<input type="text" name="email" placeholder="Рабочая электронная почта" value="{$other_contacts['email']}">
	</div>
	<div class="item">
		<p>Сайт</p>
		<input type="text" name="site" placeholder="Адрес сайта (в формате urfaq.ru)" value="{$other_contacts['site']}">
	</div>
	<div class="item">
		<p>Телеграм</p>
		<input type="text" name="tg" placeholder="Имя пользователя" value="{$other_contacts['tg']}">
	</div>
	<div class="item">
		<p>Вконтакте</p>
		<input type="text" name="vk" placeholder="Адрес страницы в VK (vk.com/urfaq)" value="{$other_contacts['vk']}">
	</div>
	<div class="item">
		<p>Яндекс. Дзен</p>
		<input type="text" name="dzen" placeholder="Адрес страницы на Я.дзен" value="{$other_contacts['dzen']}">
	</div>
	<div class="item">
		<p>Адрес</p>
		<input type="hidden" name="city_id" value="{$user['city_id']}">
		<input type="text" name="city" placeholder="Город" value="{$user['city']}">
		<input type="hidden" name="metro_id" value="{$user['metro_id']}">
		<input type="text" name="metro" placeholder="Метро" value="{$user['metro']}">
		<input type="text" name="street" placeholder="Улица" value="{$user['street']}">
		<input type="text" name="house" placeholder="Дом" value="{$user['house']}">
	</div>
	<div class="item">
		<p>Как добраться</p>
		<textarea name="how_to_get" placeholder="Опишите наиболее удобный маршрут, особенности входа (например, необходимость предъявить документы на входе) или наличие парковки. Это поможет вашим клиентам быстрее сориентироваться на месте.">{$user['how_to_get']}</textarea>
	</div>
EOF;

// График работы
$data['cabinet_contacts'] .= select_schedule($user['schedule']);


// Превью карточки
$data['cabinet_cart'] = <<<EOF
	<ul>
		<li><a href="tel:{$user['phone']}"><img src="/assets/img/cabinet/contacts1.svg"> {$user['phone']}</a></li>
EOF;
if (!is_null($user['other'])) {
	$data['cabinet_cart'] .= cart_contacts($user['other']);
}

$data['cabinet_cart'] .= "</ul>";

$data['cabinet_cart_mob'] = $data['cabinet_cart'];


$HTML = html_explode($data, $HTML);


/**
 * @param string $select_name
 * @param string $selected
 * @param bool $empty
 * @return string
 */
function select_times(string $select_name, string $time, bool $empty = false): string
{
	$flag = false;
	$hours = 0;

	$code = "<select name='{$select_name}' class='sel'>";

	if ($empty) {
		$code .= '<option value="0" class="hidden">'.$time.'</option>';
	}

	for ($i = 0; $hours < 24; $i++) {
		$minutes = $flag ? '30' : '00';

		$value = sprintf("%02d", $hours) . ":" . $minutes;

		$selected = ($value == $time && !$empty) ? 'selected' : '';

		$code .= "<option {$selected}>{$value}</option>";

		if ($flag) $hours++;

		$flag = !$flag;
	}

	$code .= "</select>";

	return $code;
}

/**
 * @param string $shedulle
 * @return string
 */
function select_schedule(string $schedule): string
{
	$days = [
		1 => 'Пн',
		2 => 'Вт',
		3 => 'Ср',
		4 => 'Чт',
		5 => 'Пт',
		6 => 'Сб',
		7 => 'Вс',
	];
	if($schedule && !is_null($schedule)){
		$schedule = json_decode($schedule, true);
	}

	$code = <<<EOF
		<div class="days">
			<p>График работы</p>
			<div class="top-text"><span>Выходной</span></div>
	EOF;
	//расписание
	if($schedule && !is_null($schedule)){
		foreach ($schedule as $key => $day) {
			$checked = (isset($day['vac']) && isset($day['start'])) ? '' : 'checked';
			$holiday = (isset($day['vac']) && isset($day['start'])) ? '' : 'holliday';

			$code .= <<<EOF
				<div class="day-item">
					<input type="checkbox" name="days[$key]" class="checkbox" id="day{$key}" {$checked}><label for="day{$key}"></label>
					<div class="day {$holiday}">
						<span class="day-name">{$days[$key]}</span>
						<div>
			EOF;

			$code .= select_times("schedule_start[$key]", $day['start'] === false ? '10:00' : $day['start'], $day['start'] === false);
			$code .= "–";
			$code .= select_times("schedule_end[$key]", $day['end'] === false ? '19:30' : $day['end'], $day['end'] === false);

			$code .= <<<EOF
						</div>
					</div>
				</div>
			EOF;
		}
	} else {
		//если расписание не заполнено (пустое расписание)
		foreach ($days as $key => $day) {

			$code .= <<<EOF
				<div class="day-item">
					<input type="checkbox" name="days[$key]" class="checkbox" id="day{$key}"><label for="day{$key}"></label>
					<div class="day">
					<span class="day-name">{$days[$key]}</span>
					<div>
			EOF;

			$code .= select_times("schedule_start[$key]", '10:00', true);
			$code .= "–";
			$code .= select_times("schedule_end[$key]", '19:30', true);

			$code .= <<<EOF
						</div>
					</div>
				</div>
			EOF;
		}
	}

	$code .= "</div>";

	return $code;
}

/**
 * @param string $contacts
 * @param array $classes
 * @return string
 */
function cart_contacts(string $contacts, array $classes = []):string
{
	$contacts = json_decode($contacts, true);

	$code = '';

	foreach ($contacts as $key => $contact) {
		$class = '';
		if(!$contact) {
			$class .= 'hidden';
		}
		$code .= "<li class='$class'>";

		switch ($key) {
			case 'email':
				$code .=  "<a href='mailto:{$contact}'><img src='/assets/img/cabinet/contacts2.svg'> {$contact}</a>";
				break;
			case 'site':
				$code .=  "<a href='https://{$contact}' target='_blank'><span><img src='/assets/img/cabinet/contacts3.svg'> Сайт</span></a>";
				break;
			case 'tg':
				$code .=  "<a href='https://t.me/{$contact}' target='_blank'><span><img src='/assets/img/cabinet/contacts4.svg'> Телеграм</span></a>";
				break;
			case 'vk':
				$code .=  "<a href='https://vk.com/{$contact}' target='_blank'><span><img src='/assets/img/cabinet/contacts5.svg'> Вконтакте</span></a>";
				break;
			case 'dzen':
				$code .=  "<a href='https://dzen.ru/{$contact}' target='_blank'><span><img src='/assets/img/cabinet/contacts6.svg'> Яндекс Дзен</span></a>";
				break;
			
			default:
				# code...
				break;
		}

		$code .= "</li>";
	}

	return $code;
}
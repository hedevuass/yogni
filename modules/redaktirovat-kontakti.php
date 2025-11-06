<?php

$data = [];

$forbidden_words = getForbiddenWords();

$escaped_forbidden_words = htmlspecialchars(json_encode($forbidden_words), ENT_QUOTES, "UTF-8");

// Получаем специалиста
$user = get_specialist($_SESSION['user']['id']);
// Подставляем телефон
if (!is_null($user['phone'])) {
	$user['phone'] = format_phone($user['phone']);
	$input_phone = str_replace('+7 ', '', format_phone($user['phone']));
}
//телефон в попапе с контактами
$data['cabinet_contacts'] = <<<EOF
	<div class="item">
		<p>Телефон для звонков</p>
		<span class="phone-mask">+7</span>
		<input type="text" class="phone-input" name="phone" placeholder="(926) 000-00-00" value="{$input_phone}">
	</div>
EOF;

if (!is_null($user['other'])) {
	$other_contacts = json_decode($user['other'], true);
}

//попап с контактами
$data['cabinet_contacts'] .= <<<EOF
	<div class="item">
		<p>Публичный email</p>
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
		<input type="hidden" name="city_id" value="{$user['city_id']}" readonly>
		<input type="text" name="city" placeholder="Город" value="{$user['city']}" readonly>
		<input type="hidden" name="metro_id" value="{$user['metro_id']}">
		<input type="text" name="metro" placeholder="Метро" value="{$user['metro']}">
		<input type="text" name="street" placeholder="Улица" value="{$user['street']}">
		<input type="text" name="house" placeholder="Дом" value="{$user['house']}">
		 <input type="hidden" id="popup-forbidden-words" value="{$escaped_forbidden_words}">
	</div>
	<div class="item">
		<p>Как добраться</p>
		<textarea name="how_to_get" placeholder="Опишите наиболее удобный маршрут, особенности входа (например, необходимость предъявить документы на входе) или наличие парковки. Это поможет вашим клиентам быстрее сориентироваться на месте (не более 280 символов)" maxlength="280">{$user['how_to_get']}</textarea>
		<div class="invalid-feedback" style="display: none;">Превышено допустимое количество символов</div>
	</div>
EOF;

// График работы
$data['cabinet_contacts'] .= select_schedule($user['schedule']);
$data['cabinet_contacts_break'] .= select_break_schedule($user['schedule']);


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
    $code = "<select name='{$select_name}' class='sel'>";

    if ($empty) {
        $code .= '<option value="0" class="hidden">' . $time . '</option>';
    }

    for ($hours = 0; $hours < 24; $hours++) {
        for ($minutes = 0; $minutes < 60; $minutes += 15) {
            $value = sprintf("%02d:%02d", $hours, $minutes);
            $selected = ($value == $time && !$empty) ? 'selected' : '';
            $code .= "<option {$selected}>{$value}</option>";
        }
    }

    $code .= "</select>";

    return $code;
}


/**
 * @param string $shedulle
 * @return string
 */
function select_schedule(string|null $schedule): string
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
	if ($schedule && !is_null($schedule)) {
		$schedule = json_decode($schedule, true);
	}

	$code = <<<EOF
		<div class="days">
			<p>График работы</p>
			<div class="top-text"><span>Выходной</span></div>
	EOF;
	//расписание
	if ($schedule && !is_null($schedule)) {
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
	$code .= "<button class='add-break-button'>Добавить перерыв</button>";
	$code .= "</div>";

	return $code;
}

/**
 *
 * @param string
 * @return string
 */
function select_break_schedule(string|null $schedule): string
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

	if ($schedule && !is_null($schedule)) {
		$schedule = json_decode($schedule, true);
	}

	$code = <<<EOF
    <div class="days">
    EOF;

	foreach ($schedule as $key => $day) {
		// Получаем значения b_start и b_end (либо false, либо строка с временем)
		$b_start = isset($day['b_start']) ? $day['b_start'] : false;
		$b_end   = isset($day['b_end'])   ? $day['b_end']   : false;

		// Определяем CSS-класс "holliday" и "background-for-label" для лейбла
		$class = ($b_start === false || $b_end === false || $b_start === '' || $b_end === '')
			? 'holliday'
			: '';
		$class_label = ($b_start === false || $b_end === false || $b_start === '' || $b_end === '')
			? ''
			: 'background-for-label';

		// Определяем, должна ли галочка «перерыв» быть изначально checked
		$checked = ($b_start !== false && $b_start !== '' && $b_end !== false && $b_end !== '')
			? 'checked'
			: '';

		$code .= <<<EOF
        <div class="day-item day-item-break">
            <!-- 1) Переименовали name чекбокса с "days[$key]" на "break_active[$key]" -->
            <!-- 2) Добавили id="break_active_$key" -->
            <input
                type="checkbox"
                name="break_active[{$key}]"
                class="checkbox checkbox-break"
                id="break_active_{$key}"
                {$checked}
            >
            <!-- 3) У label прописали for="break_active_$key" и подставили класс $class_label -->
            <label class="checkbox-break {$class_label}" for="break_active_{$key}"></label>

            <div class="day day-break {$class}">
                <span>{$days[$key]}</span>
                <div id="day-time">
EOF;

		// 4) select_times(...) теперь получает в третьем параметре флаг disabled=true, если $b_start === false
		$code .= select_times(
			"break_start[{$key}]",
			($b_start === false ? '12:00' : $b_start),
			($b_start === false)
		);

		$code .= "–";

		$code .= select_times(
			"break_end[{$key}]",
			($b_end === false ? '13:00' : $b_end),
			($b_end === false)
		);

		$code .= <<<EOF
                </div>
            </div>
        </div>
EOF;
	}

	$code .= "</div>";
	return $code;
}

/* function create_schedule(): string
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

	$code = '<div class="days">';
	foreach ($days as $key => $day) {
		$code .= <<<HTML
            <div class="day-item">
                <input type="checkbox" name="days_break[$key]" class="checkbox checkbox-break" id="day_break{$key}">
                <label for="day_break{$key}"></label>
                <div class="day day-break">
                    <span class="day-name">$day</span>
                    <div>
HTML;

		$code .= select_times("schedule_start[$key]", '10:00', true);
		$code .= "–";
		$code .= select_times("schedule_end[$key]", '19:30', true);

		$code .= <<<HTML
                </div>
            </div>
        </div>
HTML;
	}
	$code .= "</div>";

	return $code;
} */

/**
 * @param string $contacts
 * @param array $classes
 * @return string
 */
function cart_contacts(string $contacts, array $classes = []): string
{
	$contacts = json_decode($contacts, true);

	$code = '';

	foreach ($contacts as $key => $contact) {
		$class = '';
		if (!$contact) {
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

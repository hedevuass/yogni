<?php

$tariffs = get_tariffs();
$promotions = get_promotions();
$options = get_options();

$data['tariff'] = "<div class='flex'>";

$data['tariff'] .= top_buttons($tariffs, $promotions, $options);

$data['tariff'] .= top_block((array)$data['user']);

$data['tariff'] .= "</div>";

$data['tariff'] .= block_tariffs($tariffs);

$data['tariff'] .= block_promotions($promotions);

$data['tariff'] .= block_options($options);

$HTML = html_explode([
	'tariff' => $data['tariff']
], $HTML);


/**
 * @param array $tariffs
 * @param array $promotions
 * @param array $options
 * @return string
 */
function top_buttons(array $tariffs, array $promotions, array $options): string
{
	$code = "<div class='top-buttons' id='buttons-scroll'>";

	if ($tariffs) $code .= "<button data-id='#tariffs'>Тарифы</button>";
	if ($promotions) $code .= "<button data-id='#promotions'>Продвижение страницы</button>";
	if ($options) $code .= "<button data-id='#options'>Опции</button>";
		
	$code .= "</div>";
	return $code;
}

/**
 * @param array $user
 * @return string
 */
function top_block(array $user): string
{
	$code = <<<EOF
		<div class='top-block flex'>
			<p>
	EOF;

	$count_specialists = get_count_specialists();
	
	$code .= "Вы сейчас на <span>{$user['rang']}</span> месте из <span>{$count_specialists}</span> <br />";

	$code .= <<<EOF
				Подробнее в <a href="#">Статистике</a>
			</p>
			<button>Поднять в поиске</button>
		</div>
	EOF;

	return $code;
}

/**
 * @param array $tariffs
 * @return string
 */
function block_tariffs(array $tariffs):string
{
	$code = '';

	if ($tariffs) {
		$code .= <<<EOF
			<div class="top-text" id='tariffs'>
				<span>Тарифы для максимально эффективного продвижения страницы</span>
				Наборы услуг для максимально эффективного продвижения страницы
			</div>
			<div class='list flex'>
		EOF;

		foreach ($tariffs as $tariff) {
			$class = $tariff['class'] ?? '';

			$code .= <<<EOF
				<div class="item {$class}">
					<div class="name">
						{$tariff['name']}
					</div>
					<ul>
			EOF;

			$options = json_decode($tariff['options'], true);

			foreach ($options as $option) {
				$code .= "<li><span><img src='/assets/img/cabinet/{$option['image']}'></span> {$option['text']}</li>";
			}

			$code .= <<<EOF
					</ul>
					<div class='price'>
			EOF;

			if ($tariff['price_old']) $code .= "<span class='old'>{$tariff['price_old']}</span>";
			
			$code .= <<<EOF
						{$tariff['price']} ₽ в мес.
						<button>Подключить</button>
					</div>
				</div>
			EOF;
		}

		$code .= "</div>";
	}

	return $code;
}

/**
 * @param array $promotions
 * @return string
 */
function block_promotions(array $promotions):string
{
	$code = '';

	if ($promotions) {
		$code .= <<<EOF
			<div class="promote" id="promotions">
				<div class="block-name">
					<span>Продвижение страницы</span>
					Наборы услуг для максимально эффективного продвижения страницы
				</div>
				<div class="table">
					<table>
						<thead>
							<tr>
								<td></td>
								<td>Цена за 1 неделю</td>
								<td></td>
								<td>
									<img src="/assets/img/cabinet/star.svg"> Первое место в блоке
									<p>Всего +285 ₽ в день</p>
								</td>
							</tr>
						</thead>
						<tbody>
						<tr>
							<td colspan="4">Места на главной странице сайта</td>
						</tr>
		EOF;

		foreach ($promotions as $key => $promotion) {
			$code .= <<<EOF
				<tr>
					<td>
						<div class="name">
							<span>{$promotion['title']}</span>
							{$promotion['description']}
						</div>
					</td>
					<td>
						<div class="price">
							{$promotion['price']} ₽
						</div>
					</td>
					<td><button>Подключить</button></td>
					<td>
						<input type="checkbox" class="checkbox" id="promotion{$key}"><label for="promotion{$key}"></label>
					</td>
				</tr>
			EOF;
		}

		$code .= <<<EOF
						</tbody>
					</table>
				</div>
			</div>
		EOF;
	}

	return $code;
}

/**
 * @param array $options
 * @return string
 */
function block_options(array $options):string
{
	$code = '';

	if ($options) {
		$code .= <<<EOF
			<div class="options" id="options">
				<div class="block-name">
					<span>Опции страницы</span>
					Наборы услуг для максимально эффективного продвижения страницы
				</div>
				<table>
					<thead>
						<tr>
							<td></td>
							<td>Статус и срок действия</td>
							<td></td>
							<td>Цена за 1 месяц</td>
						</tr>
					</thead>
					<tbody>
		EOF;
		$date = date('d.m.y', strtotime('+1 month'));

		foreach ($options as $key => $option) {
			$code .= <<<EOF
				<tr>
					<td>
						<div class="name">
							{$option['name']}
						</div>
					</td>
					<td>
						<input type="checkbox" class="checkbox" id="option{$key}">
						<label for="option{$key}">
							<span></span>
							<i></i>
						</label>
					</td>
					<td>
						{$date}
					</td>
					<td>
						{$option['price']} ₽
					</td>
				</tr>
			EOF;
		}
					
		$code .= <<<EOF
					</tbody>
				</table>
			</div>
		EOF;
	}

	return $code;
}
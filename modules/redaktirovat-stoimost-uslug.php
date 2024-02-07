<?php

$data = [];

// Получаем список услуг специалиста
$prices = get_specialist_service($_SESSION['user']['id']);

// Формируем HTML прайсов
foreach ($prices as $key => $price) {
	$checked = $price['ot'] ? 'checked' : '';

	$data['cabinet_price'] .= <<<EOF
		<tr>
			<td>
				<div class="links">
					<a data-id="{$price['id']}", data-name="{$price['name']}", data-price="{$price['price']}", data-of="{$price['ot']}"></a>
					<a></a>
				</div>
			</td>
			<td><input type="text" name="name[$key]" placeholder="Услуга" value="{$price['name']}"></td>
			<td><input type="text" name="price[$key]" placeholder="Цена" value="{$price['price']}"></td>
			<td><input type="checkbox" name="of[$key]" class="checkbox" data-key="{$key}" id="checkbox{$key}" $checked><label for="checkbox{$key}"></label></td>
			<td><a class="edit-price-mobile"></a><a class="delete"></a></td>
		</tr>
	EOF;
}

// Добавляем пустые инпуты
$count = count($prices);
$empty_input = ((5 - $count) > 0) ? (5 - $count) : 1;

for ($i = 0; $i < $empty_input; $i++) {
	$key = $count + 1 + $i;

    $data['cabinet_price'] .= <<<EOF
		<tr class="mob__empty">
			<td>
				<div class="links"><a></a><a></a></div>
			</td>
			<td><input type="text" name="name[$key]" placeholder="Услуга"></td>
			<td><input type="text" name="price[$key]" placeholder="Цена"></td>
			<td><input type="checkbox" name="of[$key]" class="checkbox" data-key="{$key}" id="checkbox{$key}"><label for="checkbox{$key}"></label></td>
			<td><a class="edit-price-mobile"></a><a class="delete disabled"></a></td>
		</tr>
	EOF;
}

$HTML = html_explode($data, $HTML);
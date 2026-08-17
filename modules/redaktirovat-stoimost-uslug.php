<?php

$data = [];
$forbidden_words = getForbiddenWords();

$escaped_forbidden_words = htmlspecialchars(json_encode($forbidden_words), ENT_QUOTES, "UTF-8");

// Получаем список услуг специалиста
$prices = get_specialist_service($_SESSION['user']['id']);

$data['cabinet_price'] .= <<<EOF
<input type="hidden" id="popup-forbidden-words" value="{$escaped_forbidden_words}">
EOF;

// Формируем HTML прайсов
foreach ($prices as $key => $price) {
	$checked = $price['ot'] ? 'checked' : '';

	$data['cabinet_price'] .= <<<EOF
		<tr style="white-space: nowrap;">
			<td>
				<div class="links">
					<a data-id="{$price['id']}", data-name="{$price['name']}", data-price="{$price['price']}", data-of="{$price['ot']}"></a>
					<a></a>
				</div>
			</td>
			<td><input type="text" name="name[$key]" placeholder="Наименование услуги (не более 40 символов)" maxlength="40" value="{$price['name']}">
			<div class="invalid-feedback" style="display:none;">Превышено допустимое количество символов</div></td>
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
			<td>
				<input type="text" name="name[$key]" placeholder="Наименование услуги (не более 40 символов)" maxlength="40">
				<div class="invalid-feedback" style="display:none;">Превышено допустимое количество символов</div>
			</td>
			<td><input type="text" name="price[$key]" placeholder="Цена"></td>
			<td><input type="checkbox" name="of[$key]" class="checkbox" data-key="{$key}" id="checkbox{$key}"><label for="checkbox{$key}"></label></td>
			<td><a class="edit-price-mobile"></a><a class="delete disabled"></a></td>
		</tr>
	EOF;
}

$HTML = html_explode($data, $HTML);
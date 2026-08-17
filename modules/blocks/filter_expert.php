<?php

$data = [];

$main_cat = 'Направление';

if ($_SERVER['REQUEST_URI'] != '/') {
    // 1. Очищаем путь от query-параметров (?amenities=33), оставляя чистый ЧПУ
    $clean_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // 2. Разбиваем уже чистый путь
    $uri_arr = explode('/', trim($clean_path, '/'));
    $uri_arr = array_values(array_filter($uri_arr, 'strlen')); // удаляем пустые сегменты

    $filter = in_array(end($uri_arr), ['private', 'company']) ? array_pop($uri_arr) : null;
    
    // 3. Теперь здесь будет строго чистый слаг 'ajengara-joga'
    $requestion_str = $uri_arr[1] ?? null; 
    
    if ($requestion_str) {
        $category = get_service_by_slug($requestion_str);
    } else {
        $category = false;
    }

    $main_cat = 'Все направления';
}

$services = get_services_for_menu();


$data['filter_expertise'] .= '<option value="0" class="hidden">' . $main_cat . '</option>
                                <option value=".s0" data-slug="lawyers">Все направления</option>';
foreach ($services[3][0] as $parrent) {
    $selected = ($parrent['id'] == $category['id']) ? 'selected' : '';
    $data['filter_expertise'] .= "<option value='{$parrent['slug']}' $selected>{$parrent['title']}</option>";
}
if (isset($last_cat) && $last_cat != '') {
    $selected = ($last_cat['id'] == $category['id']) ? 'selected' : '';
    $data['filter_expertise'] .= "<option value='{$last_cat['slug']}' $selected>{$last_cat['title']}</option>";
}

// Чекбоксы
$specialist = $company = $specialist_mob = $company_mob = '';
$all_mob = 'active';

$current_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Проверяем, содержится ли в пути "private" или "company"
if (str_contains($current_path, 'company') && !str_contains($current_path, 'private')) {
    $company = 'checked';
    $company_mob = 'active';
    $all_mob = '';
} elseif (str_contains($current_path, 'private') && !str_contains($current_path, 'company')) {
    $specialist = 'checked';
    $specialist_mob = 'active';
    $all_mob = '';
}

$data['filter_experts_checkbox'] = <<<EOF
<div>
    <input type="checkbox" class="checkbox" id="ch3" $specialist><label for="ch3">Частные</label>
    <input type="checkbox" class="checkbox" id="ch4" $company><label for="ch4">Компании</label>
</div>
EOF;

$get_amenities = !empty($_GET['amenities']) ? explode(',', $_GET['amenities']) : [];
$get_equipment = !empty($_GET['equipment']) ? explode(',', $_GET['equipment']) : [];

// 2. Исполняем чистые функции вместо прямых SQL-запросов
$all_amenities = get_equipment_by_category('amenity');
$all_equipment = get_equipment_by_category('equipment');

// 3. Формируем плашку УДОБСТВА
$amenities_count = count($get_amenities);
$amenities_title = $amenities_count > 0 ? "Удобства ($amenities_count)" : "Все удобства";
$amenities_html = '
    <div class="custom-select-trigger"><span>' . $amenities_title . '</span></div>
    <div class="custom-select-dropdown">
        <div class="custom-select-scroll">';
        foreach ($all_amenities as $item) {
            $checked = in_array($item['id'], $get_amenities) ? 'checked' : '';
            $amenities_html .= '
            <label class="dropdown-item">
                <span>' . htmlspecialchars($item['name']) . '</span>
                <input type="checkbox" value="' . $item['id'] . '" ' . $checked . '>
            </label>';
        }
$amenities_html .= '
        </div>
        <div class="dropdown-divider"></div>
        <div class="custom-select-footer">
            <span class="btn-confirm-selection">Подтвердить</span>
            <span class="btn-reset-selection">Сбросить</span>
        </div>
        <input type="hidden" class="confirmed-values" id="res-amenities" value="' . implode(',', $get_amenities) . '">
    </div>';

// 4. Формируем плашку ОБОРУДОВАНИЕ
$equipment_count = count($get_equipment);
$equipment_title = $equipment_count > 0 ? "Оборудование ($equipment_count)" : "Всё оборудование";
$equipment_html = '
    <div class="custom-select-trigger"><span>' . $equipment_title . '</span></div>
    <div class="custom-select-dropdown">
        <div class="custom-select-scroll">';
        foreach ($all_equipment as $item) {
            $checked = in_array($item['id'], $get_equipment) ? 'checked' : '';
            $equipment_html .= '
            <label class="dropdown-item">
                <span>' . htmlspecialchars($item['name']) . '</span>
                <input type="checkbox" value="' . $item['id'] . '" ' . $checked . '>
            </label>';
        }
$equipment_html .= '
        </div>
        <div class="dropdown-divider"></div>
        <div class="custom-select-footer">
            <span class="btn-confirm-selection">Подтвердить</span>
            <span class="btn-reset-selection">Сбросить</span>
        </div>
        <input type="hidden" class="confirmed-values" id="res-equipment" value="' . implode(',', $get_equipment) . '">
    </div>';

// Записываем собранный HTML в переменные data для шаблонизатора
$data['filter_amenities'] = $amenities_html;
$data['filter_equipment'] = $equipment_html;

$get_price = !empty($_GET['price']) ? array_filter(explode(',', $_GET['price'])) : [];

$price_options = [
    ['id' => 'under_1000', 'name' => "Менее 1000 ₽"],
    ['id' => '1000_5000',  'name' => "1000 ₽ - 5000 ₽"],
    ['id' => 'over_5000',  'name' => "Более 5000 ₽"]
];

$price_count = count($get_price);
$price_title = $price_count > 0 ? "Стоимость занятия ($price_count)" : "Стоимость занятия";

$price_html = '
    <div class="custom-select-trigger"><span>' . $price_title . '</span></div>
    <div class="custom-select-dropdown">
        <div class="custom-select-scroll">';
        foreach ($price_options as $item) {
            $checked = in_array($item['id'], $get_price) ? 'checked' : '';
            $price_html .= '
            <label class="dropdown-item">
                <span>' . htmlspecialchars($item['name']) . '</span>
                <input type="checkbox" value="' . $item['id'] . '" ' . $checked . '>
            </label>';
        }
$price_html .= '
        </div>
        <div class="dropdown-divider"></div>
        <div class="custom-select-footer">
            <span class="btn-confirm-selection">Подтвердить</span>
            <span class="btn-reset-selection">Сбросить</span>
        </div>
        <input type="hidden" class="confirmed-values" id="res-price" value="' . implode(',', $get_price) . '">
    </div>';

$data['filter_price'] = $price_html;

$data['filter_checkbox_mobile'] = <<<EOF
    <div class="catalog-checkbox-mobile dn" data-class-slug="yoga-studii">
        <div class="checkbox all_ch $all_mob">Все</div>
        <div class="checkbox specialist_ch $specialist_mob">Частные</div>
        <div class="checkbox company_ch $company_mob">Компании</div>
    </div>
EOF;

// Подставляем разделы в фильтр экспертов
$HTML_ = explode('<!--data:filter_expertise-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:filter_expertise-->{$data['filter_expertise']}<!--data:filter_expertise-->" . $HTML_[2];

$HTML_ = explode('<!--data:filter_experts_checkbox-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:filter_experts_checkbox-->{$data['filter_experts_checkbox']}<!--data:filter_experts_checkbox-->" . $HTML_[2];

$HTML_ = explode('<!--data:filter_amenities-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:filter_amenities-->{$data['filter_amenities']}<!--data:filter_amenities-->" . $HTML_[2];

$HTML_ = explode('<!--data:filter_equipment-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:filter_equipment-->{$data['filter_equipment']}<!--data:filter_equipment-->" . $HTML_[2];

$HTML_ = explode('<!--data:filter_price-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:filter_price-->{$data['filter_price']}<!--data:filter_price-->" . $HTML_[2];

if (strripos($HTML, '<!--data:filter_checkbox_mobile-->')) {
    $HTML_ = explode('<!--data:filter_checkbox_mobile-->', $HTML, 3);
    $HTML = $HTML_[0] . "<!--data:filter_checkbox_mobile-->{$data['filter_checkbox_mobile']}<!--data:filter_checkbox_mobile-->" . $HTML_[2];
}

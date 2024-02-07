<?php

$data = [];

$main_cat = 'Вид экспертизы';

if ($_SERVER['REQUEST_URI'] != '/') {
    $requestion_str = (explode('/', $_SERVER['REQUEST_URI']))[2];
    if($requestion_str) {
        $requestion = strtok($requestion_str, '?');
        $category = get_service_by_slug($requestion);
    }
    $main_cat = 'Все виды экспертиз';
}

$services = get_services_for_menu();


$data['filter_expertise'] .= '<option value="0" class="hidden">'.$main_cat.'</option>
                                <option value=".s0" data-slug="lawyers">Все виды экспертиз</option>';
foreach ($services[3][0] as $parrent) {
    if (mb_stripos($parrent['title'], 'прочие') !== false) {
        $last_cat = $parrent;
    } else {
        $selected = ($parrent['id'] == $category['id']) ? 'selected' : '';
        $data['filter_expertise'] .= "<option value='{$parrent['slug']}' $selected>{$parrent['title']}</option>";
    }
}
if (isset($last_cat) && $last_cat != '') {
    $selected = ($last_cat['id'] == $category['id']) ? 'selected' : '';
    $data['filter_expertise'] .= "<option value='{$last_cat['slug']}' $selected>{$last_cat['title']}</option>";
}

// Чекбоксы
$specialist = $company = '';
$all_mob = 'active';

if (isset($_GET['company']) && !isset($_GET['specialist'])) {
    $company = 'checked';
    $company_mob = 'active';
    $all_mob = '';
}

if (isset($_GET['specialist']) && !isset($_GET['company'])) {
    $specialist = 'checked';
    $specialist_mob = 'active';
    $all_mob = '';
}

$data['filter_experts_checkbox'] = <<<EOF
    <input type="checkbox" class="checkbox" id="ch3" $specialist><label for="ch3">Частные</label>
    <input type="checkbox" class="checkbox" id="ch4" $company><label for="ch4">Компании</label>
EOF;

$data['filter_checkbox_mobile'] = <<<EOF
    <div class="catalog-checkbox-mobile" data-class-slug="experty">
        <div class="checkbox $all_mob">Все</div>
        <div class="checkbox specialist_ch $specialist_mob">Частные</div>
        <div class="checkbox company_ch $company_mob">Компании</div>
    </div>
EOF;

// Подставляем разделы в фильтр экспертов
$HTML_ = explode( '<!--data:filter_expertise-->' , $HTML , 3 );
$HTML = $HTML_[0] . "<!--data:filter_expertise-->{$data['filter_expertise']}<!--data:filter_expertise-->" . $HTML_[2];

$HTML_ = explode( '<!--data:filter_experts_checkbox-->' , $HTML , 3 );
$HTML = $HTML_[0] . "<!--data:filter_experts_checkbox-->{$data['filter_experts_checkbox']}<!--data:filter_experts_checkbox-->" . $HTML_[2];

if (strripos($HTML,'<!--data:filter_checkbox_mobile-->')) {
    $HTML_ = explode( '<!--data:filter_checkbox_mobile-->' , $HTML , 3 );
    $HTML = $HTML_[0] . "<!--data:filter_checkbox_mobile-->{$data['filter_checkbox_mobile']}<!--data:filter_checkbox_mobile-->" . $HTML_[2];
}
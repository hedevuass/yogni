<?php

$data = [];
$main_cat = 'Раздел права';
if ($_SERVER['REQUEST_URI'] != '/') {
    $uri_arr = explode('/', $_SERVER['REQUEST_URI']);
    $requestion_str = end($uri_arr);
    if ($requestion_str) {
        $requestion = strtok($requestion_str, '?');
        $category = get_service_by_slug($requestion);
    } else {
        $category = false;
    }

    if ($category) {
        $parrent_category = get_service($category['p_id']);
    }
    $main_cat = 'Все разделы';
}

$data['filter_lawyer_category'] .= '<option value="0" class="hidden">'.$main_cat.'</option>
                                    <option value=".s0" data-slug="lawyers">Все разделы</option>';

$services = get_services_for_menu();

$default = $category ? '' : 'active';
$data['filter_lawyer_subcategory'] = <<<EOF
    <select class="sel subcat $default pen">
        <option value='0'>Подраздел права</option>
    </select>
    <select class="sel subcat s0 pen">
        <option value='0'>Подраздел права</option>
    </select>
EOF;

foreach ($services as $key => $service) {
    if ($key !== 1 && $key !== 2) continue;

    foreach ($service[0] as $parrent) {

        if (mb_stripos($parrent['title'], 'прочие') !== false) {
            //раздел "прочие" в конец
            $last_cat = $parrent;
        } else {

            $selected = ($parrent['id'] == $category['id'] || $parrent['id'] == $parrent_category['id']) ? 'selected' : '';

            $data['filter_lawyer_category'] .= "<option value='.s{$parrent['id']}' data-slug='{$parrent['slug']}' $selected>{$parrent['title']}</option>";

            $pen = isset($service[$parrent['id']]) ? '' : 'pen';

            $sub = $selected ? 'active' : '';
            $data['filter_lawyer_subcategory'] .= "<select class='sel subcat s{$parrent['id']} $pen $sub'>";

            if ($pen) {
                $data['filter_lawyer_subcategory'] .= "<option value='0'>Подраздел права</option>";
            }

            if (!$pen) {
                $data['filter_lawyer_subcategory'] .= "<option value='0'>Все подразделы</option>";
                $last_child = '';
                foreach ($service[$parrent['id']] as $child) {
                    if (mb_stripos($child['title'], 'прочие') !== false) {
                        //подраздел "прочие" в конец
                        $last_child = $child;
                    } else {
                    $selected = ($child['id'] == $category['id']) ? 'selected' : '';
                    $data['filter_lawyer_subcategory'] .= "<option value='{$child['slug']}' $selected>{$child['title']}</option>";
                    }
                }
                //подраздел "прочие" в конец
                if (isset($last_child) && $last_child != '') {
                    $selected = ($last_child['id'] == $category['id']) ? 'selected' : '';
                    $data['filter_lawyer_subcategory'] .= "<option value='{$last_child['slug']}' $selected>{$last_child['title']}</option>";
                }
            }
            $data['filter_lawyer_subcategory'] .= "</select>";
        }
    }
    //раздел "прочие" в конец
    if (isset($last_cat) && $last_cat != '') {

        $selected = ($last_cat['id'] == $category['id'] || $last_cat['id'] == $parrent_category['id']) ? 'selected' : '';

        $data['filter_lawyer_category'] .= "<option value='.s{$last_cat['id']}' data-slug='{$last_cat['slug']}' $selected>{$last_cat['title']}</option>";

        $pen = isset($service[$last_cat['id']]) ? '' : 'pen';

        $sub = $selected ? 'active' : '';
        $data['filter_lawyer_subcategory'] .= "<select class='sel subcat s{$last_cat['id']} $pen $sub'>";

        if ($pen) {
            $data['filter_lawyer_subcategory'] .= "<option value='0'>Подраздел права</option>";
        }

        if (!$pen) {
            $data['filter_lawyer_subcategory'] .= "<option value='0'>Все подразделы</option>";

            foreach ($service[$last_cat['id']] as $child) {
                if (mb_stripos($child['title'], 'прочие') !== false) {
                    $last_child = $child;
                } else {
                $selected = ($child['id'] == $category['id']) ? 'selected' : '';
                $data['filter_lawyer_subcategory'] .= "<option value='{$child['slug']}' $selected>{$child['title']}</option>";
                }
            }
            if (isset($last_child) && $last_child != '') {
                $selected = ($last_child['id'] == $category['id']) ? 'selected' : '';
                $data['filter_lawyer_subcategory'] .= "<option value='{$last_child['slug']}' $selected>{$last_child['title']}</option>";
            }
        }
        $data['filter_lawyer_subcategory'] .= "</select>";
    }
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

$data['filter_lawyer_checkbox'] = <<<EOF
    <input type="checkbox" class="checkbox" id="ch1" $specialist><label for="ch1">Частные</label>
    <input type="checkbox" class="checkbox" id="ch2" $company><label for="ch2">Компании</label>
EOF;

$data['filter_checkbox_mobile'] = <<<EOF
    <div class="catalog-checkbox-mobile" data-class-slug="yuristy-i-advokaty">
        <div class="checkbox $all_mob">Все</div>
        <div class="checkbox specialist_ch $specialist_mob">Частные</div>
        <div class="checkbox company_ch $company_mob">Компании</div>
    </div>
EOF;

// Подставляем разделы в фильтр юристов
$HTML_ = explode('<!--data:filter_lawyer_category-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:filter_lawyer_category-->{$data['filter_lawyer_category']}<!--data:filter_lawyer_category-->" . $HTML_[2];

$HTML_ = explode('<!--data:filter_lawyer_subcategory-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:filter_lawyer_subcategory-->{$data['filter_lawyer_subcategory']}<!--data:filter_lawyer_subcategory-->" . $HTML_[2];

$HTML_ = explode('<!--data:filter_lawyer_checkbox-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:filter_lawyer_checkbox-->{$data['filter_lawyer_checkbox']}<!--data:filter_lawyer_checkbox-->" . $HTML_[2];

if (strripos($HTML, '<!--data:filter_checkbox_mobile-->')) {
    $HTML_ = explode('<!--data:filter_checkbox_mobile-->', $HTML, 3);
    $HTML = $HTML_[0] . "<!--data:filter_checkbox_mobile-->{$data['filter_checkbox_mobile']}<!--data:filter_checkbox_mobile-->" . $HTML_[2];
}

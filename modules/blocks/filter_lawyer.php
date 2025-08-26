<?php

$data = [];
$main_cat = 'Раздел права';

if ($_SERVER['REQUEST_URI'] != '/') {
    $uri_arr = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
    $filter = in_array(end($uri_arr), ['private', 'company']) ? array_pop($uri_arr) : null;
    $requestion_str = end($uri_arr);
    $category = $requestion_str ? get_service_by_slug($requestion_str) : false;

    $original_id   = $category['original']['id']   ?? $category['id'];
$original_p_id = $category['original']['p_id'] ?? $category['p_id'];

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

    // проход по основным разделам
    foreach ($service[0] as $parrent) {
        // пропускаем "прочие" до конца
        if (mb_stripos($parrent['title'], 'прочие') !== false) {
            $last_cat = $parrent;
            continue;
        }

        // определяем, выбран ли этот пункт
        $isSelected = ($parrent['id'] == $original_id || $parrent['id'] == $original_p_id);

        // для выбранного — используем ALT-slug, иначе — обычный
        $slugForParrent = $isSelected ? $category['slug'] : $parrent['slug'];

        // строим option родительского раздела
        $data['filter_lawyer_category'] .=
            "<option value='.s{$parrent['id']}' data-slug='{$slugForParrent}'"
            . ($isSelected ? ' selected' : '')
            . ">{$parrent['title']}</option>";

        // подменю подразделов для этого раздела
        $pen = isset($service[$parrent['id']]) ? '' : 'pen';
        $subActive = $isSelected ? 'active' : '';
        $data['filter_lawyer_subcategory'] .=
            "<select class='sel subcat s{$parrent['id']} $pen $subActive'>";

        if ($pen) {
            $data['filter_lawyer_subcategory'] .=
                "<option value='0'>Подраздел права</option>";
        } else {
            $data['filter_lawyer_subcategory'] .=
                "<option value='0'>Все подразделы</option>";

            $last_child = null;
            foreach ($service[$parrent['id']] as $child) {
                if (mb_stripos($child['title'], 'прочие') !== false) {
                    $last_child = $child;
                    continue;
                }
                $isChildSelected = ($child['id'] == $original_id);
                $slugForChild = $isChildSelected ? $category['slug'] : $child['slug'];

                $data['filter_lawyer_subcategory'] .=
                    "<option value='{$slugForChild}'"
                    . ($isChildSelected ? ' selected' : '')
                    . ">{$child['title']}</option>";
            }

            // "прочие" в конец
            if ($last_child) {
                $isLastChildSelected = ($last_child['id'] == $original_id);
                $slugForLastChild = $isLastChildSelected ? $category['slug'] : $last_child['slug'];

                $data['filter_lawyer_subcategory'] .=
                    "<option value='{$slugForLastChild}'"
                    . ($isLastChildSelected ? ' selected' : '')
                    . ">{$last_child['title']}</option>";
            }
        }

        $data['filter_lawyer_subcategory'] .= "</select>";
    }

    // теперь раздел "прочие" в конец
    if (isset($last_cat)) {
        $isLastCatSelected = ($last_cat['id'] == $original_id || $last_cat['id'] == $original_p_id);
        $slugForLastCat = $isLastCatSelected ? $category['slug'] : $last_cat['slug'];

        $data['filter_lawyer_category'] .=
            "<option value='.s{$last_cat['id']}' data-slug='{$slugForLastCat}'"
            . ($isLastCatSelected ? ' selected' : '')
            . ">{$last_cat['title']}</option>";

        $pen = isset($service[$last_cat['id']]) ? '' : 'pen';
        $subActive = $isLastCatSelected ? 'active' : '';
        $data['filter_lawyer_subcategory'] .=
            "<select class='sel subcat s{$last_cat['id']} $pen $subActive'>";

        if ($pen) {
            $data['filter_lawyer_subcategory'] .=
                "<option value='0'>Подраздел права</option>";
        } else {
            $data['filter_lawyer_subcategory'] .=
                "<option value='0'>Все подразделы</option>";

            $last_child = null;
            foreach ($service[$last_cat['id']] as $child) {
                if (mb_stripos($child['title'], 'прочие') !== false) {
                    $last_child = $child;
                    continue;
                }
                $isChildSelected = ($child['id'] == $original_id);
                $slugForChild = $isChildSelected ? $category['slug'] : $child['slug'];

                $data['filter_lawyer_subcategory'] .=
                    "<option value='{$slugForChild}'"
                    . ($isChildSelected ? ' selected' : '')
                    . ">{$child['title']}</option>";
            }

            if ($last_child) {
                $isLastChildSelected = ($last_child['id'] == $original_id);
                $slugForLastChild = $isLastChildSelected ? $category['slug'] : $last_child['slug'];

                $data['filter_lawyer_subcategory'] .=
                    "<option value='{$slugForLastChild}'"
                    . ($isLastChildSelected ? ' selected' : '')
                    . ">{$last_child['title']}</option>";
            }
        }

        $data['filter_lawyer_subcategory'] .= "</select>";
    }
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

<?php

$data = [];

$all_equipments = get_all_equipment(); 

$selected_equipment = get_specialist_equipment($_SESSION['user']['id']); 

$amenities = [];
$equipments = [];

$selected_amenities_count = 0;
$selected_equipment_count = 0;

foreach ($all_equipments as $item) {
    $is_selected = array_key_exists($item['id'], $selected_equipment);
    
    if ($item['category'] === 'amenity') {
        $amenities[] = $item;
        if ($is_selected) {
            $selected_amenities_count++;
        }
    } elseif ($item['category'] === 'equipment') {
        $equipments[] = $item;
        if ($is_selected) {
            $selected_equipment_count++;
        }
    }
}

$data['cabinet_amenities_title'] = '<div class="link">Выбор удобств ('. $selected_amenities_count .') </div>';
$data['cabinet_equipment_title'] = '<div class="link">Выбор оборудования ('. $selected_equipment_count .') </div>';

$data['cabinet_equipment_mob_title'] = '<div class="popup__section-title">Выбор оборудования</div>';

$data['cabinet_amenities'] = '<div class="select select-equipment"><ul class="two-columns-list">';
$data['cabinet_amenities'] .= list_equipment_items($amenities, $selected_equipment, 'eq_');
$data['cabinet_amenities'] .= '</ul></div>';

$data['cabinet_equipment'] = '<div class="select select-equipment"><ul class="two-columns-list">';
$data['cabinet_equipment'] .= list_equipment_items($equipments, $selected_equipment, 'eq_');
$data['cabinet_equipment'] .= '</ul></div>';

$data['cabinet_amenities_mobile'] = str_replace("class='checkbox' id='eq_", "class='checkbox' id='mob_eq_", $data['cabinet_amenities']);
$data['cabinet_amenities_mobile'] = str_replace("<label for='eq_", "<label for='mob_eq_", $data['cabinet_amenities_mobile']);

$data['cabinet_equipment_mobile'] = str_replace("class='checkbox' id='eq_", "class='checkbox' id='mob_eq_", $data['cabinet_equipment']);
$data['cabinet_equipment_mobile'] = str_replace("<label for='eq_", "<label for='mob_eq_", $data['cabinet_equipment_mobile']);

if ($selected_amenities_count > 0 || $selected_equipment_count > 0) {
    $data['cabinet_quantity_equipment'] = '<div class="specialization-quantity active">Удобства (' . $selected_amenities_count . '), Оборудование (' . $selected_equipment_count . ')</div>';
} else {
    $data['cabinet_quantity_equipment'] = '<div class="specialization-quantity">Удобства и оборудование не выбраны</div>';
}

$data['confirm_equipment_mob'] = '<div class="confirm-ways-mob">Подтвердить</div>';

$HTML = html_explode($data, $HTML);

function list_equipment_items(array $items, array $active, string $prefix): string
{
    $code = '';

    foreach ($items as $item) {
        $checked = '';
        if (array_key_exists($item['id'], $active)) {
            $checked = 'checked';
        }
        
        $code .= "<li>
            <p class='{$checked}'>{$item['name']}</p>
            <input type='checkbox' name='equipment[{$item['id']}]' class='checkbox' id='{$prefix}{$item['id']}' {$checked}>
            <label for='{$prefix}{$item['id']}'></label>
        </li>";
    }

    return $code;
}
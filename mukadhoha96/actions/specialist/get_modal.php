<?php

include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id'])) {
    $id = clean_elem_form($_POST['id']);
    $specialist_id = $id;
    $specialist = get_specialist($id);
    $metros = $data_base->selectAll("SELECT * FROM dt_metro");
    if (!$specialist) {
        $specialist = [];
    }
    $specialist['photo'] = $specialist['photo'] ? '/uploads/avatars/' . $specialist['photo'] : '';

    $employee['photo'] = $employee['photo'] ? '/uploads/avatars/' . $employee['photo'] : '/assets/img/cabinet/gray-circle.svg';

    $specialist_classification = $specialist['classification_id'];
    $classifications = get_classifications();
    $cities = get_cities('name ASC');
    $reviews = get_reviews($specialist_id);
    $specializations = get_specialist_specialization($id);
    $all_specializations = get_services_by_classification($specialist_classification);

    $active_equipment = get_specialist_equipment($id);
    $all_equipment = get_all_equipment();

    $currentYear = intval(date("Y"));
    $foundation = $specialist['foundation'] ?? '';
    $isYear = is_numeric($foundation) && $foundation >= 1900 && $foundation <= $currentYear;
    $label = 'Опыт работы (кол-во лет)/год основания (год)';
    $universities = json_decode($specialist['university']) ?? [];

    $tariffs = get_tariffs();
    $options = get_options();
    $promotions = get_promotions();

    $connectedTariffs = get_connected_tariffs($specialist_id);
    $optionsList = get_connected_options($specialist_id);

    // Проверяем, что переменные являются массивами перед применением array_column()
    $connectedTariffIds = is_array($connectedTariffs) ? array_column($connectedTariffs, 'tariff_id') : [];
    $connectedOptionIds = is_array($optionsList) ? array_column($optionsList, 'option_id') : [];

    $tariffIdMap = [];
    foreach ($connectedTariffs as $tariff) {
        $tariffIdMap[$tariff['tariff_id']] = $tariff['id'];
    }


    $specialization_html .= renderSpecializations($all_specializations, $specializations, $specialist_classification);
    $equipment_html = renderEquipmentAndAmenities($all_equipment, $active_equipment);


    $json['code'] = get_view($_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'modules/modal/specialist.php', [
        'specialist' => $specialist,
        'metros' => $metros,
        'specialist_id' => $specialist_id,
        'specialist_classification' => $specialist_classification,
        'classifications' => $classifications,
        'cities' => $cities,
        'foundation' => $foundation,
        'label' => $label,
        'reviews' => $reviews,
        'universities' => $universities,
        'tariffs' => $tariffs,
        'options' => $options,
        'services' => $services,
        'promotions' => $promotions,
        'connectedTariffs' => $connectedTariffs,
        'connectedTariffIds' => $connectedTariffIds,
        'optionsList' => $optionsList,
        'connectedOptionIds' => $connectedOptionIds,
        'specialization_html' => $specialization_html,
        'equipment_html' => $equipment_html
    ]);

    responseJson($json);
}

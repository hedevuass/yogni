<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';


if (isset($_POST['sort'])) {

    $id   = clean_elem_form($_POST['id'] ?? '');
    $sort = (int)$_POST['sort'];

    $specialist_id = $id;
    $specialist    = get_specialist($id);

    if (!$specialist) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Специалист не найден']);
        exit();
    }

    $specialist_classification = (int)$specialist['classification_id'];

    $occupied_entries = [];

    if (!empty($_POST['service_slug'])) {
        $service_slug = $_POST['service_slug'];

        // Обработка для специальных service_slug, когда нужно использовать block_id
        if ($service_slug == 'block_specialist_positions') {
            $block_id = 2;

            $conflicting_entries = get_occupied_entries_by_block_and_service($block_id, $service_slug, $sort, $specialist_id);

            // Подсчёт только записей с sort = 2 (для проверки >= 7)
            $conflicting_entries_sort2 = array_filter($conflicting_entries, function ($e) {
                return isset($e['sort']) && (int)$e['sort'] === 2;
            });

            foreach ($conflicting_entries as $entry) {
                // Если это тот же специалист — блокируем всегда
                if ($entry['specialist_id'] == $specialist['id']) {
                    $occupied_entries[] = [
                        'date_start'   => strtotime($entry['date_start']),
                        'date_expires' => strtotime($entry['date_expires']),
                    ];
                } else {
                    // Другие специалисты
                    if ($sort == 2 && count($conflicting_entries_sort2) >= 7) {
                        $occupied_entries[] = [
                            'date_start'   => strtotime($entry['date_start']),
                            'date_expires' => strtotime($entry['date_expires']),
                        ];
                    } elseif ($sort != 2) {
                        $occupied_entries[] = [
                            'date_start'   => strtotime($entry['date_start']),
                            'date_expires' => strtotime($entry['date_expires']),
                        ];
                    }
                }
            }
        } else {

            $conflicting_entries = get_conflicting_entries($specialist['id'], $service_slug);

            // Далее обрабатываем только для обычных service_slug
            foreach ($conflicting_entries as $entry) {
                $entry['date_start'] = strtotime($entry['date_start']);
                $entry['date_expires'] = strtotime($entry['date_expires']);
                $occupied_entries[] = $entry;
            }

            // Логика по sort = 2 применяется только для обычных записей
            if ($sort == 2) {
                // считаем только записи с sort = 2 (без создания новой функции)
                $entries_sort2 = get_occupied_entries($service_slug, 2);
                $entry_count = is_array($entries_sort2) ? count($entries_sort2) : 0;

                if ($entry_count >= 7) {
                    $additional_entries = get_occupied_entries($service_slug, 2);
                    foreach ($additional_entries as $entry) {
                        $entry['date_start'] = strtotime($entry['date_start']);
                        $entry['date_expires'] = strtotime($entry['date_expires']);
                        $occupied_entries[] = $entry;
                    }
                }
            } else {
                $additional_entries = get_occupied_entries($service_slug, $sort);
                foreach ($additional_entries as $entry) {
                    $entry['date_start'] = strtotime($entry['date_start']);
                    $entry['date_expires'] = strtotime($entry['date_expires']);
                    $occupied_entries[] = $entry;
                }
            }
        }
    } elseif (!empty($_POST['block_id'])) {
        $block_id = $_POST['block_id'];

        $conflicting_entries = get_conflicting_entries_by_block($specialist['id'], $block_id);

        foreach ($conflicting_entries as $entry) {
            $entry['date_start'] = strtotime($entry['date_start']);
            $entry['date_expires'] = strtotime($entry['date_expires']);
            $occupied_entries[] = $entry;
        }

        if ($sort == 2) {
            // считаем только записи с sort = 2 для данного блока
            $entries_sort2 = get_occupied_entries_by_block($block_id, 2);
            $entry_count = is_array($entries_sort2) ? count($entries_sort2) : 0;

            if ($entry_count >= 7) {
                $additional_entries = get_occupied_entries_by_block($block_id, 2);
                foreach ($additional_entries as $entry) {
                    $entry['date_start'] = strtotime($entry['date_start']);
                    $entry['date_expires'] = strtotime($entry['date_expires']);
                    $occupied_entries[] = $entry;
                }
            }
        } else {
            $additional_entries = get_occupied_entries_by_block($block_id, $sort);
            foreach ($additional_entries as $entry) {
                $entry['date_start'] = strtotime($entry['date_start']);
                $entry['date_expires'] = strtotime($entry['date_expires']);
                $occupied_entries[] = $entry;
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($occupied_entries);
    exit();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Неверный запрос']);
}

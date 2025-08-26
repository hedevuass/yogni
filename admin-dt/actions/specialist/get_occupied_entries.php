<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

function logp($msg, $data = null) {
    $line = '[' . date('c') . '] ' . $msg;
    if ($data !== null) {
        $line .= ' ' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    error_log($line);
}

logp('REQUEST: incoming $_POST', $_POST);

if (isset($_POST['sort'])) {

    $id   = clean_elem_form($_POST['id'] ?? '');
    $sort = (int)$_POST['sort'];

    logp('STEP: sanitized inputs', [
        'id'          => $id,
        'sort'        => $sort,
        'service_slug'=> $_POST['service_slug'] ?? null,
        'block_id'    => $_POST['block_id'] ?? null,
    ]);

    $specialist_id = $id;
    $specialist    = get_specialist($id);

    if (!$specialist) {
        logp('ERROR: specialist not found', ['id' => $id]);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Специалист не найден']);
        exit();
    }

    $specialist_classification = (int)$specialist['classification_id'];
    logp('INFO: specialist loaded', [
        'specialist_id'       => $specialist_id,
        'classification_id'   => $specialist_classification
    ]);

    $occupied_entries = [];

    if (!empty($_POST['service_slug'])) {
        $service_slug = $_POST['service_slug'];
        logp('BRANCH: service_slug', ['service_slug' => $service_slug, 'sort' => $sort]);

        // спец-кейс: block_specialist_positions → принудительно block_id=2
        if ($service_slug == 'block_specialist_positions') {
            $block_id = 2;
            logp('SPECIAL CASE: block_specialist_positions → force block_id=2');

            $conflicting_entries = get_occupied_entries_by_block_and_service($block_id, $service_slug, $sort, $specialist_id);
            logp('DATA: conflicting_entries (block+service)', ['count' => count($conflicting_entries), 'entries' => $conflicting_entries]);

            if ($sort == 2 && count($conflicting_entries) >= 7) {
                foreach ($conflicting_entries as $entry) {
                    $entry['date_start']   = strtotime($entry['date_start']);
                    $entry['date_expires'] = strtotime($entry['date_expires']);
                    $occupied_entries[] = $entry;
                }
                logp('ACTION: blocked dates sort=2 (>=7)', ['added' => count($conflicting_entries)]);
            } elseif ($sort != 2) {
                foreach ($conflicting_entries as $entry) {
                    $entry['date_start']   = strtotime($entry['date_start']);
                    $entry['date_expires'] = strtotime($entry['date_expires']);
                    $occupied_entries[] = $entry;
                }
                logp('ACTION: blocked dates for sort!=2', ['added' => count($conflicting_entries)]);
            }
        } else {
            $conflicting_entries = get_conflicting_entries($specialist['id'], $service_slug);
            logp('DATA: conflicting_entries (service_slug)', ['count' => count($conflicting_entries), 'entries' => $conflicting_entries]);

            foreach ($conflicting_entries as $entry) {
                $entry['date_start']   = strtotime($entry['date_start']);
                $entry['date_expires'] = strtotime($entry['date_expires']);
                $occupied_entries[] = $entry;
            }

            if ($sort == 2) {
                $entry_count = get_entry_count_by_service_slug($service_slug);
                logp('CHECK: entry_count by service_slug', ['service_slug' => $service_slug, 'count' => $entry_count]);

                if ($entry_count >= 7) {
                    $additional_entries = get_occupied_entries($service_slug, $sort);
                    logp('DATA: additional_entries (service_slug)', ['count' => count($additional_entries), 'entries' => $additional_entries]);
                    foreach ($additional_entries as $entry) {
                        $entry['date_start']   = strtotime($entry['date_start']);
                        $entry['date_expires'] = strtotime($entry['date_expires']);
                        $occupied_entries[] = $entry;
                    }
                }
            } else {
                $additional_entries = get_occupied_entries($service_slug, $sort);
                logp('DATA: additional_entries (service_slug sort!=2)', ['count' => count($additional_entries), 'entries' => $additional_entries]);
                foreach ($additional_entries as $entry) {
                    $entry['date_start']   = strtotime($entry['date_start']);
                    $entry['date_expires'] = strtotime($entry['date_expires']);
                    $occupied_entries[] = $entry;
                }
            }
        }
    } elseif (!empty($_POST['block_id'])) {
        $block_id = $_POST['block_id'];
        logp('BRANCH: block_id', ['block_id' => $block_id, 'sort' => $sort]);

        $conflicting_entries = get_conflicting_entries_by_block($specialist['id'], $block_id);
        logp('DATA: conflicting_entries (block_id)', ['count' => count($conflicting_entries), 'entries' => $conflicting_entries]);

        foreach ($conflicting_entries as $entry) {
            $entry['date_start']   = strtotime($entry['date_start']);
            $entry['date_expires'] = strtotime($entry['date_expires']);
            $occupied_entries[] = $entry;
        }

        if ($sort == 2) {
            $entry_count = get_entry_count_by_block($block_id);
            logp('CHECK: entry_count by block', ['block_id' => $block_id, 'count' => $entry_count]);

            if ($entry_count >= 8) {
                $additional_entries = get_occupied_entries_by_block($block_id, $sort);
                logp('DATA: additional_entries (block_id)', ['count' => count($additional_entries), 'entries' => $additional_entries]);
                foreach ($additional_entries as $entry) {
                    $entry['date_start']   = strtotime($entry['date_start']);
                    $entry['date_expires'] = strtotime($entry['date_expires']);
                    $occupied_entries[] = $entry;
                }
            }
        } else {
            $additional_entries = get_occupied_entries_by_block($block_id, $sort);
            logp('DATA: additional_entries (block_id sort!=2)', ['count' => count($additional_entries), 'entries' => $additional_entries]);
            foreach ($additional_entries as $entry) {
                $entry['date_start']   = strtotime($entry['date_start']);
                $entry['date_expires'] = strtotime($entry['date_expires']);
                $occupied_entries[] = $entry;
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($occupied_entries);
    logp('FINAL RESPONSE', ['count' => count($occupied_entries), 'entries' => $occupied_entries]);
    exit();
} else {
    logp('ERROR: wrong request (no sort param)');
    echo json_encode(['status' => 'error', 'message' => 'Неверный запрос']);
}

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/functions.php';
/* if (isset($_POST['favorite'])) {
    session_start();
    $_SESSION['favorite'] = json_decode($_POST['favorite']);
    if (isset($_POST['company_page']) && $_POST['company_page']) {
        responseJson(true);
    }
    if (isset($_POST['specialization']) && $_POST['specialization']) {
        $specialization = $_POST['specialization'];
        $ids = $_SESSION['favorite']->{$specialization};
        if(empty($ids)) {
            $json['specialists'] = "Не найдено избранных специалистов/компаний.";
            $count = 0;
        } else {
            $specialists =  get_specialists_by_ids($ids, 16, 0);
            $count = count_specialists_by_ids($ids); 
            $json['specialists'] = $specialists ? block($specialists) : false;
            if (count($specialists) >= 16) {
                $json['pagination'] = block_pagination_fav($count, 16, 1, $specialization);
            }
        }

        $json['page'] = 2;

        responseJson($json);
    }
    return false;
} */
if (isset($_POST['favorite'])) {
    session_start();
    $_SESSION['favorite'] = $_POST['favorite']; // убрали json_decode()

    if (isset($_POST['company_page']) && $_POST['company_page']) {
        responseJson(true);
    }

    if (isset($_POST['specialization']) && $_POST['specialization']) {
        $specializations = $_POST['specialization'];
        if (!is_array($specializations)) {
            $specializations = [$specializations];
        }

        $ids = [];
        if (!empty($_SESSION['favorite']['all'])) {
            $ids = $_SESSION['favorite']['all'];
        } else {
            foreach ($specializations as $specialization) {
                if (!empty($_SESSION['favorite'][$specialization])) {
                    $ids = array_merge($ids, $_SESSION['favorite'][$specialization]);
                }
            }
        }

        $count = count($ids);

        if (empty($ids)) {
            $json['specialists'] = "Не найдено избранных специалистов/компаний.";
            $count = 0;
        } else {
            $specialists = get_specialists_by_ids($ids); // без лимита
            $foundById = [];
            foreach ($specialists as $s) {
                $foundById[(int)$s['id']] = $s;
            }

            $ordered = [];
            foreach ($ids as $id) {
                $id = (int)$id;
                if (isset($foundById[$id])) {
                    $ordered[] = $foundById[$id];
                    if (count($ordered) >= 16) break; // лимит
                }
            }

            $json['specialists'] = block($ordered);
            if ($count > 16) { // показываем пагинацию только если больше 16
                $json['pagination'] = block_pagination_fav($count, 16, 1, implode(',', $specializations));
            }
        }

        $json['page'] = 2;
        responseJson($json);
    }

    return false;
}

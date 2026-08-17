<?php
include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . ADMIN_HREF . 'actions/database/functions.php';

if (isset($_POST['id']) && isset($_POST['banner_id'])) {
    $slidesData = [];
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/';

    foreach ($_POST['id'] as $i => $id) {
        $slideData = [
            'id' => (int)$id,
            'banner_id' => (int)$_POST['banner_id'][$i],
            'title' => clean_elem_form($_POST['title'][$i]),
            'subtitle' => clean_elem_form($_POST['subtitle'][$i]),
            'description' => json_encode([
                'line1' => clean_elem_form($_POST['description'][$i]['line1']),
                'line2' => clean_elem_form($_POST['description'][$i]['line2']),
                'line3' => clean_elem_form($_POST['description'][$i]['line3']),
                'line4' => clean_elem_form($_POST['description'][$i]['line4']),
            ]),
            'specialist_name' => clean_elem_form($_POST['specialist_name'][$i]),
            'classification' => clean_elem_form($_POST['classification'][$i]),
            'link' => clean_elem_form($_POST['link'][$i]),
            'button_text' => clean_elem_form($_POST['button_text'][$i]),
            'button_link' => clean_elem_form($_POST['button_link'][$i]),
        ];

        if (!empty($_FILES['image'])) {
            foreach ($_FILES['image']['name'] as $id => $fileName) {
                if (!empty($_FILES['image']['tmp_name'][$id])) {
                    $filePath = $uploadDir . basename($fileName);
                    if (move_uploaded_file($_FILES['image']['tmp_name'][$id], $filePath)) {
                        // Обновляем только для конкретного слайда
                        update_slide_image((int)$id, $fileName);
                    }
                }
            }
        }

        $slidesData[] = $slideData;
    }

    foreach ($slidesData as $slideData) {
        // Проверяем, существует ли слайд в базе данных по id и banner_id
        if (slide_exists($slideData['id'], $slideData['banner_id'])) {
            update_slide($slideData);
        } else {
            create_slide($slideData); 
        }
    }

    echo json_encode(['success' => 'Слайды успешно обновлены']);
}

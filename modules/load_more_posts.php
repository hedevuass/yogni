<?php
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/S.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Cases.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/CasesHelper.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Gender.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/BaseInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/NamesInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/RussianLanguage.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/Cases.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/RussianCasesHelper.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/FirstNamesInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/MiddleNamesInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/morphos/Russian/LastNamesInflection.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';

$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 1;
$start = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$post_type = isset($_POST['post_type']) ? $_POST['post_type'] : '';

if ($post_type !== '') {
    $posts = get_actual_posts($post_type);
} else {
    $posts = get_actual_posts('', 0, 0, $limit, $start);
}

$data = '';
foreach ($posts as $post) {
    $post_alias = '/journal/'.$post['alias'];
    $data .= '<div class="item2">';
    $data .= '<div class="flex post-data">';
    $data .= '<a href="/journal/'.$post['service_slug'].'" class="cat_'.$post['post_type'].'"><span>'.$post['service_title'].'</span></a>';
    $data .= '<div class="date">'.date('d.m.Y', strtotime($post['date'])).'</div>';
    $data .= '</div>';
    $data .= '<a href="'.$post_alias.'" class="name">'.$post['title'].'</a>';
    if ($post['photo'] != '' && !is_null($post['photo'])) {
        $data .= '<a href="'.$post_alias.'" class="image" style="background-image: url(/uploads/articles/'.$post['photo'].');"></a>';
    }
    $data .= '<a href="'.$post_alias.'">'.cut_text($post['content'], 300).'</a>';
    $data .= '</div>'; 
}

echo json_encode(['posts' => $data]);


?>
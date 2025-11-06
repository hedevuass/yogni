<?php
$routes = $routes ?? [];
$urn = $urn ?? '';
$posts_count = count_blog_posts();
$pro_photo_count = count_pro_photo_list();
$banner_count = count_banner_list();
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'load_full_archive_banners') {
    $sp_id = intval($_POST['specialist_id'] ?? 0);
    $full = get_archived_banners($sp_id);
    if (empty($full)) {
        echo '<p>Нет архивных размещений</p>';
    } else {
        echo '<h4> Архивные размещения
                            <a href="#" id="toggle-all-archive" class="ms-2" style="font-size: 14px; color: #000;">(развернуть все)</a>
                        </h4>';
        foreach ($full as $archive) {
            echo render_archive_card($archive);
        }
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'load_full_pro_photo_requests') {
    $sp_id = intval($_POST['specialist_id'] ?? 0);
    $allProPhotoRequests = get_pro_photo_list();

    $filtered = array_filter($allProPhotoRequests, function ($req) use ($sp_id) {
        return $req['specialist_id'] == $sp_id && $req['moderation'] == 0;
    });

    if (empty($filtered)) {
        echo '<p>Нет архивных заявок</p>';
    } else {
        echo '<h4> Архивные размещения
                            <a href="#" id="toggle-all-pro-photo" class="ms-2" style="font-size: 14px; color: #000;">(развернуть все)</a>
                        </h4>';
        foreach ($filtered as $req) {
            echo render_pro_photo_request($req);
        }
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админпанель <?= $routes[$urn]['title'] ?? '' ?></title>
    <link href="assets/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/datatables.min.css" rel="stylesheet">
    <link href="<?= ADMIN_HREF ?>assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <div class="navbar-brand" href="<?= ADMIN_HREF ?>">Админпанель</div>
            <a href="actions/logout.php" class="btn btn-outline-danger btn-sm">Выйти</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-between" id="navbarSupportedContent">
    <div class="mx-auto">
        <ul class="navbar-nav mb-2 mb-lg-0">
                    <?php foreach ($routes as $key => $route): ?>
                        <?php
                        if ($key == ADMIN_HREF) continue;

                        $active_class = ($urn == $key) ? 'active' : '';
                        if ($key == ADMIN_HREF . 'index' && $urn == ADMIN_HREF) $active_class = 'active';

                        // Определяем, показывать ли бейдж для текущего маршрута
                        $badge_count = ($key === ADMIN_HREF . 'posts') ? $posts_count : 0;
                        $pro_photo_badge_count = ($key === ADMIN_HREF . 'photo') ? $pro_photo_count : 0; 
                        $banner_badge_count = ($key === ADMIN_HREF . 'banners') ? $banner_count : 0; 
                        ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $active_class ?>" aria-current="page" href="<?= $key ?>">
                                <?= $route['title'] ?>
                                <?php if ($badge_count > 0): ?>
                                    <span class="badge bg-warning text-white f"><?= $badge_count ?></span>
                                <?php endif; ?>
                                <?php if ($pro_photo_badge_count > 0): ?>
                                    <span class="badge bg-warning text-white f">
                                        <?= $pro_photo_badge_count ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($banner_badge_count > 0): ?>
                                    <span class="badge bg-warning text-white f"><?= $banner_badge_count ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>

                    <?php if (array_key_exists(ADMIN_HREF . 'editable', $routes)): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Редактировать страницы
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="<?= ADMIN_HREF ?>editable?page=index">Главная</a></li>
                                <li><a class="dropdown-item" href="<?= ADMIN_HREF ?>editable?page=apartments">Апартаменты</a></li>
                                <li><a class="dropdown-item" href="<?= ADMIN_HREF ?>editable?page=services">Услуги</a></li>
                                <li><a class="dropdown-item" href="<?= ADMIN_HREF ?>editable?page=certificates">Сертификаты</a></li>
                                <li><a class="dropdown-item" href="<?= ADMIN_HREF ?>editable?page=contacts">Контакты</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
                </div>
                <div class="d-flex">
                    <?php if (array_key_exists(ADMIN_HREF . 'editable', $routes)): ?>
                        <button type="button" id="editableSave" class="btn btn-outline-success" style="margin-right:5px">
                            Сохранить
                        </button>;
                    <?php endif; ?>
                    <a class="btn btn-outline-primary" href="/" target="_blank" role="button">Перейти к сайту</a>
                </div>
            </div>
        </div>
    </nav>
    <script src="assets/tinymce/tinymce.min.js" referrerpolicy="origin"></script>

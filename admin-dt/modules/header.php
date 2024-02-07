<?php
$routes = $routes ?? [];
$urn = $urn ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админпанель <?= $routes[$urn]['title'] ?? '' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="<?= ADMIN_HREF ?>assets/css/main.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= ADMIN_HREF ?>">Админпанель</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($routes as $key => $route): ?>
                    <?php
                    if ($key == ADMIN_HREF) continue;

                    $active_class = ($urn == $key) ? 'active' : '';
                    if ($key == ADMIN_HREF . 'specialists' && $urn == ADMIN_HREF) $active_class = 'active';

                    ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $active_class ?>" aria-current="page" href="<?= $key ?>"><?= $route['title'] ?></a>
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
                            <li><a class="dropdown-item"
                                   href="<?= ADMIN_HREF ?>editable?page=apartments">Апартаменты</a></li>
                            <li><a class="dropdown-item" href="<?= ADMIN_HREF ?>editable?page=services">Услуги</a></li>
                            <li><a class="dropdown-item"
                                   href="<?= ADMIN_HREF ?>editable?page=certificates">Сертификаты</a></li>
                            <li><a class="dropdown-item" href="<?= ADMIN_HREF ?>editable?page=contacts">Контакты</a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
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
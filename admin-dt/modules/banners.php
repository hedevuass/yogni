<?php
$banners = get_banners();
$journal_banners = get_journal_banners();
?>
<div id="bannersPage" class="wrapper">
    <p class="h1 text-center">
        Баннеры главной страницы
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="banners" class="table table-striped" style="width:100%">
                <thead>
                <tr>
                    <td>id</td>
                    <td class="text-nowrap">Заголовок баннера</td>
                    <td>Действие</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($banners as $banner): ?>
                    <tr>
                        <td><?= $banner['id'] ?></td>
                        <td><?= $banner['name'] ?></td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                <button type="submit" class="btn btn btn-info modalEdit" data-type="banner" data-id="<?= $banner['id'] ?>>">
                                    Редактировать слайды
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="h1 text-center">
        Баннеры журнала
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="journal_banners" class="table table-striped" style="width:100%">
                <thead>
                <tr>
                    <td>id</td>
                    <td class="text-nowrap">Заголовок баннера</td>
                    <td>Действие</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($journal_banners as $journal_banner): ?>
                    <tr>
                        <td><?= $journal_banner['id'] ?></td>
                        <td><?= $journal_banner['title'] ?></td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                <button type="submit" class="btn btn btn-info modalEdit" data-type="journal-banner" data-id="<?= $journal_banner['id'] ?>>">
                                    Редактировать баннер
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
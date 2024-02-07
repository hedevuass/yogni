<?php
$banners = get_banners();
?>
<div id="bannersPage" class="wrapper">
    <p class="h1 text-center">
        Баннеры
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="banners" class="table table-striped" style="width:100%">
                <thead>
                <tr>
                    <td class="text-nowrap">Заголовок баннера</td>
                    <td>Действие</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($banners as $banner): ?>
                    <tr>
                        <td><?= $banner['name'] ?></td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                <button type="submit" class="btn btn btn-info modalEdit" data-id="<?= $banner['id'] ?>>">
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
</div>
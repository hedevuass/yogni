<?php
$pro_photos = get_pro_photo();
?>
<div id="photoPage" class="wrapper">
    <p class="h1 text-center">
    Тарифы
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="photoTable" class="table table-striped" style="width:100%">
                <thead>
                <tr>
                    <td>Заголовок</td>
                    <td>Цена</td>
                    <td>Старая цена</td>
                    <td>Длительность</td>
                    <td>Кол-во фото</td>
                    <td class="col-4">Описание</td>
                    <td>Действие</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($pro_photos as $pro_photo): 
                if ($pro_photo['id'] !== '4') :
                    ?>
                    <tr>
                        <td><?= htmlspecialchars_decode($pro_photo['title']) ?></td>
                        <td><?= htmlspecialchars_decode($pro_photo['price']) ?></td>
                        <td><?= ($pro_photo['price_old']) ? $pro_photo['price_old']: '-' ?></td>
                        <td><?= $pro_photo['time'] ?></td>
                        <td><?= $pro_photo['number'] ?></td>
                        <td><?= htmlspecialchars_decode($pro_photo['description']) ?></td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                               
                                <button type="submit" class="btn modalAdd btn-info modalAdd" 
                                        data-id="<?= $pro_photo['id'] ?>">
                                    Изменить
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php
                endif;
            endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="h1 text-center">
    Съемка сотрудников
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="photoTable" class="table table-striped" style="width:100%">
                <thead>
                <tr>
                    <td>Заголовок</td>
                    <td>Цена</td>
                    <td>Старая цена</td>
                    <td>Длительность</td>
                    <td>Кол-во фото</td>
                    <td class="col-4">Описание</td>
                    <td>Действие</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($pro_photos as $pro_photo): 
                if ($pro_photo['id'] === '4') : ?>
                    <tr>
                        <td><?= htmlspecialchars_decode($pro_photo['title']) ?></td>
                        <td><?= htmlspecialchars_decode($pro_photo['price']) ?></td>
                        <td><?= ($pro_photo['price_old']) ? $pro_photo['price_old']: '-' ?></td>
                        <td><?= $pro_photo['time'] ?></td>
                        <td><?= $pro_photo['number'] ?></td>
                        <td><?= htmlspecialchars_decode($pro_photo['description']) ?></td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                               
                                <button type="submit" class="btn modalAdd btn-info modalAdd" 
                                        data-id="<?= $pro_photo['id'] ?>">
                                    Изменить
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php
                endif;
            endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$tariffs = get_tariffs();
$options = get_options();
$promotions = get_promotions();
?>
<div id="tariffPage" class="wrapper">
    <p class="h1 text-center">
        Тарифы
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="tariffs" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <td class="text-nowrap">Название</td>
                        <td class="text-nowrap">Опции</td>
                        <td class="text-nowrap">Зачеркнутая цена</td>
                        <td class="text-nowrap">Актуальная цена</td>
                        <td class="text-nowrap">Действие</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tariffs as $tariff):
                        $tariff_options = json_decode($tariff['options'], true); ?>
                        <tr>
                            <td><?= $tariff['name'] ?></td>
                            <td>
                                <ul>
                                    <?php foreach ($tariff_options as $option): ?>
                                        <li><?= $option['text'] ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td><?= $tariff['price_old'] ?>₽</td>
                            <td><?= $tariff['price'] ?>₽</td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                    <button type="submit" class="btn btn btn-info modalEdit" data-type="tariff" data-id="<?= $tariff['id'] ?>>">
                                        Редактировать стоимость
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="h1 text-center mt-5 mb-3">
        Промо-блоки
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="promo-options" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <td class="text-nowrap">Название</td>
                        <td class="text-nowrap">Описание</td>
                        <td class="text-nowrap">Цена</td>
                        <td class="text-nowrap">Действие</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($promotions as $promotion): ?>
                        <tr>
                            <td><?= $promotion['title'] ?></td>
                            <td><?= $promotion['description'] ?></td>
                            <td><?= $promotion['price'] ?>₽</td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                    <button type="submit" class="btn btn btn-info modalEdit" data-type="promotion" data-id="<?= $promotion['id'] ?>>">
                                        Редактировать стоимость
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="h1 text-center mt-5 mb-3">
        Опции
        <button data-type="option" type="button" class="btn btn-success modalEdit">
            Добавить опцию
        </button>
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="options" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <td class="text-nowrap col-6">Название</td>
                        <td class="text-nowrap col-3">Цена</td>
                        <td class="text-nowrap">Действие</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($options as $option): ?>
                        <tr>
                            <td><?= $option['name'] ?></td>
                            <td><?= $option['price'] ?>₽</td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                    <button type="submit" class="btn btn btn-info modalEdit" data-type="option" data-id="<?= $option['id'] ?>>">
                                        Редактировать стоимость
                                    </button>
                                    <button type="submit" class="btn btn-danger delete" data-id="<?= $option['id'] ?>">Удалить</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
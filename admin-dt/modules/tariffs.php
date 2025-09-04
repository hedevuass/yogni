<?php
$tariffs = get_tariffs();
$options = get_options();
$promotions = get_promotions();

$paid_connections = get_all_payments();
$paid_promos   = array_filter($paid_connections, fn($p) => ($p['tableType'] ?? '') === 'promo');
$tariff_connections = array_filter($paid_connections, fn($p) => ($p['tableType'] ?? '') === 'tariff');
$option_connections = array_filter($paid_connections, fn($p) => ($p['tableType'] ?? '') === 'option');

function getStatus($item, $type)
{
    // Если есть поле status и оно cancelled
    if (!empty($item['status']) && $item['status'] === 'cancelled') {
        return "<span class='text-danger'>Отключено</span>";
    }

    $now = new DateTime();
    $dateStart = !empty($item['date_start']) ? new DateTime($item['date_start']) : null;
    $dateEnd   = !empty($item['date_end']) ? new DateTime($item['date_end']) : null;

    // Нормализуем границы
    if ($dateStart) {
        $dateStart->setTime(0, 0, 0);       // начало дня
    }
    if ($dateEnd) {
        $dateEnd->setTime(23, 59, 59);      // конец дня
    }

    $statusText = '';
    $statusClass = ''; // CSS класс для цвета

    if ($type === 'promo') {
        if ($dateStart && $now < $dateStart) {
            $statusText = 'Запланировано';
            $statusClass = 'text-success';
        } elseif ($dateStart && $dateEnd && $now >= $dateStart && $now <= $dateEnd) {
            $statusText = 'Размещено';
            $statusClass = 'text-success';
        } elseif ($dateEnd && $now > $dateEnd) {
            $statusText = 'Завершено';
            $statusClass = 'text-muted';
        }
    } else { // tariff / option
        if ($dateEnd && $now > $dateEnd) {
            $statusText = 'Завершено';
            $statusClass = 'text-muted';
        } else {
            $statusText = 'Подключено';
            $statusClass = 'text-success';
        }
    }

    return "<span class='{$statusClass}'>" . htmlspecialchars($statusText) . "</span>";
}

// Функция сортировки: "Завершено" всегда в конец
function sortByStatus($a, $b, $type)
{
    $statusA = getStatus($a, $type);
    $statusB = getStatus($b, $type);

    if ($statusA === $statusB) return 0;
    return $statusA == 'Завершено' ? 1 : -1;
}

// Функция вывода таблицы
function renderTableRows($connections, $type)
{
    foreach ($connections as $item):
        $specialist = get_specialist($item['specialist_id'] ?? 0);
        $specialistName = $specialist
            ? (!empty($specialist['company']) ? $specialist['company'] : $specialist['name'] . ' ' . $specialist['lastname'])
            : '';
        $connectDate = !empty($item['createdAt']) ? date('d.m.Y', strtotime($item['createdAt'])) : '';
        $period = '';
        if (!empty($item['date_start'])) $period .= 'с ' . date('d.m.Y', strtotime($item['date_start']));
        if (!empty($item['date_end'])) $period .= ' по ' . date('d.m.Y', strtotime($item['date_end']));
        $cost = number_format($item['amount'] ?? 0, 2, ',', ' ');
        $status = getStatus($item, $type);
        $placeText = ($item['sort'] ?? 2) == 1 ? '1 место' : '2-8 место';

        $sortValue = !empty($item['date_start']) ? strtotime($item['date_start']) : 0;
?>
        <tr>
            <td class="d-none"><?= $sortValue ?></td>
            <td class="nowrap" style="width:10.4%"><?= $connectDate ?></td>
            <td class="nowrap" style="width:7.7%"><?= (int)$item['specialist_id'] ?></td>
            <td class="nowrap" style="width:17.4%"><?= htmlspecialchars($specialistName) ?></td>

            <?php if ($type === 'promo'): ?>
                <td class="nowrap" style="width:23.4%"><?= htmlspecialchars($item['serviceName'] ?? '') ?></td>
                <td class="nowrap" style="width:7.7%"><?= $placeText ?></td>
            <?php else: ?>
                <td class="nowrap" style="width:31.1%"><?= htmlspecialchars($item['serviceName'] ?? '') ?></td>
            <?php endif; ?>

            <td class="nowrap" style="width:15.4%"><?= $period ?></td>
            <td class="nowrap" style="width:10.7%"><?= $cost ?> р</td>
            <td class="nowrap" style="width:7.4%"><?= $status ?></td>
        </tr>
<?php
    endforeach;
}


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
                        <td class="text-nowrap col-2">Название</td>
                        <td class="text-nowrap col-4">Опции</td>
                        <td class="text-nowrap col-2">Цена</td>
                        <td class="text-nowrap col-4">Цена со скидкой</td>
                        <td class="text-nowrap">Действие</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tariffs as $tariff):
                        $tariff_options = json_decode($tariff['options'], true); ?>
                        <tr>
                            <td><?= $tariff['name'] ?></td>
                            <td>
                                <ul style="padding-left: 0;">
                                    <?php foreach ($tariff_options as $option): ?>
                                        <li><?= $option['text'] ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td><?= $tariff['price_old'] ?>₽</td>
                            <td><?= $tariff['price'] ?>₽</td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                    <button type="submit" class="btn btn btn-info modalEdit" data-type="tariff" data-id="<?= $tariff['id'] ?>">
                                        Редактировать
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
        Подключенные тарифы
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="connected-tariffs" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <td class="d-none"></td>
                        <td class="text-nowrap col-1">Дата подключения</td>
                        <td class="text-nowrap">ID</td>
                        <td class="text-nowrap">Имя</td>
                        <td class="text-nowrap">Наименование тарифа</td>
                        <td class="text-nowrap">Период размещения</td>
                        <td class="text-nowrap">Стоимость</td>
                        <td class="text-nowrap">Статус</td>
                    </tr>
                </thead>
                <tbody>
                    <?php renderTableRows($tariff_connections, 'tariff') ?>
                </tbody>
            </table>
            <div class="d-flex justify-content-center mb-5">
                <button type="button" id="show-all-tariffs" class="btn btn-primary">Показать все записи</button>
            </div>
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
                        <td class="text-nowrap col-4">Название</td>
                        <td class="text-nowrap col-8">Описание</td>
                        <td class="text-nowrap">Действие</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($promotions as $promotion): ?>
                        <tr>
                            <td><?= $promotion['title'] ?></td>
                            <td><?= $promotion['description'] ?></td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                    <button type="submit" class="btn btn btn-info modalEdit" data-price-id="<?= $promotion['id'] == 1 ? 'price_block1' : 'price_block2' ?>" data-type="promotion" data-id="<?= $promotion['id'] ?>">
                                        Редактировать
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
        Подключенные промо-размещения
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="connected-promo-options" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <td class="d-none"></td>
                        <td class="text-nowrap col-1">Дата подключения</td>
                        <td class="text-nowrap">ID</td>
                        <td class="text-nowrap">Имя</td>
                        <td class="text-nowrap">Блок размещения</td>
                        <td class="text-nowrap">Место</td>
                        <td class="text-nowrap">Период размещения</td>
                        <td class="text-nowrap">Стоимость</td>
                        <td class="text-nowrap">Статус</td>
                    </tr>
                </thead>
                <tbody>
                    <?php renderTableRows($paid_promos, 'promo') ?>
                </tbody>

            </table>
            <div class="d-flex justify-content-center mb-5">
                <button type="button" id="show-all-promo" class="btn btn-primary">Показать все записи</button>
            </div>
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
                        <td style="display:none;"></td>
                        <td class="text-nowrap col-4">Название</td>
                        <td class="text-nowrap col-8">Цена</td>
                        <td class="text-nowrap">Действие</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($options as $option): ?>
                        <tr>
                            <td style="display:none;"><?= $option['id'] ?></td>
                            <td><?= $option['name'] ?></td>
                            <td><?= $option['price'] ?>₽</td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                    <button type="submit" class="btn btn btn-info modalEdit" data-type="option" data-id="<?= $option['id'] ?>">
                                        Редактировать
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

    <p class="h1 text-center mt-5 mb-3">
        Подключенные опции
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="connected-options" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <td class="d-none"></td>
                        <td class="text-nowrap col-1">Дата подключения</td>
                        <td class="text-nowrap">ID</td>
                        <td class="text-nowrap">Имя</td>
                        <td class="text-nowrap">Наименование опции</td>
                        <td class="text-nowrap">Период размещения</td>
                        <td class="text-nowrap">Стоимость</td>
                        <td class="text-nowrap">Статус</td>
                    </tr>
                </thead>
                <tbody>
                    <?php renderTableRows($option_connections, 'option') ?>
                </tbody>
            </table>
            <div class="d-flex justify-content-center mb-5">
                <button type="button" id="show-all-options" class="btn btn-primary">Показать все записи</button>
            </div>
        </div>
    </div>
</div>
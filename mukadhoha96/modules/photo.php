<?php
session_start();
$userRole = $_SESSION['user_role'] ?? 'editor';

if (isset($_POST['action']) && $_POST['action'] === 'load_full_table') {
    $pro_photo_list = get_pro_photo_list(); // Получить все записи
    $today = new \DateTime();

    echo '
    <table id="photoTableFull" class="table table-striped" style="width:100%">
        <thead>
            <tr>
                <td style="text-align: left;">Дата запроса</td>
                <td>ID</td>
                <td>Имя</td>
                <td>Email</td>
                <td>Телефон</td>
                <td nowrap>Вид фотосъемки</td>
                <td>Съемка сотрудников</td>
                <td class="col-2">Статус</td>
                <td>Действие</td>
            </tr>
        </thead>
        <tbody>';

    foreach ($pro_photo_list as $pro_photo) {
        $start = new \DateTime($pro_photo['start_date']);
        $end = new \DateTime($pro_photo['end_date']);
        $styleNoWrap = 'white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px; min-width: 170px;';
        $status = '';
        $rowClass = '';

        if ($pro_photo['rejected'] == 1) {
            $status = '<span class="text-danger font-weight-bold">Отклонено</span>';
            $rowClass = 'rejected-highlight';
        } else {
            if (in_array($pro_photo['moderation'], [1, 2])) {
                $status = 'На рассмотрении';
            } else {
                if ($today >= $start && $today <= $end) {
                    $status = '<span class="font-weight-bold">Опубликован</span>';
                    $rowClass = 'yellow-highlight';
                } elseif ($today < $start) {
                    $status = 'Проведена';
                } else {
                    if ($end < $today) {
                        $status = 'В архиве';
                    } else {
                        $status = 'Проведена';
                    }
                }
            }
            if ($pro_photo['moderation'] == 2 && $pro_photo['rejected'] != 1) {
                $rowClass .= ($rowClass ? ' ' : '') . 'highlight';
            }
        }

        echo '
        <tr class="' . $rowClass . '">
            <td style="text-align: left;" data-order="' . htmlspecialchars($pro_photo['connected_at']) . '">' . htmlspecialchars(date('d.m.Y', strtotime($pro_photo['connected_at']))) . '</td>
            <td>' . htmlspecialchars_decode($pro_photo['specialist_id']) . '</td>
            <td style="' . $styleNoWrap . '">' .
            (!empty($pro_photo['company_id']) && $pro_photo['company_id'] > 0
                ? htmlspecialchars_decode($pro_photo['company_name'])
                : htmlspecialchars_decode($pro_photo['specialist_name'] . ' ' . $pro_photo['specialist_lastname'])
            ) .
            '</td>
            <td>' . htmlspecialchars_decode($pro_photo['specialist_email']) . '</td>
            <td>' . htmlspecialchars_decode($pro_photo['specialist_phone']) . '</td>
            <td>' .
            ($pro_photo['pro_photo_id'] == 4
                ? 'Съемка сотрудников'
                : htmlspecialchars_decode($pro_photo['title'])) . '
            </td>
            <td style="text-align: left;">' .
            (!empty($pro_photo['emp_quantity']) ? (int) $pro_photo['emp_quantity'] : '-') . '
            </td>
            <td>' . $status . '</td>
            <td>
                <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                    <button type="submit" class="btn btn-warning go-to-specialists"
                        data-specialist-id="' . htmlspecialchars($pro_photo['specialist_id']) . '"
                        data-id="' . htmlspecialchars($pro_photo['id']) . '"
                        data-moderation="' . $pro_photo['moderation'] . '"
                        data-table="' . htmlspecialchars($pro_photo['table_name']) . '"
                        data-tab="' . htmlspecialchars($pro_photo['tab']) . '">
                        Перейти
                    </button>
                </div>
            </td>
        </tr>';
    }

    echo '
        </tbody>
    </table>';
    exit;
}

$pro_photos = get_pro_photo();

$pro_photo_list = get_pro_photo_list(5);
?>
<div id="photoPage" class="wrapper">
    <?php if ($userRole == 'superadmin'): ?>
        <p class="h1 text-center">
            Тарифы
        </p>

        <div class="table-responsive">
            <div class="container-fluid">
                <table id="photoTable" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <td class="col-1">Заголовок</td>
                            <td class="col-1" nowrap>Цена</td>
                            <td class="col-1" nowrap>Цена со скидкой</td>
                            <td class="col-1" nowrap>Длительность</td>
                            <td class="col-2" nowrap>Кол-во фото</td>
                            <td class="col-5">Описание</td>
                            <td>Действие</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pro_photos as $pro_photo):
                            if ($pro_photo['id'] != '4'):
                        ?>
                                <tr>
                                    <td><?= htmlspecialchars_decode($pro_photo['title']) ?></td>
                                    <td><?= ($pro_photo['price_old']) ? $pro_photo['price_old'] : '-' ?></td>
                                    <td><?= htmlspecialchars_decode($pro_photo['price']) ?></td>
                                    <td><?= $pro_photo['time'] ?></td>
                                    <td><?= $pro_photo['number'] ?></td>
                                    <td><?= htmlspecialchars_decode($pro_photo['description']) ?></td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Basic mixed styles example">

                                            <button type="submit" class="btn modalAdd btn-info modalAdd"
                                                data-id="<?= $pro_photo['id'] ?>">
                                                Редактировать
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

        <p class="h1 mb-5 text-center">
            Съемка сотрудников
        </p>

        <div class="table-responsive">
            <div class="container-fluid">
                <table id="photoTable2" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <td class="col-1" nowrap>Заголовок</td>
                            <td class="col-1" nowrap>Цена</td>
                            <td class="col-1" nowrap>Цена со скидкой</td>
                            <td class="col-1" nowrap>Длительность</td>
                            <td class="col-2" nowrap>Кол-во фото</td>
                            <td class="col-5">Описание</td>
                            <td>Действие</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pro_photos as $pro_photo):
                            if ($pro_photo['id'] == '4'): ?>
                                <tr>
                                    <td>Съемка сотрудников</td>
                                    <td><?= ($pro_photo['price_old']) ? $pro_photo['price_old'] : '-' ?></td>
                                    <td><?= htmlspecialchars_decode($pro_photo['price']) ?></td>
                                    <td><?= $pro_photo['time'] ?></td>
                                    <td><?= $pro_photo['number'] ?></td>
                                    <td><?= htmlspecialchars_decode($pro_photo['description']) ?></td>
                                    <td>
                                        <div class="btn-group" role="group" aria-label="Basic mixed styles example">

                                            <button type="submit" class="btn modalAdd btn-info modalAdd"
                                                data-id="<?= $pro_photo['id'] ?>">
                                                Редактировать
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
    <?php endif; ?>

    <p class="h1 mb-5 <?= ($userRole == 'superadmin') ? 'mt-5' : '' ?> text-center">
        Запросы на фотосъемку
    </p>

    <div id="table-responsive" class="table-responsive container-fluid">
        <table id="photoTable3" class="table table-striped" style="width:100%">
            <thead>
                <tr>
                    <td style="text-align: left;">Дата запроса</td>
                    <td>ID</td>
                    <td>Наименование</td>
                    <td>Email</td>
                    <td>Телефон</td>
                    <td nowrap>Вид фотосъемки</td>
                    <td>Съемка сотрудников</td>
                    <td class="col-2">Статус</td>
                    <td>Действие</td>
                </tr>
            </thead>
            <tbody id="photoTableBody">
                <?php foreach ($pro_photo_list as $pro_photo):
                    $start = new \DateTime($pro_photo['start_date']);
                    $end = new \DateTime($pro_photo['end_date']);
                    $styleNoWrap = 'white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px; min-width: 170px;';

                    $currentClass = '';

                    if ($pro_photo['rejected'] == 1) {
                        $status = '<span class="text-danger font-weight-bold">Отклонено</span>';
                        $currentClass = 'rejected-highlight';
                    } else {
                        if (in_array($pro_photo['moderation'], [1, 2])) {
                            $status = 'На рассмотрении';
                        } else {
                            if ($today >= $start && $today <= $end) {
                                $status = '<span class="font-weight-bold">Опубликован</span>';
                                $rowClass = 'yellow-highlight';
                            } elseif ($today < $start) {
                                $status = 'Проведена';
                            } else {
                                if ($end < $today) {
                                    $status = 'В архиве';
                                } else {
                                    $status = 'Проведена';
                                }
                            }
                        }
                        // Если moderation == 2 и rejected != 1, добавляем класс highlight
                        if ($pro_photo['moderation'] == 2) {
                            $currentClass = isset($currentClass) ? $currentClass . ' highlight' : 'highlight';
                        }
                    }
                ?>
                    <tr
                        class="<?= $pro_photo['moderation'] == '2' || $pro_photo['moderation'] == 2 ? 'highlight' : '' ?>">
                        <td style="text-align: left;" data-order="<?= htmlspecialchars($pro_photo['connected_at']) ?>">
                            <?= htmlspecialchars(date('d.m.Y', strtotime($pro_photo['connected_at']))) ?>
                        </td>
                        <td><?= htmlspecialchars_decode($pro_photo['specialist_id']) ?></td>
                        <td style="<?= $styleNoWrap ?>">
                            <?= htmlspecialchars_decode(
                                !empty($pro_photo['company_id']) && $pro_photo['company_id'] > 0
                                    ? $pro_photo['company_name']
                                    : $pro_photo['specialist_name'] . ' ' . $pro_photo['specialist_lastname']
                            ) ?>
                        </td>
                        <td><?= htmlspecialchars_decode($pro_photo['specialist_email']) ?></td>
                        <td>+7 <?= htmlspecialchars_decode($pro_photo['specialist_phone']) ?></td>
                        <td>
                            <?= ($pro_photo['pro_photo_id'] == 4) ? 'Съемка сотрудников' : htmlspecialchars_decode($pro_photo['title']) ?>
                        </td>
                        <td><?= ($pro_photo['emp_quantity']) ? $pro_photo['emp_quantity'] : '-' ?></td>
                        <td><?= $status ?></td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                <button type="submit" class="btn btn-warning go-to-specialists"
                                    data-specialist-id="<?= $pro_photo['specialist_id'] ?>"
                                    data-moderation="<?= $pro_photo['moderation'] ?>" data-id="<?= $pro_photo['id'] ?>"
                                    data-table="<?= $pro_photo['table_name'] ?>" data-tab="<?= $pro_photo['tab'] ?>">
                                    Перейти
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="h1 text-center">
            <button id="loadFullTableButton" class="btn center btn-primary">Показать все записи</button>
        </p>
    </div>

    <script>
        const Helper = {
            dataTableLanguage: function() {
                return {
                    url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Russian.json"
                };
            },
            start: function() {
                ['#photoTableFull'].forEach(function(selector) {
                    if ($(selector).length) {
                        if ($.fn.DataTable.isDataTable($(selector))) {
                            $(selector).DataTable().destroy();
                        }

                        $(selector).DataTable({
                            language: Helper.dataTableLanguage(),
                            iDisplayLength: 50,
                            order: [
                                [0, 'desc']
                            ],
                            columnDefs: [
                                {
                                    targets: '_all', 
                                    type: 'string'
                                }
                            ]
                        });
                    }
                });
            }
        };

        document.getElementById('loadFullTableButton')?.addEventListener('click', function() {
            fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=load_full_table'
                })
                .then(response => {
                    if (!response.ok) throw new Error('Ошибка загрузки');
                    return response.text();
                })
                .then(html => {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    const newTable = tempDiv.querySelector('table');
                    if (newTable) {
                        const tableContainer = document.getElementById('table-responsive');
                        tableContainer.innerHTML = '';
                        tableContainer.appendChild(newTable);
                        Helper.start();
                    } else {
                        console.error('Таблица не найдена в ответе');
                    }
                })
                .catch(error => console.error('Ошибка:', error));
        });
    </script>
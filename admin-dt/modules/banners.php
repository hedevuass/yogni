<?php
if (isset($_POST['action']) && in_array($_POST['action'], ['load_full_table_journals', 'load_full_table_banners'])) {
    $journal_connection_list = get_connection_banner_list(); // Получить все записи
    $filter = ($_POST['action'] === 'load_full_table_journals') ? 'dt_journal_list' : 'dt_banners_list';
    $today = new \DateTime();
    $table_id = $filter === 'dt_journal_list' ? 'tableJournal' : 'tableBanners';

    echo '
    <table id="' . $table_id . '" class="table table-striped" style="width:100%">
        <thead>
            <tr>
                <td>Дата запроса</td>
                <td>ID</td>
                <td>Имя</td>
                <td>Текст запроса</td>
                <td>Период размещения</td>
                <td>Вид</td>
                <td class="col-2">Статус</td>
                <td>Действие</td>
            </tr>
        </thead>
        <tbody>';

    foreach ($journal_connection_list as $journal_list) {
        if ($journal_list['table_name'] === $filter) {
            $desc = json_decode($journal_list['description'], true);
            $adLine1 = isset($desc['ad_line1']) ? htmlspecialchars($desc['ad_line1']) : '';
            $adLine2 = isset($desc['ad_line2']) ? htmlspecialchars($desc['ad_line2']) : '';
            $adLine3 = ($filter === 'dt_journal_list' && isset($desc['ad_line3'])) ? htmlspecialchars($desc['ad_line3']) : '';

            $description_text = $filter === 'dt_journal_list'
                ? trim($adLine1 . ' ' . $adLine2 . ' ' . $adLine3)
                : trim($adLine1 . ' ' . $adLine2);

            $start = new \DateTime($journal_list['start_date']);
            $end = new \DateTime($journal_list['end_date']);
            $styleNoWrap = 'white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 250px; min-width: 250px;';

            $currentClass = '';

            if ($journal_list['rejected'] == 1) {
                $status = '<span class="text-danger font-weight-bold">Отклонено</span>';
                $currentClass = 'rejected-highlight';
            } else {
                if (in_array($journal_list['moderation'], [1, 2])) {
                    $status = 'На модерации';
                } else {
                    if ($today >= $start && $today <= $end) {
                        $status = '<span class="font-weight-bold">Опубликован</span>';
                        $currentClass = 'yellow-highlight';
                    } elseif ($today < $start) {
                        $status = 'Одобрен для публикации';
                    } else {
                        // Здесь добавим условие по дате конца
                        if ($end < $today) {
                            $status = 'В архиве';
                        } else {
                            $status = 'Одобрен для публикации';
                        }
                    }
                }
                // Если moderation == 2 и rejected != 1, добавляем класс highlight
                if ($journal_list['moderation'] == 2) {
                    $currentClass = isset($currentClass) ? $currentClass . ' highlight' : 'highlight';
                }
            }


            echo '
            <tr class="' . htmlspecialchars($currentClass ?? '') . '">
                <td>' . htmlspecialchars(date('d.m.Y', strtotime($journal_list['connected_at']))) . '</td>
                <td>' . htmlspecialchars($journal_list['specialist_id']) . '</td>
                <td style="max-width: 250px; min-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' .
                (!empty($journal_list['company_id']) && $journal_list['company_id'] > 0
                    ? htmlspecialchars($journal_list['company_name'])
                    : htmlspecialchars($journal_list['specialist_name'] . ' ' . $journal_list['specialist_lastname'])
                ) .
                '</td>
                <td style="' . $styleNoWrap . '">'
                . $adLine1 . ' ' . $adLine2 . ($filter === 'dt_journal_list' ? ' ' . $adLine3 : '') .
                '</td>
                <td class="col-2">
                    с ' . date('d.m.Y', strtotime($journal_list['start_date'])) . '
                    по ' . date('d.m.Y', strtotime($journal_list['end_date'])) . '
                </td>
                <td  style="' . $styleNoWrap . '" class="col-2">' . htmlspecialchars($journal_list['name']) . '</td>
                <td>' . $status . '</td>
                <td>
                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                        <button type="submit" class="btn btn-warning go-to-specialists" 
                            data-specialist-id="' . $journal_list['specialist_id'] . '"
                            data-id="' . $journal_list['id'] . '"
                            data-table="' . $journal_list['table_name'] . '"
                            data-moderation="' . $journal_list['moderation'] . '"
                            data-tab="' . $journal_list['tab'] . '">
                            Перейти
                        </button>
                    </div>
                </td>
            </tr>';
        }
    }

    echo '
        </tbody>
    </table>';
    exit;
}


$banners = get_banners();
$journal_banners = get_journal_banners();
$journal_connection_list = get_connection_banner_list(5);
?>
<div id="bannersPage" class="wrapper">

    <p class="h1 text-center">Тарифы платных баннеров</p>
    <div id="table-responsive-banners" class="table-responsive">
        <div class="container-fluid">
            <table class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <td>id</td>
                        <td class="text-nowrap col-11">Заголовок баннера</td>
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
                                    <button type="submit" class="btn btn-info modalEdit" data-price="1" data-type="banner"
                                        data-id="<?= $banner['id'] ?>">
                                        Редактировать
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php foreach ($journal_banners as $journal_banner): ?>
                        <?php if (in_array($journal_banner['id'], [1, 2])): ?>
                            <?php
                            $originalId = $journal_banner['id'] == 1
                                ? '6'
                                : '7';
                            $customTitle = $journal_banner['id'] == 1
                                ? 'Блок №1 в ленте журнала'
                                : 'Блок №2 в ленте журнала';
                            ?>
                            <tr>
                                <td><?= $originalId ?></td>
                                <td><?= $customTitle ?></td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                        <button type="submit" class="btn btn-info modalEdit" data-price="1"
                                            data-type="journal-banner" data-id="<?= $journal_banner['id'] ?>">
                                            Редактировать
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="h1 text-center mt-5">Баннеры главной страницы</p>
    <div id="table-responsive-banners" class="table-responsive">
        <div class="container-fluid">
            <table class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <td>id</td>
                        <td class="text-nowrap col-11">Заголовок баннера</td>
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
                                    <button type="submit" class="btn btn-info modalEdit" data-type="banner"
                                        data-id="<?= $banner['id'] ?>">
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
    <!-- Кнопка для загрузки дополнительных записей для баннеров главной страницы -->
    <div class="my-5"></div>

    <!-- Блок с запросами на размещение для dt_banners_list -->
    <p class="h1 text-center">Запросы на размещение (Главная страница)</p>
    <div id="table-responsive-banners-requests" class="table-responsive">
        <table id="banners" class="table table-striped" style="width:100%">
            <thead>
                <tr>
                    <td>Дата запроса</td>
                    <td>ID</td>
                    <td>Имя</td>
                    <td>Текст запроса</td>
                    <td>Период размещения</td>
                    <td>Вид</td>
                    <td class="col-2">Статус</td>
                    <td>Действие</td>
                </tr>
            </thead>
            <tbody>
                <?php
                // Текущая дата для сравнения
                $today = new \DateTime();
                foreach ($journal_connection_list as $journal_list):
                    if ($journal_list['table_name'] === 'dt_banners_list'):
                        // Декодируем описание из JSON
                        $desc = json_decode($journal_list['description'], true);
                        $adLine1 = isset($desc['ad_line1']) ? htmlspecialchars($desc['ad_line1']) : '';
                        $adLine2 = isset($desc['ad_line2']) ? htmlspecialchars($desc['ad_line2']) : '';
                        $description_text = trim($adLine1 . ' ' . $adLine2);

                        // Определяем статус по moderation и датам
                        $start = new \DateTime($journal_list['start_date']);
                        $end = new \DateTime($journal_list['end_date']);
                        $styleNoWrap = 'white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 250px; min-width: 250px;';

                        $currentClass = '';

                        if ($journal_list['rejected'] == 1) {
                            $status = '<span class="text-danger font-weight-bold">Отклонено</span>';
                            $currentClass = 'rejected-highlight';
                        } else {
                            if (in_array($journal_list['moderation'], [1, 2])) {
                                $status = 'На модерации';
                            } else {
                                if ($today >= $start && $today <= $end) {
                                    $status = '<span class="font-weight-bold">Опубликован</span>';
                                    $currentClass = 'yellow-highlight';
                                } elseif ($today < $start) {
                                    $status = 'Одобрен для публикации';
                                } else {
                                    // Здесь добавим условие по дате конца
                                    if ($end < $today) {
                                        $status = 'В архиве';
                                    } else {
                                        $status = 'Одобрен для публикации';
                                    }
                                }
                            }
                            // Если moderation == 2 и rejected != 1, добавляем класс highlight
                            if ($journal_list['moderation'] == 2) {
                                $currentClass = isset($currentClass) ? $currentClass . ' highlight' : 'highlight';
                            }
                        }


                        ?>
                        <tr class="<?= htmlspecialchars($currentClass ?? '') ?>">
                            <td><?= htmlspecialchars(date('d.m.Y', strtotime($journal_list['connected_at']))) ?></td>
                            <td><?= htmlspecialchars($journal_list['specialist_id']) ?></td>
                            <td
                                style="max-width: 250px; min-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php if (!empty($journal_list['company_id']) && $journal_list['company_id'] > 0): ?>
                                    <?= htmlspecialchars($journal_list['company_name']) ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($journal_list['specialist_name'] . ' ' . $journal_list['specialist_lastname']) ?>
                                <?php endif; ?>
                            </td>
                            <td style="<?= $styleNoWrap ?>">
                                <?= $adLine1 ?>
                                <?= $adLine2 ?>
                            </td>
                            <td class="col-2">
                                c <?= date('d.m.Y', strtotime($journal_list['start_date'])) ?>
                                по <?= date('d.m.Y', strtotime($journal_list['end_date'])) ?>
                            </td>
                            <td style="<?= $styleNoWrap ?>" class="col-2"><?= htmlspecialchars($journal_list['name']) ?></td>
                            <td><?= $status ?></td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                    <button type="submit" class="btn btn-warning go-to-specialists"
                                        data-specialist-id="<?= $journal_list['specialist_id'] ?>"
                                        data-id="<?= $journal_list['id'] ?>" data-table="<?= $journal_list['table_name'] ?>"
                                        data-moderation="<?= $journal_list['moderation'] ?>"
                                        data-tab="<?= $journal_list['tab'] ?>">
                                        Перейти
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php
                    endif;
                endforeach;
                ?>
            </tbody>
        </table>
    </div>
    <!-- Кнопка для загрузки дополнительных записей для dt_banners_list -->
    <p class="h1 text-center">
        <button id="loadFullBannersRequestsButton" class="btn center btn-primary">Показать все записи</button>
    </p>
    <div style="margin-top: 100px;"></div>

    <!-- Блок с баннерами журнала -->
    <p class="h1 text-center">Баннеры журнала</p>
    <div class="table-responsive">
        <div class="container-fluid">
            <table class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <td>id</td>
                        <td class="text-nowrap col-11">Заголовок баннера</td>
                        <td>Действие</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($journal_banners as $journal_banner): ?>
                        <?php if (in_array($journal_banner['id'], [1, 2])): ?>
                            <tr>
                                <td><?= $journal_banner['id'] ?></td>
                                <td><?= $journal_banner['title'] ?></td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                        <button type="submit" class="btn btn-info modalEdit" data-type="journal-banner"
                                            data-id="<?= $journal_banner['id'] ?>">
                                            Редактировать
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="my-5"></div>

    <!-- Блок с запросами на размещение для dt_journal_list -->
    <p class="h1 text-center">Запросы на размещение (Журнал)</p>
    <div id="table-responsive-journal-requests" class="table-responsive">
        <table id="journal_banners" class="table table-striped" style="width:100%">
            <thead>
                <tr>
                    <td>Дата запроса</td>
                    <td>ID</td>
                    <td>Имя</td>
                    <td>Текст запроса</td>
                    <td>Период размещения</td>
                    <td>Вид</td>
                    <td class="col-2">Статус</td>
                    <td>Действие</td>
                </tr>
            </thead>
            <tbody>
                <?php
                $today = new \DateTime();

                foreach ($journal_connection_list as $journal_list):
                    if ($journal_list['table_name'] === 'dt_journal_list'):

                        // Декодируем описание
                        $desc = json_decode($journal_list['description'], true);
                        $adLine1 = isset($desc['ad_line1']) ? htmlspecialchars($desc['ad_line1']) : '';
                        $adLine2 = isset($desc['ad_line2']) ? htmlspecialchars($desc['ad_line2']) : '';
                        $adLine3 = isset($desc['ad_line2']) ? htmlspecialchars($desc['ad_line3']) : '';
                        $description_text = trim($adLine1 . ' ' . $adLine2);

                        // Статус
                        $start = new \DateTime($journal_list['start_date']);
                        $end = new \DateTime($journal_list['end_date']);
                        $styleNoWrap = 'white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 250px; min-width: 250px;';

                        $currentClass = '';

                        if ($journal_list['rejected'] == 1) {
                            $status = '<span class="text-danger font-weight-bold">Отклонено</span>';
                            $currentClass = 'rejected-highlight';
                        } else {
                            if (in_array($journal_list['moderation'], [1, 2])) {
                                $status = 'На модерации';
                            } else {
                                if ($today >= $start && $today <= $end) {
                                    $status = '<span class="font-weight-bold">Опубликован</span>';
                                    $currentClass = 'yellow-highlight';
                                } elseif ($today < $start) {
                                    $status = 'Одобрен для публикации';
                                } else {
                                    // Здесь добавим условие по дате конца
                                    if ($end < $today) {
                                        $status = 'В архиве';
                                    } else {
                                        $status = 'Одобрен для публикации';
                                    }
                                }
                            }
                            // Если moderation == 2 и rejected != 1, добавляем класс highlight
                            if ($journal_list['moderation'] == 2) {
                                $currentClass = isset($currentClass) ? $currentClass . ' highlight' : 'highlight';
                            }
                        }


                        ?>
                        <tr class="<?= htmlspecialchars($currentClass ?? '') ?>">
                            <td><?= htmlspecialchars(date('d.m.Y', strtotime($journal_list['connected_at']))) ?></td>
                            <td><?= htmlspecialchars($journal_list['specialist_id']) ?></td>
                            <td
                                style="max-width: 250px; min-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php if (!empty($journal_list['company_id']) && $journal_list['company_id'] > 0): ?>
                                    <?= htmlspecialchars($journal_list['company_name']) ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($journal_list['specialist_name'] . ' ' . $journal_list['specialist_lastname']) ?>
                                <?php endif; ?>
                            </td>
                            <td style="<?= $styleNoWrap ?>">
                                <?= $adLine1 ?>
                                <?= $adLine2 ?>
                                <?= $adLine3 ?>
                            </td>
                            <td class="col-2">
                                с <?= date('d.m.Y', strtotime($journal_list['start_date'])) ?>
                                по <?= date('d.m.Y', strtotime($journal_list['end_date'])) ?>
                            </td>
                            <td style="<?= $styleNoWrap ?>" class="col-2"><?= htmlspecialchars($journal_list['name']) ?></td>
                            <td><?= $status ?></td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                    <button type="submit" class="btn btn-warning go-to-specialists"
                                        data-specialist-id="<?= $journal_list['specialist_id'] ?>"
                                        data-id="<?= $journal_list['id'] ?>" data-table="<?= $journal_list['table_name'] ?>"
                                        data-moderation="<?= $journal_list['moderation'] ?>" data-tab="banners">
                                        Перейти
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php
                    endif;
                endforeach;
                ?>
            </tbody>

        </table>
    </div>
    <!-- Кнопка для загрузки дополнительных записей для dt_journal_list -->
    <p class="h1 text-center">
        <button id="loadFullJournalTableButton" class="btn center btn-primary">Показать все записи</button>
    </p>

    <div style="margin-top: 100px;"></div>

    <p class="h1 text-center">
        Баннеры – ссылки на разделы
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <td>id</td>
                        <td class="text-nowrap col-11">Заголовок баннера</td>
                        <td>Действие</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($journal_banners as $journal_banner): ?>
                        <?php if (in_array($journal_banner['id'], [3, 4, 5])): ?>
                            <tr>
                                <td><?= $journal_banner['id'] ?></td>
                                <td><?= $journal_banner['title'] ?></td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                        <button type="submit" class="btn btn-info modalEdit" data-type="journal-banner"
                                            data-id="<?= $journal_banner['id'] ?>">
                                            Редактировать
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>

    const Helper = {
        dataTableLanguage: function () {
            return {
                url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Russian.json"
            };
        },
        start: function () {
            ['#tableJournal', '#tableBanners'].forEach(function (selector) {
                if ($(selector).length) {
                    if ($.fn.DataTable.isDataTable($(selector))) {
                        $(selector).DataTable().destroy();
                    }

                    $(selector).DataTable({
                        language: Helper.dataTableLanguage(),
                        iDisplayLength: 50,
                        order: [[0, "desc"]],
                    });
                }
            });
        }
    };

    // Загрузка dt_banners_list (главная страница)
    document.getElementById('loadFullBannersRequestsButton')?.addEventListener('click', function () {
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'action=load_full_table_banners'
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
                    const tableContainer = document.getElementById('table-responsive-banners-requests');
                    tableContainer.innerHTML = '';
                    tableContainer.appendChild(newTable);
                    Helper.start(); // 🟢 Инициализация DataTable
                } else {
                    console.error('Таблица не найдена в ответе');
                }
            })
            .catch(error => console.error('Ошибка:', error));
    });

    // Загрузка dt_journal_list (журнал)
    document.getElementById('loadFullJournalTableButton')?.addEventListener('click', function () {
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'action=load_full_table_journals'
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
                    const tableContainer = document.getElementById('table-responsive-journal-requests');
                    tableContainer.innerHTML = '';
                    tableContainer.appendChild(newTable);
                    Helper.start(); // 🟢 Инициализация DataTable
                } else {
                    console.error('Таблица не найдена в ответе');
                }
            })
            .catch(error => console.error('Ошибка:', error));
    });
</script>
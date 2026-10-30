<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'load_full_requests') {
    $requests = get_all_user_requests();
    echo render_requests_table($requests);
    exit;
}

$allRequests = get_all_user_requests();
$initialRequests = array_slice($allRequests, 0, 10);
session_start();
$userRole = $_SESSION['user_role'] ?? 'guest';

function render_requests_table(array $requests, ?int $limit = null): string
{
    if ($limit !== null) {
        $requests = array_slice($requests, 0, $limit);
    }

    $statuses = request_statuses();

    ob_start();
?>
    <div class="table-responsive" id="requestsTableContainer">
        <div class="container-fluid">

            <!-- Период + фильтр (JS переносит в панель DataTables, рядом с поиском) -->
            <div id="reqFilters" class="d-flex flex-wrap align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <label for="req-period" class="mb-0">Период:</label>
                    <input type="text" id="req-period" class="form-control form-control-sm w-auto"
                        placeholder="Выберите период" readonly>
                    <button type="button" id="req-period-clear" class="btn btn-sm btn-outline-secondary"
                        title="Сбросить период">&times;</button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label for="req-filter" class="mb-0">Фильтр:</label>
                    <select id="req-filter" class="form-select form-select-sm w-auto">
                        <option value="all">Все</option>
                        <?php foreach ($statuses as $key => [$label, $cls]): ?>
                            <option value="<?= $key ?>" class="<?= $cls ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Действия + «Выбрать все / Сбросить» (JS переносит над таблицу) -->
            <div id="reqActions" class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="me-2">Действие/статус:</span>
                    <button type="button" class="btn btn-sm btn-outline-info req-bulk" data-action="publish" disabled>Отправить</button>
                    <button type="button" class="btn btn-sm btn-outline-danger req-bulk" data-action="spam" disabled>Спам</button>
                </div>
                <span>
                    <a href="#" id="req-select-all">Выбрать все</a> /
                    <a href="#" id="req-reset">Сбросить</a>
                </span>
            </div>

            <table id="requests" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <td class="d-none"></td>
                        <td style="width: 1%;"></td>
                        <td class="col-1 text-nowrap">Дата заявки</td>
                        <td class="col-1">ID заявки</td>
                        <td class="col-2">Наименование</td>
                        <td class="col-1">ID</td>
                        <td class="col-2 text-nowrap">Имя в заявке</td>
                        <td style="white-space: nowrap; width: 1%;">Номер телефона</td>
                        <td class="col-3">Информация о занятии</td>
                        <td class="col-3 text-center">Статус</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req):
                        $statusKey = request_status_key($req);
                        [$statusLabel, $statusClass] = $statuses[$statusKey];
                        $isNew = $statusKey === 'new';
                        $ts = strtotime($req['created_at']);
                        $message = (string)$req['message'];
                        $flat = trim(preg_replace('/\s+/u', ' ', $message));
                        $isLong = mb_strlen($flat) > 100;

                        // данные специалиста нужны только для новых заявок (кнопка «Отправить»)
                        $specialist_name = '';
                        $specialist_email = '';
                        if ($isNew && $req['specialist_id']) {
                            $specialist = get_specialist($req['specialist_id']);
                            $specialistOther = json_decode($specialist['other'] ?? '', true) ?: [];
                            if (!empty($specialist['company'])) {
                                $specialist_name = $specialist['company'];
                            } else {
                                $specialist_name = trim(($specialist['name'] ?? '') . ' ' . ($specialist['lastname'] ?? ''));
                            }
                            $specialist_email = $specialistOther['email'] ?? '';
                        }
                    ?>
                        <tr data-status="<?= $statusKey ?>"
                            data-date="<?= date('Y-m-d', $ts) ?>"
                            data-moderation="<?= (int)$req['moderation'] ?>"
                            class="<?= $isNew ? 'highlight' : '' ?>">
                            <td class="d-none">
                                <?= $ts ?>
                            </td>
                            <td class="text-center">
                                <?php if ($isNew): ?>
                                    <input type="checkbox" class="form-check-input border-dark req-check"
                                        value="<?= (int)$req['id'] ?>"
                                        data-specialist-name="<?= htmlspecialchars($specialist_name) ?>"
                                        data-specialist-email="<?= htmlspecialchars($specialist_email) ?>">
                                <?php else: ?>
                                    <input type="checkbox" class="form-check-input invisible" disabled>
                                <?php endif; ?>
                            </td>
                            <td class="col-1 text-nowrap" style="white-space: nowrap;">
                                <?= date("d.m.Y", $ts) ?><br>
                                <?= date("H:i:s", $ts) ?>
                            </td>
                            <td class="col-1" style="white-space: nowrap;"><?= htmlspecialchars($req['id']) ?></td>
                            <td class="col-2">
                                <?= htmlspecialchars($req['specialist_name'] ?? '-') ?>
                                <?php if ($req['specialist_id']): ?>
                                    <br>
                                    <a href="specialists?id=<?= (int)$req['specialist_id'] ?>&tab=requests" target="_blank" class="small">Перейти</a>
                                <?php endif; ?>
                            </td>
                            <td class="col-1"><?= htmlspecialchars($req['specialist_id']) ?></td>
                            <td class="col-2 input-name" data-name="<?= htmlspecialchars($req['name']) ?>"><?= htmlspecialchars($req['name']) ?></td>
                            <td class="input-phone" data-phone="<?= htmlspecialchars($req['phone']) ?>" style="white-space: nowrap; width: 1%;">
                                <?php
                                if (filter_var($req['phone'], FILTER_VALIDATE_EMAIL)) {
                                    echo "<a href='mailto:" . htmlspecialchars($req['phone']) . "'>" . htmlspecialchars($req['phone']) . "</a>";
                                } else {
                                    $cleanPhone = preg_replace('/[^\d+]/', '', $req['phone']);
                                    echo "<a href='tel:" . htmlspecialchars($cleanPhone) . "'>" . htmlspecialchars($req['phone']) . "</a>";
                                }
                                ?>
                            </td>
                            <td class="col-3 input-message" data-message="<?= htmlspecialchars($message) ?>">
                                <?php if ($isLong): ?>
                                    <div class="req-msg-short"><?= htmlspecialchars(mb_strimwidth($flat, 0, 100, '…')) ?></div>
                                    <div class="req-msg-full d-none" style="white-space: pre-line;"><?= htmlspecialchars($message) ?></div>
                                    <a href="#" class="req-msg-toggle">Развернуть</a>
                                <?php else: ?>
                                    <div style="white-space: pre-line;"><?= htmlspecialchars($message) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="col-3 text-center">
                                <span class="req-status <?= $statusClass ?>"><?= $statusLabel ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php
    return ob_get_clean();
}
?>
<div id="requestsPage" class="wrapper"
    data-statuses="<?= htmlspecialchars(json_encode(request_statuses(), JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>">
    <p class="h1 text-center">Заявки</p>

    <?= render_requests_table($initialRequests) ?>

    <p class="h1 text-center">
        <button id="loadFullRequestsButton" class="btn center btn-primary">Показать все записи</button>
    </p>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>

<script>
    // ссылки "Развернуть" у длинных сообщений (вызывается и из request.js после подгрузки таблицы)
    function updateExpandLinks() {
        $('.cut-text-lk').each(function() {
            const $el = $(this);

            // удаляем старую ссылку, если была
            $el.siblings('.expand-message').remove();

            // если текст обрезан по line-clamp, scrollHeight будет больше clientHeight
            if ($el[0].scrollHeight > $el[0].clientHeight) {
                $('<a href="#" class="expand-message">Развернуть</a>')
                    .insertAfter($el);
            }
        });
    }

    $(document).ready(function() {
        updateExpandLinks();

        $(document).on('click', '.expand-message', function(e) {
            e.preventDefault();
            const $el = $(this).siblings('.cut-text-lk');

            $el.css('-webkit-line-clamp', 'unset');
            $(this).remove(); // скрываем ссылку после раскрытия
        });

        $(window).on('resize', function() {
            updateExpandLinks();
        });
    });
</script>
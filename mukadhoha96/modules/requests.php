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

    ob_start();
?>
    <div class="table-responsive" id="requestsTableContainer">
        <div class="container-fluid">
            <div class="table-controls mb-3">
                <label for="filterSelect">Фильтр:</label>
                <select id="filterSelect" class="p-1 bg-white rounded-3"
                    style="width: auto; display: inline-block;">
                    <option value="all">Все</option>
                    <option value="published">Отправленные</option>
                    <option value="spam">Спам</option>
                </select>
            </div>

            <table id="requests" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <td class="d-none"></td>
                        <td class="col-1 text-nowrap">Дата заявки</td>
                        <td class="col-1">ID заявки</td>
                        <td class="col-2">Наименование</td>
                        <td class="col-1">ID</td>
                        <td class="col-2 text-nowrap">Имя в заявке</td>
                        <td style="white-space: nowrap; width: 1%;">Номер телефона</td>
                        <td class="col-4">Сообщение</td>
                        <td class="col-2">Действие</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req):
                        if ($req['specialist_id']) {
                            $specialist = get_specialist($req['specialist_id']);
                            $specialistOther = json_decode($specialist['other'], true);
                        }
                        if ($specialist['company']) {
                            $specialist_name = $specialist['company'];
                        } else {
                            $specialist_name = $specialist['name'] . ' ' . $specialist['lastname'];
                        }
                        $specialist_email = $specialistOther['email'];
                    ?>
                        <tr data-moderation="<?= $req['moderation'] ?>" class="<?= $req['moderation'] == 3 ? 'highlight' : '' ?>">
                            <td class="d-none">
                                <?= strtotime($req['created_at']) ?>
                            </td>
                            <td class="col-1 text-nowrap" style="white-space: nowrap;">
                                <?= date("d.m.Y", strtotime($req['created_at'])) ?><br>
                                <?= date("H:i:s", strtotime($req['created_at'])) ?>
                            </td>
                            <td class="col-1" style="white-space: nowrap;"><?= htmlspecialchars($req['id']) ?></td>
                            <td class="col-2"><?= htmlspecialchars($req['specialist_name'] ?? '-') ?></td>
                            <td class="col-1"><?= htmlspecialchars($req['specialist_id']) ?></td>
                            <td class="col-2 input-name" data-name="<?= htmlspecialchars($req['name']) ?>"><?= htmlspecialchars($req['name']) ?></td>
                            <td class="input-phone" data-phone="<?php
                                                                if (filter_var($req['phone'], FILTER_VALIDATE_EMAIL)) {
                                                                    echo htmlspecialchars($req['phone']);
                                                                } else {
                                                                    $cleanPhone = preg_replace('/[^\d+]/', '', $req['phone']);
                                                                    echo htmlspecialchars($req['phone']);
                                                                }
                                                                ?>" style="white-space: nowrap; width: 1%;">
                                <?php
                                if (filter_var($req['phone'], FILTER_VALIDATE_EMAIL)) {
                                    echo "<a href='mailto:" . htmlspecialchars($req['phone']) . "'>" . htmlspecialchars($req['phone']) . "</a>";
                                } else {
                                    $cleanPhone = preg_replace('/[^\d+]/', '', $req['phone']);
                                    echo "<a href='tel:" . htmlspecialchars($cleanPhone) . "'>" . htmlspecialchars($req['phone']) . "</a>";
                                }
                                ?>
                            </td>
                            <td class="col-4 input-message" data-message="<?= htmlspecialchars($req['message']) ?>">
                                <div class="cut-text-lk" data-original="<?= htmlspecialchars($req['message']) ?>" style="white-space: pre-line; overflow:hidden;"><?= htmlspecialchars($req['message']) ?></div>
                            </td>
                            <td class="col-2">
                                <?php if ($req['moderation'] == 3): ?>
                                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                        <button type="submit"
                                            data-id="<?= $req['id'] ?>"
                                            data-specialist-name="<?= $specialist_name ?>"
                                            data-specialist-email="<?= $specialist_email ?>"
                                            class="btn btn-info publish">
                                            Отправить
                                        </button>

                                        <button type="submit"
                                            data-id="<?= $req['id'] ?>"
                                            class="btn btn-danger delete">
                                            Спам
                                        </button>
                                    </div>

                                <?php elseif ($req['moderation'] == 2): ?>
                                    <span class="text-danger">Спам</span>

                                <?php elseif ($req['moderation'] == 1): ?>
                                    <span class="text-success">Отправлено</span>
                                <?php endif; ?>
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
<div id="requestsPage" class="wrapper">
    <p class="h1 text-center">Заявки</p>

    <?= render_requests_table($initialRequests) ?>

    <p class="h1 text-center">
        <button id="loadFullRequestsButton" class="btn center btn-primary">Показать все записи</button>
    </p>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
    const Helper = {
        dataTableLanguage: function() {
            return {
                url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Russian.json"
            };
        }
    };

    function initRequestsDataTable(pageLength = 10) {
        if ($.fn.DataTable.isDataTable('#requests')) {
            $('#requests').DataTable().destroy();
        }
        $('#requests').DataTable({
            language: Helper.dataTableLanguage(),
            iDisplayLength: pageLength,
            order: [
                [0, "desc"]
            ],
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initRequestsDataTable(10);
    });

    document.getElementById('loadFullRequestsButton')?.addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Загружаю...';

        fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=load_full_requests'
            })
            .then(response => {
                if (!response.ok) throw new Error('Ошибка загрузки');
                return response.text();
            })
            .then(html => {
                const temp = document.createElement('div');
                temp.innerHTML = html.trim();
                const newContainer = temp.querySelector('#requestsTableContainer') || temp.querySelector('table') && (function() {
                    const wrapper = document.createElement('div');
                    wrapper.id = 'requestsTableContainer';
                    wrapper.appendChild(temp.querySelector('table'));
                    return wrapper;
                })();

                if (newContainer) {
                    const old = document.getElementById('requestsTableContainer');
                    if (old) old.parentNode.replaceChild(newContainer, old);
                    else document.getElementById('requestsPage').insertBefore(newContainer, btn.parentNode);

                    // Реинициализация DataTable
                    initRequestsDataTable(50);

                    // Добавляем ссылки "Развернуть" после подгрузки
                    updateExpandLinks();

                    btn.style.display = 'none';

                    updateExpandLinks();
                } else {
                    console.error('Таблица с заявками не найдена в ответе сервера.');
                    btn.disabled = false;
                    btn.textContent = 'Показать все записи';
                }
            })
            .catch(err => {
                console.error('Ошибка:', err);
                btn.disabled = false;
                btn.textContent = 'Показать все записи';
            });
    });


    /* document.addEventListener('click', function(e) {
        if (e.target.classList.contains('expand-message')) {
            e.preventDefault();
            const shortDiv = e.target.closest('.short-message');
            const fullDiv = shortDiv.nextElementSibling;
            shortDiv.style.display = 'none';
            fullDiv.style.display = 'block';
        }
    }); */

    /* function clampLines($el, lines) {
        const el = $el[0];
        if (!el) return;

        const fullText = $el.data('original') || '';
        if (!fullText) return;

        $el.text(fullText);
        const computed = getComputedStyle(el);
        const lineHeight = parseFloat(computed.lineHeight);
        if (!lineHeight) return;

        const maxHeight = lineHeight * lines;

        if (el.scrollHeight <= maxHeight) return;

        const suffix = '…'; // только троеточие
        let text = fullText;

        // сокращаем текст, пока не влезет в 2 строки
        while (el.scrollHeight > maxHeight && text.length > 0) {
            text = text.slice(0, -1);
            $el.text(text + suffix);
        }

        // после обрезки добавляем ссылку на новой строке
        $el.html(text.trim() + '…<br><a href="#" class="expand-message">Развернуть</a>');
    }

    function loadAndClamp(selector = '.cut-text-lk', lines = 2) {
        $(selector).each(function() {
            const $el = $(this);
            if (!$el.data('original')) $el.data('original', $el.text().trim());
            $el.text($el.data('original'));
            clampLines($el, lines);
        });
    }

    $(document).ready(function() {
        loadAndClamp('.cut-text-lk', 2);

        $(window).on('resize', function() {
            loadAndClamp('.cut-text-lk', 2);
        });

        $(document).on('click', '.expand-message', function(e) {
            e.preventDefault();
            const $el = $(this).closest('.cut-text-lk');
            $el.text($el.data('original'));
        });
    }); */

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
<?php
$settings = include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
$login = $settings['login'];
$hashedPassword = $settings['password'];

$documents = get_all_documents_o_proekte();
$socials = get_all_social();

$emails = [];
$file = $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/note_notification.php';
$contents = file_get_contents($file);

// Парсим массив из файла
if (preg_match('/\$recipient_by_type\s*=\s*(\[[^\;]*\]);/s', $contents, $matches)) {
    eval('$emails = ' . $matches[1] . ';');
}


$pages_meta = [
    9 => 'Главная страница',
    10 => 'Избранное',
    17 => 'О проекте'
];

$admin = get_admin_user(1);
$adminUsers = get_admin_users();

$admins   = array_filter($adminUsers, fn($u) => $u['role'] === 'admin');
$editors  = array_filter($adminUsers, fn($u) => $u['role'] === 'editor');

$adminCount  = count($admins) + 1; // +1 для новой строки
$editorCount = count($editors) + 1;
?>
<style>
    tbody.admin-block tr>* {
        --bs-table-bg-type: var(--bs-table-striped-bg) !important;
    }

    /* Убираем полосатость у редакторов */
    tbody.editor-block tr>* {
        --bs-table-bg-type: transparent !important;
        --bs-table-color-type: inherit !important;
        background-color: transparent !important;
    }
</style>
<div id="mainPage" class="wrapper">
    <p class="h1 text-center">Данные для входа</p>
    <form id="loginForm" method="post" action="actions/settings/update_main_user.php">
        <div class="table-responsive">
            <div class="container-fluid">
                <table class="table table-striped align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <td>Логин</td>
                            <td>Пароль</td>
                            <td>Подтверждение старого пароля</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!-- ID для редактирования -->
                            <input type="hidden" name="users[<?= $admin['id'] ?>][id]" value="<?= $admin['id'] ?>">

                            <!-- Логин -->
                            <td>
                                <input
                                    type="text"
                                    name="users[<?= $admin['id'] ?>][login]"
                                    value="<?= htmlspecialchars($admin['username']) ?>"
                                    class="form-control"
                                    required>
                            </td>

                            <!-- Текущий пароль (звёздочки, возможность показать) -->
                            <td>
                                <div class="input-group">
                                    <input
                                        type="password"
                                        name="users[<?= $admin['id'] ?>][password]"
                                        value="<?= htmlspecialchars($admin['password_hash']) ?>"
                                        class="form-control password-input"
                                        required>
                                    <button type="button" class="btn btn-outline-secondary toggle-password">
                                        Показать
                                    </button>
                                </div>
                            </td>

                            <!-- Подтверждение старого пароля -->
                            <td>
                                <div class="input-group">
                                    <input
                                        type="password"
                                        name="users[<?= $admin['id'] ?>][old_password]"
                                        placeholder="Введите старый пароль"
                                        class="form-control password-input"
                                        required>
                                    <button type="button" class="btn btn-outline-secondary toggle-password">
                                        Показать
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center" id="alert-container-admin"></div>
        <div class="d-flex justify-content-center mb-5">
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </form>
    <p class="h1 text-center pt-5">Настройка доступа в админпанель</p>
    <form method="post" action="actions/settings/update_settings.php">
        <div class="table-responsive">
            <div class="container-fluid">
                <table class="table table-striped align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <td style="width: 33%;">Тип доступа</td>
                            <td>Логин</td>
                            <td>Пароль</td>
                        </tr>
                    </thead>

                    <!-- ========== Администраторы с цветом ========== -->
                    <tbody class="admin-block">
                        <?php foreach (array_values($admins) as $i => $u): ?>
                            <tr>
                                <?php if ($i === 0): ?>
                                    <td class="align-middle" rowspan="<?= $adminCount ?>">Администратор</td>
                                <?php endif ?>
                                <input type="hidden" name="users[<?= $u['id'] ?>][id]" value="<?= $u['id'] ?>">
                                <input type="hidden" name="users[<?= $u['id'] ?>][role]" value="admin">
                                <td>
                                    <input
                                        type="text"
                                        name="users[<?= $u['id'] ?>][login]"
                                        value="<?= htmlspecialchars($u['username']) ?>"
                                        class="form-control">
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input
                                            type="password"
                                            name="users[<?= $u['id'] ?>][password]"
                                            value="<?= htmlspecialchars($u['password_hash']) ?>"
                                            class="form-control">
                                        <button type="button" class="btn btn-outline-secondary toggle-password"
                                            data-target="[name='users[<?= $u['id'] ?>][password]']">
                                            Показать
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Новая строка для администратора -->
                        <tr>
                            <?php if (count($admins) === 0): ?>
                                <td class="align-middle" rowspan="<?= $adminCount ?>">Администратор</td>
                            <?php endif; ?>
                            <td>
                                <input type="hidden" name="new_admin[role]" value="admin">
                                <input type="text" name="new_admin[login]" class="form-control" value="">
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="password" name="new_admin[password]" class="form-control">
                                    <button type="button" class="btn btn-outline-secondary toggle-password"
                                        data-target="[name='new_admin[password]']">
                                        Показать
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>

                    <!-- ========== Редакторы без окраски ========== -->
                    <tbody class="editor-block">
                        <?php foreach (array_values($editors) as $i => $u): ?>
                            <tr>
                                <?php if ($i === 0): ?>
                                    <td class="align-middle" rowspan="<?= $editorCount ?>">Редактор</td>
                                <?php endif ?>
                                <input type="hidden" name="users[<?= $u['id'] ?>][id]" value="<?= $u['id'] ?>">
                                <input type="hidden" name="users[<?= $u['id'] ?>][role]" value="editor">
                                <td>
                                    <input
                                        type="text"
                                        name="users[<?= $u['id'] ?>][login]"
                                        value="<?= htmlspecialchars($u['username']) ?>"
                                        class="form-control">
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input
                                            type="password"
                                            name="users[<?= $u['id'] ?>][password]"
                                            value="<?= htmlspecialchars($u['password_hash']) ?>"
                                            class="form-control">
                                        <button type="button" class="btn btn-outline-secondary toggle-password"
                                            data-target="[name='users[<?= $u['id'] ?>][password]']">
                                            Показать
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Новая строка для редактора -->
                        <tr>
                            <?php if (count($editors) === 0): ?>
                                <td class="align-middle" rowspan="<?= $editorCount ?>">Редактор</td>
                            <?php endif; ?>
                            <td>
                                <input type="hidden" name="new_editor[role]" value="editor">
                                <input type="text" name="new_editor[login]" class="form-control" value="">
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="password" name="new_editor[password]" class="form-control">
                                    <button type="button" class="btn btn-outline-secondary toggle-password"
                                        data-target="[name='new_editor[password]']">
                                        Показать
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center" id="alert-container"></div>
        <div class="d-flex justify-content-center mb-5">
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </form>

    <div class="wrapper">
        <p class="h1 text-center pt-5">Настройка уведомлений</p>
    </div>

    <form method="post" action="/actions/mail/edit_notification_emails.php">
        <div class="table-responsive">
            <div class="container-fluid">
                <table class="table table-striped align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <td class="col-1">ID</td>
                            <td class="col-5">Тип уведомления</td>
                            <td>Электронная почта для получение уведомления</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $types = [
                            'post' => 'Новый пост блога, требующий модерации',
                            'photo' => 'Запрос на проведение фотосъемки',
                            'banner' => 'Запрос на размещение баннеров (все виды)'
                        ];
                        $i = 1;
                        foreach ($types as $key => $label):
                            $emailsList = $emails[$key] ?? [];
                            if (!is_array($emailsList)) {
                                // Если в файле старый формат — преобразуем в массив
                                $emailsList = [$emailsList];
                            }
                        ?>
                            <tr>
                                <td><?= $i ?></td>
                                <td><?= $label ?></td>
                                <td>
                                    <?php foreach ($emailsList as $email): ?>
                                        <input type="email" name="recipients[<?= $key ?>][]"
                                            value="<?= htmlspecialchars($email) ?>" class="form-control mb-1">
                                    <?php endforeach; ?>
                                    <!-- Пустое поле для нового email -->
                                    <input type="email" name="recipients[<?= $key ?>][]" value="" class="form-control"
                                        placeholder="Добавить новый email">
                                </td>
                            </tr>
                        <?php $i++;
                        endforeach; ?>
                    </tbody>
                </table>

            </div>
        </div>

        <div class="d-flex justify-content-center mb-5">
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </form>

    <?php
    $forbidden_words = get_forbidden_words();
    ?>

    <div class="wrapper">
        <p class="h1 text-center pt-5">
            Модерация контента
        </p>
    </div>
    <div class="table-responsive mb-5">
        <div class="container-fluid">
            <table id="mainTableEx" class="table table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <td class="col-12">Список запрещенных слов (например: запрещенное, слово)</td>
                        <td>Действие</td>
                    </tr>
                </thead>
                <tbody>
                    <td>
                        <textarea id="forbidden-words" name="forbidden_words"
                            class="form-control"><?php echo htmlspecialchars(get_forbidden_words()); ?></textarea>
                    </td>
                    <td class="text-end">
                        <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                            <button type="submit" class="btn btn-info saveForbiddentWords">
                                Сохранить
                            </button>
                        </div>
                    </td>
                </tbody>
            </table>
        </div>
    </div>

    <div class="wrapper">
        <p class="h1 text-center pt-5">
            Ссылки на социальные сети
        </p>
    </div>
    <form method="post" action="actions/social/edit_social.php">
        <div class="table-responsive">
            <div class="container-fluid">
                <table id="mainTableEx" class="table table-striped align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <td class="col-1">ID</td>
                            <td class="col-5">Название</td>
                            <td>Ссылка</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($socials as $social): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($social['id']); ?>
                                    <input type="hidden" name="socials[<?= $social['id'] ?>][id]"
                                        value="<?= htmlspecialchars($social['id']); ?>">
                                </td>
                                <td>
                                    <?= $social['alt'] == 'Yandex' ? 'Яндекс.Дзен' : $social['alt'] ?>
                                </td>
                                <td>
                                    <input type="text" name="socials[<?= $social['id'] ?>][link]"
                                        value="<?= htmlspecialchars($social['link']); ?>" class="form-control">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-center mb-5">
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </form>

    <p class="h1 text-center pt-5">
        Страница "О проекте"
        <button type="submit" class="btn modalAdd btn-success modalAdd">
            Добавить
        </button>
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="mainTableEx" class="table table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <td>id</td>
                        <td class="col-4">Заголовок</td>
                        <td>Описание</td>
                        <td class="col-1">Действие</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $document): ?>
                        <tr>
                            <td><?= $document['id'] ?></td>
                            <td><?= $document['title'] ?></td>
                            <td class="text-truncate" style="max-width: 200px; max-height: 100px;">
                                <?= strip_tags(htmlspecialchars_decode($document['content'])) ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Basic mixed styles example">

                                    <button type="submit" class="btn modalAdd btn-info modalAdd"
                                        data-id="<?= $document['id'] ?>">
                                        Редактировать
                                    </button>
                                    <button type="submit" class="btn btn-danger delete"
                                        data-id="<?= $document['id'] ?>">Удалить</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="wrapper mt-5">
        <p class="h1 text-center pt-5">
            Title, Description, ключевые слова страниц
        </p>
    </div>

    <form method="post" action="actions/pages/edit_page.php">
        <div class="table-responsive">
            <div class="container-fluid">
                <table class="table table-striped align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <td>Название страницы</td>
                            <td>Title</td>
                            <td>Description</td>
                            <td>Ключевые слова</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages_meta as $id => $name): ?>
                            <?php $meta = get_page($id); ?>
                            <tr>
                                <input type="hidden" name="pages[<?= $id ?>][id]" value="<?= $meta['id']; ?>">
                                <td><?= $name ?></td>
                                <td>
                                    <input type="text" name="pages[<?= $id ?>][title]"
                                        value="<?= $meta['title'] ? htmlspecialchars_decode($meta['title']) : ''; ?>"
                                        class="form-control">
                                </td>
                                <td>
                                    <input type="text" name="pages[<?= $id ?>][description]"
                                        value="<?= $meta['description'] ? htmlspecialchars_decode($meta['description']) : ''; ?>"
                                        class="form-control">
                                </td>
                                <td>
                                    <input type="text" name="pages[<?= $id ?>][keywords]"
                                        value="<?= $meta['keywords'] ? htmlspecialchars_decode($meta['keywords']) : ''; ?>"
                                        class="form-control">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-center">
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
    </form>


    <div class="wrapper mb-5 mt-5">
        <p class="h1 text-center pt-5">
            Фавикон
        </p>
    </div>

    <form id="favicon-form" class="mb-5" action="/dicfacducfer1113/actions/upload_favicon.php" method="post"
        enctype="multipart/form-data">
        <div class="table-responsive">
            <div class="container-fluid">
                <div class="d-flex justify-content-center align-items-center">
                    <div class="col-1 position-relative me-3 image-container"
                        onclick="document.getElementById('input-favicon').click();">
                        <img src="/uploads/fav-icon/<?php echo file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/fav-icon/favicon.svg') ? 'favicon.svg' : 'favicon.png'; ?>"
                            class="avatar" alt="icon" id="favicon-preview">
                        <span class="upload-avatar">Изменить</span>
                    </div>

                    <!-- Кнопка удалить -->
                    <button type="button" class="btn btn-danger" id="delete-favicon-btn">Удалить</button>

                    <input type="file" style="display: none" name="favicon" id="input-favicon"
                        accept="image/png,image/x-icon,image/svg+xml">
                </div>
            </div>
        </div>
    </form>

</div>

<script>
    document.querySelectorAll('#loginForm .toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.closest('.input-group').querySelector('.password-input');
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = 'Скрыть';
            } else {
                input.type = 'password';
                btn.textContent = 'Показать';
            }
        });
    });

    document.getElementById('input-favicon').addEventListener('change', function() {
        if (!this.files[0]) return;
        document.getElementById('favicon-preview').src = URL.createObjectURL(this.files[0]);
        document.getElementById('favicon-form').submit();
    });

    // удаление
    document.getElementById('delete-favicon-btn').addEventListener('click', function() {
        if (!confirm('Удалить текущую иконку?')) return;
        window.location.href = '/dicfacducfer1113/actions/delete_favicon.php';
    });

    document.querySelectorAll('.js-delete-user').forEach(btn => {
        btn.addEventListener('click', e => {
            if (!confirm('Удалить пользователя?')) return;

            const id = btn.dataset.id;
            fetch('actions/settings/update_settings.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        delete_user_id: id
                    })
                })
                .then(resp => resp.json())
                .then(json => {
                    if (json.status === 'success') {
                        // просто перезагрузим страницу, чтобы обновить список
                        location.reload();
                    } else {
                        alert('Ошибка: ' + json.message);
                    }
                })
                .catch(() => alert('Сетевая ошибка.'));
        });
    });
</script>
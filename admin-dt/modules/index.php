<?php
$settings = include $_SERVER['DOCUMENT_ROOT'] . '/settings.php';
$login = $settings['login'];
$hashedPassword = $settings['password'];

$documents = get_all_documents_o_proekte();
$socials = get_all_social();
?>

<div id="mainPage" class="wrapper">
    <p class="h1 text-center">Данные для входа</p>
    <form method="post" action="actions/settings/update_settings.php">
        <div class="table-responsive">
            <div class="container-fluid">
                <table id="adminData" class="table table-striped align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <td>Логин</td>
                            <td>Пароль</td>
                            <td>Подтверждение старого пароля</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="text" name="login"
                                    value="<?php echo htmlspecialchars($login); ?>"
                                    placeholder="Введите логин"
                                    class="form-control">
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="password" id="newPassword" name="password"
                                        value="<?php echo htmlspecialchars($hashedPassword); ?>"
                                        placeholder="Введите новый пароль"
                                        class="form-control">
                                    <button type="button" class="btn btn-outline-secondary toggle-password"
                                        data-target="#newPassword">
                                        Показать
                                    </button>
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="password" id="oldPassword" name="old_password"
                                        placeholder="Введите старый пароль"
                                        class="form-control">
                                    <button type="button" class="btn btn-outline-secondary toggle-password"
                                        data-target="#oldPassword">
                                        Показать
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-center mb-5">
            <button type="submit" class="btn btn-primary btn-lg">Сохранить</button>
        </div>
    </form>


    <div class="wrapper">
        <p class="h1 text-center">
            Ссылки на соц. сети
        </p>
    </div>
    <form method="post" action="actions/social/edit_social.php">
        <div class="table-responsive">
            <div class="container-fluid">
                <table id="mainTableEx" class="table table-striped align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <td>ID</td>
                            <td>alt</td>
                            <td>Ссылка</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($socials as $social): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($social['id']); ?>
                                    <input type="hidden" name="socials[<?= $social['id'] ?>][id]" value="<?= htmlspecialchars($social['id']); ?>">
                                </td>
                                <td><?= $social['alt'] ?></td>
                                <td>
                                    <input type="text" name="socials[<?= $social['id'] ?>][link]"
                                        value="<?= htmlspecialchars($social['link']); ?>"
                                        class="form-control">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-center mb-5">
            <button type="submit" class="btn btn-primary btn-lg">Сохранить</button>
        </div>
    </form>



    <p class="h1 text-center">
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
                        <td>Контент</td>
                        <td class="col-1">Действие</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $document): ?>
                        <tr>
                            <td><?= $document['id'] ?></td>
                            <td><?= $document['title'] ?></td>
                            <td class="text-truncate" style="max-width: 200px; max-height: 100px;"><?= strip_tags(htmlspecialchars_decode($document['content'])) ?></td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Basic mixed styles example">

                                    <button type="submit" class="btn modalAdd btn-info modalAdd"
                                        data-id="<?= $document['id'] ?>">
                                        Изменить
                                    </button>
                                    <button type="submit" class="btn btn-danger delete" data-id="<?= $document['id'] ?>">Удалить</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
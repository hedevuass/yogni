<?php
$journals = get_all_posts();
$page_ids = [11, 15, 2, 4];
$page_metas = [];

foreach ($page_ids as $id) {
    $page_metas[$id] = get_page($id);
}

session_start();
$userRole = $_SESSION['user_role'] ?? 'guest';
?>
<div id="journalPage" class="wrapper">
    <?php if ($userRole == 'superadmin'): ?>
        <div class="wrapper">
            <p class="h1 text-center">
                Title, Description, ключевые слова страниц
            </p>
        </div>

        <form method="post" action="actions/pages/edit_page.php">
            <div class="table-responsive">
                <div class="container-fluid">
                    <table id="" class="table table-striped align-middle" style="width:100%">
                        <thead>
                            <tr>
                                <td>Название страницы</td>
                                <td>Title</td>
                                <td>Description</td>
                                <td>Ключевые слова</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($page_metas as $id => $page_meta): ?>
                                <tr>
                                    <!-- теперь каждая группа полей лежит в pages[ID] -->
                                    <td><?= $page_meta['text_name']; ?></td>
                                    <td>
                                        <input type="text" name="pages[<?= $id ?>][title]"
                                            value="<?= htmlspecialchars_decode(strip_tags($page_meta['title'] ?? '')); ?>"
                                            class="form-control">
                                    </td>
                                    <td>
                                        <input type="text" name="pages[<?= $id ?>][description]"
                                            value="<?= htmlspecialchars_decode(strip_tags($page_meta['description'] ?? '')); ?>"
                                            class="form-control">
                                    </td>
                                    <td>
                                        <input type="text" name="pages[<?= $id ?>][keywords]"
                                            value="<?= htmlspecialchars_decode(strip_tags($page_meta['keywords'] ?? '')); ?>"
                                            class="form-control">
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

        <hr class="mb-5">
        <div class=" mb-5">
            <div class="table-responsive">
                <div class="container-fluid">
                    <table class="table table-striped" style="width:100%">
                        <tbody>
                            <tr>
                                <td class="col-11">Меню по темам</td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                        <button type="button" class="btn btn-info modalMenuAdd">
                                            Редактировать
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Меню по хештегам</td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                        <button type="button" class="btn btn-info modalTagsMenuAdd">
                                            Редактировать
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <hr>

    <?php endif; ?>

    <p class="h1 text-center">
        Журнал
        <button type="button" class="btn btn-success modalAdd">
            Добавить пост
        </button>
    </p>
    <div class="table-responsive">
        <div class="container-fluid">
            <table id="journals" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <td class="d-none"></td>
                        <td>ID</td>
                        <td class="col-1 text-nowrap">Заголовок</td>
                        <td class="col-1">Тип</td>
                        <td class="col-1">Ссылка</td>
                        <td class="col-6">Описание</td>
                        <td class="col-1">Дата</td>
                        <td class="col-1">Статус</td>
                        <td>Действие</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($journals as $journal):
                        if ($journal['post_type'] === 'articles' || $journal['post_type'] === 'news' /* || $journal['post_type'] === 'blogs' */): ?>
                            <tr>
                                <td class="d-none">
                                    <?= strtotime($journal['date']) ?>
                                </td>
                                <td><?= ($journal['id']) ?></td>
                                <td><?= strip_tags(html_entity_decode($journal['title'])) ?></td>
                                <td><?= $journal['post_type'] === 'news' ? 'Новость' : 'Статья' ?></td>
                                <td><?= ($journal['alias']) ? $journal['alias'] : "-" ?></td>
                                <td><?= truncate_text(strip_tags(html_entity_decode($journal['content'])), 270) ?></td>
                                <td><?= date("d.m.Y H:i:s", strtotime($journal['date'])) ?></td>
                                <td><?= $journal['moderation'] == '1' ? 'На модерации' : 'Опубликован' ?></td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                        <button type="submit" class="btn btn-info modalAdd" data-id="<?= $journal['id'] ?>">
                                            Редактировать
                                        </button>
                                        <?php if ($userRole == 'superadmin'): ?>
                                            <button type="submit" class="btn btn-danger delete"
                                                data-id="<?= $journal['id'] ?>">Удалить</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                    <?php endif;
                    endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
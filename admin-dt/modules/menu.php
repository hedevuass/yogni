<?php
$menu_items = get_menu_items();
?>
<div id="menuPage" class="wrapper">
    <p class="h1 text-center">
        Меню
        <button type="button" class="btn btn-success modalAdd">
            Добавить раздел
        </button>
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="menu" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <td>Изображение</td>
                        <td>ID</td>
                        <td>Иконка</td>
                        <td>Заголовок</td>
                        <td>Алиас</td>
                        <td>Родитель</td>
                        <td>Действие</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menu_items as $menu_item): ?>
                        <tr>
                            <td>
                                <img src="../assets/img/<?= $menu_item['img'] ?? '' ?>"
                                    class="table-avatar"
                                    alt="menu-<?= $menu_item['id'] ?? '' ?>">
                            </td>
                            <td><?= $menu_item['id'] ?></td>
                            <td>
                                <img src="../assets/img/<?= $menu_item['icon'] ?? '' ?>"
                                    class="table-icon"
                                    alt="">
                            </td>
                            <td><?= $menu_item['title'] ?></td>
                            <td><?= ($menu_item['slug']) ? $menu_item['slug'] : "-" ?></td>
                            <td><?= ($menu_item['parent_title']) ? $menu_item['parent_title'] : "-" ?></td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                    <button type="submit"
                                        class="btn modalAdd btn-info"
                                        data-title="<?= $menu_item['title'] ?>"
                                        data-content="<?= $menu_item['content'] ?? '' ?>"
                                        data-id="<?= $menu_item['id'] ?>">
                                        Изменить
                                    </button>
                                    <button type="submit"
                                        class="btn btn-danger delete"
                                        data-id="<?= $menu_item['id'] ?>">
                                        Удалить
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>
</div>
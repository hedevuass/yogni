<?php
$posts = get_all_posts();
session_start();
$userRole = $_SESSION['user_role'] ?? 'guest';
?>
<div id="postsPage" class="wrapper">
    <p class="h1 text-center">Посты</p>

    <div class="table-responsive">
        <div class="container-fluid">

            <div class="table-controls mb-3">
                <label for="filterSelect">Фильтр:</label>
                <select id="filterSelect" class="p-1 bg-white rounded-3"
                    style="width: auto; display: inline-block;">
                    <option value="all">Все</option>
                    <option value="published">Опубликованные</option>
                    <option value="unpublished">На модерации</option>
                </select>

            </div>

            <table id="posts" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <td class="d-none"></td>
                        <td>ID</td>
                        <td class="col-1 text-nowrap">Заголовок</td>
                        <td class="col-2">Имя автора</td>
                        <td class="col-1">Ссылка</td>
                        <td class="col-6">Описание</td>
                        <td class="col-1">Дата</td>
                        <td class="col-2">Статус</td>
                        <td>Действие</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post):
                        if ($post['post_type'] == 'blogs'): ?>
                            <tr class="<?= $post['moderation'] == 1 || $post['moderation'] == 2 ? 'highlight' : '' ?>"
                                data-status="<?= $post['moderation'] == 1 ? 'unpublished' : 'published' ?>">
                                <td class="d-none">
                                    <?= strtotime($post['date']) ?>
                                </td>
                                <td><?= $post['id'] ?></td>
                                <td><?= htmlspecialchars_decode($post['title']) ?></td>
                                <td><?= $post['author_name'] ?></td>
                                <td><?= ($post['alias']) ? $post['alias'] : "-" ?></td>
                                <td>
                                    <?php 
                                    $textContent = strip_tags(htmlspecialchars_decode($post['content']));
                                    echo truncate_text($textContent, 230); 
                                    ?>
                                </td>
                                <td><?= date("d.m.Y H:i:s", strtotime($post['date'])) ?></td>
                                <td>
                                    <?php
                                    if ($post['moderation'] == 1) {
                                        echo 'На модерации';
                                    } elseif ($post['moderation'] == 2) {
                                        echo 'Требует доработки';
                                    } else {
                                        echo 'Опубликован';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                        <button type="submit" class="btn btn-info modalAdd" data-title="<?= $post['title'] ?>"
                                            data-content="<?= $post['content'] ?>" data-id="<?= $post['id'] ?>">
                                            Проверить
                                        </button>
                                        <?php if ($userRole == 'superadmin'): ?>
                                            <button type="submit" class="btn btn-danger delete"
                                                data-id="<?= $post['id'] ?>">Удалить</button>
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
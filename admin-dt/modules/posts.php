<?php
$posts = get_all_posts();
?>
<div id="postsPage" class="wrapper">
    <p class="h1 text-center">
    Посты
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="posts" class="table table-striped" style="width:100%">
                <thead>
                <tr>
                    <td class="col-1">id</td>
                    <td class="col-1 text-nowrap">Заголовок вопроса</td>
                    <td class="col-1">Алиас</td>
                    <td class="col-7">Контент</td>
                    <td class="col-1">Статус</td>
                    <td>Действие</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($posts as $post): 
                     if ($post['moderation'] === '1' && $post['post_type'] === 'blogs') : ?>
                    <tr>
                        <td><?= $post['id'] ?></td>
                        <td><?= $post['title'] ?></td>
                        <td><?= ($post['alias']) ? $post['alias'] : "-" ?></td>
                        <td><?= $post['content'] ?></td>
                        <td><?= $post['moderation'] === '1' ? 'На модерации' : 'Опубликован' ?></td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                               
                                <button type="submit" class="btn btn-info check-out" 
                                        data-title="<?= $post['title'] ?>" 
                                        data-content="<?= $post['content'] ?>" 
                                        data-id="<?= $post['id'] ?>">
                                    Проверить
                                </button>
                                <button type="submit" class="btn btn-danger delete" data-id="<?= $post['id'] ?>">Удалить</button>
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
<?php
$journals = get_all_posts();
?>
<div id="journalPage" class="wrapper">
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
                    <td class="col-1">id</td>
                    <td class="col-1 text-nowrap">Заголовок</td>
                    <td class="col-1">Тип</td>
                    <td class="col-1">Алиас</td>
                    <td class="col-7">Контент</td>
                    <td class="col-1">Дата</td>
                    <td class="col-1">Статус</td>
                    <td>Действие</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($journals as $journal): 
                    if ($journal['post_type'] === 'articles' || $journal['post_type'] === 'news' /* || $journal['post_type'] === 'blogs' */) :?>
                    <tr>
                        <td><?= $journal['id'] ?></td>
                        <td><?= htmlspecialchars_decode($journal['title']) ?></td>
                        <td><?= $journal['post_type'] ?></td>
                        <td><?= ($journal['alias']) ? $journal['alias'] : "-" ?></td>
                        <td><?= htmlspecialchars_decode(truncate_text($journal['content'], 240)) ?></td>
                        <td><?= ($journal['date']) ?></td>
                        <td><?= $journal['moderation'] === '1' ? 'На модерации' : 'Опубликован' ?></td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                               
                                <button type="submit" class="btn btn-info modalAdd"  
                                        data-id="<?= $journal['id'] ?>">
                                    Изменить
                                </button>
                                <button type="submit" class="btn btn-danger delete" data-id="<?= $journal['id'] ?>">Удалить</button>
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
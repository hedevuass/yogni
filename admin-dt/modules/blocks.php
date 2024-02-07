<?php
$blocks = get_blocks();
?>
<div id="blocksPage" class="wrapper">
    <p class="h1 text-center">
        Блоки
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="blocks" class="table table-striped" style="width:100%">
                <thead>
                <tr>
                    <td class="text-nowrap">Заголовок блока</td>
                    <td>Описание блока</td>
                    <td>Действие</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($blocks as $block): ?>
                    <tr>
                        <td><?= $block['title'] ?></td>
                        <td><?= $block['description'] ?></td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                <button type="submit" class="btn btn btn-info modalEdit" data-id="<?= $block['id'] ?>>">
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
</div>
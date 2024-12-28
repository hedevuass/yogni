<?php
$services = get_actual_services();
$blocks = get_blocks();
?>

<div id="servicePage" class="wrapper">
    <p class="h1 text-center">Актуальные разделы</p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="servicesActive" class="table table-striped" style="width:100%">
                <thead>
                <tr>
                    <td class="col-2 text-nowrap">Изображение</td>
                    <td>Заголовок</td>
                    <td>Действие</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($services as $service): ?>
                    <?php
                    $service_in_block = false;
                    foreach ($blocks as $block) {
                        if ($block['id'] == 1) {
                            $content = json_decode($block['content'], true);
                            if (in_array($service['id'], $content)) {
                                $service_in_block = true;
                                break;
                            }
                        }
                    }
                    if ($service_in_block):
                    ?>
                        <tr>
                            <td><img alt="Нет изображения" class="table-avatar" src="/assets/img/<?= htmlspecialchars($service['img']) ?>"></td>
                            <td><?= htmlspecialchars($service['title']) ?></td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                    <button type="submit" class="btn btn-info modalEdit" data-id="<?= $service['id'] ?>">
                                        Редактировать
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="h1 text-center">Полезные разделы</p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="servicesUseful" class="table table-striped" style="width:100%">
                <thead>
                <tr>
                    <td class="col-2 text-nowrap">Изображение</td>
                    <td>Заголовок</td>
                    <td>Действие</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($services as $service): ?>
                    <?php
                    $service_in_block = false;
                    foreach ($blocks as $block) {
                        if ($block['id'] == 2) {
                            $content = json_decode($block['content'], true);
                            if (in_array($service['id'], $content)) {
                                $service_in_block = true;
                                break;
                            }
                        }
                    }
                    if ($service_in_block):
                    ?>
                        <tr>
                            <td><img alt="Нет изображения" class="table-avatar" src="/assets/img/<?= htmlspecialchars($service['img']) ?>"></td>
                            <td><?= htmlspecialchars($service['title']) ?></td>
                            <td>
                                <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                    <button type="submit" class="btn btn-info modalEdit" data-id="<?= $service['id'] ?>">
                                        Редактировать
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

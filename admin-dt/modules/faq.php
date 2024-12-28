<?php
$faq_pages = get_all_faq_pages();
$faqs_promo = get_all_faq_promo();
$faqs_pro_photo = get_all_faq_pro_photo();
?>
<div id="faqPage" class="wrapper">
    <p class="h1 text-center">
    FAQ общий (ЛК)
    <button type="button" class="btn btn-success modalEdit">
            Добавить раздел
    </button>
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="faq" class="table table-striped" style="width:100%">
                <thead>
                <tr>
                    <td class="text-nowrap">Заголовок</td>
                    <td>Контент</td>
                    <td>Действие</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($faq_pages as $faq_page): ?>
                    <tr>
                        <td><?= $faq_page['name'] ?></td>
                        <td><?= ($faq_page['alias']) ? $faq_page['alias'] : "-" ?></td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                <button type="submit" class="btn btn btn-info modalEdit" data-id="<?= $faq_page['id'] ?>>">
                                    Редактировать
                                </button>
                                <button type="submit" class="btn btn-danger delete" data-type="faq" data-id="<?= $faq_page['id'] ?>">Удалить</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="h1 text-center">
    FAQ "Продвижение"
    <button type="button" class="btn btn-success modalEditFaqPromo">
            Добавить раздел
    </button>
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="faqPromo" class="table table-striped" style="width:100%">
                <thead>
                <tr>
                    <td class="text-nowrap">Заголовок</td>
                    <td>Контент</td>
                    <td>Действие</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($faqs_promo as $faq_promo): ?>
                    <tr>
                        <td><?= $faq_promo['title'] ?></td>
                        <td><?= $faq_promo['description'] ?></td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                <button type="submit" class="btn btn btn-info modalEditFaqPromo" data-id="<?= $faq_promo['id'] ?>>">
                                    Редактировать
                                </button>
                                <button type="submit" class="btn btn-danger delete" data-type="promo" data-id="<?= $faq_promo['id'] ?>">Удалить</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="h1 text-center">
    FAQ "Фотосъемка"
    <button type="button" class="btn btn-success modalEditFaqProPhoto">
            Добавить раздел
    </button>
    </p>

    <div class="table-responsive">
        <div class="container-fluid">
            <table id="faqProPhoto" class="table table-striped" style="width:100%">
                <thead>
                <tr>
                    <td class="text-nowrap">Заголовок</td>
                    <td>Контент</td>
                    <td>Действие</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($faqs_pro_photo as $faq_pro_photo): ?>
                    <tr>
                        <td><?= $faq_pro_photo['title'] ?></td>
                        <td><?= $faq_pro_photo['description'] ?></td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                <button type="submit" class="btn btn btn-info modalEditFaqProPhoto" data-id="<?= $faq_pro_photo['id'] ?>>">
                                    Редактировать
                                </button>
                                <button type="submit" class="btn btn-danger delete" data-type="pro_photo" data-id="<?= $faq_pro_photo['id'] ?>">Удалить</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
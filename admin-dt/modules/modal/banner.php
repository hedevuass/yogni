<div class="modal fade" id="modalEditbanner" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= $banner['name'] ?? '' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="modal-body" class="modal-body">
                <?php if (isset($slides)): ?>
                    <?php foreach ($slides as $i => $slide):
                        $description = json_decode($slide['description'], true); ?>
                        <div class="mb-3">
                            <h5 class="mb-3">Слайд №<?= $i + 1 ?></h5>

                            <input type="hidden" name="id[]" value="<?= $slide['id'] ?? '' ?>">
                            <input type="hidden" name="banner_id[]" value="<?= $banner['id'] ?? '' ?>">

                            <label for="input-image-<?= $i ?>" class="form-label">Изображение слайда</label>
                            <?php if (!empty($slide['image'])): ?>
                                <img src="/assets/img/<?= htmlspecialchars($slide['image']) ?>" alt="Слайд <?= $i + 1 ?>" class="img-thumbnail mt-2" style="max-width: 150px;">
                            <?php endif; ?>
                            <input type="file" name="image[]" class="form-control" data-id="<?= $slide['id'] ?>">

                            <label for="input-title-<?= $i ?>" class="form-label">Заголовок слайда</label>
                            <input type="text" name="title[]" class="form-control" id="input-title-<?= $i ?>" value="<?= $slide['title'] ?? '' ?>" required>

                            <label for="input-description-line1-<?= $i ?>" class="form-label">Описание строка 1</label>
                            <input type="text" name="description[<?= $i ?>][line1]" class="form-control" id="input-description-line1-<?= $i ?>" value="<?= $description['line1'] ?? '' ?>">

                            <label for="input-description-line2-<?= $i ?>" class="form-label">Описание строка 2</label>
                            <input type="text" name="description[<?= $i ?>][line2]" class="form-control" id="input-description-line2-<?= $i ?>" value="<?= $description['line2'] ?? '' ?>">

                            <label for="input-description-line3-<?= $i ?>" class="form-label">Описание строка 3</label>
                            <input type="text" name="description[<?= $i ?>][line3]" class="form-control" id="input-description-line3-<?= $i ?>" value="<?= $description['line3'] ?? '' ?>">

                            <label for="input-specialist-name-<?= $i ?>" class="form-label">Имя специалиста</label>
                            <input type="text" name="specialist_name[]" class="form-control" id="input-specialist-name-<?= $i ?>" value="<?= $slide['specialist_name'] ?? '' ?>">

                            <label for="input-category-<?= $i ?>" class="form-label">Категория специалиста</label>
                            <input type="text" name="classification[]" class="form-control" id="input-category-<?= $i ?>" value="<?= $slide['classification'] ?? '' ?>">

                            <label for="input-link-<?= $i ?>" class="form-label">Ссылка на баннер</label>
                            <input type="url" name="link[]" class="form-control" id="input-link-<?= $i ?>" value="<?= $slide['link'] ?? '' ?>">

                            <label for="input-button-text-<?= $i ?>" class="form-label">Текст на кнопке</label>
                            <input type="text" name="button_text[]" class="form-control" id="input-button-text-<?= $i ?>" value="<?= $slide['button_text'] ?? '' ?>">

                            <label for="input-button-link-<?= $i ?>" class="form-label">Ссылка на кнопку</label>
                            <input type="url" name="button_link[]" class="form-control" id="input-button-link-<?= $i ?>" value="<?= $slide['button_link'] ?? '' ?>">
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-banner-id="<?= $banner['id'] ?>" id="addSlide">Добавить слайд</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="bannerEdit">Редактировать блок</button>
            </div>
        </div>
    </div>
</div>
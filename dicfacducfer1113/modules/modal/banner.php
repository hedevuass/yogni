<?php if (!empty($onlyPrice)): ?>
    <div class="modal fade" id="modalEditbanner" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $banner['name'] ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="input-price" class="form-label">Цена</label>
                        <input type="number" id="input-price" name="price" class="form-control"
                            value="<?= htmlspecialchars($banner['price'] ?? '') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary bannerEditPrice" data-id="<?= $banner['id'] ?? '' ?>" data-type="banner">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
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
                            $description = json_decode($slide['description'], true);
                            $bannerId = $banner['id'];
                        ?>
                            <div class="row mb-2">
                                <?php if (!in_array($bannerId, [5, 6])): ?>
                                    <div class="col-12 position-relative text-center">
                                        <h5 class="mb-3">Слайд №<?= $i + 1 ?></h5>
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="button" class="btn btn-danger remove-slide" data-slide-id="<?= $i ?>">Удалить
                                            слайд</button>
                                    </div>
                                <?php endif; ?>

                                <input type="hidden" name="id[]" value="<?= $slide['id'] ?? '' ?>">
                                <input type="hidden" name="banner_id[]" value="<?= $bannerId ?>">

                                <div class="row mb-2">
                                    <label for="input-image-<?= $i ?>" class="form-label">Изображение</label>
                                    <?php if (!empty($slide['image'])): ?>
                                        <img src="/assets/img/<?= htmlspecialchars($slide['image']) ?>" alt="Слайд <?= $i + 1 ?>"
                                            class="img-thumbnail mt-2" style="max-width: 150px;">
                                    <?php endif; ?>
                                    <input type="file" name="image[]" class="form-control" data-id="<?= $slide['id'] ?>">
                                </div>

                                <?php if (!in_array($bannerId, [2, 3, 4])): ?>
                                    <div class="row mb-2">
                                        <label for="input-title-<?= $i ?>" class="form-label">Заголовок</label>
                                        <input type="text" name="title[]" class="form-control" id="input-title-<?= $i ?>"
                                            value="<?= $slide['title'] ?? '' ?>" required>
                                    </div>
                                <?php endif; ?>

                                <?php if ($bannerId == 1 || $bannerId == 6) : ?>
                                    <div class="row mb-2">
                                        <label for="input-subtitle-<?= $i ?>" class="form-label">Подзаголовок</label>
                                        <input type="text" name="subtitle[]" class="form-control" id="input-subtitle-<?= $i ?>"
                                            value="<?= $slide['subtitle'] ?? '' ?>">
                                    </div>
                                <?php endif; ?>

                                <?php if ($bannerId == 1): ?>
                                    <div class="row mb-2">
                                        <label for="input-link-<?= $i ?>" class="form-label">Ссылка на подзаголовке</label>
                                        <input type="url" name="link[]" class="form-control" id="input-link-<?= $i ?>"
                                            value="<?= $slide['link'] ?? '' ?>">
                                    </div>
                                <?php endif; ?>

                                <?php if ($bannerId != 5): ?>
                                    <div class="row mb-2">
                                        <label for="input-description-line1-<?= $i ?>" class="form-label">Описание строка 1</label>
                                        <input type="text" name="description[<?= $i ?>][line1]" class="form-control"
                                            id="input-description-line1-<?= $i ?>" value="<?= $description['line1'] ?? '' ?>">
                                    </div>

                                    <?php if ($bannerId != 5): ?>
                                        <div class="row mb-2">
                                            <label for="input-description-line2-<?= $i ?>" class="form-label">Описание строка 2</label>
                                            <input type="text" name="description[<?= $i ?>][line2]" class="form-control"
                                                id="input-description-line2-<?= $i ?>" value="<?= $description['line2'] ?? '' ?>">
                                        </div>

                                        <div class="row mb-2">
                                            <label for="input-description-line3-<?= $i ?>" class="form-label">Описание строка 3</label>
                                            <input type="text" name="description[<?= $i ?>][line3]" class="form-control"
                                                id="input-description-line3-<?= $i ?>" value="<?= $description['line3'] ?? '' ?>">
                                        </div>
                                        <?php if ($bannerId == 6): ?>
                                            <div class="row mb-2">
                                                <label for="input-description-line4-<?= $i ?>" class="form-label">Описание строка 4</label>
                                                <input type="text" name="description[<?= $i ?>][line4]" class="form-control"
                                                    id="input-description-line4-<?= $i ?>" value="<?= $description['line4'] ?? '' ?>">
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php elseif ($bannerId == 5): ?>
                                    <div class="row mb-2">
                                        <label for="input-description-line1-<?= $i ?>" class="form-label">Описание</label>
                                        <input type="text" name="description[<?= $i ?>][line1]" class="form-control"
                                            id="input-description-line1-<?= $i ?>" value="<?= $description['line1'] ?? '' ?>">
                                    </div>
                                <?php endif; ?>

                                <?php if (!in_array($bannerId, [1, 5, 6])): ?>
                                    <div class="row mb-2">
                                        <label for="input-specialist-name-<?= $i ?>" class="form-label">Имя специалиста</label>
                                        <input type="text" name="specialist_name[]" class="form-control"
                                            id="input-specialist-name-<?= $i ?>" value="<?= $slide['specialist_name'] ?? '' ?>">
                                    </div>

                                    <?php if (!in_array($bannerId, [5, 6])): ?>
                                        <div class="row mb-2">
                                            <?php
                                            $labelText = ($bannerId == 4) ? 'Метро' : 'Категория специалиста';
                                            ?>
                                            <label for="input-category-<?= $i ?>" class="form-label"><?= $labelText ?></label>
                                            <input type="text" name="classification[]" class="form-control" id="input-category-<?= $i ?>"
                                                value="<?= $slide['classification'] ?? '' ?>">
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if (!in_array($bannerId, [2, 3, 4])): ?>
                                    <div class="row mb-2">
                                        <label for="input-button-text-<?= $i ?>" class="form-label">Текст на кнопке</label>
                                        <input type="text" name="button_text[]" class="form-control" id="input-button-text-<?= $i ?>"
                                            value="<?= $slide['button_text'] ?? '' ?>">
                                    </div>
                                    <?php if (!in_array($bannerId, [6])): ?>
                                        <div class="row mb-2">
                                            <label for="input-button-link-<?= $i ?>" class="form-label">Ссылка на кнопке</label>
                                            <input type="url" name="button_link[]" class="form-control" id="input-button-link-<?= $i ?>"
                                                value="<?= $slide['button_link'] ?? '' ?>">
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if (!in_array($bannerId, [1, 5])): ?>
                                    <div class="row mb-2">
                                        <label for="input-link-<?= $i ?>" class="form-label">Ссылка на баннер</label>
                                        <input type="url" name="link[]" class="form-control" id="input-link-<?= $i ?>"
                                            value="<?= $slide['link'] ?? '' ?>">
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="modal-footer">
                    <?php if (!in_array($bannerId, [6])): ?>
                        <button type="button" class="btn btn-success" data-banner-id="<?= $banner['id'] ?>"
                            id="addSlide">Добавить
                            слайд</button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary" id="bannerEdit">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
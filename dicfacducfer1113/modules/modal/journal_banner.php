<?php if (!empty($onlyPrice)): ?>
    <div class="modal fade" id="modalEditJournalbanner" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $journal['name']?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="input-price" class="form-label">Цена</label>
                        <input type="number" id="input-price" name="price" class="form-control"
                            value="<?= htmlspecialchars($journal['price'] ?? '') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary bannerEditPrice"  data-id="<?= $journal['id'] ?? '' ?>" data-type="journal-banner">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="modal fade" id="modalEditJournalbanner" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $banner['title'] ?? '' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="col-3 position-relative">
                            <div class="image-container">
                                <img src="/assets/img/<?= htmlspecialchars($banner['image']) ?>" class="avatar"
                                    alt="menu-<?= htmlspecialchars($journal['id']) ?>">
                                <span class="upload-avatar">Изменить</span>
                            </div>
                            <input type="file" style="display: none" name="photo" id="input-img">
                        </div>
                        <div class="col-9">
                            <input type="hidden" name="id" class="form-control" id="input-id"
                                value="<?= $banner['id'] ?? '' ?>">
                            <div class="mb-3">
                                <label for="input-title" class="form-label">Заголовок</label>
                                <input type="text" name="title" class="form-control" id="input-title"
                                    value="<?= $banner['title'] ?? '' ?>" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <?php if (!in_array($banner['id'], [3, 4])): ?>
                                <div class="mb-3">
                                    <label for="input-content" class="form-label">Описание</label>
                                    <textarea name="content" class="form-control" id="input-content"
                                        required><?= ($banner['description'] ?? '') ?></textarea>
                                </div>
                            <?php endif; ?>

                            <?php if (in_array($banner['id'], [1, 2])): ?>
                                <div class="mb-3">
                                    <label for="input-service_type" class="form-label">Источник</label>
                                    <input type="text" name="service_type" class="form-control" id="input-service-type"
                                        value="<?= $banner['service_type'] ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="input-description" class="form-label">Ссылка на источник</label>
                                    <input type="text" name="alias" class="form-control" id="input-description"
                                        value="<?= $banner['link'] ?>" required>
                                </div>
                            <?php endif; ?>

                            <?php if (!in_array($banner['id'], [4])): ?>
                                <div class="mb-3">
                                    <label for="input-button_text" class="form-label">Текст на кнопке</label>
                                    <input type="text" name="button_text" class="form-control" id="input-button_text"
                                        value="<?= $banner['button_text'] ?>" required>
                                </div>
                            <?php endif; ?>

                            <?php if (!in_array($banner['id'], [1, 2, 5])): ?>
                                <div class="mb-3">
                                    <label for="input-description" class="form-label">Ссылка</label>
                                    <input type="text" name="alias" class="form-control" id="input-description"
                                        value="<?= $banner['link'] ?>" required>
                                </div>
                            <?php endif; ?>

                            <?php if (!in_array($banner['id'], [3, 4])): ?>
                                <div class="mb-3">
                                    <label for="input-button_link" class="form-label">Ссылка на кнопке</label>
                                    <input type="text" name="button_link" class="form-control" id="input-button_link"
                                        value="<?= $banner['button_link'] ?>" required>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary" id="bannerJournalEdit">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<div class="modal fade" id="modalEditFaqPages" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= $faq_promo['title'] ?? '' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <input type="hidden" name="id" class="form-control" id="input-id" value="<?= $faq_promo['id'] ?? '' ?>">
                        <div class="mb-3">
                            <label for="input-title" class="form-label">Название</label>
                            <input type="text" name="title" class="form-control" id="input-name" value="<?= $faq_promo['title'] ?? '' ?>" require>
                        </div>
                        <div class="mb-3">
                            <label for="input-content" class="form-label">Контент</label>
                            <textarea name="description" class="form-control input-content" id="input-content" required><?= htmlspecialchars($faq_promo['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" data-type="<?= $type ?>" id="faqPagesEdit"><?= ($faq_promo['id']) ? 'Сохранить' : 'Добавить' ?></button>
            </div>
        </div>
    </div>
</div>
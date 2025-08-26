<div class="modal fade" id="modalEditFaqPages" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= $faq_pages['name'] ?? '' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <input type="hidden" name="id" class="form-control" id="input-id" value="<?= $faq_pages['id'] ?? '' ?>">
                        <div class="mb-3">
                            <label for="input-title" class="form-label">Заголовок</label>
                            <input type="text" name="name" class="form-control" id="input-name" value="<?= $faq_pages['name'] ?? '' ?>" require>
                        </div>
                        <div class="mb-3">
                            <label for="input-short-descr" class="form-label">Краткое описание</label>
                            <input type="text" name="short-descr" class="form-control" id="input-short-descr" value="<?= $faq_pages['short_descr'] ?? '' ?>" require>
                        </div>
                        <div class="mb-3">
                            <label for="input-content" class="form-label">Описание</label>
                            <textarea name="content" class="form-control h-100" style="min-height: 450px;" id="input-content" required><?= htmlspecialchars_decode($faq_pages['content'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="input-description" class="form-label">Ссылка</label>
                            <input type="text" name="alias" class="form-control" id="input-alias" value="<?= $faq_pages['alias'] ?>" require>
                        </div>
                        <div class="mb-3">
                            <label for="input-meta-title" class="form-label">Title страницы</label>
                            <input type="text" name="meta_title" class="form-control" id="input-meta-title" value="<?= $faq_pages['meta_title'] ?? '' ?>" require>
                        </div>
                        <div class="mb-3">
                            <label for="input-meta-description" class="form-label">Description страницы</label>
                            <input type="text" name="meta_description" class="form-control" id="input-meta-description" value="<?= $faq_pages['meta_description'] ?? '' ?>" require>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" data-type="lk" id="faqPagesEdit"><?= ($faq_pages['id']) ? 'Сохранить' : 'Добавить' ?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalEditDocument" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $document['title'] ?? '' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="col-9">
                        <input type="hidden" name="id" class="form-control" id="input-id" value="<?= $document['id'] ?? '' ?>">
                        <div class="mb-3">
                            <label for="input-title" class="form-label">Заголовок</label>
                            <input type="text" name="title" class="form-control" id="input-title" value="<?= $document['title'] ?? '' ?>" require>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="input-content" class="form-label">Описание</label>
                            <textarea name="content" class="form-control" style="height: 300px;" id="input-content" required><?= ($document['content'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="documentEdit"><?= $document['id'] ? 'Сохранить' : 'Добавить' ?></button>
            </div>
        </div>
    </div>
</div>
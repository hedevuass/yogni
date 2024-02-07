<div class="modal fade" id="modalEditBlock" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= $block['title'] ?? '' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" class="form-control" id="input-id" value="<?= $block['id'] ?? '' ?>">
                <div class="mb-3">
                    <label for="input-title" class="form-label">Название блока</label>
                    <input type="text" name="title" class="form-control" id="input-title" value="<?= $block['title'] ?? '' ?>" require>
                </div>
                <div class="mb-3">
                    <label for="input-description" class="form-label">Описание блока</label>
                    <textarea type="text" name="description" class="form-control" id="input-description" rows="6"><?= $block['description'] ?? '' ?></textarea>
                </div>
                <?php if (isset($services)):
                    $blocks = $block['blocks'] ?? 0;
                    $content = json_decode($block['content'], true);
                    for ($i = 1; $i <= $blocks; $i++): ?>
                        <div class="mb-3">
                            <label for="input-service-<?= $i ?>" class="form-label">Подблок №<?= $i ?></label>
                            <select name="services[]" class="form-select" aria-label="Выберите категорию" id="input-service-<?= $i ?>">
                                <?php foreach ($services as $service): ?>
                                    <option value="<?= $service['id'] ?>" <?= $service['id'] == $content[$i] ? 'selected' : '' ?>><?= $service['title'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endfor;
                endif; ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="blockEdit">Редактировать блок</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalEditTariff" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel"><?= $tariff['name'] ?? '' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" class="form-control" id="input-id" value="<?= $tariff['id'] ?? '' ?>">
                <input type="hidden" name="type" class="form-control" id="input-type" value="<?= $type ?? '' ?>">
                <div class="mb-3">
                    <label for="input-name" class="form-label">Название тарифа</label>
                    <input type="text" name="name" class="form-control" id="input-name" value="<?= $tariff['name'] ?? '' ?>" require>
                </div>
                <div class="mb-3">
                    <label for="input-price" class="form-label">Цена тарифа</label>
                    <input type="number" name="price" class="form-control" id="input-price" value="<?= $tariff['price'] ?? '' ?>" require>
                </div>
                <div class="mb-3">
                    <label for="input-price_old" class="form-label">Старая цена</label>
                    <input type="number" name="price_old" class="form-control" id="input-price_old" value="<?= $tariff['price_old'] ?? '' ?>" require>
                </div>

                <div class="mb-3">
                    <h6>Редактирование опций тарифа</h6>
                    <?php $tariff_options = json_decode($tariff['options'], true); ?>
                    <div id="options-container">
                        <?php foreach ($tariff_options as $key => $option): ?>
                            <div class="mb-3 option-item" data-key="<?= $key ?>">
                                <label for="input-option-<?= $key ?>" class="form-label">Опция <?= $key ?></label>
                                <div class="row mb-3">
                                    <div class="col-10">
                                        <input type="text" name="options[<?= $key ?>][text]" class="form-control" id="input-option-<?= $key ?>" value="<?= htmlspecialchars($option['text']) ?>" required>
                                        
                                        <input type="hidden" name="options[<?= $key ?>][image]" value="<?= htmlspecialchars($option['image']) ?>">
                                    </div>
                                    <div class="col-2">
                                        <button type="button" class="btn btn-danger btn-sm delete-option-btn">Удалить</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="add-option-btn" class="btn btn-primary">Добавить опцию</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="tariffEdit">Редактировать тариф</button>
            </div>
        </div>
    </div>
</div>
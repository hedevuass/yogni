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
                    <label for="input-price" class="form-label">Цена тарифа</label>
                    <input type="number" name="price" class="form-control" id="input-price" value="<?= $tariff['price'] ?? '' ?>" require>
                </div>
                <div class="mb-3">
                    <label for="input-price_old" class="form-label">Старая цена</label>
                    <input type="number" name="price_old" class="form-control" id="input-price_old" value="<?= $tariff['price_old'] ?? '' ?>" require>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="tariffEdit">Редактировать тариф</button>
            </div>
        </div>
    </div>
</div>
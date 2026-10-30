<?php
$all_items = get_all_equipment();

$amenities = [];
$equipments = [];

foreach ($all_items as $item) {
    if ($item['category'] === 'amenity') {
        $amenities[] = $item;
    } elseif ($item['category'] === 'equipment') {
        $equipments[] = $item;
    }
}
?>

<div id="equipmentPage" class="wrapper container-fluid mt-4">
    <!-- БЛОК УДОБСТВ -->
    <div class="d-flex justify-content-center align-items-center gap-3 mb-4">
        <p class="h1 mb-0">Удобства</p>
        <button type="button" class="btn btn-info add-row-btn" data-category="amenity">Добавить</button>
    </div>

    <div class="table-responsive mb-5">
        <table class="table table-striped align-middle" id="table-amenity">
            <thead>
                <tr>
                    <td style="width: 30%">Название</td>
                    <td style="width: 30%">Иконка</td>
                    <td style="width: 30%">Цветная икона</td>
                    <td style="width: 10%">Действие</td>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($amenities as $item): ?>
                    <tr data-id="<?= $item['id'] ?>" data-category="amenity">
                        <td>
                            <input type="text" class="form-control item-name" value="<?= htmlspecialchars($item['name']) ?>">
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="icon-preview-box rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border: 1px solid #dee2e6; overflow: hidden;">
                                    <?php if (!empty($item['img'])): ?>
                                        <img src="/assets/img/equipment/<?= $item['img'] ?>" class="img-fluid icon-img" style="max-height: 100%;">
                                    <?php endif; ?>
                                </div>
                                <input type="file" class="d-none upload-input-normal" accept="image/*">
                                <button type="button" class="btn btn-primary btn-sm trigger-file">Загрузить</button>
                                <button type="button" class="btn btn-danger btn-sm remove-img-btn">Удалить</button>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="icon-preview-box rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border: 1px solid #dee2e6; overflow: hidden;">
                                    <?php if (!empty($item['img_pink'])): ?>
                                        <img src="/assets/img/equipment/<?= htmlspecialchars($item['img_pink']) ?>" class="img-fluid icon-pink-img" style="max-height: 100%;">
                                    <?php endif; ?>
                                </div>
                                <input type="file" class="d-none upload-input-pink" accept="image/*">
                                <button type="button" class="btn btn-primary btn-sm trigger-file">Загрузить</button>
                                <button type="button" class="btn btn-danger btn-sm remove-pink-btn">Удалить</button>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success save-row">Сохранить</button>
                                <button type="button" class="btn btn-danger delete-row">Удалить</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- БЛОК ОБОРУДОВАНИЯ -->
    <div class="d-flex justify-content-center align-items-center gap-3 mb-4 mt-5">
        <p class="h1 mb-0">Оборудование</p>
        <button type="button" class="btn btn-info add-row-btn" data-category="equipment">Добавить</button>
    </div>

    <div class="table-responsive mb-5">
        <table class="table table-striped align-middle" id="table-equipment">
            <thead>
                <tr>
                    <td style="width: 30%">Название</td>
                    <td style="width: 30%">Иконка</td>
                    <td style="width: 30%">Цветная икона</td>
                    <td style="width: 10%">Действие</td>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($equipments as $item): ?>
                    <tr data-id="<?= $item['id'] ?>" data-category="equipment">
                        <td>
                            <input type="text" class="form-control item-name" value="<?= htmlspecialchars($item['name']) ?>">
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="icon-preview-box rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border: 1px solid #dee2e6; overflow: hidden;">
                                    <?php if (!empty($item['img'])): ?>
                                        <img src="/assets/img/equipment/<?= $item['img'] ?>" class="img-fluid icon-img" style="max-height: 100%;">
                                    <?php endif; ?>
                                </div>
                                <input type="file" class="d-none upload-input-normal" accept="image/*">
                                <button type="button" class="btn btn-primary btn-sm trigger-file">Загрузить</button>
                                <button type="button" class="btn btn-danger btn-sm remove-img-btn">Удалить</button>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="icon-preview-box rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border: 1px solid #dee2e6; overflow: hidden;">
                                    <?php if (!empty($item['img_pink'])): ?>
                                        <img src="/assets/img/equipment/<?= htmlspecialchars($item['img_pink']) ?>" class="img-fluid icon-pink-img" style="max-height: 100%;">
                                    <?php endif; ?>
                                </div>
                                <input type="file" class="d-none upload-input-pink" accept="image/*">
                                <button type="button" class="btn btn-primary btn-sm trigger-file">Загрузить</button>
                                <button type="button" class="btn btn-danger btn-sm remove-pink-btn">Удалить</button>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success save-row">Сохранить</button>
                                <button type="button" class="btn btn-danger delete-row">Удалить</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
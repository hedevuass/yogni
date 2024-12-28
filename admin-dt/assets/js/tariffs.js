import {
    Helper
} from "./helper.js";

let Tariffs = {
    selectors: {
        'pageSelector': '#tariffPage',
        'modalEditTariffSelector': '#modalEditTariff',
        'modalEditPromotionSelector': '#modalEditPromotion',
        'modalEditOptionSelector': '#modalEditOption',
        'addOptionButton': '#add-option-btn', 
        'optionsContainer': '#options-container',
        'deleteOptionButton': '.delete-option-btn'
    },
    start: function () {
        $('#tariffs').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })
        $('#options').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })
        $('#promo-options').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })

        $(this.selectors.pageSelector).on('click', '.modalEdit', this.modalEdit)
        $(this.selectors.pageSelector).on('click', '#tariffEdit, #promotionEdit, #optionEdit', this.edit)
        $(this.selectors.pageSelector).on('click', '.delete', this.delete)
        $(this.selectors.pageSelector).on('click', this.selectors.addOptionButton, this.addOption);
        $(this.selectors.pageSelector).on('click', this.selectors.deleteOptionButton, this.deleteOption);
    },
    addOption: function() {
        let optionCounter = $('#options-container .option-item').length; // Считаем количество существующих опций

        // Создаем новый HTML для поля ввода новой опции с полем для загрузки файла
        let newOptionHtml = `
            <div class="mb-3 option-item" data-key="${optionCounter + 1}">
                <label for="input-option-${optionCounter + 1}" class="form-label">Опция ${optionCounter + 1}</label>
                <div class="row mb-3">
                    <!-- Колонка для поля ввода и изображения -->
                    <div class="col-10">
                        <input type="text" name="options[${optionCounter + 1}][text]" class="form-control" id="input-option-${optionCounter + 1}" required>

                        <!-- Поле для выбора изображения -->
                        <input type="hidden" name="options[${optionCounter + 1}][image]" value="">
                    </div>
                    <!-- Колонка для кнопки удаления -->
                    <div class="col-2">
                        <button type="button" class="btn btn-danger btn-sm delete-option-btn">Удалить</button>
                    </div>
                </div>
            </div>
        `;


        // Добавляем новый блок в контейнер
        $(Tariffs.selectors.optionsContainer).append(newOptionHtml);
    },

    // Метод для удаления опции
    deleteOption: function() {
        // Удаляем родительский элемент (весь блок с опцией)
        $(this).closest('.option-item').remove();
    },
    modalEdit: function () {
        let button = $(this),
            type = button.attr('data-type'),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html()

        $.ajax({
            type: "POST",
            url: "actions/tariff/get_modal.php",
            dataType: 'json',
            data: {
                id: id,
                type: type
            },
            beforeSend: function () {
                Helper.loadingButton(button)
                $(Tariffs.selectors.modalEditTariffSelector).remove()
                $(Tariffs.selectors.modalEditPromotionSelector).remove()
                $(Tariffs.selectors.modalEditOptionSelector).remove()
            },
            success: function (json) {
                $(Tariffs.selectors.pageSelector).append(json.code)
                $(Tariffs.selectors.modalEditTariffSelector).modal('show')
                $(Tariffs.selectors.modalEditPromotionSelector).modal('show')
                $(Tariffs.selectors.modalEditOptionSelector).modal('show')
                Helper.loadingButton(button, buttonContent)
            }
        })
    },
    edit: function () {
        let button = $(this),
            buttonContent = button.html()

        $.ajax({
            type: "POST",
            url: "actions/tariff/edit_tariff.php",
            dataType: 'json',
            data: $(`${Tariffs.selectors.modalEditOptionSelector} input, ${Tariffs.selectors.modalEditTariffSelector} input, ${Tariffs.selectors.modalEditPromotionSelector} input`),
            beforeSend: function () {
                Helper.loadingButton(button)
                Helper.removeValidation(Tariffs.selectors.modalEditOptionSelector)
                Helper.removeValidation(Tariffs.selectors.modalEditTariffSelector)
                Helper.removeValidation(Tariffs.selectors.modalEditPromotionSelector)
            },
            success: function (json) {
                Helper.loadingButton(button, buttonContent)
                if (json.error) {
                    $.each(json.error, function (key, value) {
                        $(`#input-${key}`).addClass('is-invalid').after(Helper.invalidFeedback(value))
                    });
                }
                if (json.fatal) Helper.fatalError(json.fatal)
                if (json.success) location.reload()
            }
        })
    }, delete: function () {
        let id = $(this).attr('data-id'),
            button = $(this)

        if (confirm('Вы уверены, что хотите удалить пост?')) {
            $.ajax({
                type: "POST",
                url: "actions/tariff/delete.php",
                dataType: 'json',
                data: {id: id},
                beforeSend: function () {
                    Helper.loadingButton(button)
                },
                success: function (json) {
                    if (json.success) location.reload()
                }
            })
        }
    },
};

export {
    Tariffs
}
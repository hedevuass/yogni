import {
    Helper
} from "./helper.js";

let Tariffs = {
    selectors: {
        'pageSelector': '#tariffPage',
        'modalEditTariffSelector': '#modalEditTariff',
        'modalEditPromotionSelector': '#modalEditPromotion',
        'modalEditOptionSelector': '#modalEditOption'
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
    }
};

export {
    Tariffs
}
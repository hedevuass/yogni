import {
    Helper
} from "./helper.js";

let banners = {
    selectors: {
        'pageSelector': '#bannersPage',
        'modalEditSelector': '#modalEditbanner'
    },
    start: function () {
        $('#banners').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })

        $(this.selectors.pageSelector).on('click', '.modalEdit', this.modalEdit)
        $(this.selectors.pageSelector).on('click', '#bannerEdit', this.edit)
    },
    edit: function () {
        let button = $(this),
            buttonContent = button.html()

        $.ajax({
            type: "POST",
            url: "actions/banner/edit_banner.php",
            dataType: 'json',
            data: $(`${banners.selectors.modalEditSelector} select, ${banners.selectors.modalEditSelector} input, ${banners.selectors.modalEditSelector} textarea`),
            beforeSend: function () {
                Helper.loadingButton(button)
                Helper.removeValidation(banners.selectors.modalEditSelector)
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
    },
    modalEdit: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html()

        $.ajax({
            type: "POST",
            url: "actions/banner/get_modal.php",
            dataType: 'json',
            data: {id: id},
            beforeSend: function () {
                Helper.loadingButton(button)
                $(banners.selectors.modalEditSelector).remove()
            },
            success: function (json) {
                $(banners.selectors.pageSelector).append(json.code)
                $(banners.selectors.modalEditSelector).modal('show')
                Helper.loadingButton(button, buttonContent)
            }
        })
    }
};

export {
    banners
}
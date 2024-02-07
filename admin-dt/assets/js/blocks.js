import {
    Helper
} from "./helper.js";

let Blocks = {
    selectors: {
        'pageSelector': '#blocksPage',
        'modalEditSelector': '#modalEditBlock'
    },
    start: function () {
        $('#blocks').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })

        $(this.selectors.pageSelector).on('click', '.modalEdit', this.modalEdit)
        $(this.selectors.pageSelector).on('click', '#blockEdit', this.edit)
    },
    edit: function () {
        let button = $(this),
            buttonContent = button.html()

        $.ajax({
            type: "POST",
            url: "actions/block/edit_block.php",
            dataType: 'json',
            data: $(`${Blocks.selectors.modalEditSelector} select, ${Blocks.selectors.modalEditSelector} input, ${Blocks.selectors.modalEditSelector} textarea`),
            beforeSend: function () {
                Helper.loadingButton(button)
                Helper.removeValidation(Blocks.selectors.modalEditSelector)
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
            url: "actions/block/get_modal.php",
            dataType: 'json',
            data: {id: id},
            beforeSend: function () {
                Helper.loadingButton(button)
                $(Blocks.selectors.modalEditSelector).remove()
            },
            success: function (json) {
                $(Blocks.selectors.pageSelector).append(json.code)
                $(Blocks.selectors.modalEditSelector).modal('show')
                Helper.loadingButton(button, buttonContent)
            }
        })
    }
};

export {
    Blocks
}
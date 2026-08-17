import {
    Helper
} from "./helper.js";

let Services = {
    selectors: {
        'pageSelector': '#servicePage',
        'modalEditSelector': '#modalEditService',
        'photoSelector': '#input-img'
    },
    start: function () {
        $('#services').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })

        $(this.selectors.pageSelector).on('click', '.modalEdit', this.modalEdit)
        $(this.selectors.pageSelector).on('click', '#serviceEdit', this.edit)
        $(this.selectors.pageSelector).on('click', '.upload-avatar', Services.file)
        $(this.selectors.pageSelector).on('change', this.selectors.photoSelector, e => Helper.uploadImage('.avatar', e))
    },
    file: function () {
        $(Services.selectors.photoSelector).click()
    },
    edit: function () {
        let button = $(this),
            buttonContent = button.html();
    
        // Создаем объект FormData для отправки данных формы
        let formData = new FormData();
        $(`${Services.selectors.modalEditSelector} select, ${Services.selectors.modalEditSelector} input, ${Services.selectors.modalEditSelector} textarea`).each(function() {
            if ($(this).attr('type') === 'file') {
                if ($(this)[0].files[0]) {
                    formData.append('photo', $(this)[0].files[0]);
                }
            } else {
                formData.append($(this).attr('name'), $(this).val());
            }
        });
    
        $.ajax({
            type: "POST",
            url: "actions/services/edit_service.php",
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                Helper.loadingButton(button);
                Helper.removeValidation(Services.selectors.modalEditSelector);
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
        });
    },    
    modalEdit: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html()

        $.ajax({
            type: "POST",
            url: "actions/services/get_modal.php",
            dataType: 'json',
            data: {id: id},
            beforeSend: function () {
                Helper.loadingButton(button)
                $(Services.selectors.modalEditSelector).remove()
            },
            success: function (json) {
                $(Services.selectors.pageSelector).append(json.code)
                $(Services.selectors.modalEditSelector).modal('show')
                Helper.loadingButton(button, buttonContent)
            }
        })
    }
};

export {
    Services
}
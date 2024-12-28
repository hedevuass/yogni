import {
    Helper
} from "./helper.js";

let Menu = {
    selectors: {
        'pageSelector': '#menuPage',
        'modalAddSelector': '#modalAddMenu',
        'photoSelector': '#input-img',
        'iconSelector': '#input-icon',  
    },
    start: function () {
        $('#menu').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })

        /* $(this.selectors.pageSelector).on('click', '.modalEdit', this.modalEdit) */
        $(this.selectors.pageSelector).on('click', '.modalAdd', this.modalAdd)
        $(this.selectors.pageSelector).on('click', '#menuEdit', this.edit)
        $(this.selectors.pageSelector).on('click', '#menuAdd', this.add)
        $(this.selectors.pageSelector).on('click', '.delete', this.delete)
        $(this.selectors.pageSelector).on('click', '.upload-avatar', Menu.file)
        $(this.selectors.pageSelector).on('click', '.upload-icon', Menu.icon)
        $(this.selectors.pageSelector).on('change', this.selectors.photoSelector, e => Helper.uploadImage('.avatar', e))
        $(this.selectors.pageSelector).on('change', this.selectors.iconSelector, e => Helper.uploadImage('.icon', e))
    },
    file: function () {
        $(Menu.selectors.photoSelector).click()
    },
    icon: function () {
        $(Menu.selectors.iconSelector).click()
    },
    modalAdd: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html()

        $.ajax({
            type: "POST",
            url: "actions/menu/get_modal.php",
            dataType: 'json',
            data: {id: id},
            beforeSend: function () {
                Helper.loadingButton(button)
                $(Menu.selectors.modalAddSelector).remove()
            },
            success: function (json) {
                $(Menu.selectors.pageSelector).append(json.code)
                $(Menu.selectors.modalAddSelector).modal('show')
                Helper.loadingButton(button, buttonContent)
            }
        })
    },
    edit: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html();
    
        // Создаем объект FormData для отправки данных формы
        let formData = new FormData();
        $(`${Menu.selectors.modalAddSelector} input, ${Menu.selectors.modalAddSelector} select, ${Menu.selectors.modalAddSelector} textarea`).each(function() {
            if ($(this).attr('type') === 'file') {
                if ($(this)[0].files[0]) {
                    formData.append('photo', $(this)[0].files[0]);
                    formData.append('icon', $(this)[0].files[0]);
                }
            } else {
                formData.append($(this).attr('name'), $(this).val());
            }
        });

        $.ajax({
            type: "POST",
            url: "actions/menu/edit_menu.php",
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                Helper.loadingButton(button);
                Helper.removeValidation(Menu.selectors.pageSelector);
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
    add: function() {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html(),
            formData = new FormData()

        if (id) formData.append('id', id);

        formData.append('name', $('#input-name').val())
        formData.append('lastname', $('#input-lastname').val())

        if ($(Menu.selectors.photoSelector)[0].files[0]) {
            formData.append('photo', $(Menu.selectors.photoSelector)[0].files[0])
        }

        formData.append('city_id', $('#input-city_id').val())
        formData.append('email', $('#input-email').val())
        formData.append('phone', $('#input-phone').val())
        formData.append('password', $('#input-password').val())
        formData.append('type', $('#input-type').val())
        formData.append('p_id', $('#input-parent').val())
        formData.append('classification_id', $('#input-classification').val())

        if ($('#input-company').css('display') !== 'none') {
            formData.append('company[name]', $('#input-company_name').val())
            formData.append('company[id]', $('#input-company_id').val())
        }

        if ($('#input-active:checked').length) formData.append('active', $('#input-active:checked').val())



        $.ajax({
            type: "POST",
            url: "actions/specialist/add_specialist.php",
            dataType: 'json',
            contentType: false,
            processData: false,
            data: formData,
            beforeSend: function () {
                Helper.loadingButton(button)
                Helper.removeValidation(Menu.selectors.modalAddSelector)
            },
            success: function (json) {
                Helper.loadingButton(button, buttonContent)
                if (json.error) {
                    $.each(json.error, function (key, value) {
                        $(`#input-${key}`).addClass('is-invalid').after(Helper.invalidFeedback(value))
                    });
                }

                if (json.fatal) {
                    Helper.fatalError(json.fatal)
                }

                if (json.success) {
                    location.reload()
                }
            }
        })
    },
    delete: function () {
        let id = $(this).attr('data-id'),
            button = $(this)

        if (confirm('Вы уверены, что хотите удалить пост?')) {
            $.ajax({
                type: "POST",
                url: "actions/menu/delete.php",
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
    Menu
}
// noinspection JSCheckFunctionSignatures

import {
    Helper
} from "./helper.js";

let Specialists = {
    selectors: {
        'pageSelector': '#specialistPage',
        'modalAddSelector': '#modalAddSpecialist',
        'photoSelector': '#input-photo'
    },
    start: function () {
        $('#specialists').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
            'processing': true,
            'serverSide': true,
            'serverMethod': 'post',
            'ajax': {
                'url': 'actions/specialist/get_specialists.php'
            },
            'columns': [
                {data: 'id'},
                {data: 'photo'},
                {data: 'name'},
                {data: 'email'},
                {data: 'phone'},
                {data: 'classification'},
                {data: 'city'},
                {data: 'rating'},
                {data: 'active'},
                {data: 'action'}
            ]
        })

        $(this.selectors.pageSelector).on('click', '.delete', Specialists.delete)
        $(this.selectors.pageSelector).on('click', '.modalAdd', Specialists.modalAdd)
        $(this.selectors.pageSelector).on('click', '.upload-avatar', Specialists.file)
        $(this.selectors.pageSelector).on('change', this.selectors.photoSelector, e => Helper.uploadImage('.avatar', e))
        $(this.selectors.pageSelector).on('input', '#input-phone', Helper.validationPhone)
        $(this.selectors.pageSelector).on('input', '#input-password', Helper.validationPassword)
        $(this.selectors.pageSelector).on('change', '#input-classification_id', Specialists.classification)
        $(this.selectors.pageSelector).on('click', '#specialistAdd', Specialists.add)
    },
    modalAdd: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html()


        $.ajax({
            type: "POST",
            url: "actions/specialist/get_modal.php",
            dataType: 'json',
            data: {id: id},
            beforeSend: function () {
                Helper.loadingButton(button)
                $(Specialists.selectors.modalAddSelector).remove()
            },
            success: function (json) {
                $(Specialists.selectors.pageSelector).append(json.code)
                $(Specialists.selectors.modalAddSelector).modal('show')
                Helper.loadingButton(button, buttonContent)
            }
        })
    },
    add: function() {
        let button = $(this),
            buttonContent = button.html(),
            formData = new FormData()

        if ($('#input-id').val()) formData.append('id', $('#input-id').val())

        formData.append('name', $('#input-name').val())
        formData.append('lastname', $('#input-lastname').val())

        if ($(Specialists.selectors.photoSelector)[0].files[0]) {
            formData.append('photo', $(Specialists.selectors.photoSelector)[0].files[0])
        }

        formData.append('city_id', $('#input-city_id').val())
        formData.append('email', $('#input-email').val())
        formData.append('phone', $('#input-phone').val())
        formData.append('password', $('#input-password').val())
        formData.append('type', $('#input-type').val())
        formData.append('classification_id', $('#input-classification_id').val())

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
                Helper.removeValidation(Specialists.selectors.modalAddSelector)
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

                if (json.success) location.reload()
            }
        })
    },
    delete: function () {
        let id = $(this).attr('data-id'),
            button = $(this)

        if (confirm('Вы уверены, что хотите удалить специалиста?')) {
            $.ajax({
                type: "POST",
                url: "actions/specialist/delete_specialists.php",
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
    file: function () {
        $(Specialists.selectors.photoSelector).click()
    },
    classification: function () {
        let type = +$(this).children(`option[value=${$(this).val()}]`).attr('data-type')
        $(this).siblings('#input-type').val(type)

        if (type === 1) $('#input-company').css('display', 'none')
        if (type === 2) $('#input-company').css('display', 'block')
    }
}

export {
    Specialists
}
import {
    Helper
} from "./helper.js";

let FaqOptions = {
    selectors: {
        'pageSelector': '#faqPage',
        'modalEditSelector': '#modalEditFaqPages',
        'contentSelector': '.input-content',
    },
    start: function () {
        $('#faq').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })
        $('#faqPromo').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })
        $('#faqProPhoto').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })
        $(this.selectors.pageSelector).on('click', '.modalEdit', this.modalEdit)
        $(this.selectors.pageSelector).on('click', '#faqPagesEdit', this.edit)
        $(this.selectors.pageSelector).on('click', '.modalEditFaqPromo', this.modalEditPromo)
        $(this.selectors.pageSelector).on('click', '.delete', this.delete)
        $(this.selectors.pageSelector).on('click', '.modalEditFaqProPhoto', this.modalEditProPhoto)
    },
    edit: function () {
        let button = $(this),
            buttonContent = button.html(),
            type = button.attr('data-type') ?? 0;
            console.log(type);
    
        // Создаем объект FormData для отправки данных формы
        let formData = new FormData();

        formData.append('type', type);
        
        $(`${FaqOptions.selectors.modalEditSelector} input, ${FaqOptions.selectors.modalEditSelector} textarea`).each(function() {
        formData.append($(this).attr('name'), $(this).val());
        });

        formData.append('content', tinymce.get(FaqOptions.selectors.contentSelector.replace('#', '')).getContent());
    
        $.ajax({
            type: "POST",
            url: "actions/faq/edit_faq.php",
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                Helper.loadingButton(button);
                Helper.removeValidation(FaqOptions.selectors.modalEditSelector);
            },
            success: function (json) {
                Helper.loadingButton(button, buttonContent)
                if (json.error) {
                    $.each(json.error, function (key, value) {
                        $(`#input-${key}`).addClass('is-invalid').after(Helper.invalidFeedback(value))
                    });
                }
                if (json.fatal) Helper.fatalError(json.fatal)
                /* if (json.success) location.reload() */
            }
        });
    },    
    initTinyMCE: function () {
        if (typeof tinymce !== 'undefined') {
            
            tinymce.remove(`${FaqOptions.selectors.contentSelector}`);
    
            tinymce.init({
                selector: `${FaqOptions.selectors.contentSelector}`,
                plugins: 'lists link code advlist',
                relative_urls: false,
                readonly: false,
                toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link | code',
                branding: false,
                extended_valid_elements: 'div[class|style],span[class|style]', // Разрешить дополнительные элементы с атрибутами
                valid_children: '+body[style]', // Разрешить стили в body
                content_style: `
                    body { 
                        font-family: Helvetica, Arial, sans-serif; 
                        font-size: 14px; 
                    } 
                    .img-wrapper { 
                        display: block; 
                        width: 100%; 
                        height: 670px; 
                        border-radius: 16px; 
                        background-position: center; 
                        background-size: cover; 
                        background-repeat: no-repeat; 
                        margin-bottom: 32px; 
                    }
                `,
                block_formats: 'Header 1=h1; Header 2=h2; Header 3=h3; Paragraph=p',
                style_formats: [
                    { title: 'Header 1', block: 'h1' },
                    { title: 'Header 2', block: 'h2' },
                    { title: 'Header 3', block: 'h3' },
                    { title: 'Paragraph', block: 'p' },
                    { title: 'Custom Div', block: 'div', classes: 'custom-class' }
                ],
                setup: function (editor) {
                    editor.on('init', function () {
                        console.log('Editor initialized');
                    });
                }
            });
            
        } else {
            console.error('TinyMCE не загружен.');
        }
    },    
    modalEdit: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html()
            console.log(id);
        $.ajax({
            type: "POST",
            url: "actions/faq/get_modal.php",
            dataType: 'json',
            data: {id: id},
            beforeSend: function () {
                Helper.loadingButton(button)
                $(FaqOptions.selectors.modalEditSelector).remove()
            },
            success: function (json) {
                $(FaqOptions.selectors.pageSelector).append(json.code)
                $(FaqOptions.selectors.modalEditSelector).modal('show')
                Helper.loadingButton(button, buttonContent)
                FaqOptions.initTinyMCE();
            }
        })
    },  
    modalEditPromo: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html()
            
            console.log(id);
        $.ajax({
            type: "POST",
            url: "actions/faq/get_modal_promo.php",
            dataType: 'json',
            data: {id: id},
            beforeSend: function () {
                Helper.loadingButton(button)
                $(FaqOptions.selectors.modalEditSelector).remove()
            },
            success: function (json) {
                $(FaqOptions.selectors.pageSelector).append(json.code)
                $(FaqOptions.selectors.modalEditSelector).modal('show')
                Helper.loadingButton(button, buttonContent)
                FaqOptions.initTinyMCE();
            }
        })
    },
    modalEditProPhoto: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html()
            
            console.log(id);
        $.ajax({
            type: "POST",
            url: "actions/faq/get_modal_pro_photo.php",
            dataType: 'json',
            data: {id: id},
            beforeSend: function () {
                Helper.loadingButton(button)
                $(FaqOptions.selectors.modalEditSelector).remove()
            },
            success: function (json) {
                $(FaqOptions.selectors.pageSelector).append(json.code)
                $(FaqOptions.selectors.modalEditSelector).modal('show')
                Helper.loadingButton(button, buttonContent)
                FaqOptions.initTinyMCE();
            }
        })
    }, 
    delete: function () {
        let id = $(this).attr('data-id'),
            button = $(this),
        type = button.attr('data-type') ?? 0;


        if (confirm('Вы уверены, что хотите удалить пост?')) {
            $.ajax({
                type: "POST",
                url: "actions/faq/delete.php",
                dataType: 'json',
                data: {id: id, type: type},
                beforeSend: function () {
                    Helper.loadingButton(button)
                },
                success: function (json) {
                    if (json.success) location.reload()
                }
            })
        }
    }
};

export {
    FaqOptions
}
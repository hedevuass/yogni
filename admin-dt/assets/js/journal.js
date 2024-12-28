import {
    Helper
} from "./helper.js";

let Journal = {
    selectors: {
        'pageSelector': '#journalPage',
        'modalAddSelector': '#modalAddJournal',
        'contentSelector': '#input-content',
        'titleSelector': '#input-title',
        'photoSelector': '#input-img',
    },
    start: function () {
        $('#journals').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
            'order': [[5, 'desc']],
        })

        $(this.selectors.pageSelector).on('click', '.edit', this.edit)
        $(this.selectors.pageSelector).on('click', '.delete', this.delete)
        $(this.selectors.pageSelector).on('click', '.modalAdd', this.modalAdd)
        $(this.selectors.pageSelector).on('click', '.upload-avatar', this.file)
        $(this.selectors.pageSelector).on('change', this.selectors.photoSelector, e => Helper.uploadImage('.avatar', e))
    },
    file: function () {
        $(Journal.selectors.photoSelector).click()
    },
    modalAdd: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html();
            
        console.log(id);
        
        $.ajax({
            type: "POST",
            url: "actions/journal/get_modal.php",
            dataType: 'json',
            data: {id: id},
            beforeSend: function () {
                Helper.loadingButton(button);
                $(Journal.selectors.modalAddSelector).remove(); 
            },
            success: function (json) {
                $(Journal.selectors.pageSelector).append(json.code);
                
                $(Journal.selectors.modalAddSelector).modal('show');
                
                Journal.initTinyMCE();
                
                Helper.loadingButton(button, buttonContent);
            }
        });
    },
    initTinyMCE: function () {
        if (typeof tinymce !== 'undefined') {
            
            tinymce.remove(`${Journal.selectors.contentSelector}, ${Journal.selectors.titleSelector}`);

            tinymce.init({
                selector: `${Journal.selectors.contentSelector}, ${Journal.selectors.titleSelector}`,
                plugins: 'lists link',
                toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link',
                
                branding: false,
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                block_formats: 'Header 1=h1; Header 2=h2; Header 3=h3; Paragraph=p',
                style_formats: [
                    { title: 'Header 1', block: 'h1' },
                    { title: 'Header 2', block: 'h2' },
                    { title: 'Header 3', block: 'h3' },
                    { title: 'Paragraph', block: 'p' }
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
    edit: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html();
    
            let formData = new FormData();

            $(`${Journal.selectors.modalAddSelector} select, ${Journal.selectors.modalAddSelector} input, ${Journal.selectors.modalAddSelector} textarea`).each(function() {
                if ($(this).attr('type') === 'file') {
                    if ($(this)[0].files[0]) {
                        formData.append('photo', $(this)[0].files[0]);
                    }
                } else {
                    formData.append($(this).attr('name'), $(this).val());
                }
            });

            formData.append('content', tinymce.get(Journal.selectors.contentSelector.replace('#', '')).getContent());
            formData.append('title', tinymce.get(Journal.selectors.titleSelector.replace('#', '')).getContent());
    
        $.ajax({
            type: "POST",
            url: "actions/journal/edit_journal.php",
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                Helper.loadingButton(button);
                Helper.removeValidation(Journal.selectors.pageSelector);
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
    check_out: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            title = button.attr('data-title') ?? '',
            content = button.attr('data-content') ?? '',
            buttonContent = button.html();
    
            let forbiddenWords = [
                'дурак', 'идиот', 'тупица', 'кретин', 'ублюдок', 'сволочь', 'тварь', 
                'мерзавец', 'козёл', 'недоумок', 'придурок', 'паразит', 'хам', 'скотина', 
                'шлюха', 'проститутка', 'мразь', 'гнида', 'сучка', 'извращенец', 'насильник', 
                'маньяк', 'черт', 'свинья', 'бараны', 'дебилы', 'ненормальные', 'психи', 
                'кретины', 'ублюдки', 'засранец', 'гадина', 'ссученый', 'выблядок', 'петух', 
                'отброс', 'подонок', 'блядь', 'еблан', 'мудак', 'пидор', 'сука', 'хуесос', 
                'гандон', 'хер', 'пизда', 'хуй', 'ебать', 'срака', 'засранец', 'чмо', 'гондон', 
                'еблан', 'пидорас', 'шалава', 'тупорылый', 'даун', 'ущербный', 'аутист', 
                'сдохни', 'сгинь', 'урод', 'ублюдочная', 'наркоман', 'алкаш', 'гнида', 
                'скот', 'недоумок', 'засранец', 'убогий', 'выродок', 'чмошник', 'подстилка', 
                'ничтожество', 'быдло', 'мразота', 'смерд', 'ублюдочный', 'дебилоид', 
                'сосун', 'глист', 'подстилка', 'западло', 'сучара', 'падла', 'чурка', 
                'хач', 'петух', 'залупа', 'говно', 'пидорасина', 'пидрила', 'тварь', 
                'ссанина', 'выскочка', 'бараны', 'болваны', 'проститутки', 'сукины', 
                'зажравшийся', 'дармоед', 'падаль', 'сопляк', 'мусор'
            ];
            
    
        function containsForbiddenWords(text, forbiddenWords) {
            for (let word of forbiddenWords) {
                if (text.toLowerCase().includes(word.toLowerCase())) {
                    return true;
                }
            }
            return false;
        }
    
        let titleHasForbidden = containsForbiddenWords(title, forbiddenWords);
        let contentHasForbidden = containsForbiddenWords(content, forbiddenWords);
    
        if (titleHasForbidden || contentHasForbidden) {
            alert('В заголовке или содержимом обнаружены запрещённые слова.');
            return;
        }
    
        button.replaceWith(`
            <button type="submit" class="btn btn-info edit" data-id="${id}">
                Отправить
            </button>
        `);
    },    
    delete: function () {
        let id = $(this).attr('data-id'),
            button = $(this)

        if (confirm('Вы уверены, что хотите удалить пост?')) {
            $.ajax({
                type: "POST",
                url: "actions/posts/delete_post.php",
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
    Journal
}
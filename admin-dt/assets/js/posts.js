import {
    Helper
} from "./helper.js";

let Posts = {
    selectors: {
        'pageSelector': '#postsPage',
        /* 'modalEditSelector': '#modalEditFaqPages', */
    },
    start: function () {
        $('#posts').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })

        /* $(this.selectors.pageSelector).on('click', '.modalEdit', this.modalEdit) */
        $(this.selectors.pageSelector).on('click', '.check-out', this.check_out)
        $(this.selectors.pageSelector).on('click', '.edit', this.edit)
        $(this.selectors.pageSelector).on('click', '.delete', this.delete)
    },
    edit: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html();
    
        // Создаем объект FormData для отправки данных формы
        let formData = new FormData();
       
        formData.append('id', id);
    
        $.ajax({
            type: "POST",
            url: "actions/posts/edit_post.php",
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                Helper.loadingButton(button);
                Helper.removeValidation(Posts.selectors.pageSelector);
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
                'дурак', 'запрещенное', 'слово', 'идиот', 'тупица', 'кретин', 'ублюдок', 'сволочь', 'тварь', 
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
                    return word;
                }
            }
            return false;
        }
    
        let titleHasForbidden = containsForbiddenWords(title, forbiddenWords);
        let contentHasForbidden = containsForbiddenWords(content, forbiddenWords);
    
        if (titleHasForbidden) {
            alert(`В заголовке обнаружено запрещённое слово: "${titleHasForbidden}".`);
            return;
        }
        
        if (contentHasForbidden) {
            alert(`В содержимом обнаружено запрещённое слово: "${contentHasForbidden}".`);
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
    /* modalEdit: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html()
            console.log(id);
        $.ajax({
            type: "POST",
            url: "actions/posts/get_modal.php",
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
            }
        })
    } */
};

export {
    Posts
}
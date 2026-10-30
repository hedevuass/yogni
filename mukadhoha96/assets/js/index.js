import {
    Helper
} from "./helper.js";

let MainPage = {
    selectors: {
        'pageSelector': '#mainPage',
        'modalEditSelector': '#modalEditDocument',
        'togglePasswordButton': '.toggle-password',
        'contentSelector': '#input-content',
        'formSelector': 'form[action="actions/settings/update_settings.php"]',
        'formSelectorAdmin': 'form[action="actions/settings/update_main_user.php"]',
        'alertContainerAdmin': '#alert-container-admin',
        'alertContainer': '#alert-container'
    },
    start: function () {
        $('#mainTable').dataTable({
            language: Helper.dataTableLanguage(),
            'iDisplayLength': 50,
        })

        $(this.selectors.pageSelector).on('click', this.selectors.togglePasswordButton, function () {
            const target = $(this).data('target');
            const input = $(target);
            const type = input.attr('type') === 'password' ? 'text' : 'password';
            input.attr('type', type);
            $(this).text(type === 'password' ? 'Показать' : 'Скрыть');
        });

        $(this.selectors.pageSelector).on('click', '.modalAdd', this.modalEdit)
        $(this.selectors.pageSelector).on('click', '#documentEdit', this.edit)
        $(this.selectors.pageSelector).on('click', '.delete', this.delete)
        $(this.selectors.pageSelector).on('click', '.saveForbiddentWords', this.saveForbiddentWords)
        $(this.selectors.pageSelector).on('submit', this.selectors.formSelector, this.submitForm);
        $(this.selectors.pageSelector).on('submit', this.selectors.formSelectorAdmin, this.submitAdminForm);
    },
    initTinyMCE: function () {
        if (typeof tinymce !== "undefined") {
          tinymce.remove(`${MainPage.selectors.contentSelector}`);
    
          tinymce.init({
            selector: `${MainPage.selectors.contentSelector}`,
            plugins: "lists autolink code", // удалили 'link'
            toolbar:
              "undo redo | formatselect | fontselect fontsizeselect | bold italic | alignleft aligncenter alignright | bullist numlist",
            branding: false,
            content_css:
              "https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap",
            content_style: `
                        body, p, h1, h2, h3, div, span {
                            font-family: Roboto, sans-serif !important;
                            font-size: 14px !important;
                        }
                    `,
    
            font_family_formats:
              "Roboto=Roboto, sans-serif;Arial=arial,helvetica,sans-serif;Courier New=courier new,courier;",
            font_size_formats:
              "8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 17pt 18pt 19pt 20pt 24pt 36pt 48pt",
            block_formats: "Header 1=h1; Header 2=h2; Header 3=h3; Paragraph=p",
            style_formats: [
              { title: "Header 1", block: "h1" },
              { title: "Header 2", block: "h2" },
              { title: "Header 3", block: "h3" },
              { title: "Paragraph", block: "p" },
            ],
            force_br_newlines: true,
            force_p_newlines: false,
            forced_root_block: "div",
            invalid_elements: "div div",
    
            // Отключаем настройки, связанные со ссылками
            relative_urls: false,
            remove_script_host: false,
            convert_urls: false,
            readonly: false,
            paste_preprocess: function (plugin, args) {
              let content = args.content;

              // Преобразуем списки и блоки в переносы строк <br>, чтобы текст не слипался
              content = content.replace(/<\/li>\s*<li[^>]*>/gi, "<br>");
              content = content.replace(/<\/(p|div|h1|h2|h3|h4|h5|h6|li)>/gi, "<br>");
              content = content.replace(/<\/?(ul|ol)[^>]*>/gi, "");

              const allowedTags = "b|strong|i|em|u|a|br";
              const regex = new RegExp(
                `<(?!\\/?(${allowedTags})(\\s|>))[^>]+>`,
                "gi"
              );
              content = content.replace(regex, "");

              // Очищаем ссылки, оставляя только href
              content = content.replace(
                /<a\b[^>]*href\s*=\s*["']([^"']*)["'][^>]*>/gi,
                '<a href="$1">'
              );

              // Чистим неразрывные пробелы и дублирующиеся переносы
              args.content = content
                .replace(/&nbsp;/g, " ")
                .replace(/(<br\s*\/?>\s*)+/gi, "<br>")
                .trim();
            },
            setup: function (editor) {
              let modalEl = document.querySelector(
                MainPage.selectors.modalEditSelector
              );
              let modalInstance = modalEl
                ? bootstrap.Modal.getInstance(modalEl)
                : null;
    
              function disableFocusTrap() {
                if (modalInstance && modalInstance._focustrap) {
                  modalInstance._focustrap.deactivate();
                }
              }
    
              function enableFocusTrap() {
                if (modalInstance && modalInstance._focustrap) {
                  modalInstance._focustrap.activate();
                }
              }
    
              function normalizeRootDiv() {
                const body = editor.getBody();
    
                let changed = true;
                while (changed) {
                  changed = false;
                  Array.from(body.querySelectorAll("div")).forEach((div) => {
                    if (
                      div.parentElement &&
                      div.parentElement !== body &&
                      div.parentElement.tagName === "DIV"
                    ) {
                      const parent = div.parentElement;
                      parent.parentNode.insertBefore(div, parent.nextSibling);
                      changed = true;
                    }
                  });
                }
    
                Array.from(body.querySelectorAll("div")).forEach((div) => {
                  if (
                    !div.textContent.trim() &&
                    div.querySelectorAll("img").length === 0
                  ) {
                    div.remove();
                  }
                });
              }
    
              editor.on("OpenWindow", function () {
                disableFocusTrap();
                setTimeout(() => {
                  const input = document.querySelector(".tox-dialog input");
                  if (input) input.focus();
                }, 50);
              });
    
              editor.on("PastePostProcess", normalizeRootDiv);
    
              editor.on("SetContent", normalizeRootDiv);
    
              editor.on("CloseWindow", function () {
                enableFocusTrap();
              });
    
              editor.on("init", function () {
                console.log("Editor initialized");
              });
            },
          });
        } else {
          console.error("TinyMCE не загружен.");
        }
      },
    submitForm: function (event) {
        event.preventDefault();
        let form = $(this);
        let formData = form.serialize();
        let alertContainer = $(MainPage.selectors.alertContainer);

        $.ajax({
            type: "POST",
            url: form.attr("action"),
            data: formData,
            dataType: "json",
            beforeSend: function () {
                form.find("button[type='submit']").prop("disabled", true);
            },
            success: function (response) {
                form.find("button[type='submit']").prop("disabled", false);
                alertContainer.html(`<div class="alert alert-${response.status === 'success' ? 'success' : 'danger'}">${response.message}</div>`);
            },
            error: function () {
                alertContainer.html(`<div class="alert alert-danger">Произошла ошибка запроса.</div>`);
                form.find("button[type='submit']").prop("disabled", false);
            }
        });
    },
    submitAdminForm: function (event) {
        event.preventDefault();
        let form = $(this);
        let formData = form.serialize();
        let alertContainer = $(MainPage.selectors.alertContainerAdmin);

        $.ajax({
            type: "POST",
            url: form.attr("action"),
            data: formData,
            dataType: "json",
            beforeSend: function () {
                form.find("button[type='submit']").prop("disabled", true);
            },
            success: function (response) {
                form.find("button[type='submit']").prop("disabled", false);
                alertContainer.html(`<div class="alert alert-${response.status === 'success' ? 'success' : 'danger'}">${response.message}</div>`);
            },
            error: function () {
                alertContainer.html(`<div class="alert alert-danger">Произошла ошибка запроса.</div>`);
                form.find("button[type='submit']").prop("disabled", false);
            }
        });
    },
    modalEdit: function () {
        let button = $(this),
            id = button.attr('data-id') ?? 0,
            buttonContent = button.html()

        $.ajax({
            type: "POST",
            url: "actions/documents/get_modal.php",
            dataType: 'json',
            data: {id: id},
            beforeSend: function () {
                Helper.loadingButton(button)
                $(MainPage.selectors.modalEditSelector).remove()
            },
            success: function (json) {
                $(MainPage.selectors.pageSelector).append(json.code)
                $(MainPage.selectors.modalEditSelector).modal('show')
                Helper.loadingButton(button, buttonContent)
                MainPage.initTinyMCE();
            }
        })
    },
    edit: function () {
        let button = $(this),
            buttonContent = button.html();
    
        // Создаем объект FormData для отправки данных формы
        let formData = new FormData();
        
        $(`${MainPage.selectors.modalEditSelector} input, ${MainPage.selectors.modalEditSelector} textarea`).each(function() {
        formData.append($(this).attr('name'), $(this).val());
        });

        formData.append('content', tinymce.get(MainPage.selectors.contentSelector.replace('#', '')).getContent());
    
        $.ajax({
            type: "POST",
            url: "actions/documents/edit_doc.php",
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                Helper.loadingButton(button);
                Helper.removeValidation(MainPage.selectors.modalEditSelector);
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
    saveForbiddentWords: function (e) {
        e.preventDefault();
        let words = $("#forbidden-words").val().trim();

        $.ajax({
            type: "POST",
            url: "actions/settings/save_forbidden_words.php",
            data: { forbidden_words: words },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                } else {
                    alert(response.error);
                }
            },
            error: function () {
                alert("Ошибка при сохранении запрещённых слов");
            }
        });
    },
    delete: function () {
        let id = $(this).attr('data-id'),
            button = $(this)

        if (confirm('Вы уверены, что хотите удалить пост?')) {
            $.ajax({
                type: "POST",
                url: "actions/documents/delete.php",
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
    MainPage
}
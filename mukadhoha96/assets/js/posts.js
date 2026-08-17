import { Helper } from "./helper.js";

let Posts = {
  selectors: {
    pageSelector: "#postsPage",
    modalAddSelector: "#modalAddPost",
    contentSelector: "#input-content",
    titleSelector: "#input-title",
    tableSelector: "#posts",
    filterButtons: ".filter-btn",
    modalEditSelector: "#modalAddPost",
    photoSelector: "#input-img",
    existingPhotoSelector: "#input-existing-photo",
    deletePhotoSelector: ".delete-avatar",
  },
  start: function () {
    $("#posts").dataTable({
      language: Helper.dataTableLanguage(),
      iDisplayLength: 50,
      order: [[0, "desc"]],
    });

    $(this.selectors.pageSelector).on("change", "#filterSelect", function () {
      let filterValue = $(this).val();

      $("#posts tbody tr").each(function () {
        let row = $(this);
        let isPublished = row.hasClass("highlight");

        if (
          filterValue === "all" ||
          (filterValue === "published" && !isPublished) ||
          (filterValue === "unpublished" && isPublished)
        ) {
          row.show();
        } else {
          row.hide();
        }
      });
    });

    /* $(this.selectors.pageSelector).on('click', '.modalEdit', this.modalEdit) */
    $(this.selectors.pageSelector).on("click", ".check-out", this.check_out);
    $(this.selectors.pageSelector).on("click", ".edit", this.edit);
    $(this.selectors.pageSelector).on("click", ".publish", this.publish);
    $(this.selectors.pageSelector).on("click", ".modalAdd", this.modalAdd);
    $(this.selectors.pageSelector).on("click", ".delete", this.delete);
    $(this.selectors.pageSelector).on("click", ".upload-avatar", this.file);
    $(this.selectors.pageSelector).on(
        "change",
        this.selectors.photoSelector,
        function (e) {
          if (this.files && this.files[0]) {
              Helper.uploadImage(".avatar", e);
              
              $(".upload-avatar").hide();
              $(Posts.selectors.deletePhotoSelector).show();
          }
        }
      );
      $(this.selectors.pageSelector).on(
        "click",
        this.selectors.deletePhotoSelector,
        function (e) {
          e.preventDefault();
      
          $(Posts.selectors.photoSelector).val("");
          $(Posts.selectors.existingPhotoSelector).val("");
          $(".avatar").attr("src", "/uploads/no-image.png");
      
          $(this).hide();
          $(".upload-avatar").show();
        }
      );
  },
  initModalListeners: function () {
    const metaTitleInput = document.getElementById("input-meta-title");
    const metaDescriptionInput = document.getElementById(
      "input-meta-description"
    );
    const slugInput = document.getElementById("input-alias");

    function transliterate(text) {
      const ru = {
        А: "A",
        Б: "B",
        В: "V",
        Г: "G",
        Д: "D",
        Е: "E",
        Ё: "YO",
        Ж: "ZH",
        З: "Z",
        И: "I",
        Й: "J",
        К: "K",
        Л: "L",
        М: "M",
        Н: "N",
        О: "O",
        П: "P",
        Р: "R",
        С: "S",
        Т: "T",
        У: "U",
        Ф: "F",
        Х: "H",
        Ц: "TS",
        Ч: "CH",
        Ш: "SH",
        Щ: "SCH",
        Ъ: "",
        Ы: "Y",
        Ь: "",
        Э: "E",
        Ю: "YU",
        Я: "YA",
        а: "a",
        б: "b",
        в: "v",
        г: "g",
        д: "d",
        е: "e",
        ё: "yo",
        ж: "zh",
        з: "z",
        и: "i",
        й: "j",
        к: "k",
        л: "l",
        м: "m",
        н: "n",
        о: "o",
        п: "p",
        р: "r",
        с: "s",
        т: "t",
        у: "u",
        ф: "f",
        х: "h",
        ц: "ts",
        ч: "ch",
        ш: "sh",
        щ: "sch",
        ъ: "",
        ы: "y",
        ь: "",
        э: "e",
        ю: "yu",
        я: "ya",
      };
      return text
        .split("")
        .map((char) => (ru[char] !== undefined ? ru[char] : char))
        .join("");
    }

    function stripHtmlTags(html) {
      return html.replace(/<\/?[^>]+(>|$)/g, "").trim();
    }

    function decodeHTMLEntities(text) {
      let doc = new DOMParser().parseFromString(text, "text/html");
      return doc.documentElement.textContent;
    }

    // Функция для генерации slug на английском
    function generateSlug(text) {
      text = transliterate(text);
      text = decodeHTMLEntities(text);
      text = stripHtmlTags(text);
      return text
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, "") // удаляем все не буквенно-цифровые символы
        .replace(/\s+/g, "-") // заменяем пробелы на дефисы
        .replace(/-+/g, "-"); // убираем повторяющиеся дефисы
    }

    // Функция для генерации мета описания из контента (без HTML)
    function generateMetaDescription(htmlContent) {
      let decodedContent = decodeHTMLEntities(htmlContent); // Декодируем сущности
      let textContent = stripHtmlTags(decodedContent); // Убираем HTML-теги
      return textContent.length > 150
        ? textContent.substring(0, 150) + "..."
        : textContent;
    }

    // Обработчик получения контента из TinyMCE и генерации мета-описания
    if (
      typeof tinymce !== "undefined" &&
      tinymce.get(Posts.selectors.contentSelector.replace("#", ""))
    ) {
      tinymce
        .get(Posts.selectors.contentSelector.replace("#", ""))
        .on("change", function () {
          const contentHtml = tinymce
            .get(Posts.selectors.contentSelector.replace("#", ""))
            .getContent();
          metaDescriptionInput.value = generateMetaDescription(contentHtml);
        });
      tinymce
        .get(Posts.selectors.titleSelector.replace("#", ""))
        .on("change", function () {
          const contentHtml = tinymce
            .get(Posts.selectors.titleSelector.replace("#", ""))
            .getContent();
          if (slugInput) {
            slugInput.value = generateSlug(contentHtml);
          }
          metaTitleInput.value = generateMetaDescription(contentHtml);
        });
    }
  },
  initTinyMCE: function () {
    if (typeof tinymce !== "undefined") {
      // Удаляем старые редакторы перед повторной инициализацией
      tinymce.remove(
        `${Posts.selectors.contentSelector}, ${Posts.selectors.titleSelector}`
      );

      // Инициализируем отдельно для каждого поля
      [Posts.selectors.contentSelector, Posts.selectors.titleSelector].forEach(
        (selector) => {
          const el = document.querySelector(selector);
          if (!el) return;

          const isTitle = selector === Posts.selectors.titleSelector;

          tinymce.init({
            target: el,
            height: isTitle ? 170 : 450, // Выравниваем высоту по стандарту (170 для заголовка)

            // Жесткое разделение на абзацы при нажатии Enter
            force_br_newlines: false,
            force_p_newlines: true,
            forced_root_block: "p",

            // Плагины (в TinyMCE 6+ плагин paste встроен по умолчанию)
            plugins: isTitle
              ? "lists autolink code link"
              : "lists autolink code link image",

            paste_as_text: false,

            // Умная обработка вставляемого текста (из буфера)
            paste_preprocess: function (plugin, args) {
              let content = args.content;

              if (!isTitle) {
                // 1. Превращаем двойные переносы строк в закрытие и открытие абзаца
                content = content.replace(
                  /<br\s*\/?>\s*<br\s*\/?>/gi,
                  "</p><p>"
                );
                // 2. Одиночные переносы строк тоже изолируем в независимые абзацы
                content = content.replace(/<br\s*\/?>/gi, "</p><p>");
              }

              // Список разрешенных тегов
              const allowedTags = isTitle
                ? "p|br"
                : "p|br|ul|ol|li|h1|h2|h3|blockquote|img";

              // Очищаем все остальные неразрешенные теги
              const regex = new RegExp(
                `<(?!\\/?(${allowedTags})(\\s|>))[^>]+>`,
                "gi"
              );

              args.content = content
                .replace(regex, "")
                .replace(/&nbsp;/g, " ")
                .replace(/<p>\s*<\/p>/g, "") // Вычищаем пустые абзацы
                .trim();
            },

            // Кнопки управления в строгом порядке
            toolbar: isTitle
              ? "undo redo | formatselect | fontselect fontsizeselect"
              : "undo redo formatselect fontselect fontsizeselect alignleft aligncenter alignright h2 h3 blockquote bullist numlist bold italic underline strikethrough image link code",

            branding: false,
            relative_urls: false,
            remove_script_host: false,
            convert_urls: false,
            readonly: false,

            content_css:
              "https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap",

            // Стилизация текста внутри редактора
            content_style: `
                        body, p, h1, h2, h3, div, span, blockquote {
                            font-family: Roboto, sans-serif !important;
                            font-size: 14px !important;
                        }
                        blockquote {
                            border-left: 3px solid #F8A3AA;
                            margin-left: 1.5rem;
                            padding-left: 1rem;
                            font-style: italic;
                        }
                        img {
                            width: 100% !important; 
                            height: auto !important;
                            aspect-ratio: 16 / 9 !important; 
                            object-fit: cover !important; 
                            border-radius: 16px !important; 
                            display: block !important; 
                            margin: 10px 0 !important;
                        }
                    `,

            font_family_formats:
              "Roboto=Roboto, sans-serif;Arial=arial,helvetica,sans-serif;Courier New=courier new,courier;",
            font_size_formats:
              "8pt 9pt 10pt 11pt 12pt 13pt 14pt 15pt 16pt 17pt 18pt 19pt 20pt 24pt 36pt 48pt",
            block_formats:
              "Header 1=h1; Header 2=h2; Header 3=h3; Paragraph=p; Blockquote=blockquote",

            style_formats: [
              { title: "Header 1", block: "h1" },
              { title: "Header 2", block: "h2" },
              { title: "Header 3", block: "h3" },
              { title: "Paragraph", block: "p" },
              { title: "Blockquote", block: "blockquote" },
            ],

            image_title: true,
            image_description: true,
            automatic_uploads: true,
            file_picker_types: "image",
            image_dimensions: false,

            // Асинхронный загрузчик изображений (исправлен URL для v6)
            images_upload_handler: function (
              blobInfo,
              success,
              failure,
              progress
            ) {
              var isV6 = parseInt(tinymce.majorVersion) >= 6;

              if (isV6) {
                return new Promise(function (resolve, reject) {
                  var formData = new FormData();
                  formData.append("file", blobInfo.blob(), blobInfo.filename());

                  // ИСПРАВЛЕНО: Теперь запрос идет в posts, а не в journal
                  fetch("actions/posts/upload_img.php", {
                    method: "POST",
                    body: formData,
                  })
                    .then(function (response) {
                      if (!response.ok)
                        throw new Error("HTTP статус " + response.status);
                      return response.json();
                    })
                    .then(function (result) {
                      if (result && result.location) {
                        resolve(result.location);
                      } else {
                        reject(result.error || "Неверный ответ от сервера");
                      }
                    })
                    .catch(function (error) {
                      reject("Ошибка: " + error.message);
                    });
                });
              } else {
                var formData = new FormData();
                formData.append("file", blobInfo.blob(), blobInfo.filename());

                fetch("actions/posts/upload_img.php", {
                  method: "POST",
                  body: formData,
                })
                  .then(function (response) {
                    if (!response.ok)
                      throw new Error("HTTP статус " + response.status);
                    return response.json();
                  })
                  .then(function (result) {
                    if (result && result.location) {
                      success(result.location);
                    } else {
                      failure(result.error || "Неверный ответ от сервера");
                    }
                  })
                  .catch(function (error) {
                    failure("Ошибка: " + error.message);
                  });
              }
            },

            setup: function (editor) {
              // Работа с фокусом модального окна Bootstrap
              let modalEl = document.querySelector(
                Posts.selectors.modalEditSelector
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

              editor.on("OpenWindow", function () {
                disableFocusTrap();
                setTimeout(() => {
                  const inputInDialog =
                    document.querySelector(".tox-dialog input");
                  if (inputInDialog) inputInDialog.focus();
                }, 50);
              });

              editor.on("OpenWindow", function (e) {
                var tabs = document.querySelectorAll(".tox-tab");
                tabs.forEach(function (tab) {
                  if (tab.textContent.trim() === "Upload") {
                    tab.click();
                  }
                });
              });

              editor.on("CloseWindow", function () {
                enableFocusTrap();
              });

              // Чистый лог инициализации без принудительной мутации DOM
              editor.on("init", function () {
                console.log("Editor initialized");
              });
            },
          });
        }
      );
    } else {
      console.error("TinyMCE не загружен.");
    }
  },
  file: function () {
    $(Posts.selectors.photoSelector).click();
  },
  modalAdd: function () {
    let button = $(this),
      id = button.attr("data-id") ?? 0,
      buttonContent = button.html();

    console.log(id);

    $.ajax({
      type: "POST",
      url: "actions/posts/get_modal.php",
      dataType: "json",
      data: { id: id },
      beforeSend: function () {
        Helper.loadingButton(button);
        $(Posts.selectors.modalAddSelector).remove();
      },
      success: function (json) {
        $(Posts.selectors.pageSelector).append(json.code);
        $(Posts.selectors.modalAddSelector).modal("show");
        Posts.initTinyMCE();
        Posts.initModalListeners();
        Helper.loadingButton(button, buttonContent);
      },
    });
  },
  publish: function () {
    let button = $(this),
      email = button.attr("data-email"),
      buttonContent = button.html();
    let id = $("#input-id").val();
    let moderation = $("#input-comment").val();

    // Создаем объект FormData для отправки данных формы
    let formData = new FormData();

    formData.append("id", id);
    formData.append("email", email);
    formData.append("moderation", moderation);

    $.ajax({
      type: "POST",
      url: "../actions/mail/revision.php",
      dataType: "json",
      data: formData,
      processData: false,
      contentType: false,
      beforeSend: function () {
        Helper.loadingButton(button);
        Helper.removeValidation(Posts.selectors.pageSelector);
      },
      success: function (json) {
        Helper.loadingButton(button, buttonContent);
        if (json.error) {
          $.each(json.error, function (key, value) {
            $(`#input-${key}`)
              .addClass("is-invalid")
              .after(Helper.invalidFeedback(value));
          });
        }
        if (json.fatal) Helper.fatalError(json.fatal);
        if (json.success) location.reload();
      },
    });

    let table_name = button.data("table-name");
    let post_id = button.data("id");
    let formDataPost = new FormData();
    let status = "2";
    formDataPost.append("id", post_id);
    formDataPost.append("table", table_name);
    formDataPost.append("status", status);

    $.ajax({
      type: "POST",
      url: "actions/update_status.php",
      dataType: "json",
      data: formDataPost,
      processData: false,
      contentType: false,
      success: function (updateJson) {
        if (updateJson.error) {
          $.each(updateJson.error, function (key, value) {
            // Обработка ошибок второго запроса
            $(`#input-${key}`)
              .addClass("is-invalid")
              .after(Helper.invalidFeedback(value));
          });
        }
        if (updateJson.fatal) Helper.fatalError(updateJson.fatal);

        // Обработка успешного второго запроса
        if (updateJson.success) {
          /* location.reload(); */
        }
      },
    });
  },
  edit: function () {
    let button = $(this),
      id = button.attr("data-id") ?? 0,
      moderation = button.attr("data-moderation"),
      buttonContent = button.html();

    // Получаем экземпляр редактора контента для Posts
    const contentEditor = tinymce.get(
      Posts.selectors.contentSelector.replace("#", "")
    );

    // Выносим отправку данных в отдельную функцию, чтобы вызвать её после загрузки картинок
    function sendFormData() {
      let formData = new FormData();

      $(
        `${Posts.selectors.modalAddSelector} select, ${Posts.selectors.modalAddSelector} input, ${Posts.selectors.modalAddSelector} textarea`
      ).each(function () {
        if ($(this).attr("type") === "file") {
          if ($(this)[0].files[0]) {
            formData.append("photo", $(this)[0].files[0]);
          }
        } else {
          formData.append($(this).attr("name"), $(this).val());
        }
      });

      // Теперь здесь гарантированно будут нормальные постоянные пути к картинкам
      formData.append("content", contentEditor.getContent());

      formData.append(
        "title",
        tinymce.get(Posts.selectors.titleSelector.replace("#", "")).getContent()
      );

      // Сохраняем передачу статуса модерации, специфичного для Posts
      formData.append("moderation", moderation);

      $.ajax({
        type: "POST",
        url: "actions/posts/edit_post.php",
        dataType: "json",
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
          Helper.loadingButton(button);
          Helper.removeValidation(Posts.selectors.pageSelector);
        },
        success: function (json) {
          Helper.loadingButton(button, buttonContent);
          if (json.error) {
            $.each(json.error, function (key, value) {
              $(`#input-${key}`)
                .addClass("is-invalid")
                .after(Helper.invalidFeedback(value));
            });
          }
          if (json.fatal) Helper.fatalError(json.fatal);
          if (json.success) location.reload();
        },
      });
    }

    // Если редактор успешно инициализирован, запускаем выгрузку картинок на сервер
    if (contentEditor) {
      // uploadImages() находит все Base64/Blob картинки, отправляет их в upload_img.php,
      // автоматически меняет их src на человеческие ссылки и только потом выполняет .then()
      contentEditor.uploadImages().then(function () {
        sendFormData();
      });
    } else {
      // Резервный случай: если редактора на странице нет, просто отправляем форму
      sendFormData();
    }
  },
  check_out: function () {
    let button = $(this),
      id = button.attr("data-id") ?? 0,
      title = button.attr("data-title") ?? "",
      content = button.attr("data-content") ?? "",
      buttonContent = button.html();

    let forbiddenWords = [
      "дурак",
      "запрещенное",
      "слово",
      "идиот",
      "тупица",
      "кретин",
      "ублюдок",
      "сволочь",
      "тварь",
      "мерзавец",
      "козёл",
      "недоумок",
      "придурок",
      "паразит",
      "хам",
      "скотина",
      "шлюха",
      "проститутка",
      "мразь",
      "гнида",
      "сучка",
      "извращенец",
      "насильник",
      "маньяк",
      "черт",
      "свинья",
      "бараны",
      "дебилы",
      "ненормальные",
      "психи",
      "кретины",
      "ублюдки",
      "засранец",
      "гадина",
      "ссученый",
      "выблядок",
      "петух",
      "отброс",
      "подонок",
      "блядь",
      "еблан",
      "мудак",
      "пидор",
      "сука",
      "хуесос",
      "гандон",
      "хер",
      "пизда",
      "хуй",
      "ебать",
      "срака",
      "засранец",
      "чмо",
      "гондон",
      "еблан",
      "пидорас",
      "шалава",
      "тупорылый",
      "даун",
      "ущербный",
      "аутист",
      "сдохни",
      "сгинь",
      "урод",
      "ублюдочная",
      "наркоман",
      "алкаш",
      "гнида",
      "скот",
      "недоумок",
      "засранец",
      "убогий",
      "выродок",
      "чмошник",
      "подстилка",
      "ничтожество",
      "быдло",
      "мразота",
      "смерд",
      "ублюдочный",
      "дебилоид",
      "сосун",
      "глист",
      "подстилка",
      "западло",
      "сучара",
      "падла",
      "чурка",
      "хач",
      "петух",
      "залупа",
      "говно",
      "пидорасина",
      "пидрила",
      "тварь",
      "ссанина",
      "выскочка",
      "бараны",
      "болваны",
      "проститутки",
      "сукины",
      "зажравшийся",
      "дармоед",
      "падаль",
      "сопляк",
      "мусор",
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
      alert(
        `В заголовке обнаружено запрещённое слово: "${titleHasForbidden}".`
      );
      return;
    }

    if (contentHasForbidden) {
      alert(
        `В содержимом обнаружено запрещённое слово: "${contentHasForbidden}".`
      );
      return;
    }

    button.replaceWith(`
            <button type="submit" class="btn btn-info edit" data-id="${id}">
                Отправить
            </button>
        `);
  },
  delete: function () {
    let id = $(this).attr("data-id"),
      button = $(this);

    if (confirm("Вы уверены, что хотите удалить пост?")) {
      $.ajax({
        type: "POST",
        url: "actions/posts/delete_post.php",
        dataType: "json",
        data: { id: id },
        beforeSend: function () {
          Helper.loadingButton(button);
        },
        success: function (json) {
          if (json.success) location.reload();
        },
      });
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

export { Posts };

import { Helper } from "./helper.js";

let Journal = {
  selectors: {
    pageSelector: "#journalPage",
    modalAddSelector: "#modalAddJournal",
    modalAddMenuSelector: "#modalAddMenuSelector",
    contentSelector: "#input-content",
    titleSelector: "#input-title",
    photoSelector: "#input-img",
    existingPhotoSelector: "#input-existing-photo",
    deletePhotoSelector: ".delete-avatar",
  },
  start: function () {
    $("#journals").dataTable({
      language: Helper.dataTableLanguage(),
      iDisplayLength: 50,
      order: [[0, "desc"]],
    });

    $(this.selectors.pageSelector).on("click", ".edit", this.edit);
    $(this.selectors.pageSelector).on(
      "click",
      "#journalMenuEdit",
      this.journalMenuEdit
    );
    $(this.selectors.pageSelector).on(
      "click",
      "#journalTagsMenuEdit",
      this.journalTagsMenuEdit
    );
    $(this.selectors.pageSelector).on("click", ".delete", this.delete);
    $(this.selectors.pageSelector).on("click", ".modalAdd", this.modalAdd);
    $(this.selectors.pageSelector).on(
      "click",
      ".modalMenuAdd",
      this.modalMenuAdd
    );
    $(this.selectors.pageSelector).on(
      "click",
      ".modalTagsMenuAdd",
      this.modalTagsMenuAdd
    );
    $(this.selectors.pageSelector).on("click", ".upload-avatar", function (e) {
      e.preventDefault();
      $(Journal.selectors.photoSelector).click();
    });
    $(this.selectors.pageSelector).on(
      "change",
      this.selectors.photoSelector,
      function (e) {
        if (this.files && this.files[0]) {
            Helper.uploadImage(".avatar", e);
            
            $(".upload-avatar").hide();
            $(Journal.selectors.deletePhotoSelector).show();
        }
      }
    );
    $(this.selectors.pageSelector).on(
      "click",
      this.selectors.deletePhotoSelector,
      function (e) {
        e.preventDefault();
    
        $(Journal.selectors.photoSelector).val("");
        $(Journal.selectors.existingPhotoSelector).val("");
        $(".avatar").attr("src", "/uploads/no-image.png");
    
        $(this).hide();
        $(".upload-avatar").show();
      }
    );

    $(document).on("click", ".delete-tag", function () {
      const row = $(this).closest(".list-group-item");
      const id = $(this).data("id");

      if (!confirm("Вы уверены, что хотите удалить этот тег?")) {
        return; // Пользователь отменил
      }

      $.ajax({
        url: "actions/journal/delete_tag.php",
        type: "POST",
        dataType: "json",
        data: { id },
        success(json) {
          if (json.success) {
            row.remove(); // Убираем строку из UI
          } else if (json.error) {
            alert(json.error);
          } else if (json.fatal) {
            Helper.fatalError(json.fatal);
          }
        },
      });
    });
  },
  file: function () {
    $(Journal.selectors.photoSelector).click();
  },
  modalAdd: function () {
    let button = $(this),
      id = button.attr("data-id") ?? 0,
      buttonContent = button.html();

    console.log(id);

    $.ajax({
      type: "POST",
      url: "actions/journal/get_modal.php",
      dataType: "json",
      data: { id: id },
      beforeSend: function () {
        Helper.loadingButton(button);
        $(Journal.selectors.modalAddSelector).remove();
      },
      success: function (json) {
        $(Journal.selectors.pageSelector).append(json.code);

        $(Journal.selectors.modalAddSelector).modal("show");

        Journal.initTinyMCE();
        Journal.initModalListeners();

        Helper.loadingButton(button, buttonContent);
      },
    });
  },
  modalMenuAdd: function () {
    let button = $(this),
      buttonContent = button.html();

    $.ajax({
      type: "POST",
      url: "actions/journal/get_modal_menu.php",
      dataType: "json",
      beforeSend: function () {
        Helper.loadingButton(button);
        $(Journal.selectors.modalAddMenuSelector).remove();
      },
      success: function (json) {
        $(Journal.selectors.pageSelector).append(json.code);

        $(Journal.selectors.modalAddMenuSelector).modal("show");

        Helper.loadingButton(button, buttonContent);
      },
    });
  },
  modalTagsMenuAdd: function () {
    let button = $(this),
      buttonContent = button.html();

    $.ajax({
      type: "POST",
      url: "actions/journal/get_modal_tag_menu.php",
      dataType: "json",
      beforeSend: function () {
        Helper.loadingButton(button);
        $(Journal.selectors.modalAddMenuSelector).remove();
      },
      success: function (json) {
        $(Journal.selectors.pageSelector).append(json.code);

        $(Journal.selectors.modalAddMenuSelector).modal("show");

        Helper.loadingButton(button, buttonContent);
      },
    });
  },
  initTinyMCE: function () {
    if (typeof tinymce !== "undefined") {
      // Удаляем старые редакторы перед повторной инициализацией
      tinymce.remove(
        `${Journal.selectors.contentSelector}, ${Journal.selectors.titleSelector}`
      );

      // Инициализация для каждого поля отдельно
      [
        Journal.selectors.contentSelector,
        Journal.selectors.titleSelector,
      ].forEach((selector) => {
        const el = document.querySelector(selector);
        if (!el) return;

        // Проверяем, если это input, то задаём высоту 170
        const isInput = el.tagName.toLowerCase() === "input";

        const isTitle = selector === Journal.selectors.titleSelector;

        tinymce.init({
          target: el,
          height: isTitle ? 170 : 450, // Настройка высоты по типу поля

          // Жесткое разделение на абзацы при нажатии Enter
          force_br_newlines: false,
          force_p_newlines: true,
          forced_root_block: "p",

          // Убрали плагин "paste", так как в TinyMCE 6+ он встроен по умолчанию
          plugins: isTitle
            ? "lists autolink code link"
            : "lists autolink code link image",

          paste_as_text: false,

          // Умная обработка вставляемого текста
          paste_preprocess: function (plugin, args) {
            let content = args.content;
  
            // Списки -> абзацы, чтобы пункты не слипались при последующей очистке тегов
            content = content.replace(/<\/li>\s*<li[^>]*>/gi, "</p><p>");
            content = content.replace(/<li[^>]*>/gi, "<p>");
            content = content.replace(/<\/li>/gi, "</p>");
            content = content.replace(/<\/?(ul|ol)[^>]*>/gi, "");
  
            const allowedTags = "p|br";
            const regex = new RegExp(
              `<(?!\\/?(${allowedTags})(\\s|>))[^>]+>`,
              "gi"
            );
            content = content.replace(regex, "");
  
            content = content.replace(/<br\s*\/?>\s*<br\s*\/?>/gi, "</p><p>");
            content = content.replace(/<br\s*\/?>/gi, "</p><p>");
  
            args.content = content
              .replace(/&nbsp;/g, " ")
              .replace(/<p>\s*<\/p>/g, "")
              .trim();
          },
          // Кнопки строго в порядке как на скриншоте
          toolbar: isTitle
            ? "undo redo | formatselect | fontselect fontsizeselect"
            : "undo redo formatselect fontselect fontsizeselect alignleft aligncenter alignright h2 h3 blockquote bullist numlist bold italic underline strikethrough image link code",
          image_title: true,
          image_description: true,
          automatic_uploads: true,
          file_picker_types: "image",
          image_dimensions: false,
          images_upload_handler: function (
            blobInfo,
            success,
            failure,
            progress
          ) {
            // Проверяем мажорную версию редактора
            var isV6 = parseInt(tinymce.majorVersion) >= 6;

            if (isV6) {
              // Логика для TinyMCE 6+ (обязан возвращать Promise)
              return new Promise(function (resolve, reject) {
                var formData = new FormData();
                formData.append("file", blobInfo.blob(), blobInfo.filename());

                fetch("actions/journal/upload_img.php", {
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
                      resolve(result.location); // В TinyMCE 6+ возвращаем ссылку через resolve
                    } else {
                      reject(result.error || "Неверный ответ от сервера");
                    }
                  })
                  .catch(function (error) {
                    reject("Ошибка: " + error.message);
                  });
              });
            } else {
              // Резервная логика для старых TinyMCE 4 / 5 (через колбэки)
              var formData = new FormData();
              formData.append("file", blobInfo.blob(), blobInfo.filename());

              fetch("actions/journal/upload_img.php", {
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
                    success(result.location); // В TinyMCE 5 вызываем колбэк success
                  } else {
                    failure(result.error || "Неверный ответ от сервера");
                  }
                })
                .catch(function (error) {
                  failure("Ошибка: " + error.message);
                });
            }
          },
          branding: false,
          height: isInput ? 170 : undefined,
          content_css:
            "https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap",
          content_style: `
            body, p, h1, h2, h3, div, span, blockquote {
              font-family: Roboto, sans-serif !important;
              font-size: 14px !important;
            }
            blockquote {
              border-left: 2px solid #ccc;
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
          force_br_newlines: false,
          force_p_newlines: true,
          forced_root_block: "p",
          relative_urls: false,
          remove_script_host: false,
          convert_urls: false,
          readonly: false,
          setup: function (editor) {
            let modalEl = document.querySelector(
              Journal.selectors.modalAddSelector
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

            // Открытие и закрытие окна модалки
            editor.on("OpenWindow", function () {
              disableFocusTrap();
              setTimeout(() => {
                const input = document.querySelector(".tox-dialog input");
                if (input) input.focus();
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

            // Лог при инициализации
            editor.on("init", function () {
              console.log("Editor initialized");
            });
          },
        });
      });
    } else {
      console.error("TinyMCE не загружен.");
    }
  },
  initModalListeners: function () {
    const metaTitleInput = document.getElementById("input-meta-title");
    const metaDescriptionInput = document.getElementById(
      "input-meta-description"
    );
    const slugInput = document.getElementById("input-alias");

    // Функция транслитерации из русского в латиницу
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

    // Функция для генерации slug на английском
    function generateSlug(text) {
      // Транслитерируем, если есть русские символы
      text = transliterate(text);
      return text
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, "") // удаляем все не буквенно-цифровые символы
        .replace(/\s+/g, "-") // заменяем пробелы на дефисы
        .replace(/-+/g, "-"); // убираем повторяющиеся дефисы
    }

    function stripHtmlTags(html) {
      return html.replace(/<\/?[^>]+(>|$)/g, "").trim();
    }

    function decodeHTMLEntities(text) {
      let doc = new DOMParser().parseFromString(text, "text/html");
      return doc.documentElement.textContent;
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
      tinymce.get(Journal.selectors.contentSelector.replace("#", ""))
    ) {
      tinymce
        .get(Journal.selectors.contentSelector.replace("#", ""))
        .on("change", function () {
          const contentHtml = tinymce
            .get(Journal.selectors.contentSelector.replace("#", ""))
            .getContent();
          metaDescriptionInput.value = generateMetaDescription(contentHtml);
        });
      tinymce
        .get(Journal.selectors.titleSelector.replace("#", ""))
        .on("change", function () {
          const contentHtml = tinymce
            .get(Journal.selectors.titleSelector.replace("#", ""))
            .getContent();
          slugInput.value = generateSlug(decodeHTMLEntities(contentHtml));
          metaTitleInput.value = generateMetaDescription(contentHtml);
        });
    }
  },
  edit: function () {
    let button = $(this),
      id = button.attr("data-id") ?? 0,
      buttonContent = button.html();

    // Получаем экземпляр редактора контента
    const contentEditor = tinymce.get(
      Journal.selectors.contentSelector.replace("#", "")
    );

    // Выносим отправку данных в отдельную функцию
    function sendFormData() {
      let formData = new FormData();

      $(
        `${Journal.selectors.modalAddSelector} select, ${Journal.selectors.modalAddSelector} input, ${Journal.selectors.modalAddSelector} textarea`
      ).each(function () {
        if ($(this).attr("type") === "file") {
          if ($(this)[0].files[0]) {
            formData.append("photo", $(this)[0].files[0]);
          }
        } else {
          formData.append($(this).attr("name"), $(this).val());
        }
      });

      // Теперь здесь гарантированно будут нормальные пути /uploads/articles/имя.webp
      formData.append("content", contentEditor.getContent());

      formData.append(
        "title",
        tinymce
          .get(Journal.selectors.titleSelector.replace("#", ""))
          .getContent()
      );

      $.ajax({
        type: "POST",
        url: "actions/journal/edit_journal.php",
        dataType: "json",
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
          Helper.loadingButton(button);
          Helper.removeValidation(Journal.selectors.pageSelector);
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

    // Если редактор готов, запускаем выгрузку картинок на сервер
    if (contentEditor) {
      // uploadImages() находит все Base64/Blob картинки, отправляет их в upload_img.php,
      // автоматически меняет их src на человеческие ссылки и только потом выполняет .then()
      contentEditor.uploadImages().then(function () {
        sendFormData();
      });
    } else {
      // Если редактора нет (мало ли), просто шлем форму
      sendFormData();
    }
  },
  journalMenuEdit: function () {
    let button = $(this),
      id = button.attr("data-id") ?? 0,
      buttonContent = button.html();

    let formData = new FormData();
    $(".meta-title").each(function () {
      formData.append(
        "meta_title_journal[" + $(this).data("id") + "]",
        $(this).val()
      );
    });

    $(".meta-description").each(function () {
      formData.append(
        "meta_description_journal[" + $(this).data("id") + "]",
        $(this).val()
      );
    });

    $(".meta-keywords").each(function () {
      formData.append(
        "meta_keywords_journal[" + $(this).data("id") + "]",
        $(this).val()
      );
    });

    $.ajax({
      type: "POST",
      url: "actions/journal/edit_journal_menu.php",
      dataType: "json",
      data: formData,
      processData: false,
      contentType: false,
      beforeSend: function () {
        Helper.loadingButton(button);
        Helper.removeValidation(Journal.selectors.pageSelector);
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
  },
  journalTagsMenuEdit: function () {
    let button = $(this),
      buttonContent = button.html();

    let formData = new FormData();
    $(".tag-title").each(function () {
      formData.append("title[" + $(this).data("id") + "]", $(this).val());
    });

    $(".meta-title").each(function () {
      formData.append("meta_title[" + $(this).data("id") + "]", $(this).val());
    });

    $(".meta-description").each(function () {
      formData.append(
        "meta_description[" + $(this).data("id") + "]",
        $(this).val()
      );
    });

    $(".meta-keywords").each(function () {
      formData.append(
        "meta_keywords[" + $(this).data("id") + "]",
        $(this).val()
      );
    });

    $.ajax({
      type: "POST",
      url: "actions/journal/edit_journal_tags_menu.php",
      dataType: "json",
      data: formData,
      processData: false,
      contentType: false,
      beforeSend: function () {
        Helper.loadingButton(button);
        Helper.removeValidation(Journal.selectors.pageSelector);
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
  },
  check_out: function () {
    let button = $(this),
      id = button.attr("data-id") ?? 0,
      title = button.attr("data-title") ?? "",
      content = button.attr("data-content") ?? "",
      buttonContent = button.html();

    let forbiddenWords = [
      "дурак",
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
          return true;
        }
      }
      return false;
    }

    let titleHasForbidden = containsForbiddenWords(title, forbiddenWords);
    let contentHasForbidden = containsForbiddenWords(content, forbiddenWords);

    if (titleHasForbidden || contentHasForbidden) {
      alert("В заголовке или содержимом обнаружены запрещённые слова.");
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
};

export { Journal };

import {
    MainPage
} from "./index.js";
import {
    Specialists
} from "./specialists.js";
import {
    Blocks
} from "./blocks.js";
import {
    Services
} from "./service.js";
import {
    FaqOptions
} from "./faq.js";
import {
    Posts
} from "./posts.js";
import {
    Menu
} from "./menu.js";
import {
    Tariffs
} from "./tariffs.js";
import {
    Photo
} from "./photo.js";
import {
    Journal
} from "./journal.js";
import {
    banners
} from "./banners.js";

MainPage.start()
Specialists.start()
Blocks.start()
Services.start()
FaqOptions.start()
Posts.start()
Menu.start()
Tariffs.start()
Photo.start()
Journal.start()
banners.start()

$(document).on("click", ".go-to-specialists", function() {
    let recordId = $(this).data("id");  
    let specialistId = $(this).data("specialist-id");
    let tableName = $(this).data("table");
    let moderation = $(this).data("moderation");
    let tab = $(this).data("tab");

    let status = (moderation < 1) ? moderation : 1;
    
    $.ajax({
        type: "POST",
        url: "actions/update_status.php",
        data: { 
            table: tableName,
            id: recordId,
            status: status
        },
        success: function(response) {
            window.location.href = "specialists?id=" + specialistId + "&tab=" + tab;
        }
    });
});

$(document).ready(function() {
    setTimeout(function() {
      $('input[type="search"]').each(function() {
        $(this).attr({
          'autocomplete': 'off',
          'autocorrect': 'off',
          'autocapitalize': 'off',
          'spellcheck': 'false',
          'name': 'no-autofill-' + Date.now()
        });
      });
    }, 100);
  });
  
  $('#specialists thead input').on('keyup change', function () {
    const columnIndex = $(this).parent().index();
    if (columnIndex === 9) return; // НЕ фильтровать по "Действие"
  
    table
      .column(columnIndex)
      .search(this.value)
      .draw();
  });

/**
 * Инициализирует TinyMCE для указанных селекторов.
 * @param {string} selectors - Селекторы для инициализации редактора, например: '.content, .title'
 * @param {object} [customConfig] - Дополнительные настройки для редактора (опционально)
 */
function initEditor(selectors, customConfig) {

    console.log('Succefull init')
    if (typeof tinymce === 'undefined') {
        console.error('TinyMCE не загружен.');
        return;
    }
    
    // Удаляем предыдущие экземпляры редактора по заданным селекторам
    tinymce.remove(selectors);
    
    // Базовая конфигурация
    var defaultConfig = {
        selector: selectors,
        plugins: 'lists link',
        toolbar: 'undo redo | formatselect | fontselect fontsizeselect | bold italic | alignleft aligncenter alignright | bullist numlist | link',
        branding: false,
        content_style: 'body { font-family: Roboto, Helvetica, Arial, sans-serif; font-size:14px }',
        font_formats: 'Roboto=Roboto, sans-serif; Arial=Arial, sans-serif; Courier New=Courier New, monospace; Georgia=Georgia, serif; Times New Roman=Times New Roman, serif; Verdana=Verdana, sans-serif;',
        fontsize_formats: '10px 12px 14px 16px 18px 20px 24px 28px 32px 36px 48px 60px 72px 96px',
        block_formats: 'Header 1=h1; Header 2=h2; Header 3=h3; Paragraph=p',
        style_formats: [
            { title: 'Header 1', block: 'h1' },
            { title: 'Header 2', block: 'h2' },
            { title: 'Header 3', block: 'h3' },
            { title: 'Paragraph', block: 'p' }
        ],
        force_br_newlines: true,
        force_p_newlines: false,
        forced_root_block: 'div',
        setup: function(editor) {
            editor.on('init', function() {
                console.log('Editor initialized for selectors: ' + selectors);
            });
        }
    };

    // Объединяем стандартную конфигурацию с дополнительными настройками, если они переданы
    var finalConfig = customConfig ? Object.assign({}, defaultConfig, customConfig) : defaultConfig;
    
    tinymce.init(finalConfig);
}

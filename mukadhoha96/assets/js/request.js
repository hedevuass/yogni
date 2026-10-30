import { Helper } from "./helper.js";

let Request = {
  selectors: {
    pageSelector: "#requestsPage",
    tableSelector: "#requests",
    filterSelect: "#req-filter",
    periodInput: "#req-period",
  },

  searchRegistered: false,

  start: function () {
    const $page = $(this.selectors.pageSelector);

    this.registerSearch();
    this.initTable(10);

    // статус
    $page.on("change", this.selectors.filterSelect, function () {
      Request.loadAll(function () {
        $(Request.selectors.tableSelector).DataTable().draw();
      });
    });

    // Сброс периода
    $page.on("click", "#req-period-clear", function () {
      document
        .querySelector(Request.selectors.periodInput)
        ?._flatpickr?.clear();
    });

    // после каждой перерисовки таблицы и при клике по галочке
    $page.on("draw.dt", this.selectors.tableSelector, this.syncSelection);
    $page.on("change", ".req-check", this.syncSelection);

    // «Выбрать все» — видимые на странице строки, «Сбросить» — все
    $page.on("click", "#req-select-all, #req-reset", function (e) {
      e.preventDefault();
      const check = this.id === "req-select-all";
      const table = $(Request.selectors.tableSelector).DataTable();
      const rows = check
        ? table.rows({ page: "current", search: "applied" })
        : table.rows();
      rows.nodes().to$().find(".req-check").prop("checked", check);
      Request.syncSelection();
    });

    $page.on("click", ".req-bulk", this.bulk);
    $page.on("click", "#loadFullRequestsButton", function () {
      Request.loadAll();
    });

    // кнопки в строках больше нет, обработчики оставлены на случай возврата
    $page.on("click", ".publish", this.publish);
    $page.on("click", ".delete", this.delete);

    $page.on("click", ".req-msg-toggle", function (e) {
      e.preventDefault();
      const $td = $(this).closest("td");
      const open = $td.find(".req-msg-full").hasClass("d-none");
      $td.find(".req-msg-short").toggleClass("d-none", open);
      $td.find(".req-msg-full").toggleClass("d-none", !open);
      $(this).text(open ? "Свернуть" : "Развернуть");
    });
  },

  /* ===== Таблица и фильтры ===== */

  initTable: function (pageLength = 10, prevState = null) {
    const selector = Request.selectors.tableSelector;

    if ($.fn.DataTable.isDataTable(selector)) {
      $(selector).DataTable().destroy();
    }

    return $(selector).DataTable({
      language: Helper.dataTableLanguage(),
      iDisplayLength: pageLength,
      order: [[0, "desc"]],
      columnDefs: [{ targets: 1, orderable: false, searchable: false }],
      dom:
        '<"d-flex mb-2"l>' +
        '<"d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3"<"req-filters-slot">f>' +
        '<"req-actions-slot">' +
        "rt" +
        '<"d-flex flex-wrap justify-content-between align-items-center mt-3"ip>',
      initComplete: function () {
        $("#requestsTableContainer .req-filters-slot").append($("#reqFilters"));
        $("#requestsTableContainer .req-actions-slot").append($("#reqActions"));
        $("#requests_length select").addClass("mx-2");
        Request.initPeriod(this.api(), prevState);
      },
    });
  },

  initPeriod: function (table, prev) {
    const input = document.querySelector(Request.selectors.periodInput);
    const fp = flatpickr(input, {
      mode: "range",
      locale: "ru",
      dateFormat: "d.m.Y",
      onChange: (dates) => {
        if (dates.length === 2 || dates.length === 0) {
          // При выборе периода тоже проверяем/подгружаем все записи через loadAll
          Request.loadAll(function () {
            $(Request.selectors.tableSelector).DataTable().draw();
          });
        }
      },
      onClose: (dates, str, instance) => {
        if (dates.length === 1) instance.setDate([dates[0], dates[0]], true);
      },
    });

    if (prev) {
      $(Request.selectors.filterSelect).val(prev.filter);
      if (prev.dates.length === 2) fp.setDate(prev.dates, false);
      table.draw();
    }
  },

  // общий фильтр DataTables: статус + период
  registerSearch: function () {
    if (Request.searchRegistered) return;
    Request.searchRegistered = true;

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
      if (settings.nTable.id !== "requests") return true;

      const tr = settings.aoData[dataIndex].nTr;

      const filter = $(Request.selectors.filterSelect).val() || "all";
      if (filter !== "all" && tr.dataset.status !== filter) return false;

      const picker = document.querySelector(
        Request.selectors.periodInput
      )?._flatpickr;
      const dates = picker ? picker.selectedDates : [];
      if (dates.length === 2) {
        const d = new Date(tr.dataset.date + "T00:00:00");
        if (d < dates[0] || d > dates[1]) return false;
      }
      return true;
    });
  },

  getUiState: function () {
    const picker = document.querySelector(
      Request.selectors.periodInput
    )?._flatpickr;
    return {
      filter: $(Request.selectors.filterSelect).val() || "all",
      dates: picker ? picker.selectedDates.slice() : [],
    };
  },

  // снимаем галочки со скрытых фильтром строк, обновляем доступность кнопок
  syncSelection: function () {
    const table = $(Request.selectors.tableSelector).DataTable();
    table
      .rows({ search: "removed" })
      .nodes()
      .to$()
      .find(".req-check")
      .prop("checked", false);
    const any =
      table.rows().nodes().to$().find(".req-check:checked").length > 0;
    $(".req-bulk").prop("disabled", !any);
  },

  // «Показать все записи»
  loadAll: function (callback) {
    const $btn = $("#loadFullRequestsButton");

    if (!$btn.length || $btn.is(":hidden")) {
      if (callback) callback();
      return;
    }

    const prevState = Request.getUiState();
    $btn.prop("disabled", true).text("Загружаю...");

    $.ajax({
      type: "POST",
      url: window.location.href,
      dataType: "html",
      data: { action: "load_full_requests" },
    })
      .done(function (html) {
        const $new = $("<div>")
          .html(html.trim())
          .find("#requestsTableContainer");

        if (!$new.length) {
          console.error("Таблица с заявками не найдена в ответе сервера.");
          $btn.prop("disabled", false).text("Показать все записи");
          if (callback) callback();
          return;
        }

        $("#requestsTableContainer").replaceWith($new);

        // Возвращаем 50 (или больше, например 100/500), чтобы записи отображались вместе
        Request.initTable(50, prevState);

        window.updateExpandLinks?.();
        $btn.hide();

        if (callback) callback();
      })
      .fail(function () {
        $btn.prop("disabled", false).text("Показать все записи");
        if (callback) callback();
      });
  },

  /* ===== Массовые действия ===== */

  bulk: async function () {
    const $btn = $(this);
    const action = $btn.data("action"); // publish | spam
    const table = $(Request.selectors.tableSelector).DataTable();
    const checked = table
      .rows()
      .nodes()
      .to$()
      .find(".req-check:checked")
      .toArray();

    if (!checked.length) return;

    const question =
      action === "publish"
        ? `Отправить заявки специалистам (${checked.length})?`
        : `Пометить заявки как спам (${checked.length})?`;
    if (!confirm(question)) return;

    Helper.loadingButton($btn);
    $(".req-bulk").prop("disabled", true);

    // по одной, чтобы не отправлять письма пачкой
    const failed = [];
    for (const cb of checked) {
      try {
        await Request.processOne(cb, action);
      } catch (e) {
        failed.push($(cb).val());
      }
    }

    if (failed.length) {
      alert("Не удалось обработать заявки (ID): " + failed.join(", "));
    }
    location.reload();
  },

  processOne: async function (cb, action) {
    const $cb = $(cb);
    const id = $cb.val();

    if (action === "publish") {
      const row = $cb.closest("tr");
      const mail = await $.ajax({
        type: "POST",
        url: "/actions/mail/send_message.php",
        dataType: "json",
        data: {
          email: $cb.data("specialist-email"),
          specialist_name: $cb.data("specialist-name"),
          name: row.find(".input-name").data("name"),
          contactInfo: row.find(".input-phone").data("phone"),
          message: row.find(".input-message").data("message"),
        },
      });
      if (mail.error || mail.fatal) throw new Error("mail");
    }

    // 1 — отправлена, 2 — спам (для спама статус предположительно такой, сверьте с delete)
    const res = await $.ajax({
      type: "POST",
      url: "actions/request/update.php",
      dataType: "json",
      data: { id: id, status: action === "publish" ? "1" : "2" },
    });
    if (!res.success) throw new Error("update");
  },
  publish: function () {
    let button = $(this);
    let buttonContent = button.html();
    let row = button.closest("tr");

    let id = button.data("id");

    let mailData = {
      email: button.data("specialist-email"),
      specialist_name: button.data("specialist-name"),
      name: row.find(".input-name").data("name"),
      contactInfo: row.find(".input-phone").data("phone"),
      message: row.find(".input-message").data("message"),
    };

    let status = "1";

    if (!confirm("Вы уверены, что хотите отправить запрос?")) {
      return;
    }

    $.ajax({
      type: "POST",
      url: "/actions/mail/send_message.php",
      dataType: "json",
      data: mailData,
      beforeSend: function () {
        Helper.loadingButton(button);
        Helper.removeValidation(Request.selectors.pageSelector);
      },
      success: function (json) {
        if (json.error) {
          Helper.loadingButton(button, buttonContent);
          $.each(json.error, function (key, value) {
            $(`#input-${key}`)
              .addClass("is-invalid")
              .after(Helper.invalidFeedback(value));
          });
          return;
        }

        if (json.fatal) {
          Helper.loadingButton(button, buttonContent);
          Helper.fatalError(json.fatal);
          return;
        }

        // письмо ушло → обновляем статус
        $.ajax({
          type: "POST",
          url: "actions/request/update.php",
          dataType: "json",
          data: { id: id, status: status },
          beforeSend: function () {
            Helper.loadingButton(button);
          },
          success: function (json) {
            if (json.success) location.reload();
          },
        });
      },
    });
  },
  delete: function () {
    let id = $(this).attr("data-id"),
      button = $(this);

    let status = "2";

    if (confirm('Вы уверены, что хотите отметить запрос как "спам"?')) {
      $.ajax({
        type: "POST",
        url: "actions/request/update.php",
        dataType: "json",
        data: { id: id, status: status },
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

export { Request };

// noinspection JSCheckFunctionSignatures

import { Helper } from "./helper.js";

let photoRemoved = false;

let Specialists = {
  selectors: {
    pageSelector: "#specialistPage",
    modalAddSelector: "#modalAddSpecialist",
    photoSelector: "#input-photo",
    photoSelectorEmployee: "#input-photo-employee",
    documentFileInput: ".document-file-input",
    documentPreview: ".document-preview",
    uploadTrigger: ".upload-trigger",
    pageDescriptionSelector: "#input-page-discription",
    companyFieldsSelector: "#companyFields",
    universityField: "#universityField",
    companyContentSelector: "#content-company-id",
    companyTabSelector: "#tab-company",
    classificationSelect: "#input-classification_id",
  },
  start: function () {
    $("#specialists").dataTable({
      language: Helper.dataTableLanguage(),
      iDisplayLength: 50,
      processing: true,
      serverSide: true,
      serverMethod: "post",
      ajax: {
        url: "actions/specialist/get_specialists.php",
      },
      order: [[0, "desc"]],
      columns: [
        { data: "id" },
        { data: "photo" },
        { data: "name" },
        { data: "email" },
        { data: "phone" },
        { data: "classification" },
        { data: "city" },
        { data: "rating" },
        { data: "computed_rating" },
        { data: "active", searchable: false },
        { data: "action", searchable: false },
      ],
    });

    this.bindEvents();
    this.handleClassificationChange();

    $(this.selectors.pageSelector).on("click", ".delete", Specialists.delete);
    $(this.selectors.pageSelector).on(
      "click",
      ".modalAdd",
      Specialists.modalAdd
    );
    $(this.selectors.pageSelector).on("click", ".upload-avatar-add", () => {
      $(Specialists.selectors.photoSelector).click();
    });
    $(this.selectors.pageSelector).on(
      "click",
      ".upload-avatar-delete",
      function () {
        $(".avatar").attr("src", "/assets/img/cabinet/gray-circle.svg"); // Показываем заглушку
        $(Specialists.selectors.photoSelector).val(""); // Очищаем input file
        photoRemoved = true; // Устанавливаем флаг удаления
      }
    );
    $(this.selectors.pageSelector).on(
      "change",
      this.selectors.photoSelector,
      (e) => Helper.uploadImage(".avatar", e)
    );
    $(this.selectors.pageSelector).on(
      "change",
      this.selectors.photoSelectorEmployee,
      (e) => Helper.uploadImage(".avatar-employee", e)
    );
    /* $(this.selectors.pageSelector).on(
      "input",
      "#input-phone",
      Helper.validationPhone
    ); */
    $(this.selectors.pageSelector).on(
      "input",
      "#input-password",
      Helper.validationPassword
    );
    $(this.selectors.pageSelector).on(
      "change",
      "#input-classification_id",
      Specialists.classification
    );
    $(this.selectors.pageSelector).on(
      "click",
      "#specialistAdd",
      Specialists.add
    );
    $(this.selectors.pageSelector).on(
      "click",
      ".connect",
      Specialists.promoAdd
    );
    $(this.selectors.pageSelector).on(
      "click",
      ".disconnect",
      Specialists.promoDelete
    );
    // Редактирование запросов
    $(this.selectors.pageSelector).on(
      "click",
      ".manage-request",
      Specialists.manageRequest
    );
    $(this.selectors.pageSelector).on(
      "click",
      ".connect-service",
      Specialists.connectService
    );

    $(this.selectors.pageSelector).on(
      "click",
      ".toggle-pro-photo-details",
      function () {
        var requestId = $(this).data("request-id");
        $("#pro-photo-request-details-" + requestId).toggle();
      }
    );

    // Обработчик для массового открытия/закрытия
    let allProPhotoVisible = false;

    $(this.selectors.pageSelector).on(
      "click",
      "#toggle-all-pro-photo",
      function (e) {
        e.preventDefault();
        allProPhotoVisible = !allProPhotoVisible;

        $(".card-body[id^='pro-photo-request-details-']").each(function () {
          $(this).toggle(allProPhotoVisible);
        });

        $(this).text(allProPhotoVisible ? "(скрыть все)" : "(развернуть все)");
      }
    );

    $(this.selectors.pageSelector).on(
      "click",
      ".toggle-banner-details",
      function () {
        var bannerId = $(this).data("banner-id");
        $("#banner-details-" + bannerId).toggle();
      }
    );

    // Показ всех скрытых запросов и переключение текста кнопки
    $(document).on("click", ".show-all-requests", function () {
      var bannerId = $(this).data("banner-id");
      var hiddenItems = $(".hidden-request-" + bannerId);
      hiddenItems.slideToggle();
      var btn = $(this);
      if (btn.text().trim().startsWith("Показать")) {
        btn.text("Скрыть");
      } else {
        btn.text("Показать все (" + hiddenItems.length + ")");
      }
    });

    $(document).on("click", ".show-all-journal-requests", function () {
      var id = $(this).data("journal-id");
      var items = $(".hidden-journal-" + id);
      items.slideToggle();
      var btn = $(this);
      btn.text(
        btn.text().trim().startsWith("Показать")
          ? "Скрыть"
          : "Показать все (" + items.length + ")"
      );
    });

    $(document).on("click", ".toggle-journal-details", function () {
      var journalId = $(this).data("journal-id");
      $("#journal-details-" + journalId).toggle();
    });

    $(this.selectors.pageSelector).on(
      "click",
      ".mark-pro-photo-done",
      function () {
        let requestId = $(this).data("request-id"); // Получаем ID записи

        // Показываем диалоговое окно для подтверждения
        if (
          !confirm("Вы уверены, что хотите отметить PRO-фото как 'Проведена'?")
        ) {
          return; // Если пользователь нажал "Отмена", ничего не делаем
        }

        $.ajax({
          type: "POST",
          url: "actions/update_status.php",
          data: {
            table: "dt_pro_photo_list", // Указываем нужную таблицу
            id: requestId, // ID записи
            status: 0, // Новый статус (moderation = 0)
          },
          success: function (response) {
            let res = JSON.parse(response);
            if (res.success) {
              location.reload(); // Перезагружаем страницу при успехе
            } else {
              alert("Ошибка: " + res.error);
            }
          },
          error: function () {
            alert("Ошибка запроса. Попробуйте позже.");
          },
        });
      }
    );

    // Для архивных размещений
    // Индивидуальное раскрытие архива
    $(this.selectors.pageSelector).on("click", ".toggle-archive", function () {
      var archiveId = $(this).data("archive-id");
      $("#archive-details-" + archiveId).toggle();
    });

    // Массовое раскрытие/скрытие архива
    let allArchiveVisible = false;

    $(this.selectors.pageSelector).on(
      "click",
      "#toggle-all-archive",
      function (e) {
        e.preventDefault();
        allArchiveVisible = !allArchiveVisible;

        $(".card-body[id^='archive-details-']").each(function () {
          $(this).toggle(allArchiveVisible);
        });

        $(this).text(allArchiveVisible ? "(скрыть все)" : "(развернуть все)");
      }
    );

    $(document).on("click", ".delete-service-btn", function () {
      // Получаем родительский элемент, который представляет услугу
      var serviceRow = $(this).closest(".service-row");

      // Удаляем строку из DOM
      serviceRow.remove();
    });

    $(document).on("click", ".delete-document-btn", function () {
      $(this).closest("[data-document]").remove();
    });


    $(document).ready(function () {
      let urlParams = new URLSearchParams(window.location.search);
      let specialistId = parseInt(urlParams.get("id"), 10); // Преобразуем в целое число
      let tab = urlParams.get("tab");

      // Проверяем, что specialistId больше нуля
      if (specialistId > 0) {
        // Открываем модалку с нужной вкладкой
        $.ajax({
          type: "POST",
          url: "actions/specialist/get_modal.php",
          dataType: "json",
          data: { id: specialistId },
          success: function (json) {
            $("#specialistPage").append(json.code);
            $("#modalAddSpecialist").modal("show");
            Specialists.initTinyMCE();
            Specialists.initModalListeners();
            Specialists.handleClassificationChange();
            setTimeout(() => {
              $("#tab-" + tab).trigger("click");
            }, 300);
          },
        });
      }
    });
    $(document).on("click", ".button_close, .btn-close", function () {
      const newUrl =
        window.location.protocol +
        "//" +
        window.location.host +
        window.location.pathname;
      history.replaceState(null, "", newUrl);
    });

    $(document).on("click", ".delete-review", function () {
      let reviewId = $(this).data("id");
      Specialists.deleteReview(reviewId, $(this).closest(".review"));
    });

    $(document).on("click", this.selectors.uploadTrigger, function () {
      $(this).siblings(Specialists.selectors.documentFileInput).click();
    });

    // Обработка изменения файла в инпуте
    $(document).on("change", this.selectors.documentFileInput, function (e) {
      const fileInput = $(this); // Сохраняем текущий input
      const preview = fileInput.siblings(Specialists.selectors.documentPreview);
      Specialists.previewFile(preview, e.target.files[0], fileInput); // Передаем fileInput
    });

    function doConnectService(payload, $btn, successCallback) {
      return $.ajax({
        type: "POST",
        url: "actions/specialist/connect_service.php",
        data: payload,
        beforeSend: function () {
          if ($btn) $btn.prop("disabled", true).text("Подключение…");
        },
        success: function (response) {
          try {
            var res = typeof response === "string" ? JSON.parse(response) : response;
            if (res.success) {
              if (typeof successCallback === "function") {
                successCallback(res);
              }
            } else {
              alert(res.error || "Ошибка выполнения запроса");
            }
          } catch (e) {
            console.error("Неверный формат ответа:", response);
          }
        },
        error: function (err) {
          console.error("Ошибка AJAX:", err);
        },
        complete: function () {
          if ($btn) $btn.prop("disabled", false).text("Подключить");
        }
      });
    }

    $(document).on("click", ".confirm-service", function () {
      var $promoOptions = $(this).closest(".promo-options");
      $promoOptions.hide(); // Скрываем блок
    });
    $(document).on("click", ".check-service", function () {
      var $btn = $(this);
      var $currentRow = $btn.closest(".row");
      var specialistId = $btn.data("specialist-id");
      var classificationId = $btn.data("classifications-id");
      var serviceSlug = $btn.data("service-slug");
      var blockId = $btn.data("block-id");
      var serviceType = $btn.data("service-type");    // "parent", "page" или "child"
      var serviceId = $btn.data("id");
      var isConnected = $btn.data("connected") == 1;
      var userRole = $btn.data("user-role");
      var conns = JSON.parse($btn.attr("data-connections") || '[]');
      var specialistEmail = $btn.data("email");
      var priceBlock1 = parseFloat($btn.data("service-price-1")) || 0;
      var priceBlock2 = parseFloat($btn.data("service-price-2")) || 0;
      var discount = parseFloat($btn.data("discount")) || 0;

      var discountedPriceBlock1 = discount ? (priceBlock1 * (1 - discount / 100)).toFixed(2) : priceBlock1.toFixed(2);
      var discountedPriceBlock2 = discount ? (priceBlock2 * (1 - discount / 100)).toFixed(2) : priceBlock2.toFixed(2);


      var serviceName = $btn.data("service-name") || $currentRow.find(".col-3 strong").text().trim();

      var orderId = $btn.data("order-id") || Date.now();

      // 1) Если wrapper уже есть — просто переключаем видимость и выходим
      var $wrapper = $currentRow.next(".promo-options-wrapper");
      if ($wrapper.length) {
        $wrapper.toggle();
        return;
      }

      // 2) Собираем единый контейнер для всех conns
      var promoHtml = '<div class="promo-options-wrapper" style="margin-left:20px; margin-bottom:10px;">';

      conns.forEach(function (conn, i) {
        var isNew = !conn.id;
        var checkedTop = (conn.sort === 1) ? 'checked' : '';
        var checkedStandard = (conn.sort === 2) ? 'checked' : '';
        var title = $currentRow.find(".col-3.service-title").text().trim();
        var serviceName = serviceType === "child"
          ? 'Подраздел "' + title + '"'
          : serviceType === "parent"
            ? 'Раздел "' + title + '"'
            : title;

        // Экранируем кавычки перед вставкой в HTML
        var safeServiceName = serviceName.replace(/"/g, '&quot;');

        promoHtml +=
          '<div class="promo-options row" data-idx="' + i + '" style="margin-right:20px; margin-bottom:10px; margin-top: 20px;">' +
          '  <div class="col-12">' +
          '    <div class="card shadow-sm border-primary">' +
          '      <div class="card-body">' +

          // Радиокнопки
          '        <div class="mb-3">' +
          '          <div class="form-check form-check-inline">' +
          '            <input type="radio" class="form-check-input" ' +
          '                   id="promoTop_' + i + '" ' +
          '                   name="promoPlacement_' + i + '" ' +
          '                   value="block_top_specialists" ' + checkedTop + '>' +
          '            <label class="form-check-label" for="promoTop_' + i + '">Размещение на 1‑м месте</label>' +
          '          </div>' +
          '          <div class="form-check form-check-inline">' +
          '            <input type="radio" class="form-check-input" ' +
          '                   id="promoStd_' + i + '" ' +
          '                   name="promoPlacement_' + i + '" ' +
          '                   value="block_specialist_positions" ' + checkedStandard + '>' +
          '            <label class="form-check-label" for="promoStd_' + i + '">Стандартное размещение</label>' +
          '          </div>' +
          '        </div>' +

          // Даты
          '        <div class="mb-3">' +
          '          <label class="form-label">Дата начала:</label>' +
          '          <input type="text" class="form-control date-start" value="' + (conn.date_start || '') + '">' +
          '        </div>' +
          '        <div class="mb-3">' +
          '          <label class="form-label">Дата окончания:</label>' +
          '          <input type="text" class="form-control date-expires" value="' + (conn.date_expires || '') + '">' +
          '        </div>' +

          // Кнопки
          '        <div class="text-end d-flex gap-2 justify-content-end">';
        promoHtml +=
          '          <button type="button" class="btn btn-sm btn-success connect-service" ' +
          '                  data-idx="' + i + '" ' +
          '                  data-order-id="' + orderId + '" ' +
          '                  data-block-id="' + blockId + '" ' +
          '                  data-specialist-id="' + specialistId + '" ' +
          '                  data-classifications-id="' + classificationId + '" ' +
          '                  data-service-slug="' + serviceSlug + '" ' +
          '                  data-service-type="' + serviceType + '"' +
          '                  data-price-block-1="' + discountedPriceBlock1 + '" ' +
          '                  data-price-block-2="' + discountedPriceBlock2 + '" ' +
          '                  data-service-name="' + safeServiceName + '" ' +
          (isNew ? '' : ' data-id="' + conn.id + '"') +
          (isConnected ? '' : ' disabled') +
          '>Подключить</button>';

        if (userRole == 'superadmin') {
          promoHtml +=
            '          <button type="button" class="btn btn-sm btn-danger disconnect" ' +
            '                  data-type="promo" ' +
            '                  data-id="' + (conn.id || '') + '"' +
            '                  data-specialist-id="' + (specialistId || '') + '"' +
            (conn.id ? '' : ' disabled') +
            '>Отключить</button>';
        }

        promoHtml +=
          '           <button type="button" class="btn btn-sm btn-primary send-invoice" ' +
          '                  data-id="' + serviceId + '" ' +
          '                  data-type="Размещение карточки в промо-блоках" ' +
          '                  data-specialist-id="' + specialistId + '" ' +
          '                  data-block-id="' + blockId + '" ' +
          '                  data-classifications-id="' + classificationId + '" ' +
          '                  data-service-slug="' + serviceSlug + '" ' +
          '                  data-service-type="' + serviceType + '" ' +
          '                  data-email="' + specialistEmail + '" ' +
          '                  data-service-price-1="' + discountedPriceBlock1 + '" ' +
          '                  data-service-price-2="' + discountedPriceBlock2 + '" ' +
          '                  data-order-id="' + orderId + '" ' +
          '                  data-service-name="' + safeServiceName + '" ' + (conn.id ? '' : ' disabled') + '>' +
          'Отправить счет</button>' +
          '        </div>' +
          '      </div>' +
          '    </div>' +
          '  </div>' +
          '</div>';
      });

      promoHtml += '</div>';

      // 3) Вставляем wrapper после текущей строки
      $currentRow.after(promoHtml);

      // 4) Инициализируем flatpickr внутри wrapper
      var $newWrapper = $currentRow.next(".promo-options-wrapper");
      $newWrapper.find('.date-start, .date-expires').each(function () {
        flatpickr(this, {
          altInput: true,
          altFormat: 'd.m.Y',
          dateFormat: 'Y-m-d',
          locale: 'ru',
          minDate: 'today'
        });
      });

      // 5) Навешиваем обработчик на радио внутри wrapper
      $newWrapper.find('input[name^="promoPlacement_"]').on('change', function () {
        var nameParts = $(this).attr('name').split('_');
        var idx = nameParts[1];
        var selectedSort = $(this).val() === 'block_top_specialists' ? 1 : 2;
        var $opt = $newWrapper.find('.promo-options[data-idx="' + idx + '"]');

        $.ajax({
          type: 'POST',
          url: 'actions/specialist/get_occupied_entries.php',
          data: {
            id: specialistId,
            sort: selectedSort,
            service_slug: serviceSlug,
            block_id: blockId
          },
          dataType: 'json',
          success: function (response) {
            var disabledRanges = response.map(function (entry) {
              return {
                from: new Date(entry.date_start * 1000),
                to: new Date(entry.date_expires * 1000)
              };
            });
            $opt.find('.date-start, .date-expires').each(function () {
              var fp = this._flatpickr;
              if (fp) {
                fp.set('disable', disabledRanges);
                fp.set('minDate', 'today');
              }
            });
          }
        });
      });
    });

    function buildConnectPayloadFrom($btn) {
      var idx = $btn.data("idx");
      var orderId = $btn.data("order-id");
      var $wrapper = $btn.closest(".promo-options-wrapper");
      var $optionsRow = $wrapper.find('.promo-options[data-idx="' + idx + '"]');

      var sortVal = $optionsRow.find('input[name="promoPlacement_' + idx + '"]:checked').val();
      var sort = sortVal === "block_top_specialists" ? 1 : 2;

      var dateStart = $optionsRow.find(".date-start").val();
      var dateExpires = $optionsRow.find(".date-expires").val();

      var serviceTitle = $btn.closest(".row").find(".col-3").text().trim();
      var serviceType = $btn.data("service-type");
      var serviceName = serviceType === "child"
        ? 'Подраздел "' + serviceTitle + '"'
        : serviceType === "parent"
          ? 'Раздел "' + serviceTitle + '"'
          : serviceTitle;

      var priceBlock1 = parseFloat($btn.data("price-block-1")) || 0;
      var priceBlock2 = parseFloat($btn.data("price-block-2")) || 0;
      // считать недели включительно
      var weeks = 1;
      if (dateStart && dateExpires) {
        var startDate = new Date(dateStart),
          endDate = new Date(dateExpires),
          diff = endDate - startDate + 24 * 60 * 60 * 1000;
        weeks = Math.ceil(diff / (7 * 24 * 60 * 60 * 1000));
      }
      var baseAmount = sort === 1 ? priceBlock1 : priceBlock2;
      var amount = (baseAmount * weeks).toFixed(2);

      var payload = {
        specialist_id: $btn.data("specialist-id"),
        sort: sort,
        order_id: orderId,
        date_start: dateStart,
        date_expires: dateExpires,
        service_name: serviceName,
        amount: amount,
        service_slug: $btn.data("service-slug")
      };
      // block_id и id, если есть
      var classId = $btn.data("classifications-id");
      if ([1, 2, 5, 6, 7].includes(classId)) payload.block_id = 1;
      else if ([3, 8].includes(classId)) payload.block_id = 2;
      else if (classId === 4) payload.block_id = 3;
      if ($btn.data("id")) payload.id = $btn.data("id");

      return payload;
    }

    $(document).on("click", ".send-invoice", function () {
      var $btn = $(this);
      var type = $btn.data("type"); // "promo" или "tariff"
      var orderId = $btn.data("order-id");

      // Сбрасываем форму
      var $form = $("#invoice-form");
      $form[0].reset();
      $form.find("input[type=hidden]").remove();
      // Дефолтные общие поля
      $form.append(`<input type="hidden" name="action" value="invoice">`);
      $form.append(`<input type="hidden" name="orderId" value="${orderId}">`);
      // Специальный флаг: при отправке из админки не менять moderation (оставить 0)
      $form.append(`<input type="hidden" name="dont_change_moderation" value="1">`);

      if (type === "tariff") {
        // === ВЕТКА ТАРИФОВ ===
        var specialistId = parseInt($btn.data("specialist-id"), 10);
        var tariffId = $btn.data("id");
        var price = parseFloat($btn.data("price")) || 0;
        var durationName = $btn.data("duration-checkbox"); // “duration_{id}”
        var $chk = $(`input[name="${durationName}"]:checked`);
        if (!$chk.length) {
          return alert("Пожалуйста, выберите длительность тарифа.");
        }
        var months = parseInt($chk.val(), 10);
        var dateStart = flatpickr.formatDate(new Date(), "Y-m-d");
        var dt = new Date();
        dt.setMonth(dt.getMonth() + months);
        var yyyy = dt.getFullYear(),
          mm = String(dt.getMonth() + 1).padStart(2, "0"),
          dd = String(dt.getDate()).padStart(2, "0");
        var dateEnd = `${yyyy}-${mm}-${dd}`;
        var payAmount = (price * months).toFixed(2);

        // Подставляем в модалку
        $("#service-name").val("Подключение тарифа");
        $("#period").val(months + " мес.");
        $("#place").val("");
        $("#amount").val(payAmount + " руб.");
        $("#inn").val("");

        // Скрытые поля
        $form.append(`<input type="hidden" name="service_id"    value="${tariffId}">`);
        $form.append(`<input type="hidden" name="specialistId"  value="${specialistId}">`);
        $form.append(`<input type="hidden" name="pay_amount"    value="${payAmount}">`);
        $form.append(`<input type="hidden" name="date_start"    value="${dateStart}">`);
        $form.append(`<input type="hidden" name="date_end"      value="${dateEnd}">`);
        $form.append(`<input type="hidden" name="specialist_email" value="${$btn.data("email") || ""}">`);

      } else if (type === "option") {
        // === ВЕТКА ОПЦИЙ ===
        var specialistId = parseInt($btn.data("specialist-id"), 10);
        var optionId = $btn.data("id");
        var price = parseFloat($btn.data("price")) || 0;
        var months = parseInt($btn.data("duration"), 10) || 1; // можно хранить в data-duration, по умолчанию 1
    
        var dateStart = flatpickr.formatDate(new Date(), "Y-m-d");
        var dt = new Date();
        dt.setMonth(dt.getMonth() + months);
        var yyyy = dt.getFullYear(),
            mm = String(dt.getMonth() + 1).padStart(2, "0"),
            dd = String(dt.getDate()).padStart(2, "0");
        var dateEnd = `${yyyy}-${mm}-${dd}`;
    
        var payAmount = (price * months).toFixed(2);
    
        // Подставляем в модалку
        $("#service-name").val("Подключение опции");
        $("#period").val(months + " мес.");
        $("#place").val("");
        $("#amount").val(payAmount + " руб.");
        $("#inn").val("");
    
        // Скрытые поля
        $form.append(`<input type="hidden" name="service_id"    value="${optionId}">`);
        $form.append(`<input type="hidden" name="specialistId"  value="${specialistId}">`);
        $form.append(`<input type="hidden" name="pay_amount"    value="${payAmount}">`);
        $form.append(`<input type="hidden" name="date_start"    value="${dateStart}">`);
        $form.append(`<input type="hidden" name="date_end"      value="${dateEnd}">`);
        $form.append(`<input type="hidden" name="specialist_email" value="${$btn.data("email") || ""}">`);
       } else {
        // === ВЕТКА ПРОМО ===
        $form.append(`<input type="hidden" name="service_name" value="${type}">`);
        var $promoOptions = $btn.closest(".promo-options");
        var specialistId = parseInt($btn.data("specialist-id"), 10);
        var classificationId = $btn.data("classifications-id");
        var serviceSlug = $btn.data("service-slug");
        var serviceType = $btn.data("service-type");
        var serviceName = $btn.data("service-name");
        var priceBlock1 = parseFloat($btn.data("service-price-1")) || 0;
        var priceBlock2 = parseFloat($btn.data("service-price-2")) || 0;
        var idx = $promoOptions.data("idx");
        var placement = $promoOptions
          .find(`input[name="promoPlacement_${idx}"]:checked`)
          .val();
        if (!placement) {
          return alert("Выберите тип размещения (1‑е место или стандарт).");
        }
        var placeText = placement === "block_top_specialists" ? "1 место" : "2‑8 место";
        var sort = placement === "block_top_specialists" ? 1 : 2;
        var dateStart = $promoOptions.find(".date-start").val() ||
          flatpickr.formatDate(new Date(), "Y-m-d");
        var dateExpires = $promoOptions.find(".date-expires").val() ||
          flatpickr.formatDate(new Date(Date.now() + 7 * 24 * 60 * 60 * 1000), "Y-m-d");
        var startDate = new Date(dateStart);
        var endDate = new Date(dateExpires);
        var weeks = Math.ceil((endDate - startDate + 24 * 60 * 60 * 1000) / (7 * 24 * 60 * 60 * 1000)) || 1;
        var amount = ((sort === 1 ? priceBlock1 : priceBlock2) * weeks).toFixed(2);
        var period = flatpickr.formatDate(startDate, "d.m.Y") +
          " - " +
          flatpickr.formatDate(endDate, "d.m.Y");

        // Временная кнопка для payload
        var tempBtn = $('<button>')
          .data("service-type", serviceType)
          .data("service-slug", serviceSlug)
          .data("classifications-id", classificationId)
          .data("specialist-id", specialistId)
          .data("price-block-1", priceBlock1)
          .data("price-block-2", priceBlock2)
          .data("idx", idx)
          .data("order-id", $form.find("input[name=orderId]").val() || orderId)
          .data("date-start", dateStart)
          .data("date-expires", dateExpires);

        var payload = buildConnectPayloadFrom(tempBtn);
        payload.moderation = 0;
        payload.service_name = serviceName;
        payload.sort = sort;
        payload.service_slug = serviceSlug;
        payload.date_start = dateStart;
        payload.date_expires = dateExpires;

        // Отправляем промо‑payload сразу
        /* doConnectService(payload, null); */

        // Заполняем модалку
        $("#service-name").val("Размещение карточки в промо-блоках");
        $("#period").val(period);
        $("#place").val(placeText);
        $("#amount").val(amount + " руб.");
        $("#inn").val("");

        // Скрытые поля промо
        $form.append(`<input type="hidden" name="specialistId"   value="${specialistId}">`);
        $form.append(`<input type="hidden" name="service_slug"     value="${serviceSlug}">`);
        $form.append(`<input type="hidden" name="placement"        value="${placement}">`);
        $form.append(`<input type="hidden" name="date_start"       value="${dateStart}">`);
        $form.append(`<input type="hidden" name="date_end"     value="${dateExpires}">`);
        $form.append(`<input type="hidden" name="pay_amount"       value="${amount}">`);
        $form.append(`<input type="hidden" name="specialist_email" value="${$btn.data("email") || ""}">`);
      }

      // Показываем модальное окно в любом случае
      new bootstrap.Modal(document.getElementById('invoiceModal')).show();
    });


    // === Обработка отправки формы ===
    $(document).on("submit", "#invoice-form", function (e) {
      e.preventDefault();

      var $form = $(this);
      var $submitBtn = $form.find('button[type="submit"]');
      var originalBtnHtml = $submitBtn.html();

      // Теперь тип достаётся из скрытого поля (может не использоваться)
      var type = $form.find('input[name="invoice_type"]').val();

      var inn = $("#inn").val().trim();
      var customerType = inn.length === 12 ? "ip" : inn.length === 10 ? "company" : null;
      if (!customerType) {
        return alert("ИНН должен быть 10 или 12 цифр");
      }

      // Добавляем поля юр.лица
      var formData = $(this).serialize() +
        "&customer_type=" + customerType +
        "&tax_code=" + encodeURIComponent(inn);

      // Отправляем на Toчку
      $.ajax({
        type: "POST",
        url: "../actions/payment/tochka_invoice.php",
        data: formData,
        dataType: "json",
        beforeSend: function () {
          $submitBtn
            .prop("disabled", true)
            .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="border-width:.15em; border-right-color:transparent;"></span>');
        },
        statusCode: {
          200: function (res) {
            // Шлем письмо
            $.post("../actions/mail/send_mail_tochka.php", {
              email: $("[name=specialist_email]").val() || "",
              download_url: res.download_url || "",
              service_name: $("#service-name").val()
            });
            alert("Счёт успешно отправлен");
            $("#invoiceModal").modal("hide");
          }
        },
        error: function (xhr) {
          alert("Ошибка при отправке счёта: " + (xhr.responseJSON?.error || "Неизвестная ошибка"));
        },
        complete: function () {
          $submitBtn.prop("disabled", false).html(originalBtnHtml || "Отправить счёт");
        }
      });
    });

   /*  $(document).on("submit", "#invoice-form", function (e) {
      e.preventDefault();
    
      // Шлём письмо
      $.post("../actions/mail/send_mail_tochka.php", {
        email: $("[name=specialist_email]").val() || "",
        service_name: $("#service-name").val()
      })
      .done(function () {
        alert("Письмо успешно отправлено");
        $("#invoiceModal").modal("hide");
      })
      .fail(function (xhr) {
        alert("Ошибка при отправке письма: " + (xhr.responseJSON?.error || "Неизвестная ошибка"));
      });
    }); */


    $(document).on("input change", ".promo-options input", function () {
      const $promoOptions = $(this).closest(".promo-options");
      // Теперь кнопка находится ВНУТРИ этого promo-options
      $promoOptions.find(".connect-service").prop("disabled", false);
    });


    let newEmployeeCounter = 0;
    $(document).on("click", "#add-employee-btn", function () {
      // Генерируем уникальный ключ для нового сотрудника
      const newKey = "new_" + newEmployeeCounter;
      newEmployeeCounter++;

      // Создаем HTML для нового блока сотрудника
      let newEmployeeHtml = `
        <div class="employee mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Фотография</label>
                    <input type="file" name="employees[${newKey}][photo]" class="form-control">
                </div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Имя</label>
                            <input type="text" name="employees[${newKey}][name]" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Фамилия</label>
                            <input type="text" name="employees[${newKey}][lastname]" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Должность</label>
                            <input type="text" name="employees[${newKey}][post]" class="form-control">
                        </div>
                        <div class="col-md-2 mt-4">
                            <button type="button" class="btn btn-danger remove-employee-btn mt-2">Удалить</but>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Для новых сотрудников поле id может быть пустым -->
            <input type="hidden" name="employees[${newKey}][id]" value="">
        </div>
    `;

      // Добавляем новый блок в контейнер сотрудников
      $("#employees-container").append(newEmployeeHtml);
    });
    $(document).on("click", ".remove-employee-btn", function () {
      $(this).closest(".employee").remove();
    });
  },
  bindEvents: function () {
    let self = this;

    $(self.selectors.pageSelector).on(
      "change",
      self.selectors.classificationSelect,
      function () {
        self.handleClassificationChange();

        const selectedVal = $(this).val();

        $("#input-classification-company_id").val(selectedVal);
      }
    );
  },
  handleClassificationChange: function () {
    let self = this;
    var selectedId = parseInt($(self.selectors.classificationSelect).val(), 10);
    var specialIds = [5, 6, 7, 8];

    if (specialIds.includes(selectedId)) {
      $(self.selectors.companyFieldsSelector).show();
      $(self.selectors.universityField).hide();
      $(self.selectors.companyContentSelector).show();
      $(self.selectors.companyTabSelector).show();

      // Сделать поле классификации бледным
      /* $(self.selectors.classificationSelect).addClass("select-faded"); */
    } else {
      $(self.selectors.companyFieldsSelector).hide();
      $(self.selectors.universityField).show();
      $(self.selectors.companyContentSelector).hide();
      $(self.selectors.companyTabSelector).hide();

      // Вернуть нормальный вид
      /* $(self.selectors.classificationSelect).removeClass("select-faded"); */
    }
  },
  initModalListeners: function () {
    const metaTitleInput = document.getElementById("input-meta-title");
    const metaDescriptionInput = document.getElementById(
      "input-meta-description"
    );
    const titleInput = document.getElementById("input-name");

    // Функция для обрезки текста до 150 символов
    function generateMetaDescription(text) {
      return text.length > 150 ? text.substring(0, 150) + "..." : text;
    }

    function decodeHTMLEntities(text) {
      let doc = new DOMParser().parseFromString(text, "text/html");
      return doc.documentElement.textContent;
    }

    if (titleInput) {
      titleInput.addEventListener("input", function () {
        metaTitleInput.value = titleInput.value;
      });
    }

    if (
      typeof tinymce !== "undefined" &&
      tinymce.get(
        Specialists.selectors.pageDescriptionSelector.replace("#", "")
      )
    ) {
      tinymce
        .get(Specialists.selectors.pageDescriptionSelector.replace("#", ""))
        .on("change", function () {
          const contentHtml = tinymce
            .get(Specialists.selectors.pageDescriptionSelector.replace("#", ""))
            .getContent();
          const textContent = contentHtml.replace(/<\/?[^>]+(>|$)/g, "").trim();
          const decodedText = decodeHTMLEntities(textContent);
          metaDescriptionInput.value = generateMetaDescription(decodedText);
        });
    }
  },
  initTinyMCE: function () {
    if (typeof tinymce !== "undefined") {
      tinymce.remove(Specialists.selectors.pageDescriptionSelector);

      tinymce.init({
        selector: Specialists.selectors.pageDescriptionSelector,
        plugins: "lists autolink", // удалили 'link'
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

        // Отключаем настройки, связанные со ссылками
        relative_urls: false,
        remove_script_host: false,
        convert_urls: false,
        readonly: false,
        setup: function (editor) {
          let modalEl = document.querySelector(
            Specialists.selectors.modalEditSelector
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
              const input = document.querySelector(".tox-dialog input");
              if (input) input.focus();
            }, 50);
          });

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
  previewFile: function (previewElement, file, fileInput) {
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        previewElement.attr("src", e.target.result); // Обновление превью
        fileInput.attr("data-file", file.name); // Обновление атрибута data-file
      };
      reader.readAsDataURL(file);
    }
  },
  modalAdd: function () {
    let button = $(this),
      id = button.attr("data-id") ?? 0,
      buttonContent = button.html();

    $.ajax({
      type: "POST",
      url: "actions/specialist/get_modal.php",
      dataType: "json",
      data: { id: id },
      beforeSend: function () {
        Helper.loadingButton(button);
        $(Specialists.selectors.modalAddSelector).remove();
      },
      success: function (json) {
        $(Specialists.selectors.pageSelector).append(json.code);
        $(Specialists.selectors.modalAddSelector).modal("show");
        Helper.loadingButton(button, buttonContent);
        Specialists.initTinyMCE();
        Specialists.initModalListeners();
        Specialists.handleClassificationChange();
      },
    });
  },
  add: function () {
    let button = $(this),
      id = button.attr("data-id") ?? 0,
      buttonContent = button.html(),
      formData = new FormData();

    if (id !== 0) {
      formData.append("id", id);
    } else {
      // Если id равно 0, не передаем его вообще
      formData.append("id", ""); // Или можно использовать null
    }

    formData.append("name", $("#input-name").val());
    formData.append("lastname", $("#input-lastname").val());
    formData.append("slug", $("#input-slug").val());

    if ($(Specialists.selectors.photoSelector)[0].files[0]) {
      formData.append(
        "photo",
        $(Specialists.selectors.photoSelector)[0].files[0]
      );
    }

    if (photoRemoved) {
      formData.append("remove_photo", "1");
    }

    formData.append("city_id", $("#input-city_id").val());
    formData.append("email", $("#input-email").val());
    formData.append("phone", $("#input-phone").val());
    formData.append("password", $("#input-password").val());
    formData.append("type", $("#input-type").val());
    formData.append("classification_id", $("#input-classification_id").val());

    // Добавляем данные компании
    const classificationId = parseInt(
      $("#input-classification-company_id").val(),
      10
    );
    const specialIds = [5, 6, 7, 8];

    if (specialIds.includes(classificationId)) {
      formData.append("company_id", $("#input-company_id").val());
      formData.append("company_name", $("#input-company").val());
      formData.append("company_classification_id", classificationId);
    } else {
      formData.append("remove_company", 1);

      let universityValues = [];
      $('input[name="university[]"]').each(function () {
        const val = $(this).val().trim();
        if (val) universityValues.push(val);
      });

      formData.append("university", JSON.stringify(universityValues));
    }

    $(".company-employees .employee").each(function (index) {
      let employeeBlock = $(this);
      let employeeId = employeeBlock.find(`input[name$="[id]"]`).val() || index;
      let employeeName = employeeBlock.find(`input[name$="[name]"]`).val();
      let employeeLastname = employeeBlock
        .find(`input[name$="[lastname]"]`)
        .val();
      let employeePost = employeeBlock.find(`input[name$="[post]"]`).val();
      let employeePhoto = employeeBlock.find(`input[type="file"]`)[0]?.files[0];

      console.log(employeePhoto); // Логируем файл

      // Добавляем в formData только если поля не пустые
      if (employeeName && employeePost) {
        formData.append(`employees[${employeeId}][name]`, employeeName);
        formData.append(`employees[${employeeId}][lastname]`, employeeLastname);
        formData.append(`employees[${employeeId}][post]`, employeePost);
        if (employeePhoto) {
          formData.append(`employees[${employeeId}][photo]`, employeePhoto);
        }
      }
    });

    if ($("#input-active:checked").length)
      formData.append("active", $("#input-active:checked").val());

    formData.append("contacts_email", $("#input-contacts-email").val());
    formData.append("contacts_site", $("#input-contacts-site").val());
    formData.append("contacts_tg", $("#input-contacts-tg").val());
    formData.append("contacts_vk", $("#input-contacts-vk").val());
    formData.append("contacts_dzen", $("#input-contacts-dzen").val());

    // Блок контакты
    formData.append("address", $("#contact-address").val());
    formData.append("metro", $("#contact-metro").val());
    formData.append("street", $("#contact-street").val());
    formData.append("house", $("#contact-house").val());
    formData.append("how_to_get", $("#contact-how-to-get").val());

    let scheduleParts = {};

    // Заполняем начальную структуру
    for (let i = 1; i <= 7; i++) {
      scheduleParts[i] = {
        start: false,
        end: false,
        b_start: false,
        b_end: false
        // vac не добавляем по умолчанию
      };
    }

    $("[name^='schedule']").each(function () {
      let name = $(this).attr("name");
      let dayMatch = name.match(/\[(\d+)\]/);
      let fieldMatch = name.match(/\[([a-z_]+)\]/);

      if (!dayMatch || !fieldMatch) return;

      let day = dayMatch[1];
      let fieldName = fieldMatch[1];
      let value = $(this).val();

      if (fieldName === "vac") {
        // Если чекбокс НЕ отмечен → это рабочий день → добавляем vac: false
        if (!$(this).is(":checked")) {
          scheduleParts[day]["vac"] = false;
        }
        // Если чекбокс отмечен — ничего не добавляем (по умолчанию будет выходной)
      } else {
        scheduleParts[day][fieldName] = value ? value : false;
      }
    });

    // Теперь scheduleParts не содержит vac у выходных
    let scheduleString = JSON.stringify(scheduleParts);
    formData.append("schedule", scheduleString);

    let documents = {};

    // Обрабатываем старые документы
    $("[data-document]").each(function () {
      let docKey = $(this).data("document");
      let nameInput = $(`[name="documents[${docKey}][name]"]`);
      let fileInput = $(`[name="documents[${docKey}][file]"]`)[0];
      let file = fileInput.files[0];

      // Сохраняем в JSON
      documents[docKey] = {
        name: nameInput.val() || false,
        file: file ? file.name : $(fileInput).data("file") || false,
      };

      // Добавляем файл в formData, если есть
      if (file) {
        formData.append(`documents[${docKey}][file]`, file);
      }

      // Добавляем имя
      formData.append(`documents[${docKey}][name]`, nameInput.val());
    });

    // Новый документ
    let newNameDoc = $("input[name='documents[new][name]']").val();
    let newFileInput = $("input[name='documents[new][file]']")[0];
    let newFile = newFileInput.files[0];

    if (newNameDoc || newFile) {
      documents["new"] = {
        name: newNameDoc || false,
        file: newFile ? newFile.name : false,
      };

      // Добавляем в formData
      formData.append(`documents[new][name]`, newNameDoc);
      if (newFile) {
        formData.append(`documents[new][file]`, newFile);
      }
    }

    formData.append("foundation", $("#input-foundation").val());
    formData.append(
      "page-discription",
      tinymce
        .get(Specialists.selectors.pageDescriptionSelector.replace("#", ""))
        .getContent()
    );

    // Сбор данных об услугах
    $("#content-services .row[data-service]").each(function () {
      let serviceKey = $(this).data("service"),
        name = $(this).find(`[name="services[${serviceKey}][name]"]`).val(),
        price = $(this).find(`[name="services[${serviceKey}][price]"]`).val(),
        isActive = $(this)
          .find(`[name="services[${serviceKey}][is_active]"]`)
          .is(":checked")
          ? 1
          : 0;

      if (name && price) {
        formData.append(`services[${serviceKey}][name]`, name);
        formData.append(`services[${serviceKey}][price]`, price);
        formData.append(`services[${serviceKey}][is_active]`, isActive);
      }
    });

    // Сбор данных о новой услуге, если добавлена
    let newName = $(`#service-new-name`).val(),
      newPrice = $(`#service-new-price`).val(),
      newIsActive = $(`#service-new-active`).is(":checked") ? 1 : 0;

    if (newName && newPrice) {
      formData.append(`services[new][name]`, newName);
      formData.append(`services[new][price]`, newPrice);
      formData.append(`services[new][is_active]`, newIsActive);
    }

    // Добавление выбранных специализаций
    $(".form-check-input[name^='specializations']:checked").each(function () {
      let specializationId = $(this).attr("name").match(/\d+/)[0];
      formData.append(`specializations[${specializationId}]`, 1);
    });

    formData.append("meta_title", $("#input-meta-title").val());
    formData.append("meta_description", $("#input-meta-description").val());
    formData.append("meta_keywords", $("#input-meta-keywords").val());

    $.ajax({
      type: "POST",
      url: "actions/specialist/add_specialist.php",
      dataType: "json",
      contentType: false,
      processData: false,
      data: formData,
      beforeSend: function () {
        Helper.loadingButton(button);
        Helper.removeValidation(Specialists.selectors.modalAddSelector);
      },
      success: function (json) {
        Helper.loadingButton(button, buttonContent);
        if (json.error) {
          $.each(json.error, function (key, value) {
            $(`#input-${key}`)
              .addClass("is-invalid")
              .after(Helper.invalidFeedback(value));
          });
          if (json.error) {
            let errorMessages = Object.values(json.error).join("<br>"); // Объединяем ошибки в один текст с переносами строк

            // Проверяем, есть ли уже alert, если да – удаляем
            $(".alert-error").remove();

            // Добавляем новый alert перед формой
            $(Specialists.selectors.modalAddSelector).prepend(`
                    <div class="alert alert-danger alert-error" role="alert">
                        ${errorMessages}
                    </div>
                `);
          }

          let errorMessages = Object.values(json.error).join("<br>");

          $(".alert-error").remove();

          $(Specialists.selectors.modalAddSelector).prepend(`
                <div class="alert alert-danger alert-error" role="alert">
                    ${errorMessages}
                </div>
            `);
        }

        if (json.fatal) {
          Helper.fatalError(json.fatal);
        }

        if (json.success) {
          location.reload();
        }
      },
    });
  },
  manageRequest: function () {
    let button = $(this);
    let recordId = button.data("id"); // ID записи подключения (должен совпадать с id контейнера)
    let type = button.data("type"); // "banner" или "journal"
    let action = button.data("action"); // "publish" или "delete"

    let confirmText =
      action === "publish"
        ? "Разместить запрос с обновлёнными данными?"
        : "Отклонить запрос?";
    if (!confirm(confirmText)) {
      return;
    }

    // Формируем базовую нагрузку (payload)
    let payload = { id: recordId, type: type, action: action };

    // Если публикуем, считываем дополнительные поля из контейнера
    if (action === "publish") {
      // Изменили выбор контейнера — теперь ищем ближайшую карточку, где находится кнопка
      let container = button.closest(".card");
      payload.ad_line1 = container.find(".ad_line1").val() || "";
      payload.ad_line2 = container.find(".ad_line2").val() || "";
      payload.ad_line3 = container.find(".ad_line3").val() || "";
      payload.start_date = container.find(".start_date").val() || "";
      payload.end_date = container.find(".end_date").val() || "";
    }

    $.ajax({
      type: "POST",
      url: "actions/specialist/manage_request.php",
      data: payload,
      beforeSend: function () {
        Helper.loadingButton(button);
      },
      success: function (response) {
        try {
          let res = JSON.parse(response);
          if (res.success) {
            // При успешном обновлении скрываем контейнер запроса
            $("#" + type + "-request-details-" + recordId).fadeOut();
            location.reload();
          } else {
            alert(res.error || "Ошибка выполнения запроса");
          }
        } catch (e) {
          console.error("Неверный формат ответа:", response);
        }
      },
      error: function (error) {
        console.error("Ошибка запроса:", error);
      },
    });
  },
  connectService: function () {
    var $btn = $(this);
    var idx = $btn.data("idx");
    var orderId = $btn.data("order-id");
    var $wrapper = $btn.closest(".promo-options-wrapper");
    var $optionsRow = $wrapper.find('.promo-options[data-idx="' + idx + '"]');
    var $currentRow = $wrapper.prev(".row");

    // Данные
    var serviceType = $btn.data("service-type");    // "page", "child" или "parent"
    var id = $btn.data("id");
    var blockId = $btn.data("block-id");
    var specialist_id = $btn.data("specialist-id");
    var service_slug = $btn.data("service-slug");
    var classificationId = $btn.data("classifications-id");

    var priceBlock1 = parseFloat($btn.data("price-block-1")) || 0;
    var priceBlock2 = parseFloat($btn.data("price-block-2")) || 0;

    // Заголовок
    var serviceTitle = $currentRow.find(".col-3").text().trim();
    var serviceName = serviceType === "child"
      ? 'Подраздел "' + serviceTitle + '"'
      : serviceType === "parent"
        ? 'Раздел "' + serviceTitle + '"'
        : serviceTitle;

    // Считываем sort из radio внутри своего ряда
    var sort = 0;
    var val = $optionsRow.find('input[name="promoPlacement_' + idx + '"]:checked').val();
    if (val == "block_top_specialists") sort = 1;
    else if (val == "block_specialist_positions") sort = 2;

    // Даты
    var dateStart = $optionsRow.find(".date-start").val();
    var dateExpires = $optionsRow.find(".date-expires").val();

    var weeks = 1;
    if (dateStart && dateExpires) {
      var startDate = new Date(dateStart);
      var endDate = new Date(dateExpires);
      var timeDiff = endDate - startDate + (24 * 60 * 60 * 1000); // включаем последний день
      weeks = Math.ceil(timeDiff / (7 * 24 * 60 * 60 * 1000));
    }

    // Рассчитываем сумму за все недели
    var baseAmount = sort == 1 ? priceBlock1 : priceBlock2;
    var amount = (baseAmount * weeks).toFixed(2);

    // blockId по классификации
    /* var blockId = "";
    if ([1, 2, 5, 6, 7].includes(classificationId)) blockId = 1;
    if ([3, 8].includes(classificationId)) blockId = 2;
    if (classificationId === 4) blockId = 3; */

    // Собираем payload
    var payload = {
      specialist_id: specialist_id,
      sort: sort,
      order_id: orderId,
      date_start: dateStart,
      date_expires: dateExpires,
      service_name: serviceName,
      amount: amount,
      service_slug: service_slug
    };
    if (blockId) payload.block_id = blockId;
    if (id) payload.id = id;

    // AJAX-запрос
    $.ajax({
      type: "POST",
      url: "actions/specialist/connect_service.php",
      data: payload,
      beforeSend: function () {
        $btn.prop("disabled", true).text("Подключение...");
      },
      success: function (response) {
        try {
          var res = JSON.parse(response);
          if (res.success) {
            // Обновляем кнопку Отключить внутри своего ряда
            $optionsRow.find(".disconnect").each(function () {
              var $disc = $(this);
              if (!$disc.data("id")) {
                $disc.data("id", res.id).prop("disabled", false);
              }
            });

            // Меняем бейдж на "Подключено"
            var $badge = $currentRow.find(".btn-sm.text-white");
            $badge.removeClass("btn-secondary").addClass("btn-info").text("Подключено");

            // Обновляем data-connected и data-connections на кнопке "Проверить"
            var $check = $currentRow.find(".check-service");
            $check.data("connected", 1);
            if (res.updatedConnections) {
              $check.attr("data-connections", JSON.stringify(res.updatedConnections));
            }

            // Перезагрузка для синхронизации
            location.reload();
          } else {
            alert(res.error || "Ошибка выполнения запроса");
          }
        } catch (e) {
          console.error("Неверный формат ответа:", response);
        }
      },
      error: function (err) {
        console.error("Ошибка AJAX:", err);
      },
      complete: function () {
        $btn.prop("disabled", false).text("Подключить");
      }
    });
  },
  promoAdd: function () {
    let button = $(this);
    let promoId = button.data("id");
    let specialistId = button.data("specialist-id");
    let type = button.data("type");

    let durationCheckbox = $(`input[name="duration_${promoId}"]:checked`);
    let selectedDuration = durationCheckbox.val();

    let durationCheckboxOption = $(`input[name="duration_${promoId}"]:checked`);
    let selectedDurationOption = durationCheckboxOption.val();
    var price = parseFloat(button.data("price")) || 0;
    let priceOld = parseFloat(button.data("price-old")) || price;
    var orderId = Date.now();

    if (type == "option") {
      if (!selectedDurationOption) {
        alert("Пожалуйста, выберите срок подключения");
        return;
      }
      if (!confirm("Подключить опцию?")) {
        return;
      }
      let discount = parseFloat(button.data("discount")) || 0;
      let finalPrice = discount ? (price * (1 - discount / 100)).toFixed(2) : price.toFixed(2);

      let payload = {
        specialist_id: specialistId,
        option_id: promoId,
        order_id: orderId,
        amount: finalPrice
      };

      $.ajax({
        type: "POST",
        url: "actions/specialist/connect_option.php",
        data: payload,
        beforeSend: function () {
          // Показываем индикатор загрузки на кнопке
          button
            .prop("disabled", true)
            .html(
              '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Подключение...'
            );
        },
        success: function (response) {
          try {
            let res = JSON.parse(response);
            if (res.success) {
              // Успешное подключение
              alert("Опция успешно подключен!");
              location.reload(); // Перезагружаем страницу для обновления данных
            } else {
              // Ошибка
              alert(res.error || "Ошибка при подключении опции");
            }
          } catch (e) {
            console.error("Неверный формат ответа:", response);
            alert("Ошибка при обработке ответа сервера");
          }
        },
        error: function (error) {
          console.error("Ошибка запроса:", error);
          alert("Ошибка при отправке запроса");
        },
        complete: function () {
          // Восстанавливаем кнопку
          button.prop("disabled", false).html("Подключить");
        },
      });
    }

    if (type == "tariff") {
      if (!selectedDuration) {
        alert("Пожалуйста, выберите срок подключения (1 или 3 месяца).");
        return;
      }

      if (!confirm("Подключить тариф?")) {
        return;
      }

      let duration = parseInt(selectedDuration, 10);
      let finalAmount = (duration === 1) ? priceOld : price * duration;

      let payload = {
        specialist_id: specialistId,
        tariff_id: promoId,
        duration: parseInt(selectedDuration, 10),
        order_id: orderId,
        amount: finalAmount.toFixed(2)
      };

      $.ajax({
        type: "POST",
        url: "actions/specialist/connect_tariff.php",
        data: payload,
        beforeSend: function () {
          // Показываем индикатор загрузки на кнопке
          button
            .prop("disabled", true)
            .html(
              '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Подключение...'
            );
        },
        success: function (response) {
          try {
            let res = JSON.parse(response);
            if (res.success) {
              // Успешное подключение
              alert("Тариф успешно подключен!");
              location.reload(); // Перезагружаем страницу для обновления данных
            } else {
              // Ошибка
              alert(res.error || "Ошибка при подключении тарифа");
            }
          } catch (e) {
            console.error("Неверный формат ответа:", response);
            alert("Ошибка при обработке ответа сервера");
          }
        },
        error: function (error) {
          console.error("Ошибка запроса:", error);
          alert("Ошибка при отправке запроса");
        },
        complete: function () {
          // Восстанавливаем кнопку
          button.prop("disabled", false).html("Подключить");
        },
      });
    }
  },
  promoDelete: function () {
    let button = $(this);
    let promoId = button.attr("data-id");
    let specialistId = button.data("specialist-id");
    let type = button.data("type");

    const typeNames = {
      tariff: "тариф",
      option: "опцию",
      promo: "промо-размещение",
    };

    if (
      !typeNames[type] ||
      !confirm(`Вы уверены, что хотите удалить ${typeNames[type]}?`)
    ) {
      return;
    }

    let payload = {
      specialist_id: specialistId,
      id: promoId,
      type: type,
    };

    $.ajax({
      type: "POST",
      url: "actions/specialist/disconnect.php",
      data: payload,
      beforeSend: function () {
        button
          .prop("disabled", true)
          .html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Удаление...'
          );
      },
      success: function (response) {
        try {
          let res = JSON.parse(response);
          if (res.success) {
            alert(`${type === "tariff" ? "Тариф" : "Опция"} успешно удален!`);
            location.reload();
          } else {
            alert(res.error || "Ошибка при удалении");
          }
        } catch (e) {
          console.error("Ошибка парсинга ответа:", response);
          alert("Ошибка при обработке ответа сервера");
        }
      },
      error: function (error) {
        console.error("Ошибка запроса:", error);
        alert("Ошибка при отправке запроса");
      },
      complete: function () {
        button.prop("disabled", false).html("Отключить");
      },
    });
  },
  delete: function () {
    let id = $(this).attr("data-id"),
      button = $(this);

    if (confirm("Вы уверены, что хотите удалить специалиста?")) {
      $.ajax({
        type: "POST",
        url: "actions/specialist/delete_specialists.php",
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
  file: function () {
    $(Specialists.selectors.photoSelector).click();
  },
  classification: function () {
    let type = +$(this)
      .children(`option[value=${$(this).val()}]`)
      .attr("data-type");
    $(this).siblings("#input-type").val(type);

    if (type === 1) $("#input-company").css("display", "none");
    if (type === 2) $("#input-company").css("display", "block");
  },
  deleteReview: function (reviewId, reviewElement) {
    if (!confirm("Вы уверены, что хотите удалить этот отзыв?")) {
      return;
    }

    $.ajax({
      type: "POST",
      url: "actions/specialist/delete_review.php",
      data: { id: reviewId },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          reviewElement.remove();
        } else {
          alert("Ошибка при удалении отзыва: " + response.error);
        }
      },
      error: function () {
        alert("Ошибка при отправке запроса на сервер.");
      },
    });
  },
};

export { Specialists };

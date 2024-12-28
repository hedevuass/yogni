// noinspection JSCheckFunctionSignatures

import { Helper } from "./helper.js";

let Specialists = {
  selectors: {
    pageSelector: "#specialistPage",
    modalAddSelector: "#modalAddSpecialist",
    photoSelector: "#input-photo",
    photoSelectorEmployee: "#input-photo-employee",
    documentFileInput: ".document-file-input",
    documentPreview: ".document-preview",
    uploadTrigger: ".upload-trigger",
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
      columns: [
        { data: "id" },
        { data: "photo" },
        { data: "name" },
        { data: "email" },
        { data: "phone" },
        { data: "classification" },
        { data: "city" },
        { data: "rating" },
        { data: "active" },
        { data: "action" },
      ],
    });

    $(this.selectors.pageSelector).on("click", ".delete", Specialists.delete);
    $(this.selectors.pageSelector).on(
      "click",
      ".modalAdd",
      Specialists.modalAdd
    );
    $(this.selectors.pageSelector).on(
      "click",
      ".upload-avatar",
      Specialists.file
    );
    $(this.selectors.pageSelector).on(
      "click",
      ".upload-avatar",
      Specialists.file
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
    $(this.selectors.pageSelector).on(
      "input",
      "#input-phone",
      Helper.validationPhone
    );
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

    $(document).on("click", ".delete-service-btn", function () {
      // Получаем родительский элемент, который представляет услугу
      var serviceRow = $(this).closest(".service-row");

      // Удаляем строку из DOM
      serviceRow.remove();
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
      },
    });
  },
  add: function () {
    let button = $(this),
      id = button.attr("data-id") ?? 0,
      buttonContent = button.html(),
      formData = new FormData();

    if (id) formData.append("id", id);

    formData.append("name", $("#input-name").val());
    formData.append("lastname", $("#input-lastname").val());

    if ($(Specialists.selectors.photoSelector)[0].files[0]) {
      formData.append(
        "photo",
        $(Specialists.selectors.photoSelector)[0].files[0]
      );
    }

    formData.append("city_id", $("#input-city_id").val());
    formData.append("email", $("#input-email").val());
    formData.append("phone", $("#input-phone").val());
    formData.append("password", $("#input-password").val());
    formData.append("type", $("#input-type").val());
    formData.append("classification_id", $("#input-classification_id").val());

     // Добавляем данные компании
     formData.append("company_id", $("#input-company_id").val());
     formData.append("company_name", $("#input-company").val());
     formData.append("company_classification_id", $("#input-classification-company_id").val());
 
     // Добавляем данные сотрудников компании
     $(".company-employees .employee").each(function (index) {
      let employeeBlock = $(this);
      
      // Если id сотрудника существует, то используем его, иначе передаем индекс
      let employeeId = employeeBlock.find(`input[name$="[id]"]`).val() || index; // Если id не найден, используем индекс как id
      
      let employeeName = employeeBlock.find(`input[name$="[name]"]`).val();
      let employeePost = employeeBlock.find(`input[name$="[post]"]`).val();
      let employeePhoto = employeeBlock.find(`input[type="file"]`)[0]?.files[0];
  
      // Добавляем данные о сотруднике в formData
      if (employeeName && employeePost) {
          formData.append(`employees[${employeeId}][name]`, employeeName);
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

    let scheduleParts = {};

    $("[name^='schedule']").each(function () {
      let day = $(this).attr("name").match(/\d+/)[0];

      if (!scheduleParts[day]) {
        scheduleParts[day] = {
          start: false,
          end: false,
          b_start: false,
          b_end: false,
          vac: false,
        };
      }

      let fieldName = $(this)
        .attr("name")
        .match(/\[([a-z_]+)\]/)[1];

      let value = $(this).val();

      if (fieldName === "vac") {
        scheduleParts[day][fieldName] = $(this).is(":checked") ? true : false;
      } else {
        scheduleParts[day][fieldName] = value ? value : false;
      }
    });

    // Формируем строку расписания
    let scheduleString = JSON.stringify(scheduleParts);

    formData.append("schedule", scheduleString);

    let documents = {};

    $("[data-document]").each(function () {
      let docKey = $(this).data("document");
      let nameInput = $(`[name="documents[${docKey}][name]"]`);
      let fileInput = $(`[name="documents[${docKey}][file]"]`)[0];

      documents[docKey] = {
        name: nameInput.val() || false,
        file: fileInput.files[0]
          ? fileInput.files[0].name
          : $(fileInput).data("file") || false,
      };
    });

    let documentsString = JSON.stringify(documents);

    formData.append("documents", documentsString);

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
        }

        if (json.fatal) {
          Helper.fatalError(json.fatal);
        }

        if (json.success) {
          /* location.reload() */
        }
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
};

export { Specialists };

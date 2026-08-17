$(function () {
  $(
    ".popup .bg, .popup:not(.delete-confirmation-popup) .window .close, .popup .window .close-popup-btn"
  ).click(function () {
    var popup = $(this).closest(".popup");
    if (popup.attr("id") !== "tariff-popup") {
      popup.fadeOut(function () {
        popup.find("form").trigger("reset");
      });
    } else {
      popup.fadeOut();
    }

    $("body").removeClass("hid");
  });
  $("#scrollToFormati").click(function (e) {
    e.preventDefault();
    $("html, body").animate(
      { scrollTop: $("#formati").offset().top - 50 },
      1000
    );
  });
  $(
    ".popup.delete-confirmation-popup .window .close, .popup.delete-confirmation-popup .bg"
  ).click(function () {
    var popup = $(this).closest(".popup");
    popup.fadeOut();
    $("body").removeClass("hid");
    $(location).attr("href", "/actions/account/delete-user.php");
  });
  $(".create-article-popup").on("click", ".close, .bg", function () {
    let link_id = $(".create-article-popup").attr("data-link-id");
    $(location).attr("href", "/cabinet/moy-blog");
  });
  $(".cabinet-popup .popup__close").click(function () {
    $(".cabinet-popup").fadeOut(200);
    $("body").removeClass("hid");
  });
  $(".show-contacs-card").click(function () {
    $("#cabinet-contacts-card-mobile").fadeIn(200);
    $("body").addClass("hid");
  });
  $(".profile-photo-mmenu").click(function () {
    $("#change-photo-mobile").fadeIn(200);
    $("body").addClass("hid");
  });
  $(".delete-account").click(function () {
    $(".delete-account-popup").fadeIn(200);
    $("body").addClass("hid");
  });
  $("#not-delete-btn").click(function () {
    $(".delete-account-popup").fadeOut(200);
    $("body").removeClass("hid");
  });
  $("#delete-account-btn").click(function () {
    $(".delete-account-popup").fadeOut(200);
    $(".delete-confirmation-popup").fadeIn(200);
  });
  $("#not-delete-btn2").click(function () {
    $(".delete-confirmation-popup").fadeOut(200);
    $(".recovered-popup").fadeIn(200);
  });
  $("#delete-confirm-btn").click(function () {
    $(".delete-confirmation-popup").fadeOut(200);
  });
  $(".specialization-quantity").click(function () {
    $("#cabinet-ways").fadeIn(200);
    $("body").addClass("hid");
  });
  $(".edit-info-item").click(function () {
    $(this).toggleClass("opened");
    $(this).siblings(".submenu").slideToggle();
  });
  $("#cabinet-ways.cabinet-popup .confirm-ways-mob").click(function () {
    let selected = $("#cabinet-specialization-mobile input[type=checkbox]:checked").length;

    $(".specialization-quantity").removeClass("active");
    if (selected > 0) {
      $(".specialization-quantity")
        .text("Выбрано " + selected + " направлений")
        .addClass("active");
    } else {
      $(".specialization-quantity").text("Направления не выбраны");
    }

    $('#cabinet-specialization-mobile button[type="submit"]')
      .prop("disabled", false)
      .text("Сохранить информацию");

    const $btn = $(".confirm-ways-mob");
    $btn.addClass("disabled");
    $btn.text("Подтверждение");

    // Через 1 секунду меняем текст
    setTimeout(() => {
      $btn.text("Подтверждено");
    }, 1000);

    // Ещё через 1 секунду (т.е. через 2 секунды от нажатия) — закрываем попап
    setTimeout(() => {
      $(".cabinet-popup").fadeOut(200);
      $("body").removeClass("hid");
    }, 1500);
  });
  $(
    ".cabinet-page .cabinet-content .mob-ways-block button, .popup-ways header a"
  ).click(function () {
    $(".popup-ways").toggleClass("opened");
  });
  $(".popup-ways ul li a").click(function () {
    $(this).toggleClass("active");
  });
  $(".cabinet-page .cabinet-content .ways-block .select .link").click(
    function () {
      $(this).toggleClass("active");
      $(".cabinet-page .cabinet-content .ways-block .select ul").toggle();
    }
  );
  $(
    ".cabinet-page .cabinet-content .contacts-block .block .days .day-item label"
  ).click(function () {
    $(this).parent().find(".day").toggleClass("holliday");
  });
  $(".day-item .checkbox-break").click(function () {
    $(this).toggleClass("background-for-label");
    $("#break_submit").prop("disabled", false).text("Подтвердить");
  });
  $(document).ready(function () {
    if ($(".day-item .checkbox-break").hasClass("background-for-label")) {
      $(".day-item .checkbox-break.background-for-label").css(
        "backgroundImage",
        'url("../assets/img/cabinet/checkbox6.svg")'
      );
    }
  });
  $(".day-item .checkbox-break").click(function () {
    if ($(this).hasClass("background-for-label")) {
      $("#break_submit").prop("disabled", false).text("Подтвердить");
      $(this).css(
        "backgroundImage",
        'url("../assets/img/cabinet/checkbox6.svg")'
      );
    } else {
      $(this).css("backgroundImage", "url(../assets/img/cabinet/checkbox5.svg");
      $("#break_submit").prop("disabled", false).text("Подтвердить");
    }
  });
  $(".cabinet-page .mob-header .menu-button").click(function () {
    $(this).toggleClass("opened");
    $(".cabinet-page .mob-nav2").fadeToggle(200);
    if ($(this).hasClass("opened")) {
      $(".cabinet-page .mob-nav2").scrollTop(0);
    }
    $("body, .cabinet-content").toggleClass("hid");
  });
  $(".company-page .prices button").click(function () {
    $(".popup.prices").fadeIn();
    $("body").addClass("hid");
  });
  $(".company-page .reviews .buttons button:nth-child(1)").click(function () {
    $(".popup.reviews").fadeIn();
    $("body").addClass("hid");
  });
  $(".company-page .reviews .buttons button:nth-child(2)").click(function () {
    $(".popup.review").fadeIn();
    $("body").addClass("hid");
  });
  $(".company-page .ways button").click(function () {
    $(".popup.ways").fadeIn();
    $("body").addClass("hid");
  });
  $(".popup-contacts .bg").click(function () {
    $(".popup-contacts").removeClass("opened");
  });
  $(".company-page .top-info .contacts-button").click(function () {
    $(".popup-contacts").addClass("opened");
  });
  $(".company-page .contacts-block ul li.more a").click(function () {
    $(".company-page .contacts-block ul li.more").hide();
    $(".company-page .contacts-block .is-sticky ul li.no-sticky").show();
  });
  $(".mobile-fixed-menu .filter-link").click(function () {
    $(".mobile-filter-block").addClass("opened");
  });
  $(".mobile-filter-block .close").click(function () {
    $(".mobile-filter-block").removeClass("opened");
  });
  $(".mobile-filter-block .list label.l1").click(function () {
    $(".mobile-filter-block .list").hide();
    $(".mobile-filter-block .list.cat1").show();
  });
  $(".mobile-filter-block .list label.l2").click(function () {
    $(".mobile-filter-block .list").hide();
    $(".mobile-filter-block .list.cat2").show();
  });
  $(".mobile-filter-block .list label.l3").click(function () {
    $(".mobile-filter-block .list").hide();
    $(".mobile-filter-block .list.cat3").show();
  });
  $(".mobile-filter-block .list .sub-item .back-link").click(function () {
    $(".mobile-filter-block .list p").show();
    $(".mobile-filter-block .list .item").show();
    $(this).parent().hide();
  });
  $(".mobile-filter-block .list .item.with_sub label").click(function () {
    $(".mobile-filter-block .list p").hide();
    $(".mobile-filter-block .list .item").hide();
    $(this).parent().next(".sub-item").show();
  });
  $(".popup .window .select-block .list label.l1").click(function () {
    $(".header .city span").removeClass("active");
    $(".header .city span.c1").addClass("active");
    $(".index-catalog-block .filter .city-link span").removeClass("active");
    $(".index-catalog-block .filter .city-link span.c1").addClass("active");
    $(".popup .window .select-block .m-list").removeClass("active");
    $(".popup .window .select-block .m-list.l1").addClass("active");
    $(".mobile-filter-block .city span").removeClass("active");
    $(".mobile-filter-block .city span.c1").addClass("active");
    $(".mobile-filter-block .list .lst").removeClass("active");
    $(".mobile-filter-block .list .lst.l1").addClass("active");
  });
  $(".popup .window .select-block .list label.l2").click(function () {
    $(".header .city span").removeClass("active");
    $(".header .city span.c2").addClass("active");
    $(".index-catalog-block .filter .city-link span").removeClass("active");
    $(".index-catalog-block .filter .city-link span.c2").addClass("active");
    $(".popup .window .select-block .m-list").removeClass("active");
    $(".popup .window .select-block .m-list.l2").addClass("active");
    $(".mobile-filter-block .city span").removeClass("active");
    $(".mobile-filter-block .city span.c2").addClass("active");
    $(".mobile-filter-block .list .lst").removeClass("active");
    $(".mobile-filter-block .list .lst.l2").addClass("active");
  });
  $(".popup .window .select-block .list label.l3").click(function () {
    $(".header .city span").removeClass("active");
    $(".header .city span.c3").addClass("active");
    $(".index-catalog-block .filter .city-link span").removeClass("active");
    $(".index-catalog-block .filter .city-link span.c3").addClass("active");
    $(".popup .window .select-block .m-list").removeClass("active");
    $(".popup .window .select-block .m-list.l3").addClass("active");
    $(".mobile-filter-block .city span").removeClass("active");
    $(".mobile-filter-block .city span.c3").addClass("active");
    $(".mobile-filter-block .list .lst").removeClass("active");
    $(".mobile-filter-block .list .lst.l3").addClass("active");
  });
  $(".popup .window .select-block .list label.l4").click(function () {
    $(".header .city span").removeClass("active");
    $(".header .city span.c4").addClass("active");
    $(".index-catalog-block .filter .city-link span").removeClass("active");
    $(".index-catalog-block .filter .city-link span.c4").addClass("active");
    $(".popup .window .select-block .m-list").removeClass("active");
    $(".popup .window .select-block .m-list.l4").addClass("active");
    $(".mobile-filter-block .city span").removeClass("active");
    $(".mobile-filter-block .city span.c4").addClass("active");
    $(".mobile-filter-block .list .lst").removeClass("active");
    $(".mobile-filter-block .list .lst.l4").addClass("active");
  });
  $(".popup .window .select-block .list label.l5").click(function () {
    $(".header .city span").removeClass("active");
    $(".header .city span.c5").addClass("active");
    $(".index-catalog-block .filter .city-link span").removeClass("active");
    $(".index-catalog-block .filter .city-link span.c5").addClass("active");
    $(".popup .window .select-block .m-list").removeClass("active");
    $(".popup .window .select-block .m-list.l5").addClass("active");
    $(".mobile-filter-block .city span").removeClass("active");
    $(".mobile-filter-block .city span.c5").addClass("active");
    $(".mobile-filter-block .list .lst").removeClass("active");
    $(".mobile-filter-block .list .lst.l5").addClass("active");
  });
  $(".popup .window .select-block .list label.l6").click(function () {
    $(".header .city span").removeClass("active");
    $(".header .city span.c6").addClass("active");
    $(".index-catalog-block .filter .city-link span").removeClass("active");
    $(".index-catalog-block .filter .city-link span.c6").addClass("active");
    $(".popup .window .select-block .m-list").removeClass("active");
    $(".popup .window .select-block .m-list.l6").addClass("active");
    $(".mobile-filter-block .city span").removeClass("active");
    $(".mobile-filter-block .city span.c6").addClass("active");
    $(".mobile-filter-block .list .lst").removeClass("active");
    $(".mobile-filter-block .list .lst.l6").addClass("active");
  });
  $(".popup .window .select-block .list label.l7").click(function () {
    $(".header .city span").removeClass("active");
    $(".header .city span.c7").addClass("active");
    $(".index-catalog-block .filter .city-link span").removeClass("active");
    $(".index-catalog-block .filter .city-link span.c7").addClass("active");
    $(".popup .window .select-block .m-list").removeClass("active");
    $(".popup .window .select-block .m-list.l7").addClass("active");
    $(".mobile-filter-block .city span").removeClass("active");
    $(".mobile-filter-block .city span.c7").addClass("active");
    $(".mobile-filter-block .list .lst").removeClass("active");
    $(".mobile-filter-block .list .lst.l7").addClass("active");
  });
  $(".popup .window .select-block .list label.l8").click(function () {
    $(".header .city span").removeClass("active");
    $(".header .city span.c8").addClass("active");
    $(".index-catalog-block .filter .city-link span").removeClass("active");
    $(".index-catalog-block .filter .city-link span.c8").addClass("active");
    $(".popup .window .select-block .m-list").removeClass("active");
    $(".popup .window .select-block .m-list.l8").addClass("active");
    $(".mobile-filter-block .city span").removeClass("active");
    $(".mobile-filter-block .city span.c8").addClass("active");
    $(".mobile-filter-block .list .lst").removeClass("active");
    $(".mobile-filter-block .list .lst.l8").addClass("active");
  });
  $("#btn1").on("click", function () {
    $(".form1 .inp").each(function () {
      if ($(this).val() != "") {
        console.log(32);
        $(this).removeClass("error");
      } else {
        console.log(33);
        $(this).addClass("error");
      }
    });
  });
  $("#btn2").on("click", function () {
    $(".form2 .inp").each(function () {
      if ($(this).val() != "") {
        console.log(32);
        $(this).removeClass("error");
      } else {
        console.log(33);
        $(this).addClass("error");
      }
    });
  });
  $("#btn3").on("click", function () {
    $(".form3 .inp").each(function () {
      if ($(this).val() != "") {
        console.log(32);
        $(this).removeClass("error");
      } else {
        console.log(33);
        $(this).addClass("error");
      }
    });
  });
  $("#btn4").on("click", function () {
    $(".form4 .inp").each(function () {
      if ($(this).val() != "") {
        console.log(32);
        $(this).removeClass("error");
      } else {
        console.log(33);
        $(this).addClass("error");
      }
    });
  });
  $("#btn5").on("click", function () {
    $(".form5 .inp").each(function () {
      if ($(this).val() != "") {
        console.log(32);
        $(this).removeClass("error");
      } else {
        console.log(33);
        $(this).addClass("error");
      }
    });
  });
  $("#btn6").on("click", function () {
    $(".form6 .inp").each(function () {
      if ($(this).val() != "") {
        console.log(32);
        $(this).removeClass("error");
      } else {
        console.log(33);
        $(this).addClass("error");
      }
    });
  });
  $("#category").change(function (e) {
    e.preventDefault();
    $(".index-catalog-block .filter .jq-selectbox.subcat").removeClass(
      "active"
    );
    $(this.value).addClass("active");
  });
  $(".popup .bg").click(function () {
    $(".popup").fadeOut();
    $("body").removeClass("hid");
    $(".popup .window .reg .step").removeClass("active");
    $(".popup .window .reg .step.s1").addClass("active");
  });
  $(
    ".mobile-menu .close, .mobile-menu ul li a.login, .mobile-menu ul li a.reg, .mobile-menu ul li a.feedback"
  ).click(function () {
    $(".mobile-menu").removeClass("opened");
  });
  $("a.feedback-cabinet").click(function () {
    $(".popup").fadeOut();
    $(".popup.feedback-cabinet").fadeIn();
    $("body").addClass("hid");
  });
  $(
    ".popup .window .reg .bottom-links a.feedback, .footer ul li a.feedback, .mobile-menu ul li a.feedback"
  ).click(function () {
    $(".popup").fadeOut();
    $(".popup.feedback").fadeIn();
    $("body").addClass("hid");
  });
  $(".popup .window .reg .bottom-links a.pass").click(function () {
    $(".popup").fadeOut();
    $(".popup.pass").fadeIn();
    $("body").addClass("hid");
  });
  $(
    ".popup .window .reg .bottom-links a.reg, .footer ul li a.reg, .mobile-menu ul li a.reg"
  ).click(function () {
    $(".popup").fadeOut();
    $(".popup.reg").fadeIn();
    $("body").addClass("hid");
  });
  $(
    ".header .user-links a.login, .footer ul li a.login, .mobile-menu ul li a.login"
  ).click(function () {
    $(".popup.login").fadeIn();
    $("body").addClass("hid");
  });
  $(".header .user-links a.reg").click(function () {
    $(".popup.reg").fadeIn();
    $("body").addClass("hid");
  });
  $(
    '#cabinet-price td:nth-child(3) input[type="text"], input#popup-price'
  ).each(function () {
    digits_int(this);
  });

  $("#cabinet-price").on(
    "input",
    'td:nth-child(3) input[type="text"]',
    function () {
      digits_int(this);
    }
  );
  $("input#popup-price").on("input", function () {
    digits_int(this);
  });
  $("body").on("click", "#show3", function () {
    if ($("#pass3").attr("type") == "password") {
      $(this).addClass("view");
      $("#pass3").attr("type", "text");
    } else {
      $(this).removeClass("view");
      $("#pass3").attr("type", "password");
    }
    return false;
  });
  $("body").on("click", "#show2", function () {
    if ($("#pass2").attr("type") == "password") {
      $(this).addClass("view");
      $("#pass2").attr("type", "text");
    } else {
      $(this).removeClass("view");
      $("#pass2").attr("type", "password");
    }
    return false;
  });
  $("body").on("click", "#show", function () {
    if ($("#pass1").attr("type") == "password") {
      $(this).addClass("view");
      $("#pass1").attr("type", "text");
    } else {
      $(this).removeClass("view");
      $("#pass1").attr("type", "password");
    }
    return false;
  });
  $(".popup .window .reg .step.s3 .select label").click(function () {
    $(".popup .window .reg .step.s3").removeClass("active");
    $(".popup .window .reg .step.s5").addClass("active");
  });
  $(".popup .window .reg .step.s2 .select label").click(function () {
    $(".popup .window .reg .step.s2").removeClass("active");
    $(".popup .window .reg .step.s4").addClass("active");
  });
  $(".popup .window .reg .select label.l1").click(function () {
    $(".popup .window .reg .step.s1").removeClass("active");
    $(".popup .window .reg .step.s2").addClass("active");
  });
  $(".popup .window .reg .select label.l2").click(function () {
    $(".popup .window .reg .step.s1").removeClass("active");
    $(".popup .window .reg .step.s3").addClass("active");
  });
  $(".header .menu1 li a.site-menu").click(function () {
    $(".popup.menu").fadeIn();
    $("body").addClass("hid");
  });
  $(".index-catalog-block .filter .metro-link").click(function () {
    $(".popup.metro").fadeIn();
    $("body").addClass("hid");
  });
  $(
    ".mobile-filter-block .city, .index-catalog-block .filter .city-link, .header .city"
  ).click(function () {
    $(".popup.cities").fadeIn();
    $("body").addClass("hid");
  });
  $(".popup .window .close").click(function () {
    $(".popup").fadeOut();
    $("body").removeClass("hid");
  });
  $(".sel").styler();
  $(".docs-block .list").slick({
    arrows: false,
    speed: 300,
    slidesToShow: 5,
    slidesToScroll: 1,
    responsive: [
      {
        breakpoint: 1200,
        settings: {
          slidesToShow: 4,
        },
      },
      {
        breakpoint: 992,
        settings: {
          slidesToShow: 3,
        },
      },
      {
        breakpoint: 768,
        settings: "unslick",
      },
    ],
  });

  //скролл по клику до элемента
  $("body").on("click", '[href*="#"]', function (e) {
    var fixed_offset = 30;
    if (window.innerWidth <= 575) {
      fixed_offset = 80;
    }
    $("html,body")
      .stop()
      .animate({ scrollTop: $(this.hash).offset().top - fixed_offset }, 1000);
    e.preventDefault();
  });
  $(".pro-photo__wrapper").on("click", ".card__buy-btn", function (e) {
    const phoneInputs = document.querySelectorAll('.phone-input');

    const maskOptions = {
      mask: '(000) 000-00-00'
    };

    phoneInputs.forEach(function (input) {
      IMask(input, maskOptions);
    });
    let card = $(this).closest(".pro-photo__card");
    let price = $(card)
      .find(".card__price-block .card__price:not(.old) .price")
      .first()
      .text()
      .trim();
    price = price.replace(/[^0-9]/g, "") + " ₽";

    let proPhotoId = $(card).find(".card__title").data("id");
    $("#photo-pro-photo-id").val(proPhotoId);

    $(".buy-pro-photo .photo-type .title").text(
      $(card).children(".card__title").children(".title").text()
    );
    $(".buy-pro-photo .photo-type ul li.amount").text(
      $(card)
        .children(".card__body")
        .children(".card__descr")
        .children("li:last-child")
        .text()
    );
    $(".buy-pro-photo .photo-type .photo-time").text(
      $(card)
        .children(".card__body")
        .children(".card__descr")
        .children("li:first-child")
        .text()
    );
    var listHtml = $(card).closest('.pro-photo__card-block')
      .find('.popup-description ul')
      .html() || '';

    // Подставляем в попап, вместе с <li class="amount"> для кол-ва
    $(".buy-pro-photo .photo-type ul").html(listHtml + '<li class="amount"></li>');
    $(".buy-pro-photo .photo-price").text(price);
    $(".popup.buy-pro-photo #buy-photo-popup .photo-type").show();
    $(".more-photo-employee .delete-emp").show();
    $(".more-photo-employee").hide().removeClass("added");
    $(".popup.buy-pro-photo #buy-photo-popup .add-photo-emp").show();
    $(".popup.buy-pro-photo #buy-photo-popup .add-company-photo").hide();
    $(".popup.buy-pro-photo #buy-photo-popup .photo-select__wrapper").hide();
    $(".buy-pro-photo").fadeIn(200);
    $("body").addClass("hid");
  });
  $(".employee__bottom-btn").on("click", function (e) {
    $(".popup.buy-pro-photo #buy-photo-popup .add-photo-emp").click();
    $(".more-photo-employee .delete-emp").hide();
    $(".popup.buy-pro-photo #buy-photo-popup .photo-type").hide();
    $(".popup.buy-pro-photo #buy-photo-popup .add-company-photo").show();
    $(".popup.buy-pro-photo #buy-photo-popup .photo-select__wrapper").hide();
    $(".popup.buy-pro-photo").fadeIn(200);
    $("body").addClass("hid");
    const phoneInputs = document.querySelectorAll('.phone-input');

    const maskOptions = {
      mask: '(000) 000-00-00'
    };

    phoneInputs.forEach(function (input) {
      IMask(input, maskOptions);
    });
  });

  $("body").on("click", ".pro-photo__mobile-btn", function (e) {
    let card = $(".pro-photo__wrapper .pro-photo__card:nth-child(2)");
    let price = $(card)
      .find(".card__body .card__price-block .card__price:not(.old) .price")
      .first()
      .text()
      .trim();
    price = price.replace(/[^0-9]/g, "") + " ₽";
    $(".buy-pro-photo .photo-type .title").text(
      $(card).children(".card__title").children(".title").text()
    );
    $(".buy-pro-photo .photo-type ul li.amount").text(
      $(card)
        .children(".card__body")
        .children(".card__descr")
        .children("li:last-child")
        .text()
    );
    $(".buy-pro-photo .photo-type .photo-time").text(
      $(card)
        .children(".card__body")
        .children(".card__descr")
        .children("li:first-child")
        .text()
    );
    $(".buy-pro-photo .photo-price").text(price);
    $(".popup.buy-pro-photo #buy-photo-popup .photo-type").show();
    $(".more-photo-employee .delete-emp").show();
    $(".more-photo-employee").hide().removeClass("added");
    $(".popup.buy-pro-photo #buy-photo-popup .add-photo-emp").show();
    $(".popup.buy-pro-photo #buy-photo-popup .add-company-photo").hide();
    $(".popup.buy-pro-photo #buy-photo-popup .photo-select__wrapper").hide();
    $(".buy-pro-photo").fadeIn(200);
    $("body").addClass("hid");
  });
  $("body").on("click", ".add-photo-emp", function (e) {
    $(this).hide();
    $(".more-photo-employee").addClass("added").slideDown(200);
  });
  $("body").on("click", ".add-company-photo", function (e) {
    $(this).hide();
    $(".photo-select__wrapper").slideDown({
      start: function () {
        $(this).css({
          display: "flex",
        });
      },
      duration: 200,
    });
  });
  $(".more-photo-employee").on("click", ".delete-emp", function (e) {
    $(".more-photo-employee").removeClass("added").slideUp(200);
    setTimeout(function () {
      $(".add-photo-emp").fadeIn(100);
    }, 200);
    $(".more-photo-employee input.quantity").val("");
  });
  $(".photo-select__wrapper").on("click", ".delete-photo-select", function (e) {
    $(".photo-select__wrapper").slideUp(200);
    setTimeout(function () {
      $(".add-company-photo").fadeIn(100);
    }, 200);
  });

  $("#cabinet-contacts .day-item .jq-selectbox__dropdown ul li").on(
    "click",
    function (e) {
      $("#shedule_submit").prop("disabled", false).text("Сохранить информацию");
      $("#break_submit").prop("disabled", false).text("Подтвердить");
    }
  );
  $("#cabinet-contacts .day-item-break .jq-selectbox__dropdown ul li").on(
    "click",
    function (e) {
      $("#shedule_submit").prop("disabled", true).text("Сохранить информацию");
      $("#break_submit").prop("disabled", false).text("Подтвердить");
    }
  );
  $("#break_submit").on("click", function (e) {
    e.preventDefault();

    const $btn = $("#break_submit");

    $btn.prop("disabled", true).text("Подтверждение");

    setTimeout(function () {
      $btn.text("Подтверждено");
    }, 1000);

    setTimeout(function () {
      $(".break__popup").fadeOut(200);
      $(".hid").css("overflow", "auto");
      $("#shedule_submit").prop("disabled", false).text("Сохранить информацию");
    }, 1500);
  });
  if (window.innerWidth < 575) {
    $(
      '#cabinet-price tbody input[type="text"], #cabinet-docs tbody input[type="text"], #cabinet-employee tbody input[type="text"]'
    ).prop("readonly", true);
    $(
      '#cabinet-docs tbody input[type="file"], #cabinet-docs tr.mob__empty, #cabinet-employee tbody input[type="file"], #cabinet-employee tr.mob__empty'
    ).remove();
    $("#cabinet-docs tbody .doc, #cabinet-employee tbody .doc").addClass(
      "loaded"
    );
    $(
      '#cabinet-docs input[name="is_mobile"], #cabinet-employee input[name="is_mobile"]'
    ).val("true");
  }
  $(".change-pass-link").on("click", function (e) {
    $(".security-block1").slideUp(200);
    $(".heading__mobile").fadeOut(200);
    setTimeout(function () {
      $("#change-password").slideDown(200);
      if (window.innerWidth < 991) {
        $(".heading__mobile.back").fadeIn({
          duration: 200,
          start: function () {
            $(this).css("display", "flex");
          },
        });
      }
    }, 200);
  });
});

$(".support-block ul .open-popup").on("click", function (e) {
  $("#support-popup textarea").val("").removeClass("error");
  $("#support-popup .invalid-feedback").remove();
  //let type = $(this).attr('data-id');
  //$('#support-popup input#support-type').val(type);
  $("#support-popup .support-type")
    .css("background-image", $(this).css("background-image"))
    .text($(this).text());

  $('.support-write button[type="submit"]').removeClass("open-idea");
  // if(type == 3) {
  if ($(this).hasClass("idea")) {
    $(".popup.support-write-header").fadeIn(200);
  } else {
    $(".popup.support-write").fadeIn(200);
  }
  $("body").addClass("hid");
});

$(".mob-menu__nav .idea-suggestion").on("click", function (e) {
  $(".popup.support-write-header").fadeIn(200);
  $("body").addClass("hid");
});

$("#buy-photo-popup").on("click", 'button[type="submit"]', function (e) {
  e.preventDefault();

  // Сброс ошибок
  let form = $(this).closest("form").attr("id");
  $(`#${form} input[type="text"]`).removeClass("error");
  $(`#${form} .invalid-feedback`).remove();

  // Проверка телефона
  if ($(`#${form} input[name="phone"]`).val() == "") {
    $(`#${form} input[name="phone"]`).addClass("error");
    $(`#${form} input[name="phone"]`).after(
      `<div class="invalid-feedback">Введите номер телефона.</div>`
    );
    return;
  }

  // Проверка количества сотрудников, если активна секция
  if (
    $(".more-photo-employee").hasClass("added") &&
    $(`#${form} input[name="emp_quantity"]`).val() == ""
  ) {
    $(`#${form} input[name="emp_quantity"]`).addClass("error");
    $(`#${form} input[name="emp_quantity"]`).after(
      `<div class="invalid-feedback">Введите кол-во сотрудников.</div>`
    );
    return;
  }

  let specialistId = $("#photo-specialist-id").val();
  let proPhotoId = $("#photo-pro-photo-id").val();
  let photoTitle = $(".pro-photo-title").text().trim();
  // Если proPhotoId пустое, берем значение из select
  if (!proPhotoId) {
    proPhotoId = $("#company-photo-select").val();
  }
  let price = $(".buy-pro-photo .photo-price").text().trim();

  if (!price) {
    price = $(".buy-pro-photo .photo-price-emp").text().trim();
  }

  let formData = new FormData();
  formData.append("specialist_id", specialistId);
  formData.append("pro_photo_id", proPhotoId || "4");
  formData.append("pro_photo_name", photoTitle);
  formData.append("price", price);
  formData.append("phone", $(`#${form} input[name="phone"]`).val());
  if ($(".more-photo-employee").hasClass("added")) {
    formData.append(
      "emp_quantity",
      $(`#${form} input[name="emp_quantity"]`).val()
    );
  }

  $.ajax({
    type: "POST",
    url: "",
    data: formData,
    processData: false,
    contentType: false,
    dataType: "text",
    success: function (response) {
      try {
        if (response.startsWith("Array")) {
          response = response.slice(5); // Убираем "Array" с json ответа чтобы правильно прасить
        }

        let json = JSON.parse(response);
        if (json.success) {
          $(".buy-pro-photo").fadeOut(200);
          setTimeout(function () {
            $(".photo-end").fadeIn(200);
          }, 200);
        } else {
          $(`#${form} button[type="submit"]`).after(
            `<div class="invalid-feedback">${json.message || "Что-то пошло не так. Повторите попытку позже."
            }</div>`
          );
        }
      } catch (e) {
        console.error("Error parsing JSON:", e);
        $(`#${form} button[type="submit"]`).after(
          `<div class="invalid-feedback">Ошибка сервера. Повторите попытку позже.</div>`
        );
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      console.error("Server Error:", jqXHR.responseText);
      $(`#${form} button[type="submit"]`).after(
        `<div class="invalid-feedback">Ошибка сервера. Повторите попытку позже.</div>`
      );
    },
  });
});

$("#photo_file_obzor").change(function (e) {
  e.preventDefault();
  var fileName = $(this).val().split("\\").pop(); // Получаем имя файла
  $(".photo__rules-block").text("Выбран файл: " + fileName);

  if (window.FormData === undefined) {
    alert("В вашем браузере FormData не поддерживается");
  } else {
    $(".photo__rules-block").text("");
    $(
      ".photo__item .invalid-feedback, #change-photo-mobile .popup__body .photo__wrapper .invalid-feedback"
    ).remove();
    $(
      ".photo__item .valid-feedback, #change-photo-mobile .popup__body .photo__wrapper .valid-feedback"
    ).remove();

    var formData = new FormData();
    formData.append("photo_file_obzor", $("#photo_file_obzor")[0].files[0]);
    $.ajax({
      type: "POST",
      url: "/actions/cabinet/upload_image.php",
      cache: false,
      contentType: false,
      processData: false,
      data: formData,
      dataType: "json",
      success: function (json) {
        if (json["error"]) {
          $(
            ".photo__item, #change-photo-mobile .popup__body .photo__wrapper"
          ).append(`<div class="invalid-feedback">${json["error"]}</div>`);
          location.reload();
        }

        if (json["success"]) {
          $(".photo__rules-block").remove();
          $(
            ".photo__item, #change-photo-mobile .popup__body .photo__wrapper"
          ).append(`<div class="valid-feedback">Фото успешно сохранено.</div>`);
          $(
            ".cabinet-page .cabinet-content .main-info-block .block1 .item .photo__btn"
          )
            .html("<i></i>Изменить")
            .addClass("refresh");
          $("#change-photo-mobile .popup__body .photo__btn").html(
            '<img src="/assets/img/cabinet/refresh-file-mob.svg">Изменить'
          );
          $(".photo__image")
            .css(
              "background-image",
              "url(/uploads/avatars/" + json["success"] + ")"
            )
            .addClass("refresh");
          if (
            $(".index-mob__info-block .photo-block.profile-photo-mmenu .photo")
              .length
          ) {
            $(
              ".index-mob__info-block .photo-block.profile-photo-mmenu .photo"
            ).css(
              "background-image",
              "url(/uploads/avatars/" + json["success"] + ")"
            );
          }
          location.reload();
        }
      },
    });
  }
});

$("#photo_file").change(function () {
  const file = this.files[0];

  if (!file || !file.type.startsWith("image/")) {
    $(".photo__rules-block").text("Выбранный файл не является изображением");
    return;
  }
  // Отображение имени файла
  const fileName = file.name;

  const reader = new FileReader();
  reader.onload = function (e) {
    $(".photo__image")
      .css("background-image", `url('${e.target.result}')`)
      .addClass("refresh");
    $(".profile-photo-mmenu .photo").removeClass("default");
  };
  reader.readAsDataURL(file);
});



function checkForbiddenWords(inputsSelector) {
  let forbiddenWords = [];
  const foundWords = new Set();

  // Получаем список запрещённых слов из скрытого поля
  const jsonStr = $("#popup-forbidden-words").val();
  if (jsonStr) {
    try {
      forbiddenWords = JSON.parse(jsonStr).map(w => w.toLowerCase());
    } catch (err) {
      console.error("Ошибка парсинга запрещённых слов", err);
      return { hasForbidden: false, words: [] };
    }
  }

  // Поля, в которых проверка ссылок не выполняется
  const skipLinkCheck = ['tg', 'site', 'vk', 'dzen'];

  $(inputsSelector).each(function () {
    let $field = $(this);
    let workingText = $field.val() || "";
    let fieldName = $field.attr('name') || "";

    // Поиск HTML-тегов во всех полях
    if (workingText.match(/<[^>]+>/g)) {
      foundWords.add("HTML-вставка");
    }

    // Если поле НЕ входит в список skipLinkCheck, ищем “отдельные” ссылки
    if (!skipLinkCheck.includes(fieldName)) {
      const standaloneUrlRegex = /(?:^|\s)(https?:\/\/[^\s]+)(?=$|\s)/g;
      let m;
      while ((m = standaloneUrlRegex.exec(workingText)) !== null) {
        foundWords.add(`ссылка – ${m[1]}`);
      }

      // Удаляем все URL из текста (во всех полях, кроме тех, где link-проверка отключена)
      workingText = workingText.replace(/https?:\/\/[^\s]+/g, "");
    }

    // Во всех полях удаляем HTML-теги, чтобы не мешать проверке слов
    workingText = workingText.replace(/<[^>]+>/g, "");

    // Проверка на запрещённые слова (кириллица, латиница, цифры, подчёркивание)
    const wordsInText = workingText.toLowerCase().match(/[a-zA-Zа-яА-ЯёЁ0-9_]+/g) || [];
    wordsInText.forEach(word => {
      if (forbiddenWords.includes(word)) {
        foundWords.add(word);
      }
    });
  });

  return {
    hasForbidden: foundWords.size > 0,
    words: Array.from(foundWords)
  };
}

function showForbiddenPopup(wordsArray) {
  const wordList = wordsArray.map(w => `"${w}"`).join(", ");
  $("#forbidden-words-list").text(wordList);
  $("#forbidden-popup").fadeIn();
}

$("#main-info").on("click", 'button[type="submit"]', function (e) {
  e.preventDefault();
  const btn = $(this);

  // Если кнопка находится внутри #change-photo-mobile → грузим только фото и выходим
  if (btn.closest("#change-photo-mobile").length) {
    uploadOnlyPhoto(btn);
    return;
  }

  // === СТАНДАРТНОЕ ПОВЕДЕНИЕ ДЛЯ #main-info ===
  $("input, textarea").removeClass("error");
  $(".item:not(.photo__item) .invalid-feedback").remove();

  btn.prop("disabled", true).text("Сохранение");
  let form = "#main-info";

  const { hasForbidden, words } = checkForbiddenWords(`${form} input[type="text"], ${form} textarea`);
  if (hasForbidden) {
    showForbiddenPopup(words);
    btn.prop("disabled", true).text("Сохранение");
    return false;
  }

  function sendMainInfo() {
    $.ajax({
      type: "POST",
      url: "/actions/cabinet/update_main_info.php",
      dataType: "json",
      data: $(`${form} input[type=text]:not(.quantity.default), ${form} input[type=hidden], ${form} textarea, ${form} input[type=radio]:checked`),
      success: function (json) {
        if (json["error"]) {
          btn.text("Сохранить информацию");
          if (json["error"]["fatal_error"]) {
            $(`${form}`).prepend(`<div class="invalid-feedback">${json["error"]["fatal_error"]}</div>`);
          }

          $.each(json["error"], function (key, value) {
            if (key == "description") {
              let textarea = $(`${form} textarea[name=${key}]`);
              textarea.addClass("error");
              if (textarea.siblings(".invalid-feedback").length) {
                textarea.siblings(".invalid-feedback").html(value);
              } else {
                textarea.after(`<div class="invalid-feedback">${value}</div>`);
              }
              return true;
            }
            $(`${form} input[name=${key}]`).after(`<div class="invalid-feedback">${value}</div>`);
          });
        }

        if (json["success"] && window.photoUploaded !== false) {
          btn.text("Сохранено");
        } else {
          btn.prop("disabled", true).text("Сохранить информацию");
        }
      },
      error: function () {
        btn.prop("disabled", true).text("Сохранить информацию");
      },
    });
  }

  // Если выбран файл — сначала грузим фото, потом форму
  if (window.FormData !== undefined && $("#photo_file")[0].files.length) {
    uploadPhoto(btn, sendMainInfo);
  } else {
    // Фото не выбрано — сразу шлём форму
    sendMainInfo();
  }
});


// === Вспомогательная функция для полной загрузки фото + формы ===
function uploadPhoto(btn, callback) {
  $(".photo__rules-block").text("");
  $(".photo__item .invalid-feedback, #change-photo-mobile .popup__body .photo__wrapper .invalid-feedback").remove();
  $(".photo__item .valid-feedback, #change-photo-mobile .popup__body .photo__wrapper .valid-feedback").remove();

  const formData = new FormData();
  formData.append("photo_file", $("#photo_file")[0].files[0]);

  $.ajax({
    type: "POST",
    url: "/actions/cabinet/upload_image.php",
    cache: false,
    contentType: false,
    processData: false,
    data: formData,
    dataType: "json",
    success: function (json) {
      if (json["error"]) {
        window.photoUploaded = false;
        $(".photo__item, #change-photo-mobile .popup__body .photo__wrapper").append(
          `<div class="invalid-feedback">${json["error"]}</div>`
        );
      } else if (json["success"]) {
        window.photoUploaded = true;
        handlePhotoSuccess(json["success"], btn);
      }
    },
    error: function () {
      window.photoUploaded = false;
    },
    complete: function () {
      // После загрузки фото (успешно или нет) — отправляем форму
      callback();
    },
  });
}


// === Только загрузка фото, без update_main_info.php ===
function uploadOnlyPhoto(btn) {
  btn.prop("disabled", true).text("Сохранение");
  $(".photo__item .invalid-feedback, #change-photo-mobile .popup__body .photo__wrapper .invalid-feedback").remove();
  $(".photo__item .valid-feedback, #change-photo-mobile .popup__body .photo__wrapper .valid-feedback").remove();

  const fileInput = $("#photo_file")[0];
  if (!fileInput.files.length) {
    $(".photo__wrapper").append(`<div class="invalid-feedback">Выберите фото для загрузки</div>`);
    return;
  }

  const formData = new FormData();
  formData.append("photo_file", fileInput.files[0]);

  $.ajax({
    type: "POST",
    url: "/actions/cabinet/upload_image.php",
    cache: false,
    contentType: false,
    processData: false,
    data: formData,
    dataType: "json",
    success: function (json) {
      if (json["error"]) {
        $(".photo__wrapper").append(`<div class="invalid-feedback">${json["error"]}</div>`);
      } else if (json["success"]) {
        handlePhotoSuccess(json["success"], btn);
      }
    },
  });
}


// === Общая логика успешной загрузки фото ===
function handlePhotoSuccess(filename, btn) {
  $(".photo__rules-block").remove();
  $(".photo__item, #change-photo-mobile .popup__body .photo__wrapper").append(
    `<div class="valid-feedback">Фото успешно сохранено.</div>`
  );
  $(".cabinet-page .cabinet-content .main-info-block .block1 .item .photo__btn")
    .html("<i></i>Изменить")
    .addClass("refresh");
  $("#change-photo-mobile .popup__body .photo__btn").html("<i></i>Изменить");
  $(".photo__image").css("background-image", "url(/uploads/avatars/" + filename + ")").addClass("refresh");

  if ($(".index-mob__info-block .photo-block.profile-photo-mmenu .photo").length) {
    $(".index-mob__info-block .photo-block.profile-photo-mmenu .photo").css(
      "background-image",
      "url(/uploads/avatars/" + filename + ")"
    );
  }
  btn.prop("disabled", true).text("Сохранено");
}


$("#main-info").on(
  "change keyup input",
  "input[type=text], input[type=hidden], textarea, input[type=radio]:checked, input[type=file]",
  function (e) {
    $('#main-info button[type="submit"]')
      .prop("disabled", false)
      .text("Сохранить информацию");
  }
);

$("#main-info").on("click", ".add-university", function () {
  let key =
    +$(".university-inputs input[type=text]").last().attr("data-key") + 1;

  $(".university-inputs").append(
    `<input type="text" name="university[${key}]" data-key="${key}" placeholder="Наименование учебного заведения (не более 70 символов)" value="" maxlength="70">
    <div class="invalid-feedback" style="display: none;">Превышено допустимое количество символов</div>`
  );
});

// City Autocomplete
$("input[name=city]")
  .on("keydown", function (event) {
    if (
      event.keyCode === $.ui.keyCode.TAB &&
      $(this).autocomplete("instance").menu.active
    ) {
      event.preventDefault();
    }
  })
  .autocomplete({
    source: function (request, response) {
      $.getJSON(
        "/actions/location/get_cities.php",
        {
          term: request.term,
        },
        response
      );
    },
    search: function () {
      let term = this.value;
      if (term.length < 2) return false;
    },
    select: function (event, ui) {
      let id = ui.item.id,
        name = ui.item.name;
      $("input[name=city_id]").val(id);
    },
  });

$("#cabinet-contacts").on("click", 'button[type="submit"]', function (e) {
  e.preventDefault();

  $(this).prop("disabled", true).text("Сохранение");
  $("input, textarea").removeClass("error");
  $(".invalid-feedback").remove();

  let popup = "#cabinet-contacts";

  const { hasForbidden, words } = checkForbiddenWords(`${popup} input[type="text"], ${popup} textarea`);

  if (hasForbidden) {
    showForbiddenPopup(words);
    $(this).prop("disabled", true).text("Сохранить");
    return false;
  }

  $.ajax({
    type: "POST",
    url: "/actions/cabinet/update_contacts.php",
    dataType: "json",
    data: $(
      `${popup} input[type=text], ${popup} input[type=hidden], ${popup} textarea, ${popup} input[type=radio]:checked, select, ${popup} input[type=checkbox]:checked`
    ),
    success: function (json) {
      if (json["error"]) {
        $('#cabinet-contacts button[type="submit"]').text("Сохранить информацию");
        if (json["error"]["fatal_error"]) {
          $(`${popup}`).prepend(
            `<div class="invalid-feedback">${json["error"]["fatal_error"]}</div>`
          );
        }

        $.each(json["error"], function (key, value) {
          $(`${popup} input[name=${key}]`).after(
            `<div class="invalid-feedback">${value}</div>`
          );
        });
      }

      if (json["success"]) {
        $('#cabinet-contacts button[type="submit"]').text("Сохранено");
        $(
          ".cabinet-page .cabinet-content .contacts-block .block .contacts ul"
        ).html(json["success"]);
        $(
          ".cabinet-popup#cabinet-contacts-card-mobile .popup__body .contacts-card ul"
        ).html(json["success"]);
      }
    },
  });
});

$(
  "#cabinet-contacts input[type=text], #cabinet-contacts input[type=hidden], #cabinet-contacts textarea, #cabinet-contacts input[type=radio]:checked, #cabinet-contacts input[type=checkbox]"
).on("change keyup input", function (e) {
  $("#shedule_submit").prop("disabled", false).text("Сохранить информацию");
});

// Metro Autocomplete
$("input[name=metro]")
  .on("keydown", function (event) {
    if (
      event.keyCode === $.ui.keyCode.TAB &&
      $(this).autocomplete("instance").menu.active
    ) {
      event.preventDefault();
    }
  })
  .autocomplete({
    source: function (request, response) {
      $.getJSON(
        "/actions/location/get_metro.php",
        {
          term: request.term,
        },
        response
      );
    },
    search: function () {
      let term = this.value;
      if (term.length < 2) return false;
    },
    select: function (event, ui) {
      let id = ui.item.id,
        name = ui.item.name;

      $("input[name=metro_id]").val(id);
    },
  });

// Update specialization
$("#cabinet-specialization, #cabinet-specialization-mobile").on(
  "click",
  'button[type="submit"]',
  function (e) {
    e.preventDefault();

    let form = "#" + $(this).parent().attr("id");
    $(this).prop("disabled", true).text("Сохранение");

    $(form + " .invalid-feedback").remove();

    $.ajax({
      type: "POST",
      url: "/actions/cabinet/update_specialization.php",
      dataType: "json",
      data: $(`${form} input[type=checkbox]:checked`),
      success: function (json) {
        if (json["success"]) {
          $(form + ' button[type="submit"]').text("Сохранено");
        }
        if (json["error"]) {
          $(this).text("Сохранить информацию");
          $("#cabinet-specialization").append(
            `<div class="invalid-feedback">${json["error"]}</div>`
          );
        }
      },
    });
  }
);
$(
  "#cabinet-specialization input[type=checkbox].child-checkbox, #cabinet-specialization-mobile input[type=checkbox].child-checkbox"
).change(function () {
  $(this).prop("checked", $(this).prop("checked"));
  if ($(this).prop("checked") === true) {
    $(this)
      .parent()
      .parent()
      .parent()
      .children("input[type=checkbox]:not(.child-checkbox)")
      .prop("checked", true);
  } else if (
    $(this).parent().siblings().children("input[type=checkbox]:checked")
      .length == 0
  ) {
    let parent_li = $(this).parent().parent().parent();
    parent_li
      .children("input[type=checkbox]:not(.child-checkbox)")
      .prop("checked", false);
    parent_li
      .children("p.has-children")
      .toggleClass("checked")
      .removeClass("opened");
    parent_li.children("ul").slideUp(200);
  }
  $('#cabinet-specialization button[type="submit"]')
    .prop("disabled", false)
    .text("Сохранить информацию");
});

$(
  "#cabinet-specialization input[type=checkbox]:not(.child-checkbox), #cabinet-specialization-mobile input[type=checkbox]:not(.child-checkbox)"
).change(function () {
  if ($(this).prop("checked") === false) {
    $(this)
      .siblings("ul")
      .children("li")
      .children("input[type=checkbox].child-checkbox")
      .prop("checked", false);
    $(this).siblings("ul.child-ul").slideUp(200);
  } else {
    $(this)
      .siblings("ul")
      .children("li")
      .children("input[type=checkbox].child-checkbox")
      .prop("checked", true);
    $(this).siblings("ul.child-ul").slideDown(200);
  }
  $(this).siblings("p.has-children").toggleClass("checked opened");
  $('#cabinet-specialization button[type="submit"]')
    .prop("disabled", false)
    .text("Сохранить информацию");
});

$("#cabinet-specialization-mobile input[type=checkbox]").change(function () {
  let selected = $(
    "#cabinet-specialization-mobile input[type=checkbox]:checked"
  ).length;
  $(".confirm-ways-mob").removeClass("disabled");
  if (selected > 0) {
    $(".confirm-ways-mob").text("Подтвердить (" + selected + ")");
  } else {
    $(".confirm-ways-mob").text("Подтвердить");
  }
});
$("#cabinet-specialization input[type=checkbox]").change(function () {
  const $checkbox = $(this);
  const $label = $checkbox.siblings("p.has-children");

  if ($checkbox.prop("checked")) {
    // Если чекбокс отмечен и есть блок с has-children — ставим opened, если его нет
    if ($label.length && !$label.hasClass("opened")) {
      $label.addClass("opened");
      $label.siblings("ul.child-ul").slideDown(200);
    }
  }

  let selected = $(
    "#cabinet-specialization input[type=checkbox]:checked"
  ).length;
  if (selected > 0) {
    $("#cabinet-specialization .link").text(
      "Выбор направлений (" + selected + ")"
    );
  } else {
    $("#cabinet-specialization .link").text("Выберите направления");
  }
});

$(
  ".cabinet-page .cabinet-content .ways-block .select > ul li p, .cabinet-page .cabinet-content .mob-ways-block .cabinet-popup .select > ul li p"
).on("click", function (e) {
  if ($(this).hasClass("checked") && $(this).hasClass("has-children")) {
    $(this).toggleClass("opened");
    $(this).siblings("ul.child-ul").slideToggle(200);
  }
});

$("#cabinet-price").on("click", ".add", function () {
  let key = +$("tbody .checkbox").last().attr("data-key") + 1;

  $("tbody").append(`
		<tr>
			<td>
				<div class="links"><a></a><a></a></div>
			</td>
			<td><input type="text" name="name[${key}]" placeholder="Наименование услуги (не более 40 символов)" maxlength="40"><div class="invalid-feedback" style="display:none;">Превышено допустимое количество символов</div></td>
			<td><input type="text" name="price[${key}]" placeholder="Цена"></td>
			<td><input type="checkbox" name="of[${key}]" class="checkbox" data-key="${key}" id="checkbox${key}"><label for="checkbox${key}"></label></td>
			<td><a class="edit-price-mobile"></a><a class="delete disabled"></a></td>
		</tr>
	`);
});

$("#cabinet-price").on("change keyup input", 'input[type="text"]', function () {
  $(this)
    .parent()
    .parent()
    .children("td:last-child")
    .children(".delete")
    .removeClass("disabled");
});

$("#cabinet-price").on(
  "change keyup input",
  'input[type="text"], input[type="checkbox"]',
  function () {
    $("#cabinet-price .bottom-button")
      .prop("disabled", false)
      .text("Сохранить информацию");
  }
);

function digits_int(target) {
  let val = $(target)
    .val()
    .replace(/[^0-9]/g, "");
  val = val.replace(/\B(?=(\d{3})+(?!\d))/g, "'");
  $(target).val(val);
}

$("#cabinet-price").on("click", ".delete", function () {
  $(this).closest("tr").next(".invalid-feedback").remove();
  $(this).closest("tr").remove();
  $("#cabinet-price .bottom-button")
    .prop("disabled", false)
    .text("Сохранить информацию");
});

$("#cabinet-price").on("click", ".bottom-button", function (e) {
  e.preventDefault();
  let button = $(this);
  button.prop("disabled", true).text("Сохранение");

  let form = "#cabinet-price";
  $(".invalid-feedback").remove();

  const { hasForbidden, words } = checkForbiddenWords(`${form} input[type="text"], ${form} textarea`);
  if (hasForbidden) {
    showForbiddenPopup(words);
    $(this).prop("disabled", false).text("Сохранить");
    return false;
  }

  // Подготовка данных для отправки
  let data = {};

  $(`${form} input[type=text], ${form} input[type=checkbox]:checked`).each(function () {
    let name = $(this).attr("name");
    let val = $(this).val();

    // Очищаем только числовые поля (price), но не меняем значение в самом инпуте
    if (name && name.indexOf("price") !== -1) {
      val = val.replace(/[^\d]/g, ""); // удаляем всё, кроме цифр
    }

    // Добавляем в объект данных
    data[name] = val;
  });

  // Отправка AJAX-запроса
  $.ajax({
    type: "POST",
    url: "/actions/cabinet/update_price.php",
    dataType: "json",
    data: data,
    success: function (json) {
      button.text("Сохранить информацию");

      if (json["error"]) {
        $.each(json["error"], function (key, value) {
          let parentTr = $(`${form} input[name="${key}"]`).closest("tr");

          let errorDiv = `<div class="invalid-feedback">${value}</div>`;

          $(`${form} input[name="${key}"]`).after(errorDiv);

          parentTr.find(".invalid-feedback").css({
            position: "absolute",
            marginTop: "5px",
            color: "#dc3545",
            fontSize: "0.875rem",
          });

          if (!parentTr.next(".empty-error").length) {
            let emptyTr = $('<tr class="empty-error"></tr>').css({
              height: "20px",
              background: "transparent",
            });

            parentTr.after(emptyTr);
          }
        });
      } else {
        // Убираем пустые строки, если ошибок нет
        $(".empty-error").remove();
      }

      if (json["success"]) {
        $('#cabinet-price button[type="submit"]')
          .prop("disabled", true)
          .text("Сохранено");
        $('#cabinet-price td:nth-child(3) input[type="text"]').each(function () {
          digits_int(this);
        });
      }
    },
  });
});

$("#cabinet-price").on("click", ".mob-add", function () {
  $("#popup-price, #popup-price-name").val("");
  $("#popup-price-from").prop("checked", false);
  $("#popup-price-id").val("");
  $("#cabinet-prices-popup").fadeIn(200);
  $(".mob-prices-btn").addClass("disabled").text("Подтвердить");
  $("body").addClass("hid");
});

$("#cabinet-price").on(
  "click",
  ".edit-price-mobile, input[type=text]",
  function () {
    if (window.innerWidth < 575) {
      let info = $(this)
        .parent()
        .parent()
        .children("td:first-child")
        .children(".links")
        .children("a");
      let name = info.attr("data-name");
      let price = info.attr("data-price");
      let from = info.attr("data-of") == "1" ? true : false;
      let id = info.attr("data-id");
      $("#popup-price-name").val(name);
      $("#popup-price").val(price);
      digits_int("#popup-price");
      $("#popup-price-from").prop("checked", from);
      $("#popup-price-from").prop("checked", from);
      $("#popup-price-id").val(id);
      $("#cabinet-prices-popup").fadeIn(200);
      $(".mob-prices-btn").addClass("disabled").text("Подтвердить");
      $("body").addClass("hid");
    }
  }
);

$("#cabinet-prices-popup input[type=text]").on("input", function () {
  $(".mob-prices-btn").removeClass("disabled").text("Подтвердить");
});
$("#cabinet-prices-popup .mob-prices-btn").on("click", function () {
  has_error = false;
  let name = $("#popup-price-name").val();
  let price = $("#popup-price").val();
  let from_bool = $("#popup-price-from").prop("checked");
  let from = from_bool ? "1" : "0";
  let from_checked = from_bool ? "checked" : "";
  let id = $("#popup-price-id").val();

  $(".invalid-feedback").text("").hide();
  $(".invalid-feedback").remove();
  if (name == "") {
    $("#popup-price-name").after(
      `<div class="invalid-feedback">Введите название услуги</div>`
    );
    has_error = true;
  }
  if (price == "") {
    $("#popup-price").after(
      `<span class="invalid-feedback small">Введите цену услуги</span>`
    );
    has_error = true;
  }

  let form = "#cabinet-prices-popup";

  // Очистка только числовых полей, таких как цена
  $(`${form} input[type=text]`).each(function () {
    let name = $(this).attr("name"); // Получаем имя поля
    // Если это не поле с названием, очищаем только цену
    if (name && name.indexOf("price") !== -1) {
      let cleanVal = $(this).val().replace(/[^\d]/g, ""); // удаление всех символов, кроме цифр
      $(this).val(cleanVal); // обновление значения поля
    }
  });
  const { hasForbidden, words } = checkForbiddenWords(`${form} input[type="text"], ${form} textarea`);

  if (hasForbidden) {
    has_error = true;
    showForbiddenPopup(words);
    $(this).prop("disabled", false).text("Сохранить");
    return false;
  }

  if (!has_error) {
    if (id == "") {
      let new_key = 0;
      while (
        $('#cabinet-price tr td input[name="price[' + new_key + ']"]').length !=
        0
      ) {
        new_key++;
      }
      let new_id = 0;
      while (
        $('#cabinet-price tr td:first-child .links a[data-id="' + new_id + '"]')
          .length != 0
      ) {
        new_id++;
      }
      $("#cabinet-price table tbody").append(`
			<tr>
				<td>
					<div class="links"><a data-id="${new_id}" data-name="${name}" data-price="${price}" data-of="${from}"></a><a></a></div>
				</td>
				<td><input type="text" name="name[${new_key}]" placeholder="Услуга" value="${name}" readonly></td>
				<td><input type="text" min="0" name="price[${new_key}]" placeholder="Цена" value="${price}" readonly></td>
				<td><input type="checkbox" name="of[${new_key}]" class="checkbox" data-key="${new_key}" id="checkbox${new_key}" ${from_checked}><label for="checkbox${new_key}"></label></td>
				<td><a class="edit-price-mobile"></a><a class="delete"></a></td>
			</tr>
		`);
    } else {
      let elem = $(
        '#cabinet-price tr td:first-child .links a[data-id="' + id + '"]'
      );
      elem.attr("data-name", name);
      elem.attr("data-price", price);
      elem.attr("data-of", from);
      let tr = elem.parent().parent().parent();
      console.log(tr.children('input[type="text"]'));
      tr.children("td:nth-child(2)").children('input[type="text"]').val(name);
      tr.children("td:nth-child(3)").children('input[type="text"]').val(price);
      tr.children()
        .children('input[type="checkbox"]')
        .prop("checked", from_bool);
    }

    $(".cabinet-page .cabinet-content .prices-block .bottom-button")
      .prop("disabled", false)
      .text("Сохранить информацию");
    $(".mob-prices-btn").addClass("disabled");
    $(".mob-prices-btn").text("Подтверждение");

    // Через 1 секунду меняем текст
    setTimeout(() => {
      $(".mob-prices-btn").text("Подтверждено");
    }, 1000);
    setTimeout(() => {
      $("#cabinet-prices-popup").fadeOut(200);
      $("body").removeClass("hid");
    }, 1500);
  }
});

$("#cabinet-docs").on("click", ".mob-add", function (e) {
  let key = 0;
  if ($("#cabinet-docs tbody tr").length !== 0) {
    key = +$(".docs tr td input[type=text]").last().attr("data-key") + 1;
  }
  $("#popup-docs-name").val("");
  $("#popup-doc-key").val(key);
  $("#popup-doc-file").val("0");
  $("#cabinet-docs-popup").append(`<input type="file" name="${key}" hidden>`);
  $(".docs-load a, .docs-load .doc").remove();
  $(".docs-load .popup-delete-doc")
    .removeClass("showed")
    .removeClass("editing");
  $(".docs-load").prepend(
    `<a class="upload" onclick="$('#cabinet-docs-popup input[name=${key}]').click()">Загрузить</a>`
  );
  $("#cabinet-docs-popup").fadeIn(200);
  $(".mob-docs-btn").addClass("disabled").text("Подтвердить");
  $("body").addClass("hid");
});

$("#cabinet-docs").on(
  "click",
  'tbody a:not(.delete), tbody input[type="text"]',
  function (e) {
    if (window.innerWidth < 575) {
      let key = $(this)
        .closest("tr")
        .children("td")
        .children('input[type="text"]')
        .attr("data-key");
      let name = $(this)
        .closest("tr")
        .children("td")
        .children('input[type="text"]')
        .val();
      $(".docs-load").html(`<div class="doc">
				Загружен
			</div>
		<div class="popup-delete-doc showed editing"></div>`);
      $("#popup-docs-name").val(name);
      $("#popup-doc-key").val(key);
      $("#popup-doc-file").val("1");
      $("#cabinet-docs-popup").fadeIn(200);
      $(".mob-docs-btn").addClass("disabled").text("Подтвердить");
      $("body").addClass("hid");
    }
  }
);

$("#cabinet-docs-popup input[type=text]").on("input", function () {
  $(".mob-docs-btn").removeClass("disabled").text("Подтвердить");
});

$("#cabinet-docs-popup").on("click", ".popup__close", function (e) {
  let key = $("#popup-doc-key").val();
  $('#cabinet-docs-popup input[name="' + key + '"]').remove();
});

let uploadInProgress = false; // флаг загрузки

$("#cabinet-docs-popup").on("click", ".mob-docs-btn", async function (e) {
  e.preventDefault();

  if (uploadInProgress) {
    return;
  }

  $(".mob-docs-btn").addClass("disabled").text("Подтверждение");

  $("#cabinet-docs-popup .invalid-feedback").remove();
  let key = $("#popup-doc-key").val();
  let name = $("#popup-docs-name").val();
  let file = $("#popup-doc-file").val();
  let fileInput = $("#cabinet-docs-popup input[type=file]")[0];
  let hasError = false;

  const { hasForbidden, words } = checkForbiddenWords(`${form} input[type="text"], ${form} textarea`);

  if (name == "") {
    $("#popup-docs-name").after(`<div class="invalid-feedback">Введите наименование документа</div>`);
    hasError = true;
  }

  // Если есть выбранный файл — ждём загрузку
  if (file == "1" && fileInput && fileInput.files[0]) {
    uploadInProgress = true; // начинаем загрузку
    const result = await uploadUserDocument(fileInput.files[0]);
    uploadInProgress = false; // загрузка завершена

    if (!result.success) {
      hasError = true;
      $(".mob-docs-btn").addClass("disabled").text("Подтверждение");
    }
  } else if (file == "0") {
    $(".docs-load").append(`<div class="invalid-feedback">Загрузите файл</div>`);
    $(".mob-docs-btn").addClass("disabled").text("Подтверждение");
    hasError = true;
  }

  if (hasForbidden) {
    has_error = true;
    showForbiddenPopup(words);
    $(this).prop("disabled", false).text("Сохранить");
    return false;
  }

  if (!hasError) {
    $(".mob-docs-btn").removeClass("disabled").text("Подтверждено");
    // остальная логика
    if ($(`#cabinet-docs tr td input[name="name[${key}]"]`).length == 1) {
      $(`#cabinet-docs tr td input[name="name[${key}]"]`).val(name);
    } else {
      $("#cabinet-docs tbody").append(`
        <tr>
          <td><input type="text" name="name[${key}]" data-key="${key}" 
          placeholder="Наименование акции (не более 65 символов)" maxlength="65" value="${name}">
          <div class="invalid-feedback" style="display:none;">Превышено допустимое количество символов</div></td>
          <td><div class="doc loaded"></div></td>
          <td><a class="edit-docs-mobile"></a><a class="delete"></a></td>
        </tr>
      `);
    }

    $(".mob-docs-btn").addClass("disabled").text("Подтверждение");

    // Через 1 секунду меняем текст
    setTimeout(() => {
      $(".mob-docs-btn").text("Подтверждено");
    }, 1000);
    setTimeout(() => {
      $("#cabinet-docs-popup").fadeOut(200);
      $("body").removeClass("hid");
    }, 1500);
    $("body").removeClass("hid");
    $('#cabinet-docs button[type="submit"]').prop("disabled", false).text("Сохранить информацию");
  }
});

$("#cabinet-docs-popup").on("click", ".popup-delete-doc", function (e) {
  let key = $("#popup-doc-key").val();
  $("#cabinet-docs-popup .docs-load .invalid-feedback").remove();
  $("#cabinet-docs-popup #popup-doc-file").val("0");
  $(this).siblings(".doc, a").remove();
  if (
    $(this).hasClass("editing") &&
    $(`#cabinet-docs-popup input[name=${key}]`).length == 0
  ) {
    $("#cabinet-docs-popup").append(`<input type="file" name="${key}" hidden>`);
  } else {
    $(`#cabinet-docs-popup input[name=${key}]`).val("");
  }
  $(this).removeClass("showed").removeClass("editing");
  $(this)
    .closest(".docs-load")
    .prepend(
      `<a class="upload" onclick="$('#cabinet-docs-popup input[name=${key}]').click()">Загрузить</a>`
    );
});

$("#cabinet-docs-popup").on("change", "input[type=file]", function (e) {
  const fileInput = e.target;
  const file = fileInput.files[0];

  $(".mob-docs-btn").removeClass("disabled").text("Подтвердить");

  if (!file || !file.type.match("image")) return;

  const $docsLoad = $(".docs-load");

  // Ставим флаг, что файл есть
  $("#popup-doc-file").val("1");

  // Вставляем временный блок
  $docsLoad.prepend(`<div class="doc">Загружен</div>`);
  $(".popup-delete-doc").addClass("showed");
  $docsLoad.find("a").remove();

  // Загружаем файл через AJAX
  /* uploadUserDocument(file, fileInput); */
});

function uploadUserDocument(file) {
  const $popup = $("#cabinet-docs-popup");
  const $docsLoad = $popup.find(".docs-load");
  const formData = new FormData();
  formData.append("document_file", file);

  // блокируем кнопку на время загрузки
  $(".mob-docs-btn").prop("disabled", true).text("Подтверждение");

  return new Promise((resolve) => {
    $.ajax({
      type: "POST",
      url: "/actions/cabinet/upload_image_file.php",
      dataType: "json",
      data: formData,
      contentType: false,
      processData: false,
      success: function (json) {
        $(".mob-docs-btn").prop("disabled", false).text("Подтвердить");

        if (json.error) {
          const firstError = json.error[Object.keys(json.error)[0]];
          $docsLoad.append(`<div class="invalid-feedback">${firstError}</div>`);
          resolve({ success: false, error: firstError });
        } else if (json.success) {
          const firstSuccess = json.success[Object.keys(json.success)[0]];
          $("#popup-doc-filename").val(firstSuccess);
          $docsLoad.find(".doc").first().text("Загружен");
          resolve({ success: true, name: firstSuccess });
        } else {
          $docsLoad.append(`<div class="invalid-feedback">Неизвестная ошибка</div>`);
          resolve({ success: false, error: "Неизвестная ошибка" });
        }
      },
      error: function () {
        $(".mob-docs-btn").prop("disabled", false).text("Сохранить");
        $docsLoad.append(`<div class="invalid-feedback">Ошибка соединения</div>`);
        resolve({ success: false, error: "Ошибка соединения" });
      },
      complete: function () {
        uploadInProgress = false; // на всякий случай
      }
    });
  });
}

$("#cabinet-docs table").on("change", "input[type=file]", function (e) {
  const file = e.target.files[0];
  if (!file || !file.type.match("image")) return;

  const $input = $(this);
  const $td = $input.closest("td");
  const fileName = file.name;

  let $doc = $td.find("div.doc");

  if (!$doc.length) {
    // Если doc нет, создаём и перемещаем туда input
    $doc = $('<div class="doc"></div>');
    $input.appendTo($doc);
    $td.append($doc);
    // Удаляем старую ссылку "Загрузить", если была
    $td.find("a.upload").remove();
  }

  // Обновляем ссылку внутри doc
  $doc.html(''); // очищаем содержимое
  $doc.append(
    $input, // input должен остаться внутри
    `<a onclick="$(this).siblings('input[type=file]').click()">${fileName}</a>`
  );

  // Кнопка "Сохранить"
  $('#cabinet-docs button[type="submit"]')
    .prop("disabled", false)
    .text("Сохранить информацию");
});


$("#cabinet-docs").on(
  "input change",
  "input[type=text], input[type=file]",
  function (e) {
    $(this).parent().siblings("td").children(".delete").removeClass("disabled");
    $('#cabinet-docs button[type="submit"]')
      .prop("disabled", false)
      .text("Сохранить информацию");
  }
);

$("#cabinet-docs table").on("click", ".upload", function (e) {
  e.preventDefault();
  $(this).siblings("input[type=file]").click();
});

$("#cabinet-docs").on("click", ".add", function () {
  let key = +$("tbody input[type=text]").last().attr("data-key") + 1;

  $("tbody").append(`
		<tr>
			<td><input type="text" name="name[${key}]" data-key="${key}" placeholder="Наименование акции (не более 65 символов)" maxlength="65"><div class="invalid-feedback" style="display:none;">Превышено допустимое количество символов</div></td>
			<td>
				<a class="upload" onclick="$(this).siblings('input').click()">Загрузить</a>
				<input type="file" name="${key}" hidden>
			</td>
			<td><a class="edit-docs-mobile"></a><a class="delete disabled"></a></td>
		</tr>
	`);
});

$("#cabinet-docs").on("click", ".delete", function () {
  $(this).closest("tr").next(".invalid-feedback").remove();
  $(this).closest("tr").remove();
  $("#cabinet-docs .refresh button")
    .prop("disabled", false)
    .text("Сохранить информацию");
});

$("#cabinet-docs").on("click", 'button[type="submit"]', function (e) {
  e.preventDefault();
  $("input, textarea").removeClass("error");
  $(".invalid-feedback").remove();

  let formData = new FormData(),
    form = "#cabinet-docs",
    names = $(`${form} input[type=text]`),
    files = $(`${form} input[type=file]`);

  $.each(names, function () {
    formData.append($(this).attr("name"), $(this).val());
  });

  $.each(files, function () {
    formData.append($(this).attr("name"), $(this).prop("files")[0]);
  });
  formData.append($("#is_mobile").attr("name"), $("#is_mobile").val());

  $(this).prop("disabled", true).text("Сохранение");

  const { hasForbidden, words } = checkForbiddenWords(`${form} input[type="text"], ${form} textarea`);

  if (hasForbidden) {
    showForbiddenPopup(words);
    $(this).prop("disabled", false).text("Сохранить");
    return false;
  }

  $.ajax({
    type: "POST",
    url: "/actions/cabinet/update_files.php",
    dataType: "json",
    data: formData,
    contentType: false,
    processData: false,
    success: function (json) {
      if (json["error"]) {
        $('#cabinet-docs button[type="submit"]').text("Сохранить информацию");
        if (json["error"]["fatal_error"]) {
          $(`${form}`).prepend(
            `<div class="invalid-feedback">${json["error"]["fatal_error"]}</div>`
          );
        }
        $.each(json["error"], function (key, value) {
          $(`${form} input[data-key=${key}]`).after(
            `<div class="invalid-feedback">${value}</div>`
          );
        });
      }

      if (json["success"]) {
        $('#cabinet-docs button[type="submit"]').text("Сохранено");
      }
    },
  });
});
var files_count = 0;

$("#cabinet-employee table").on("change", "input[type=file]", function (e) {
  const file = e.target.files[0];
  if (!file || !file.type.match("image")) return;

  const $input = $(this);
  const $td = $input.closest("td");
  const fileName = file.name;
  const filePath = URL.createObjectURL(file);

  let $doc = $td.find("div.doc");

  // Если <div class="doc"> отсутствует — создаём
  if (!$doc.length) {
    $doc = $('<div class="doc"></div>');
    $input.appendTo($doc);
    $td.append($doc);
    // Удаляем ссылку "Загрузить", если была
    $td.find("a.upload").remove();
  }

  // Удаляем старые ссылки и добавляем новую
  $doc.find("a").remove();
  $doc.append(
    $input,
    `<a onclick="$(this).siblings('input[type=file]').click()">${fileName}</a>
  `);

  $('#cabinet-employee button[type="submit"]')
    .prop("disabled", false)
    .text("Сохранить информацию");
});

$("#cabinet-employee").on(
  "input change",
  "input[type=text], input[type=file]",
  function (e) {
    $(this).parent().siblings("td").children(".delete").removeClass("disabled");
    $('#cabinet-employee button[type="submit"]')
      .prop("disabled", false)
      .text("Сохранить информацию");
  }
);

$("#cabinet-employee").on("click", ".add", function () {
  let key = +$("tbody input[type=text]").last().attr("data-key") + 1;

  $("tbody").append(`
	<tr>
		<td>
			<div class="employee-info-mobile"><input type="text" readonly></div>
			<input type="text" name="name[${key}]" data-key="${key}" placeholder="Имя (не более 12 символов)" maxlength="12">
		</td>
		<td>
			<input type="text" name="lastname[${key}]" data-key="${key}" placeholder="Фамилия (не более 15 символов)" maxlength="15">
		</td>
		<td>
			<input type="text" name="post[${key}]" data-key="${key}" placeholder="Должность (не более 20 символов)" maxlength="20">
		</td>
		<td>
			<a class="upload" onclick="$(this).siblings('input').click()">Загрузить</a>
			<input type="file" name="${key}" data-key="${key}" hidden>
		</td>
		<td><a class="edit-employee-mobile"></a><a class="delete disabled"></a></td>
	</tr>
	<tr class="invalid-feedback-tr">
		<td colspan="5">
			<div class="invalid-feedback" style="display:none;">Превышено допустимое количество символов</div>
		</td>
	</tr>
	`);
});

$("#cabinet-employee").on("click", ".mob-add", function (e) {
  $("input").removeClass("error");
  /* $(".invalid-feedback").remove(); */
  let key = 0;
  if ($("#cabinet-employee tbody tr").length !== 0) {
    key =
      +$(".employee tr td:first-child > input[type=text]")
        .last()
        .attr("data-key") + 1;
  }
  $("#cabinet-employee-popup .popup-employee-photo").addClass("empty");
  $("#cabinet-employee-popup .employee-photo-btn").html(
    '<i><img src="/assets/img/cabinet/employee-upload.svg"></i>Загрузить'
  );
  $("#popup-employee-name").val("");
  $("#popup-employee-lastname").val("");
  $("#popup-employee-post").val("");
  $("#popup-emp-key").val(key);
  $("#popup-emp-file").val("0");
  $("#cabinet-employee-popup").append(
    `<input type="file" name="${key}" hidden>`
  );
  $("#cabinet-employee-popup").fadeIn(200);
  $(".mob-employee-btn").addClass("disabled").text("Подтвердить");
  $("body").addClass("hid");
});

$("#cabinet-employee").on(
  "click",
  ".employee-info-mobile, .edit-employee-mobile, .doc",
  function (e) {
    if (window.innerWidth < 575) {
      $("input").removeClass("error");

      let key = $(this)
        .closest("tr")
        .children("td:first-child")
        .children("input[type=text]")
        .attr("data-key");

      let name = $(this)
        .closest("tr")
        .children("td:first-child")
        .children("input[type=text]")
        .val();
      let lastname = $(this)
        .closest("tr")
        .children("td:nth-child(2)")
        .children("input[type=text]")
        .val();
      let post = $(this)
        .closest("tr")
        .children("td:nth-child(3)")
        .children("input[type=text]")
        .val();

      // 🔹 Проверяем, есть ли сохранённое изображение
      let img_name = $(this)
        .closest("tr")
        .find("td:nth-child(4) .doc a")
        .text().trim();

      if (img_name) {
        // если уже было фото
        let image_url = "/uploads/employee/" + img_name;
        $("#cabinet-employee-popup .popup-employee-photo")
          .removeClass("empty")
          .css("background-image", "url(" + image_url + ")");
        $("#popup-emp-file").val("1");
        $("#popup-emp-filename").val(img_name);
        $("#cabinet-employee-popup .employee-photo-btn").html(
          '<i><img src="/assets/img/cabinet/employee-change.svg"></i>Изменить'
        );
      } else {
        // если фото нет
        $("#cabinet-employee-popup .popup-employee-photo")
          .addClass("empty")
          .css("background-image", "");
        $("#popup-emp-file").val("0");
        $("#popup-emp-filename").val("");
        $("#cabinet-employee-popup .employee-photo-btn").html(
          '<i><img src="/assets/img/cabinet/employee-change.svg"></i>Загрузить'
        );
      }

      $("#popup-employee-name").val(name);
      $("#popup-employee-lastname").val(lastname);
      $("#popup-employee-post").val(post);
      $("#popup-emp-key").val(key);

      $(".mob-employee-btn").addClass("disabled").text("Подтвердить");
      $("#cabinet-employee-popup .invalid-feedback").remove();
      $("#cabinet-employee-popup").fadeIn(200);
      $("body").addClass("hid");
    }
  }
);

$("#cabinet-employee-popup").on("click", ".employee-photo-btn", function (e) {
  let key = $("#popup-emp-key").val();
  if ($(`#cabinet-employee-popup input[name=${key}]`).length === 0) {
    $("#cabinet-employee-popup").append(
      `<input type="file" name="${key}" hidden>`
    );
  }
  $(`#cabinet-employee-popup input[name=${key}]`).click();
});

$("#cabinet-employee-popup").on("click", ".popup__close", function (e) {
  let key = $("#popup-emp-key").val();
  $(`#cabinet-employee-popup input[name=${key}]`).remove();
  $("#popup-emp-filename").val("");
});

// Вызываем при изменении файла
$("#cabinet-employee-popup").on("change", "input[type=file]", async function (e) {
  const file = e.target.files[0];
  if (!file) return;

  const $popup = $("#cabinet-employee-popup");
  const $photo = $popup.find(".popup-employee-photo");
  const $btn = $popup.find(".employee-photo-btn");

  // Локальное превью сразу
  const reader = new FileReader();
  reader.onload = function (event) {
    $photo.removeClass("empty").css("background-image", `url(${event.target.result})`);
  };
  reader.readAsDataURL(file);

  // Ждём загрузку на сервер
  /* const result = await uploadEmployeePhoto(file); */

  // 🔓 Разблокируем кнопку
  $btn.prop("disabled", false).html('<i><img src="/assets/img/cabinet/employee-change.svg"></i>Изменить');

  /* if (result.success) {
    // Сохраняем имя файла для дальнейшего использования
    $("#popup-emp-file").val("1");
    $("#popup-emp-filename").val(result.fileName);
  } */
});

$("#cabinet-employee-popup").on("input", function () {
  const $mobBtn = $("#cabinet-employee-popup .mob-employee-btn");
  $mobBtn.removeClass("disabled").text("Подтвердить");
});

$("#cabinet-employee-popup").on("click", ".mob-employee-btn", async function () {
  $("#cabinet-employee-popup .invalid-feedback").remove();
  $("#cabinet-employee-popup input").removeClass("error");
  let has_error = false;
  let name = $("#popup-employee-name").val();
  let lastname = $("#popup-employee-lastname").val();
  let post = $("#popup-employee-post").val();
  let key = $("#popup-emp-key").val();
  let img_name = $("#popup-emp-filename").val();
  const $btn = $("#cabinet-employee-popup .employee-photo-btn");

  const fileInput = $("#cabinet-employee-popup input[type=file]")[0];
  const file = fileInput?.files[0];

  let form = "#cabinet-employee-popup";
  $(".invalid-feedback").remove();

  // Очистка только числовых полей, таких как цена
  $(`${form} input[type=text]`).each(function () {
    let name = $(this).attr("name"); // Получаем имя поля
    // Если это не поле с названием, очищаем только цену
    if (name && name.indexOf("price") !== -1) {
      let cleanVal = $(this).val().replace(/[^\d]/g, ""); // удаление всех символов, кроме цифр
      $(this).val(cleanVal); // обновление значения поля
    }
  });
  const { hasForbidden, words } = checkForbiddenWords(`${form} input[type="text"], ${form} textarea`);

  if (name == "") {
    $("#popup-employee-name").after(
      `<div class="invalid-feedback">Введите имя сотрудника</div>`
    );
    has_error = true;
  }
  if (lastname == "") {
    $("#popup-employee-lastname").after(
      `<div class="invalid-feedback">Введите фамилию сотрудника</div>`
    );
    has_error = true;
  }
  if (post == "") {
    $("#popup-employee-post").after(
      `<div class="invalid-feedback">Введите должность сотрудника</div>`
    );
    has_error = true;
  }

  // Если есть файл, проверяем его перед сохранением
  if (file) {
    const $mobBtn = $(this);
    $mobBtn.addClass("disabled").text("Подтверждение");

    const result = await uploadEmployeePhoto(file);

    $mobBtn.removeClass("disabled").text("Подтвердить");

    if (!result.success) {
      $mobBtn.addClass("disabled").text("Подтверждение"); // если ошибка — прерываем
      return;
    }
    // ✅ После успешной загрузки обновляем имя файла и флаг
    img_name = result.fileName;
    $("#popup-emp-filename").val(img_name);
    $("#popup-emp-file").val("1");
  } else if ($("#popup-emp-file").val() !== "1") {
    const $btn = $("#cabinet-employee-popup .employee-photo-btn");
    if ($btn.next(".invalid-feedback").length === 0) {
      $btn.after(`<div class="invalid-feedback">Загрузите изображение</div>`);
    }
  }

  if ($("#popup-emp-file").val() == "0") {
    // Берём текст уже существующей ошибки после кнопки
    const existingError = $btn.next(".invalid-feedback");

    // Если ошибка уже есть и она не про размер файла — тогда можно показать "Загрузите изображение"
    if (
      existingError.length === 0 ||
      !existingError.text().includes("Файл слишком большой")
    ) {
      // Удаляем старую (если вдруг была другая)
      existingError.remove();
      $btn.after(`<div class="invalid-feedback">Загрузите изображение</div>`);
    }

    has_error = true;
  }

  if (hasForbidden) {
    has_error = true;
    $("#cabinet-employee-popup").fadeOut(200);
    showForbiddenPopup(words);
    $(this).prop("disabled", false).text("Сохранить");
    return false;
  }

  if (!has_error) {
    if (
      $(`#cabinet-employee tbody tr td:first-child > input[data-key="${key}"]`)
        .length === 0
    ) {
      $("#cabinet-employee tbody").append(`
				<tr>
					<td><div class="employee-info-mobile"><input type="text" value="${name} ${lastname}, ${post}" readonly></div><input type="text" name="name[${key}]" data-key="{${key}}" placeholder="Имя" value="${name}"></td>
					<td><input type="text" name="lastname[${key}]" placeholder="Фамилия" value="${lastname}"></td>
					<td><input type="text" name="post[${key}]" placeholder="Должность" value="${post}"></td>
					<td>
						<input type="hidden" name="mobile_loaded[${key}]" value="${img_name}">
					</td>
					<td><a class="edit-employee-mobile"></a><a class="delete"></a></td>
				</tr>
			`);
    } else {
      $(
        `#cabinet-employee tbody tr td:first-child > input[data-key="${key}"]`
      ).val(name);
      $(
        `#cabinet-employee tbody tr td:first-child:has(input[data-key="${key}"]) .employee-info-mobile input`
      ).val(name + " " + lastname + ", " + post);
      $(`input[name="lastname[${key}]"]`).val(lastname);
      $(`input[name="post[${key}]"]`).val(post);
      $(`input[name="post[${key}]"]`)
        .closest("tr")
        .children("td:nth-child(4)")
        .append(
          `<input type="hidden" name="mobile_loaded[${key}]" value="${img_name}">`
        );
    }
    $(".mob-employee-btn").addClass("disabled").text("Подтверждено");
    $("#popup-emp-filename").val("");
    $("#cabinet-employee-popup").fadeOut(200);
    $("body").removeClass("hid");
  }
});

function uploadEmployeePhoto(file) {
  const $photo = $("#cabinet-employee-popup .popup-employee-photo");
  const $btn = $("#cabinet-employee-popup .employee-photo-btn");

  // Сбрасываем ошибки
  $(".employee-photo-btn + .invalid-feedback").remove();

  return new Promise((resolve) => {
    if (!file || !file.type.match("image")) {
      resolve({ success: false, error: "Файл не является изображением" });
      return;
    }

    // Локальное превью
    const reader = new FileReader();
    reader.onload = function (event) {
      $photo.removeClass("empty").css("background-image", `url(${event.target.result})`);
    };
    reader.readAsDataURL(file);

    // Отправка на сервер
    const formData = new FormData();
    formData.append("photo_file", file);

    $.ajax({
      type: "POST",
      url: "/actions/cabinet/upload_image_emp.php",
      dataType: "json",
      data: formData,
      contentType: false,
      processData: false,
      success: function (json) {
        if (json.error) {
          $btn.after(`<div class="invalid-feedback">${json.error}</div>`);
          resolve({ success: false, error: json.error });
        } else if (json.success) {
          $("#popup-emp-file").val("1");
          $("#popup-emp-filename").val(json.success);
          $btn.html('<i><img src="/assets/img/cabinet/employee-change.svg"></i>Изменить');
          resolve({ success: true, file: json.success });
        }
      },
    });
  });
}

$("#cabinet-employee").on("click", ".delete", function () {
  $(this).closest("tr").next(".invalid-feedback").remove();
  $(this).closest("tr").remove();
  $("#cabinet-employee .refresh button")
    .prop("disabled", false)
    .text("Сохранить информацию");
});

$("#cabinet-employee").on("click", 'button[type="submit"]', function (e) {
  e.preventDefault();
  $("input, textarea").removeClass("error");
  $(".invalid-feedback").remove();

  let formData = new FormData(),
    form = "#cabinet-employee",
    names = $(`${form} td:first-child input[type=text]`),
    lastnames = $(`${form} td:nth-child(2) input[type=text]`),
    posts = $(`${form} td:nth-child(3) input[type=text]`),
    files = $(`${form} input[type=file]`),
    filenames = $(`${form} td:nth-child(3) input[type=hidden]`),
    company_id = $(`${form} input[name="company_id"]`),
    is_mobile = $(`${form} input[name="is_mobile"]`);

  const { hasForbidden, words } = checkForbiddenWords(`${form} input[type="text"], ${form} textarea`);

  if (hasForbidden) {
    showForbiddenPopup(words);
    $(this).prop("disabled", false).text("Сохранить");
    return false;
  }

  $.each(names, function () {
    formData.append($(this).attr("name"), $(this).val());
  });

  $.each(lastnames, function () {
    formData.append($(this).attr("name"), $(this).val());
  });

  $.each(posts, function () {
    formData.append($(this).attr("name"), $(this).val());
  });
  $.each(files, function () {
    formData.append($(this).attr("name"), $(this).prop("files")[0]);
  });
  $.each(filenames, function () {
    formData.append($(this).attr("name"), $(this).val());
  });
  formData.append($(company_id).attr("name"), $(company_id).val());
  formData.append($(is_mobile).attr("name"), $(is_mobile).val());

  $(this).prop("disabled", true).text("Сохранение");

  $.ajax({
    type: "POST",
    url: "/actions/cabinet/update_employee.php",
    dataType: "json",
    data: formData,
    contentType: false,
    processData: false,
    success: function (json) {
      console.log(json);

      // Сначала удаляем старые ошибки
      $(`${form} .error-row`).remove();

      if (json["error"]) {
        $('#cabinet-employee button[type="submit"]').text("Сохранить информацию");

        // Фатальная ошибка — в начало формы
        if (json["error"]["fatal_error"]) {
          $(`${form}`).prepend(
            `<div class="invalid-feedback">${json["error"]["fatal_error"]}</div>`
          );
        }

        // Перебираем ошибки по строкам таблицы
        $.each(json["error"], function (key, value) {
          const tr = $(`${form} input[data-key=${key}]`).closest("tr");

          // Добавляем под tr отдельную строку с сообщением
          tr.after(`
            <tr class="error-row">
              <td colspan="5">
                <div class="invalid-feedback">${value}</div>
              </td>
            </tr>
          `);
        });
      }

      // Обновление информации о файлах
      if (json["img_info"]) {
        $.each(json["img_info"], function (key, value) {
          $(`${form} tr td:nth-child(4) input[name=${key}] + .doc a`).attr(
            "href",
            "/uploads/employee/" + value
          );
        });
      }

      // Успех
      if (json["success"]) {
        $('#cabinet-employee button[type="submit"]').text("Сохранено");
      }
    },
  });
});

$("#create-note-form").on("click", 'button[type="submit"]', function (e) {
  e.preventDefault();
  $("input, textarea").removeClass("error");
  $(".invalid-feedback").remove();

  let formData = new FormData(),
    inputs = $(`#create-note-form input[type=text]`),
    blog_id = $(`#create-note-form input[name="blog_id"]`),
    is_edit = $(`#create-note-form input[name="is_edit"]`),
    content = $(`#create-note-form textarea`),
    theme = $(`#create-note-form select`);

  let forbiddenWords = [];
  try {
    forbiddenWords = JSON.parse($("#popup-forbidden-words").val()).map(w => w.toLowerCase());
  } catch (err) {
    console.error("Ошибка парсинга запрещённых слов", err);
  }

  let forbiddenFound = false;
  let foundWords = new Set();

  function cleanAndDetectLinksAndHtml(text) {
    // Ищем все HTML-теги
    let htmlMatches = text.match(/<[^>]+>/g);
    if (htmlMatches) {
      forbiddenFound = true;
      // Можно добавить просто метку "HTML-вставка"
      foundWords.add("HTML-вставка");
    }

    // Ищем «отдельные» ссылки вида https://… или http://…, окружённые пробелами или началом/концом строки
    let standaloneUrlRegex = /(?:^|\s)(https?:\/\/[^\s]+)(?=$|\s)/g;
    let match;
    while ((match = standaloneUrlRegex.exec(text)) !== null) {
      forbiddenFound = true;
      // match[1] — это сама ссылка без пробелов, добавляем префикс
      foundWords.add(`ссылка – ${match[1]}`);
    }
    // Удаляем все URL-фрагменты (включая те, что «смешаны» в словах)
    text = text.replace(/https?:\/\/[^\s]+/g, "");

    // Удаляем все HTML-теги
    text = text.replace(/<[^>]+>/g, "");

    return text;
  }

  // Функция разбивает текст на отдельные слова и ищет совпадения
  function checkTextForForbiddenWords(text) {
    const wordsInText = text.toLowerCase().match(/[a-zA-Zа-яА-ЯёЁ0-9_]+/g) || [];
    wordsInText.forEach(word => {
      if (forbiddenWords.includes(word)) {
        forbiddenFound = true;
        foundWords.add(word);
      }
    });
  }

  // Проверяем input[type="text"]
  $.each(inputs, function () {
    let rawValue = $(this).val();
    // Сначала чистим на наличие ссылок/HTML
    let cleanedValue = cleanAndDetectLinksAndHtml(rawValue);

    // Проверяем на запретные слова
    checkTextForForbiddenWords(cleanedValue);
  });

  // Проверяем textarea
  let contentValue = content.val();
  let cleanedContentValue = cleanAndDetectLinksAndHtml(contentValue);

  checkTextForForbiddenWords(cleanedContentValue);

  // Если найдено запрещённое слово, показываем предупреждение и выходим
  if (forbiddenFound) {
    const wordList = Array.from(foundWords).map(w => `"${w}"`).join(", ");
    $("#forbidden-words-list").text(wordList);
    $("#forbidden-popup").fadeIn();
    return false;
  }


  $.each(inputs, function () {
    formData.append($(this).attr("name"), $(this).val());
  });

  formData.append($(blog_id).attr("name"), $(blog_id).val());
  formData.append($(is_edit).attr("name"), $(is_edit).val());
  formData.append($(content).attr("name"), $(content).val());
  formData.append($(theme).attr("name"), $(theme).val());

  $.ajax({
    type: "POST",
    url: "/actions/cabinet/new_blog_note.php",
    dataType: "json",
    data: formData,
    contentType: false,
    processData: false,
    success: function (json) {
      if (json["error"]) {
        $.each(json["error"], function (key, value) {
          if (key == "title") {
            $(`#create-note-form input[name=${key}]`).after(
              `<div class="invalid-feedback">${value}</div>`
            );
          } else if (key == "content") {
            $(`#create-note-form textarea`).after(
              `<div class="invalid-feedback">${value}</div>`
            );
          } else if (key == "keywords") {
            $(`#create-note-form .keywords`).addClass("error");
            $(`#create-note-form .keywords`).after(
              `<div class="invalid-feedback">${value}</div>`
            );
          } else if (key == "theme") {
            if (window.innerWidth < 575) {
              $(`#create-note-form .mobile-theme-btn`).addClass("error");
              $(`#create-note-form .mobile-theme-btn`).after(
                `<div class="invalid-feedback">${value}</div>`
              );
            } else {
              $(`#create-note-form .jq-selectbox`).addClass("error");
              $(`#create-note-form .jq-selectbox`).after(
                `<div class="invalid-feedback">${value}</div>`
              );
            }
          }
        });
      }

      if (json["success"]) {
        $(".create-article-popup")
          .attr("data-link-id", json["success"])
          .fadeIn(200);
        $("body").addClass("hid");
      }
    },
  });
});

$(".delete-theme").on("click", function (e) {
  e.stopPropagation();
  $(".mobile-theme-btn .theme-btn-text").text("Раздел не выбран");
  $(".mobile-theme-btn").addClass("not-chosen");
  $("#blog_theme").val("0");
});

$(".mobile-theme-btn").on("click", function (e) {
  $("#article-popup-service").fadeIn(200);
  $("body").addClass("hid");
});
$(".add-break-button").on("click", function (e) {
  e.preventDefault();

  const $btn = $("#break_submit");

  $btn.prop("disabled", true).text("Подтвердить");

  $(".break__popup").fadeIn(200);
  $("body").addClass("hid");
});

$("#article-popup-service").on("click", "li", function (e) {
  $(".mobile-theme-btn").removeClass("not-chosen");
  $(".mobile-theme-btn .theme-btn-text").text($(this).text());
  $("#blog_theme").val($(this).attr("data-art-id"));
  $("#article-popup-service").fadeOut(200);
  $("body").removeClass("hid");
});

$(".pro-photo__questions").on("click", ".question", function () {
  $(this).siblings(".answer").slideToggle(200);
  $(this).toggleClass("opened");
});

$(".articles__wrapper").on("click", ".article-menu-btn", function () {
  let was_opened = false;
  if ($(this).hasClass("opened")) {
    was_opened = true;
  }
  $(".article-menu").fadeOut(200);
  $(".article-menu-btn").removeClass("opened");
  if (!was_opened) {
    $(this).children(".article-menu").fadeIn(200);
    $(this).addClass("opened");
  }
});
$(".articles__wrapper").on("click", ".more-articles", function () {
  let start = $('.articles__wrapper input[name="start"]').val();
  console.log(start);
  let formData = new FormData();
  formData.append("start", start);
  $.ajax({
    type: "POST",
    url: "/actions/cabinet/more_articles.php",
    dataType: "json",
    data: formData,
    contentType: false,
    processData: false,
    success: function (json) {
      if (json["articles"]) {
        $(".articles__wrapper .articles__block").append(json["articles"]);
        $('.articles__wrapper input[name="start"]').val(parseInt(start) + 5);
      }
      if (json["no_more"]) {
        $(".articles__wrapper .more-articles").prop("disabled", true);
      }
    },
  });
});
$(".articles__wrapper").on("click", ".menu-point.delete", function () {
  let article_id = $(this).attr("data-id");
  $("#delete-article-btn").attr("data-article", article_id);
  $(".delete-article").fadeIn(200);
  $("body").addClass("hid");
});
$(".delete-article").on("click", ".button-new", function () {
  $(".delete-article").fadeOut(200);
  $("body").removeClass("hid");
});

$(".delete-article").on("click", "#delete-article-btn", function () {
  let article_id = $(this).attr("data-article");
  let blog_id = $('.delete-article input[name="blog_id"]').val();
  let formData = new FormData();
  formData.append("article_id", article_id);
  formData.append("blog_id", blog_id);
  $.ajax({
    type: "POST",
    url: "/actions/cabinet/delete_article.php",
    dataType: "json",
    data: formData,
    contentType: false,
    processData: false,
    success: function (json) {
      if (json["success"]) {
        $(
          ".articles__block .article__item[data-article-id=" + article_id + "]"
        ).remove();
        console.log(
          $(
            ".articles__block .article__item[data-article-id=" +
            article_id +
            "]"
          )
        );
      }
    },
  });
});

$(document).mouseup(function (e) {
  // событие клика по веб-документу
  var div = $(".article-menu, .article-menu-btn"); // тут указываем ID элемента
  if (
    !div.is(e.target) && // если клик был не по нашему блоку
    div.has(e.target).length === 0
  ) {
    // и не по его дочерним элементам
    $(".article-menu").fadeOut(200); // скрываем его
    $(".article-menu-btn").removeClass("opened");
  }
});

// Убавляем кол-во по клику
$(".quantity_inner .bt_minus").click(function () {
  let $input = $(this).parent().find(".quantity");
  if ($input.val() == "") $input.val("1");
  let count = parseInt($input.val()) - 1;
  count = count < 0 ? 0 : count;
  $input.val(count);
  $input.removeClass("default");
  $('#main-info button[type="submit"]')
    .prop("disabled", false)
    .text("Сохранить информацию");
});
// Прибавляем кол-во по клику
$(".quantity_inner .bt_plus").click(function () {
  let $input = $(this).parent().find(".quantity");
  if ($input.val() == "") $input.val("1");
  let count = parseInt($input.val()) + 1;
  count =
    count > parseInt($input.data("max-count"))
      ? parseInt($input.data("max-count"))
      : count;
  $input.val(count);
  $input.removeClass("default");
  $('#main-info button[type="submit"]')
    .prop("disabled", false)
    .text("Сохранить информацию");
});
// Убираем все лишнее и невозможное при изменении поля
$(".quantity_inner .quantity").bind("change input", function () {
  $(this).removeClass("default");
  if (this.value.match(/[^0-9]/g)) {
    this.value = this.value.replace(/[^0-9]/g, "");
  }
  if (this.value == "") {
    this.value = 1;
  }
  if (this.value > parseInt($(this).data("max-count"))) {
    this.value = parseInt($(this).data("max-count"));
  }
});

$("#buttons-scroll").on("click", "button", function () {
  let block = $(this).attr("data-id");

  $("html, body").animate(
    {
      scrollTop: $(block).offset().top, // класс объекта к которому приезжаем
    },
    1000
  );
});

$(document).ready(function () {
  const $passwordInput = $("#password-input");
  const $submitBtn = $("#change-password button[type='submit']");

  // при загрузке страницы сразу блокируем кнопку
  $submitBtn.prop("disabled", true);

  // следим за вводом
  $passwordInput.on("input", function () {
    if ($(this).val().trim().length > 0) {
      $submitBtn.prop("disabled", false);
    } else {
      $submitBtn.prop("disabled", true);
    }
  });
});
$("#change-password").on("click", 'button[type="submit"]', function (e) {
  e.preventDefault();

  // Берем user_id из URL или hidden input
  var urlParams = new URLSearchParams(window.location.search);
  var userId = urlParams.get("user_id") || $("#user_id").val();

  $(".invalid-feedback").remove();

  if ($("#password-input").val() == "") {
    $(`#password-input`).after(
      `<div class="invalid-feedback">Введите текущий пароль</div>`
    );
    return;
  }

  $.ajax({
    type: "POST",
    url: "/actions/cabinet/check_password.php",
    dataType: "json",
    data: { id: userId, password: $("#password-input").val() },
    success: function (json) {
      if (json["error"]) {
        $(`#password-input`).after(
          `<div class="invalid-feedback">Пароль неверный</div>`
        );
      }

      if (json["success"]) {
        $("#change-password").slideUp(200);
        setTimeout(function () {
          $("#new-password").slideDown(200);
        }, 200);
      }
    },
  });
});

//Подтверждение прав
$(document).on(
  "click",
  "#rights-confirmation-popup-2 .button-confirm",
  function (e) {
    e.preventDefault();

    $("input").removeClass("error");
    $(".invalid-feedback").html("");

    $.ajax({
      type: "POST",
      url: "/actions/account/rights_confirmation.php",
      dataType: "json",
      data: $(
        "#rights-confirmation-popup-2 input[type=password], #rights-confirmation-popup-2 input[type=hidden], #rights-confirmation-popup-2 input[name=email]"
      ),
      success: function (json) {
        if (json["error"]) {
          $.each(json["error"], function (key, value) {
            $(`#rights-confirmation-popup-2 input[name=${key}]`).addClass(
              "error"
            );
            $(`#rights-confirmation-popup-2 input[name=${key}]`)
              .siblings(".invalid-feedback")
              .html(value);
          });

          if (json["error"]["recovery"])
            $(".contetn").html(json["error"]["recovery"]);
        }

        if (json["success"]) $(".content").html(json["success"]);
      },
    });
  }
);

$(document).ready(function () {
  var urlParams = new URLSearchParams(window.location.search);
  var userId = urlParams.get("user_id");
  var hash = urlParams.get("hash");

  if (userId) {
    $(".security.security-block1").slideUp(0);
  }

  if (hash && userId) {
    $("#change-password").slideUp(0);
    setTimeout(function () {
      $("#new-password").slideDown(200);
    }, 200);
  }
});

$(document).ready(function () {
  const $newPasswordInput = $("#new-password-input");
  const $submitBtn = $("#new-password button[type='submit']");

  // Сразу блокируем кнопку при загрузке
  $submitBtn.prop("disabled", true);

  // Следим за вводом в поле нового пароля
  $newPasswordInput.on("input", function () {
    if ($(this).val().trim().length > 0) {
      $submitBtn.prop("disabled", false);
    } else {
      $submitBtn.prop("disabled", true);
    }
  });

  // Обработка клика по кнопке
  $("#new-password").on("click", 'button[type="submit"]', function (e) {
    e.preventDefault();

    $(".invalid-feedback").remove();

    // Берем user_id из URL или hidden input
    var urlParams = new URLSearchParams(window.location.search);
    var id = urlParams.get("user_id") || $("#user_id").val();
    var newPassword = $newPasswordInput.val().trim();

    // Дополнительная проверка на случай обхода блокировки кнопки
    if (newPassword === "") {
      $newPasswordInput.after(
        `<div class="invalid-feedback">Введите новый пароль</div>`
      );
      return;
    }

    $.ajax({
      type: "POST",
      url: "/actions/cabinet/set_new_password.php",
      dataType: "json",
      data: { id: id, new_password: newPassword },
      success: function (json) {
        if (json["error"]) {
          $newPasswordInput.after(
            `<div class="invalid-feedback">${json["error"]}</div>`
          );
        }

        if (json["success"]) {
          $newPasswordInput.prop("disabled", true);
          $submitBtn.prop("disabled", true).text("Сохранено");
        }
      },
    });
  });
});

$(".show-pass-link").click(function (e) {
  if (!$(this).hasClass("showed")) {
    $(this).addClass("showed").text("Скрыть");
    $(this).siblings("input").attr("type", "text");
  } else {
    $(this).siblings("input").attr("type", "password");
    $(this).removeClass("showed").text("Показать");
  }
});
$(".forget-pass-link").click(function (e) {
  let email = $("#user-email").val();

  $.ajax({
    type: "POST",
    url: "/actions/mail/recovery_password.php",
    dataType: "json",
    data: { email: email },
    success: function (json) {
      console.log(json);
      if (json["success"]) {
        $(".popup.recovery-pass").fadeIn(200);
        $("body").addClass("hid");
      }
      if (json["error"]) {
        $(".forget-pass-link").after(
          `<div class="invalid-feedback">${json["error"]}</div>`
        );
        $(".popup.recovery-pass").fadeIn(200);
      } else {
        $(".popup.recovery-pass").fadeIn(200);
      }
    },
  });
});

$(document).ready(function () {
  var isSyncing = false;

  $(".tariff-card .confirm-btn").click(function () {
    console.log('dsadsa');
    $(".confirm-btn").removeClass("active-button");
    $(this).addClass("active-button");
    var tariffId = $(this).data("tariff-id");
    var tariffName = $(this).data("tariff-name");
    var tariffPrice = $(this).data("tariff-price");
    var tariffPriceOld = $(this).data("tariff-old-price");
    var specialistId = $(this).data("specialist-id");

    $("#popup-tariff-id").val(tariffId);
    $("#popup-specialist-id").val(specialistId);
    $("#popup-tariff-name").text(tariffName);
    $("#popup-tariff-name").text(tariffName.toUpperCase());
    var isChecked =
      $("#duration-switch").is(":checked") ||
      $("#duration-switch-popup").is(":checked");

    if (isChecked) {
      $("#popup-tariff-price").html(
        '<span class="old-price-popup">' +
        tariffPriceOld +
        "</span> " +
        tariffPrice +
        " ₽ в мес."
      );
      $("#popup-tariff-total").text(tariffPrice * 3 + " руб.");
      $("#popup-tariff-total-button").html(
        "&nbsp;" + tariffPrice * 3 + " руб."
      );
      $(".new-price").css("display", "inline-block");
      $(".price-old").addClass("old-price");
      $(".three-month").removeClass("thin");
      $(".one-month").addClass("thin");
      updateDates(3);
    } else {
      $("#popup-tariff-price").text(tariffPriceOld + " ₽ в мес.");
      $("#popup-tariff-total").text(tariffPriceOld + " руб.");
      $("#popup-tariff-total-button").html("&nbsp;" + tariffPriceOld + " руб.");
      $(".new-price").css("display", "none");
      $(".price-old").removeClass("old-price");
      $(".one-month").removeClass("thin");
      $(".three-month").addClass("thin");
      updateDates(1);
    }

    $("#tariff-popup").fadeIn();
  });

  $("#tariff-popup-form").submit(function (event) {
    event.preventDefault();

    const serviceName = "Подключение тарифа";
    ["#js-service-name", "#js-service-name-legal", "#js-service-name-ip"].forEach(id =>
      $(id).val(serviceName)
    );

    const totalText = $("#popup-tariff-total").text();
    const payAmount = parseFloat(totalText.replace(/[^\d\.]/g, "")) || 0;
    ["#js-pay-amount", "#js-pay-amount-legal", "#js-pay-amount-ip"].forEach(id =>
      $(id).val(payAmount)
    );

    var orderId = Date.now();
    $("#js-order-id").val(orderId);
    $("#js-order-id-ip").val(orderId);
    $("#js-order-id-legal").val(orderId);

    const form = $(this);

    // Получаем тариф и продолжительность
    const tariffId = form.find("[name='tariff_id']").val();
    const duration = form.find("[name='duration']").val(); // "1 month" или "3 month"

    // Сегодняшняя дата
    const today = new Date();
    const dateStart = today.toISOString().split("T")[0];

    // Расчёт даты окончания
    const endDate = new Date(today);
    if (duration === "1 month") {
      endDate.setMonth(endDate.getMonth() + 1);
    } else if (duration === "3 months") {
      endDate.setMonth(endDate.getMonth() + 3);
    }
    const dateEnd = endDate.toISOString().split("T")[0];

    // Устанавливаем service_id и даты в скрытые поля всех трёх форм
    ["#js-service-id", "#js-service-id-ip", "#js-service-id-legal"].forEach(id =>
      $(id).val(tariffId)
    );
    ["#js-date-start", "#js-date-start-ip", "#js-date-start-legal"].forEach(id =>
      $(id).val(dateStart)
    );
    ["#js-date-end", "#js-date-end-ip", "#js-date-end-legal"].forEach(id =>
      $(id).val(dateEnd)
    );

    let formData = form.serialize();
    formData += "&orderId=" + encodeURIComponent(orderId);

    $.ajax({
      type: "POST",
      url: "",
      data: formData,
      success: function (response) {
        $("#tariff-popup").fadeOut(function () {
          $("#successful-popup .bold").html("Отлично!<br>Вы подключили тариф.");
          $("#successful-popup .middle-text").text(
            'Информация появится в разделе "Продвижение" в течение 60 минут.'
          );
          $("#payment-popup").fadeIn();
          $(".popup_after_text").html("Услуга будет подключена и отобразится на сайте и в личном кабинете в течение 60 минут после оплаты.");
        });
      },
      error: function (response) {
        alert("Произошла ошибка. Попробуйте снова.");
      },
    });
  });

  /* const urlParams = new URLSearchParams(window.location.search);
  const status = urlParams.get('status');

  if (status === 'success') {
    $("#successful-popup-tariff .bold").html("Отлично!<br>Вы подключили тариф.");
    $("#successful-popup-tariff .middle-text").text(
      'Информация появится в разделе "Продвижение" в течение 60 минут.'
    );
    $("#successful-popup-tariff").fadeIn();

    // Удаляем параметр status из URL, не перезагружая страницу
    urlParams.delete('status');
    const newUrl =
      window.location.pathname +
      (urlParams.toString() ? '?' + urlParams.toString() : '');
    window.history.replaceState({}, document.title, newUrl);
  } */

  var isChecked =
    $("#duration-switch").is(":checked") ||
    $("#duration-switch-popup").is(":checked");
  $("#popup-duration").val(isChecked ? 3 : 1);

  $(".one-month").removeClass("thin");

  function togglePrice(tariffPrice, tariffPriceOld) {
    var isChecked =
      $("#duration-switch").is(":checked") ||
      $("#duration-switch-popup").is(":checked");

    if (isChecked) {
      $("#popup-tariff-price").html(
        '<span class="old-price-popup">' +
        tariffPriceOld +
        "</span> " +
        tariffPrice +
        " ₽ в мес."
      );
      $("#popup-tariff-total").text(tariffPrice * 3 + " руб.");
      $("#popup-tariff-total-button").html(
        "&nbsp;" + tariffPrice * 3 + " руб."
      );
      $(".new-price").css("display", "inline-block");
      $(".price-old").addClass("old-price");
      $(".three-month").removeClass("thin");
      $(".one-month").addClass("thin");
    } else {
      $("#popup-tariff-price").text(tariffPriceOld + " ₽ в мес.");
      $("#popup-tariff-total").text(tariffPriceOld + " руб.");
      $("#popup-tariff-total-button").html("&nbsp;" + tariffPriceOld + " руб.");
      $(".new-price").css("display", "none");
      $(".price-old").removeClass("old-price");
      $(".one-month").removeClass("thin");
      $(".three-month").addClass("thin");
    }
  }

  $("#duration-switch").change(function () {
    if (isSyncing) return;
    isSyncing = true;

    var isChecked = $(this).is(":checked");
    $("#duration-switch-popup").prop("checked", isChecked);
    updateDates(isChecked ? 3 : 1);

    var tariffPrice = $(".active-button").data("tariff-price");
    var tariffPriceOld = $(".active-button").data("tariff-old-price");
    togglePrice(tariffPrice, tariffPriceOld);

    isSyncing = false;
  });

  $("#duration-switch-popup").change(function () {
    if (isSyncing) return;
    isSyncing = true;

    var isChecked = $(this).is(":checked");
    $("#duration-switch").prop("checked", isChecked);
    updateDates(isChecked ? 3 : 1);

    var tariffPrice = $(".active-button").data("tariff-price");
    var tariffPriceOld = $(".active-button").data("tariff-old-price");
    togglePrice(tariffPrice, tariffPriceOld);

    isSyncing = false;
  });

  togglePrice();

  function updateDates(months) {
    var startDate = new Date();
    var endDate = new Date();
    endDate.setMonth(endDate.getMonth() + months);

    $("#popup-duration").val(months + " month" + (months > 1 ? "s" : ""));
    $("#popup-tariff-period").text(months + " месяц" + (months > 1 ? "а" : ""));
    $("#popup-tariff-duration").text(
      "с " +
      startDate.toLocaleDateString("ru-RU") +
      " по " +
      endDate.toLocaleDateString("ru-RU")
    );
  }
});

function clampLines($el, lines) {
  const el = $el[0];
  if (!el) return;

  // Получаем высоту строки через временный элемент
  const temp = document.createElement('span');
  temp.textContent = 'A';
  temp.style.visibility = 'hidden';
  temp.style.position = 'absolute';
  const computed = getComputedStyle(el);
  temp.style.fontSize = computed.fontSize;
  temp.style.fontFamily = computed.fontFamily;
  temp.style.fontWeight = computed.fontWeight;
  temp.style.lineHeight = computed.lineHeight;
  el.appendChild(temp);
  const lineHeight = temp.offsetHeight;
  el.removeChild(temp);

  if (!lineHeight || lineHeight <= 0) {
    return;
  }

  const maxHeight = lineHeight * lines;
  const fullText = $el.data('original') || $el.text().trim();
  if (!fullText) return;

  // Вставляем весь текст
  $el.text(fullText);

  // Проверяем, влезает ли он целиком
  if (el.scrollHeight <= maxHeight) return;

  // Иначе — обрезаем бинарным поиском
  let low = 0, high = fullText.length, mid;

  while (low <= high) {
    mid = Math.floor((low + high) / 2);
    $el.text(fullText.slice(0, mid) + '…');
    if (el.scrollHeight > maxHeight) {
      high = mid - 1;
    } else {
      low = mid + 1;
    }
  }

  $el.text(fullText.slice(0, high) + '…');
}



function loadAndClamp(selector = '.cut-text-lk', lines) {
  $(selector).each(function () {
    const $el = $(this);
    const url = $el.data('url');

    if (!$el.data('original')) {
      $el.data('original', $el.text().trim()); // сохраняем оригинальный текст
    }

    if (url) {
      $.get(url)
        .done(function (data) {
          if (typeof data === 'string') {
            $el.data('original', data.trim());
            $el.text(data.trim());
            clampLines($el, lines);
          } else {
            console.warn('Ожидался текст от', url, 'но получен:', data);
          }
        })
        .fail(function (jqXHR, textStatus) {
          console.error('Ошибка загрузки по URL:', url, '-', textStatus);
        });
    } else {
      $el.text($el.data('original'));
      clampLines($el, lines);
    }
  });
}

$(document).ready(function () {

  function updateClamp() {
    loadAndClamp('.cut-text-lk', 4);
  }

  updateClamp();

  $(window).on('resize', function () {
    updateClamp();
  });
});

$(document).ready(function () {
  var isSyncing = false;

  // Обработка клика по чекбоксу
  $(".table-row").on("click", ".switch input", function (event) {
    var $checkbox = $(this);
    if ($checkbox.is('input[type="checkbox"]')) {
      if ($checkbox.prop("checked")) {
        event.preventDefault();
        var $label = $checkbox.closest("label");
        var optionId = $label.data("option-id");
        var optionName = $checkbox
          .closest(".table-row")
          .find(".table-cell:first-child")
          .text();
        var optionPriceText = $checkbox
          .closest(".table-row")
          .find(".connect-button")
          .text();
        var optionPrice = (optionPriceText.match(/\d+/) || [])[0] || "0";
        var specialistId = $label.data("specialist-id");
        const discount = parseFloat($("#discount-value").val()); // Получаем скидку

        $("#popup-option-id").val(optionId);
        $("#popup-specialist-option-id").val(specialistId);
        $("#popup-option-name").text(optionName);
        $("#popup-option-price").text(optionPrice);
        $("#popup-option-price-after").text(" ₽ в мес. ");

        // Расчет новой цены с учетом скидки
        var discountAmount = (optionPrice * discount) / 100;
        var newPrice = Math.round(optionPrice - discountAmount);

        // Обновление полей с ценами и скидкой
        if (discount > 0) {
          $("#popup-option-discount").text(discount + "%");
          $("#popup-option-old-price").text(optionPrice + " ₽");
          $("#popup-option-total").text(newPrice + " ₽");
          $("#popup-option-total-button").html("&nbsp;" + newPrice + " ₽");
        } else {
          $(".popup_discount").hide();
          $("#popup-option-discount").text("");
          $("#popup-option-old-price").text("");
          $("#popup-option-total").text(optionPrice + " ₽");
          $("#popup-option-total-button").html("&nbsp;" + optionPrice + " ₽");
        }

        updatePopupDates(1);
        $("#option-popup").fadeIn();
      } else {
        event.preventDefault();
        return;
      }
    }
  });

  // Обработка клика по кнопке "Подключить"
  $(".table-row").on("click", ".connect-button-option", function (event) {
    event.preventDefault();
    var $button = $(this);
    var optionId = $button.data("option-id");
    var optionName = $button
      .closest(".table-row")
      .find(".table-cell:first-child")
      .text();
    var optionPriceText = $button.text();
    var optionPrice = parseFloat(
      (optionPriceText.match(/\d+/) || [])[0] || "0"
    );
    var specialistId = $button.data("specialist-id");
    const discount = parseFloat($("#discount-value").val());

    $("#popup-option-id").val(optionId);
    $("#popup-specialist-option-id").val(specialistId);
    $("#popup-option-name").text(optionName);
    $("#popup-option-price").text(optionPrice);
    $("#popup-option-price-after").text(" ₽ в мес. ");

    // Расчет новой цены с учетом скидки
    var discountAmount = (optionPrice * discount) / 100;
    var newPrice = Math.round(optionPrice - discountAmount);

    // Обновление полей с ценами и скидкой
    if (discount > 0) {
      $("#popup-option-discount").text(discount + "%");
      $("#popup-option-old-price").text(optionPrice + " ₽");
      $("#popup-option-total").text(newPrice + " ₽");
      $("#popup-option-total-button").html("&nbsp;" + newPrice + " ₽");
    } else {
      $(".popup_discount").hide();
      $("#popup-option-discount").text("");
      $("#popup-option-old-price").text("");
      $("#popup-option-total").text(optionPrice + " ₽");
      $("#popup-option-total-button").html("&nbsp;" + optionPrice + " ₽");
    }

    updatePopupDates(1);
    $("#option-popup").fadeIn();
  });

  // Обработка клика по кнопке "Подключить"
  $(".table-row").on("click", ".connect-button-option", function (event) {
    event.preventDefault();
    var $button = $(this);
    var optionId = $button.data("option-id");
    var optionName = $button
      .closest(".table-row")
      .find(".table-cell:first-child")
      .text();
    var optionPriceText = $button.text();
    var optionPrice = parseFloat(
      (optionPriceText.match(/\d+/) || [])[0] || "0"
    );
    var specialistId = $button.data("specialist-id");
    const discount = parseFloat($("#discount-value").val());

    $("#popup-option-id").val(optionId);
    $("#popup-specialist-option-id").val(specialistId);
    $("#popup-option-name").text(optionName);
    $("#popup-option-price").text(optionPrice);
    $("#popup-option-price-after").text(" ₽ в мес. ");

    // Расчет новой цены с учетом скидки
    var discountAmount = (optionPrice * discount) / 100;
    var newPrice = Math.round(optionPrice - discountAmount);

    // Обновление полей с ценами и скидкой
    if (discount > 0) {
      $("#popup-option-discount").text(discount + "%");
      $("#popup-option-old-price").text(optionPrice + " ₽");
      $("#popup-option-total").text(newPrice + " ₽");
      $("#popup-option-total-button").html("&nbsp;" + newPrice + " ₽");
    } else {
      $(".popup_discount").hide();
      $("#popup-option-discount").text("");
      $("#popup-option-old-price").text("");
      $("#popup-option-total").text(optionPrice + " ₽");
      $("#popup-option-total-button").html("&nbsp;" + optionPrice + " ₽");
    }

    updatePopupDates(1);
    $("#option-popup").fadeIn();
  });

  function updatePopupDates(months) {
    var startDate = new Date();
    var endDate = new Date();
    endDate.setMonth(endDate.getMonth() + months);

    $("#popup-duration").val(months + " month" + (months > 1 ? "s" : ""));
    $("#popup-option-period").text(months + " месяц" + (months > 1 ? "а" : ""));
    $("#popup-option-duration").text(
      "с " +
      startDate.toLocaleDateString("ru-RU") +
      " по " +
      endDate.toLocaleDateString("ru-RU")
    );
  }

  $(document).on("submit", "#option-popup-form", function (event) {
    event.preventDefault();
    var form = $(this);

    var serviceName = "Подключение опции";
    $("#js-service-name").val(serviceName);
    $("#js-service-name-legal").val(serviceName);
    $("#js-service-name-ip").val(serviceName);

    var totalText = $("#popup-option-total").text();
    var payAmount = parseFloat(totalText.replace(/[^\d\.]/g, "")) || 0;
    $("#js-pay-amount").val(payAmount);
    $("#js-pay-amount-legal").val(payAmount);
    $("#js-pay-amount-ip").val(payAmount);

    var orderId = Date.now();
    $("#js-order-id").val(orderId);
    $("#js-order-id-ip").val(orderId);
    $("#js-order-id-legal").val(orderId);

    // Получаем option_id из формы
    var optionId = form.find("[name='option_id']").val();

    // Дата начала — сегодня
    var today = new Date();
    var dateStart = today.toISOString().split('T')[0];

    // Дата окончания — через месяц
    var nextMonth = new Date(today);
    nextMonth.setMonth(nextMonth.getMonth() + 1);
    var dateEnd = nextMonth.toISOString().split('T')[0];

    // Заполняем скрытые поля во всех типах форм
    ['#js-service-id', '#js-service-id-ip', '#js-service-id-legal'].forEach(id =>
      $(id).val(optionId)
    );

    ['#js-date-start', '#js-date-start-ip', '#js-date-start-legal'].forEach(id =>
      $(id).val(dateStart)
    );

    ['#js-date-end', '#js-date-end-ip', '#js-date-end-legal'].forEach(id =>
      $(id).val(dateEnd)
    );

    var formData = form.serialize(); // сериализуем форму
    formData += '&orderId=' + encodeURIComponent(orderId);

    $.ajax({
      type: "POST",
      url: "",
      data: formData,
      success: function (response) {
        $("#option-popup").fadeOut(function () {
          $("#successful-popup .bold").html("Отлично!<br>Вы подключили опции.");
          $("#successful-popup .middle-text").text(
            'Информация появится в разделе "Продвижение" в течение 60 минут.'
          );
          $("#payment-popup").fadeIn();
          $(".popup_after_text").html("Услуга будет подключена и отобразится на сайте и в личном кабинете в течение 60 минут после оплаты.");
        });
      },
      error: function (response) {
        console.log(response);
        alert("Произошла ошибка. Попробуйте снова.");
      },
    });
  });
});

function calculateDays(startDate, endDate) {
  var oneDay = 1000 * 60 * 60 * 24;
  var differenceInTime = endDate.getTime() - startDate.getTime();
  return Math.round(differenceInTime / oneDay) + 1;
}

$(document).ready(function () {
  $(
    'input[name="service_ids[]"], input[name="journal_id"], input[name="banner_id"]'
  ).on("change", function () {
    var selectedName = $(this).data("name");

    if ($(this).attr("name") === "service_ids[]") {
      $(".selected-text-promo").text(selectedName);
    } else if ($(this).attr("name") === "journal_id") {
      $(".selected-text-journal").text(selectedName);
    } else if ($(this).attr("name") === "banner_id") {
      $(".selected-text-banner").text(selectedName);
    }
  });

  function fullValidationBanner() {
    let valid = true;
    // очистка всех ошибок
    $(".error").text("");

    // 1) Выбор места размещения
    if (!$('input[name="banner_id"]:checked').length) {
      $(".error-banner_id").text("Выберите место размещения");
      valid = false;
    }

    // 2) Даты
    let dv = $("#date-select").val();
    if (!dv) {
      $(".error-date").text("Выберите период размещения");
      valid = false;
    } else {
      let parts = dv.split("—");
      if (
        parts.length < 2 ||
        !moment(parts[0].trim(), "DD.MM.YYYY", true).isValid() ||
        !moment(parts[1].trim(), "DD.MM.YYYY", true).isValid()
      ) {
        $(".error-date").text("Введите корректный диапазон дат");
        valid = false;
      }
    }

    // 3) Текст баннера строка 1
    let l1 = $('input[name="ad_line1"]').val() || "";
    if (l1.length === 0) {
      $(".error-ad_line1").text("Строка 1 не может быть пустой.");
      valid = false;
    } else if (l1.length > 30) {
      $(".error-ad_line1").text("Превышено допустимое количество символов");
      valid = false;
    }

    // 4) Строка 2
    let l2 = $('input[name="ad_line2"]').val() || "";
    if (l2.length === 0) {
      $(".error-ad_line2").text("Строка 2 не может быть пустой.");
      valid = false;
    } else if (l2.length > 30) {
      $(".error-ad_line2").text("Превышено допустимое количество символов");
      valid = false;
    }

    // 5) Строка 3
    let l3 = $('input[name="ad_line3"]').val() || "";
    if (l3.length === 0) {
      $(".error-ad_line3").text("Строка 3 не может быть пустой.");
      valid = false;
    } else if (l3.length > 30) {
      $(".error-ad_line3").text("Превышено допустимое количество символов");
      valid = false;
    }

    return valid;
  }

  // 2) При вводе/смене — убираем ошибку у конкретного поля
  $(document).on(
    "input change",
    '#banner-popup-form input, #banner-popup-form .select-option input[type="radio"]',
    function () {
      let name = $(this).attr("name"),
        sel =
          name === "banner_id"
            ? ".error-banner_id"
            : name === "date"
              ? ".error-date"
              : ".error-" + name;
      $(sel).text("");
    }
  );

  // Открытие первого попапа и установка значений
  $(".reklama-button").click(function () {
    var bannerName = $(this)
      .closest(".table-row")
      .find(".table-cell:first-child")
      .text();
    var specialistId = $(this).data("specialist-id");

    $("#popup-specialist-id").val(specialistId);
    $("#popup-banner-name").text(bannerName);

    $("#banner-popup").fadeIn();
  });

  // Обработка клика по кнопке "Продолжить"
  $(".reklama-continue-btn").click(function (event) {
    event.preventDefault();

    if (!fullValidationBanner()) {
      // ошибки показаны, дальше не идём
      return;
    }

    let isFormValid = true;
    let form = ("#banner-popup-form");

    const { hasForbidden, words } = checkForbiddenWords(`${form} input[type="text"], ${form} textarea`);

    if (hasForbidden) {
      showForbiddenPopup(words);
      $(this).prop("disabled", false).text("Сохранить");
      return false;
    }

    // Проверяем, что все поля заполнены, включая радиокнопки
    $(
      '#banner-popup-form input, #banner-popup-form .select-option input[type="radio"]'
    ).each(function () {
      if (
        $(this).val() === "" ||
        !$('input[name="banner_id"]:checked').length
      ) {
        isFormValid = false;
        return false;
      }
    });

    var selectedDates = $("#date-select").val().split("—");
    console.log("Selected Dates:", selectedDates);

    if (selectedDates.length < 2) {
      alert("Пожалуйста, выберите обе даты.");
      return;
    }

    var startDate = moment(selectedDates[0].trim(), "DD.MM.YYYY").toDate();
    var endDate = moment(selectedDates[1].trim(), "DD.MM.YYYY").toDate();
    console.log("Start Date:", startDate);
    console.log("End Date:", endDate);

    if (!moment(startDate).isValid() || !moment(endDate).isValid()) {
      alert("Пожалуйста, выберите корректные даты.");
      return;
    }

    // Получаем выбранный баннер из радиокнопок
    var selectedBannerId = $('input[name="banner_id"]:checked').val();
    var selectedBannerPrice = $(
      `#popup-banner-price-${selectedBannerId}`
    ).val();
    var selectedBannerOption = $('input[name="banner_id"]:checked')
      .closest("label")
      .text()
      .trim();
    var daysCount = calculateDays(startDate, endDate);
    var weeksCount = Math.ceil(daysCount / 7);
    var pricePerMonth = parseFloat(selectedBannerPrice);
    var pricePerDay = pricePerMonth / 30;
    var totalPrice = weeksCount * pricePerMonth;

    var selectedBannerName = $('input[name="banner_id"]:checked').data('name');

    console.log("Days Count:", daysCount);
    console.log("Price Per Month:", pricePerMonth);
    console.log("Price Per Day:", pricePerDay);
    console.log("Total Price:", totalPrice);

    // Устанавливаем данные во второй попап перед его отображением
    $("#banner-name").text(selectedBannerOption);
    $("#banner-price").html(
      `${selectedBannerPrice}<span class="light"> ₽ в нед.</span>`
    );
    let weekText;

    if (weeksCount === 1) {
      weekText = "неделя";
    } else if (weeksCount >= 2 && weeksCount <= 4) {
      weekText = "недели";
    } else {
      weekText = "недель";
    }

    $("#banner-period").text(weeksCount + " " + weekText);
    $("#start-date").text(selectedDates[0].trim());
    $("#end-date").text(selectedDates[1].trim());
    $("#total-price").text(Math.floor(totalPrice) + " руб.");

    // Скрытые поля
    $("#popup-banner-id-2").val(selectedBannerId);
    $("#popup-banner-name-2").val(selectedBannerName);
    $("#popup-start-date").val(startDate.toISOString().split("T")[0]);
    $("#popup-end-date").val(endDate.toISOString().split("T")[0]);

    $("#banner-popup").fadeOut(function () {
      $("#banner-popup-2").fadeIn();
    });
    $(".popup-footer").addClass("hidden");
  });

  // Обработка отправки формы второго попапа
  $("#banner-popup-form-2").submit(function (event) {
    event.preventDefault();

    var adLine1 = $('input[name="ad_line1"]').val() || "";
    var adLine2 = $('input[name="ad_line2"]').val() || "";
    var adLine3 = $('input[name="ad_line3"]').val() || "";
    var description;

    try {
      description = JSON.stringify({
        ad_line1: adLine1,
        ad_line2: adLine2,
        ad_line3: adLine3,
      });
      $("#popup-description").val(description);
    } catch (e) {
      alert("Ошибка при обработке данных.");
      return;
    }

    let formData = $(this).serialize();

    $.ajax({
      type: "POST",
      url: "",
      data: formData,
      success: function () {
        $("#successful-popup .successful-title").html(
          "Отлично!<br>Вы направили баннер на модерацию."
        );
        $("#successful-popup .middle-text").text(
          "Как правило, проверка занимает до 24 часов."
        );
        $("#successful-popup").fadeIn();
      },
      error: function (xhr) {
        alert("Произошла ошибка. Попробуйте снова.");
        console.error(xhr.responseText);
      },
    });
  });

  $('.physical-form-button').on('click', function (e) {
    e.preventDefault();

    let payAmountText = $("#total-price").text();
    let payAmount = parseFloat(payAmountText.replace(/[^\d\.]/g, ""));

    let buttonType = $(this).data('type');
    let form = $(this).closest('form');

    // Собираем данные из этой формы
    let paymentData = {
      pay_amount: form.find('input[name="pay_amount"]').val(),
      clientid: form.find('input[name="client_name"]').val() || 'Клиент',
      orderId: form.find('input[name="orderId"]').val(),
      client_email: form.find('input[name="client_email"]').val(),
      reg_email: form.find('input[name="reg_email"]').val(),
      service_name: form.find('input[name="service_name"]').val(),
      client_phone: form.find('input[name="client_phone"]').val() || '',

      // Новые поля
      service_id: form.find('input[name="service_id"]').val(),
      date_start: form.find('input[name="date_start"]').val(),
      date_end: form.find('input[name="date_end"]').val(),
      service_type: buttonType
    };

    $.ajax({
      type: "POST",
      url: "../actions/payment/paykeeper_invoice.php",
      data: paymentData,
      success: function (response) {
        if (!response.success) {
          alert("Ошибка: " + response.error);
          return;
        }
        window.location.href = response.invoice_url;
      },
      error: function (xhr) {
        alert("Ошибка при отправке запроса. Проверьте корректность введенных данных.");
        console.error(xhr.responseText);
      },
    });
  });

  let pollCount = 0;
  const maxPolls = 18; // 3 минуты

  function pollPaymentStatus(documentId, customerCode, onSuccess) {
    const interval = setInterval(() => {
      $.get(
        "../actions/mail/check_payment_status.php",
        {
          documentId: documentId,
          customerCode: customerCode,
        },
        function (res) {
          if (res.paid) {
            clearInterval(interval);
            onSuccess();
          } else if (pollCount++ > maxPolls) {
            clearInterval(interval);
            alert("Оплата не была подтверждена в течение 3 минут.");
          }
        }
      );
    }, 10000);
  }

  $('.legal-form-button').on('click', function (e) {
    e.preventDefault();
    let button = $(this);
    let action = button.data('action'); // 'invoice' | 'sbp' | 'card'
    let form = button.closest('form');

    // Сохраняем оригинальный текст и ставим спиннер
    button.data('original-text', button.html())
      .prop('disabled', true)
      .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="border-width:.15em; border-right-color:transparent;"></span>');

    // Собираем данные
    let customerType = form.attr('id') === 'legal-payment-form' ? 'company' : 'ip';
    let data = {
      pay_amount: form.find('input[name="pay_amount"]').val(),
      service_name: form.find('input[name="service_name"]').val(),
      orderId: form.find('input[name="orderId"]').val(),
      specialistId: form.find('input[name="specilaistId"]').val(),
      client_email: form.find('input[name="client_email"]').val(),
      tax_code: form.find('input[name="inn"]').val(),
      customer_type: customerType,
      action: action,

      // Новые поля
      service_id: form.find('input[name="service_id"]').val(),
      date_start: form.find('input[name="date_start"]').val(),
      date_end: form.find('input[name="date_end"]').val()
    };

    $.ajax({
      type: 'POST',
      url: '../actions/payment/tochka_invoice.php',
      data: data,
      dataType: 'json',
      success: function (response) {
        console.log('ТОЧНЫЙ response из PHP:', response);

        $.ajax({
          type: 'POST',
          url: '../actions/mail/send_mail_tochka.php',
          data: { email: data.client_email, download_url: response.download_url, service_name: data.service_name, specialist_id: data.specialistId },
          success: function (res) {
            console.log('Письмо успешно отправлено через send_mail_tochka.php', res);
          },
          error: function (err) {
            console.error('Ошибка при отправке письма через send_mail_tochka.php', err);
          }
        });

        $("#payment-popup").fadeOut(() => {
          $("#successful-popup .successful-title").html('Счёт успешно сформирован!');

          const container = $("#successful-popup .middle-text");
          container.empty(); // очищаем

          if (action === 'invoice') {
            const promoText = 'Сформированный счёт отправлен вам на электронную почту и ожидает оплаты <span style="font-weight: 500; text-decoration: underline;">в течение 1 часа. В случае неоплаты бронь выбранных дат пропадет.</span><br><br> Также вы можете скачать счёт в формате PDF, нажав на кнопку.';

            const defaultText = 'Сформированный счёт отправлен вам на электронную почту и ожидает оплаты.<br><br> Также вы можете скачать счёт в формате PDF, нажав на кнопку.';

            $("#successful-popup .middle-text").html(
              data.service_name == 'Размещение карточки в промо-блоках' ? promoText : defaultText
            );
            $("#download-pdf")
              .attr("href", response.download_url)
              .attr("download", `invoice_${response.invoice_id}.pdf`)
              .removeAttr("target")
              .show();
          }
          else if (action === 'sbp') {
            container.append(
              $('<p>').text('Отсканируйте QR‑код для оплаты по СБП:')
            );
            // вставляем QR-код
            container.append(
              $('<img>')
                .attr('src', 'data:image/png;base64,' + response.qr_code_base64)
                .css({ 'max-width': '200px', 'display': 'block', 'margin': '10px auto' })
            );
          }
          else if (action === 'card') {
            container.append(
              $('<p>').text('Чтобы оплатить банковской картой, перейдите по ссылке:')
            );
            // вставляем ссылку на оплату
            container.append(
              $('<a>')
                .attr({ href: response.card_payment_url, target: '_blank' })
                .addClass('btn btn-primary mt-2')
                .text('Оплатить картой')
            );
          }

          $("#successful-popup").fadeIn();
        });

        // Если нужно опрашивать статус платежа
        if (action !== 'invoice') {
          pollPaymentStatus(
            response.invoice_id,
            response.customer_code,
            () => sendPromoDataToBackend()
          );
        }
      },
      error: function (xhr) {
        alert('Ошибка при отправке запроса. Проверьте корректность введенных данных.');
        console.error(xhr.responseText);
      },
      complete: function () {
        // Восстанавливаем кнопку
        button.prop('disabled', false)
          .html(button.data('original-text'));
      }
    });
  });

  $("#payer-type").on("change", function () {
    const selected = $(this).val();
    $(".payer-form").hide();
    $("#" + selected).show();
  });
});
$(document).ready(function () {
  function fullValidation() {
    let valid = true;

    // Сразу очищаем _все_ ошибки
    $(".error").text("");

    // 1. Журнал
    if (!$('input[name="journal_id"]:checked').length) {
      $(".error-journal_id").text("Выберите место размещения");
      valid = false;
    }

    // 2. Дата
    let dv = $("#date-select-journal").val();
    if (!dv) {
      $(".error-date").text("Выберите период размещения");
      valid = false;
    } else {
      let parts = dv.split("—");
      if (
        parts.length < 2 ||
        !moment(parts[0].trim(), "DD.MM.YYYY", true).isValid() ||
        !moment(parts[1].trim(), "DD.MM.YYYY", true).isValid()
      ) {
        $(".error-date").text("Введите корректный диапазон дат");
        valid = false;
      }
    }

    // 3. Заголовок
    let title = $('input[name="ad_line1_j"]').val() || "";
    if (title.length === 0) {
      $(".error-ad_line1_j").text("Заголовок не может быть пустым");
      valid = false;
    } else if (title.length > 50) {
      $(".error-ad_line1_j").text("Превышено допустимое количество символов");
      valid = false;
    }

    // 4. Описание
    let desc = $('input[name="ad_line2_j"]').val() || "";
    if (desc.length === 0) {
      $(".error-ad_line2_j").text("Описание не может быть пустым");
      valid = false;
    } else if (desc.length > 80) {
      $(".error-ad_line2_j").text("Превышено допустимое количество символов");
      valid = false;
    }

    return valid;
  }

  // 2) При вводе/смене — убираем сообщение только под этим полем
  $(document).on(
    "input change",
    '#journal-popup-form input, #journal-popup-form .select-option input[type="radio"]',
    function () {
      let name = $(this).attr("name"),
        sel =
          name === "journal_id"
            ? ".error-journal_id"
            : name === "date"
              ? ".error-date"
              : ".error-" + name;
      $(sel).text("");
    }
  );

  // Открытие первого попапа и установка значений
  $(".journal-button").click(function () {
    var journalName = $(this)
      .closest(".table-row")
      .find(".table-cell:first-child")
      .text();
    var specialistId = $(this).data("specialist-id");

    $("#popup-specialist-id").val(specialistId);
    $("#popup-journal-name").text(journalName);
    $("#journal-popup").fadeIn();
  });

  // Обработчик для кнопки продолжить
  $(".journal-continue-btn").click(function (event) {
    event.preventDefault();

    const isValid = fullValidation(); // строгое валидирование и показ ошибок

    if (!isValid) return;

    let form = ("#journal-popup-form");

    const { hasForbidden, words } = checkForbiddenWords(`${form} input[type="text"], ${form} textarea`);

    if (hasForbidden) {
      showForbiddenPopup(words);
      $(this).prop("disabled", false).text("Сохранить");
      return false;
    }

    var selectedDates = $("#date-select-journal").val().split("—");
    console.log("Selected Dates:", selectedDates);
    if (selectedDates.length < 2) {
      alert("Пожалуйста, выберите обе даты.");
      return;
    }

    var startDate = moment(selectedDates[0].trim(), "DD.MM.YYYY").toDate();
    var endDate = moment(selectedDates[1].trim(), "DD.MM.YYYY").toDate();
    console.log("Start Date:", startDate);
    console.log("End Date:", endDate);

    if (!moment(startDate).isValid() || !moment(endDate).isValid()) {
      alert("Пожалуйста, выберите корректные даты.");
      return;
    }

    var selectedJournalId = $('input[name="journal_id"]:checked').val();
    var selectedJournalPrice = $(
      `#popup-journal-price-${selectedJournalId}`
    ).val();
    var selectedJournalOption = $('input[name="journal_id"]:checked')
      .closest("label")
      .text()
      .trim();
    var daysCount = calculateDays(startDate, endDate);
    var weeksCount = Math.ceil(daysCount / 7);
    var pricePerMonth = parseFloat(selectedJournalPrice);
    var pricePerDay = pricePerMonth / 30;
    var totalPrice = weeksCount * pricePerMonth;

    var selectedJournalName = $('input[name="journal_id"]:checked').data('name') || "";

    console.log("Days Count:", daysCount);
    console.log("Price Per Month:", pricePerMonth);
    console.log("Price Per Day:", pricePerDay);
    console.log("Total Price:", totalPrice);

    $("#journal-name").text(selectedJournalOption);
    $("#journal-price").html(
      `${selectedJournalPrice}<span class="light"> ₽ в нед.</span>`
    );
    let weekText;

    if (weeksCount === 1) {
      weekText = "неделя";
    } else if (weeksCount >= 2 && weeksCount <= 4) {
      weekText = "недели";
    } else {
      weekText = "недель";
    }

    $("#journal-period").text(weeksCount + " " + weekText);
    $("#start-date-journal").text(selectedDates[0].trim());
    $("#end-date-journal").text(selectedDates[1].trim());
    $("#total-price-journal").text(Math.floor(totalPrice) + " руб.");

    $("#popup-journal-id-2").val(selectedJournalId);
    $("#popup-journal-name-2").val(selectedJournalName);
    $("#popup-start-date-journal").val(startDate.toISOString().split("T")[0]);
    $("#popup-end-date-journal").val(endDate.toISOString().split("T")[0]);

    $("#journal-popup").fadeOut(function () {
      $("#journal-popup-2").fadeIn();
    });
    $(".popup-footer").addClass("hidden");
  });

  // Обработка отправки формы второго попапа
  $("#journal-popup-form-2").submit(function (event) {
    event.preventDefault();

    // Сбор данных из полей
    var adLine1 = $('input[name="ad_line1_j"]').val() || "";
    var adLine2 = $('input[name="ad_line2_j"]').val() || "";

    // Формирование JSON-строки
    try {
      var description = JSON.stringify({
        ad_line1: adLine1,
        ad_line2: adLine2,
      });

      // Устанавливаем значение скрытого поля
      $("#popup-description-journal").val(description);
    } catch (e) {
      console.error("Ошибка при формировании JSON:", e);
      alert("Произошла ошибка при обработке данных. Попробуйте снова.");
      return;
    }
    let formData = $(this).serialize();

    var serviceName = "Размещение в журнале";
    $("#js-service-name").val(serviceName);
    $("#js-service-name-legal").val(serviceName);
    $("#js-pay-amount-ip").val(serviceName);

    var payAmountText = $("#journal-price").text() || "";
    var payAmount = parseFloat(payAmountText.replace(/[^\d\.]/g, "")) || 0;
    $("#js-pay-amount").val(payAmount);
    $("#js-pay-amount-legal").val(payAmount);
    $("#js-pay-amount-ip").val(payAmount);

    $.ajax({
      type: "POST",
      url: "",
      data: formData,
      success: function (response) {
        $("#successful-popup .successful-title").html(
          "Отлично!<br>Вы направили блок на модерацию."
        );
        $("#successful-popup .middle-text").text(
          "Как правило, проверка занимает до 24 часов."
        );
        $("#successful-popup").fadeIn();
      },
      error: function (xhr, status, error) {
        console.error("Ошибка при отправке:", xhr.responseText);
        alert("Произошла ошибка. Попробуйте снова.");
      },
    });
  });
});

$(document).ready(function () {
  $(".user").hover(function () {
    $(".dropdown-content").toggle();
  });
});

$(document).ready(function () {

  $("#cat-item").on("click", function (e) {
    e.preventDefault();
    const href = $(this).attr("href");
    // Добавляем параметр, если его нет
    const url = new URL(href, window.location.origin);
    url.searchParams.set('openCatalog', '1');
    window.location.href = url.toString();
  });

  $(".promo-card-button").click(function () {
    $("#promo-popup").fadeIn();
  });

  const discount = parseFloat($("#discount-value").val());

  let selectedPromos = [];

  function truncateText(text, maxLength) {
    if (text.length > maxLength) {
      const truncated = text.slice(0, maxLength - 2);
      const lastChar = '"';
      return `${truncated}...${lastChar}`;
    }
    return text;
  }

  function updateSecondPopup() {
    // Очистка содержимого второго попапа
    $("#second-popup-content").empty();
    // Добавление всех выбранных опций во второй попап
    selectedPromos.forEach((promo, index) => {
      const truncatedTitle = truncateText(promo.title, 20);
      const promoHtml = `
              <div class="selected-promo">
                <div class="promo-number">${index + 1}.</div>
                <div class="promo-details">
                  <div class="promo-info">
                    <span class="promo-title">${truncatedTitle}</span>
                    <span class="promo-dates">${promo.dates}</span>
                  </div>
                  <img src="${promo.icon}" alt="Icon" class="promo-icon">
                </div>
                <div class="promo-right">
                  <div class="promo-price"><span class="light">${promo.price
        }</span> <span>₽</span></div>
                  <button class="delete-selected-promo" data-index="${index}">
                  </button>
                </div>
              </div>`;
      $("#second-popup-content").append(promoHtml);
    });

    // Расчет старой цены (без скидки)
    const oldPrice = selectedPromos.reduce(
      (total, promo) => total + parseFloat(promo.price),
      0
    );

    // Расчет новой цены с учетом скидки
    if (discount > 0) {
      const discountAmount = (oldPrice * discount) / 100;
      let newPrice = oldPrice - discountAmount;
      newPrice = Math.round(newPrice);

      // Отображение скидки и цен
      $("#popup-discount").text(`${discount}%`);
      $("#popup-old-price").text(`${oldPrice} руб.`);
      $("#discount-section").show();
      $("#total-price2").text(`${newPrice} руб.`);
      $("#confirm-price").html(
        `Подключить за <span class="regular">${newPrice} руб.</span>`
      );
    } else {
      // Скрываем скидку и старую цену
      $(".popup_discount").hide();
      $("#total-price2").text(`${oldPrice} руб.`);
      $("#confirm-price").html(
        `Подключить за <span class="regular">${oldPrice} руб.</span>`
      );
    }
  }

  function rangesOverlap(range1, range2) {
    return range1.from <= range2.to && range2.from <= range1.to;
  }
  // Обработка нажатия кнопки "Далее" в первом попапе
  // 1) Утилиты
  function rangesOverlap(a, b) {
    return a.from <= b.to && b.from <= a.to;
  }

  function subtractRanges(selected, disabledList) {
    let result = [{ ...selected }];
    disabledList.forEach(disabled => {
      const next = [];
      result.forEach(range => {
        if (!rangesOverlap(range, disabled)) {
          next.push(range);
        } else {
          if (disabled.from > range.from) {
            next.push({
              from: range.from,
              to: new Date(disabled.from.getTime() - 24 * 60 * 60 * 1000)
            });
          }
          if (disabled.to < range.to) {
            next.push({
              from: new Date(disabled.to.getTime() + 24 * 60 * 60 * 1000),
              to: range.to
            });
          }
        }
      });
      result = next;
    });
    return result;
  }

  // 1) Утилиты
  function rangesOverlap(a, b) {
    return a.from <= b.to && b.from <= a.to;
  }

  function subtractRanges(selected, disabledList) {
    let result = [{ ...selected }];
    disabledList.forEach(disabled => {
      const next = [];
      result.forEach(range => {
        if (!rangesOverlap(range, disabled)) {
          next.push(range);
        } else {
          if (disabled.from > range.from) {
            next.push({
              from: range.from,
              to: new Date(disabled.from.getTime() - 24 * 60 * 60 * 1000)
            });
          }
          if (disabled.to < range.to) {
            next.push({
              from: new Date(disabled.to.getTime() + 24 * 60 * 60 * 1000),
              to: range.to
            });
          }
        }
      });
      result = next;
    });
    return result;
  }

  // 2) Обработчик кнопки
  $(document).on("click", ".promo-continue-btn", function (event) {
    event.preventDefault();

    // 1) Собираем ввод
    const selectedPromoId = $('input[name="promo_id"]:checked').val();
    const selectedServiceIds = $('input[name="service_ids[]"]:checked')
      .map(function () { return $(this).val(); }).get();
    const datesStr = $("#promo-date-select").val() || "";
    const parts = datesStr.split("—").map(s => s.trim());

    if (!selectedPromoId || !selectedServiceIds.length || parts.length < 2) {
      return $(".popup-footer")
        .removeClass("hidden").addClass("red")
        .text("Выберите место, позицию и период размещения.");
    }

    const startDate = moment(parts[0], "DD.MM.YYYY").toDate();
    const endDate = moment(parts[1], "DD.MM.YYYY").toDate();
    const userRange = { from: startDate, to: endDate };

    // 2) Проверка точного дубликата в локальных selectedPromos
    const isExactDuplicate = selectedPromos.some(promo =>
      promo.id == selectedPromoId &&
      selectedServiceIds.some(slug => promo.service_ids.includes(slug)) &&
      promo.dates === `с ${moment(startDate).format("DD.MM.YYYY")} по ${moment(endDate).format("DD.MM.YYYY")}`
    );
    if (isExactDuplicate) {
      return $(".popup-footer")
        .removeClass("hidden").addClass("red")
        .text("Идентичные даты в данном разделе уже выбраны вами ранее. Выберите другой раздел или период размещения.");
    }

    for (let slug of selectedServiceIds) {
      for (let promo of selectedPromos) {
        if (!promo.service_ids.includes(slug)) continue;

        const m = promo.dates.match(/с (.+) по (.+)/);
        if (!m) continue;

        const existingFrom = moment(m[1], "DD.MM.YYYY").toDate();
        const existingTo = moment(m[2], "DD.MM.YYYY").toDate();

        if (rangesOverlap(userRange, { from: existingFrom, to: existingTo })) {
          $(".popup-footer")
            .removeClass("hidden").addClass("red")
            .text("Идентичные даты в данном разделе уже выбраны вами ранее. Выберите другой раздел или период размещения.");
          return;
        }
      }
    }

    // 3) Получаем только серверные блокировки из flatpickr
    const calendar = document.querySelector("#promo-date-select")._flatpickr;
    const serverDisabled = (calendar.config.disable || [])
      .filter(d => d.from && d.to)
      .map(d => ({ from: d.from, to: d.to }));

    // 4) Разбиваем userRange на свободные куски по serverDisabled
    const freeChunksBySlug = {};
    selectedServiceIds.forEach(slug => {
      freeChunksBySlug[slug] = subtractRanges(userRange, serverDisabled);
    });

    // 5) Проверяем, что для каждого slug есть хотя бы один свободный кусок
    const allHaveFree = selectedServiceIds.every(slug => freeChunksBySlug[slug].length > 0);
    if (!allHaveFree) {
      return $(".popup-footer")
        .removeClass("hidden").addClass("red")
        .text("Выбранный период пересекается с уже занятыми датами. Выберите другой период.");
    }

    // 6) Очищаем сообщение
    $(".popup-footer").addClass("hidden").removeClass("red").text("");

    // 7) Пушим все свободные куски в selectedPromos
    selectedServiceIds.forEach(slug => {
      const serviceInput = $(`input[name="service_ids[]"][value="${slug}"]`);
      const title = serviceInput.data("name");
      const priceAttr = selectedPromoId == 1 ? "priceBlock1" : "priceBlock2";
      const basePrice = parseFloat(serviceInput.data(priceAttr).toString().replace(/[^\d.-]/g, ""));
      const icon = $('input[name="promo_id"]:checked').closest("label")
        .find("img").attr("src").replace(/(\.[a-z]+)$/i, "-no-background$1");
      const specialistId = $("#popup-specialist-id-promo").val();

      freeChunksBySlug[slug].forEach(chunk => {
        const days = calculateDays(chunk.from, chunk.to);
        const weeks = Math.ceil(days / 7);
        const totalPrice = weeks * basePrice;

        selectedPromos.push({
          id: selectedPromoId,
          title: title,
          dates: `с ${moment(chunk.from).format("DD.MM.YYYY")} по ${moment(chunk.to).format("DD.MM.YYYY")}`,
          icon: icon,
          price: totalPrice,
          specialistId: specialistId,
          service_ids: [slug]
        });
      });
    });

    updateSecondPopup();
    $("#promo-popup").fadeOut(() => $("#promo-popup-2").fadeIn());
  });



  // Обработка нажатия кнопки "Добавить ещё размещение"
  $(document).on("click", ".add-more-promo", function () {
    // Очистка первого попапа
    $('input[name="promo_id"]:checked').prop("checked", false);
    $('input[name="service_ids[]"]:checked').prop("checked", false);
    $("#promo-date-select").val("");

    $("#promo-popup-2").fadeOut(function () {
      $("#promo-popup").fadeIn();
    });
  });

  // Закрытие попапа
  $(document).on("click", ".close, #delete-selected-promo", function () {
    $("#promo-popup-2").fadeOut();
  });

  // Обработка удаления опции
  $(document).on("click", ".delete-selected-promo", function () {
    const index = $(this).data("index");
    selectedPromos.splice(index, 1);
    updateSecondPopup();
  });

  function convertToDbDate(dateStr) {
    const [day, month, year] = dateStr.split('.');
    return `${year}-${month}-${day}`;
  }

  // Обработка подтверждения
  $(document).on('submit', '#promo-confirm-form', function (event) {
    event.preventDefault();

    const paymentOrderId = Date.now();
    $("#js-order-id").val(paymentOrderId);
    $("#js-order-id-ip").val(paymentOrderId);
    $("#js-order-id-legal").val(paymentOrderId);

    // Формируем массив промо с уникальными orderId для БД
    const promoData = selectedPromos.map((promo, index) => {
      const orderId = paymentOrderId + index; // уникальный для каждой промо

      // Применяем скидку, если есть
      let finalPrice = promo.price;
      if (discount > 0) {
        finalPrice = +(finalPrice * (1 - discount / 100)).toFixed(2);
      }

      return {
        promo_id: promo.id,
        service_ids: promo.service_ids,
        start_date: promo.dates.split(' по ')[0].replace('с ', '').trim(),
        end_date: promo.dates.split(' по ')[1].trim(),
        price: finalPrice, // передаём уже со скидкой
        specialist_id: promo.specialistId,
        service_name: promo.title,
        orderId: orderId
      };
    });

    const firstPromo = promoData[0];

    const serviceName = "Размещение карточки в промо-блоках";
    ['#js-service-name', '#js-service-name-ip', '#js-service-name-legal'].forEach(id =>
      $(id).val(serviceName)
    );

    const start_date = convertToDbDate(firstPromo.start_date);
    const end_date = convertToDbDate(firstPromo.end_date);

    // service_id — promo.title
    ['#js-service-id', '#js-service-id-ip', '#js-service-id-legal'].forEach(id =>
      $(id).val(firstPromo.service_name)
    );

    // Даты
    ['#js-date-start', '#js-date-start-ip', '#js-date-start-legal'].forEach(id =>
      $(id).val(start_date)
    );

    ['#js-date-end', '#js-date-end-ip', '#js-date-end-legal'].forEach(id =>
      $(id).val(end_date)
    );

    var oldPrice = selectedPromos.reduce((sum, promo) => sum + promo.price, 0);
    var payAmount = discount > 0
      ? Math.round(oldPrice * (1 - discount / 100))
      : oldPrice;
    $("#js-pay-amount").val(payAmount);
    $("#js-pay-amount-legal").val(payAmount);
    $("#js-pay-amount-ip").val(payAmount);

    console.log('Отправка данных на сервер:', promoData);

    // Отправка данных на сервер
    $.ajax({
      type: 'POST',
      url: '',
      data: { promos: promoData },
      success: function (response) {
        console.log('Ответ сервера:', response);
        // Отображение третьего попапа при успешной обработке
        $('#successful-popup .successful-title').html('Отлично!<br>Вы подключили промо-блоки.');
        $('#successful-popup .middle-text').text('Информация появится в разделе "Продвижение" в течение 60 минут.');
        $("#promo-popup").fadeOut();
        $("#promo-popup-2").fadeOut();
        $("#payment-popup").fadeIn();
        $(".popup_after_text").html("После выбора способа оплаты даты забронируются на 10 минут, успейте завершить бронирование за это время. <br><br>Услуга будет подключена и отобразится на сайте и в личном кабинете в течение 60 минут после оплаты.")
      },
      error: function (xhr, status, error) {
        console.log('Ошибка запроса:', xhr.responseText);
        alert('Произошла ошибка. Попробуйте снова.');
      }
    });
  });
});

document.querySelectorAll(".faq-question").forEach((button) => {
  const faqItem = button.parentElement;
  const faqAnswer = button.nextElementSibling;

  // Устанавливаем базовые стили через JS
  faqAnswer.style.overflow = "hidden";
  faqAnswer.style.maxHeight = "0";
  faqAnswer.style.transition = "max-height 0.3s ease, padding 0.3s ease";

  button.addEventListener("click", () => {
    const isOpen = faqItem.classList.contains("open");

    if (isOpen) {
      button.style.transition = "border-radius 1.1s ease";
      // Закрываем
      const fullHeight = faqAnswer.scrollHeight + "px";
      faqAnswer.style.maxHeight = fullHeight; // нужно зафиксировать перед анимацией

      requestAnimationFrame(() => {
        faqAnswer.style.maxHeight = "0";
        faqAnswer.style.paddingTop = "0";
        faqAnswer.style.paddingBottom = "0";
        button.style.borderRadius = "16px";
      });

      setTimeout(() => {
        faqItem.classList.remove("open");
      }, 100);
    } else {
      button.style.transition = "none";
      // Открываем
      faqItem.classList.add("open");

      const fullHeight = faqAnswer.scrollHeight + "px";
      faqAnswer.style.maxHeight = fullHeight;
      button.style.borderRadius = "16px 16px 0 0";
    }
  });
});

$(document).ready(function () {
  const phoneInputs = document.querySelectorAll('.phone-input');
  const phoneInputsFull = document.querySelectorAll('.phone-input-full');

  const maskOptions = {
    mask: '(000) 000-00-00'
  };
  const maskOptionsFull = {
    mask: '{+7} (000) 000-00-00'
  };

  phoneInputs.forEach(function (input) {
    IMask(input, maskOptions);
  });
  phoneInputsFull.forEach(function (input) {
    IMask(input, maskOptionsFull);
  });
});

document.addEventListener('input', function (e) {
  const el = e.target;

  if (!((el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') && el.hasAttribute('maxlength'))) {
    return;
  }

  const max = Number(el.getAttribute('maxlength'));

  // Мгновенно обрезаем лишнее
  if (el.value.length > max) {
    el.value = el.value.substring(0, max);
  }

  const atMax = el.value.length >= max;
  const errorText = 'Достигнуто максимальное количество символов';

  // 1) inline-feedback
  const inlineFB = el.nextElementSibling;
  if (inlineFB && inlineFB.classList.contains('invalid-feedback')) {
    inlineFB.textContent = errorText; // ← вставляем текст
    el.classList.toggle('is-invalid', atMax);
    inlineFB.style.display = atMax ? 'block' : 'none';
    return;
  }

  // 2) tr + invalid-feedback-tr
  const tr = el.closest('tr');
  if (!tr) return;

  const fbRow = tr.nextElementSibling;
  if (!fbRow || !fbRow.classList.contains('invalid-feedback-tr')) return;

  const rowInputs = tr.querySelectorAll('input[maxlength], textarea[maxlength]');
  const anyAtMax = Array.from(rowInputs).some(i =>
    i.value.length >= Number(i.getAttribute('maxlength'))
  );

  rowInputs.forEach(i => i.classList.toggle('is-invalid', anyAtMax));

  const fb = fbRow.querySelector('.invalid-feedback');
  if (fb) {
    fb.textContent = errorText; // ← вставляем текст
    fb.style.display = anyAtMax ? 'block' : 'none';
  }
});

if (window.innerWidth <= 768) {
  // Блокируем масштабирование пальцами
  document.addEventListener('touchmove', function (e) {
    if (e.touches.length > 1) {
      e.preventDefault();
    }
  }, { passive: false });

  let lastTouchEnd = 0;
  document.addEventListener('touchend', function (e) {
    const now = Date.now();
    if (now - lastTouchEnd <= 300) e.preventDefault(); // блокируем двойной тап
    lastTouchEnd = now;
  }, false);

  // iOS gesture
  document.addEventListener('gesturestart', e => e.preventDefault());
  document.addEventListener('gesturechange', e => e.preventDefault());
  document.addEventListener('gestureend', e => e.preventDefault());
}
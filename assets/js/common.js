$(function () {
  var bannersEl = [];
  let banner1SwiperEl = document.querySelector("#banner1");
  if (banner1SwiperEl) {
    const banner1Swiper = new Swiper("#banner1", {
      navigation: {
        nextEl: "#banner1__next",
        prevEl: "#banner1__prev",
      },
      loop: true,
      autoplay: {
        delay: 4500,
      },
      speed: 600,
      slidesPerView: 1,
    });
    banner1SwiperEl.swiper.autoplay.stop();
    bannersEl.push(banner1SwiperEl);
  }
  let banner2SwiperEl = document.querySelector("#banner2");
  if (banner2SwiperEl) {
    const banner2Swiper = new Swiper("#banner2", {
      navigation: {
        nextEl: "#banner2__next",
        prevEl: "#banner2__prev",
      },
      loop: true,
      autoplay: {
        delay: 4500,
      },
      speed: 600,
      slidesPerView: 1,
    });
    banner2SwiperEl.swiper.autoplay.stop();
    bannersEl.push(banner2SwiperEl);
  }
  let banner3SwiperEl = document.querySelector("#banner3");
  if (banner3SwiperEl) {
    const banner3Swiper = new Swiper("#banner3", {
      navigation: {
        nextEl: "#banner3__next",
        prevEl: "#banner3__prev",
      },
      loop: true,
      autoplay: {
        delay: 4500,
      },
      speed: 600,
      slidesPerView: 1,
    });
    banner3SwiperEl.swiper.autoplay.stop();
    bannersEl.push(banner3SwiperEl);
  }
  let banner4SwiperEl = document.querySelector("#banner4");
  if (banner4SwiperEl) {
    const banner4Swiper = new Swiper("#banner4", {
      navigation: {
        nextEl: "#banner4__next",
        prevEl: "#banner4__prev",
      },
      loop: true,
      autoplay: {
        delay: 4500,
      },
      speed: 600,
      slidesPerView: 1,
    });
    banner4SwiperEl.swiper.autoplay.stop();
    bannersEl.push(banner4SwiperEl);
  }
  let banner5SwiperEl = document.querySelector("#banner5");
  if (banner5SwiperEl) {
    const banner5Swiper = new Swiper("#banner5", {
      navigation: {
        nextEl: "#banner5__next",
        prevEl: "#banner5__prev",
      },
      loop: true,
      autoplay: {
        delay: 4500,
      },
      speed: 600,
      slidesPerView: 1,
    });
    banner5SwiperEl.swiper.autoplay.stop();
    bannersEl.push(banner5SwiperEl);
  }

  $(document).ready(function () {
    var windowHeight = $(window).height();

    $(bannersEl).each(function () {
      var self = $(this);
      if ($(document).scrollTop() < self.offset().top && $(document).scrollTop() + windowHeight / 2 >= self.offset().top) {
        document.querySelector('#' + self.attr('id')).swiper.autoplay.start();
      }
    });

    $(document).on('scroll', function () {
      $(bannersEl).each(function () {
        var self = $(this);
        if ($(document).scrollTop() < self.offset().top && $(document).scrollTop() + windowHeight / 2 >= self.offset().top && !document.querySelector('#' + self.attr('id')).swiper.autoplay.running) {
          document.querySelector('#' + self.attr('id')).swiper.autoplay.start();
        }
      });
    });
  });

  $(".confirmation-sticky .confirmation__button").click(function () {
    $(".popup.confirmation__popup").fadeIn();
    $("body").addClass("hid");
  });
  $("#confirmation .confirmation__close").click(function () {
    $("#confirmation").fadeOut();
  });
  $("#confirmation .confirmation__close").click(function () {
    $("#confirmation").fadeOut();
  });

  $(".company-page .prices button").click(function () {
    if (window.innerWidth > 575) {
      let count = 0;
      if ($(".prices__table").hasClass("opened")) {
        $(".prices__table")
          .find("tr:not(.hidden-price)")
          .each(function () {
            count += $(this).height();
          });
      } else {
        $(".prices__table")
          .find("tr")
          .each(function () {
            count += $(this).height();
          });
      }
      $(".prices__table").css({ height: count + "px" });
      $(".prices__table").toggleClass("opened");
    } else {
      $(".mobile-prices").fadeIn();
      $("body").addClass("hid");
    }
  });

  function setPriceTable() {
    let count = 0;
    $(".prices__table")
      .find("tr:not(.hidden-price)")
      .each(function () {
        count += $(this).height();
      });
    $(".prices__table").css({ height: count + "px" });
  }
  setPriceTable();
  $(window).resize(function () {
    setPriceTable();
  });

  $(".company-page .reviews .buttons button.write-review").click(function () {
    $(".popup.review").fadeIn();
    $("body").addClass("hid");
  });
  $("a.show-reviews, button.show-reviews").click(function () {
    $(".popup-reviews").fadeIn();
    $("body").addClass("hid");
  });
  $(".company-page .ways button").click(function () {
    $(".mobile-ways").fadeIn();
    $("body").addClass("hid");
  });

  $(".company-page .top-info .contacts-button").click(function () {
    var top = $(".contacts-block.mobile-contacts").offset().top;
    if (window.innerWidth > 575) {
      extraHeight = 5;
    } else {
      extraHeight = 104;
    }
    $("body, html").animate({ scrollTop: top - extraHeight }, 800);
  });

  $(".company-page .company-blog-btn").click(function () {
    var top = $(".articles").offset().top;
    if (window.innerWidth > 575) {
      extraHeight = 0;
    } else {
      extraHeight = 48;
    }
    $("body, html").animate({ scrollTop: top - extraHeight }, 800);
  });

  $(".company-page .company-write").click(function () {
    $(".popup.write").fadeIn();
    $("body").addClass("hid");
  });

  $(".company-page .contacts-block ul li.event").click(function () {
    if (
      $(this).hasClass("contacts-tel") ||
      $(this).hasClass("contacts-email")
    ) {
      $(this).addClass("clicked");
    } else {
      $(".company-page .contacts-block ul li.no-sticky").addClass("shown");
      $(this).removeClass("shown");
      $(this).addClass("hidden");
    }
    if (
      $(".company-page .contacts-block ul li.contacts-tel").hasClass(
        "clicked"
      ) &&
      $(".company-page .contacts-block ul li.contacts-email").hasClass(
        "clicked"
      ) &&
      !$(".company-page .contacts-block ul li.other").hasClass("hidden") &&
      !$(".company-page .contacts-block ul li.more").hasClass("hidden")
    ) {
      $(".company-page .contacts-block ul li.more").addClass("shown");
      $(".company-page .contacts-block ul li.other").addClass("hidden");
    }
  });

  function setCity(selectedClass) {
    $(".topbar .city span").removeClass("active");
    $(".topbar .city span.c" + selectedClass).addClass("active");
    $(".mobile-header .city span").removeClass("active");
    $(".mobile-header .city span.c" + selectedClass).addClass("active");
    $(".index-catalog-block .filter .city-link span").removeClass("active");
    $(
      ".index-catalog-block .filter .city-link span.c" + selectedClass
    ).addClass("active");
    $(".popup .window .select-block .m-list").removeClass("active");
    $(".popup .window .select-block .m-list.l" + selectedClass).addClass(
      "active"
    );
  }
  $(".popup.cities .window .select-block .list label").click(function () {
    let selectedClass = $(this).attr("class").slice(-1);
    setCity(selectedClass);
  });
  $(".popup-btn").on("click", function () {
    $(this)
      .parents(".form")
      .children(".field")
      .each(function () {
        let input = $(this).children(".inp");
        if (input.length === 1) {
          input = $(input[0]);
          if (input.val() != "") {
            console.log(32);
            input.removeClass("error");
          } else {
            console.log(33);
            input.addClass("error");
          }
        }
      });
  });
  $("#btn2").on("click", function () {
    $(".popup").fadeOut();
    $(".popup.pass2").fadeIn();
    $("body").addClass("hid");
  });
  $("#category").change(function (e) {
    e.preventDefault();
    $(".index-catalog-block .filter .jq-selectbox.subcat").removeClass(
      "active"
    );
    selClass = this.value;
    $(selClass).addClass("active");
    setTimeout(function () {
      $('.sel.subcat' + selClass).trigger('refresh');
    }, 50);
  });
  $(".popup .bg, .popup .window .close").click(function () {
    $(".popup").fadeOut();
    $("body").removeClass("hid");
    $(".popup .window .reg .step").removeClass("active");
    $(".popup .window .reg .step.s1").addClass("active");
  });


  $(
    ".catalog-checkbox-mobile .checkbox, .favorite-filter .checkbox, .articles-filter .checkbox, .search-filter .checkbox"
  ).click(function () {
    $(this).parents().children(".checkbox").removeClass("active");
    $(this).addClass("active");
  });
  $(".btn__mobile-filters:not(.metro, .main-categories, .blog-theme-filter)").click(function () {
    $(".mobile-subcat-block").addClass("opened");
    $("body").addClass("hid");
  });
  $(".big-mobile-popup .close, .big-mobile-popup ul li").click(function () {
    $(".big-mobile-popup").removeClass("opened");
    $("body").removeClass("hid");
  });

  var blog_mmenu_height = +$('ul.menu-links-mobile.blog-links-mobile').attr('data-themes-count') * 32;
  var blog_mmenu_height_tags = +$('ul.menu-links-mobile.blog-tags-mobile').attr('data-tags-count') * 32;

  $(".journal-filter-button").click(function () {
    $('ul.menu-links-mobile.blog-links-mobile').css('height', '272px');
    $('.big-mobile-popup.mobile-journal-themes .more-article-themes-btn').show();

    $('ul.menu-links-mobile.blog-tags-mobile').css('height', '112px');

    $(".mobile-journal-themes").addClass("opened");
    $("body").addClass("hid");
  });

  $(".big-mobile-popup.mobile-journal-themes .more-article-themes-btn").click(function () {
    $(this).hide(300);
    $('ul.menu-links-mobile.blog-links-mobile').css('height', blog_mmenu_height + 'px');
    $('ul.menu-links-mobile.blog-links-mobile').addClass('opened');
  });

  var activeBottomItem = $(".mobile-bottom-header .bottom-header__item.active");

  function activateCatItemMenu() {
    $(".mobile-menu, .big-mobile-popup").removeClass("opened");
    $(".mobile-menu.m1").addClass("opened");
    $("body").addClass("hid");
    $(".mobile-bottom-header .bottom-header__item").removeClass("active");
    $(".mobile-bottom-header .bottom-header__item#cat-item").addClass("active");
  }

  $(document).ready(function () {
    function activateCatItemMenu() {
      // Сначала плавно скроем меню, если оно открыто
      $(".mobile-menu.m1").removeClass("opened");
      $("body").removeClass("hid");

      // Небольшая задержка для плавности (300ms совпадает с CSS transition)
      setTimeout(() => {
        $(".mobile-menu, .big-mobile-popup").removeClass("opened");
        $(".mobile-menu.m1").addClass("opened");
        $("body").addClass("hid");
        $(".mobile-bottom-header .bottom-header__item").removeClass("active");
        $(".mobile-bottom-header .bottom-header__item#cat-item").addClass("active");
      }, 200);
    }

    // При клике
    $(".mobile-bottom-header .bottom-header__item#cat-item, .btn__mobile-filters.main-categories").click(function () {
      activateCatItemMenu();
    });

    $(".mobile-bottom-header .bottom-header__item:not(#cat-item)").click(function () {
      $(".mobile-menu.m1").removeClass("opened");
      $("body").removeClass("hid");
    });

    // При загрузке страницы — если в URL есть параметр openCatalog=1
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('openCatalog') === '1') {
      // Добавим задержку чтобы меню не "мигало"
      setTimeout(() => {
        activateCatItemMenu();
      }, 100);
    }
  });


  $(".mobile-menu.m2 .close").click(function () {
    $(".mobile-menu.m2").removeClass("opened");
    $("body").removeClass("hid");
  });
  $(".mobile-menu.m1 .close").click(function () {
    $(".mobile-menu.m1").removeClass("opened");
    $("body").removeClass("hid");
    $(".mobile-bottom-header .bottom-header__item").removeClass("active");
    if (activeBottomItem) {
      activeBottomItem.addClass("active");
    }
  });

  $(".mobile-header .menu-button2").click(function () {
    $("body").removeClass("hid");
    $(".mobile-menu, .big-mobile-popup").removeClass("opened");
    $(".mobile-bottom-header .bottom-header__item").removeClass("active");
    if (activeBottomItem) {
      activeBottomItem.addClass("active");
    }
    if (!$(".mobile-menu.m2").hasClass("opened")) {
      $(".mobile-menu.m2").addClass("opened");
      $("body").addClass("hid");
    }
  });
  $("a.feedback").click(function () {
    $(".popup").fadeOut();
    $(".popup.feedback").fadeIn();
    $("body").addClass("hid");
  });
  $("a.pass").click(function () {
    $(".popup").fadeOut();
    $(".popup.pass").fadeIn();
    $("body").addClass("hid");
  });
  $(".btn__reg-footer, button.reg, a.reg").click(function () {
    $(".popup").fadeOut();
    $(".popup.reg").fadeIn();
    $("body").addClass("hid");
  });
  $("a.login, .mobile-menu ul li a.login").click(function () {
    $(".popup").fadeOut();
    $(".popup.login").fadeIn();
    $("body").addClass("hid");
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
  $(".header a.site-menu__btn").click(function () {
    $(".header a.site-menu__btn").toggleClass('opened');
    $(".site-menu-wrapper").slideToggle(500);
    $(".site-menu-bg").fadeToggle(500);
  });

  $(".site-menu-bg, .site-menu-wrapper .close").click(function (event) {
    $(".header a.site-menu__btn").removeClass('opened');
    $(".site-menu-wrapper").slideUp(750);
    $(".site-menu-bg").fadeOut(750);
  });
  $(
    ".index-catalog-block .filter .metro-link, .btn__mobile-filters.metro"
  ).click(function () {
    $(".popup.metro").fadeIn();
    $("body").addClass("hid");
  });
  // Открытие попапа выбора города
  /* $(
    ".mobile-filter-block .city, .index-catalog-block .filter .city-link, .topbar .city, .mobile-header .city"
  ).click(function () {
    $(".popup.cities").fadeIn();
    $("body").addClass("hid");
  }); */
  $(".sel:not(.subcat, .themes-sel)").styler({
    selectSmartPositioning: false,
    onSelectOpened: function () {
      let height = parseInt($(this).children('.jq-selectbox__dropdown').children('ul').css('max-height').slice(0, -2));
      if (height > 248) $(this).children('.jq-selectbox__dropdown').children('ul').css('max-height', '248px');
    }
  });
  $(".sel.subcat").styler({
    selectSmartPositioning: false,
    onSelectOpened: function () {
      let height = parseInt($(this).children('.jq-selectbox__dropdown').children('ul').css('max-height').slice(0, -2));
      if (height > 248) $(this).children('.jq-selectbox__dropdown').children('ul').css('max-height', '248px');
    }
  });
  $(".sel.themes-sel").styler({
    selectSmartPositioning: true,
  });
  $(".sel.tags-sel").styler({
    selectSmartPositioning: true,
  });
  $('select.sel.themes-sel, select.sel.tags-sel').on('change', function () {
    window.location.href = '/journal/' + this.value;
  });
  $(".docs-block .list").slick({
    arrows: true,
    prevArrow: $(".docs-block .slider-arrows .slick-prev"),
    nextArrow: $(".docs-block .slider-arrows .slick-next"),
    speed: 300,
    slidesToShow: 5,
    slidesToScroll: 1,
    responsive: [
      { breakpoint: 1200, settings: { slidesToShow: 4 } },
      { breakpoint: 992, settings: { slidesToShow: 3 } },
      { breakpoint: 768, settings: "unslick" }
    ],
  });
  $(".search-window .search-categories").slick({
    arrows: false,
    variableWidth: true,
    draggable: true,
    touchMove: true,
    swipeToSlide: true,
    infinite: false,
    slidesToShow: 5,
    responsive: [
      {
        breakpoint: 992,
        settings: {
          slidesToShow: 4,
        },
      },
      {
        breakpoint: 900,
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

  $(".show-catalog").on("click", function () {
    $('.mobile-menu.m1.big-mobile-popup').addClass('opened');
  });

  $(".search__wrap input.search__input, .search__wrap .search__btn, .mobile-header .search__btn").on("click", function () {
    $('.search-popup').fadeIn().addClass('opened');
    $("body").addClass("hid");
    $('.search__input').blur();
    $('.search-popup #search-query').focus();
  });
  $(".search-popup .bg, .search-window .close, .search-window .search__find-btn").click(function (event) {
    $(".search-popup").fadeOut().removeClass('opened');
    $("body").removeClass("hid");
  });
  $(".search-window .search__find-btn").click(function (event) {
    let value = $("input#search-query").val();
    if (value != '') {
      $(location).attr('href', '/search?text=' + value);
    }
  });
  $("input#search-query").on("input", function () {
    if ($(this).val() != "") {
      $(".clear-search").addClass("active");
    } else {
      $(".clear-search").removeClass("active");
      $(".search__find-btn").prop("disabled", true);
      $(".search__result").empty();
    }
  });
  $(".clear-search").on("click", function () {
    $("#search-query").val("");
    $(this).removeClass("active");
    $(".search__find-btn").prop("disabled", true);
    $(".search__result").empty();
  });
  $("#registration-popup").on("click", '.recovery_popup_call', function () {
    $(".popup").fadeOut(200);
    setTimeout(function () {
      $(".popup.pass").fadeIn(200);
    }, 200)
  });
  $('body').keyup(function (event) {
    if (event.which == 13 && !$('.search__find-btn').prop('disabled') && $(".search-popup").hasClass('opened')) {
      $('.search__find-btn').click();
    }
  });

  $(window).on('scroll', function () {
    if (window.innerWidth > 1200 && $('.contacts-block:not(.mobile-contacts)').length > 0) {
      if ($(window).scrollTop() > $(".company-page").offset().top && $(window).scrollTop() < $(".company-page").offset().top + $(".company-page").height() - 32 - $('.contacts-block:not(.mobile-contacts)').height()) {
        if (!$('.contacts-block:not(.mobile-contacts)').hasClass('affix')) {
          $('.contacts-block:not(.mobile-contacts)').addClass('affix');
          /* if(window.innerWidth > 1920) { */
          let right_pos = $('body > .wrap').offset().left + 32;
          $('.contacts-block:not(.mobile-contacts)').css('right', right_pos + 'px');
          /* } else {
            $('.contacts-block:not(.mobile-contacts)').css('right', '32px');
          } */
        }
      } else {
        $('.contacts-block:not(.mobile-contacts)').removeClass('affix').css('right', '0');
      }
      $('.contacts-block:not(.mobile-contacts)').removeClass('after-affix');
      if ($(window).scrollTop() >= $(".company-page").offset().top + $(".company-page").height() - 32 - $('.contacts-block:not(.mobile-contacts)').height()) {
        $('.contacts-block:not(.mobile-contacts)').addClass('after-affix');
      }
    } else if ($('.contacts-block:not(.mobile-contacts)').hasClass('affix')) {
      $('.contacts-block:not(.mobile-contacts)').removeClass('affix').css('right', '0');
    }
  })

  $(".company-fav").on("click", function () {
    let favorite = { lawyers: [], experts: [], notaries: [], all: [] };
    let classification_id = parseInt($("#favorite_cat").val());
    let entity_id = parseInt($("#favorite_id").val());
    const lawyers_id = [1, 2, 5, 6, 7];
    const experts_id = [3, 8];
    const notaries_id = [4];

    if (localStorage.getItem("favorite")) {
      favorite = JSON.parse(localStorage.getItem("favorite"));
      if (!favorite.all) favorite.all = [];
    }

    let category = '';
    if (lawyers_id.includes(classification_id)) category = 'lawyers';
    else if (experts_id.includes(classification_id)) category = 'experts';
    else if (notaries_id.includes(classification_id)) category = 'notaries';

    if ($(this).hasClass("active")) {
      // удалить из категории
      const indexCat = favorite[category].indexOf(entity_id);
      if (indexCat !== -1) favorite[category].splice(indexCat, 1);

      // удалить из общего массива
      const indexAll = favorite.all.indexOf(entity_id);
      if (indexAll !== -1) favorite.all.splice(indexAll, 1);
    } else {
      // добавить в категорию
      favorite[category].push(entity_id);

      // добавить в конец общего массива
      favorite.all.push(entity_id);
    }

    localStorage.setItem("favorite", JSON.stringify(favorite));

    $.post("/actions/set_favorite.php", { favorite: favorite, company_page: true })
      .done(function () {
        $(".company-fav").toggleClass("active");
      });
  });


  $('#copy-adress').on("click", function () {
    copyCleanText(this);
  });
});

function copyCleanText(el) {
  const text = el.innerText.trim(); // убираем лишние пробелы/переносы
  const textarea = document.createElement("textarea");
  textarea.value = text;

  // Стили, чтобы не мигал на экране
  textarea.setAttribute('readonly', '');
  textarea.style.position = 'absolute';
  textarea.style.left = '-9999px';

  document.body.appendChild(textarea);
  textarea.select();
  document.execCommand("copy");
  document.body.removeChild(textarea);

}

function setCookie(name, value, days) {
  let expires = "";
  if (days) {
    let date = new Date();
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
    expires = "; expires=" + date.toUTCString();
  }
  document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function getCookie(name) {
  let matches = document.cookie.match(
    new RegExp(
      "(?:^|; )" +
      name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, "\\$1") +
      "=([^;]*)"
    )
  );
  return matches ? decodeURIComponent(matches[1]) : undefined;
}

function checkCookies() {
  let cookieNote = document.getElementById("cookie_note");
  let cookieBtnAccept = cookieNote.querySelector(".cookie_accept");

  // Если куки cookies_policy нет или она просрочена, то показываем уведомление
  if (!getCookie("cookies_policy")) {
    setTimeout(() => {
      $("#cookie_note").show({
        duration: 500,
        start: function () {
          $("#cookie_note").css("display", "flex");
          button = $('.btn__mobile-filters').length ? $('.btn__mobile-filters') : $('.contacts-button');
          button_journal = $('.journal-filter-button').length ? $('.journal-filter-button') : $('.contacts-button');
          if ($(button)) {
            $(button).css({ "transform": "translate(-50%, -52px)" });
          }
          if ($(button_journal)) {
            $(button_journal).css({ "transform": "translate(-50%, -52px)" });
          }
        },
      });
    }, 3000);
  }

  cookieBtnAccept.addEventListener("click", function () {
    setCookie("cookies_policy", "true", 365);
    $("#cookie_note").hide(300);
    button = $('.btn__mobile-filters').length ? $('.btn__mobile-filters') : $('.contacts-button');
    button_journal = $('.journal-filter-button').length ? $('.journal-filter-button') : $('.contacts-button');
    if ($(button)) {
      $(button).css({ "transform": "translateX(-50%)" });
    }
    if ($(button_journal)) {
      $(button_journal).css({ "transform": "translateX(-50%)" });
    }
  });
}

document.addEventListener("DOMContentLoaded", function () {
  checkCookies();
});

function copytext(el) {
  var $tmp = $("<textarea>");
  $("body").append($tmp);
  $tmp.val($(el).text()).select();
  document.execCommand("copy");
  $tmp.remove();
}  
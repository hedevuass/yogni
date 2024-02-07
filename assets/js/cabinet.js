$(function () {
	$(".popup .bg, .popup:not(.delete-confirmation-popup) .window .close, .popup .window .close-popup-btn").click(function () {
		$(".popup").fadeOut();
		$("body").removeClass("hid");
	});
	$(".popup.delete-confirmation-popup .window .close, .popup.delete-confirmation-popup .bg").click(function () {
		$(".popup").fadeOut();
		$("body").removeClass("hid");
		$(location).attr('href', '/actions/account/delete-user.php');
	});
	$('.create-article-popup').on('click', '.close, .bg', function() {
		let link_id = $('.create-article-popup').attr('data-link-id');
		$(location).attr('href', '/cabinet/moy-blog');
	});
	$('.cabinet-popup .popup__close').click(function () {
		$('.cabinet-popup').fadeOut(200);
		$('body').removeClass('hid');
	});
	$('.show-contacs-card').click(function () {
		$('#cabinet-contacts-card-mobile').fadeIn(200);
		$('body').addClass('hid');
	});
	$('.profile-photo-mmenu').click(function () {
		$('#change-photo-mobile').fadeIn(200);
		$('body').addClass('hid');
	});
	$('.delete-account').click(function () {
		$('.delete-account-popup').fadeIn(200);
		$('body').addClass('hid');
	});
	$('#not-delete-btn').click(function () {
		$('.delete-account-popup').fadeOut(200);
		$('body').removeClass('hid');
	});
	$('#delete-account-btn').click(function () {
		$('.delete-account-popup').fadeOut(200);
		$('.delete-confirmation-popup').fadeIn(200);
	});
	$('#not-delete-btn2').click(function () {
		$('.delete-confirmation-popup').fadeOut(200);
		$('.recovered-popup').fadeIn(200);
	});
	$('#delete-confirm-btn').click(function () {
		$('.delete-confirmation-popup').fadeOut(200);
	});
	$('.specialization-quantity').click(function () {
		$('#cabinet-ways').fadeIn(200);
		$('body').addClass('hid');
	});
	$('.edit-info-item').click(function () {
		$(this).toggleClass('opened');
		$(this).siblings('.submenu').slideToggle();
	});
	$('#cabinet-ways.cabinet-popup .confirm-ways-mob').click(function () {
		let selected = $('#cabinet-specialization-mobile input[type=checkbox]:checked').length;
		$('.specialization-quantity').removeClass('active');
		if (selected > 0) {
			$('.specialization-quantity').text('Выбрано ' + selected + ' направлений');
			$('.specialization-quantity').addClass('active');
		} else {
			$('.specialization-quantity').text('Направления не выбраны');
		}
		$('#cabinet-specialization-mobile button[type="submit"]').prop('disabled', false).text('Сохранить информацию');

		$('.cabinet-popup').fadeOut(200);
		$('body').removeClass('hid');
	});
	$('.cabinet-page .cabinet-content .mob-ways-block button, .popup-ways header a').click(function () {
		$('.popup-ways').toggleClass('opened');
	});
	$('.popup-ways ul li a').click(function () {
		$(this).toggleClass('active');
	});
	$('.cabinet-page .cabinet-content .ways-block .select .link').click(function () {
		$(this).toggleClass('active');
		$('.cabinet-page .cabinet-content .ways-block .select ul').toggle();
	});
	$('.cabinet-page .cabinet-content .contacts-block .block .days .day-item label').click(function () {
		$(this).parent().find('.day').toggleClass('holliday');
	});
	$('.cabinet-page .mob-header .menu-button').click(function () {
		$(this).toggleClass('opened');
		$('.cabinet-page .mob-nav2').fadeToggle(200);
		if ($(this).hasClass('opened')) {
			$('.cabinet-page .mob-nav2').scrollTop(0);
		}
		$('body, .cabinet-content').toggleClass('hid');
	});
	$('.company-page .prices button').click(function () {
		$('.popup.prices').fadeIn();
		$('body').addClass('hid');
	});
	$('.company-page .reviews .buttons button:nth-child(1)').click(function () {
		$('.popup.reviews').fadeIn();
		$('body').addClass('hid');
	});
	$('.company-page .reviews .buttons button:nth-child(2)').click(function () {
		$('.popup.review').fadeIn();
		$('body').addClass('hid');
	});
	$('.company-page .ways button').click(function () {
		$('.popup.ways').fadeIn();
		$('body').addClass('hid');
	});
	$('.popup-contacts .bg').click(function () {
		$('.popup-contacts').removeClass('opened');
	});
	$('.company-page .top-info .contacts-button').click(function () {
		$('.popup-contacts').addClass('opened');
	});
	$('.company-page .contacts-block ul li.more a').click(function () {
		$('.company-page .contacts-block ul li.more').hide();
		$('.company-page .contacts-block .is-sticky ul li.no-sticky').show();
	});
	$('.mobile-fixed-menu .filter-link').click(function () {
		$('.mobile-filter-block').addClass('opened');
	});
	$('.mobile-filter-block .close').click(function () {
		$('.mobile-filter-block').removeClass('opened');
	});
	$('.mobile-filter-block .list label.l1').click(function () {
		$('.mobile-filter-block .list').hide();
		$('.mobile-filter-block .list.cat1').show();
	});
	$('.mobile-filter-block .list label.l2').click(function () {
		$('.mobile-filter-block .list').hide();
		$('.mobile-filter-block .list.cat2').show();
	});
	$('.mobile-filter-block .list label.l3').click(function () {
		$('.mobile-filter-block .list').hide();
		$('.mobile-filter-block .list.cat3').show();
	});
	$('.mobile-filter-block .list .sub-item .back-link').click(function () {
		$('.mobile-filter-block .list p').show();
		$('.mobile-filter-block .list .item').show();
		$(this).parent().hide();
	});
	$('.mobile-filter-block .list .item.with_sub label').click(function () {
		$('.mobile-filter-block .list p').hide();
		$('.mobile-filter-block .list .item').hide();
		$(this).parent().next('.sub-item').show();
	});
	$('.popup .window .select-block .list label.l1').click(function () {
		$('.header .city span').removeClass('active');
		$('.header .city span.c1').addClass('active');
		$('.index-catalog-block .filter .city-link span').removeClass('active');
		$('.index-catalog-block .filter .city-link span.c1').addClass('active');
		$('.popup .window .select-block .m-list').removeClass('active');
		$('.popup .window .select-block .m-list.l1').addClass('active');
		$('.mobile-filter-block .city span').removeClass('active');
		$('.mobile-filter-block .city span.c1').addClass('active');
		$('.mobile-filter-block .list .lst').removeClass('active');
		$('.mobile-filter-block .list .lst.l1').addClass('active');
	});
	$('.popup .window .select-block .list label.l2').click(function () {
		$('.header .city span').removeClass('active');
		$('.header .city span.c2').addClass('active');
		$('.index-catalog-block .filter .city-link span').removeClass('active');
		$('.index-catalog-block .filter .city-link span.c2').addClass('active');
		$('.popup .window .select-block .m-list').removeClass('active');
		$('.popup .window .select-block .m-list.l2').addClass('active');
		$('.mobile-filter-block .city span').removeClass('active');
		$('.mobile-filter-block .city span.c2').addClass('active');
		$('.mobile-filter-block .list .lst').removeClass('active');
		$('.mobile-filter-block .list .lst.l2').addClass('active');
	});
	$('.popup .window .select-block .list label.l3').click(function () {
		$('.header .city span').removeClass('active');
		$('.header .city span.c3').addClass('active');
		$('.index-catalog-block .filter .city-link span').removeClass('active');
		$('.index-catalog-block .filter .city-link span.c3').addClass('active');
		$('.popup .window .select-block .m-list').removeClass('active');
		$('.popup .window .select-block .m-list.l3').addClass('active');
		$('.mobile-filter-block .city span').removeClass('active');
		$('.mobile-filter-block .city span.c3').addClass('active');
		$('.mobile-filter-block .list .lst').removeClass('active');
		$('.mobile-filter-block .list .lst.l3').addClass('active');
	});
	$('.popup .window .select-block .list label.l4').click(function () {
		$('.header .city span').removeClass('active');
		$('.header .city span.c4').addClass('active');
		$('.index-catalog-block .filter .city-link span').removeClass('active');
		$('.index-catalog-block .filter .city-link span.c4').addClass('active');
		$('.popup .window .select-block .m-list').removeClass('active');
		$('.popup .window .select-block .m-list.l4').addClass('active');
		$('.mobile-filter-block .city span').removeClass('active');
		$('.mobile-filter-block .city span.c4').addClass('active');
		$('.mobile-filter-block .list .lst').removeClass('active');
		$('.mobile-filter-block .list .lst.l4').addClass('active');
	});
	$('.popup .window .select-block .list label.l5').click(function () {
		$('.header .city span').removeClass('active');
		$('.header .city span.c5').addClass('active');
		$('.index-catalog-block .filter .city-link span').removeClass('active');
		$('.index-catalog-block .filter .city-link span.c5').addClass('active');
		$('.popup .window .select-block .m-list').removeClass('active');
		$('.popup .window .select-block .m-list.l5').addClass('active');
		$('.mobile-filter-block .city span').removeClass('active');
		$('.mobile-filter-block .city span.c5').addClass('active');
		$('.mobile-filter-block .list .lst').removeClass('active');
		$('.mobile-filter-block .list .lst.l5').addClass('active');
	});
	$('.popup .window .select-block .list label.l6').click(function () {
		$('.header .city span').removeClass('active');
		$('.header .city span.c6').addClass('active');
		$('.index-catalog-block .filter .city-link span').removeClass('active');
		$('.index-catalog-block .filter .city-link span.c6').addClass('active');
		$('.popup .window .select-block .m-list').removeClass('active');
		$('.popup .window .select-block .m-list.l6').addClass('active');
		$('.mobile-filter-block .city span').removeClass('active');
		$('.mobile-filter-block .city span.c6').addClass('active');
		$('.mobile-filter-block .list .lst').removeClass('active');
		$('.mobile-filter-block .list .lst.l6').addClass('active');
	});
	$('.popup .window .select-block .list label.l7').click(function () {
		$('.header .city span').removeClass('active');
		$('.header .city span.c7').addClass('active');
		$('.index-catalog-block .filter .city-link span').removeClass('active');
		$('.index-catalog-block .filter .city-link span.c7').addClass('active');
		$('.popup .window .select-block .m-list').removeClass('active');
		$('.popup .window .select-block .m-list.l7').addClass('active');
		$('.mobile-filter-block .city span').removeClass('active');
		$('.mobile-filter-block .city span.c7').addClass('active');
		$('.mobile-filter-block .list .lst').removeClass('active');
		$('.mobile-filter-block .list .lst.l7').addClass('active');
	});
	$('.popup .window .select-block .list label.l8').click(function () {
		$('.header .city span').removeClass('active');
		$('.header .city span.c8').addClass('active');
		$('.index-catalog-block .filter .city-link span').removeClass('active');
		$('.index-catalog-block .filter .city-link span.c8').addClass('active');
		$('.popup .window .select-block .m-list').removeClass('active');
		$('.popup .window .select-block .m-list.l8').addClass('active');
		$('.mobile-filter-block .city span').removeClass('active');
		$('.mobile-filter-block .city span.c8').addClass('active');
		$('.mobile-filter-block .list .lst').removeClass('active');
		$('.mobile-filter-block .list .lst.l8').addClass('active');
	});
	$('#btn1').on('click', function () {
		$('.form1 .inp').each(function () {
			if ($(this).val() != '') {
				console.log(32);
				$(this).removeClass('error');
			}
			else {
				console.log(33);
				$(this).addClass('error');
			}
		});
	});
	$('#btn2').on('click', function () {
		$('.form2 .inp').each(function () {
			if ($(this).val() != '') {
				console.log(32);
				$(this).removeClass('error');
			}
			else {
				console.log(33);
				$(this).addClass('error');
			}
		});
	});
	$('#btn3').on('click', function () {
		$('.form3 .inp').each(function () {
			if ($(this).val() != '') {
				console.log(32);
				$(this).removeClass('error');
			}
			else {
				console.log(33);
				$(this).addClass('error');
			}
		});
	});
	$('#btn4').on('click', function () {
		$('.form4 .inp').each(function () {
			if ($(this).val() != '') {
				console.log(32);
				$(this).removeClass('error');
			}
			else {
				console.log(33);
				$(this).addClass('error');
			}
		});
	});
	$('#btn5').on('click', function () {
		$('.form5 .inp').each(function () {
			if ($(this).val() != '') {
				console.log(32);
				$(this).removeClass('error');
			}
			else {
				console.log(33);
				$(this).addClass('error');
			}
		});
	});
	$('#btn6').on('click', function () {
		$('.form6 .inp').each(function () {
			if ($(this).val() != '') {
				console.log(32);
				$(this).removeClass('error');
			}
			else {
				console.log(33);
				$(this).addClass('error');
			}
		});
	});
	$("#category").change(function (e) {
		e.preventDefault();
		$('.index-catalog-block .filter .jq-selectbox.subcat').removeClass('active');
		$(this.value).addClass('active');
	});
	$('.popup .bg').click(function () {
		$('.popup').fadeOut();
		$('body').removeClass('hid');
		$('.popup .window .reg .step').removeClass('active');
		$('.popup .window .reg .step.s1').addClass('active');
	});
	$('.mobile-menu .close, .mobile-menu ul li a.login, .mobile-menu ul li a.reg, .mobile-menu ul li a.feedback').click(function () {
		$('.mobile-menu').removeClass('opened');
	});
	$('.popup .window .reg .bottom-links a.feedback, .footer ul li a.feedback, .mobile-menu ul li a.feedback').click(function () {
		$('.popup').fadeOut();
		$('.popup.feedback').fadeIn();
		$('body').addClass('hid');
	});
	$('.popup .window .reg .bottom-links a.pass').click(function () {
		$('.popup').fadeOut();
		$('.popup.pass').fadeIn();
		$('body').addClass('hid');
	});
	$('.popup .window .reg .bottom-links a.reg, .footer ul li a.reg, .mobile-menu ul li a.reg').click(function () {
		$('.popup').fadeOut();
		$('.popup.reg').fadeIn();
		$('body').addClass('hid');
	});
	$('.header .user-links a.login, .footer ul li a.login, .mobile-menu ul li a.login').click(function () {
		$('.popup.login').fadeIn();
		$('body').addClass('hid');
	});
	$('.header .user-links a.reg').click(function () {
		$('.popup.reg').fadeIn();
		$('body').addClass('hid');
	});
	$('#cabinet-price td:nth-child(3) input[type="text"], input#popup-price').each(function (){
		digits_int(this);
	})

	$('#cabinet-price').on('input', 'td:nth-child(3) input[type="text"]', function() {
		digits_int(this);
	})
	$('input#popup-price').on('input', function() {
		digits_int(this);
	})
	$('body').on('click', '#show3', function () {
		if ($('#pass3').attr('type') == 'password') {
			$(this).addClass('view');
			$('#pass3').attr('type', 'text');
		} else {
			$(this).removeClass('view');
			$('#pass3').attr('type', 'password');
		}
		return false;
	});
	$('body').on('click', '#show2', function () {
		if ($('#pass2').attr('type') == 'password') {
			$(this).addClass('view');
			$('#pass2').attr('type', 'text');
		} else {
			$(this).removeClass('view');
			$('#pass2').attr('type', 'password');
		}
		return false;
	});
	$('body').on('click', '#show', function () {
		if ($('#pass1').attr('type') == 'password') {
			$(this).addClass('view');
			$('#pass1').attr('type', 'text');
		} else {
			$(this).removeClass('view');
			$('#pass1').attr('type', 'password');
		}
		return false;
	});
	$('.popup .window .reg .step.s3 .select label').click(function () {
		$('.popup .window .reg .step.s3').removeClass('active');
		$('.popup .window .reg .step.s5').addClass('active');
	});
	$('.popup .window .reg .step.s2 .select label').click(function () {
		$('.popup .window .reg .step.s2').removeClass('active');
		$('.popup .window .reg .step.s4').addClass('active');
	});
	$('.popup .window .reg .select label.l1').click(function () {
		$('.popup .window .reg .step.s1').removeClass('active');
		$('.popup .window .reg .step.s2').addClass('active');
	});
	$('.popup .window .reg .select label.l2').click(function () {
		$('.popup .window .reg .step.s1').removeClass('active');
		$('.popup .window .reg .step.s3').addClass('active');
	});
	$('.header .menu1 li a.site-menu').click(function () {
		$('.popup.menu').fadeIn();
		$('body').addClass('hid');
	});
	$('.index-catalog-block .filter .metro-link').click(function () {
		$('.popup.metro').fadeIn();
		$('body').addClass('hid');
	});
	$('.mobile-filter-block .city, .index-catalog-block .filter .city-link, .header .city').click(function () {
		$('.popup.cities').fadeIn();
		$('body').addClass('hid');
	});
	$('.popup .window .close').click(function () {
		$('.popup').fadeOut();
		$('body').removeClass('hid');
	});
	$('.sel').styler();
	$('.docs-block .list').slick({
		arrows: false,
		speed: 300,
		slidesToShow: 5,
		slidesToScroll: 1,
		responsive: [
			{
				breakpoint: 1200,
				settings: {
					slidesToShow: 4
				}
			},
			{
				breakpoint: 992,
				settings: {
					slidesToShow: 3
				}
			},
			{
				breakpoint: 768,
				settings: "unslick"
			}
		]
	});

	//скролл по клику до элемента
	$("body").on('click', '[href*="#"]', function(e){
		var fixed_offset = 30;
		if (window.innerWidth <= 575) {
			fixed_offset = 80;
		}
		$('html,body').stop().animate({ scrollTop: $(this.hash).offset().top - fixed_offset }, 1000);
		e.preventDefault();
	  });
	$(".pro-photo__wrapper").on('click', '.card__buy-btn', function(e){
		let card = $(this).closest('.pro-photo__card');
		let price = $(card).children('.card__body').children('.card__price-block').children('.card__price:not(.old)').children('.price').text();
		price = price.replace(/[^0-9]/g, '') + ' ₽';
		$(".buy-pro-photo .photo-type .title").text($(card).children('.card__title').children('.title').text());
		$(".buy-pro-photo .photo-type ul li.amount").text($(card).children('.card__body').children('.card__descr').children('li:last-child').text());
		$(".buy-pro-photo .photo-type .photo-time").text($(card).children('.card__body').children('.card__descr').children('li:first-child').text());
		$(".buy-pro-photo .photo-price").text(price);
		$(".popup.buy-pro-photo #buy-photo-popup .photo-type").show();
		$(".more-photo-employee .delete-emp").show();
		$(".more-photo-employee").hide().removeClass('added');
		$(".popup.buy-pro-photo #buy-photo-popup .add-photo-emp").show();
		$(".popup.buy-pro-photo #buy-photo-popup .add-company-photo").hide();
		$(".popup.buy-pro-photo #buy-photo-popup .photo-select__wrapper").hide();
		$(".buy-pro-photo").fadeIn(200);
		$("body").addClass('hid');
	});
	$(".employee__bottom-btn").on('click', function(e){
		$(".popup.buy-pro-photo #buy-photo-popup .add-photo-emp").click();
		$(".more-photo-employee .delete-emp").hide();
		$(".popup.buy-pro-photo #buy-photo-popup .photo-type").hide();
		$(".popup.buy-pro-photo #buy-photo-popup .add-company-photo").show();
		$(".popup.buy-pro-photo #buy-photo-popup .photo-select__wrapper").hide();
		$(".popup.buy-pro-photo").fadeIn(200);
		$("body").addClass('hid');
	});

	$("body").on('click', '.pro-photo__mobile-btn', function(e){
		let card = $('.pro-photo__wrapper .pro-photo__card:nth-child(2)');
		let price = $(card).children('.card__body').children('.card__price-block').children('.card__price:not(.old)').children('.price').text();
		price = price.replace(/[^0-9]/g, '') + ' ₽';
		$(".buy-pro-photo .photo-type .title").text($(card).children('.card__title').children('.title').text());
		$(".buy-pro-photo .photo-type ul li.amount").text($(card).children('.card__body').children('.card__descr').children('li:last-child').text());
		$(".buy-pro-photo .photo-type .photo-time").text($(card).children('.card__body').children('.card__descr').children('li:first-child').text());
		$(".buy-pro-photo .photo-price").text(price);
		$(".popup.buy-pro-photo #buy-photo-popup .photo-type").show();
		$(".more-photo-employee .delete-emp").show();
		$(".more-photo-employee").hide().removeClass('added');
		$(".popup.buy-pro-photo #buy-photo-popup .add-photo-emp").show();
		$(".popup.buy-pro-photo #buy-photo-popup .add-company-photo").hide();
		$(".popup.buy-pro-photo #buy-photo-popup .photo-select__wrapper").hide();
		$(".buy-pro-photo").fadeIn(200);
		$("body").addClass('hid');
	});
	$("body").on('click', '.add-photo-emp', function(e){
		$(this).hide();
		$(".more-photo-employee").addClass('added').slideDown(200);
	});
	$("body").on('click', '.add-company-photo', function(e){
		$(this).hide();
		$(".photo-select__wrapper").slideDown({
			start: function () {
			  $(this).css({
				display: "flex"
			  })
			},
			duration: 200
		});
	});
	$(".more-photo-employee").on('click', '.delete-emp', function(e){
		$(".more-photo-employee").removeClass('added').slideUp(200);
		setTimeout(function () {
			$(".add-photo-emp").fadeIn(100);
		}, 200)
		$(".more-photo-employee input.quantity").val('');
	});
	$(".photo-select__wrapper").on('click', '.delete-photo-select', function(e){
		$(".photo-select__wrapper").slideUp(200);
		setTimeout(function () {
			$(".add-company-photo").fadeIn(100);
		}, 200)
	});

	$('#cabinet-contacts .day-item .jq-selectbox__dropdown ul li').on('click', function (e) {
		$('#cabinet-contacts button[type="submit"]').prop('disabled', false).text('Сохранить информацию');
	})
	if (window.innerWidth < 575) {
		$('#cabinet-price tbody input[type="text"], #cabinet-docs tbody input[type="text"], #cabinet-employee tbody input[type="text"]').prop('readonly', true);
		$('#cabinet-docs tbody input[type="file"], #cabinet-docs tr.mob__empty, #cabinet-employee tbody input[type="file"], #cabinet-employee tr.mob__empty').remove();
		$('#cabinet-docs tbody .doc, #cabinet-employee tbody .doc').addClass('loaded');
		$('#cabinet-docs input[name="is_mobile"], #cabinet-employee input[name="is_mobile"]').val('true');
	}
	$('.change-pass-link').on('click', function (e) {
		$('.security-block1').slideUp(200);
		$('.heading__mobile').fadeOut(200);
		setTimeout(function(){
			$('#change-password').slideDown(200);
			if(window.innerWidth < 991) {
				$('.heading__mobile.back').fadeIn({
					duration: 200,
					start: function () {
						$(this).css("display", "flex")
					}
				});
			}
		}, 200)
	})
});

$('.support-block ul .open-popup').on('click', function (e) {
	$('#support-popup textarea').val('').removeClass('error');
	$('#support-popup .invalid-feedback').remove();
	//let type = $(this).attr('data-id');
	//$('#support-popup input#support-type').val(type);
	$('#support-popup .support-type').css('background-image', $(this).css('background-image')).text($(this).text());

	$('.support-write button[type="submit"]').removeClass('open-idea');
	// if(type == 3) {
	if($(this).hasClass('idea')) {
		$('.support-write button[type="submit"]').addClass('open-idea');
	}
	$('.support-write').fadeIn(200);
	$('body').addClass('hid');
})

$('.mob-menu__nav .idea-suggestion').on('click', function (e) {
	$('.support-write-header').fadeIn(200);
	$('body').addClass('hid');
})

$('#support-popup, #support-popup-header').on('click', 'button[type="submit"]', function (e) {
	e.preventDefault();
	let form = $(this).closest('form').attr('id');
	$(`#${form} textarea`).removeClass('error');
	$(`#${form} .invalid-feedback`).remove();
	if ($(`#${form} textarea`).val() == '') {
		$(`#${form} textarea`).addClass('error');
		$(`#${form} textarea`).after(`<div class="invalid-feedback">Заполните данное поле.</div>`);
		return;
	}
	//сохранение обращения/предложения (в админке)
	// $.ajax({
	// 	type: "POST",
	// 	url: "/actions/cabinet/save_support_note.php",
	// 	dataType: 'json',
	// 	data: $(`#support-popup textarea, #support-popup input[type=hidden]`),
	// 	success: function (json) {
	// 		if (json['success']) {
				$('.support-write, .support-write-header').fadeOut(200);
				
				if ($(this).hasClass('open-idea')) {
					setTimeout(function() {
						$('.support-idea-end').fadeIn(200);
					}, 200)
				} else {
					setTimeout(function() {
						$('.support-end').fadeIn(200);
					}, 200)
				}
				
// 			} else {
// 				$('#support-popup button[type="submit"]').after(`<div class="invalid-feedback">Что-то пошло не так. Повторите попытку позже.</div>`)
// 			}
// 		}
// 	})
})

$('#buy-photo-popup').on('click', 'button[type="submit"]', function (e) {
	e.preventDefault();
	let form = $(this).closest('form').attr('id');
	$(`#${form} input[type="text"]`).removeClass('error');
	$(`#${form} .invalid-feedback`).remove();
	if ($(`#${form} input[name="phone"]`).val() == '') {
		$(`#${form} input[name="phone"]`).addClass('error');
		$(`#${form} input[name="phone"]`).after(`<div class="invalid-feedback">Введите номер телефона.</div>`);
		return;
	} else if ($('.more-photo-employee').hasClass('added') && $(`#${form} input[name="emp_quantity"]`).val() == '') {
		$(`#${form} input[name="emp_quantity"]`).addClass('error');
		$(`#${form} input[name="emp_quantity"]`).after(`<div class="invalid-feedback">Введите кол-во сотрудников.</div>`);
		return;
	}
	//сохранение обращения/предложения (в админке)
	// $.ajax({
	// 	type: "POST",
	// 	url: "/actions/cabinet/save_support_note.php",
	// 	dataType: 'json',
	// 	data: $(`#support-popup textarea, #support-popup input[type=hidden]`),
	// 	success: function (json) {
	// 		if (json['success']) {
				$('.buy-pro-photo').fadeOut(200);
				setTimeout(function() {
					$('.photo-end').fadeIn(200);
				}, 200)
				
// 			} else {
// 				$('#support-popup button[type="submit"]').after(`<div class="invalid-feedback">Что-то пошло не так. Повторите попытку позже.</div>`)
// 			}
// 		}
// 	})
})

$('#photo_file').on('change', function (e) {
	if (window.FormData === undefined) {
		alert('В вашем браузере FormData не поддерживается')
	} else {
		e.preventDefault()

		$('.photo__item .invalid-feedback, #change-photo-mobile .popup__body .photo__wrapper .invalid-feedback').remove();
		$('.photo__item .valid-feedback, #change-photo-mobile .popup__body .photo__wrapper .valid-feedback').remove();

		var formData = new FormData();
		formData.append('photo_file', $('#photo_file')[0].files[0]);
		$.ajax({
			type: "POST",
			url: '/actions/cabinet/upload_image.php',
			cache: false,
			contentType: false,
			processData: false,
			data: formData,
			dataType : 'json',
			success: function(json){
				if (json['error']) {
					$(".photo__item, #change-photo-mobile .popup__body .photo__wrapper").append(`<div class="invalid-feedback">${json['error']}</div>`)
				}

				if (json['success']) {
					$(".photo__item, #change-photo-mobile .popup__body .photo__wrapper").append(`<div class="valid-feedback">Фото успешно сохранено.</div>`);
					$(".cabinet-page .cabinet-content .main-info-block .block1 .item .photo__btn").html('<i></i>Изменить').addClass('refresh');
					$("#change-photo-mobile .popup__body .photo__btn").html('<img src="/assets/img/cabinet/refresh-file-mob.svg">Изменить');
					$(".photo__image").css('background-image','url(/uploads/avatars/' + json['success'] + ')').addClass('refresh');
					if($(".index-mob__info-block .photo-block.profile-photo-mmenu .photo").length) {
						$(".index-mob__info-block .photo-block.profile-photo-mmenu .photo").css('background-image','url(/uploads/avatars/' + json['success'] + ')');
					}
				}
			}
		});
	}
})

// Update Main Info
$('#main-info').on('click', 'button[type="submit"]', function (e) {
	e.preventDefault()

	$('input, textarea').removeClass('error');
	$('.item:not(.photo__item) .invalid-feedback').remove();
	$(this).prop('disabled', true).text('Сохранение');

	let form = '#main-info'

	$.ajax({
		type: "POST",
		url: "/actions/cabinet/update_main_info.php",
		dataType: 'json',
		data: $(`${form} input[type=text]:not(.quantity.default), ${form} input[type=hidden], ${form} textarea, ${form} input[type=radio]:checked`),
		success: function (json) {
			if (json['error']) {
				$(this).text('Сохранить информацию')
				if (json['error']['fatal_error']) {
					$(`${form}`).prepend(`<div class="invalid-feedback">${json['error']['fatal_error']}</div>`)
				}

				$.each(json['error'], function (key, value) {
					if (key == 'description') {
						$(`${form} textarea[name=${key}]`).addClass('error')
						$(`${form} textarea[name=${key}]`).siblings('.invalid-feedback').html(value)
						return true
					}

					$(`${form} input[name=${key}]`).addClass('error')
					$(`${form} input[name=${key}]`).after(`<div class="invalid-feedback">${value}</div>`)
				});
			}

			if (json['success']) {
				$('#main-info button[type="submit"]').text("Сохранено");
			}
		}
	})
})

$('#main-info').on('change keyup input', 'input[type=text], input[type=hidden], textarea, input[type=radio]:checked',function (e) {
	$('#main-info button[type="submit"]').prop('disabled', false).text('Сохранить информацию');
})

$('#main-info').on('click', '.add-university', function() {
	let key = +$('.university-inputs input[type=text]').last().attr('data-key') + 1
	
	$('.university-inputs').append(`<input type="text" name="university[${key}]" data-key="${key}" placeholder="Наименование учебного заведения" value="">`);
})

// City Autocomplete
$("input[name=city]").on("keydown", function (event) {
	if (event.keyCode === $.ui.keyCode.TAB &&
		$(this).autocomplete("instance").menu.active) {
		event.preventDefault()
	}
})
	.autocomplete({
		source: function (request, response) {
			$.getJSON("/actions/location/get_cities.php", {
				term: request.term
			}, response)
		},
		search: function () {
			let term = this.value
			if (term.length < 2) return false
		},
		select: function (event, ui) {
			let id = ui.item.id,
				name = ui.item.name

			$('input[name=city_id]').val(id)
		}
	});

$('#cabinet-contacts').on('click', 'button[type="submit"]', function (e) {
	e.preventDefault()

	$(this).prop('disabled', true).text('Сохранение');
	$('input, textarea').removeClass('error')
	$('.invalid-feedback').remove()

	let popup = '#cabinet-contacts'

	$.ajax({
		type: "POST",
		url: "/actions/cabinet/update_contacts.php",
		dataType: 'json',
		data: $(`${popup} input[type=text], ${popup} input[type=hidden], ${popup} textarea, ${popup} input[type=radio]:checked, select, ${popup} input[type=checkbox]:checked`),
		success: function (json) {
			if (json['error']) {
				$(this).text('Сохранить информацию')
				if (json['error']['fatal_error']) {
					$(`${popup}`).prepend(`<div class="invalid-feedback">${json['error']['fatal_error']}</div>`)
				}

				$.each(json['error'], function (key, value) {
					$(`${popup} input[name=${key}]`).addClass('error')
					$(`${popup} input[name=${key}]`).after(`<div class="invalid-feedback">${value}</div>`)
				});
			}

			if (json['success']) {
				$('#cabinet-contacts button[type="submit"]').text("Сохранено");
				$('.cabinet-page .cabinet-content .contacts-block .block .contacts ul').html(json['success']);
				$('.cabinet-popup#cabinet-contacts-card-mobile .popup__body .contacts-card ul').html(json['success']);
			}
		}
	})
})

$('#cabinet-contacts input[type=text], #cabinet-contacts input[type=hidden], #cabinet-contacts textarea, #cabinet-contacts input[type=radio]:checked, #cabinet-contacts input[type=checkbox]').on('change keyup input', function (e) {
	$('#cabinet-contacts button[type="submit"]').prop('disabled', false).text('Сохранить информацию');
})

// Metro Autocomplete
$("input[name=metro]").on("keydown", function (event) {
	if (event.keyCode === $.ui.keyCode.TAB &&
		$(this).autocomplete("instance").menu.active) {
		event.preventDefault()
	}
})
	.autocomplete({
		source: function (request, response) {
			$.getJSON("/actions/location/get_metro.php", {
				term: request.term
			}, response)
		},
		search: function () {
			let term = this.value
			if (term.length < 2) return false
		},
		select: function (event, ui) {
			let id = ui.item.id,
				name = ui.item.name

			$('input[name=metro_id]').val(id)
		}
	});

// Update specialization
$('#cabinet-specialization, #cabinet-specialization-mobile').on('click', 'button[type="submit"]', function(e) {
	e.preventDefault()

	let form = '#' + $(this).parent().attr('id');
	$(this).prop('disabled', true).text('Сохранение');

	$(form+' .invalid-feedback').remove();

	$.ajax({
        type: "POST",
        url: "/actions/cabinet/update_specialization.php",
        dataType: 'json',
        data: $(`${form} input[type=checkbox]:checked`),
        success: function (json) {
			if(json['success']) {
            	$(form + ' button[type="submit"]').text("Сохранено");
			}
			if (json['error']){
				$(this).text('Сохранить информацию')
				$('#cabinet-specialization').append(`<div class="invalid-feedback">${json['error']}</div>`);
			}
        }
    })
})
$('#cabinet-specialization input[type=checkbox].child-checkbox, #cabinet-specialization-mobile input[type=checkbox].child-checkbox').change(function() {
	$(this).prop('checked', $(this).prop('checked') )
	if ($(this).prop('checked') === true) {
		$(this).parent().parent().parent().children('input[type=checkbox]:not(.child-checkbox)').prop('checked', true)
	} else if($(this).parent().siblings().children('input[type=checkbox]:checked').length == 0) {
		let parent_li = $(this).parent().parent().parent();
		parent_li.children('input[type=checkbox]:not(.child-checkbox)').prop('checked', false);
		parent_li.children('p.has-children').toggleClass('checked').removeClass('opened');
		parent_li.children('ul').slideUp(200);
	}
	$('#cabinet-specialization button[type="submit"]').prop('disabled', false).text('Сохранить информацию');
})

$('#cabinet-specialization input[type=checkbox]:not(.child-checkbox), #cabinet-specialization-mobile input[type=checkbox]:not(.child-checkbox)').change(function() {
	if ($(this).prop('checked') === false) {
		$(this).siblings('ul').children('li').children('input[type=checkbox].child-checkbox').prop('checked', false)
		$(this).siblings('ul.child-ul').slideUp(200);
	} else {
		$(this).siblings('ul').children('li').children('input[type=checkbox].child-checkbox').prop('checked', true)
		$(this).siblings('ul.child-ul').slideDown(200);
	}
	$(this).siblings('p.has-children').toggleClass('checked');
	$('#cabinet-specialization button[type="submit"]').prop('disabled', false).text('Сохранить информацию');
})

$('#cabinet-specialization-mobile input[type=checkbox]').change(function() {
	let selected = $('#cabinet-specialization-mobile input[type=checkbox]:checked').length;
	$('.confirm-ways-mob').removeClass('disabled');
	if (selected > 0) {
		$('.confirm-ways-mob').text('Подтвердить (' + selected + ')');
	} else {
		$('.confirm-ways-mob').text('Подтвердить');
	}
})
$('#cabinet-specialization input[type=checkbox]').change(function() {
	let selected = $('#cabinet-specialization input[type=checkbox]:checked').length;
	if (selected > 0) {
		$('#cabinet-specialization .select .link').text('Выбор направлений (' + selected + ')');
	} else {
		$('#cabinet-specialization .select .link').text('Выберите направления');
	}
})

$('.cabinet-page .cabinet-content .ways-block .select > ul li p, .cabinet-page .cabinet-content .mob-ways-block .cabinet-popup .select > ul li p').on('click', function (e) {
	if($(this).hasClass('checked') && $(this).hasClass('has-children')) {
		$(this).toggleClass('opened');
		$(this).siblings('ul.child-ul').slideToggle(200);
	}
})

$('#cabinet-price').on('click', '.add', function() {
	let key = +$('tbody .checkbox').last().attr('data-key') + 1

	$('tbody').append(`
		<tr>
			<td>
				<div class="links"><a></a><a></a></div>
			</td>
			<td><input type="text" name="name[${key}]" placeholder="Услуга"></td>
			<td><input type="text" name="price[${key}]" placeholder="Цена"></td>
			<td><input type="checkbox" name="of[${key}]" class="checkbox" data-key="${key}" id="checkbox${key}"><label for="checkbox${key}"></label></td>
			<td><a class="edit-price-mobile"></a><a class="delete"></a></td>
		</tr>
	`)
})

$('#cabinet-price').on('change keyup input', 'input[type="text"]', function() {
	$(this).parent().parent().children('td:last-child').children('.delete').removeClass('disabled');
})

$('#cabinet-price').on('change keyup input', 'input[type="text"], input[type="checkbox"]', function() {
	$('#cabinet-price .bottom-button').prop('disabled', false).text("Сохранить информацию");
})

function digits_int(target){
	let val = $(target).val().replace(/[^0-9]/g, '');
	val = val.replace(/\B(?=(\d{3})+(?!\d))/g, '\'');
	$(target).val(val);
	console.log($(target).val())
}

$('#cabinet-price').on('click', '.delete', function() {
	$(this).closest('tr').remove()
	$('#cabinet-price .bottom-button').prop('disabled', false).text("Сохранить информацию");
})

$('#cabinet-price').on('click', '.bottom-button', function(e) {
	e.preventDefault()
	$(this).prop('disabled', true).text('Сохранение');

	let form = '#cabinet-price';
	$('.invalid-feedback').remove();

	$.ajax({
        type: "POST",
        url: "/actions/cabinet/update_price.php",
        dataType: 'json',
        data: $(`${form} input[type=text], ${form} input[type=checkbox]:checked`),
        success: function (json) {
			if (json['error']) {
				$(this).text('Сохранить информацию');
				$.each(json['error'], function (key, value) {
					$(`${form} input[name="${key}"]`).addClass('error')
					$(`${form} input[name="${key}"]`).after(`<div class="invalid-feedback">${value}</div>`)
				});
			}

			if (json['success']) {
				$('#cabinet-price button[type="submit"]').prop('disabled', true).text("Сохранено");
			}
        }
    })
})

$('#cabinet-price').on('click', '.mob-add', function() {
	$('#popup-price, #popup-price-name').val('');
	$('#popup-price-from').prop('checked', false);
	$('#popup-price-id').val('');
	$('#cabinet-prices-popup').fadeIn(200);
	$('body').addClass('hid');
})

$('#cabinet-price').on('click', '.edit-price-mobile, input[type=text]', function() {
	if (window.innerWidth < 575) {
		let info = $(this).parent().parent().children('td:first-child').children('.links').children('a');
		let name = info.attr('data-name');
		let price = info.attr('data-price');
		let from = info.attr('data-of') == '1' ? true : false;
		let id = info.attr('data-id');
		$('#popup-price-name').val(name);
		$('#popup-price').val(price);
		digits_int('#popup-price');
		$('#popup-price-from').prop('checked', from);
		$('#popup-price-from').prop('checked', from);
		$('#popup-price-id').val(id);
		$('#cabinet-prices-popup').fadeIn(200);
		$('body').addClass('hid');
	}
})

$('#cabinet-prices-popup .mob-prices-btn').on('click', function() {

	let name = $('#popup-price-name').val();
	let price = $('#popup-price').val();
	let from_bool = $('#popup-price-from').prop('checked');
	let from = from_bool ? '1' : '0';
	let from_checked = from_bool ? 'checked' : '';
	let id = $('#popup-price-id').val();
	
	if (id == '') {
		let new_key = 0;
		while ($('#cabinet-price tr td input[name="price['+new_key+']"]').length != 0) {
			new_key++;
		}
		let new_id = 0;
		while ($('#cabinet-price tr td:first-child .links a[data-id="'+new_id+'"]').length != 0) {
			new_id++;
		}
		$('#cabinet-price table tbody').append(`
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
		let elem = $('#cabinet-price tr td:first-child .links a[data-id="'+id+'"]');
		elem.attr('data-name', name);
		elem.attr('data-price', price);
		elem.attr('data-of', from);
		let tr = elem.parent().parent().parent();
		console.log(tr.children('input[type="text"]'));
		tr.children('td:nth-child(2)').children('input[type="text"]').val(name);
		tr.children('td:nth-child(3)').children('input[type="text"]').val(price);
		tr.children().children('input[type="checkbox"]').prop('checked', from_bool);
	}
	$('.cabinet-page .cabinet-content .prices-block .bottom-button').prop('disabled', false).text('Сохранить информацию');
	$('#cabinet-prices-popup').fadeOut(200);
	$('body').removeClass('hid');
})

$('#cabinet-docs').on('click', '.mob-add', function(e) {
	let key = 0;
	if ($('#cabinet-docs tbody tr').length !== 0) {
		key = +$('.docs tr td input[type=text]').last().attr('data-key') + 1;
	}
	$('#popup-docs-name').val('');
	$('#popup-doc-key').val(key);
	$('#popup-doc-file').val('0');
	$('#cabinet-docs-popup').append(`<input type="file" name="${key}" hidden>`);
	$('.docs-load a, .docs-load .doc').remove();
	$('.docs-load .popup-delete-doc').removeClass('showed').removeClass('editing');
	$('.docs-load').prepend(`<a class="upload" onclick="$('#cabinet-docs-popup input[name=${key}]').click()">Загрузить</a>`);
	$('#cabinet-docs-popup').fadeIn(200);
	$('body').addClass('hid');
})

$('#cabinet-docs').on('click', 'tbody a:not(.delete), tbody input[type="text"]', function(e) {
	if(window.innerWidth < 575) {
		let key = $(this).closest('tr').children('td').children('input[type="text"]').attr('data-key');
		let name = $(this).closest('tr').children('td').children('input[type="text"]').val();
		$('.docs-load').html(`<div class="doc">
				Загружен
			</div>
		<div class="popup-delete-doc showed editing"></div>`);
		$('#popup-docs-name').val(name);
		$('#popup-doc-key').val(key);
		$('#popup-doc-file').val('1');
		$('#cabinet-docs-popup').fadeIn(200);
		$('body').addClass('hid');
	}
})
$('#cabinet-docs-popup').on('click', '.popup__close', function(e) {
	let key = $('#popup-doc-key').val();
	$('#cabinet-docs-popup input[name="'+key+'"]').remove();
})

$('#cabinet-docs-popup').on('click', '.mob-docs-btn', function(e) {
	$('#cabinet-docs-popup .invalid-feedback').remove();
	let key = $('#popup-doc-key').val();
	let name = $('#popup-docs-name').val();
	let file = $('#popup-doc-file').val();
	if (name == '') {
		$('#popup-docs-name').after(`<div class="invalid-feedback">Введите наименование документа</div>`);
	} else if (file == '0') {
		$('.docs-load').append(`<div class="invalid-feedback">Загрузите файл</div>`);
	} else {
		if ($('#cabinet-docs tr td input[name="name['+key+']"]').length == 1) {
			$('#cabinet-docs tr td input[name="name['+key+']"]').val(name);
		} else {
			$('#cabinet-docs tbody').append(`
			<tr>
				<td><input type="text" name="name[${key}]" data-key="${key}" placeholder="Наименование документа" value="${name}"></td>
				<td>
					<div class="doc loaded"></div>
				</td>
				<td><a class="edit-docs-mobile"></a><a class="delete"></a></td>
			</tr>
		`)
		}
		$('#cabinet-docs-popup').fadeOut(200);
		$('body').removeClass('hid');
		$('#cabinet-docs button[type="submit"]').prop('disabled', false).text("Сохранить информацию");
	}
})

$('#cabinet-docs-popup').on('click', '.popup-delete-doc', function(e) {
	let key = $('#popup-doc-key').val();
	$('#cabinet-docs-popup #popup-doc-file').val('0');
	$(this).siblings('.doc, a').remove();
	if($(this).hasClass('editing') && $(`#cabinet-docs-popup input[name=${key}]`).length == 0) {
		$('#cabinet-docs-popup').append(`<input type="file" name="${key}" hidden>`);
	} else {
		$(`#cabinet-docs-popup input[name=${key}]`).val('');
	}
	$(this).removeClass('showed').removeClass('editing');
	$(this).closest('.docs-load').prepend(`<a class="upload" onclick="$('#cabinet-docs-popup input[name=${key}]').click()">Загрузить</a>`);
})

$('#cabinet-docs-popup').on('change', 'input[type=file]', function(e) {
	console.log(e.target.files)
	let file = e.target.files[0]

	if(!file || !file.type.match('image')) return

	$('#cabinet-docs-popup #popup-doc-file').val('1');

	$('.docs-load').prepend(`
		<div class="doc">
			Загружен
		</div>
	`)
	$('.popup-delete-doc').addClass('showed');

	$('.docs-load a').remove();
})

$('#cabinet-docs table').on('change', 'input[type=file]', function(e) {
	console.log(e.target.files)
	let file = e.target.files[0]

	if(!file || !file.type.match('image')) return

	$(this).closest('td').append(`
		<div class="doc">
			<a>${file.name}</a>
		</div>
	`)

	$(this).siblings('a').remove()
})
$('#cabinet-docs').on('input change', 'input[type=text], input[type=file]', function(e) {
	$(this).parent().siblings('td').children('.delete').removeClass('disabled');
	$('#cabinet-docs button[type="submit"]').prop('disabled', false).text("Сохранить информацию");
})

$('#cabinet-docs').on('click', '.add', function() {
	let key = +$('tbody input[type=text]').last().attr('data-key') + 1

	$('tbody').append(`
		<tr>
			<td><input type="text" name="name[${key}]" data-key="${key}" placeholder="Наименование документа"></td>
			<td>
				<a class="upload" onclick="$(this).siblings('input').click()">Загрузить</a>
				<input type="file" name="${key}" hidden>
			</td>
			<td><a class="edit-docs-mobile"></a><a class="delete disabled"></a></td>
		</tr>
	`)
})

$('#cabinet-docs').on('click', '.delete', function() {
	$(this).closest('tr').remove()
	$('#cabinet-docs .refresh button').prop('disabled', false).text("Сохранить информацию");
})

$('#cabinet-docs').on('click', 'button[type="submit"]', function(e) {
	e.preventDefault()
	$('input, textarea').removeClass('error')
	$('.invalid-feedback').remove()
	
	let formData = new FormData(),
		form = '#cabinet-docs',
		names = $(`${form} input[type=text]`),
		files = $(`${form} input[type=file]`)

	$.each(names, function() {
		formData.append($(this).attr('name'), $(this).val())
	})

	$.each(files, function() {
		formData.append($(this).attr('name'), $(this).prop('files')[0])
	})
	formData.append($('#is_mobile').attr('name'), $('#is_mobile').val())

	$(this).prop('disabled', true).text('Сохранение');

	$.ajax({
        type: "POST",
        url: "/actions/cabinet/update_files.php",
        dataType: 'json',
        data: formData,
        contentType: false,
        processData: false,
        success: function (json) {
            if (json['error']) {
				$(this).text('Сохранить информацию')
				if (json['error']['fatal_error']) {
					$(`${form}`).prepend(`<div class="invalid-feedback">${json['error']['fatal_error']}</div>`)
				}

				$.each(json['error'], function (key, value) {
					$(`${form} input[data-key=${key}]`).addClass('error')
					$(`${form} input[data-key=${key}]`).after(`<div class="invalid-feedback">${value}</div>`)
				});
			}

			if (json['success']) {
				$('#cabinet-docs button[type="submit"]').text("Сохранено");
			}
        }
    })
})
var files_count = 0;

$('#cabinet-employee table').on('change', 'input[type=file]', function(e) {
	let key = $(this).closest('tr').children('td:first-child').children('input[type="text"]').attr('data-key');

	let file = e.target.files[0]

	if(!file || !file.type.match('image')) return

	$(this).closest('td').append(`
		<div class="doc">
			<a>${file.name}</a>
		</div>
	`);
	files_count++;

	$(this).siblings('a').remove()
})
$('#cabinet-employee').on('input change', 'input[type=text], input[type=file]', function(e) {
	$(this).parent().siblings('td').children('.delete').removeClass('disabled');
	$('#cabinet-employee button[type="submit"]').prop('disabled', false).text("Сохранить информацию");
})

$('#cabinet-employee').on('click', '.add', function() {
	let key = +$('tbody input[type=text]').last().attr('data-key') + 1

	$('tbody').append(`
	<tr>
		<td><div class="employee-info-mobile"><input type="text" readonly></div><input type="text" name="name[${key}]" data-key="{${key}}" placeholder="Имя"></td>
		<td><input type="text" name="lastname[${key}]" placeholder="Фамилия"></td>
		<td><input type="text" name="post[${key}]" placeholder="Должность"></td>
		<td>
			<a class="upload" onclick="$(this).siblings('input').click()">Загрузить</a>
			<input type="file" name="{${key}}" hidden>
		</td>
		<td><a class="edit-employee-mobile"></a><a class="delete disabled"></a></td>
	</tr>
	`)
})

$('#cabinet-employee').on('click', '.mob-add', function(e) {
	$('input').removeClass('error')
	$('.invalid-feedback').remove()
	let key = 0;
	if ($('#cabinet-employee tbody tr').length !== 0) {
		key = +$('.employee tr td:first-child > input[type=text]').last().attr('data-key') + 1;
	}
	$('#cabinet-employee-popup .popup-employee-photo').addClass('empty');
	$('#cabinet-employee-popup .employee-photo-btn').html('<i><img src="/assets/img/cabinet/employee-upload.svg"></i>Загрузить');
	$('#popup-employee-name').val('');
	$('#popup-employee-lastname').val('');
	$('#popup-employee-post').val('');
	$('#popup-emp-key').val(key);
	$('#popup-emp-file').val('0');
	$('#cabinet-employee-popup').append(`<input type="file" name="${key}" hidden>`);
	$('#cabinet-employee-popup').fadeIn(200);
	$('body').addClass('hid');
})

$('#cabinet-employee').on('click', '.employee-info-mobile, .edit-employee-mobile, .doc', function(e) {
	$('input').removeClass('error')
	$('.invalid-feedback').remove()
	let key = $(this).closest('tr').children('td:first-child').children('input[type=text]').attr('data-key');
	let name = $(this).closest('tr').children('td:first-child').children('input[type=text]').val();
	let lastname = $(this).closest('tr').children('td:nth-child(2)').children('input[type=text]').val();
	let post = $(this).closest('tr').children('td:nth-child(3)').children('input[type=text]').val();
	let image_url = $(this).closest('tr').children('td:nth-child(4)').children('.doc').children('a').attr('href');
	$('#cabinet-employee-popup .popup-employee-photo').removeClass('empty').css('background-image', 'url('+image_url+')');
	$('#cabinet-employee-popup .employee-photo-btn').html('<i><img src="/assets/img/cabinet/employee-change.svg"></i>Изменить');
	$('#popup-employee-name').val(name);
	$('#popup-employee-lastname').val(lastname);
	$('#popup-employee-post').val(post);
	$('#popup-emp-key').val(key);
	$('#popup-emp-file').val('1');
	$('#cabinet-employee-popup').fadeIn(200);
	$('body').addClass('hid');
})

$('#cabinet-employee-popup').on('click', '.employee-photo-btn', function(e) {
	let key = $('#popup-emp-key').val();
	if ($(`#cabinet-employee-popup input[name=${key}]`).length === 0) {
		$('#cabinet-employee-popup').append(`<input type="file" name="${key}" hidden>`);
	}
	$(`#cabinet-employee-popup input[name=${key}]`).click();
})

$('#cabinet-employee-popup').on('click', '.popup__close', function(e) {
	let key = $('#popup-emp-key').val();
	$(`#cabinet-employee-popup input[name=${key}]`).remove();
	$('#popup-emp-filename').val('');
})

$('#cabinet-employee-popup').on('change', 'input[type=file]', function(e) {
	console.log(e.target.files)
	let file = e.target.files[0]

	if(!file || !file.type.match('image')) return
	$('.popup-employee-photo + .invalid-feedback').remove();
	$('#popup-emp-file').val('0');
	var formData = new FormData();
	formData.append('photo_file', e.target.files[0]);
	$.ajax({
        type: "POST",
        url: "/actions/cabinet/upload_image_emp.php",
        dataType: 'json',
        data: formData,
        contentType: false,
        processData: false,
        success: function (json) {
            if (json['error']) {
				$('.popup-employee-photo').after(`<div class="invalid-feedback">${json['error']}</div>`)
			}
			if (json['success']) {
				$('#popup-emp-file').val('1');
				$('#popup-emp-filename').val(json['success']);
				$('#cabinet-employee-popup .popup-employee-photo').removeClass('empty').css('background-image', 'url(/uploads/employee/'+json['success']+')');
				$('#cabinet-employee-popup .employee-photo-btn').html('<i><img src="/assets/img/cabinet/employee-change.svg"></i>Изменить');
			}
        }
    })

})

$('#cabinet-employee-popup').on('click', '.mob-employee-btn', function() {
	$('#cabinet-employee-popup .invalid-feedback').remove();
	$('#cabinet-employee-popup input').removeClass('error');
	let has_error = false;
	let name = $('#popup-employee-name').val();
	let lastname = $('#popup-employee-lastname').val();
	let post = $('#popup-employee-post').val();
	let key = $('#popup-emp-key').val();
	let img_name = $('#popup-emp-filename').val();
	if(name == "") {
		$('#popup-employee-name').addClass('error').after(`<div class="invalid-feedback">Введите имя сотрудника</div>`);
		has_error = true;
	}
	if(lastname == "") {
		$('#popup-employee-lastname').addClass('error').after(`<div class="invalid-feedback">Введите фамилию сотрудника</div>`);
		has_error = true;
	}
	if(post == "") {
		$('#popup-employee-post').addClass('error').after(`<div class="invalid-feedback">Введите должность сотрудника</div>`);
		has_error = true;
	}
	if($('#popup-emp-file').val() == "0") {
		$('.popup-employee-photo').addClass('error').after(`<div class="invalid-feedback">Загрузите подходящее по правилам фото сотрудника</div>`);
		has_error = true;
	}
	if(!has_error) {
		if($(`#cabinet-employee tbody tr td:first-child > input[data-key="${key}"]`).length === 0) {
			$('#cabinet-employee tbody').append(`
				<tr>
					<td><div class="employee-info-mobile"><input type="text" value="${name} ${lastname}, ${post}" readonly></div><input type="text" name="name[${key}]" data-key="{${key}}" placeholder="Имя" value="${name}"></td>
					<td><input type="text" name="lastname[${key}]" placeholder="Фамилия" value="${lastname}"></td>
					<td><input type="text" name="post[${key}]" placeholder="Должность" value="${post}"></td>
					<td>
						<input type="hidden" name="mobile_loaded[${key}]" value="${img_name}">
					</td>
					<td><a class="edit-employee-mobile"></a><a class="delete"></a></td>
				</tr>
			`)
		} else {
			$(`#cabinet-employee tbody tr td:first-child > input[data-key="${key}"]`).val(name);
			$(`#cabinet-employee tbody tr td:first-child:has(input[data-key="${key}"]) .employee-info-mobile input`).val(name + ' ' + lastname + ', ' + post);
			$(`input[name="lastname[${key}]"]`).val(lastname);
			$(`input[name="post[${key}]"]`).val(post);
			$(`input[name="post[${key}]"]`).closest('tr').children('td:nth-child(4)').append(`<input type="hidden" name="mobile_loaded[${key}]" value="${img_name}">`)
		}
		$('#popup-emp-filename').val('');
		$('#cabinet-employee-popup').fadeOut(200);
		$('body').removeClass('hid');
	}
})

$('#cabinet-employee').on('click', '.delete', function() {
	$(this).closest('tr').remove()
	$('#cabinet-employee .refresh button').prop('disabled', false).text("Сохранить информацию");
})

$('#cabinet-employee').on('click', 'button[type="submit"]', function(e) {
	e.preventDefault()
	$('input, textarea').removeClass('error')
	$('.invalid-feedback').remove()
	
	let formData = new FormData(),
		form = '#cabinet-employee',
		names = $(`${form} td:first-child input[type=text]`),
		lastnames = $(`${form} td:nth-child(2) input[type=text]`),
		posts = $(`${form} td:nth-child(3) input[type=text]`),
		files = $(`${form} input[type=file]`),
		filenames = $(`${form} td:nth-child(3) input[type=hidden]`),
		company_id = $(`${form} input[name="company_id"]`),
		is_mobile = $(`${form} input[name="is_mobile"]`)

	$.each(names, function() {
		formData.append($(this).attr('name'), $(this).val())
	})

	$.each(lastnames, function() {
		formData.append($(this).attr('name'), $(this).val())
	})

	$.each(posts, function() {
		formData.append($(this).attr('name'), $(this).val())
	})
	$.each(files, function() {
		formData.append($(this).attr('name'), $(this).prop('files')[0])
	})
	$.each(filenames, function() {
		formData.append($(this).attr('name'), $(this).val())
	})
	formData.append($(company_id).attr('name'), $(company_id).val())
	formData.append($(is_mobile).attr('name'), $(is_mobile).val())

	$(this).prop('disabled', true).text('Сохранение');

	$.ajax({
        type: "POST",
        url: "/actions/cabinet/update_employee.php",
        dataType: 'json',
        data: formData,
        contentType: false,
        processData: false,
        success: function (json) {
            if (json['error']) {
				$(this).text('Сохранить информацию')
				if (json['error']['fatal_error']) {
					$(`${form}`).prepend(`<div class="invalid-feedback">${json['error']['fatal_error']}</div>`)
				}

				$.each(json['error'], function (key, value) {
					$(`${form} input[data-key=${key}]`).addClass('error')
					$(`${form} input[data-key=${key}]`).closest('tr').after(`<div class="invalid-feedback">${value}</div>`)
				});
			}

			if (json['success']) {
				$('#cabinet-employee button[type="submit"]').text("Сохранено");
			}
        }
    })
})

$('#create-note-form').on('click', 'button[type="submit"]', function(e) {
	e.preventDefault()
	$('input, textarea').removeClass('error')
	$('.invalid-feedback').remove()

	let formData = new FormData(),
		inputs = $(`#create-note-form input[type=text]`),
		blog_id = $(`#create-note-form input[name="blog_id"]`),
		is_edit = $(`#create-note-form input[name="is_edit"]`),
		content = $(`#create-note-form textarea`),
		theme = $(`#create-note-form select`)

	$.each(inputs, function() {
		formData.append($(this).attr('name'), $(this).val())
	})

	formData.append($(blog_id).attr('name'), $(blog_id).val())
	formData.append($(is_edit).attr('name'), $(is_edit).val())
	formData.append($(content).attr('name'), $(content).val())
	formData.append($(theme).attr('name'), $(theme).val())
	
	$.ajax({
        type: "POST",
        url: "/actions/cabinet/new_blog_note.php",
        dataType: 'json',
        data: formData,
        contentType: false,
        processData: false,
        success: function (json) {
            if (json['error']) {
				$.each(json['error'], function (key, value) {
					if (key == 'title') {
						$(`#create-note-form input[name=${key}]`).addClass('error')
						$(`#create-note-form input[name=${key}]`).after(`<div class="invalid-feedback">${value}</div>`)
					} else if (key == 'content') {
						$(`#create-note-form textarea`).addClass('error')
						$(`#create-note-form textarea`).after(`<div class="invalid-feedback">${value}</div>`)
					} else if (key == 'keywords') {
						$(`#create-note-form .keywords`).addClass('error')
						$(`#create-note-form .keywords`).after(`<div class="invalid-feedback">${value}</div>`)
					} else if (key == 'theme'){
						if (window.innerWidth < 575) {
							$(`#create-note-form .mobile-theme-btn`).addClass('error')
							$(`#create-note-form .mobile-theme-btn`).after(`<div class="invalid-feedback">${value}</div>`)
						} else {
							$(`#create-note-form .jq-selectbox`).addClass('error')
							$(`#create-note-form .jq-selectbox`).after(`<div class="invalid-feedback">${value}</div>`)
						}
					}
				});
			}

			if (json['success']) {
				$('.create-article-popup').attr('data-link-id', json['success']).fadeIn(200);
				$('body').addClass('hid');
			}
        }
    })
});

$('.delete-theme').on('click', function(e) {
	e.stopPropagation();
	$('.mobile-theme-btn .theme-btn-text').text('Раздел не выбран');
	$('.mobile-theme-btn').addClass('not-chosen');
	$('#blog_theme').val('0');
});

$('.mobile-theme-btn').on('click', function(e) {
	$('#article-popup-service').fadeIn(200);
	$('body').addClass('hid');
});

$('#article-popup-service').on('click', 'li', function(e) {
	$('.mobile-theme-btn').removeClass('not-chosen');
	$('.mobile-theme-btn .theme-btn-text').text($(this).text());
	$('#blog_theme').val($(this).attr('data-art-id'));
	$('#article-popup-service').fadeOut(200);
	$('body').removeClass('hid');
});

$('.pro-photo__questions').on('click', '.question', function() {
	$(this).siblings('.answer').slideToggle(200);
	$(this).toggleClass('opened');
});

$('.articles__wrapper').on('click', '.article-menu-btn', function() {
	$(this).children('.article-menu').fadeToggle(200);
	$(this).toggleClass('opened');
});
$('.articles__wrapper').on('click', '.more-articles', function() {
	let start = $('.articles__wrapper input[name="start"]').val();
	console.log(start);
	let formData = new FormData();
	formData.append('start', start)
	$.ajax({
        type: "POST",
        url: "/actions/cabinet/more_articles.php",
        dataType: 'json',
        data: formData,
        contentType: false,
        processData: false,
        success: function (json) {
			if (json['articles']) {
				$('.articles__wrapper .articles__block').append(json['articles']);
				$('.articles__wrapper input[name="start"]').val(parseInt(start) + 5);
			}
			if(json['no_more']){
				$('.articles__wrapper .more-articles').prop('disabled', true);
			}
        }
    })
});
$('.articles__wrapper').on('click', '.menu-point.delete', function() {
	let article_id = $(this).attr('data-id');
	$('#delete-article-btn').attr('data-article', article_id);
	$('.delete-article').fadeIn(200);
	$('body').addClass('hid');
});
$('.delete-article').on('click', '.button-new', function() {
	$('.delete-article').fadeOut(200);
	$('body').removeClass('hid');
});

$('.delete-article').on('click', '#delete-article-btn', function() {
	let article_id = $(this).attr('data-article');
	let blog_id = $('.delete-article input[name="blog_id"]').val();
	let formData = new FormData();
	formData.append('article_id', article_id)
	formData.append('blog_id', blog_id)
	$.ajax({
        type: "POST",
        url: "/actions/cabinet/delete_article.php",
        dataType: 'json',
        data: formData,
        contentType: false,
        processData: false,
        success: function (json) {
			if (json['success']) {
				location.reload();
			}
        }
    })
});

$(document).mouseup( function(e){ // событие клика по веб-документу
	var div = $( ".article-menu, .article-menu-btn" ); // тут указываем ID элемента
	if ( !div.is(e.target) // если клик был не по нашему блоку
		&& div.has(e.target).length === 0 ) { // и не по его дочерним элементам
		$(".article-menu").fadeOut(200); // скрываем его
		$( ".article-menu-btn" ).removeClass('opened');
	}
});


// Убавляем кол-во по клику
$('.quantity_inner .bt_minus').click(function() {
    let $input = $(this).parent().find('.quantity');
	if($input.val() == '') $input.val('1');
    let count = parseInt($input.val()) - 1;
    count = count < 0 ? 0 : count;
    $input.val(count);
	$input.removeClass('default');
	$('#main-info button[type="submit"]').prop('disabled', false).text('Сохранить информацию');
});
// Прибавляем кол-во по клику
$('.quantity_inner .bt_plus').click(function() {
    let $input = $(this).parent().find('.quantity');
	if($input.val() == '') $input.val('1');
    let count = parseInt($input.val()) + 1;
    count = count > parseInt($input.data('max-count')) ? parseInt($input.data('max-count')) : count;
	$input.val(count);
    $input.removeClass('default');
	$('#main-info button[type="submit"]').prop('disabled', false).text('Сохранить информацию');
}); 
// Убираем все лишнее и невозможное при изменении поля
$('.quantity_inner .quantity').bind("change input", function() {
	$(this).removeClass('default');
    if (this.value.match(/[^0-9]/g)) {
        this.value = this.value.replace(/[^0-9]/g, '');
    }
    if (this.value == "") {
        this.value = 1;
    }
    if (this.value > parseInt($(this).data('max-count'))) {
        this.value = parseInt($(this).data('max-count'));
    }
});  

$('#buttons-scroll').on('click', 'button', function() {
	let block = $(this).attr('data-id')

	$('html, body').animate({
		scrollTop: $(block).offset().top // класс объекта к которому приезжаем
		}, 1000);
})

$('#change-password').on('click', 'button[type="submit"]', function (e) {
	e.preventDefault()

	$('.invalid-feedback').remove()

	if($('#password-input').val() == '')  {
		$(`#password-input`).after(`<div class="invalid-feedback">Введите текущий пароль</div>`);
		return;
	}

	$.ajax({
		type: "POST",
		url: "/actions/cabinet/check_password.php",
		dataType: 'json',
		data: $(`input#password-input`),
		success: function (json) {
			if (json['error']) {
				$(`#password-input`).after(`<div class="invalid-feedback">Пароль неверный</div>`)
			}

			if (json['success']) {
				$('#change-password').slideUp(200);
				setTimeout(function () {
					$('#new-password').slideDown(200);
				}, 200);
			}
		}
	})
})

$('#new-password').on('click', 'button[type="submit"]', function (e) {
	e.preventDefault()

	$('.invalid-feedback').remove()

	$.ajax({
		type: "POST",
		url: "/actions/cabinet/set_new_password.php",
		dataType: 'json',
		data: $(`input#new-password-input`),
		success: function (json) {
			if (json['error']) {
				$(`#new-password-input`).after(`<div class="invalid-feedback">${json['error']}</div>`)
			}

			if (json['success']) {
				$('#new-password-input').prop('disabled', true);
				$('#new-password button[type="submit"]').prop('disabled', true).text('Сохранено');
			}
		}
	})
})
$('.show-pass-link').click(function (e) {
	if(!$(this).hasClass('showed')) {
		$(this).addClass('showed').text('Скрыть');
		$(this).siblings('input').attr('type', 'text');
	} else {
		$(this).siblings('input').attr('type', 'password');
		$(this).removeClass('showed').text('Показать');
	}
})
$('.forget-pass-link').click(function (e) {
	let email = $('#user-email').val();

	$.ajax({
        type: "POST",
        url: "/actions/mail/recovery_password.php",
        dataType: 'json',
        data: {email: email},
        success: function (json) {
			if(json['success']) {
            	$('.popup.recovery-pass').fadeIn(200);
				$("body").addClass("hid");
			}
			if (json['error']){
				$('.forget-pass-link').after(`<div class="invalid-feedback">${json['error']}</div>`);
			}
        }
    })
})
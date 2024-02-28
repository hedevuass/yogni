$(function(){
	$('.blog-mob-menu .bottom-link').click(function() {
		$(this).hide();
		$('.blog-mob-menu ul .hidden').show();
	});
	$('.blog-mob-menu .close').click(function() {
		$('.blog-filter-button').show();
		$('.blog-page').show();
		$('.blog-mob-menu').removeClass('opened');
	});
	$('.blog-filter-button').click(function() {
		$('.blog-filter-button').hide();
		$('.blog-page').hide();
		$('.blog-mob-menu').addClass('opened');
	});
	$('.cabinet-page .cabinet-content .prices-block .mob-add, .cabinet-page .add-service .back-link').click(function() {
		$('body').toggleClass('cabinet');
		$('.cabinet-page .mob-nav1').toggle();
		$('.cabinet-page .cabinet-content').toggle();
		$('.cabinet-page .add-service').toggleClass('opened');
	});
	$('.cabinet-page .cabinet-content .mob-ways-block button, .popup-ways header a').click(function() {
		$('.popup-ways').toggleClass('opened');
	});
	$('.popup-ways ul li a').click(function() {
		$(this).toggleClass('active');
	});
	$('.cabinet-page .cabinet-content .ways-block .select ul li a').click(function() {
		$(this).toggleClass('active');
	});
	$('.cabinet-page .cabinet-content .ways-block .select .link').click(function() {
		$(this).toggleClass('active');
		$('.cabinet-page .cabinet-content .ways-block .select ul').toggle();
	});
	$('.cabinet-page .cabinet-content .contacts-block .block .days .day-item label').click(function() {
		$(this).parent().find('.day').toggleClass('holliday');
	});
	$('.cabinet-page .mob-header .menu-button').click(function() {
		$('body').toggleClass('cabinet');
		$('.cabinet-page .mob-nav1').toggle();
		$('.cabinet-page .mob-nav2').toggle();
		$('.cabinet-page .cabinet-content').toggle();
	});
	$('.cabinet-page .mob-nav1 .back-link').click(function() {
		$('body').addClass('cabinet');
		$('.cabinet-page .mob-nav1 .top-link').show();
		$('.cabinet-page .mob-nav1 .back-link').hide();
		$('.cabinet-page .mob-nav1 ul').hide();
		$('.cabinet-page .cabinet-content').show();
	});
	$('.cabinet-page .mob-nav1 .top-link').click(function() {
		$('body').removeClass('cabinet');
		$('.cabinet-page .mob-nav1 .top-link').hide();
		$('.cabinet-page .mob-nav1 .back-link').show();
		$('.cabinet-page .mob-nav1 ul').show();
		$('.cabinet-page .cabinet-content').hide();
	});
	$('.popup .window .ways .button button, .popup .window .reviews .button button, .popup .window .prices .button button').click(function() {
		$('.popup').fadeOut();
		$('body').removeClass('hid');
	});
	$('.company-page .prices button').click(function() {
		$('.popup.prices').fadeIn();
		$('body').addClass('hid');
	});
	$('.company-page .reviews .buttons button:nth-child(1)').click(function() {
		$('.popup.reviews').fadeIn();
		$('body').addClass('hid');
	});
	$('.company-page .reviews .buttons button:nth-child(2)').click(function() {
		$('.popup.review').fadeIn();
		$('body').addClass('hid');
	});
	$('.company-page .ways button').click(function() {
		$('.popup.ways').fadeIn();
		$('body').addClass('hid');
	});
	$('.popup-contacts .bg').click(function() {
		$('.popup-contacts').removeClass('opened');
	});
	$('.company-page .top-info .contacts-button').click(function() {
		$('.popup-contacts').addClass('opened');
	});
	$('.company-page .contacts-block ul li.more a').click(function() {
		$('.company-page .contacts-block ul li.more').hide();
		$('.company-page .contacts-block .is-sticky ul li.no-sticky').show();
	});
	$('.mobile-fixed-menu .filter-link').click(function() {
		$('.mobile-filter-block').addClass('opened');
	});
	$('.mobile-filter-block .close').click(function() {
		$('.mobile-filter-block').removeClass('opened');
	});
	$('.mobile-filter-block .list label.l1').click(function() {
		$('.mobile-filter-block .list').hide();
		$('.mobile-filter-block .list.cat1').show();
	});
	$('.mobile-filter-block .list label.l2').click(function() {
		$('.mobile-filter-block .list').hide();
		$('.mobile-filter-block .list.cat2').show();
	});
	$('.mobile-filter-block .list label.l3').click(function() {
		$('.mobile-filter-block .list').hide();
		$('.mobile-filter-block .list.cat3').show();
	});
	$('.mobile-filter-block .list .sub-item .back-link').click(function() {
		$('.mobile-filter-block .list p').show();
		$('.mobile-filter-block .list .item').show();
		$(this).parent().hide();
	});
	$('.mobile-filter-block .list .item.with_sub label').click(function() {
		$('.mobile-filter-block .list p').hide();
		$('.mobile-filter-block .list .item').hide();
		$(this).parent().next('.sub-item').show();
	});
	$('.popup .window .reg .button button#btn4').click(function() {
		$('.popup.reg').fadeOut();
		$('.popup.reg2').fadeIn();
	});
	$('.popup .window .select-block .list label.l1').click(function() {
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
	$('.popup .window .select-block .list label.l2').click(function() {
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
	$('.popup .window .select-block .list label.l3').click(function() {
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
	$('.popup .window .select-block .list label.l4').click(function() {
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
	$('.popup .window .select-block .list label.l5').click(function() {
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
	$('.popup .window .select-block .list label.l6').click(function() {
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
	$('.popup .window .select-block .list label.l7').click(function() {
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
	$('.popup .window .select-block .list label.l8').click(function() {
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
	$('#btn1').on('click', function() {
		$('.form1 .inp').each(function() {
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
	$('#btn2').on('click', function() {
		$('.form2 .inp').each(function() {
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
	$('#btn3').on('click', function() {
		$('.form3 .inp').each(function() {
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
	$('#btn4').on('click', function() {
		$('.form4 .inp').each(function() {
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
	$('#btn5').on('click', function() {
		$('.form5 .inp').each(function() {
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
	$('#btn6').on('click', function() {
		$('.form6 .inp').each(function() {
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
	$("#category").change(function(e) {
		e.preventDefault();
		$('.index-catalog-block .filter .jq-selectbox.subcat').removeClass('active');
		$(this.value).addClass('active');
	});
	$('.popup .bg').click(function() {
		$('.popup').fadeOut();
		$('body').removeClass('hid');
		$('.popup .window .reg .step').removeClass('active');
		$('.popup .window .reg .step.s1').addClass('active');
	});
	$('.mobile-menu .close, .mobile-menu ul li a.login, .mobile-menu ul li a.reg, .mobile-menu ul li a.feedback').click(function() {
		$('.mobile-menu').removeClass('opened');
	});
	$('.mobile-header .menu-button').click(function() {
		$('.mobile-menu.m1').addClass('opened');
	});
	$('.mobile-header .menu-button2').click(function() {
		$('.mobile-menu.m2').addClass('opened');
	});
	$('.popup .window .reg .bottom-links a.feedback, .footer ul li a.feedback, .mobile-menu ul li a.feedback').click(function() {
		$('.popup').fadeOut();
		$('.popup.feedback').fadeIn();
		$('body').addClass('hid');
	});
	$('.popup .window .reg .bottom-links a.pass').click(function() {
		$('.popup').fadeOut();
		$('.popup.pass').fadeIn();
		$('body').addClass('hid');
	});
	$('.popup .window .reg .bottom-links a.reg, .footer ul li a.reg, .mobile-menu ul li a.reg').click(function() {
		$('.popup').fadeOut();
		$('.popup.reg').fadeIn();
		$('body').addClass('hid');
	});
	$('.header .user-links a.login, .footer ul li a.login, .mobile-menu ul li a.login').click(function() {
		$('.popup.login').fadeIn();
		$('body').addClass('hid');
	});
	$('.header .user-links a.reg').click(function() {
		$('.popup.reg').fadeIn();
		$('body').addClass('hid');
	});
	$('body').on('click', '#show3', function(){
		if ($('#pass3').attr('type') == 'password'){
			$(this).addClass('view');
			$('#pass3').attr('type', 'text');
		} else {
			$(this).removeClass('view');
			$('#pass3').attr('type', 'password');
		}
		return false;
	});
	$('body').on('click', '#show2', function(){
		if ($('#pass2').attr('type') == 'password'){
			$(this).addClass('view');
			$('#pass2').attr('type', 'text');
		} else {
			$(this).removeClass('view');
			$('#pass2').attr('type', 'password');
		}
		return false;
	});
	$('body').on('click', '#show', function(){
		if ($('#pass1').attr('type') == 'password'){
			$(this).addClass('view');
			$('#pass1').attr('type', 'text');
		} else {
			$(this).removeClass('view');
			$('#pass1').attr('type', 'password');
		}
		return false;
	});
	$('.popup .window .reg .step.s3 .select label').click(function() {
		$('.popup .window .reg .step.s3').removeClass('active');
		$('.popup .window .reg .step.s5').addClass('active');
	});
	$('.popup .window .reg .step.s2 .select label').click(function() {
		$('.popup .window .reg .step.s2').removeClass('active');
		$('.popup .window .reg .step.s4').addClass('active');
	});
	$('.popup .window .reg .select label.l1').click(function() {
		$('.popup .window .reg .step.s1').removeClass('active');
		$('.popup .window .reg .step.s2').addClass('active');
	});
	$('.popup .window .reg .select label.l2').click(function() {
		$('.popup .window .reg .step.s1').removeClass('active');
		$('.popup .window .reg .step.s3').addClass('active');
	});
	$('.header .menu1 li a.site-menu').click(function() {
		$('.popup.menu').fadeIn();
		$('body').addClass('hid');
	});
	$('.index-catalog-block .filter .metro-link').click(function() {
		$('.popup.metro').fadeIn();
		$('body').addClass('hid');
	});
	$('.mobile-filter-block .city, .index-catalog-block .filter .city-link, .header .city').click(function() {
		$('.popup.cities').fadeIn();
		$('body').addClass('hid');
	});
	$('.popup .window .close').click(function() {
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
});
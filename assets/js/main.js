
// Check Storage
if (localStorage.getItem('popup')) {
    $(localStorage.getItem('popup')).addClass('opened')
    localStorage.removeItem('popup')
}

//изменение ссылки "Все темы" при наличии в юрле id
$(document).ready(function() {
    var currentUrl = window.location.href;
    if (currentUrl.includes('/journal/id')) {
        var id = currentUrl.substring(currentUrl.lastIndexOf('/') + 1);
        var newLink = $('<li class="find_lawyer" id="journal_link_with_id"><a href="/journal/' + id + '">Все темы</a></li>');
        $('#journal_link').before(newLink);
        $('#journal_link').hide();
    }
    var queryString = window.location.search;
    if (queryString.indexOf('show_login') !== -1) {
        $('.popup.login').fadeIn();
    }
});

// City Autocomplete
$("#select-city").on("keydown", function (event) {
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

            $.post("/actions/location/set_city.php", { id: id, name: name }).done(function () {
                localStorage.setItem('popup', '.mobile-filter-block')
                location.reload()
            })
        }
    });

// City Radio Select
$('input[name=city]').on('click', function () {
    let id = $(this).attr('data-id')

    $.post("/actions/location/set_city.php", { id: id }).done(function () {
        localStorage.setItem('popup', '.mobile-filter-block')
        location.reload()
    })
})

// Filter Lawyer
$(document).ready(function() {
    // Получаем параметры из URL
    const urlParams = new URLSearchParams(window.location.search);

    // Проверяем параметры и устанавливаем состояние галочек
    if (urlParams.has('specialist') && urlParams.get('specialist') === 'true') {
        $('#ch1').prop('checked', true); 
    }
    if (urlParams.has('company') && urlParams.get('company') === 'true') {
        $('#ch2').prop('checked', true); 
    }

    if (urlParams.get('specialist') === 'true') {
        $('#ch3').prop('checked', true);
    }
    if (urlParams.get('company') === 'true') {
        $('#ch4').prop('checked', true);
    }
});

$('#filter-lawyers').on('click', 'button', function () {
    let category = $('#category').val(),
        get = '/yuristy-i-advokaty';

    if (category !== '0' && category !== '.s0') {
        let subcategory = $(`select${category}`).val();

        if (subcategory === '0') {
            get += '/' + $(`option[value='${category}']`).attr('data-slug');
        } else {
            get += '/' + $(`option[value='${category}']`).attr('data-slug') + '/' + subcategory;
        }
    }

    let params = [];

    // Если выбраны обе галочки, добавляем оба параметра
    if ($('#ch1').is(':checked')) {
        params.push('specialist=true');
    }
    if ($('#ch2').is(':checked')) {
        params.push('company=true');
    }

    // Если есть параметры, добавляем их в URL
    if (params.length > 0) {
        get += '?' + params.join('&');
    }

    $(location).attr('href', get);
});



$('#filter-blog').on('click', 'button', function () {
    let theme = $('#blog_theme').val();
    let spec_id = $('#spec_id').val();
        get = '/journal';
        question = false;

    if (spec_id && spec_id !== '0') {
        get += '/id' + spec_id;
    }

    if (theme !== '0' && theme !== '.s0') {
        get += '?theme=' + $(`option[value='${theme}']`).attr('data-slug');
    }

    // if (!$('#ch1').is(':checked') && $('#ch2').is(':checked')) {
    //     get += '?company=true';
    // } else if ($('#ch1').is(':checked') && !$('#ch2').is(':checked')){
    //     get += '?specialist=true';
    // }
    
    $(location).attr('href', get);
})

$('.catalog-checkbox-mobile .checkbox').on('click', function () {
    let path = window.location.pathname;

    let currentParams = new URLSearchParams(window.location.search);

    let get = path;

    // Логика обработки нажатий на чекбоксы
    if ($(this).hasClass('specialist_ch')) {
        currentParams.set('specialist', 'true'); 
        currentParams.delete('company');  
    } else if ($(this).hasClass('company_ch')) {
        currentParams.set('company', 'true'); 
        currentParams.delete('specialist'); 
    } else if ($(this).hasClass('checkbox') && $(this).text() === 'Все') {
        // Если выбрано "Все", удаляем оба фильтра
        currentParams.delete('specialist');
        currentParams.delete('company');
    }

    // Если есть параметры фильтрации, добавляем их в URL
    if (currentParams.toString()) {
        get += '?' + currentParams.toString(); 
    }

    $(location).attr('href', get);
});


// Filter Expertise
$('#filter-expertise').on('click', 'button', function () {
    let category = $('#expertise').val(),
        get = '/experty',
        question = false,
        filter = ''


    if (category !== '0' && category !== '.s0') get += '/' + category;

    if ($('#ch3').is(':checked')) {
        filter += question ? '&specialist=true' : '?specialist=true'
        question = true
    }

    if ($('#ch4').is(':checked')) {
        filter += question ? '&company=true' : '?company=true'
    }

    get += filter

    $(location).attr('href', get)
})


// Metro Autocomplete
$("#select-metro, #mobile-select-metro").on("keydown", function (event) {
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

            if (!$(`#metro-${id}`).length) {
                $('#metro-list, #mobile-metro-list').prepend(`<input type="checkbox" class="checkbox" id="metro-${id}" name="metro-${id}" data-id="${id}" checked>
                                        <label for="metro-${id}">${name}</label>`)
            }

            $(this).val('')

            return false
        }
    });

$('#popup-metro').on('click', 'button', function () {
    let metro = ''

    $('#popup-metro input:checkbox:checked').each(function () {
        metro += $(this).attr('data-id') + ','
    });

    $.post("/actions/location/set_metro.php", { metro: metro }).done(function () {
        $(location).attr('href', '/notariusy');
    })
})

//поведение поисковой строки
$("input#search-query").on("input", function() {
    let search = $(this).val();
    $.post("/actions/search.php", { search: search }).done(function (response) {
        $(".search-window .search-categories").slick('unslick');
        $(".search__result-box").empty();
        $(".search__result-box").html(response);
        $(".search-window .search-categories").slick({
            arrows: false,
            variableWidth:true,
            draggable:true,
            touchMove:true,
            swipeToSlide:true,
            infinite:false,
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
        if ($('.search-categories') && $('.search-categories').hasClass('disable-btn')) {
            $(".search__find-btn").prop( "disabled", true);
        } else {
            $(".search__find-btn").prop( "disabled", false);
        }
    });
});

//поиск совпадений имени при регистрации
$("#registration-popup .form4 input[name='lastname'], #registration-popup .form5 input[name='company']").on("input", function() {
    let search_name = $(this).val();
    let type = 'company';
    form = $('#registration-popup .form5');
    if ($(this).attr('name') == 'lastname') {
        search_name = $("#registration-popup input[name='name']").val() + ' ' + $(this).val();
        type = 'specialist';
        form = $('#registration-popup .form4');
    }
    $.post("/actions/account/registration.php", { spec_name: search_name, spec_type: type }).done(function (response) {
        
        form.children(".existing-name").empty();
        form.children(".existing-name").html(response);
    });
});

// Registration
$('#registration-popup').on('click', 'button', function () {
    $('input').removeClass('error')
    $('.invalid-feedback').html('')

    let type = $('#registration-popup input[name=classification]:checked').val(),
        popup

    if (type <= 4) popup = '#registration-specialist'
    if (type >= 5) popup = '#registration-company'

    $.ajax({
        type: "POST",
        url: "/actions/account/registration.php",
        dataType: 'json',
        data: $(`${popup} input[type=text], ${popup} input[type=password], #registration-popup input[type=\'radio\']:checked`),
        success: function (json) {
            if (json['error']) {
                $.each(json['error'], function (key, value) {
                    if (key == 'email_resend') {
                        $(`${popup} input[name=email]`).addClass('error');
                        $(`${popup} input[name=email]`).siblings('.invalid-feedback').addClass('black').html('Пожалуйста, подтвердите регистрационные данные. Проверьте папку "Cпам" или <span class="resend-confirmation">получите письмо повторно</span>.');
                    }
                    $(`${popup} input[name=${key}]`).addClass('error');
                    $(`${popup} input[name=${key}]`).siblings('.invalid-feedback').html(value)
                });
            }

            if (json['fatal_error']) {
                $('.popup.error activate').html(json['fatal_error'])
                $('.popup.error').fadeIn();
            }

            if (json['success']) {
                $('.popup.reg').fadeOut();
                $('.popup.reg2').fadeIn();
                // var nameValue = $('input[name="name"]').val();
                // var greeting = nameValue + ", ваша страница подтверждена!";
                // $('#new_name').text(greeting);
            }
        }
    });
    // $.ajax({
    //     type: "POST",
    //     url: "/actions/mail/registration.php",
    //     dataType: 'json',
    //     data: { email: $(`${popup} input[name=email]`).val() },
    //     success: function (json) {
    //         if (json['success']) {
    //             $('#registration-popup').fadeOut();
    //             $('.reg2').fadeIn();
    //         }
    //     }
    // });
})
$('#login-popup-resend').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
        type: "POST",
        url: "/actions/account/registration.php",
        dataType: 'json',
        data: {email: $(`#login-popup-resend input[name=email]`).val(), type: 'resend-confirmation'},
        success: function (json) {

            if (json['fatal_error']) {
                $('.popup.error .activate').html(json['fatal_error'])
                $('.popup.login-not-confirmed').fadeOut();
                $('.popup.error').fadeIn();
            }

            if (json['success']) {
                $('.popup.login-not-confirmed').fadeOut();
                $('.popup.login').fadeIn();
            }
        }
    })
})
// Переотправить письмо с подтверждением
$('#registration-popup').on('click', '.resend-confirmation', function (e) {
    $('input').removeClass('error')
    $('.invalid-feedback').html('')

    let type = $('#registration-popup input[name=classification]:checked').val(),
        popup

    if (type <= 4) popup = '#registration-specialist'
    if (type >= 5) popup = '#registration-company'

    $.ajax({
        type: "POST",
        url: "/actions/account/registration.php",
        dataType: 'json',
        data: {email: $(`${popup} input[name=email]`).val(), type: 'resend-confirmation'},
        success: function (json) {

            if (json['fatal_error']) {
                $('.popup.error .activate').html(json['fatal_error'])
                $('.popup.reg').fadeOut();
                $('.popup.error').fadeIn();
            }

            if (json['success']) {
                $('.popup.reg').fadeOut();
                $('.popup.reg2').fadeIn();
            }
        }
    })
})

// Login
$('#login-popup').on('click', 'button', function (e) {
    e.preventDefault()

    $('input').removeClass('error')
    $('.invalid-feedback').html('')

    $.ajax({
        type: "POST",
        url: "/actions/account/login.php",
        dataType: 'json',
        data: $('#login-popup input[type=text], #login-popup input[type=password]'),
        success: function (json) {
            if (json['error']) {
                
                if (json['error']['auth']) {
                    let button = $('#login-popup button').html()

                    $('#login-popup button').html(json['error']['auth'])

                    setTimeout(function () {
                        $('#login-popup button').html(button)
                    }, 2000);
                }

                if (!json['error']['auth']) {
                    $.each(json['error'], function (key, value) {
                        if (key == 'email_resend') {
                            let email = $('#login-popup input[name="email"]').val();
                            $('#login-popup-resend input[name="email"]').val(email);
                            $('.popup.login').fadeOut();
                            $('.popup.login-not-confirmed').fadeIn();
                        }
                        $(`#login-popup input[name=${key}]`).addClass('error');
                        $(`#login-popup input[name=${key}]`).siblings('.invalid-feedback').html(value)
                    });
                }
            }

            if (json['success']) $(location).attr('href', json['success'])
        }
    })
})
$('.add-break').on('click', function (e) {
    e.preventDefault()
    console.log('Hello');
})
document.addEventListener("DOMContentLoaded", function() {
    function getUserIdFromUrl() {
        var url = window.location.href;
        var idPos = url.indexOf('id');
        if (idPos !== -1) {
            var id = url.substr(idPos + 2);
            return id;
        }
        return null;
    }
    var userId = getUserIdFromUrl();
    if (userId !== null) {
        document.getElementById('user_id_input').value = userId;
    }
});
$('.send-confirmation').click(function(e) {
    e.preventDefault();
    var email = $('input[name="email"]').val();
    var user_id = $('input[name="user_id"]').val();

    // Очищаем предыдущие сообщения об ошибках
    $('.confirmation__popup input[name="email"]').removeClass('error');
    $('.confirmation__popup .invalid-feedback').html('');

    $.ajax({
        type: 'POST',
        url: '/actions/mail/rights_confirmation.php',
        dataType: 'json',
        data: { email: email, user_id: user_id },
        success: function(json) {
            if (json['error']) {
                $('.confirmation__popup input[name="email"]').addClass('error');
                $('.confirmation__popup .invalid-feedback').html(json['error']);
            } else if (json['success']) {
                $(".confirmation__popup").fadeOut();
                $(".confirmation__popup2").fadeIn();
                $("body").addClass("hid");
            }
        },
        error: function() {
            $('.confirmation__popup .invalid-feedback').html('Что-то пошло не так. Повторите попытку позже.');
        }
    });
});
//Send mail to specialist
$('.popup.write .popup-btn').on('click', function(e) {
    e.preventDefault();

    // Собираем данные формы
    const email = $('#specialist_mail').val();
    const contactInfo = $('.popup.write input[type=text]').val();
    const message = $('.popup.write textarea').val();

    // Проверяем, заполнены ли поля
    if (contactInfo.trim() === '' || message.trim() === '') {
        alert('Заполните все поля.');
        return;
    }

    // Отправляем данные на сервер
    $.ajax({
        type: 'POST',
        url: '/actions/mail/send_message.php',  // Укажи путь до обработчика
        data: {
            email: email,
            contactInfo: contactInfo,
            message: message
        },
        success: function(response) {
            // Очищаем форму и вставляем сообщение об успешной отправке
            $('.popup.write .form').html(`
                <div style="text-align: center;" class="middle-text">
                    Сообщение успешно отправлено.
                </div>
            `);
        },
        error: function(xhr, status, error) {
            $('.popup.write .form').html(`
                <div style="text-align: center;" class="middle-text">
                    Произошла ошибка при отправке сообщения.
                </div>
            `);
        }
    });
});

// Recovery Password
$('#recovery-password-popup').on('click', 'button', function (e) {
    e.preventDefault()

    $('input').removeClass('error')
    $('.invalid-feedback').html('')

    $.ajax({
        type: "POST",
        url: "/actions/mail/recovery_password.php",
        dataType: 'json',
        data: $('#recovery-password-popup input[type=text]'),
        success: function (json) {
            if (json['error']) {
                $.each(json['error'], function (key, value) {
                    $(`#recovery-password-popup input[name=${key}]`).addClass('error');
                    $(`#recovery-password-popup input[name=${key}]`).siblings('.invalid-feedback').html(value)
                    $('.popup.pass').fadeOut();
                });
            }
            if (json['success']) {
                $('#recovery-password-popup .form2').html(`<div class="field">${json['success']}</div>`);
                $(".recovery-password__popup2").fadeIn();
                $('.popup.pass').fadeOut();
            }
        },
        error: function(xhr, status, error) {
            $(".recovery-password__popup2").fadeIn();
            $("#recovery-password-popup").fadeOut();
            $('.popup.pass').fadeOut();
        }
    })
})

$('#recovery-password-popup-2').on('click', 'button', function (e) {
    e.preventDefault()

    $('input').removeClass('error')
    $('.invalid-feedback').html('')

    $.ajax({
        type: "POST",
        url: "/actions/account/recovery_password.php",
        dataType: 'json',
        data: $('#recovery-password-popup-2 input[type=password], #recovery-password-popup-2 input[type=hidden]'),
        success: function (json) {
            if (json['error']) {
                $.each(json['error'], function (key, value) {
                    $(`#recovery-password-popup-2 input[name=${key}]`).addClass('error');
                    $(`#recovery-password-popup-2 input[name=${key}]`).siblings('.invalid-feedback').html(value)
                });

                if (json['error']['recovery']) $('.contetn').html(json['error']['recovery'])
            }

            if (json['success']) $('.content').html(json['success'])
        }
    })
})

document.addEventListener("DOMContentLoaded", function() {
    $('.recovery2').fadeIn();
});
$('#reset_password').on('click', function (e) {
    e.preventDefault();
    $('.recovery2').fadeOut();
});
$('.recovery-button').on('click', function(e) {
    e.preventDefault();

    var currentUrl = window.location.href;

    var urlParts = currentUrl.split('?');
    var path = urlParts[0];
    var queryString = urlParts[1];

    var newPath = '/cabinet/login-i-parol';

    var newUrl = newPath;
    if (queryString) {
        newUrl += '?' + queryString;
    }

    window.location.href = newUrl;  
});

$('.confirmation-button').on('click', function(e) {
    e.preventDefault();

    var currentUrl = window.location.href;

    var urlParts = currentUrl.split('?');
    var path = urlParts[0];
    var queryString = urlParts[1];

    var newPath = '/cabinet/login-i-parol';

    var newUrl = newPath;
    if (queryString) {
        newUrl += '?' + queryString;
    }

    window.location.href = newUrl;  
});


// Feedback
$('#support-popup, #support-popup-header').on('click', 'button[type="submit"]', function (e) {
	e.preventDefault();
	let form = $(this).closest('form').attr('id');
	$(`#${form} textarea[name="content"]`).removeClass('error');
	$(`#${form} .invalid-feedback`).remove();
	if ($(`#${form} textarea[name="content"]`).val() == '') {
		$(`#${form} textarea[name="content"]`).addClass('error');
		$(`#${form} textarea[name="content"]`).after(`<div class="invalid-feedback">Введите сообщение.</div>`);
		return;
	}
    let message_type = $(`#${form} .support-type`).text();
    let message = $(`#${form} textarea[name="content"]`).val();
    let email = $(`#${form} input[name="user_email"]`).val();
    let user_name = $(`#${form} input[name="user_name"]`).val();

    $.ajax({
        type: "POST",
        url: "/actions/mail/feedback.php",
        dataType: 'json',
        data: {message_type: message_type, message: message, email: email, user_name: user_name},
        success: function (json) {
            if (json['fatal_error']) {
                let error_message = json['fatal_error'];
                $(`#${form} textarea`).after(`<div class="invalid-feedback">` + error_message +`</div>`);
            }

            if (json['success']) {
                $('.popup.support-write, .popup.support-write-header').fadeOut(200);
				
				if (form == 'support-popup-header') {
					setTimeout(function() {
						$('.popup.support-idea-end').fadeIn(200);
					}, 200)
				} else {
					setTimeout(function() {
						$('.popup.support-end').fadeIn(200);
					}, 200)
				}
            }
        }
    })
})

// Review
$('#review-popup').on('click', 'button', function (e) {
    e.preventDefault()

    $('input, textarea').removeClass('error')
    $('.invalid-feedback').html('')

    $.ajax({
        type: "POST",
        url: "/actions/review.php",
        dataType: 'json',
        data: $('#review-popup input[type=text], #review-popup input[type=hidden], #review-popup textarea, #review-popup input[type=radio]:checked'),
        success: function (json) {
            if (json['error']) {
                if (json['error']['fatal_error']) $('#review-popup .form6').html(`<div class="field">${json['error']['fatal_error']}</div>`)

                $.each(json['error'], function (key, value) {
                    if (key == 'review') {
                        $(`#review-popup textarea[name=${key}]`).addClass('error')
                        $(`#review-popup textarea[name=${key}]`).siblings('.invalid-feedback').html(value)
                        return true
                    }

                    $(`#review-popup input[name=${key}]`).addClass('error')

                    if (key == 'rating') {
                        $(`#review-popup input[name=${key}]`).closest('.rating-area').siblings('.invalid-feedback').html(value)
                        return true
                    }

                    $(`#review-popup input[name=${key}]`).siblings('.invalid-feedback').html(value)
                });
            }

            if (json['success']) $('#review-popup .form6').html(`<div class="field">${json['success']}</div>`)
        }
    })
})

$('.index-catalog-block').on('click', '#get-specialist button', function () {
    let form = '#get-specialist',
        url = new URL(window.location)

    $.ajax({
        type: "GET",
        url: "/actions/blocks/get_specialist.php",
        dataType: 'json',
        data: $(`${form} input[type=hidden]`),
        success: function (json) {
            $('.index-catalog-block .pages').remove()
            $('.index-catalog-block .bottom-button').remove()

            $('.index-catalog-block .grid_flex').append(json.specialists)
            if (json.pagination) {
                $('.index-catalog-block .grid_flex').after(json.pagination)
            }

            url.searchParams.set('page', json.page)
            history.pushState(null, null, url);
        }
    })
})

$('.index-catalog-block').on('click', '#get-search-result button', function () {
    let form = '#get-search-result',
        url = new URL(window.location)

    $.ajax({
        type: "GET",
        url: "/actions/blocks/get_search_result.php",
        dataType: 'json',
        data: $(`${form} input[type=hidden]`),
        success: function (json) {
            $('.index-catalog-block .pages').remove()
            $('.index-catalog-block .bottom-button').remove()

            $('.index-catalog-block .grid_flex').append(json.specialists)
            if (json.pagination) {
                $('.index-catalog-block .grid_flex').after(json.pagination)
            }

            url.searchParams.set('page', json.page)
            history.pushState(null, null, url);
        }
    })
})

$('.index-catalog-block').on('click', '#get-favorite button', function () {
    let form = '#get-favorite',
        url = new URL(window.location)

    $.ajax({
        type: "GET",
        url: "/actions/blocks/get_favorite.php",
        dataType: 'json',
        data: $(`${form} input[type=hidden]`),
        success: function (json) {
            $('.index-catalog-block .pages').remove()
            $('.index-catalog-block .bottom-button').remove()

            $('.index-catalog-block .grid_flex').append(json.specialists)
            if (json.pagination) {
                $('.index-catalog-block .grid_flex').after(json.pagination)
            }

            url.searchParams.set('page', json.page)
            history.pushState(null, null, url);
        }
    })
})

$('.index-catalog-block').on('click', '#get-blog button', function () {
    let form = '#get-blog',
        url = new URL(window.location)

    $.ajax({
        type: "GET",
        url: "/actions/blocks/get_blog.php",
        dataType: 'json',
        data: $(`${form} input[type=hidden]`),
        success: function (json) {
            $('.index-catalog-block .pages').remove()
            $('.index-catalog-block .bottom-button').remove()

            $('.index-catalog-block .articles').append(json.articles)
            if (json.pagination) {
                $('.index-catalog-block .articles').after(json.pagination)
            }

            url.searchParams.set('page', json.page)
            history.pushState(null, null, url);
        }
    })
})

$('.message-to-support').on('click', function (e) {
	$('.support-write-oproekte').fadeIn(200);
	$('body').addClass('hid');
})

$('#support-popup').on('click', 'button[type="submit"]', function (e) {
    e.preventDefault();
    
    // Получаем ID формы и необходимые поля
    let form = $(this).closest('form').attr('id');
    let $textarea = $(`#${form} textarea[name="content"]`);
    let $feedbackContainer = $textarea.closest('.field');

    // Убираем предыдущие ошибки, если они есть
    $textarea.removeClass('error');
    $(`#${form} .invalid-feedback`).remove();

    // Проверка на пустое сообщение
    if ($textarea.val() === '') {
        $textarea.addClass('error');
        $feedbackContainer.append(`<div class="invalid-feedback">Введите сообщение.</div>`);
        return;
    }

    // Получаем значения полей
    let message_type = $(`#${form} .support-type`).text();
    let message = $textarea.val();
    let email = $(`#${form} input[name="user_email"]`).val();
    let user_name = $(`#${form} input[name="user_name"]`).val();

    // Отправляем данные на сервер
    $.ajax({
        type: "POST",
        url: "/actions/mail/feedback.php",
        dataType: 'json',
        data: { message_type: message_type, message: message, email: email, user_name: user_name },
        success: function (json) {
            if (json['fatal_error']) {
                let error_message = json['fatal_error'];
                $feedbackContainer.append(`<div class="invalid-feedback">` + error_message + `</div>`);
            }

            if (json['success']) {
                // Закрываем текущий попап
                $('.popup.support-write-oproekte').fadeOut(200);

                // Открываем попап завершения
                setTimeout(function () {
                    $('.popup.support-end-oproekte').fadeIn(200);
                }, 200);
            }
        }
    });
});

// Закрытие попапа завершения
$('.popup.support-end-oproekte').on('click', '.close-popup-btn, .close', function () {
    $(this).closest('.popup').fadeOut(200);
});


// Check Storage
if (localStorage.getItem('popup')) {
    $(localStorage.getItem('popup')).addClass('opened')
    localStorage.removeItem('popup')
}

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
$('#filter-lawyers').on('click', 'button', function () {
    let category = $('#category').val(),
        get = '/yuristy-i-advokaty';

    if (category !== '0' && category !== '.s0') {
        let subcategory = $(`select${category}`).val()

        if (subcategory === '0') {
            get += '/' + $(`option[value='${category}']`).attr('data-slug');
        } else {
            get += '/' + $(`option[value='${category}']`).attr('data-slug') +'/'+  subcategory;
        }
    }

    if (!$('#ch1').is(':checked') && $('#ch2').is(':checked')) {
        get += '?company=true';
    } else if ($('#ch1').is(':checked') && !$('#ch2').is(':checked')){
        get += '?specialist=true';
    }
    
    $(location).attr('href', get);
})

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
    let slug = $('.catalog-checkbox-mobile').attr('data-class-slug');
    get = '/'+slug;
    if($(this).hasClass('specialist_ch')) {
        get += '?specialist=true';
    }
    if($(this).hasClass('company_ch')) {
        get += '?company=true';
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

    if ($('#ch3').is(':checked') && $('#ch4').is(':checked')) {
        filter = ''
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

// Переотправить письмо с подтверждением
$('#registration-popup').on('click', '.resend-confirmation', function () {
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
        data: $(`${popup} input[name=email]`),
        success: function (json) {

            if (json['fatal_error']) {
                $('.popup.error activate').html(json['fatal_error'])
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
                    }, 1500);
                }

                if (!json['error']['auth']) {
                    $.each(json['error'], function (key, value) {
                        $(`#login-popup input[name=${key}]`).addClass('error');
                        $(`#login-popup input[name=${key}]`).siblings('.invalid-feedback').html(value)
                    });
                }
            }

            if (json['success']) $(location).attr('href', json['success'])
        }
    })
})

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
                });
            }

            if (json['success']) {
                $('#recovery-password-popup .form2').html(`<div class="field">${json['success']}</div>`);
                $('.popup.pass .reg .bottom-links').remove();
            }
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

// Feedback
$('#feedback-popup').on('click', 'button', function (e) {
    e.preventDefault()

    $('input, textarea').removeClass('error')
    $('.invalid-feedback').html('')

    $.ajax({
        type: "POST",
        url: "/actions/mail/feedback.php",
        dataType: 'json',
        data: $('#feedback-popup input[type=text], #feedback-popup textarea'),
        success: function (json) {
            if (json['error']) {
                if (json['error']['contact']) {
                    $(`#feedback-popup input[name=contact]`).addClass('error');
                    $(`#feedback-popup input[name=contact]`).siblings('.invalid-feedback').html(json['error']['contact'])
                }
                if (json['error']['message']) {
                    $(`#feedback-popup textarea[name=message]`).addClass('error');
                    $(`#feedback-popup textarea[name=message]`).siblings('.invalid-feedback').html(json['error']['message'])
                }
            }

            if (json['success']) $('#feedback-popup .form1').html(`<div class="field">${json['success']}</div>`)
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
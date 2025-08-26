
// Check Storage
if (localStorage.getItem('popup')) {
    $(localStorage.getItem('popup')).addClass('opened')
    localStorage.removeItem('popup')
}

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
        console.warn('Не удалось определить высоту строки:', $el);
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



function loadAndClamp(selector = '.cut-text', lines) {
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
        loadAndClamp('.cut-text', 4);

        const isMobile = window.innerWidth <= 768;
        const blogLines = isMobile ? 4 : 4;
        const blogLinesCatalog = isMobile ? 2 : 2;
        loadAndClamp('.cut-text-blogs', blogLines);
        loadAndClamp('.cut-text-catalog', blogLinesCatalog);
    }

    updateClamp();

    $(window).on('resize', function () {
        updateClamp();
    });
});


//изменение ссылки "Все темы" при наличии в юрле id
$(document).ready(function () {
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

// City Radio Select
$(document).on('click', '.radio-city', function () {
    let id = $(this).data('id'); // используем data-атрибут для извлечения id

    $.post("/actions/location/set_city.php", { id: id }).done(function () {
        localStorage.setItem('popup', '.mobile-filter-block');
        location.reload();
    });
});

$(document).ready(function () {
    const currentPath = window.location.pathname;

    // Проверяем наличие параметров в URL
    let hasPrivate = currentPath.includes('/private');
    let hasCompany = currentPath.includes('/company');

    // Определяем, какие чекбоксы должны быть активны
    let ch1 = hasPrivate, ch2 = hasCompany, ch3 = hasPrivate, ch4 = hasCompany;

    // Проверяем сохранённое состояние чекбоксов
    let savedCh1 = localStorage.getItem('ch1') === 'true';
    let savedCh2 = localStorage.getItem('ch2') === 'true';
    let savedCh3 = localStorage.getItem('ch3') === 'true';
    let savedCh4 = localStorage.getItem('ch4') === 'true';

    // Если в URL нет private или company, но в localStorage есть активные чекбоксы — включаем их
    if (!(hasPrivate || hasCompany)) {
        ch1 = savedCh1;
        ch2 = savedCh2;
        ch3 = savedCh3;
        ch4 = savedCh4;
    }

    // Устанавливаем состояние чекбоксов
    $('#ch1').prop('checked', ch1).attr('data-checked', ch1 ? 'true' : 'false');
    $('#ch2').prop('checked', ch2).attr('data-checked', ch2 ? 'true' : 'false');
    $('#ch3').prop('checked', ch3).attr('data-checked', ch3 ? 'true' : 'false');
    $('#ch4').prop('checked', ch4).attr('data-checked', ch4 ? 'true' : 'false');

    // Сбрасываем чекбоксы, если в URL нет соответствующего параметра и выбран только один чекбокс
    if (!hasPrivate && ch1 && !ch2) {
        $('#ch1').prop('checked', false).attr('data-checked', 'false');
        localStorage.setItem('ch1', 'false');
    }
    if (!hasCompany && ch2 && !ch1) {
        $('#ch2').prop('checked', false).attr('data-checked', 'false');
        localStorage.setItem('ch2', 'false');
    }
    if (!hasPrivate && ch3 && !ch4) {
        $('#ch3').prop('checked', false).attr('data-checked', 'false');
        localStorage.setItem('ch3', 'false');
    }
    if (!hasCompany && ch4 && !ch3) {
        $('#ch4').prop('checked', false).attr('data-checked', 'false');
        localStorage.setItem('ch4', 'false');
    }
});

// Filter Lawyers
$('#filter-lawyers').on('click', 'button', function () {
    let category = $('#category').val(),
        baseURL = '/yuristy-i-advokaty';

    if (category !== '0' && category !== '.s0') {
        let subcategory = $(`select${category}`).val();

        if (subcategory === '0') {
            baseURL += '/' + $(`option[value='${category}']`).attr('data-slug');
        } else {
            baseURL += '/' + $(`option[value='${category}']`).attr('data-slug') + '/' + subcategory;
        }
    }

    // Получаем состояния чекбоксов ch1 и ch2
    let ch1 = $('#ch1').is(':checked'),
        ch2 = $('#ch2').is(':checked');

    // Сохраняем состояние чекбоксов в localStorage
    localStorage.setItem('ch1', ch1 ? 'true' : 'false');
    localStorage.setItem('ch2', ch2 ? 'true' : 'false');

    // Если выбраны оба чекбокса, ничего не добавляем в URL
    if (!(ch1 && ch2)) {
        if (ch1) baseURL += '/private';
        if (ch2) baseURL += '/company';
    }

    $(location).attr('href', baseURL);
});

// Filter Expertise
$('#filter-expertise').on('click', 'button', function () {
    let category = $('#expertise').val(),
        baseURL = '/experty';

    if (category !== '0' && category !== '.s0') baseURL += '/' + category;

    // Получаем состояния чекбоксов ch3 и ch4
    let ch3 = $('#ch3').is(':checked'),
        ch4 = $('#ch4').is(':checked');

    // Сохраняем состояние чекбоксов в localStorage
    localStorage.setItem('ch3', ch3 ? 'true' : 'false');
    localStorage.setItem('ch4', ch4 ? 'true' : 'false');

    // Если выбраны оба чекбокса, ничего не добавляем в URL
    if (!(ch3 && ch4)) {
        if (ch3) baseURL += '/private';
        if (ch4) baseURL += '/company';
    }

    $(location).attr('href', baseURL);
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
    let path = window.location.pathname.split('/');
    let filterIndex = path.findIndex((segment) => segment === 'private' || segment === 'company');

    if (filterIndex !== -1) {
        path.splice(filterIndex, 1);
    }

    if ($(this).hasClass('specialist_ch')) {
        path.push('private');
    } else if ($(this).hasClass('company_ch')) {
        path.push('company');
    }

    const newPath = path.join('/');
    $(location).attr('href', newPath);
});

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
                $('#metro-list, #mobile-metro-list').prepend(`
                <input type="checkbox" class="checkbox" id="metro-${id}" name="metro-${id}" data-id="${id}" checked>
                <label for="metro-${id}">${name}</label>`)
            }

            $(this).val('')
            return false
        }
    })

    function updateMetroLinkText() {
        let checked = $('#popup-metro input:checkbox:checked');
        let count = checked.length;
        let text = 'Метро';
    
        if (count === 1) {
            text = checked.first().next('label').text().trim();
        } else if (count > 1) {
            text = `Выбрано (${count})`;
        } 

        if (count > 0) {
            mob_text = `Выбрано (${count})`;
        }
    
        $('.metro-link').text(text);
        $('.btn.btn__mobile-filters.metro .metro-text').text(mob_text);
    }

const isDesktop = window.innerWidth > 768

if (isDesktop) {
    $('#popup-metro').on('click', 'button', function () {
        $('.popup.metro').fadeOut(300)
        $("body").removeClass("hid");
        updateMetroLinkText()
    })

    $('#filter-notaries .btn__find').on('click', function () {
        let metro = ''
        $('#popup-metro input:checkbox:checked').each(function () {
            metro += $(this).attr('data-id') + ','
        })

        $.post("/actions/location/set_metro.php", { metro: metro }).done(function () {
            window.location.href = '/notariusy'
        })
    })
} else {
    $('#popup-metro').on('click', 'button', function () {
        let metro = ''
        $('#popup-metro input:checkbox:checked').each(function () {
            metro += $(this).attr('data-id') + ','
        })

        $.post("/actions/location/set_metro.php", { metro: metro }).done(function () {
            window.location.href = '/notariusy'
        })
    })
}

//поведение поисковой строки
$("input#search-query").on("input", function () {
    let search = $(this).val();
    $.post("/actions/search.php", { search: search }).done(function (response) {
        $(".search-window .search-categories").slick('unslick');
        $(".search__result-box").empty();
        $(".search__result-box").html(response);
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
        if ($('.search-categories') && $('.search-categories').hasClass('disable-btn')) {
            $(".search__find-btn").prop("disabled", true);
        } else {
            $(".search__find-btn").prop("disabled", false);
        }
    });
});

//поиск совпадений имени при регистрации
$("#registration-popup .form4 input[name='lastname'], #registration-popup .form5 input[name='company']").on("input", function () {
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
        data: $(`${popup} input[type=text], ${popup} input[type=password], ${popup} input[type=hidden], #registration-popup input[type=\'radio\']:checked`),
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
        data: { email: $(`#login-popup-resend input[name=email]`).val(), type: 'resend-confirmation' },
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
        data: { email: $(`${popup} input[name=email]`).val(), type: 'resend-confirmation' },
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

function toggleContent(header) {
    header.classList.toggle('active');
    const container = header.closest('.toggle-block');
    const contents = container.querySelectorAll('.toggle-content');
    contents.forEach(el => {
        el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'grid' : 'none';
    });
}

document.addEventListener("DOMContentLoaded", function () {
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
$('.send-confirmation').click(function (e) {
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
        success: function (json) {
            if (json['error']) {
                $('.confirmation__popup input[name="email"]').addClass('error');
                $('.confirmation__popup .invalid-feedback').html(json['error']);
            } else if (json['success']) {
                $(".confirmation__popup").fadeOut();
                $(".confirmation__popup2").fadeIn();
                $("body").addClass("hid");
            }
        },
        error: function () {
            $('.confirmation__popup .invalid-feedback').html('Что-то пошло не так. Повторите попытку позже.');
        }
    });
});
//Send mail to specialist
$('.popup.write .popup-btn').on('click', function (e) {
    e.preventDefault();

    // Собираем данные формы
    const email = $('#specialist_mail').val();
    const name = $('.popup.write input[name="name"]').val();
    const contactInfo = $('.popup.write input.phone-input-reg').val();
    const message = $('.popup.write textarea').val();

    // Проверяем, заполнены ли поля
    if (name.trim() === '' || contactInfo.trim() === '' || message.trim() === '') {
        alert('Заполните все поля.');
        return;
    }

    // Отправляем данные на сервер
    $.ajax({
        type: 'POST',
        url: '/actions/mail/send_message.php',
        data: {
            email: email,
            name: name,
            contactInfo: contactInfo,
            message: message
        },
        success: function (response) {
            // Очищаем форму и вставляем сообщение об успешной отправке
            $('.popup.write .form').html(`
                <div style="text-align: center;" class="middle-text">
                    Сообщение успешно отправлено!<br>
Специалист свяжется с вами по оставленному в форме записи номеру телефона.
                </div>
            `);
        },
        error: function (xhr, status, error) {
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
    e.preventDefault();

    $('input').removeClass('error');
    $('.invalid-feedback').html('');

    $.ajax({
        type: "POST",
        url: "/actions/mail/recovery_password.php",
        dataType: 'json',
        data: $('#recovery-password-popup input[type=text]'),
        success: function (json) {
            if (json['error']) {
                $.each(json['error'], function (key, value) {
                    $(`#recovery-password-popup input[name=${key}]`).addClass('error');
                    $(`#recovery-password-popup input[name=${key}]`).siblings('.invalid-feedback').html(value);
                });
            }
            if (json['success']) {
                
                // Сначала скрываем .popup.pass, затем показываем recovery-password__popup2
                $('.popup.pass').fadeOut(function() {
                    $(".recovery-password__popup2").fadeIn();
                });
            }
        },
        error: function (xhr, status, error) {
            // Аналогично в блоке error
            $('.popup.pass').fadeOut(function() {
                $(".recovery-password__popup2").fadeIn();
            });
            $("#recovery-password-popup").fadeOut();
        }
    });
});

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

document.addEventListener("DOMContentLoaded", function () {
    $('.recovery2').fadeIn();
});
$('#reset_password').on('click', function (e) {
    e.preventDefault();
    $('.recovery2').fadeOut();
});
$('.recovery-button').on('click', function (e) {
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

$('.confirmation-button').on('click', function (e) {
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
        data: { message_type: message_type, message: message, email: email, user_name: user_name },
        success: function (json) {
            if (json['fatal_error']) {
                let error_message = json['fatal_error'];
                $(`#${form} textarea`).after(`<div class="invalid-feedback">` + error_message + `</div>`);
            }

            if (json['success']) {
                $('.popup.support-write, .popup.support-write-header').fadeOut(200);

                if (form == 'support-popup-header') {
                    setTimeout(function () {
                        $('.popup.support-idea-end').fadeIn(200);
                    }, 200)
                } else {
                    setTimeout(function () {
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
                if (json['error']['fatal_error']) {
                    $('#review-popup .name').text('Отзыв не будет опубликован')
                    $('#review-popup .form6').html(`<div class="field">${json['error']['fatal_error']}</div>`)
                    return
                }

                $.each(json['error'], function (key, value) {
                    if (key === 'review') {
                        $(`#review-popup textarea[name=${key}]`).addClass('error')
                        $(`#review-popup textarea[name=${key}]`).siblings('.invalid-feedback').html(value)
                        return true
                    }

                    $(`#review-popup input[name=${key}]`).addClass('error')

                    if (key === 'rating') {
                        $(`#review-popup input[name=${key}]`).closest('.rating-area').siblings('.invalid-feedback').html(value)
                        return true
                    }

                    $(`#review-popup input[name=${key}]`).siblings('.invalid-feedback').html(value)
                })
            }

            if (json['success']) {
                $('#review-popup .form6').html(`<div class="field">${json['success']}</div>`)
            }
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
    let favorite = JSON.parse(localStorage.getItem("favorite") || '{"lawyers":[],"experts":[],"notaries":[]}');
    let form = '#get-favorite';
    let url = new URL(window.location);
    let data = $(`${form} input[type=hidden]`).serializeArray();

    // Добавляем фавориты из localStorage
    data.push({name: 'favorite', value: JSON.stringify(favorite)});

    $.ajax({
        type: "GET",
        url: "/actions/blocks/get_favorite.php",
        dataType: 'json',
        data: data,
        success: function (json) {
            $('.index-catalog-block .pages').remove();
            $('.index-catalog-block .bottom-button').remove();

            $('.index-catalog-block .grid_flex').append(json.specialists);
            if (json.pagination) {
                $('.index-catalog-block .grid_flex').after(json.pagination);
            }

            url.searchParams.set('page', json.page);
            history.pushState(null, null, url);
        }
    });
});


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

$(document).ready(function () {
    const phoneInputs = document.querySelectorAll('.phone-input-reg');

    const maskOptions = {
        mask: '+{7} (000) 000-00-00'
    };

    phoneInputs.forEach(function (input) {
        IMask(input, maskOptions);
    });
});

(function () {
    const form = document.querySelector('.form6');
    if (!form) return;

    form.addEventListener('input', function (e) {
        const el = e.target;
        if (!el.classList.contains('inp') || !el.hasAttribute('maxlength')) return;

        const max = Number(el.getAttribute('maxlength'));
        if (el.value.length > max) {
            el.value = el.value.slice(0, max); // обрезаем лишнее сразу
        }

        const curLen = el.value.length;
        const atMax = curLen >= max;

        const field = el.closest('.field');
        if (!field) return;
        const fb = field.querySelector('.invalid-feedback');
        if (!fb) return;

        const errorText = 'Достигнуто максимальное количество символов';

        if (atMax) {
            el.classList.add('is-invalid');
            fb.textContent = errorText; // ← вставляем нужный текст
            fb.style.display = 'block';
        } else {
            el.classList.remove('is-invalid');
            fb.textContent = '';
            fb.style.display = '';
        }
    });
})();

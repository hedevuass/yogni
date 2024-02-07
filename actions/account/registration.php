<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/send_mail.php';

if (isset($_POST['classification'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    if (!$data['name']) $json['error']['name'] = 'Укажите ваше имя';
    if (!$data['lastname']) $json['error']['lastname'] = 'Укажите вашу фамилию';

    $pattern = '/^((\+?7|8)(?!95[4-7]|99[08]|907|94[^09]|336)([348]\d|9[0-6789]|7[0247])\d{8}|\+?(99[^4568]\d{7,11}|994\d{9}|9955\d{8}|996[57]\d{8}|9989\d{8}|380[34569]\d{8}|375[234]\d{8}|372\d{7,8}|37[0-4]\d{8}))$/';

    if (!$data['phone']) $json['error']['phone'] = 'Укажите номер телефона';

    if ($data['phone']) {
        $data['phone'] = preg_replace("/[^0-9]/", '', $data['phone']);

        if (substr($data['phone'], 0, 1) == 8) $data['phone'][0] = 7;

        if (!preg_match($pattern, $data['phone'])) $json['error']['phone'] = 'Некорректный номер телефона';
    }

    if (!$data['email']) $json['error']['email'] = 'Укажите email';

    if ($data['email']) {
        if (!filter_var($data['email'], FILTER_SANITIZE_EMAIL)) $json['error']['email'] = 'Некорректный email';

        $email_check = check_email($data['email']);
        if ($email_check !== false) {
            if ($email_check === 2) $json['error']['email'] = '<span class="basic_text">Страница с такой почтой уже существует.</span>
            <span class="basic_text">Для получения доступа к странице <a class="recovery_popup_call">восстановите пароль</a>.</span>';
            else if ($email_check === 1) $json['error']['email_resend'] = true;
        }
    }

    if (strlen($data['password']) < 6) $json['error']['password'] = 'Минимальная длина пароля 6 символов';
    if ($data['password']) $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

    if (isset($data['company']) && !$data['company']) $json['error']['company'] = 'Укажите название компании';

    if (isset($json['error'])) responseJson($json);

    $data['hash'] = md5($data['phone'] . bin2hex(random_bytes(10)));

    $data['company_id'] = null;

    if (isset($data['company'])) {
        $data['company_id'] = set_company($data['company'], $data['classification']);
    }


    // $user_id = check_registration($data);
    $user_id = registration($data);

    if (!$user_id) {
        $json['fatal_error'] = 'Регистрация временно недоступна';
        responseJson($json);
    } else {
        set_specialist_description($user_id);
        set_specialist_contacts($user_id, $data['phone']);
        set_specialist_blog($user_id);
    }
    // $last_user_id = last_user_id();
    $title = 'ЮРФАК - завершение регистрации';
    $body = 'Для завершения регистрации перейдите по ссылке: ' . ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/action?reg&hash=' . $data['hash'] . '&id=' . $user_id;
    
    var_dump($body);
    die();
    if (send_mail_smtp($data['email'], $title, $body)) {
        $json['success'] = true;
    } else {
        $json['fatal_error'] = 'Ошибка отправки письма';
    }
    responseJson($json);
} else if (isset($_POST['email'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }

    $user = get_user_by_email($data['email']);

    if (!$user) {
        $json['fatal_error'] = 'Регистрация временно недоступна';
        responseJson($json);
    }

    $title = 'ЮРФАК - завершение регистрации';
    $body = 'Для завершения регистрации перейдите по ссылке: ' . ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/action?reg&hash=' . $user['hash'] . '&id=' . $user['id'];
    if (send_mail_smtp($data['email'], $title, $body)) {
        $json['success'] = true;
    }
    responseJson($json);
} else if (isset($_POST['spec_name']) && isset($_POST['spec_type'])) {
    foreach ($_POST as $key => $value) {
        $data[$key] = clean_elem_form($value);
    }
    if ($data['spec_type'] == 'company') {
        $specialists = check_сompany($data['spec_name']);
    } else if ($data['spec_type'] == 'specialist') {
        $name = explode(' ', $data['spec_name']);
        if ($name[0] && $name[1]) {
            $specialists = check_user_name($name[0], $name[1]);
        } else die();
    }
    if (isset($specialists) && $specialists) {
        $response = 
            '<b>Возможно ваша страница уже существует.<br>Найдены совпадения:</b>
            <div class="users-links">';
            foreach ($specialists as $specialist) {
                $response .= '<a href="/specialist/id'.$specialist['id'].'">';
                if ($data['spec_type'] == 'company') {
                    $response .= $specialist['title'].', ';
                    $classification_id = $specialist['classification'];
                } else if ($data['spec_type'] == 'specialist') {
                    $response .= $specialist['name'] . ' ' . $specialist['lastname'] . ', ';
                    $classification_id = $specialist['classification_id'];
                }
                $classification = get_classification($classification_id);
                $response .= mb_strtolower($classification['title']) . '</a>';
            }
        $response .= '</div><span>Если это не ваша страница, то продолжайте регистрацию.</span>';
        echo $response;
    }
}



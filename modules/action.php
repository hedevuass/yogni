<?php

$data = [];

if (!isset($_GET['reg']) && !isset($_GET['confirmation']) && !isset($_GET['recovery'])) responseRedirect('/');


if (isset($_GET['reg'])) {
    $data['title'] = 'Завершение регистрации';

    $user_id = clean_elem_form($_GET['id']);
    $hash = clean_elem_form($_GET['hash']);

    if (check_hash($hash, $user_id)) {
        $hash = md5($user_id . bin2hex(random_bytes(15)));

        $activate = activate_user($hash, $user_id);

        if ($activate) $data['content'] = 'Регистрация прошла успешно, вы можете авторизоваться';
        if (!$activate) $data['content'] = 'Подтверждение регистрации временно недоступно!';
    } else {
        responseRedirect('/');
    }
}

if (isset($_GET['recovery'])) {
    $data['title'] = 'Восстановление пароля';

    $specialist_id = clean_elem_form($_GET['id']);
    $hash = clean_elem_form($_GET['hash']);

    if (check_hash($hash, $specialist_id)) {
        $data['content'] = <<<EOF
            <form method="POST" id="recovery-password-popup-2">
                <input type="hidden" name="hash" value="{$hash}">
                <input type="hidden" name="specialist_id" value="{$specialist_id}">
                <div class="field" style="position:relative;margin-bottom: 1rem;">
                    <input  type="password" 
                            name="password" 
                            placeholder="Пароль*" 
                            class="inp" 
                            oninput="this.value=this.value.replace(/[^\w\d]*/gi,"");" 
                            style=" width: 400px;
                                    height: 34px;
                                    font-weight: 300;
                                    padding: 0 12px;
                                    border: 1px solid #B0B0B0;
                                    border-radius: 8px;">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="button">
                    <button type="submit" style="
                                                background: #fff;
                                                font-weight: 500;
                                                font-size: 16px;
                                                color: #4F77B4;
                                                border: 2px solid #4F77B4;
                                                border-radius: 8px;
                                                padding: 0.5rem 2rem;
                                                height: 40px;
                ">Изменить пароль</button>
                </div>
            </form>
        EOF;
    } else {
        responseRedirect('/');
    }
}
if (isset($_GET['confirmation'])) {
    $data['title'] = 'Придумайте пароль';

    $specialist_id = clean_elem_form($_GET['id']);
    $email = clean_elem_form($_GET['email']);

   
        $data['content'] = <<<EOF
            <form method="POST" id="rights-confirmation-popup-2">
                <input type="hidden" name="specialist_id" value="{$specialist_id}">
                <input type="hidden" name="email" value="{$email}">
                <div class="field" style="position:relative;margin-bottom: 1rem;">
                    <input  type="password" 
                            name="password" 
                            placeholder="Пароль*" 
                            class="inp" 
                            oninput="this.value=this.value.replace(/[^\w\d]*/gi,"");" 
                            style=" width: 400px;
                                    height: 34px;
                                    font-weight: 300;
                                    padding: 0 12px;
                                    border: 1px solid #B0B0B0;
                                    border-radius: 8px;">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="button">
                    <button type="submit" style="
                                                background: #fff;
                                                font-weight: 500;
                                                font-size: 16px;
                                                color: #4F77B4;
                                                border: 2px solid #4F77B4;
                                                border-radius: 8px;
                                                padding: 0.5rem 2rem;
                                                height: 40px;
                ">Подтвердить права</button>
                </div>
            </form>
        EOF;
    
}

// Устанавливаем title
$HTML = str_replace( '<!--title-->' , $data['title'] , $HTML );

$HTML_ = explode( '<!--data:title-->' , $HTML , 3 );
$HTML = $HTML_[0] . "<!--data:title-->{$data['title']}<!--data:title-->" . $HTML_[2];

$HTML_ = explode( '<!--data:content-->' , $HTML , 3 );
$HTML = $HTML_[0] . "<!--data:content-->{$data['content']}<!--data:content-->" . $HTML_[2];
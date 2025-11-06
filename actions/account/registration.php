<?php
include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/actions/mail/send_mail.php';

require_once __DIR__ . '/../../libs/securimage/securimage.php';

//Социальные сети
$socials = get_all_social();
$social_html = '<tr style="border-collapse:collapse">';
foreach ($socials as $social) {
  if (!empty($social['link'])) {
    $image_src = 'https://urfaq.ru/assets/img/login-parol/';
    $height = 24;
    $width = 30;

    switch ($social['alt']) {
      case 'Youtube':
        $image_src .= $social['image'];
        $height = 24;
        $width = 30;
        break;
      case 'Telegram':
        $image_src .= $social['image'];
        $height = 24;
        $width = 24;
        break;
      case 'Vk':
        $image_src .= $social['image'];
        $height = 24;
        $width = 24;
        break;
      case 'Yandex':
        $image_src .= $social['image'];
        $height = 24;
        $width = 24;
        break;
      default:
        $image_src .= $social['image'];
        break;
    }

    $social_html .= '
            <td align="center" valign="top" style="padding:0;Margin:0;padding-right:15px">
                <a target="_blank" href="' . htmlspecialchars($social['link']) . '" 
                   style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;text-decoration:none;color:#3E8EB8;font-size:14px">
                    <img src="' . $image_src . '" 
                         alt="' . htmlspecialchars($social['alt']) . '" 
                         title="' . ucfirst(htmlspecialchars($social['alt'])) . '" 
                         height="' . $height . '" 
                         width="' . $width . '" 
                         style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic">
                </a>
            </td>';
  }
}

$social_html .= '</tr>';

if (isset($_POST['classification']) && isset($_POST['email']) && isset($_POST['password'])) {
  $securimage = new Securimage();

  // Определяем, какая форма — специалист или компания
  $classification = intval($_POST['classification']);
  if ($classification <= 4) { // специалист
    $captchaCode = $_POST['captcha_code'] ?? '';
    $captchaId = $_POST['captcha_id2'] ?? null;
  } else { // компания
    $captchaCode = $_POST['captcha_code_registration_company'] ?? '';
    $captchaId = $_POST['captcha_id_registration_company'] ?? null;
  }

  // собираем данные из формы
  foreach ($_POST as $key => $value) {
    $data[$key] = clean_elem_form($value);
  }

  $json = ['error' => []];

  // проверка капчи
  if (!$captchaCode) {
    $json['error']['captcha_error'] = 'Введите код с картинки.';
  } elseif (!$securimage->check($captchaCode, $captchaId)) {
    $json['error']['captcha_error'] = 'Неправильный код каптчи. Попробуйте снова.';
  }

  // проверка других полей
  if (!$data['name']) $json['error']['name'] = 'Укажите ваше имя';
  if (!$data['lastname']) $json['error']['lastname'] = 'Укажите вашу фамилию';

  $pattern = '/^((\+?7|8)(?!95[4-7]|99[08]|907|94[^09]|336)([348]\d|9[0-6789]|7[0247])\d{8}|\+?(99[^4568]\d{7,11}|994\d{9}|9955\d{8}|996[57]\d{8}|9989\d{8}|380[34569]\d{8}|375[234]\d{8}|372\d{7,8}|37[0-4]\d{8}))$/';
  if ($data['phone']) {
    $data['phone'] = preg_replace("/[^0-9]/", '', $data['phone']);
    if (substr($data['phone'], 0, 1) == 8) $data['phone'][0] = 7;
    if (!preg_match($pattern, $data['phone'])) $json['error']['phone'] = 'Некорректный номер телефона';
  } else $json['error']['phone'] = 'Укажите номер телефона';

  if ($data['email']) {
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
      $json['error']['email'] = 'Некорректный email';
    }

    $email_check = check_email($data['email']);

    if ($email_check !== false) {
      if ($email_check == 2) {
        // Пробуем очистить email у удалённого пользователя
        if (!clear_deleted_user_email_by_email($data['email'])) {
          $json['error']['email'] = '
                    <span class="basic_text">Страница с такой почтой уже существует.</span>
                    <span class="basic_text">Для получения доступа <a class="recovery_popup_call">восстановите пароль</a>.</span>';
        }
      } elseif ($email_check == 1) {
        $json['error']['email'] = '
                <span class="basic_text">На данную почту уже отправлено письмо со ссылкой для подтверждения электронного адреса.</span>
                <span class="basic_text">Обратитесь в поддержку, если вы не получили письмо.</span>';
      }
    }
  } else {
    $json['error']['email'] = 'Укажите email';
  }

  if (mb_strlen($data['password'], 'UTF-8') < 6) $json['error']['password'] = 'Минимальная длина пароля 6 символов';
  if ($data['password']) $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

  if ($classification > 4 && isset($data['company']) && !$data['company']) $json['error']['company'] = 'Укажите название компании';

  // если есть хоть одна ошибка, возвращаем JSON с ними всеми
  if (!empty($json['error'])) {
    responseJson($json);
  }

  $data['hash'] = md5($data['phone'] . bin2hex(random_bytes(10)));

  $data['company_id'] = null;

  if (isset($data['company'])) {
    $data['company_id'] = set_company($data['company'], $data['classification']);
  }


  // $user_id = check_registration($data);
  $user_id = registration($data);

  if (!$user_id) {
    $json['fatal_error'] = 'Регистрация временно недоступна';
  } else {
    if (set_specialist_description($user_id) && set_specialist_contacts($user_id, $data['phone']) && set_specialist_blog($user_id)) {
      $title = 'ЮРФАК - регистрация';
      if (!empty($data['company'])) {
        $email_name = $data['company'];
      } else {
        $email_name = $data['name'] . ' ' . $data['lastname'];
      }
      $site_url = $link_url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
      $link_url .= '/action?reg&user_id=' . $user_id . '&hash=' . $data['hash'];
      $body = <<<EOF
    <html dir="ltr" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office" lang="ru" style="padding:0;Margin:0">
 <head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <meta name="x-apple-disable-message-reformatting">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="telephone=no" name="format-detection">
  <title>юрфак</title><!--[if (mso 16)]>
    <style type="text/css">
    a {text-decoration: none;}
    </style>
    <![endif]--><!--[if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]--><!--[if gte mso 9]>
<xml>
    <o:OfficeDocumentSettings>
    <o:AllowPNG></o:AllowPNG>
    <o:PixelsPerInch>96</o:PixelsPerInch>
    </o:OfficeDocumentSettings>
</xml>
<![endif]-->
  <style type="text/css">
#outlook a {
	padding:0;
}
.ExternalClass {
	width:100%;
}
.ExternalClass,
.ExternalClass p,
.ExternalClass span,
.ExternalClass font,
.ExternalClass td,
.ExternalClass div {
	line-height:100%;
}
.es-button {
	mso-style-priority:100!important;
	text-decoration:none!important;
}
a[x-apple-data-detectors] {
	color:inherit!important;
	text-decoration:none!important;
	font-size:inherit!important;
	font-family:inherit!important;
	font-weight:inherit!important;
	line-height:inherit!important;
}
.es-desk-hidden {
	display:none;
	float:left;
	overflow:hidden;
	width:0;
	max-height:0;
	line-height:0;
	mso-hide:all;
}
@media only screen and (max-width:600px) {p, ul li, ol li, a { line-height:150%!important } h1, h2, h3, h1 a, h2 a, h3 a { line-height:120%!important } h1 { font-size:30px!important; text-align:left } h2 { font-size:18px!important; text-align:left } h3 { font-size:14px!important; text-align:left } h1 a { text-align:left } .es-header-body h1 a, .es-content-body h1 a, .es-footer-body h1 a { font-size:30px!important } h2 a { text-align:left } .es-header-body h2 a, .es-content-body h2 a, .es-footer-body h2 a { font-size:18px!important } h3 a { text-align:left } .es-header-body h3 a, .es-content-body h3 a, .es-footer-body h3 a { font-size:14px!important } .es-menu td a { font-size:10px!important } .es-header-body p, .es-header-body ul li, .es-header-body ol li, .es-header-body a { font-size:12px!important } .es-content-body p, .es-content-body ul li, .es-content-body ol li, .es-content-body a { font-size:10px!important } .es-footer-body p, .es-footer-body ul li, .es-footer-body ol li, .es-footer-body a { font-size:12px!important } .es-infoblock p, .es-infoblock ul li, .es-infoblock ol li, .es-infoblock a { font-size:12px!important } *[class="gmail-fix"] { display:none!important } .es-m-txt-c, .es-m-txt-c h1, .es-m-txt-c h2, .es-m-txt-c h3 { text-align:center!important } .es-m-txt-r, .es-m-txt-r h1, .es-m-txt-r h2, .es-m-txt-r h3 { text-align:right!important } .es-m-txt-l, .es-m-txt-l h1, .es-m-txt-l h2, .es-m-txt-l h3 { text-align:left!important } .es-m-txt-r img, .es-m-txt-c img, .es-m-txt-l img { display:inline!important } .es-button-border { display:block!important } a.es-button, button.es-button { font-size:12px!important; display:block!important; border-left-width:0px!important; border-right-width:0px!important; padding-left:0px!important; padding-right:0px!important } .es-btn-fw { border-width:10px 0px!important; text-align:center!important } .es-adaptive table, .es-btn-fw, .es-btn-fw-brdr, .es-left, .es-right { width:100%!important } .es-content table, .es-header table, .es-footer table, .es-content, .es-footer, .es-header { width:100%!important; max-width:600px!important } .es-adapt-td { display:block!important; width:100%!important } .adapt-img { width:100%!important; height:auto!important } .es-m-p0 { padding:0!important } .es-m-p0r { padding-right:0!important } .es-m-p0l { padding-left:0!important } .es-m-p0t { padding-top:0!important } .es-m-p0b { padding-bottom:0!important } .es-m-p20b { padding-bottom:20px!important } .es-mobile-hidden, .es-hidden { display:none!important } tr.es-desk-hidden, td.es-desk-hidden, table.es-desk-hidden { width:auto!important; overflow:visible!important; float:none!important; max-height:inherit!important; line-height:inherit!important } tr.es-desk-hidden { display:table-row!important } table.es-desk-hidden { display:table!important } td.es-desk-menu-hidden { display:table-cell!important } .es-menu td { width:1%!important } table.es-table-not-adapt, .esd-block-html table { width:auto!important } table.es-social { display:inline-block!important } table.es-social td { display:inline-block!important } .es-desk-hidden { display:table-row!important; width:auto!important; overflow:visible!important; max-height:inherit!important } .es-m-p5 { padding:5px!important } .es-m-p5t { padding-top:5px!important } .es-m-p5b { padding-bottom:5px!important } .es-m-p5r { padding-right:5px!important } .es-m-p5l { padding-left:5px!important } .es-m-p10 { padding:10px!important } .es-m-p10t { padding-top:10px!important } .es-m-p10b { padding-bottom:10px!important } .es-m-p10r { padding-right:10px!important } .es-m-p10l { padding-left:10px!important } .es-m-p15 { padding:15px!important } .es-m-p15t { padding-top:15px!important } .es-m-p15b { padding-bottom:15px!important } .es-m-p15r { padding-right:15px!important } .es-m-p15l { padding-left:15px!important } .es-m-p20 { padding:20px!important } .es-m-p20t { padding-top:20px!important } .es-m-p20r { padding-right:20px!important } .es-m-p20l { padding-left:20px!important } .es-m-p25 { padding:25px!important } .es-m-p25t { padding-top:25px!important } .es-m-p25b { padding-bottom:25px!important } .es-m-p25r { padding-right:25px!important } .es-m-p25l { padding-left:25px!important } .es-m-p30 { padding:30px!important } .es-m-p30t { padding-top:30px!important } .es-m-p30b { padding-bottom:30px!important } .es-m-p30r { padding-right:30px!important } .es-m-p30l { padding-left:30px!important } .es-m-p35 { padding:35px!important } .es-m-p35t { padding-top:35px!important } .es-m-p35b { padding-bottom:35px!important } .es-m-p35r { padding-right:35px!important } .es-m-p35l { padding-left:35px!important } .es-m-p40 { padding:40px!important } .es-m-p40t { padding-top:40px!important } .es-m-p40b { padding-bottom:40px!important } .es-m-p40r { padding-right:40px!important } .es-m-p40l { padding-left:40px!important } .es-m-margin { padding-left:5px!important; padding-right:5px!important; padding-top:5px!important; padding-bottom:5px!important } }
@media screen and (max-width:384px) {.mail-message-content { width:414px!important } }
</style>
 </head>
 <body style="font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;width:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0">
  <div dir="ltr" class="es-wrapper-color" lang="ru" style="background-color:#F5F5F5"><!--[if gte mso 9]>
			<v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
				<v:fill type="tile" color="#f5f5f5"></v:fill>
			</v:background>
		<![endif]-->
   <table class="es-wrapper" width="100%" cellspacing="0" cellpadding="0" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;padding:0;Margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top;background-color:#F5F5F5">
     <tr style="border-collapse:collapse">
      <td class="es-m-margin" valign="top" style="padding:0;Margin:0">
       <table class="es-content es-mobile-hidden" cellspacing="0" cellpadding="0" align="center" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%">
         <tr style="border-collapse:collapse">
          <td align="center" style="padding:0;Margin:0">
           <table class="es-content-body" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;width:600px" cellspacing="0" cellpadding="0" align="center" role="none">
             <tr style="border-collapse:collapse">
              <td align="left" style="padding:0;Margin:0">
               <table width="100%" cellspacing="0" cellpadding="0" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                 <tr style="border-collapse:collapse">
                  <td valign="top" align="center" style="padding:0;Margin:0;width:600px">
                   <table width="100%" cellspacing="0" cellpadding="0" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                     <tr style="border-collapse:collapse">
                      <td align="center" height="20" style="padding:0;Margin:0"></td>
                     </tr>
                   </table></td>
                 </tr>
               </table></td>
             </tr>
           </table></td>
         </tr>
       </table>
       <table cellpadding="0" cellspacing="0" class="es-content" align="center" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%">
         <tr style="border-collapse:collapse">
          <td align="center" style="padding:0;Margin:0">
           <table class="es-content-body" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;background-color:#ededed;border-radius:25px;width:600px" bgcolor="#EDEDED" role="none">
             <tr style="border-collapse:collapse">
              <td align="left" style="padding:0;Margin:0">
               <table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                 <tr style="border-collapse:collapse">
                  <td align="center" valign="top" style="padding:0;Margin:0;width:600px">
                   <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                     <tr class="es-mobile-hidden" style="border-collapse:collapse">
                     <td align="center" style="padding:0;Margin:0;font-size:0px"><img class="adapt-img" src="https://urfaq.ru/assets/img/login-parol/group_6249.png" alt="" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic" width="600" height="226"></td>
                     </tr><!--[if !mso]><!-- -->
                     <tr class="es-desk-hidden" style="display:none;float:left;overflow:hidden;width:0;max-height:0;line-height:0;mso-hide:all;border-collapse:collapse">
                      <td align="center" style="padding:0;Margin:0;font-size:0px"><img class="adapt-img" src="https://urfaq.ru/assets/img/login-parol/group_6252.png" alt style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic" width="600" height="377"></td>
                     </tr><!--<![endif]-->
                   </table></td>
                 </tr>
               </table></td>
             </tr>
             <tr style="border-collapse:collapse">
              <td class="es-m-p20r es-m-p20l" align="left" bgcolor="#ffffff" style="padding:30px;Margin:0;background-color:#ffffff;border-radius:0px 0px 25px 25px">
               <table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                 <tr style="border-collapse:collapse">
                  <td align="center" valign="top" style="padding:0;Margin:0;width:540px">
                   <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                     <tr style="border-collapse:collapse">
                      <td class="es-m-txt-l" align="left" style="padding:0;Margin:0"><h2 style="Margin:0;line-height:22px;mso-line-height-rule:exactly;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;font-size:18px;font-style:normal;font-weight:normal;color:#373737">Здравствуйте, {$email_name}!</h2></td>
                     </tr>
                     <tr style="border-collapse:collapse">
                      <td class="es-m-txt-l" align="left" style="padding:0;Margin:0;padding-top:30px"><h3 style="Margin:0;line-height:17px;mso-line-height-rule:exactly;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;font-size:14px;font-style:normal;font-weight:normal;color:#373737">Вы почти у цели.</h3></td>
                     </tr>
                     <tr style="border-collapse:collapse">
                      <td class="es-m-txt-l" align="left" style="padding:0;Margin:0;padding-top:5px;padding-bottom:20px"><h3 style="Margin:0;line-height:17px;mso-line-height-rule:exactly;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;font-size:14px;font-style:normal;font-weight:normal;color:#373737">Для начала работы подтвердите адрес почты.</h3></td>
                     </tr>
                     <tr style="border-collapse:collapse">
                      <td align="center" style="padding:0;Margin:0;padding-left:15px;padding-right:15px;padding-top:40px"><span class="es-button-border" style="border-style:solid;border-color:transparent;background:#ff8139;border-width:0px;display:block;border-radius:32px;width:auto"><a href="{$link_url}" class="es-button es-button-1701729247556" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;color:#FFFFFF;font-size:12px;display:block;background:#ff8139;border-radius:32px;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-weight:normal;font-style:normal;line-height:14px;width:auto;text-align:center;padding:15px 5px;mso-padding-alt:0;mso-border-alt:10px solid #ff8139">Подтвердить</a></span></td>
                     </tr>
                   </table></td>
                 </tr>
               </table></td>
             </tr>
             <tr style="border-collapse:collapse">
             <td class="es-m-p20r es-m-p20l" align="left" style="padding:40px;Margin:0">
             <table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                 <tbody><tr style="border-collapse:collapse">
                 <td align="center" valign="top" style="padding:0;Margin:0;width:520px">
                 <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                     <tbody><tr style="border-collapse:collapse">
                     <td align="center" class="es-m-p0t" style="padding:0;Margin:0;padding-top:20px;padding-bottom:40px;font-size:0">
                     <table cellpadding="0" cellspacing="0" class="es-table-not-adapt es-social" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                        <tbody>
                         {$social_html}
                         </tbody></table></td>
                         </tr>
                         <tr style="border-collapse:collapse">
                         <td class="es-m-txt-l es-m-p0t" align="left" style="padding:0;Margin:0;padding-top:10px"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:14px;color:#373737;font-size:12px">Поддержка: <strong><a target="_blank" href="mailto:support@urfaq.ru" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;text-decoration:none;color:#373737;font-size:12px">support@urfaq.ru</a></strong></p></td>
                         </tr>
                         <tr style="border-collapse:collapse">
                         <td class="es-m-txt-l" align="left" style="padding:0;Margin:0;padding-top:10px"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:14px;color:#373737;font-size:12px">ИП Швырков ИД ИНН 772788549260</p></td>
                         </tr>
                         <tr style="border-collapse:collapse">
                         <td class="es-m-txt-l es-m-p0b" align="left" style="padding:0;Margin:0;padding-bottom:10px;padding-top:15px"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:14px;color:#373737;font-size:12px">Вы получили это письмо, так как по этому адресу был зарегистрирован аккаунт на <a target="_blank" href="{$site_url}">urfaq.ru</a> Если вы не регистрировались на сайте, просто проигнорируйте это сообщение.</p></td>
                         </tr>
                         <tr style="border-collapse:collapse">
                         <td class="es-m-txt-c es-m-p35t" align="center" style="padding:0;Margin:0;padding-top:40px"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:17px;color:#373737;font-size:14px">ЮРФАК 2025 © - удобный сервис по поиску лучших юристов, адвокатов, экспертов и нотариусов. Все права защищены.</p></td>
                         </tr>
                         <tr style="border-collapse:collapse">
                         <td align="center" style="padding:0;Margin:0;padding-top:25px;font-size:0px"><a target="_blank" href="{$site_url}" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;text-decoration:none;color:#3E8EB8;font-size:14px"><img src="https://urfaq.ru/assets/img/login-parol/urfakru.png" alt="" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic" width="105" height="15"></a></td>
                         </tr>
                        </tbody>
                     </table></td>
                 </tr>
             </tbody></table></td>
             </tr>
         </tbody></table></td>
         </tr>
     </tbody></table></td>
     </tr>
     </tbody></table>
    </div>
 </body>
</html>
EOF;
      if (send_mail_smtp($data['email'], $title, $body)) {
        $json['success'] = true;
      } else {
        $json['fatal_error'] = 'Ошибка отправки письма, проверьте корректность введенной вами почты.';
      }
    } else {
      $json['fatal_error'] = 'Регистрация временно недоступна';
    }
  }
  responseJson($json);
} else if (isset($_POST['email']) && isset($_POST['type'])) {
  foreach ($_POST as $key => $value) {
    $data[$key] = clean_elem_form($value);
  }
  if ($data['type'] == 'resend-confirmation') {

    $user = get_user_by_email_reg($data['email']);

    if (!$user) {
      $json['fatal_error'] = 'Регистрация временно недоступна';
      responseJson($json);
    }

    $title = 'ЮРФАК - регистрация';
    $email_name = $user['name'] . ' ' . $user['lastname'];
    $site_url = $link_url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $link_url .= '/action?reg&user_id=' . $user['id'] . '&hash=' . $user['hash'];
    $body = <<<EOF
    <html dir="ltr" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office" lang="ru" style="padding:0;Margin:0">
 <head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <meta name="x-apple-disable-message-reformatting">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="telephone=no" name="format-detection">
  <title>юрфак</title><!--[if (mso 16)]>
    <style type="text/css">
    a {text-decoration: none;}
    </style>
    <![endif]--><!--[if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]--><!--[if gte mso 9]>
<xml>
    <o:OfficeDocumentSettings>
    <o:AllowPNG></o:AllowPNG>
    <o:PixelsPerInch>96</o:PixelsPerInch>
    </o:OfficeDocumentSettings>
</xml>
<![endif]-->
  <style type="text/css">
#outlook a {
	padding:0;
}
.ExternalClass {
	width:100%;
}
.ExternalClass,
.ExternalClass p,
.ExternalClass span,
.ExternalClass font,
.ExternalClass td,
.ExternalClass div {
	line-height:100%;
}
.es-button {
	mso-style-priority:100!important;
	text-decoration:none!important;
}
a[x-apple-data-detectors] {
	color:inherit!important;
	text-decoration:none!important;
	font-size:inherit!important;
	font-family:inherit!important;
	font-weight:inherit!important;
	line-height:inherit!important;
}
.es-desk-hidden {
	display:none;
	float:left;
	overflow:hidden;
	width:0;
	max-height:0;
	line-height:0;
	mso-hide:all;
}
@media only screen and (max-width:600px) {p, ul li, ol li, a { line-height:150%!important } h1, h2, h3, h1 a, h2 a, h3 a { line-height:120%!important } h1 { font-size:30px!important; text-align:left } h2 { font-size:18px!important; text-align:left } h3 { font-size:14px!important; text-align:left } h1 a { text-align:left } .es-header-body h1 a, .es-content-body h1 a, .es-footer-body h1 a { font-size:30px!important } h2 a { text-align:left } .es-header-body h2 a, .es-content-body h2 a, .es-footer-body h2 a { font-size:18px!important } h3 a { text-align:left } .es-header-body h3 a, .es-content-body h3 a, .es-footer-body h3 a { font-size:14px!important } .es-menu td a { font-size:10px!important } .es-header-body p, .es-header-body ul li, .es-header-body ol li, .es-header-body a { font-size:12px!important } .es-content-body p, .es-content-body ul li, .es-content-body ol li, .es-content-body a { font-size:10px!important } .es-footer-body p, .es-footer-body ul li, .es-footer-body ol li, .es-footer-body a { font-size:12px!important } .es-infoblock p, .es-infoblock ul li, .es-infoblock ol li, .es-infoblock a { font-size:12px!important } *[class="gmail-fix"] { display:none!important } .es-m-txt-c, .es-m-txt-c h1, .es-m-txt-c h2, .es-m-txt-c h3 { text-align:center!important } .es-m-txt-r, .es-m-txt-r h1, .es-m-txt-r h2, .es-m-txt-r h3 { text-align:right!important } .es-m-txt-l, .es-m-txt-l h1, .es-m-txt-l h2, .es-m-txt-l h3 { text-align:left!important } .es-m-txt-r img, .es-m-txt-c img, .es-m-txt-l img { display:inline!important } .es-button-border { display:block!important } a.es-button, button.es-button { font-size:12px!important; display:block!important; border-left-width:0px!important; border-right-width:0px!important; padding-left:0px!important; padding-right:0px!important } .es-btn-fw { border-width:10px 0px!important; text-align:center!important } .es-adaptive table, .es-btn-fw, .es-btn-fw-brdr, .es-left, .es-right { width:100%!important } .es-content table, .es-header table, .es-footer table, .es-content, .es-footer, .es-header { width:100%!important; max-width:600px!important } .es-adapt-td { display:block!important; width:100%!important } .adapt-img { width:100%!important; height:auto!important } .es-m-p0 { padding:0!important } .es-m-p0r { padding-right:0!important } .es-m-p0l { padding-left:0!important } .es-m-p0t { padding-top:0!important } .es-m-p0b { padding-bottom:0!important } .es-m-p20b { padding-bottom:20px!important } .es-mobile-hidden, .es-hidden { display:none!important } tr.es-desk-hidden, td.es-desk-hidden, table.es-desk-hidden { width:auto!important; overflow:visible!important; float:none!important; max-height:inherit!important; line-height:inherit!important } tr.es-desk-hidden { display:table-row!important } table.es-desk-hidden { display:table!important } td.es-desk-menu-hidden { display:table-cell!important } .es-menu td { width:1%!important } table.es-table-not-adapt, .esd-block-html table { width:auto!important } table.es-social { display:inline-block!important } table.es-social td { display:inline-block!important } .es-desk-hidden { display:table-row!important; width:auto!important; overflow:visible!important; max-height:inherit!important } .es-m-p5 { padding:5px!important } .es-m-p5t { padding-top:5px!important } .es-m-p5b { padding-bottom:5px!important } .es-m-p5r { padding-right:5px!important } .es-m-p5l { padding-left:5px!important } .es-m-p10 { padding:10px!important } .es-m-p10t { padding-top:10px!important } .es-m-p10b { padding-bottom:10px!important } .es-m-p10r { padding-right:10px!important } .es-m-p10l { padding-left:10px!important } .es-m-p15 { padding:15px!important } .es-m-p15t { padding-top:15px!important } .es-m-p15b { padding-bottom:15px!important } .es-m-p15r { padding-right:15px!important } .es-m-p15l { padding-left:15px!important } .es-m-p20 { padding:20px!important } .es-m-p20t { padding-top:20px!important } .es-m-p20r { padding-right:20px!important } .es-m-p20l { padding-left:20px!important } .es-m-p25 { padding:25px!important } .es-m-p25t { padding-top:25px!important } .es-m-p25b { padding-bottom:25px!important } .es-m-p25r { padding-right:25px!important } .es-m-p25l { padding-left:25px!important } .es-m-p30 { padding:30px!important } .es-m-p30t { padding-top:30px!important } .es-m-p30b { padding-bottom:30px!important } .es-m-p30r { padding-right:30px!important } .es-m-p30l { padding-left:30px!important } .es-m-p35 { padding:35px!important } .es-m-p35t { padding-top:35px!important } .es-m-p35b { padding-bottom:35px!important } .es-m-p35r { padding-right:35px!important } .es-m-p35l { padding-left:35px!important } .es-m-p40 { padding:40px!important } .es-m-p40t { padding-top:40px!important } .es-m-p40b { padding-bottom:40px!important } .es-m-p40r { padding-right:40px!important } .es-m-p40l { padding-left:40px!important } .es-m-margin { padding-left:5px!important; padding-right:5px!important; padding-top:5px!important; padding-bottom:5px!important } }
@media screen and (max-width:384px) {.mail-message-content { width:414px!important } }
</style>
 </head>
 <body style="font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;width:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0">
  <div dir="ltr" class="es-wrapper-color" lang="ru" style="background-color:#F5F5F5"><!--[if gte mso 9]>
			<v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
				<v:fill type="tile" color="#f5f5f5"></v:fill>
			</v:background>
		<![endif]-->
   <table class="es-wrapper" width="100%" cellspacing="0" cellpadding="0" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;padding:0;Margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top;background-color:#F5F5F5">
     <tr style="border-collapse:collapse">
      <td class="es-m-margin" valign="top" style="padding:0;Margin:0">
       <table class="es-content es-mobile-hidden" cellspacing="0" cellpadding="0" align="center" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%">
         <tr style="border-collapse:collapse">
          <td align="center" style="padding:0;Margin:0">
           <table class="es-content-body" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;width:600px" cellspacing="0" cellpadding="0" align="center" role="none">
             <tr style="border-collapse:collapse">
              <td align="left" style="padding:0;Margin:0">
               <table width="100%" cellspacing="0" cellpadding="0" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                 <tr style="border-collapse:collapse">
                  <td valign="top" align="center" style="padding:0;Margin:0;width:600px">
                   <table width="100%" cellspacing="0" cellpadding="0" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                     <tr style="border-collapse:collapse">
                      <td align="center" height="20" style="padding:0;Margin:0"></td>
                     </tr>
                   </table></td>
                 </tr>
               </table></td>
             </tr>
           </table></td>
         </tr>
       </table>
       <table cellpadding="0" cellspacing="0" class="es-content" align="center" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%">
         <tr style="border-collapse:collapse">
          <td align="center" style="padding:0;Margin:0">
           <table class="es-content-body" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;background-color:#ededed;border-radius:25px;width:600px" bgcolor="#EDEDED" role="none">
             <tr style="border-collapse:collapse">
              <td align="left" style="padding:0;Margin:0">
               <table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                 <tr style="border-collapse:collapse">
                  <td align="center" valign="top" style="padding:0;Margin:0;width:600px">
                   <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                     <tr class="es-mobile-hidden" style="border-collapse:collapse">
                     <td align="center" style="padding:0;Margin:0;font-size:0px"><img class="adapt-img" src="https://urfaq.ru/assets/img/login-parol/group_6249.png" alt="" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic" width="600" height="226"></td>
                     </tr><!--[if !mso]><!-- -->
                     <tr class="es-desk-hidden" style="display:none;float:left;overflow:hidden;width:0;max-height:0;line-height:0;mso-hide:all;border-collapse:collapse">
                      <td align="center" style="padding:0;Margin:0;font-size:0px"><img class="adapt-img" src="https://urfaq.ru/assets/img/login-parol/group_6252.png" alt style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic" width="600" height="377"></td>
                     </tr><!--<![endif]-->
                   </table></td>
                 </tr>
               </table></td>
             </tr>
             <tr style="border-collapse:collapse">
              <td class="es-m-p20r es-m-p20l" align="left" bgcolor="#ffffff" style="padding:30px;Margin:0;background-color:#ffffff;border-radius:0px 0px 25px 25px">
               <table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                 <tr style="border-collapse:collapse">
                  <td align="center" valign="top" style="padding:0;Margin:0;width:540px">
                   <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                     <tr style="border-collapse:collapse">
                      <td class="es-m-txt-l" align="left" style="padding:0;Margin:0"><h2 style="Margin:0;line-height:22px;mso-line-height-rule:exactly;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;font-size:18px;font-style:normal;font-weight:normal;color:#373737">Здравствуйте, {$email_name}!</h2></td>
                     </tr>
                     <tr style="border-collapse:collapse">
                      <td class="es-m-txt-l" align="left" style="padding:0;Margin:0;padding-top:30px"><h3 style="Margin:0;line-height:17px;mso-line-height-rule:exactly;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;font-size:14px;font-style:normal;font-weight:normal;color:#373737">Вы почти у цели.</h3></td>
                     </tr>
                     <tr style="border-collapse:collapse">
                      <td class="es-m-txt-l" align="left" style="padding:0;Margin:0;padding-top:5px;padding-bottom:20px"><h3 style="Margin:0;line-height:17px;mso-line-height-rule:exactly;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;font-size:14px;font-style:normal;font-weight:normal;color:#373737">Для начала работы подтвердите адрес почты.</h3></td>
                     </tr>
                     <tr style="border-collapse:collapse">
                      <td align="center" style="padding:0;Margin:0;padding-left:15px;padding-right:15px;padding-top:40px"><span class="es-button-border" style="border-style:solid;border-color:transparent;background:#ff8139;border-width:0px;display:block;border-radius:32px;width:auto"><a href="{$link_url}" class="es-button es-button-1701729247556" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;color:#FFFFFF;font-size:12px;display:block;background:#ff8139;border-radius:32px;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-weight:normal;font-style:normal;line-height:14px;width:auto;text-align:center;padding:15px 5px;mso-padding-alt:0;mso-border-alt:10px solid #ff8139">Подтвердить</a></span></td>
                     </tr>
                   </table></td>
                 </tr>
               </table></td>
             </tr>
             <tr style="border-collapse:collapse">
             <td class="es-m-p20r es-m-p20l" align="left" style="padding:40px;Margin:0">
             <table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                 <tbody><tr style="border-collapse:collapse">
                 <td align="center" valign="top" style="padding:0;Margin:0;width:520px">
                 <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                     <tbody><tr style="border-collapse:collapse">
                     <td align="center" class="es-m-p0t" style="padding:0;Margin:0;padding-top:20px;padding-bottom:40px;font-size:0">
                     <table cellpadding="0" cellspacing="0" class="es-table-not-adapt es-social" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                        <tbody>
                         {$social_html}
                         </tbody></table></td>
                         </tr>
                         <tr style="border-collapse:collapse">
                         <td class="es-m-txt-l es-m-p0t" align="left" style="padding:0;Margin:0;padding-top:10px"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:14px;color:#373737;font-size:12px">Поддержка: <strong><a target="_blank" href="mailto:support@urfaq.ru" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;text-decoration:none;color:#373737;font-size:12px">support@urfaq.ru</a></strong></p></td>
                         </tr>
                         <tr style="border-collapse:collapse">
                         <td class="es-m-txt-l" align="left" style="padding:0;Margin:0;padding-top:10px"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:14px;color:#373737;font-size:12px">ИП Швырков ИД ИНН 772788549260</p></td>
                         </tr>
                         <tr style="border-collapse:collapse">
                         <td class="es-m-txt-l es-m-p0b" align="left" style="padding:0;Margin:0;padding-bottom:10px;padding-top:15px"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:14px;color:#373737;font-size:12px">Вы получили это письмо, так как по этому адресу был зарегистрирован аккаунт на <a target="_blank" href="{$site_url}">urfaq.ru</a> Если вы не регистрировались на сайте, просто проигнорируйте это сообщение.</p></td>
                         </tr>
                         <tr style="border-collapse:collapse">
                         <td class="es-m-txt-c es-m-p35t" align="center" style="padding:0;Margin:0;padding-top:40px"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:17px;color:#373737;font-size:14px">ЮРФАК 2025 © - удобный сервис по поиску лучших юристов, адвокатов, экспертов и нотариусов. Все права защищены.</p></td>
                         </tr>
                         <tr style="border-collapse:collapse">
                         <td align="center" style="padding:0;Margin:0;padding-top:25px;font-size:0px"><a target="_blank" href="{$site_url}" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;text-decoration:none;color:#3E8EB8;font-size:14px"><img src="https://urfaq.ru/assets/img/login-parol/urfakru.png" alt="" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic" width="105" height="15"></a></td>
                         </tr>
                        </tbody>
                     </table></td>
                 </tr>
             </tbody></table></td>
             </tr>
         </tbody></table></td>
         </tr>
     </tbody></table></td>
     </tr>
     </tbody></table>
    </div>
 </body>
</html>
EOF;
    if (send_mail_smtp($user['email'], $title, $body)) {
      $json['success'] = true;
    } else {
      $json['fatal_error'] = 'Ошибка отправки письма, проверьте корректность введенной вами почты.';
    }
  } else {
    $json['fatal_error'] = 'Регистрация временно недоступна';
  }
  responseJson($json);
} else if (isset($_POST['spec_name']) && isset($_POST['spec_type'])) {
  foreach ($_POST as $key => $value) {
    $data[$key] = clean_elem_form($value);
  }

  if ($data['spec_type'] == 'company') {
    $companies = check_company_registration($data['spec_name']);
    if ($companies) {
      $response = '<b>Возможно ваша страница уже существует.<br>Найдены совпадения:</b><div class="users-links">';
      foreach ($companies as $company) {
        // Получаем ОДНОГО специалиста с этой компанией
        $spec = get_specialists_by_company($company['id']);
        if ($spec) {
          $classification = get_classification($spec['classification_id']);
          $response .= '<a href="/specialist/id' . $spec['id'] . '">';
          $response .= $company['title'] . ', ';
          $response .= mb_strtolower($classification['title']) . '</a>';
        }
      }
      $response .= '</div><span>Если это не ваша страница, то продолжайте регистрацию.</span>';
      echo $response;
    }
  } else if ($data['spec_type'] == 'specialist') {
    $name = explode(' ', $data['spec_name']);
    if (!empty($name[0]) && !empty($name[1])) {
      $specialists = check_user_name($name[0], $name[1]);
      if ($specialists) {
        $response = '<b>Возможно ваша страница уже существует.<br>Найдены совпадения:</b><div class="users-links">';
        foreach ($specialists as $spec) {
          $classification = get_classification($spec['classification_id']);
          $response .= '<a href="/specialist/id' . $spec['id'] . '">';
          $response .= $spec['name'] . ' ' . $spec['lastname'] . ', ';
          $response .= mb_strtolower($classification['title']) . '</a>';
        }
        $response .= '</div><span>Если это не ваша страница, то продолжайте регистрацию.</span>';
        echo $response;
      }
    } else {
      die();
    }
  }
}

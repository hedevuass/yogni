<?php


if (isset($_GET['recovery']) || isset($_GET['confirmation'])) {
	$data['title'] = 'Восстановление пароля';
	$specialist_id = clean_elem_form($_GET['user_id']);
	$hash = clean_elem_form($_GET['hash']);
	$email = get_email_by_id($specialist_id);
	if (!check_hash($hash, $specialist_id)) {
		unset($_SESSION['user']);
		responseRedirect('/moy-urfaq');
	}
	if (isset($_GET['recovery'])) {
		$user = get_specialist($specialist_id);
		$token = clean_elem_form($_GET['token'] ?? '');
		if ($user['recovery_token'] !== $token || empty($token)) {
			// Токен не валиден или уже использован
			responseRedirect('/action?recovery');
			exit;
		}
		recover_user($specialist_id);
		clear_recovery_token($user['id']);
	}
} else {
	$user_id = $_SESSION['user']['id'];
	$email = get_email_by_id($user_id);
}

// if (isset($_GET['confirmation'])) {
// 	$data['title'] = 'Придумайте пароль';

// 	$specialist_id = clean_elem_form($_GET['user_id']);
// 	$email = get_email_by_id($specialist_id);


// 	$data['content-confirm'] = <<<HTML
//     <form method="POST" id="rights-confirmation-popup-2" style="display: none;">
//         <input type="hidden" name="specialist_id" value="{$specialist_id}">
// 		<div class="security">
// 		<div class="field">
// 			<div class="field__name">Логин</div>
// 			<div class="field__input login">
// 			<input type="text" name="email" disabled value="{$email}">
// 			</div>
// 		</div>
// 		<div class="field">
// 		<div class="field__name">Пароль</div>
// 		    <div class="field__input password" style="position:relative;margin-bottom: 1rem;">
//                 <input  type="password" 
//                         name="password" 
//                         placeholder="Введите пароль*" 
//                         class="" 
//                         oninput="this.value=this.value.replace(/[^\w\d]*/gi,'');">
//                 <div class="invalid-feedback"></div>
//             </div>
//         </div>
// 	</div>
//         <div class="button">
//             <a class="button-confirm" style="
// 			    display: block;
// 				text-align: center;
// 			    width: 396px;
//                 max-width: 100%;
//                 background: #fff;
//                 font-weight: 500;
//                 font-size: 16px;
//                 color: #4F77B4;
//                 border: 2px solid #4F77B4;
//                 border-radius: 8px;
//                 padding: 0.5rem 2rem;
//                 height: 40px;
// 				margin: 32px 0;
//             ">Подтвердить права</a>
//         </div>
//     </form>
// HTML;
// }
$data['login_field'] = '<input type="text" id="user-email" disabled value="' . $email . '">';
$data['login_field2'] = '<input type="text" disabled value="' . $email . '">';
$HTML = html_explode([
	'login_field' => $data['login_field'],
	'login_field2' => $data['login_field2']
], $HTML);

$HTML_ = explode('<!--data:content-confirm-->', $HTML, 3);
$HTML = $HTML_[0] . "<!--data:content-confirm-->{$data['content-confirm']}<!--data:content-confirm-->" . $HTML_[2];

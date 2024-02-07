<?php
// Подключение модулей
include $_SERVER['DOCUMENT_ROOT'] . '/heic_converter/src/HeicToJpg.php';
use Maestroerror\HeicToJpg;
use JetBrains\PhpStorm\NoReturn;

function modules_include($HTML, $data = [])
{
	$modules_list = explode("<!--module:", $HTML);

	$modules_size = sizeof($modules_list);
	for ($modules_i = 1; $modules_i < $modules_size; $modules_i += 2) {
		list($module) = explode("-->", $modules_list[$modules_i], 2);

		if (!file_exists("modules/$module.html")) {
			continue;
		}

		$code = file_get_contents("modules/$module.html");
		$code = modules_include($code, $data);

		if (isset($_GET['comand']) && $_GET['comand'] == 'editable') {
			$HTML = str_replace("<!--module:$module--><!--module:$module-->", "<!--module:$module-->$code<!--module:$module-->", $HTML);
		} else {
			$HTML = str_replace("<!--module:$module--><!--module:$module-->", $code, $HTML);
		}

		if (file_exists("modules/$module.php")) {
			require "modules/$module.php";
		}
	}

	return $HTML;
}

function html_explode(array $data, string $HTML): string
{
	foreach ($data as $key => $value) {
		$HTML_ = explode("<!--data:$key-->", $HTML, 3);
		$HTML = $HTML_[0] . "<!--data:$key-->$value<!--data:$key-->" . $HTML_[2];
	}

	return $HTML;
}

function html_replace(array $data, string $HTML): string
{
	foreach ($data as $key => $value) {
		$HTML = str_replace("<!--$key-->", $value, $HTML);
	}

	return $HTML;
}

// Очищает инпуты
function clean_input($string, $length): string
{
	$string = trim($string);
    return substr($string, 0, $length);
}

// Очищает элементы формы
function clean_elem_form($elem)
{
	if (is_array($elem)) {
		foreach ($elem as &$value) {
			$value = clean_elem_form($value);
		}
	}

	if (!is_array($elem)) {
		$elem = trim($elem);
		$elem = stripslashes($elem);
		$elem = htmlspecialchars($elem);
	}

	return $elem;
}

// Возвращает JSON
#[NoReturn] function responseJson($array)
{
	echo json_encode($array);
	exit;
}

// Красиво выводит массив
function pre(mixed $array)
{
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}

// Склонение слов
function num_decline($number, $titles, $show_number = true): string
{
	if (is_string($titles)) $titles = preg_split('/, */', $titles);

	// когда указано 2 элемента
	if (empty($titles[2])) $titles[2] = $titles[1];
	$cases = [2, 0, 1, 1, 1, 2];
	$intnum = abs((int)strip_tags($number));
	$title_index = ($intnum % 100 > 4 && $intnum % 100 < 20) ? 2 : $cases[min($intnum % 10, 5)];

	return ($show_number ? "$number " : '') . $titles[$title_index];
}

// Редирект
#[NoReturn] function responseRedirect(string $location)
{
	header("Location: $location");
	exit();
}

// Базовая аутентификация
function base_auth()
{
	if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_PW'] != PASSWORD || $_SERVER['PHP_AUTH_USER'] != LOGIN) {
		header('WWW-Authenticate: Basic realm="Backend"');
		header('HTTP/1.0 401 Unauthorized');
		echo 'Text to send if user hits Cancel button';
		exit;
	}
}

// Editable
function editable(string $HTML): array|string
{
	if (isset($_GET['comand']) && $_GET['comand'] == 'editable') {
		base_auth();

		$HTML = str_replace('<div ', '<div contenteditable="true" ', $HTML);
		$HTML = str_replace('<span ', '<span contenteditable="true" ', $HTML);
		$HTML = str_replace('<a ', '<a contenteditable="true" ', $HTML);
		$HTML = str_replace('&amp;', '&', $HTML);
	}

	return $HTML;
}


/**
 * @param string $phone
 * @return array|bool|string|null
 */
function validation_phone(string $phone): array|bool|string|null
{
	if (!$phone) return false;

	$pattern = '/^((\+?7|8)(?!95[4-7]|99[08]|907|94[^09]|336)([348]\d|9[0-6789]|7[0247])\d{8}|\+?(99[^4568]\d{7,11}|994\d{9}|9955\d{8}|996[57]\d{8}|9989\d{8}|380[34569]\d{8}|375[234]\d{8}|372\d{7,8}|37[0-4]\d{8}))$/';

	$phone = preg_replace("/[^0-9]/", '', $phone);

	if (substr($phone, 0, 1) == 8) $phone[0] = 7;

	if (!preg_match($pattern, $phone)) $phone = false;

	return $phone;
}

/**
 * @param string $email
 * @return mixed
 */
function validation_email(string $email): mixed
{
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $email = false;
    return $email;
}

/**
 * Красиво преобразует номер телефона
 * @param string $phone
 * @param bool $trim
 * @return string
 */
function format_phone(string $phone = '', bool $trim = true): string
{
	if (empty($phone)) return '';

	$phone = preg_replace("/[^0-9A-Za-z]/", "", $phone);

	if ($trim == true && strlen($phone) > 11) {
		$phone = substr($phone,  0, 11);
	}

	if (strlen($phone) == 7) {
		return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1-$2", $phone);
	} elseif (strlen($phone) == 10) {
		return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1 $2-$3", $phone);
	} elseif (strlen($phone) == 11) {
		return preg_replace("/([0-9a-zA-Z])([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})/", "+$1 $2 $3-$4-$5", $phone);
	}

	return $phone;
}

/**
 * Загрузка изображений
 * @param array $file
 * @param string $directory
 * @param int $max_weight MB
 * @param array $min_sizes wh
 * @param array $max_sizes wh
 * @return string|array|bool
 */
function load_image(array $file, string $directory, int|float $max_weight = 1.1, array $min_sizes = ['width'=> 250, 'height' => 250], array $max_sizes = []): string|array|bool
{
	if (!$file) return false;
	$is_heic = false;
	if (HeicToJpg::isHeic($file['tmp_name'])) {
		$is_heic = true;
	} else if (!exif_imagetype($file['tmp_name'])) {
		return $result['error'] = 'Загрузите файл в форме jpeg, jpg, png или heic.';
	}

	if ($min_sizes) {
		$min_width = $min_sizes['width'];
		$min_height = $min_sizes['height'];

		list ($width, $height) = getimagesize($file['tmp_name']);

		if ($min_width > $width || $min_height > $height) $result['error'] = 'Изображение слишком маленькое.';
	}

	if ($max_sizes) {
		$max_width = $max_sizes['width'];
		$max_height = $max_sizes['height'];

		list ($width, $height) = getimagesize($file['tmp_name']);

		if ($max_width < $width || $max_height < $height) $result['error'] = 'Изображение слишком большое';
	}

	if ($file['size'] > ($max_weight * 1024 * 1024)) $result['error'] = 'Изображение слишком большое';

	if (!isset($result)) {
		$directory = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $directory . '/';
		$path_info = pathinfo($file['name']);

		$name = $path_info['filename'] . '_' . date('d-m-y_H-i-s') . '.' . $path_info['extension'];
	
		$path = $directory . $name;
		if(file_exists($path)) {
			unlink($path);
		}
	
		if (move_uploaded_file($file['tmp_name'], $path)) {
			if($is_heic) {
				$new_name = $path_info['filename'] . '_' . date('d-m-y_H-i-s') . '.jpg';
				$new_path = $directory . $new_name;
				if (HeicToJpg::convert($path)->saveAs($new_path)) {
					$result['name'] = $new_name;
				} else {
					$result['error'] = 'Не удалось конвертировать изображение';
				}
			} else {
				$result['name'] = $name;
			}
		} else {
			$result['error'] = 'Не удалось загрузить изображение';
		}
	}
	
	return $result;
}

/**
 * Возвращает массив с месяцами года
 * @return array
 */
function monthArr():array
{
	return array(
        "01" => "Январь",
        "02" => "Февраль",
        "03" => "Март",
        "04" => "Апрель",
        "05" => "Май",
        "06" => "Июнь",
        "07" => "Июль",
        "08" => "Август",
        "09" => "Сентябрь",
        "10" => "Октябрь",
        "11" => "Ноябрь",
        "12" => "Декабрь"
    );
}

/**
 * @param string $path
 * @param array $content
 * @return false|string
 */
function get_view(string $path, array $content): bool|string
{
    ob_start();

    extract($content);

    include $path;

    $result = ob_get_contents();
    ob_get_clean();

    return $result;
}

/**
 * Обрезает текст до нужного количества символов
 * @param string $text
 * @param int $characters
 * @return string
 */
function cut_text(string $text, int $characters): string
{
    if (strlen($text) <= $characters) return $text;

    $text = mb_strimwidth($text, 0, $characters, encoding: 'UTF-8');
    $text = rtrim($text, "!,.-");

    if ($strpos = strrpos($text, ' ')) {
        $text = substr($text, 0, $strpos);
    }

    return $text . '...';
}
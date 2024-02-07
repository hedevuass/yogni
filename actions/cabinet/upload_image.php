<?php
include $_SERVER['DOCUMENT_ROOT'] . '/heic_converter/src/HeicToJpg.php';
use Maestroerror\HeicToJpg;

include $_SERVER['DOCUMENT_ROOT'] . '/actions/database/functions.php';
include $_SERVER['DOCUMENT_ROOT'] . '/functions.php';
session_start();
 
// Разрешенные расширения файлов.
$allow = array('jpg', 'jpeg', 'png', 'heic');
 
// Запрещенные расширения файлов.
$deny = array(
	'phtml', 'php', 'php3', 'php4', 'php5', 'php6', 'php7', 'phps', 'cgi', 'pl', 'asp', 
	'aspx', 'shtml', 'shtm', 'htaccess', 'htpasswd', 'ini', 'log', 'sh', 'js', 'html', 
	'htm', 'css', 'sql', 'spl', 'scgi', 'fcgi', 'exe'
);
 
// Директория куда будут загружаться файлы.
$path = dirname(__DIR__, 2) . '/uploads/avatars/';
 
if (!isset($_FILES['photo_file'])) {
	$json['error'] = 'Файл не загружен.';
} else {
	$file = $_FILES['photo_file'];
 
	// Проверим на ошибки загрузки.
	if (!empty($file['error']) || empty($file['tmp_name'])) {
		$json['error'] = 'Не удалось загрузить файл.';
	} elseif ($file['tmp_name'] == 'none' || !is_uploaded_file($file['tmp_name'])) {
		$json['error'] = 'Не удалось загрузить файл.';
	} else {
        // Получаем расширение файла
		$ext = pathinfo($file['name'])['extension'];
 
		if (!isset($_SESSION['user']['id']) || empty($ext)) {
			$json['error'] = 'Не удалось загрузить файл.';
		} elseif (!empty($allow) && !in_array(strtolower($ext), $allow)) {
			$json['error'] = 'Загрузите файл в форме jpeg, jpg, png или heic.';
		} elseif (!empty($deny) && in_array(strtolower($ext), $deny)) {
			$json['error'] = 'Загрузите файл в форме jpeg, jpg, png или heic.';
		} elseif (intval($file['size']) > 1.1 * 1024 * 1024) {
			$json['error'] = 'Файл слишком большой. Выберите файл размером менее 1мб.';
        } elseif (intval(getimagesize($file['tmp_name'])[0]) < 250 || intval(getimagesize($file['tmp_name'])[1]) < 250) {
			$json['error'] = 'Ширина или высота изображения слишком маленькое.';
		} else {
			//создаем имя файла по формуле "avatar" + id + '_' + дата + расширение файла
			$name = 'avatar' . $_SESSION['user']['id'] . '_'. date('d-m-y_H-i-s') .'.' . $ext;
			if (file_exists($path . $name)){
				// удаляем старое фото с сервера
				unlink($path . $name);
			}

			// Перемещаем файл в директорию, сохраняем название файла в БД и т.п.
			if (move_uploaded_file($file['tmp_name'], $path . $name) && set_user_avatar($name, $_SESSION['user']['id'])) {
                $json['success'] = $name;
				if (HeicToJpg::isHeic($path . $name)) {
					$new_name = 'avatar' . $_SESSION['user']['id'] . '_'. date('d-m-y_H-i-s') . '.jpg';
					if (HeicToJpg::convert($path . $name)->saveAs($path . $new_name)) {
						set_user_avatar($new_name, $_SESSION['user']['id']);
						$result['name'] = $new_name;
					} else {
						$result['error'] = 'Не удалось конвертировать изображение';
					}
				} else {
					$json['success'] = $name;
				}
			} else {
				$json['error'] = 'Не удалось загрузить файл.';
			}
		}
	}
}
 
header('Content-Type: application/json');
responseJson($json);
die();
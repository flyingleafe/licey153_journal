<?php
require_once '../smsc_api.php';

function sms_send_code($phone) {

	session_start();
	//код - любое пятизначное число
	$secret = rand(10000, 99999);

	$_SESSION['sms_validation_secret'] = $secret;

	$msg = "Автоматическая валидация Лицея №153. Ваш секретный код: " . $secret;
	$status = send_sms($phone, $msg);

	return (bool)$status[2];
}

function sms_validate($secret) {
	session_start();
	return ($secret === $_SESSION['sms_validation_secret']);
}
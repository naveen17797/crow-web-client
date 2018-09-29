<?php
session_start();
require 'template_handler.php';
require 'headers.php';
require 'lib/class.json_handler.php';
require 'lib/api.github.php';


if (file_exists("../../cr-users.json")) {
	header("location: login.php");
	exit();
}
else if (empty($_SESSION['registration_granted'])) {
	header("location: setup.php");
	exit();
}

$loader = new loader();
$loader->set_template_file("register");
$loader->assign("TITLE", "Set up credentials");
$loader->output();


//Handle signup information
if (isset($_POST)) {
	if (!empty($_POST['email']) && !empty($_POST['password'])) {
		if (!file_exists("../../cr-users.json")) {
			$email = $_POST['email'];
			$password = $_POST['password'];
			$jsonHandler = new jsonHandler("../../cr-users.json");
			$encrypted_access_token = encrypt_decrypt("encrypt", $_SESSION['access_token'], $password);
			$value = array("password"=>password_hash($password,PASSWORD_BCRYPT), "r"=>"1", "c"=>"1", "d"=>"1", "access_token"=>$encrypted_access_token);
			$value = json_encode($value);
			$jsonHandler->create_key_value($email, $value);
			unset($_SESSION['registration_granted']);
			header("location: login.php");
			exit();
		}
		else {
			header("location: login.php");
			exit();
		}
	}
}



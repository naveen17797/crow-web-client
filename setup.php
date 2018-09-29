<?php
session_start();
if (file_exists("../../cr-users.json")) {
	header("location: register.php");
	exit();
}
require 'template_handler.php';
require 'lib/api.github.php';
require 'headers.php';


if (isset($_POST['access_token'])) {
	if (!empty($_POST['access_token'])) {
		$access_token = $_POST['access_token'];
		$array = getOwnerInfo($access_token);
		if (empty($array) || isset($array['message'])) {
			$errorMessageLoader = new loader();
			$errorMessageLoader->set_template_file("error_alert");
			$errorMessageLoader->assign("ERROR_MESSAGE", "Invalid access token");
			$errorMessageLoader->output();
		}
		else {
			$_SESSION['access_token'] = $_POST['access_token']; 
			$_SESSION['registration_granted'] = 1;
			header("location: register.php");
		}

	}	
}


$loader = new loader();
$loader->set_template_file("setup");
$loader->assign("TITLE", "Enter Your Github User Access Token");
$loader->output();


?>
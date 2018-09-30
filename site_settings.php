<?php 
session_start();
require 'template_handler.php';
require 'lib/api.github.php';
require 'headers.php';
require 'lib/commonFunctions.php';

if (isset($_POST)) {
	if (!empty($_POST['blog_name']) && !empty($_POST['blog_description'])) {
        $blog_name = $_POST['blog_name'];
        $blog_description = $_POST['blog_description'];
        $site_settings = array();
        $site_settings['blog_name'] = $blog_name;
        $site_settings['blog_description'] = $blog_description; 
		$_SESSION['site_settings'] = $site_settings;
		pushUpdatedFile ("cr_info.json", $_SESSION['site_settings_sha'], $_SESSION['site_settings'], $_SESSION['access_token'], $_SESSION['owner'], $_SESSION['repo_name'], $_SESSION['user_email']);
		$access_token = $_SESSION['access_token'];

		//obtain site settings
		$pathToFile = "cr_info.json";

		$username = $_SESSION['owner'];

		$repo_name = $_SESSION['repo_name'];

		$file_info = getFileUrl($access_token, $username, $repo_name, $pathToFile);

		$file_url = $file_info['download_url'];

		$_SESSION['site_settings_sha'] = $file_info['sha'];

		header("location: site_settings.php");
		exit();
		
	}
}

//only update operation takes place here
$loader = new loader();
$loader->set_template_file("site_settings");
$loader->assign("TITLE","site settings");
$SETTINGS = "";
$site_settings = $_SESSION['site_settings'];
$loader->assign("BLOG_NAME_VALUE", $site_settings["blog_name"]);
$loader->assign("BLOG_DESCRIPTION_VALUE", $site_settings["blog_description"]);
$loader->output();


?>
<?php 
session_start();
require 'template_handler.php';
require 'lib/api.github.php';
require 'headers.php';

if (empty($_SESSION['authorized_email'])) {
	header("location: login.php");
	exit();
}


$loader = new loader();
$loader->set_template_file("index");




$access_token = $_SESSION['access_token'];

//TEST CASES
//1.the repo might not be present (Dont proceed)
//2.The repo is present (fetch json files to sessions);
//3.AccessToken may turn invalid

if (empty($_SESSION['posts'])) {

	$owner_array = getOwnerInfo($access_token);

	$username = $owner_array['login'];

	$repo_name = $username.".github.io";

	$pathToFile = "cr_posts.json";

	$file_info = getFileUrl($access_token, $username, $repo_name, $pathToFile);

	$file_url = $file_info['download_url'];

	$_SESSION['posts'] = getContentsFile($file_url);

	$_SESSION['posts_file_sha'] = $file_info['sha'];

	$fp = fopen("cr-posts.json", "w");
	fwrite($fp, json_encode($_SESSION['posts']));
	fclose($fp);
	foreach ($_SESSION['posts'] as $key) {
		$navigation_posts = new loader();
		$navigation_posts->set_template_file("display_posts_navigation");
		$navigation_posts->assign("TITLE", $key['title']);
		$navigation_posts->assign("ID", $key['id']);
		$POSTS = $POSTS.$navigation_posts->returnHtml();
	
	}

	$loader->assign("TITLE", "write a post - Crow");
	$loader->assign("CR_POSTS", $POSTS);
	$loader->output();
}
else if (!empty($_GET['id']) && !empty($_GET['mode'])) {

	$mode = $_GET['mode'];

	$id = $_GET['id'];

	$TITLE = " ";

	$DESCRIPTION = " ";

	$POSTS =  "";
	
	foreach ($_SESSION['posts'] as $key) {
	    $navigation_posts = new loader();
		$navigation_posts->set_template_file("display_posts_navigation");
		$navigation_posts->assign("TITLE", $key['title']);
		$navigation_posts->assign("ID", $key['id']);
		$POSTS = $POSTS.$navigation_posts->returnHtml();	
		if ($key['id'] == $id) {
			$TITLE = $key['title'];
			$DESCRIPTION = $key['description'];
		}		
	}

	if ($mode == "edit") {
			$loader->assign("TITLE", "Edit this post - ".$TITLE);
			$loader->assign("CR_POSTS", $POSTS);
			$loader->assign("POST_TITLE", $TITLE);
			$loader->assign("POST_DESCRIPTION", $DESCRIPTION);
			$loader->assign("ACTION_TEXT", "Update Post");
			$loader->output();
	}
	else if ($mode == "delete") {
		$navigationBarLoader = new Loader();
		$navigationBarLoader->set_template_file("navigation_bar");
		$navigationBarLoader->assign("TITLE", $TITLE);
		$navigationBarLoader->output();
		$confirmDeleteLoader = new loader();
		$confirmDeleteLoader->set_template_file("confirm_delete");
		$confirmDeleteLoader->assign("POST_TITLE", $TITLE);
		$confirmDeleteLoader->assign("POST_DESCRIPTION", substr($DESCRIPTION, 0, 500));
		$confirmDeleteLoader->assign("ID", $id);
		$confirmDeleteLoader->output();
		$footerLoader = new Loader();
		$footerLoader->set_template_file("footer");
		$footerLoader->assign("h", "");
		$footerLoader->output();
	}
}
else if ($mode  == "confirm_delete") {
	$navigationBarLoader = new Loader();
	$navigationBarLoader->set_template_file("navigation_bar");
	$navigationBarLoader->assign("TITLE", $TITLE);
	$navigationBarLoader->output();
	//remove the key from the session array

	//rewrite the file
	//update the content to host

}
else {
	foreach ($_SESSION['posts'] as $key) {
		$navigation_posts = new loader();
		$navigation_posts->set_template_file("display_posts_navigation");
		$navigation_posts->assign("TITLE", $key['title']);
		$navigation_posts->assign("ID", $key['id']);
		$POSTS = $POSTS.$navigation_posts->returnHtml();
	
	}
	$loader->assign("TITLE", "Write a post");
	$loader->assign("CR_POSTS", $POSTS);
	$loader->assign("POST_TITLE", "");
	$loader->assign("POST_DESCRIPTION", "");
	$loader->assign("ACTION_TEXT", "Create Post");
	$loader->output();
}



?>

<script src="js/medium-editor.js"></script>
<link rel="stylesheet" href="css/medium-editor.css">
<link rel="stylesheet" href="css/themes/tim.css">

<script>
var editor = new MediumEditor('.editable', {toolbar: {
                    buttons: ['bold', 'italic', 'underline', 'strikethrough', 'quote', 'anchor', 'image', 'justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull', 'superscript', 'subscript', 'orderedlist', 'unorderedlist', 'h2', 'h3', 'h1', 'h4', 'h5']
                    	
                    },
                    placeholder: {
      				text: '    Description',
        			hideOnClick: true
    			}
            });
</script>

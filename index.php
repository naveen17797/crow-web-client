<?php 
session_start();
require 'template_handler.php';
require 'lib/api.github.php';
require 'headers.php';
require 'lib/commonFunctions.php';

if (empty($_SESSION['authorized_email'])) {
	header("location: login.php");
	exit();
}

if (isset($_GET['show_message'])) {
	if (!empty($_GET['show_message'])) {
		$show_message = $_GET['show_message'];
		$show_message = str_replace("+", " ", $show_message);
		$alertLoader = new Loader();
		$alertLoader->set_template_file("success_message");
		$alertLoader->assign("MESSAGE_INFO", $show_message);
		$alertLoader->output();
	}
}

if (isset($_POST)) {
	if (!empty($_POST['cr_post_title']) && !empty($_POST['cr_post_description'])) {

		$post_action = $_POST['form_action']; 
		
		//either be create, update, delete
		if ($post_action == "edit_post") {
			$post_id = $_POST['cr_post_id'];
			$post_title = $_POST['cr_post_title'];
			$post_description = $_POST['cr_post_description'];
			$propertyName = array("title", "description");
			$propertyValue = array($post_title, $post_description);
			$_SESSION['posts'] = updateArrayByProperty ("id", $post_id,$propertyName, $propertyValue, $_SESSION['posts']);
			
			pushUpdatedFile ("cr_posts.json", $_SESSION['posts_file_sha'], $_SESSION['posts'], $_SESSION['access_token'], $_SESSION['owner'], $_SESSION['repo_name'], $_SESSION['user_email']);
			
			//get the changed sha and store
			$pathToFile = "cr_posts.json";
			$file_info = getFileUrl($_SESSION['access_token'], $_SESSION['owner'], $_SESSION['repo_name'], $pathToFile);
			$_SESSION['posts_file_sha'] = $file_info['sha'];
			$message = "Edits are Saved";
			$message = urlencode($message);
			header("location: index.php?show_message=$message");
			exit();
		}
		else if ($post_action == "create_post") {
			$random_id = md5(str_shuffle(time()));
			
			$post_title = $_POST['cr_post_title'];
			$post_description = $_POST['cr_post_description'];
			$propertyName = array("id","title","description","date_created", "author", "author_url");
			$date_created = date("Y-m-d", time());
			$author_url = "https://github.com/".$_SESSION['owner'];
			$propertyValue = array($random_id, $post_title, $post_description, $date_created, $_SESSION['owner'], $author_url);
			$_SESSION['posts'] = addArrayToExistingArray($propertyName, $propertyValue, $_SESSION['posts']);

			pushUpdatedFile ("cr_posts.json", $_SESSION['posts_file_sha'], $_SESSION['posts'], $_SESSION['access_token'], $_SESSION['owner'], $_SESSION['repo_name'], $_SESSION['user_email']);
			

			$pathToFile = "cr_posts.json";
			$file_info = getFileUrl($_SESSION['access_token'], $_SESSION['owner'], $_SESSION['repo_name'], $pathToFile);
			$_SESSION['posts_file_sha'] = $file_info['sha'];
			$message = "Post was successfully created";
			$message = urlencode($message);
			header("location: index.php?show_message=$message");
			exit();
			
		}
	}
}


$loader = new loader();
$loader->set_template_file("index");




$access_token = $_SESSION['access_token'];

//TEST CASES
//1.the repo might not be present (Dont proceed)
//2.The repo is present (fetch json files to sessions);
//3.AccessToken may turn invalid

if (!isset($_SESSION['posts'])) {

	$owner_array = getOwnerInfo($access_token);
	$username = $owner_array['login'];
	$_SESSION['user_email'] = $owner_array['email'];

	$repo_name = $username.".github.io";

	$_SESSION['owner'] = $username;

	$_SESSION['repo_name'] = $repo_name;

	$pathToFile = "cr_posts.json";

	$file_info = getFileUrl($access_token, $username, $repo_name, $pathToFile);

	$file_url = $file_info['download_url'];

	$_SESSION['posts'] = getContentsFile($file_url);

	$_SESSION['posts_file_sha'] = $file_info['sha'];

	$POSTS = "";
	foreach ($_SESSION['posts'] as $key) {
		$navigation_posts = new loader();
		$navigation_posts->set_template_file("display_posts_navigation");
		$navigation_posts->assign("TITLE", $key['title']);
		$navigation_posts->assign("ID", $key['id']);
		$navigation_posts->assign("BLOG_URL", $_SESSION['repo_name']);
		$POSTS = $POSTS.$navigation_posts->returnHtml();
	
	}

	//obtain site settings
	$pathToFile = "cr_info.json";

	$file_info = getFileUrl($access_token, $username, $repo_name, $pathToFile);

	$file_url = $file_info['download_url'];

	$_SESSION['site_settings'] = getContentsFile($file_url);

	$_SESSION['site_settings_sha'] = $file_info['sha'];


	$loader->assign("TITLE", "write a post - Crow");
	$loader->assign("CR_POSTS", $POSTS);
	$loader->assign("POST_ACTION", "create_post");
	$loader->assign("POST_TITLE", "");
	$loader->assign("POST_DESCRIPTION", "");
	$loader->assign("ACTION_TEXT", "create post");
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
		$navigation_posts->assign("BLOG_URL", $_SESSION['repo_name']);
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
			$loader->assign("POST_ACTION", "edit_post");
			$loader->assign("CR_POST_ID", $id);
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
	else if ($mode  == "confirm_delete") {
		$navigationBarLoader = new Loader();
		$navigationBarLoader->set_template_file("navigation_bar");
		$navigationBarLoader->assign("TITLE", $TITLE);
		$navigationBarLoader->output();
		//remove the key from the session array
		$keyRemovedArray = removeKeyFromJSONArrayByProperty("id", $id, $_SESSION['posts']);
		//update the content to host
		$_SESSION['posts'] = array_values($keyRemovedArray);

		pushUpdatedFile ("cr_posts.json", $_SESSION['posts_file_sha'], $_SESSION['posts'], $_SESSION['access_token'], $_SESSION['owner'], $_SESSION['repo_name'], $_SESSION['user_email']);

		$pathToFile = "cr_posts.json";
		$file_info = getFileUrl($_SESSION['access_token'], $_SESSION['owner'], $_SESSION['repo_name'], $pathToFile);
		$_SESSION['posts_file_sha'] = $file_info['sha'];
		header("location: index.php");
		exit();

    }
}

else {
	$POSTS = "";
	foreach ($_SESSION['posts'] as $key) {
		$navigation_posts = new loader();
		$navigation_posts->set_template_file("display_posts_navigation");
		$navigation_posts->assign("TITLE", $key['title']);
		$navigation_posts->assign("ID", $key['id']);
		$navigation_posts->assign("BLOG_URL", $_SESSION['repo_name']);
		$POSTS = $POSTS.$navigation_posts->returnHtml();	
	}
	$loader->assign("TITLE", "Write a post");
	$loader->assign("CR_POSTS", $POSTS);
	$loader->assign("POST_TITLE", "");
	$loader->assign("POST_DESCRIPTION", "");
	$loader->assign("ACTION_TEXT", "Create Post");
	$loader->assign("POST_ACTION", "create_post");
	$loader->output();
}


?>














<!-- library to support formatting in text area -->
<script src="js/medium-editor.js"></script>
<link rel="stylesheet" href="css/medium-editor.css">
<link rel="stylesheet" href="css/themes/tim.css">

<script>
var editor = new MediumEditor('.editable', {toolbar: {
                    buttons: ['bold', 'italic', 'underline', 'strikethrough', 'quote', 'anchor', 'image', 'justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull', 'superscript', 'subscript', 'orderedlist', 'unorderedlist', 'h2', 'h3', 'h1', 'h4', 'h5']
                    	
                    },
                    placeholder: {
      				text: 'Enter your post description',
        			hideOnClick: true
    			}
            });
</script>

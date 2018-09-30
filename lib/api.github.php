<?php

function getFileUrl($access_token, $owner, $repo_name, $pathToFile){
		$curl = curl_init();
		$url = "https://api.github.com/repos/".$owner."/".$repo_name."/contents/".$pathToFile."?access_token=$access_token";
		$options = array(CURLOPT_URL=>$url, CURLOPT_RETURNTRANSFER=>1);
		curl_setopt($curl, CURLOPT_USERAGENT, "Crow");
		curl_setopt_array($curl, $options);
		$json = curl_exec($curl);
		curl_close($curl);
		$array = json_decode($json, true);
		return $array;
}


function getContentsFile($url) {		
		
		$curl = curl_init();
		curl_setopt_array($curl, array(CURLOPT_URL=>$url, CURLOPT_RETURNTRANSFER=>1));
		curl_setopt($curl, CURLOPT_USERAGENT, "Crow");
		$json = curl_exec($curl);
		return $arr = json_decode($json, true);
}

function getOwnerInfo($access_token) {
	$curl = curl_init();
	$url = "https://api.github.com/user?access_token=$access_token";
	curl_setopt_array($curl, array(CURLOPT_URL=>$url, CURLOPT_RETURNTRANSFER=>1));
	curl_setopt($curl, CURLOPT_USERAGENT, "LibreUpdater");
	$json = curl_exec($curl);
	return $array = json_decode($json, true);
}


function encrypt_decrypt($action, $string, $secret_key) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = $secret_key;
    $secret_iv = 'crow';
    // hash
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if( $action == 'decrypt' ) {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}


function pushUpdatedFile ($filename, $sha, $content, $access_token, $owner, $repo_name, $email) {
		if (count($content) == 0) {
			$content = "[{}]";
		}
		else {
			$content = json_encode($content);
		}
		$curl = curl_init();
		$url = "https://api.github.com/repos/".$owner."/".$repo_name."/contents/".$filename."?access_token=$access_token";
		$post_array = array();
		$post_array["message"] =  "update $filename";
		$post_array["committer"]["name"] = $owner;
		$post_array["committer"]["email"] = $email;
		$post_array["content"] = base64_encode($content);
		$post_array["sha"] = $sha;
		$post_json = json_encode($post_array);  
		$options = array(CURLOPT_URL=>$url, CURLOPT_RETURNTRANSFER=>1, CURLOPT_POSTFIELDS=>$post_json, CURLOPT_CUSTOMREQUEST=>"PUT");
		curl_setopt($curl, CURLOPT_USERAGENT, "Crow");
		curl_setopt_array($curl, $options);
		echo $json = curl_exec($curl);
		curl_close($curl);
		


}



?>
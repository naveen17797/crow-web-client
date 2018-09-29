<?php
session_start();
/**
 * Main page for the application
 *
 *
 * Copyright (C) 2018 Naveen Muthusamy <kmnaveen101@gmail.com>
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * LICENSE: This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 * See the Mozilla Public License for more details.
 * If a copy of the MPL was not distributed with this file, You can obtain one at https://mozilla.org/MPL/2.0/.
 *
 * @package Newway File Manager
 * @author Naveen Muthusamy <kmnaveen101@gmail.com>
 * @link https://github.com/naveen17797
 */

require 'template_handler.php';
require 'headers.php';
require 'lib/class.json_handler.php';
require 'lib/api.github.php';

$loader = new loader();
$loader->set_template_file("login");
$loader->assign("TITLE", "login to crow");
$loader->output();

//check if a admin user is present
if (!file_exists("../../cr-users.json")) {
	header("location: setup.php");
	exit();
}
else if (!empty($_SESSION['authorized_email'])) {
	header("location: index.php");
	exit();
}

if (isset($_POST)) {

	if (!empty($_POST['email']) && !empty($_POST['password'])) {

		$email = $_POST['email'];
		$password = $_POST['password'];
		$jsonHandler = new jsonHandler("../../cr-users.json");
		$email_exists = $jsonHandler->check_if_key_exists($email);
		if ($email_exists) {
			//get the password from json
			$values_json = $jsonHandler->get_value_by_key($email);
			$values_array = json_decode($values_json, true);
			$hashed_password = $values_array['password'];
			$encrypted_access_token = $values_array['access_token'];
			$decrypted_access_token = encrypt_decrypt("decrypt", $encrypted_access_token, $password);
			if (password_verify($password, $hashed_password)) {
				$_SESSION['authorized_email'] = $email;
				$_SESSION['access_token'] = $decrypted_access_token;

				header("location: index.php");
				exit();
            }
			// Error message
			echo "<div class='alert alert-danger'>
			Email or password is incorrect
			</div>";
		} else {
			// Error message
            echo "<div class='alert alert-danger'>
            Email or password is incorrect
            </div>";
		}

	}

}


?>
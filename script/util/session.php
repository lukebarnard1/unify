<?php
	//Begin a session to allow access to the session variables
	session_start();

	ini_set('upload_max_filesize', '50M');
	ini_set('post_max_size', '50M');

	$logged_in = isset($_SESSION["user"]);
	if ($logged_in) {
		$user = $_SESSION["user"];
	}

	include "session_var.php";
?>
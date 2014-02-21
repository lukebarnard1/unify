<?php
	include "../util/session.php";
	include "../util/redirect.php";
	
	if ($logged_in) {
		$_SESSION["logged_in"] = false;
		redirect("../../welcome/?user_email=".$_SESSION["user"]->user_email);
		unset($_SESSION["user"]);
		unset($_SESSION["selected_user"]);
	} else {
		redirect("../../welcome/");
	}
	
?>
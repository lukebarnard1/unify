<?php
	include "../util/session.php";
	include "../util/redirect.php";
	include "../util/pwd.php";
	include_once("../util/mysql.php");
	$redirect = "/";
	if (isset($_POST["r"]) && $_POST["r"]!="") {
		$redirect = htmlspecialchars($_POST["r"]);
	}

	if (isset($_POST["user_email"]) && isset($_POST["user_password"]) && $_POST["user_email"] != "" && $_POST["user_password"] != "") {
		$dao = new DAO();
		$user_email = $dao->escape($_POST["user_email"]);
		$user_password = $dao->escape(salt($_POST["user_password"]));
		
		$user_query = "SELECT user_id,user_name,user_email,cohort_id,user_picture FROM user WHERE user_email=\"$user_email\" AND user_password=\"$user_password\";";
		
		$dao->myquery($user_query);
		
		if ($dao->fetch_num_rows() == 1) {
			$_SESSION["user"] = $dao->fetch_one_obj_part(array("user_id","user_name","user_email","cohort_id","user_picture"));
			
			unset($_SESSION["selected_user"]);
			redirect($redirect);//Go to the redirect link
		} else {
			redirect("../../welcome/?&m=2&r=".$redirect."&user_email=".htmlspecialchars($user_email));
		}
	} else {
		redirect("../../welcome/?m=3".(isset($_POST["user_email"])?"&user_email=".$_POST["user_email"]:"")."&r=".$redirect);
	}
?>
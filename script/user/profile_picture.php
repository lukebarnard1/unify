<?php
	include "../util/session.php";
	include "../util/mysql.php";
	include "../util/redirect.php";
	
	$f = "../img/dp1.jpg";

	if(isset($_GET["user_id1"])) {

		$dao = new DAO(false);
		$user_id1 = $dao->escape($_GET["user_id1"]);

		$dao->myquery("SELECT user_picture FROM user WHERE user_id=\"$user_id1\";");
		$user1 = $dao->fetch_one_obj_part(array("user_picture"));

		$f = "../profile_pictures/".$user1->user_picture;

		if (!$user1->user_picture || !file_exists($f)) {
			$f = "../img/dp1.jpg";
		}
		header('Content-Type: image/jpeg');
		header("Content-Disposition: inline; filename=\"$user1->user_picture\"");
		readfile($f);
	}
?>
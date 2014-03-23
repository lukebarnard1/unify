<?php
	//Unhide a post that has been hidden
	
	include "../util/session.php";
	include_once("../util/mysql.php");
	include "../util/status.php";
	
	$dao = new DAO(false);

	if (isset($_GET["post_id"])) {
		$post_id = $dao->escape($_GET["post_id"]);

		$hidden_post = DataObject::select_one($dao,"hidden_post",array("hide_id"),array("post_id"=>$post_id,"user_id"=>$user->user_id));

		if ($hidden_post) {
			$result = $hidden_post->delete();
			if ($result) {
				echo Status::json(0,"Post unhidden");
			} else {
				echo Status::json(1,"Post could not be unhidden");
			}
		} else {
			echo Status::json(2,"Post not hidden");
		}
	} else {
		echo Status::json(3,"No post id");
	}
?>
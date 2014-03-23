<?php
	include "../util/session.php";
	include_once("../util/mysql.php");
	include "../util/status.php";
	
	$dao = new DAO(false);
	
	if (isset($_GET["post_id"])) {
		$post_id = $_GET["post_id"];
		$post = DataObject::select_one($dao,"post",array("post_id","user_id"),array("post_id"=>$post_id));
		if ($post) {
			if ($user->user_id == $post->user_id) {
				//User's own post, so delete it
				if ($post->delete()) {
					echo Status::json(0,"Post deleted");
				} else {
					echo Status::json(5,"Failed to delete post");
				}
			} else {
				//Not the user's own post, so hide it from them
				$hidden_post = DataObject::create($dao, "hidden_post", array("user_id"=>$user->user_id,"post_id"=>$post_id));
				if ($hidden_post) {
					if ($hidden_post->commit()) {
						echo Status::json(0,"Post hidden");
					} else {
						echo Status::json(1,"Failed to commit hidden_post");
					}
				} else {
					echo Status::json(2,"Failed to create hidden_post");
				}
			}
		} else {
			echo Status::json(3,"Failed to select post");
		}
	} else {
		echo Status::json(4,"post_id not set");
	}
?>
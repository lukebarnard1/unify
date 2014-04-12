<?php
	//Vote up or down on a post
	include "../util/session.php";
	include_once("../util/mysql.php");
	include "../util/status.php";
	
	$dao = new DAO(false);
	
	if (isset($_GET["d"]) && isset($_GET["post_id"])){
		$direction = $_GET["d"];
		$post_id = $_GET["post_id"];
		
		$post_vote = DataObject::select_one($dao,"post_vote",array("vote_id"),array("user_id"=>$user->user_id,"post_id"=>$post_id)); 

		if ($post_vote) {
			echo Status::json(1,"User has already voted");
		} else {
			$post = DataObject::select_one($dao,"post",array("post_id","post_rating_up","post_rating_dn"),array("post_id"=>$post_id));
			if ($post) {
				if ($direction=="u") {
					$post->post_rating_up++;
				} else {
					$post->post_rating_dn++;
				}
				if ($post->commit()) {
					$post_vote = DataObject::create($dao,"post_vote",array("user_id"=>$user->user_id,"post_id"=>$post_id));
					if ($post_vote) {
						if ($post_vote->commit()) {
							echo Status::json(0,"Vote added");
						} else {
							echo Status::json(2,"Failed to prevent future votes");
						}
					} else {
						echo Status::json(3,"Failed to insert post_vote");
					}
				} else {
					echo Status::json(4,"Failed to commit change post rating");
				}
			} else {
				echo Status::json(5,"Failed to select post");
			}
		}
	} else {
		echo Status::json(6,"d or post_id not set");
	}
?>
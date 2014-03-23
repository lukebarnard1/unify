<?php
	//Add a comment to a post on a cohort/user's feed
	
	include "../util/session.php";
	include_once("../util/mysql.php");
	include "../util/status.php";
	include "../notification/add.php";
	
	$dao = new DAO(false);

	$post_id = $_POST["post_id"];
	$comment_content = $_POST["comment_content"];

	if ($comment_content != "") {
		$comment = DataObject::create($dao,"comment",array("user_id"=>$user->user_id,"post_id"=>$post_id,"comment_content"=>$comment_content));
		if ($comment->commit()){
			//Comment has been added, notifier the orignal poster

			//Find the original poster
			$post = DataObject::select_one($dao,"post",array("post_id","user_id"), array("post_id"=>$post_id));

			if ($post->user_id != $user->user_id) {
				$notification_user = $post->user_id;
				$notification_title = "New comment on your post";
				$notification_message = "$user->user_name has commented on one of your posts.";
				$notification_link = "post/".$post->post_id;

				notify($dao, $notification_user, $notification_title, $notification_message, $notification_link);
			}
			echo Status::json(0, "Comment added");
		} else {
			echo Status::json(2, "Comment could not be added");
		}
	} else {
		echo Status::json(1, "No comment content");
	}
?>
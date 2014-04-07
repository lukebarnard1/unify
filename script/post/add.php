<?php
	//Add a post to a cohort/user's feed
	include "../util/session.php";
	include_once("../util/mysql.php");
	include "../util/status.php";
	
	include "../notification/add.php";
	
	$group_id = -1;//For the feed

	$dao = new DAO(false);
	if (isset($_POST["group_id"])) {
		//For a specific group
		$group_id = $_POST["group_id"];
	}
	
	if (isset($_POST["post_content"]) && trim($_POST["post_content"]) != "") {
		$post_content = $_POST["post_content"];
		$post_time = date("Y-m-d H:i:s", time() + 3600);

		$post = DataObject::create($dao,"post",
			array(
				"user_id"=>$user->user_id,
				"group_id"=>$group_id,
				"post_content"=>$post_content,
				"post_time"=>$post_time
			)
		);
		if ($post) {
			$success = $post->commit();
			if ($success) {
				//Notify the group of students
				if ($group_id != -1) {
					$notification_users = DataObject::select_all($dao, "grouping", array("grouping_id","user_id"),array("group_id"=>$group_id));

					$notification_title = "New post in your group.";
					$notification_message = "$user->user_name has posted in your group.";
					$notification_link = "post/".$post->get_primary_id();

					foreach ($notification_users as $notification_user) {
						if ($notification_user->user_id != $user->user_id) {
							echo notify($dao, $notification_user->user_id, $notification_title, $notification_message, $notification_link);
						}
					}
				}
				echo Status::json(0,"Added post");
			} else {
				echo Status::json(1,"Failed to add post");
			}
		} else {
			echo Status::json(2,"Failed to create post");
		}
	} else {
		echo Status::json(3,"No post content");
	}
?>
<?php
	//Delete a comment from a post

	include "../util/session.php";
	include "../util/mysql.php";
	include "../util/status.php";
	
	$dao = new DAO(false);

	if (isset($_GET["comment_id"])) {
		$comment_id = $_GET["comment_id"];

		$comment = DataObject::select_one($dao, "comment", array("comment_id"), array("comment_id"=>$comment_id,"user_id"=>$user->user_id));
		
		$success = $comment->delete();

		if ($success) {
			echo Status::json(0,"Comment deleted");
		} else {
			echo Status::json(1,"Comment could not be deleted from database");
		}
	} else {
		echo Status::json(2,"No comment id");
	}
?>
<?php
	// Request to disunify or unfriend from someone
	include "../util/session.php";
	include_once("../util/mysql.php");
	include_once("../util/redirect.php");
	
	$dao = new DAO(false);

	$friend_request = DataObject::select_one($dao, "friend_request",
	 	array("req_id"), 
	 	array("user_id1"=>$user->user_id,"user_id2"=>$selected_user->user_id)
	);

	if ($friend_request != NULL) {
		$friend_request->delete();
	}

	redirect("/user/".$selected_user->user_id);
?>
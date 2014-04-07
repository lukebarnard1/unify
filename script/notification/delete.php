<?php
	//Delete a notification

	include_once("../util/session.php");
	include_once("../util/status.php");
	include_once("../util/mysql.php");

	$dao = new DAO(false);

	$notification = DataObject::select_one($dao, "notification", 
		array("notif_id"), 
		array(
			"user_id"=>$user->user_id,
			"notif_id"=>$_POST["notif_id"])
	);

	if ($notification != NULL) {
		if ($notification->delete()) {
			echo Status::json(0,"Notification deleted");
		} else {
			echo Status::json(1, "Could not delete notification");
		}
	} else {
		echo Status::json(2, "Could not find notification");
	}
?>
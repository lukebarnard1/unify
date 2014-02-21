<?php
	//Get all available notifcations for the user
	
	include_once("../util/session.php");
	include_once("../util/mysql.php");

	$dao = new DAO(false);

	$notifications = DataObject::select_all_json($dao,"notification",
		array("notif_id","user_id","notif_title","notif_message","notif_link","notif_seen"),
		array("user_id"=>$user->user_id)
	);

	echo $notifications;
?>
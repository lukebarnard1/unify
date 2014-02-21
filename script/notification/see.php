<?php
	//Delete a notifcation from the database
	include_once("../util/mysql.php");
	include_once("../util/redirect.php");
	// include_once("../util/status.php");
	include_once("../util/constants.php");

	$notification_id = $_GET["notif_id"];
	$dao = new DAO(false);
	$notification = DataObject::select_one($dao, "notification",array("notif_id","notif_link","notif_seen"),array("notif_id"=>$notification_id));
	if ($notification) {
		$notification->notif_seen = 1;// User has seen this now
		if ($notification->commit()) {
			redirect($SITE_URL.$notification->notif_link);
		} else {
			redirect($SITE_URL,array("m"=>"0"));
		}
	} else {
		redirect($SITE_URL,array("m"=>"0"));
	}
?>
<?php
	//Adds a notification to the database
	// This will later be deleted when an email is sent out

	//Notifications are as general as possible having a title, a message and a link.

	include_once("../util/constants.php");

	function notify($dao, $user_id, $notif_title, $notif_message, $link_to_content) {

		$notification = DataObject::create($dao, "notification",
			array(
				"user_id"=>$user_id,
				"notif_title"=>$notif_title,
				"notif_message"=>$notif_message,
				"notif_link"=>$link_to_content,
				"notif_departure"=>date("Y-m-d H:i:s",time() + 60*60)//one hour before an email is sent instead
			)
		);

		if ($notification) {
			return $notification->commit();
		} else {
			return false;
		}
	}
?>
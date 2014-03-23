#!/usr/bin/php -q
<?php
	//Scan database for notifications that have not been seen and are ready for departure
	include "../util/mysql.php";
	include "../mail/send.php";
	include "../util/constants.php";

	// chdir("..");
	
	// error_log("\n\n");
	// error_log("PHP_ini_loaded_file: ".php_ini_loaded_file());
	// error_log("executable path: ".PHP_BINDIR);
	// error_log("user: ".get_current_user());
	error_log("Beginning to email notifications...");
	$dao = new DAO(false);

	$part = array("user_id","user_email","user_name","notif_id","notif_title","notif_message","notif_link");
	$query = "SELECT user.user_id,user_email,user_name,notif_id,notif_title,notif_message,notif_link 
				FROM notification 
				JOIN user ON user.user_id=notification.user_id 
				WHERE NOT notif_seen AND NOT notif_emailed
				AND notif_departure < NOW();";
	$dao->myquery($query);

	$notifications = $dao->fetch_all_obj();
	
	foreach ($notifications as $notification) {
		$body = "<p>Hello ".$notification->user_name.",</p>
		<p>".$notification->notif_message." <a href=\"".$SITE_URL."script/notification/see.php?notif_id=".$notification->notif_id."\">Click here to view</a>.</p>
		<p>Best Wishes,<br>The Unify Team</p>";

		mail_message($notification->user_email, $notification->notif_title, $body);

		$query = "UPDATE notification SET notif_emailed=\"1\" WHERE notif_id=\"".$notification->notif_id."\";";
		$dao->myquery($query);
	}
	error_log("Finished emailing notifications...");
?>
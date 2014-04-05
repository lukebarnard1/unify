<?php
	include "../util/session.php";
	include_once("../util/mysql.php");
	include "../mail/send.php";
	include "../util/status.php";
	include "../util/constants.php";

	$dao = new DAO(false);

	$user_id = $_POST["user_id"];
	$group_id = $_POST["group_id"];
	
	$member = DataObject::select_one($dao, "user", array("user_id","user_email","user_name"), array("user_id"=>$user_id));
	$group = DataObject::select_one($dao, "user_group", array("group_id","group_name"), array("group_id"=>$group_id));

	if ($group != NULL) {
		if ($member != NULL) {

			if (NULL == DataObject::select_one($dao, "grouping_request", 
				array("gr_id"), 
				array("group_id"=>$group_id,"user_id"=>$user_id))) {

				$body = "<p>Hello ".$member->user_name.",</p>
				<p>".$user->user_name." has asked you to join the group \"".$group->group_name."\".
					If you would like to join, please click on this link: 
					<a href=\"".$SITE_URL."script/grouping/confirm.php?group_id=".$group_id."\">Click here to join</a>.</p>
				<p>Best Wishes,<br>The Unify Team</p>";

				$request = DataObject::create($dao,"grouping_request", array("group_id"=>$group_id,"user_id"=>$user_id));
				$request->commit();//Put the request in the database. So long as this is here, the user can accept (only when logged in)

				mail_message($member->user_email, "Group Join Request", $body);
				echo Status::json(0,"Request sent :)");
			} else {
				echo Status::json(3,"Member has already been requested to join");
			}
		} else {
			echo Status::json(1,"Member not found");
		}
	} else {
		echo Status::json(2,"Group not found");
	}
?>
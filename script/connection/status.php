<?php
	//Determine the status of a connection: requested/connected/non existant

	include "../util/session.php";
	include "../util/session_var.php";
	include_once("../util/mysql.php");
	include_once("../util/status.php");

	$dao = new DAO(false);

	$connection = array(
		"user_id1" => $user->user_id,
		"user_id2" => $selected_user->user_id
	);
	$connection_rev = array(
		"user_id2" => $user->user_id,
		"user_id1" => $selected_user->user_id
	);

	//Has it been requested?

	$request = DataObject::select_one($dao, "friend_request", array("req_id"), $connection);
	if ($request == NULL) {
		//Check if they are friends

		$friendship = DataObject::select_one($dao, "connection", array("connection_id"), $connection_rev);
		if ($friendship != NULL) {
			echo Status::json(0, "Unification complete: <a href=\"javascript:;\" onclick=\"location.reload()\">refresh page?</a>");
		} else {
			echo Status::json(1, "Unification failed!");
		}
	} else {
		echo Status::json(1, "Unification requested");
	}
?>
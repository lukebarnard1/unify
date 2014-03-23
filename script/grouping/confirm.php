<?php
	//Confirm that this user logged in wants to join the group
	include "../util/session.php";
	include "../util/redirect.php";
	include_once("../util/mysql.php");

	$group_id = $_GET["group_id"];

	$dao = new DAO(false);

	$request = DataObject::select_one($dao, "grouping_request", array("gr_id","group_id","user_id"),array("group_id"=>$group_id,"user_id"=>$user->user_id));

	if ($request != NULL) {
		$request->delete();//Delete the request from the database

		$grouping = DataObject::create($dao, "grouping", array("group_id"=>$group_id,"user_id"=>$user->user_id));
		$grouping->commit();

		if ($grouping != NULL) {
			redirect("/group/$group_id");//Send them to the new group!
		} else {
			redirect("/?m=0");
		}
	} else {
		redirect("/?m=0");
	}

?>
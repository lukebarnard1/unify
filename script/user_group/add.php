<?php
	//Add a new group, given its name and also add the user who's created it to the group

	include "../util/session.php";
	include_once("../util/mysql.php");
	include_once("../util/status.php");
	include_once("../util/redirect.php");

	$group_name = $_GET["group_name"];

	$dao = new DAO(false);

	$group = DataObject::create($dao,"user_group",array("group_name"=>$group_name));
	$group->commit();

	if ($group) {
		$grouping = DataObject::create($dao,"grouping",array("group_id"=>$group->group_id,"user_id"=>$user->user_id));
		$grouping->commit();
		if ($grouping) {
			redirect("/group/".$grouping->group_id);
		} else {
			redirect("/?m=11");//Could not add you to the group after creating it!
		}
	} else {
		redirect("/?m=12");//Could not create the group, sorry!
	}
?>
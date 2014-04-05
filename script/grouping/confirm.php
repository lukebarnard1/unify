<?php
//Confirm that this user logged in wants to join the group
include "../util/session.php";
include "../util/redirect.php";
include_once("../util/mysql.php");

$group_id = $_GET["group_id"];

if (isset($user)) {

	$new_values = array("group_id"=>$group_id,"user_id"=>$user->user_id);

	$dao = new DAO(false);

	//Check if the user has already been added:
	$already_grouped = DataObject::select_one($dao, "grouping", array("grouping_id"), $new_values);

	if ($already_grouped == NULL) {
		$grouping = DataObject::create($dao, "grouping", $new_values);
		$request = DataObject::select_one($dao, "grouping_request", array("gr_id","group_id","user_id"),array("group_id"=>$group_id,"user_id"=>$user->user_id));

		if ($request != NULL) {
			$request->delete();//Delete the request from the database

			if ($grouping->commit()) {
				redirect("/",array("group_id"=>$group_id,"m"=>17));//Send them to the new group!
			} else {
				redirect("/?m=11");
			}
		} else {
			redirect("/?m=13"); //You have not been asked to join this group
		}
	} else {
		redirect("/",array("group_id"=>$group_id,"m"=>14)); //You are already in this group... See!
	}
} else {//Not logged in!
	redirect("/welcome/?r=/script/grouping/confirm.php%3Fgroup_id%3D".$group_id);
}
?>

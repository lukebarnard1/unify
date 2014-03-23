<?php
	//Print in JSON format the groups that this user belongs to, excluding their cohort group

	include "../util/session.php";
	include_once("../util/mysql.php");
	include_once("../util/status.php");
	
	$dao = new DAO(false);

	$query = "SELECT user_group.group_id, user_group.group_name 
				FROM grouping JOIN user_group ON grouping.group_id = user_group.group_id 
				WHERE user_id=\"".$user->user_id."\" 
					AND NOT grouping.group_id=(SELECT group_id FROM cohort WHERE cohort.cohort_id=\"".$user->cohort_id."\");";

	$dao->myquery($query);

	echo $dao->fetch_json_part(array("group_id","group_name"));
?>
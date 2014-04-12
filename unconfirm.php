<?php
	//Remove this user's request to register
	include "script/util/mysql.php";
	include "script/util/redirect.php";
	
	$dao = new DAO();
	$rnd = $dao->escape($_GET["rnd"]);
	
	//Delete the confirmation
	//Delete the user
	
	//Find the user id first
	$conf_query = "SELECT user_id FROM confirmation WHERE conf_rnd = \"$rnd\";";
	
	$dao->myquery($conf_query);
	
	$row = $dao->fetch_one();
	$user->user_id = $row["user_id"];
	
	//Then delete the confirmation
	$conf_query = "DELETE FROM confirmation WHERE conf_rnd = \"$rnd\";";
	$dao->myquery($conf_query);
	
	//And delete the user
	$user_query = "DELETE FROM user WHERE user_id = \"$user->user_id\";";
	$dao->myquery($user_query);
	
	redirect("welcome/?m=9");
?>
<?php
	include "script/util/mysql.php";
	include "script/util/redirect.php";

	$dao = new DAO(false);
	$rnd = $dao->escape($_GET["rnd"]);

	//Delete the confirmation
	//Fix the users email!

	//Find the user id first
	$conf_query = "SELECT user_id FROM confirmation WHERE conf_rnd = \"$rnd\";";

	$dao->myquery($conf_query);

	$row = $dao->fetch_one();
	$user = new stdClass();
	$user->user_id = $row["user_id"];

	//Then delete the confirmation
	$conf_query = "DELETE FROM confirmation WHERE conf_rnd = \"$rnd\";";
	$dao->myquery($conf_query);

	$email_query = "SELECT user_email FROM user WHERE user_id = \"$user->user_id\";";
	$dao->myquery($email_query);
	$row = $dao->fetch_one();
	$user_email = $row["user_email"];

	//EMAIL IS CHANGED HERE

	$space_pos = strpos($user_email, " ") + 1;
	$user_email = substr($user_email,$space_pos);//Take everything after space


	//And change the user's email
	$user_query = "UPDATE user SET user_email = \"$user_email\" WHERE user_id = \"$user->user_id\";";
	$dao->myquery($user_query);

	redirect("welcome/?m=10");
?>

<?php
	//Get number of unread messages for logged-in user
	include "../util/session.php";
	include_once("../util/mysql.php");

	$dao = new DAO(false);

	$query = "SELECT COUNT(*) AS number_unread FROM chat_msg WHERE user_id2=\"$user->user_id\" AND NOT msg_seen";

	$dao->myquery($query);

	echo $dao->fetch_json();
?>
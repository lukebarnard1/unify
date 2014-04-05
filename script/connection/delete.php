<?php
	//Deletes a friend connection (unfriending)

	// Are you friends with this person?
	// Then you can unfriend
	
	include "../util/session.php";
	include_once("../util/mysql.php");
	include_once("../util/redirect.php");

	$user_id1 = $user->user_id;
	$user_id2 = $_GET["user_id2"];

	$dao = new DAO(false);

	$connection = DataObject::select_one($dao, "connection", 
		array("connection_id"), 
		array("user_id1" => $user_id1,
			  "user_id2" => $user_id2)
	);

	if ($connection) {
		$connection->delete();
	} else {
		// Reverse connection
		$connection = DataObject::select_one($dao, "connection", 
			array("connection_id"), 
			array("user_id2" => $user_id1,
				  "user_id1" => $user_id2)
		);
		if ($connection) {
			$connection->delete();
		}
	}

	//Now delete the messages relating to these two users

	$delete_query = "DELETE FROM chat_msg WHERE ".
	 					"(user_id1 = $user_id1 AND user_id2 = $user_id2) OR".
	 					"(user_id2 = $user_id1 AND user_id1 = $user_id2);";

	$dao->myquery($delete_query);

	redirect("/user/" . $user_id2);
?>
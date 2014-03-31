<?php
	//Deletes a friend connection (unfriending)

	// Are you friends with this person?
	// Then you can unfriend
	
	include "../util/session.php";
	include_once("../util/mysql.php");
	include_once("../util/redirect.php");

	$user_id2 = $_GET["user_id2"];

	$dao = new DAO();

	$connection = DataObject::select_one($dao, "connection", 
		array("connection_id"), 
		array("user_id1" => $user->user_id,
			  "user_id2" => $user_id2)
	);

	if ($connection) {
		$connection->delete();
		redirect("/user/" . $user_id2);
	} else {
		// Reverse connection
		$connection = DataObject::select_one($dao, "connection", 
			array("connection_id"), 
			array("user_id2" => $user->user_id,
				  "user_id1" => $user_id2)
		);
		if ($connection) {
			$connection->delete();
			redirect("/user/" . $user_id2);
		} else {
			redirect("/user/" . $user_id2);
		}
	}
?>
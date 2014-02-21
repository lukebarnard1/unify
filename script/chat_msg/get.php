<?php
	//Get any chat messages available by polling database
	
	include "../util/session.php";
	include "../util/mysql.php";
	
	$dao = new DAO(false);

	$user_id = $dao->escape($_POST["user_id"]);

	$latest_pulled = $dao->escape($_POST["latest_pulled"]);
	$latest_seen_by_u2 = $dao->escape($_POST["latest_seen_by_u2"]);

	$this_conversation = "((user_id1=\"$user->user_id\" AND user_id2=\"$user_id\") OR 
	 					   (user_id2=\"$user->user_id\" AND user_id1=\"$user_id\"))";

	$order_limit = "ORDER BY msg_id ASC LIMIT 100";
	
	$properties = array("msg_id","user_id1","user_name","msg_content","msg_seen");

	//Select all messages that have not been pulled by this client 
		// AND all messages that have been seen by the other user, but this has not yet been observed
		// by this client.
	if ($latest_pulled != -1){
		$query = "SELECT ".implode(",",$properties)." FROM
				chat_msg JOIN user ON user.user_id=user_id1 
				WHERE $this_conversation
					AND ((msg_id > $latest_seen_by_u2 AND msg_seen AND user_id2=\"$user_id\")
					      OR (msg_id > $latest_pulled))
				$order_limit ;";
	} else {
		$query = "SELECT ".implode(",",$properties)." FROM
					chat_msg JOIN user ON user.user_id=user_id1 
		 			WHERE $this_conversation AND msg_id > (SELECT MAX(msg_id) FROM chat_msg WHERE $this_conversation) - 100 $order_limit ;";
	}
	
	$dao->myquery($query);

	$messages = $dao->fetch_all_obj_part($properties);

	foreach ($messages as $message) {
		$dao->myquery("UPDATE chat_msg SET msg_seen=1 WHERE msg_id=\"$message->msg_id\" AND user_id2=\"$user->user_id\";");
	}

	echo json_encode_strip($messages);
?>
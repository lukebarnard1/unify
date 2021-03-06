<?php
	//Get any chat messages available by polling database
	
	include "../util/session.php";
	include_once("../util/mysql.php");

	$INITIAL_CONVO_SIZE = 30;
	
	function get_conversations($dao, $user_id, $latest_pulled, $latest_seen_by_u2){
		global $user;
		global $INITIAL_CONVO_SIZE;

		$this_conversation = "((user_id1=\"$user->user_id\" AND user_id2=\"$user_id\") OR 
		 					   (user_id2=\"$user->user_id\" AND user_id1=\"$user_id\"))";
		
		$properties = array("msg_id","user_id1","user_id2","user_name","msg_content","msg_seen");

		//Select all messages that have not been pulled by this client 
			// AND all messages that have been seen by the other user, but this has not yet been observed
			// by this client.
		if ($latest_pulled != -1){
			$query = "SELECT ".implode(",",$properties)." FROM
						chat_msg JOIN user ON user.user_id=user_id1 
						WHERE $this_conversation
							AND ((msg_id > $latest_seen_by_u2 AND msg_seen AND user_id2=\"$user_id\")
							      OR (msg_id > $latest_pulled))
						ORDER BY msg_id ASC;";
		} else {
			$query = "(SELECT ".implode(",",$properties)." FROM
						chat_msg JOIN user ON user.user_id=user_id1 
			 			WHERE $this_conversation
			 			ORDER BY msg_id DESC LIMIT $INITIAL_CONVO_SIZE) ORDER BY msg_id ASC;";
		}

		$dao->myquery($query);
		$messages = $dao->fetch_all_obj_part($properties);

		if (connection_aborted()) {
			echo "Connection aborted";
		}

		$conversations = array();

		//When a request for a specific user is made, include conversation info 
		// even if there aren't any messages.
		if ($user_id != "-1") {
			$user2 = DataObject::select_one($dao,"user",array("user_id","user_name","user_picture"),array("user_id"=>$user_id));

			$conversation = new stdClass();
			$conversation->messages = array();
			$conversation->user_name = $user2->user_name;
			$conversation->user_picture = $user2->user_picture;
			$conversation->user_id = $user_id;
			$conversations[$user_id] = $conversation;
		}

		foreach ($messages as $message) {
			$dao->myquery("UPDATE chat_msg SET msg_seen=1 WHERE msg_id=\"$message->msg_id\" AND user_id2=\"$user->user_id\";");

			if ($message->user_id2 != $user->user_id) {
				$convo_id = $message->user_id2;
			} else {
				$convo_id = $message->user_id1;
			}

			if ( ! array_key_exists($convo_id, $conversations)) {
				$user2 = DataObject::select_one($dao,"user",array("user_id","user_name","user_picture"),array("user_id"=>$convo_id));

				$conversation = new stdClass();
				$conversation->messages = array();
				$conversation->user_name = $user2->user_name;
				$conversation->user_id = $convo_id;
				$conversation->user_picture = $user2->user_picture;
				$conversations[$convo_id] = $conversation;
			} else {
				$conversation = $conversations[$convo_id];
			}

			$conversation->messages[$message->msg_id] = $message;
		}

		return $conversations;
	}

	$dao = new DAO(false);

	if (isset($_POST["user_id"])){
		if($_POST["user_id"] == "-1") {
			//Get an array of all the conversations
			$conversations_query = "(SELECT user_id2 AS user_id FROM chat_msg WHERE user_id1=$user->user_id GROUP BY user_id2) 
									UNION 
									(SELECT user_id1 AS user_id FROM chat_msg WHERE user_id2=$user->user_id GROUP BY user_id1)";
			$dao->myquery($conversations_query);
			$conversation_requests = $dao->fetch_all_part(array("user_id"));

			$conversations = array();
			foreach ($conversation_requests as $request) {
				$c = get_conversations($dao, $request["user_id"], -1, -1)[$request["user_id"]];
				$conversations[$request["user_id"]] = $c;
			}
			echo json_encode_strip($conversations);
			
		} else {
			$conversations = get_conversations($dao, $_POST["user_id"], -1, -1)[$_POST["user_id"]];

			echo json_encode_strip($conversations);
		}
	} else {
		$conversation_requests = $_POST;
		$conversations = array();
		foreach ($conversation_requests as $request) {
			$c = get_conversations(
				$dao,
				$request["user_id"],
				$request["latest_pulled"],
				$request["latest_seen_by_u2"])[$request["user_id"]];
			$conversations[$request["user_id"]] = $c;
		}
		echo json_encode_strip($conversations);
	}

?>
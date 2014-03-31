<?php
	//Send a message in chat
	
	include "../util/session.php";
	include_once("../util/mysql.php");
	include "../util/status.php"; 
	
	$dao = new DAO();

	$user_id = $_POST["user_id"];


	$msg_content = trim($_POST["msg_content"],chr(0xC2).chr(0xA0).chr(0x20));
	$msg_content = trim($msg_content);
	echo bin2hex($msg_content);

	if ($msg_content != "") {
		$chat_msg = DataObject::create($dao,"chat_msg",array("user_id1"=>$user->user_id,"user_id2"=>$user_id,"msg_content"=>$msg_content));
		
		$chat_msg->commit();
		echo Status::json(0,"Message added"); // {code:0,message:"message added"}
	} else {
		echo Status::json(1,"No message content");
	}
?>
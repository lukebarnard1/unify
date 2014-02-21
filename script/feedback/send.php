<?php
	include_once("../util/no_errors.php");
	include "../util/session.php";
	
	include "../mail/send.php";

	$feedback_content = $_POST["feedback_content"];

	$success =  mail_message("unify@lukebarnard.co.uk","Site Feedback",
		"<p>A user has sent feedback about the site.</p><p>".$user->user_email.":<br>".$feedback_content."</p>");


	$status = new stdClass();
	$status->message = $success?"Feedback sent":"Failure to send feedback";
	$status->code = $success?0:1;

	echo json_encode($status);
?>
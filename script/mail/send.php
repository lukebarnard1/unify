<?php
	//Provide a function for sending emails to users given an address, subject and body.
	include "generator.php";

	function mail_message($to, $subject, $body) {
		
		$boundary = md5(uniqid(rand()));

		$email = "\n--$boundary\n";
		$email .= generate_plain_text_email($subject, $body);
		$email .= "\n--$boundary\n";
		$email .= generate_email($subject, $body);
		$email .= "\n--$boundary--\n";
		
		$headers = array (
			// "Subject" => "Unify - ".$subject,
			"Message-ID" => "<".time()."@".$_SERVER["SERVER_ADDR"].">",
			"Date" => date(DATE_RFC2822),
			"From" => "The Unify Team<unify@lukebarnard.co.uk>",
			// "To" => $to,
			"MIME-Version" => "1.0",
			"Content-type" => "multipart/alternative; boundary=\"$boundary\"",
			"Reply-To" => "The Unify Team<unify@lukebarnard.co.uk>"
		); 

		$str_headers = "";
		foreach ($headers as $key => $header) {
			$str_headers .= $key.": ".$header."\n";
		}
		// echo $str_headers.$email;

		$success = mail($to,"Unify - ".$subject,$email,$str_headers);

		// chdir("../../../php/");
		// include_once("Mail.php");
		// $smtp = Mail::factory("smtp", array ("host" => "localhost"));
		// $success = $smtp->send($to, $headers, $email);

		// chdir("../public_html/unify/script/");
		if ($success) {
			// echo "success";
			error_log("Mail Success - Address:" . $to . " Subject:" . $subject);
			return true;
		} else {
			// echo "fail";
			error_log("Mail Failure - Address:" . $to . " Subject:" . $subject);
			return false;
		}
	}
?>
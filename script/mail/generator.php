<?php
	//Provide functions for generation of plain text and html versions of information emails
	include_once( dirname(__FILE__) . "/../util/constants.php");
	function generate_email($subject, $message) {
		global $SITE_URL;
		return "Content-type: text/html; charset=\"iso-8859-1\"\r\nContent-Transfer-Encoding: 7bit\r\n\r\n".
		'<html><body>
		<table style="max-width:640px;min-width:320px;margin:auto;font-family:Arial, sans-serif;">
			<tr>
				<th>
					<a href="'.$SITE_URL.'" style="color:#000000;">
						<h1 style="font-size:50px;font-weight:normal;margin:0">unify</h1>
					</a>
				</th>
			</tr>
			<tr><td style="background-color:#fbfbfb;border:1px solid #f6f6f6;"><h1 style="margin:0;font-size:20px">'.$subject.'</h1></td></tr>
			<tr><td>'.$message.'</td></tr>
			<tr><td></td></tr>
		</table></body></html>';
	}

	function generate_plain_text_email($subject, $message) {
		global $SITE_URL;

		$message = preg_replace("/<\/p>|<br>/","\r\n",$message);//brs and ps to newlines
		$message = preg_replace("/<a href=\"([^\"]*)\">([^<]*)<\/a>/",'${2}: ${1}',$message);//Replace links with URLS
		$message = preg_replace("/<\/?[^a][^>]*>/","",$message);//Replace any tags that aren't links

		$message = "Content-type: text/plain; charset=\"iso-8859-1\"\r\nContent-Transfer-Encoding: 7bit\r\n\r\n".$subject.": ".$message;

		return $message;
	}
?>

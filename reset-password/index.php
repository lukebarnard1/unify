<!DOCTYPE>
<html><head><style>*{font-family: Arial,sans-serif}</style></head><body>
<?php
	include "../script/util/mysql.php";
	include "../script/util/redirect.php";

	if (isset($_POST["user_email"])) {
		include "../script/mail/send.php";

		$dao = new DAO(false);

		$user_email = $dao->escape($_POST["user_email"]);

		$query = "SELECT user_email,user_id,user_name FROM user WHERE user_email=\"$user_email\";";

		$dao->myquery($query);

		if ($dao->fetch_num_rows() == 1) {
			//Store intent to reset in the database with a checksum as the old password?
			$user = $dao->fetch_one_obj();

			$names = explode(" ", $user->user_name);
			if (count($names) == 0) {
				$user_first_name = $user->user_name;
			} else {
				$user_first_name = $names[0];
			}

			$conf_rnd = md5("lsdfuh.uh3".rand(0,10000000)."g.adugi213y");

			$query = "INSERT INTO reset_request VALUES (NULL,\"$user->user_id\",\"$conf_rnd\")".
						"ON DUPLICATE KEY UPDATE conf_rnd=\"$conf_rnd\";";

			$dao->myquery($query);

			$body = "<p>Hello $user_first_name,</p>".
					"<p>It appears you are having trouble remembering your password for Unify. ".
						"As such, someone (hopefully you) has requested that you reset your password. ".
						"If you have no idea what's going on, feel free to take no further action, ".
						"it's possible someone entered your email by mistake or is dillberately trying to ".
						"confuse you. However, if you really do want to reset your password, click the ".
						"link below!</p>".
					"<p><a href=\"http://unify.lukebarnard.co.uk/reset-password/confirm.php?user_id=$user->user_id&conf_rnd=$conf_rnd\">RESET YOUR PASSWORD</a></p>".
					"<p>Best Wishes,<br>".
					"The Unify Team</p>";

			if (mail_message($user_email,"Password Reset",$body)) {
				echo "A message has been sent to your email account. When you get the email, click on the link it contains and you will be taken to a page where you can reset your password. ";
			} else {
				echo "Something has gone wrong when trying to email you. <a href=\".\">Try again?</a>";
			}
		} else {
			echo "Your email could not be found in our database. Perhaps you made a mistake when typing it? <a href=\".\">Try again?</a>";
		}
	} else {
		?>
		<div id="main">
			<p>
				Please enter your email below so that a reset link can be sent to your email address. This is to confirm
				that the email address belongs to you.
			</p>
			<form method="POST">
				<input type="text" placeholder="Your email address" name="user_email">
				<input type="submit" value="Send confirmation email">
			</form>
		</div>
		<?php
	}
?>
</body></html>

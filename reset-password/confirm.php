<?php
	//Confirm password reset and display reset form
	//If the checksum matches, then the user is presented a password reset dialogue for them to enter a new one.
	//		The password is reset and they are directed to the login page.
	//Otherwise
	//		They are sent back to the login page
	include "../script/util/constants.php";
	include "../script/util/mysql.php";
	include "../script/util/redirect.php";
	
	include "../script/mail/send.php";

	$dao = new DAO(false);
	$user = new stdClass();
	$user->user_id = $dao->escape($_GET["user_id"]);
	$conf_rnd = $dao->escape($_GET["conf_rnd"]);
?>
<!DOCTYPE HMTL>
<html>
	<head>
		<style>
			* {
				font-family: Arial, sans-serif;
				font-size:14px;
			}
		</style>
		<script type="text/javascript" src="../jquery.js"></script>
		<script type="text/javascript">
			// Return the element with the specified ID
			function id(element) {
				return document.getElementById(element);
			}

			// Is the password matching and long enough?
			password_valid = false;
		
			// Verify the passwords
			function verify_password() {
				orig_input = id("new_pwd");
				conf_input = id("conf_pwd");
				p1 = orig_input.value;
				p2 = conf_input.value;
				if (p1==p2) {
					if (p1.length >= <?php echo $MIN_PWD_LENGTH;?>) {
						password_valid = true;
						id("info").innerHTML = "Passwords match.";
					}else{
						password_valid = false;
						id("info").innerHTML = "Password needs to be <?php echo $MIN_PWD_LENGTH;?> characters or longer!";
					}
				}else{
					password_valid = false;
					id("info").innerHTML = "Passwords don't match!";
				}
			}

			// Change the password of the user
			function reset_password(e) {
				e.preventDefault();

				if (password_valid) {
					new_pwd = id("new_pwd").value;
					conf_pwd = id("conf_pwd").value;
					user_id = "<?php echo $user->user_id;?>";
					conf_rnd = "<?php echo $conf_rnd;?>";

					id("info").innerHTML = "Passwords match, resetting...";

					$.ajax({
						url: "../script/user/set_password.php",
						data:{user_password:new_pwd,conf_rnd: conf_rnd,user_id: user_id},
						type:"POST"
					}).done(function() {
						id("info").innerHTML = "Password reset. Please <a href=\"../welcome/\">login</a>.";
					});
				}
				return false;
			}
		</script>
	</head>
	<body>
	<div id="main">
<?php

	//Determine whether this request is genuine and still live
	$query = "SELECT * FROM reset_request WHERE user_id=\"$user->user_id\" AND conf_rnd=\"$conf_rnd\";";

	$dao->myquery($query);
	if ($dao->fetch_num_rows() == 1) {
?>
		<div>
			<form onsubmit="reset_password(event)" action="">
				<input id="new_pwd" type="password" placeholder="New password" onkeyup="verify_password()"/>
				<input id="conf_pwd" type="password" placeholder="Confirm password" onkeyup="verify_password()"/>
				<input type="submit" value="reset"/>
			</form>
		</div>
		<div id="info">

		</div>
<?php
	} else {
?>

<?php
	}
?>
	</div>
</body></html>
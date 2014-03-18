<?php
	// phpinfo();
	include "../script/util/session.php";
	include "../script/util/redirect.php";
	if ($logged_in) {
		redirect("../");
	}
	include "../script/util/display_message.php";

	$r = "";
	if (isset($_GET["r"])) {
		$r = $_GET["r"];
	}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" href="welcome.css"/>
	<link rel="stylesheet" href="welcome_mobile.css" media="only screen and (max-width: 700px)"/>
	<title>Unify - Welcome</title>
	<style type="text/css">
			<?php
		if ($r != "") {
?>
		#login {
			background-color:#4AB948;
		}
<?php
		}
		?>
	</style>
	<script>
		function id(element) {
			return document.getElementById(element);
		}

		function display_description() {
			id('error_description').style.display='block';
		}

		function load() {
			<?php
			if (isset($_GET["user_email"])) {//Password incorrect probably
			?>
				id("login_pwd").focus();
			<?php
			}
			if (isset($_GET["r"])) {
			?>
				id("login_mail").focus();
			<?php
			}	
			?>
		}
	</script>
</head>
<body onload="load()">
	<div id="main">
		<div id="column1">
			<h1>Welcome to 
			<img id="logo" src="../img/unify1.png">.</h1>
			<p>Unify is a new social network site dedicated to close connections between you
			and your coursemates at university. All you need to sign up is an email address,
			a password and your University course. After that, you're ready to go.
			<br>Here are some of the great features of unify:</p>
			<ul class="top">
				<li>Connect with friends that are <u>right in front of you</u>
					<ul>
						<li>You can only add friends that you are sitting next to.
						</li>
						<li>This means you
							form a very small set of connections, giving unify a more friendly
							feel.</li>
						<li>No more posts that you <u>don't</u> want to see from people
							you <u>haven't even met.</u></li>
					</ul>
				</li>
				<li>Find people at your cohort, course or university
					<ul>
						<li>Search for your coursemates to connect with them.</li>
						<li>See who's connected with who to find more friends.</li>
					</ul>
				</li>
				<li>Make posts to your friends or your cohort
					<ul>
						<li>Ask friends if they want to meet on campus.</li>
						<li>Ask coursemates questions about your course.</li>
					</ul>
				</li>
			</ul>
		</div>
		<div id="column2">
			<div id="register">
				<h1>Register</h1>
				<p>Please enter your details below to join unify.</p>
				<form action="../register/" method="GET">
					<input class="text" type="text" placeholder="Name" name="user_name"><br>
					<input class="text" type="text" placeholder="Email" name="user_email"><br>
					<input class="submit" type="submit" value="Register">
				</form>
			</div>
			<div id="login">
				<h1>Login</h1>
				<p>Please enter your details below to login:</p>
				<form action="../script/user/login.php" method="POST">
					<input id="login_mail" class="text" type="text" placeholder="Email" name="user_email" value="<?php if(isset($_GET["user_email"]))echo $_GET["user_email"];?>"><br>
					<input id="login_pwd" class="text" type="password" placeholder="Password" name="user_password"><br>
					<input type="hidden" name="r" value="<?php echo $r;?>">
					<input class="submit" type="submit" value="Login"><br>
					<a href="../reset-password/">Reset Password</a>
				</form>
			</div>
		</div>
	</div>
</body>
</html>
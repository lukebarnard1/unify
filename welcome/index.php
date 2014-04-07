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
	<meta name="viewport" content="width=device-width, initial-scale=1"/>
	<link rel="stylesheet" href="welcome.css"/>
	<link rel="stylesheet" href="welcome_mobile.css" media="only screen and (max-width: 700px)"/>
	<title>Unify - Welcome</title>
	<style type="text/css">
			<?php
		if ($r != "") {
			?>
					#login {
						background-color:#000;
						color:#fff;
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
		<div style="display:table-row">
			<div id="column1">
				<img class="mobile_title" src="../img/unify1.png">
				<h1 class="non_mobile_title">Welcome to
				<a href="/welcome/"><img id="logo" src="../img/unify1.png"></a>.</h1>
				<p>Unify is a new social network site dedicated to close connections between you
				and your coursemates at university. All you need to sign up is an email address,
				a password and your University course. After that, you're ready to go.
				<br>Here are some of the great features of unify:</p>
				<ul class="top">
					<li>Connect with friends that are <b>right in front of you</b>
						<ul>
							<li>You can only add friends that you are sitting next to.
							</li>
							<li>This means you
								form a very small set of connections, giving unify a more friendly
								feel.</li>
							<li>No more posts that you <b>don't</b> want to see from people
								you <b>haven't even met.</b></li>
						</ul>
					</li>
					<li>Find people at your cohort, course or university
						<ul>
							<li>Search for your coursemates to connect with them.</li>
							<!---<li>See who's connected with who to find more friends.</li> NOT IMPLEMENTED!-->
						</ul>
					</li>
					<li>Make posts to your friends or your cohort
						<ul>
							<li>Ask friends if they want to meet on campus.</li>
							<li>Ask coursemates questions about your course.</li>
						</ul>
					</li>
					<li>Create customised groups of people to communicate about group coursework
						<ul>
							<li>What's the next deliverable? Maybe post a question on your group.</li>
							<li>Carry out anonymous voting for making decisions democratically.</li>
						</ul>
					</li>
				</ul>
			</div>
			<div id="column2">
				<div id="register">
					<h1>register</h1>
					<p>Enter your details below to join unify.</p>
					<form action="../register/" method="GET">
						<div style="padding-right:5px"><input class="text" type="text" placeholder="Full Name" name="user_name"></div>
						<div style="padding-right:5px"><input class="text" type="email" placeholder="Email" name="user_email"></div>
						<input class="submit" type="submit" value="Register">
					</form>
				</div>
				<div id="login">
					<h1>login</h1>
					<p>Enter your details below to login:</p>
					<form action="../script/user/login.php" method="POST">
						<div style="padding-right:5px"><input id="login_mail" class="text" type="email" placeholder="Email" name="user_email" value="<?php if(isset($_GET["user_email"]))echo $_GET["user_email"];?>"></div>
						<div style="padding-right:5px"><input id="login_pwd" class="text" type="password" placeholder="Password" name="user_password"></div>
						<input type="hidden" name="r" value="<?php echo $r;?>">
						<input class="submit" type="submit" value="Login"><br>
						<a href="../reset-password/">Reset Password</a>
					</form>
				</div>
			</div>
		</div>
	</div>
</body>
</html>

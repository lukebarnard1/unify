<?php
	include "../script/util/constants.php";
	include "../script/util/display_message.php";

	function GET($var) {
		if (isset($_GET[$var])) {
			return $_GET[$var];
		} else {
			return "";
		}
	}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1"/>
	<title>Unify - Register</title>
	<style type="text/css">
		*{
			margin:0;
			padding:0;
			
			font-family: Arial, sans-serif;
		}
		
		html,body {
			background-color:#fff;
		}
		
		body {
			margin:5px;
		}
		
		a {
			color:inherit;
			text-decoration:none;
		}
		
		a:hover {
			text-decoration:underline;
		}
		
		#main {
			position:relative;
			padding:0px 10px 10px 10px;
			margin:auto;
			max-width:900px;
			/*height:510px;*/
			
			border:1px solid #ccc;
			background-color:#fff;
			color:#222;
		}
		
		p {
			text-align:justify;
		}
		
		h1 {
			font-size:48pt;
			font-weight:500;
			color:#000;
		}
		
		#logo {
			position:relative;
			top:16px;
			width:125px;
		}
		
		#form {
			padding:10px;
			background-color:#222;
		}

		#form table {
			width:100%;
		}
		
		form table tr td {
			padding:4px;
			color:#fff;
		}
		
		form input.text {
			font-size:14pt;
			width:100%;
		}
		
		input.error {
			background-color:#ff7788;
		}
		
		input.correct {
			background-color:#4AB948;
			color:#fff;
		}
		
		#continue, #mobile_continue {
			padding:10px;
			text-align:center;
			background-color:#f4f4f4;
		}
		
		#continue a, #mobile_continue a{
			display:block;
			font-size:40pt;
			text-decoration:none;
		}

		#mobile_continue {
			display:none;
		}
		
		#course_select {
			position:absolute;
			background-color:#f4f4f4;
			color:#000;
			border:1px solid #ccc;
			padding:5px;
			display:none;
		}
	</style>
	<link rel="stylesheet" href="register_mobile.css" media="only screen and (max-width: 800px)"/>
	<script src="../jquery.js"></script>
	<script type="text/javascript">
		var email_valid = false;
		var password_valid = false;
		var course_valid = false;
		
		function id(el_id) {
			return document.getElementById(el_id);
		}
		
		function load() {
			$.ajax({
				url: "../script/university/get.php",
				dataType: "json"
			}).done(function(data) {
			  	for (i = 0; i < data.length; i++) {
					id("university_id").innerHTML += "<option value='"+data[i]["university_id"]+"'>"+data[i]["university_name"]+"</option>";
				}
			});
			verify_email();
			verify_password();
		}
		
		function valid_email(email) {
			pat = /^[^\s\@]{1,}\@[^\s\@]{1,}\.[a-z]{2,}$/;
			return pat.test(email);
		}
		
		function verify_email() {
			orig_input = id("ea1");
			conf_input = id("ea2");
			email1 = orig_input.value;
			email2 = conf_input.value;

			if (email1 == "" || email2 == "")return; 

			if (email1==email2) {
				if (valid_email(email1)) {
					orig_input.className = "text correct";
					conf_input.className = "text correct";
					email_valid = true;
					id("error").innerHTML = "&nbsp;";
				} else {
					orig_input.className = "text error";
					conf_input.className = "text error";
					email_valid = false;
					id("error").innerHTML = "Email is not valid!";
				}
			} else {
				conf_input.className = "text error";
				email_valid = false;
				id("error").innerHTML = "Emails don't match!";
			}
			verify_all();
		}
		
		function verify_password() {
			orig_input = id("p1");
			conf_input = id("p2");
			p1 = orig_input.value;
			p2 = conf_input.value;
			if (p1 == "" || p2 == "")return;

			if (p1==p2) {
				if (p1.length >= <?php echo $MIN_PWD_LENGTH;?>) {
					orig_input.className = "text correct";
					conf_input.className = "text correct";
					password_valid = true;
					id("error").innerHTML = "&nbsp;";
				}else{
					orig_input.className = "text error";
					conf_input.className = "text error";
					password_valid = false;
					id("error").innerHTML = "Password needs to be <?php echo $MIN_PWD_LENGTH;?> characters or longer!";
				}
			}else{
				conf_input.className = "text error";
					password_valid = false;
				id("error").innerHTML = "Passwords don't match!";
			}
			verify_all();
		}
		
		function verify_all() {
			submit = $(".submit");
			
			start_valid = false;
			start_valid = 	id("start_month").value != "-" &&
							id("start_year").value != "-";
			
			if (email_valid && password_valid && course_valid && start_valid) {
				$(".continue").css({backgroundColor: '#4AB948',color: '#FFFFFF'});
				submit.attr("href","javascript:id('form_to_submit').submit();");
			} else {
				$(".continue").css({backgroundColor: '#F4F4F4',color: '#000000'});
				submit.removeAttr("href");
			}
		}
		
		function choose_course() {
			course_valid = false;
			inputer = id("course_input");
			selector = id("course_select");
			
			query = inputer.value;
			
			if ($.trim(query) != "") {
				$.ajax({
					url: "../script/course/search.php",
					data: {course: query, university_id: id("university_id").value},
					dataType: "json"
				}).done(function(data) {
					if (data.length > 0) {
						selector.style.display = "block";
						selector.innerHTML = "";
						for (i = 0; i < data.length; i++) {
							selector.innerHTML += "<a href='javascript:;' onclick='pick_course(\"" +
													data[i]["course_id"] + "\",\""+
													data[i]["course_name"]+"\")'>" +
													data[i]["course_name"] + "</a><br>";
						}
						//Focus on the first result
						if (selector.childNodes.length > 0) {
							selector.childNodes[0].focus();
						}
					} else {
						selector.style.display = "none";
					}	
				}).fail(function(data) {
					selector.style.display = "none";
				});
			}
		}
		
		function pick_course(course_id,course_name) {
			course_valid = true;
			id("course_id").value = course_id;
			id("course_input").value = course_name;
			selector.style.display = "none";
			verify_all();
		}
	</script>
</head>
<body onload="load()">
	<div id="main">
		<table>
			<tr>
				<td colspan=2 >
					<h1>Register at
					<a href="/welcome/"><img id="logo" src="../img/unify1.png"></a>.</h1>
					<p>Once you have clicked "Continue >>", you will be sent an email to the email address entered to confirm your account. Please check your spam inbox for emails because it could be marked as spam.</p>
				</td>
			</tr>
			<tr>
				<td id="form">
					<form id="form_to_submit" action="../script/user/register.php" method="POST">
						<table>
							<tr>
								<td>Full name:</td>
								<td><input class="text" type="text" placeholder="Name" name="user_name" value="<?php echo GET('user_name');?>"></td>
							</tr>
							<tr>
								<td>Email address:</td>
								<td><input id="ea1" onkeyup="verify_email()" class="text" type="email" placeholder="Email" name="user_email" value="<?php echo GET('user_email');?>"></td>
							</tr>
							<tr>
								<td>Confirm email:</td>
								<td><input id="ea2" onkeyup="verify_email()" class="text" type="email" autocomplete="off" placeholder="Confirm email" name="conf_email" value="<?php echo GET('conf_email');?>"></td>
							</tr>
							<tr>
								<td>New password:</td>
								<td><input id="p1" onkeyup="verify_password()" class="text" type="password" placeholder="Password" name="user_password" value="<?php echo GET('user_password');?>"></td>
							</tr>
							<tr>
								<td>Confirm password:</td>
								<td><input id="p2" onkeyup="verify_password()" class="text" type="password" placeholder="Confirm password" name="conf_password" value="<?php echo GET('conf_password');?>"></td>
							</tr>
							<tr>
								<td>University</td>
								<td><select id="university_id" class="option" type="password" placeholder="" name="university_id"></select></td>
							</tr>
							<tr>
								<td>Course</td>
								<td>
									<input id="course_input" onkeyup="choose_course()" class="text" type="text" placeholder="">
									<div id="course_select"></div>
									<input id="course_id" type="hidden" name="course_id" value="<?php echo GET('course_id');?>">
								</td>
							</tr>
							<tr>
								<td>Course start date</td>
								<td>
									<select id="start_month" name="start_month" onchange="verify_all()">
										<option value="-">Month</option>
										<?php 
											for ($i = 1; $i < 13; ++$i) {
										?>
											<option value="<?php echo $i;?>" <?php echo GET('start_month')==$i?"selected":"";?>><?php echo $MONTHS[$i];?></option>
										<?php
											}
										?>
									</select>
									<select id="start_year" name="start_year" onchange="verify_all()">
										<option value="-">Year</option>
										<?php
											$date = getdate();
											$i = $date["year"];
											$end = 1900;
											for (;$i > $end;$i--) {
										?>
										<option value="<?php echo $i;?>" <?php echo GET('start_year')==$i?"selected":"";?>><?php echo $i;?></option>
										<?php
											}
										?>
									</select>
								</td>
							</tr>
							<tr>
								<td id="error" colspan="2">&nbsp;
								</td>
							</tr>
						</table>
					</form>
				</td>
				<td class="continue" id="continue">
					<a class="submit">Continue >></a>
				</td>
			</tr>
			<tr>
				<td class="continue" id="mobile_continue">
					<a class="submit">Continue >></a>
				</td>
			</tr>
		</table>

	</div>
</body>
</html>
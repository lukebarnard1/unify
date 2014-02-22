<?php

	include_once "script/util/session.php";
	include_once "script/util/constants.php";
	include_once "script/view/chat_box_json.php";

	include_once "script/util/redirect.php";
	if (!$logged_in) {
		redirect("../welcome/?r=".$_SERVER["REQUEST_URI"]);
	}
	include_once "script/util/mysql.php";

	include "script/user_select.php";
	include "script/cohort_select.php";

	if (! (isset($_GET["user_id"]) || isset($_GET["cohort_id"]))) {
		unset($selected_user);
		unset($_SESSION["selected_user"]);
		unset($selected_group);
		unset($_SESSION["selected_group"]);
	}

	//Reload variables again
	include "script/util/session_var.php";

	include "script/util/display_message.php";
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1"/>
	<link rel="stylesheet" href="style/index.css" />
	<link rel="stylesheet" href="style/mobile.css" media="only screen and (max-width: 700px)"/>
	<title>Unify - Bringing students together</title>

	<script type="text/javascript" src="jquery.js"></script>
	<script type="text/javascript" src="util.js"></script>
	<script type="text/javascript" src="Template.js"></script>
	<script type="text/javascript">
	<?php
		include "index.js";
	?>
	</script>
</head>
<body onload="load()">
	<?php
		if ($user->user_id != $DEVELOPER_ID) {
	?>
	<script type="text/javascript">
		if (document.location.hostname == "unify.lukebarnard.co.uk") {
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-16927282-2']);
		  _gaq.push(['_trackPageview']);
		
 		 (function() {
  		  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
 		   ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  		  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		}
	</script>
	<?php 
		}
	?>
	<form id="prof_pic_form" action="script/user/set_profile_picture.php" method="POST" enctype="multipart/form-data">
		<input type="file" id="prof_pic_file" onchange="sub_prof_pic()" name='file'>
		<input type="hidden" name="r" value="<?php echo $_SERVER["REQUEST_URI"]; ?>">
		<input type="hidden" name="MAX_FILE_SIZE" value="5242880">
	</form>
	<div class="nav_bar">
		<div class="inner">
			<?php
				$links = array("unify"=>"/","you"=>"/user/$user->user_id","cohort"=>"/cohort/$user->cohort_id","logout"=>"/script/user/logout.php");

				foreach($links as $text => $link) {
					$selected = ($link == $_SERVER["REQUEST_URI"]);
					?>
					<a class="nav_button <?php echo $selected?"selected":""?>" href="<?php echo $link?>"><?php echo $text?></a>
					<?php
				}
			?>
			<div class="nav_button notif" onmouseover="notif_container.style.display='block'" onmouseout="notif_container.style.display='none'">
				notifications
				<div id="notif_container" style="display:none">
					<div id="notifications" style="max-height:300px;width:100%;overflow:auto;position:absolute;left:0px;top:38px;z-index:1000"></div>
				</div>
			</div>
		</div>
	</div>
	<div id="main">
		<div style="display:table-row">
			<div id="column1">
				<a href="../">
					<img id="logo" src="img/unify1.png">
				</a>
				<div id="prof_pic" onmouseover="show_pp_button(true)" onmouseout="show_pp_button(false)">
					<a href="../user/<?php echo $user->user_id;?>" >
						<img id="display_picture" src="../profile_pictures/<?php echo $user->user_picture;?>">
					</a>
					<span id="prof_pic_button" class="button" onmouseover="show_pp_button(true)" href="javascript:;" onmousedown="this.className='button pressed';change_prof_pic()" onmouseup="this.className='button'" onmouseout="this.className='button'">Edit your picture</span>
				</div>
				<h2>friend search</h2>
				<input id="friend_search" class="box" type="text" placeholder="Search for friends..." onkeyup="choose_friend()" autocomplete="off">
				<div id="friend_select"></div>
				<h2>your friends</h2>
				<div id="friends"></div>
				<form id="feedback_form" class="box" onsubmit="send_feedback(event)">
					<h2>feedback</h2>
					<textarea id="feedback_content" placeholder="Is there something you think we could improve on?"></textarea>
					<input id="feedback_submit" type="submit" class="button" value="Send Feedback" style="display:block;margin:4px 0px 0px 0px;padding:2px;position:relative;right:0px;width:194px"
					  onmousedown="this.className='button pressed';" onmouseclick="send_feedback(event)" onmouseup="this.className='button'" onmouseout="this.className='button'">
				</form>
			</div><!--/Column 1-->
			<div id="column2">
				<?php
					$display_posts = !isset($selected_user) || (isset($selected_user) && $selected_user->is_friend);
					if (isset($selected_user)) {
				?>
				<div class="user_header box">
					<img class="display_picture" src="../profile_pictures/<?php echo $selected_user->user_picture;?>">
					<div class="info">
						<h2><?php echo $selected_user->user_name;?>'s page</h2>
						<p><?php echo $selected_user->user_name;?> is studying <?php echo $selected_user->course_name;?> at <?php echo $selected_user->university_name;?>.</p>
					</div>
				</div>
				<?php
					if ($user->user_id != $selected_user->user_id && $selected_user->is_friend) {
						view_chat_box($user, $selected_user);
					}
				?>
				<?php
					} else if (isset($selected_group)){
				?>
					<h1><?php echo $selected_group->university_name;?> / <?php echo $selected_group->course_name;?> / <?php echo $selected_group->cohort_start;?></h1>
				<?php
					} else if (!isset($_GET["post_id"])){
				?>
					<h1>your news feed</h1>
				<?php 
					}
					if ($can_post && !isset($_GET["post_id"])) {
				?>
				<div id="new_post" class="box">
					<p class="prompt">What's going on at the moment?</p>
					<form id="post_form" onsubmit="add_post(event)" onkeydown="if(event.keyIdentifier == 'Enter' && !event.shiftKey)add_post(event);" method="POST">
						<div class="post_content_container">
							<div id="post_content" 
								onClick="this.contentEditable='true';this.innerHTML=(this.innerHTML=='Write something...')?'':this.innerHTML;">Write something...</div>
						</div>
						<input id="group_id" type="hidden" name="group_id" value="<?php echo $post_group_id; ?>">
						<input class="button" type="button" value="Post" onmousedown="this.className='button pressed';add_post(event)" onmouseup="this.className='button'" onmouseout="this.className='button'">
					</form>
				</div>
				<?php
					}
					if (isset($selected_user) && !$selected_user->is_friend) {
						if (!$selected_user->request_sent) {
				?>
					<div class="post unify">
						<h1>you are not friends with <?php echo $selected_user->user_name;?></h1>
						<ol>
							<li>Find <?php echo $selected_user->user_name;?>. You must be within <u>10 metres</u> of each other.</li>
							<li>Click "unify" (below)</li>
							<li>Ask <?php echo $selected_user->user_name;?> to go to your page and do steps 1 and 2</li>
						</ol>
						<div id="unify_link" class="button" onclick="start_unify()" onmousedown="this.className='button pressed'" onmouseup="this.className='button'" onmouseout="this.className='button'">unify</div>
						<script type="text/javascript">

							function send_request(position) {
								lat = position.coords.latitude; 
								lng = position.coords.longitude;
								url = "script/user_friend_request.php";

								fd = new FormData();
								fd.append("my_lat",lat);
								fd.append("my_lng",lng);

								$.ajax({
									url: url,
									type: "POST",
									data: fd,
									processData: false,
									contentType: false
								}).done(function (data) {
									location.reload();
								});
							}

							function start_unify() {
								id("unify_link").innerHTML = "please wait...";
								navigator.geolocation.getCurrentPosition(send_request);
							}
						</script>
					</div>
					<?php
						} else {
							?>
							<div class="post unify">
								<h1>unification in progress</h1>
								<ol>
									<li>Ask <?php echo $selected_user->user_name;?> to go to your page and click "unify"</li>
									<li>Unification is complete</li>
								</ol>
							</div>
							<?php
						}
					}

					if ($display_posts) {
						if (isset($selected_user)) {
							?>
							<h1><?php echo $selected_user->user_name?>'s posts</h1>
							<?php
						}
						?>
						<div id="posts"></div>
						<?php
					}
				?>
				<!--/Column 2-->
			</div>
	</div>
</body>
</html>
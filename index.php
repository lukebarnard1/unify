<?php
	include "header.php";
?>
	<script type="text/javascript">
	<?php
		include "index.js";
	?>
	</script>
	<link rel="stylesheet" href="style/index.css"/>
	<link rel="stylesheet" href="style/index_mobile.css" media="only screen and (max-width: 700px)"/>
	<!--End nav bar and header-->
	<form id="prof_pic_form" action="script/user/set_profile_picture.php" method="POST" enctype="multipart/form-data">
		<input type="file" id="prof_pic_file" onchange="sub_prof_pic()" name='file'>
		<input type="hidden" name="r" value="<?php echo $_SERVER["REQUEST_URI"]; ?>">
		<input type="hidden" name="MAX_FILE_SIZE" value="5242880">
	</form>
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
					<span id="prof_pic_button" 
						class="button" 
						onmouseover="show_pp_button(true)" 
						href="javascript:;" 
						onmousedown="this.className='button pressed';change_prof_pic()" 
						onmouseup="this.className='button'" 
						onmouseout="this.className='button'">Edit your picture</span>
				</div>
				<h2>friend search</h2>
				<input id="friend_search" class="box" type="text" placeholder="Search for friends..." onkeyup="choose_friend(this.value,'friend_select',view_friend)" autocomplete="off">
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
						<?php
							if ($selected_user->is_friend && $selected_user->user_id != $user->user_id) {
						?>
						<p style="position:absolute;bottom:0px;right:0px">
							<a href="/script/connection/delete.php?user_id2=<?php echo $selected_user->user_id; ?>">disunify</a>
						</p>
						<?php
							}
							if ($selected_user->user_id == $user->user_id) {
						?>
						<a 
							class="button"
							href="javascript:;" 
							onmousedown="this.className='button pressed';change_prof_pic()" 
							onmouseup="this.className='button'" 
							onmouseout="this.className='button'">Edit your picture</a>
						<?php
							}
						?>
					</div>
				</div>
				<?php
					} else if (isset($selected_group)){
				?>
					<h1><?php echo $selected_group->group_name;?></h1>
				<?php
						if ($selected_group->can_be_added_to) {
							?>
								<div id="member_search"
									onclick="this.contentEditable=true;if(this.innerText=='Add a member...')this.innerText=''"
									onblur="if(this.innerText=='')this.innerText='Add a member...'"
									style="height:20px;background-color:#444;color:#fff;padding:3px;margin-bottom:5px"
									onkeyup="choose_member(this.innerText,'member_select',view_member)"
									autocomplete="off">
									Add a member...
								</div>
								<div id="member_select"></div>
								<div id="group_members"></div>
							<?php
						}
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
								url = "script/connection/request.php";

								fd = new FormData();
								fd.append("my_lat",lat);
								fd.append("my_lng",lng);

								$.ajax({
									url: url,
									type: "POST",
									data: fd,
									processData: false,
									dataType: "json"
								}).done(function (data) {
									if (data.code == "0") {
										location.reload();
									} else {
										id("unify_link").innerHTML = data.message;
									}
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
								<a href="script/connection/delete_request.php">Cancel Unification</a>
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
	<!--Begin footer-->
</body>
</html>

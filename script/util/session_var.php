<?php
	
	include_once "mysql.php";

	if (isset($user)) {
		if (isset($_SESSION["selected_user"])) {
			$selected_user = $_SESSION["selected_user"];
			
			$can_post = ($selected_user->user_id == $user->user_id);
			$post_group_id = -1;

		} else if (isset($_SESSION["selected_group"])) {
			$selected_group = $_SESSION["selected_group"];

			$can_post = $selected_group->posting_enabled;
			$post_group_id = $selected_group->group_id;
		} else {
			//News feed
			$can_post = true;
			$post_group_id = -1;
		}
	}
?>
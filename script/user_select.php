<?php
	//Select a user in order to unify, disunify or see posts from them
	if (isset($selected_user))unset($selected_user);
	if ($logged_in && isset($_GET["user_id"])) {
		$dao = new DAO(false);
		$user_request = $dao->escape($_GET["user_id"]);
		
		$properties = array("user_id","user_name","user_picture","course_name","university_name");

		$dao->myquery("SELECT ".implode(",", $properties)." FROM user ".
						"JOIN cohort ON user.cohort_id=cohort.cohort_id ".
						"JOIN course ON cohort.course_id=course.course_id ".
						"JOIN university ON course.university_id=university.university_id WHERE user_id=\"$user_request\";");

		if ($dao->fetch_num_rows() > 0) {//User exists
			$selected_user  = $dao->fetch_one_obj_part($properties);

			$friends_query = "SELECT * FROM connection WHERE (user_id1=\"$user->user_id\" AND user_id2=\"$selected_user->user_id\") OR ".
															"(user_id2=\"$user->user_id\" AND user_id1=\"$selected_user->user_id\");";
			$dao->myquery($friends_query);
			$is_friend = $dao->fetch_num_rows() != 0 || $selected_user->user_id == $user->user_id || $selected_user->user_id == 1;// I am friends with myself
			$selected_user->is_friend = $is_friend;

			$dao->myquery("SELECT * FROM friend_request WHERE user_id1=\"$user->user_id\" AND user_id2=\"$selected_user->user_id\";");
			$selected_user->request_sent = $dao->fetch_num_rows() != 0;

			$_SESSION["selected_user"] = $selected_user;
			unset($_SESSION["selected_cohort"]);
		}
	}
?>
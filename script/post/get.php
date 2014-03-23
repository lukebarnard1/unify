<?php
	include "../util/session.php";
	include "../util/session_var.php";
	include_once "../util/mysql.php";
	
	//Return posts from a certain cohort
	$query = "";

	$dao = new DAO(false);
	$page_from = "0";
	
	if (!(isset($_POST["post_id"]) || isset($_POST["comment_id"]))) {
		$page_from = $dao->escape($_POST["page_from"]);
		$page_to = $dao->escape($_POST["page_to"]);
		$PAGE_LENGTH = 10;

		$limit = "LIMIT " . ($page_from * $PAGE_LENGTH) . "," . ($page_to - $page_from) * $PAGE_LENGTH;
	}

	$hidden = "(post.post_id in(SELECT post_id FROM hidden_post WHERE user_id=\"$user->user_id\"))";
	$can_vote = "!(post.post_id in(SELECT post_id FROM post_vote WHERE user_id=\"$user->user_id\"))";

	$properties = "post.post_id,user.user_id,post.post_time,post.post_content,post.post_rating_up,post.post_rating_dn,user.user_name,user.user_picture,$hidden AS post_is_hidden,$can_vote AS can_vote";
	if (isset($_POST["comment_id"])) {
		$comment = DataObject::select_one($dao,"comment",
			array("comment_id","post_id"),
			array("comment_id"=>$_POST["comment_id"])
		);
		if ($comment) { 
			$post_id = $comment->post_id;
		}
		$query = "SELECT $properties FROM post JOIN user ON user.user_id=post.user_id WHERE post_id=\"$post_id\" ORDER BY post_time;";
	} else if (isset($_POST["post_id"])) {
		$post_id = $dao->escape($_POST["post_id"]);
		$query = "SELECT $properties FROM post JOIN user ON user.user_id=post.user_id WHERE post_id=\"$post_id\" ORDER BY post_time;";
	} else if (isset($selected_user)){
		$query = "SELECT $properties FROM post JOIN user ON user.user_id=post.user_id WHERE post.group_id=\"-1\" AND post.user_id=\"$selected_user->user_id\" ORDER BY post_time DESC $limit;";
	} else if (isset($selected_group)) {
		$query = "SELECT $properties FROM post JOIN user ON user.user_id=post.user_id WHERE post.group_id=\"$selected_group->group_id\" ORDER BY post_time DESC $limit;";
	} else {
		$query = "SELECT $properties FROM post JOIN user ON post.user_id=user.user_id ".
			"WHERE post.group_id=\"-1\" AND (post.user_id=\"$user->user_id\" OR \"$user->user_id\" in(SELECT user_id2 FROM connection WHERE user_id1=post.user_id) ".
			"OR \"$user->user_id\" in(SELECT user_id1 FROM connection WHERE user_id2=post.user_id) OR post.user_id=\"1\") ".
			" ORDER BY post_time DESC $limit;";
	}

	$dao->myquery($query);
	$posts = $dao->fetch_all_obj();

	foreach ($posts as $post) {
		if (isset($page_from)) {
			$post->page = $page_from;
		}

		$query = "SELECT comment.comment_id,comment.comment_content,user.user_name,user.user_id,user.user_picture, (user.user_id=\"$user->user_id\") AS can_delete FROM comment JOIN user ON user.user_id=comment.user_id WHERE comment.post_id=\"$post->post_id\" ORDER BY comment.comment_id ASC;";
		$dao->myquery($query);
		$post->comments = $dao->fetch_all_obj();
		foreach ($post->comments as $comment) {
			$comment->page = $page_from;
		}
	}

	echo json_encode_strip($posts);
?>
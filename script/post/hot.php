<?php
	include "session.php";
	include "mysql.php";

	$dao = new DAO(false);

	$query = "SELECT post.post_id,user.user_id,post.post_time,post.post_content,post.post_rating_up,post.post_rating_dn,user.user_name,
				(post.post_rating_up + post.post_rating_dn) AS rating,
				(SELECT COUNT(*) FROM comment WHERE comment.post_id=post.post_id) AS comments
				FROM post JOIN user ON post.user_id=user.user_id 
				WHERE post.post_time > DATE(NOW()) AND post.user_id!=\"$user->user_id\" 
				ORDER BY rating DESC,comments DESC LIMIT 5;";

	$dao->myquery($query);
	echo $dao->fetch_json_part(array("post_id","user_id","post_time","post_content","post_rating_up","post_rating_dn","user_name"));
?>
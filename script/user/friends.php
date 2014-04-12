<?php
	// Get all of the friends of a user
	include_once("../util/session.php");
	include_once("../util/mysql.php");
	$friends_query = "SELECT user_id,user_name,user_picture,course.course_name,university.university_name,cohort.cohort_start FROM user ".
	"JOIN cohort ON user.cohort_id=cohort.cohort_id ".
	"JOIN course ON cohort.course_id=course.course_id ".
	"JOIN university ON university.university_id=course.university_id ".
	"WHERE (user_id in(SELECT user_id1 FROM connection WHERE user_id2=\"$user->user_id\") ".
	   "OR user_id in(SELECT user_id2 FROM connection WHERE user_id1=\"$user->user_id\")) ORDER BY user.user_name ASC;";
	$dao = new DAO(false);
	$dao->myquery($friends_query);
	echo $dao->fetch_json();
?>
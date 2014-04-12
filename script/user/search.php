<?php
	//Search for people that the user might be refering to when
	// a partial name is given
	include_once("../util/mysql.php");
	include "../util/session.php";

	$dao = new DAO(false);
	$name = $dao->escape($_POST["q"]);
	$name = trim(strtolower($name));

	if ($name != "") {
		//Find the select the cohort, course and university of the user
		$query = "SELECT cohort.cohort_id,course.course_id,university.university_id FROM user ".
				"JOIN cohort ON user.cohort_id=cohort.cohort_id ".
				"JOIN course ON cohort.course_id=course.course_id ".
				"JOIN university ON university.university_id=course.university_id ".
				"WHERE user_id=\"$user->user_id\";";
		$dao->myquery($query);
		$row = $dao->fetch_one();

		$cohort_id = $row["cohort_id"];
		$course_id = $row["course_id"];
		$university_id = $row["university_id"];

		if (isset($_POST["group_id"])) {
			$group_id = $dao->escape($_POST["group_id"]);
			$not_in_group = "AND NOT EXISTS(SELECT grouping_id FROM grouping WHERE user.user_id=grouping.user_id AND grouping.group_id=\"$group_id\")";
		} else {
			$not_in_group = "";
		}

		//Take the query and return a json list of courses that might match this one
		$dao->myquery("SELECT user_id,user_name,cohort_start,course_name,university_name,user_picture FROM user ".
				"JOIN cohort ON user.cohort_id=cohort.cohort_id ".
				"JOIN course ON cohort.course_id=course.course_id ".
				"JOIN university ON university.university_id=course.university_id ".
				"WHERE (cohort.cohort_id=\"$cohort_id\" OR ".
					   "course.course_id=\"$course_id\" OR ".
					   "university.university_id=\"$university_id\") AND ".
					   "LOWER(user_name) LIKE \"%$name%\" AND user_id!=\"$user->user_id\" $not_in_group;");

		echo $dao->fetch_json();
	} else {
		echo "[]";
	}
?>

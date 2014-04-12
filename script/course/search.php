<?php
	//Search for a course given a partial course name and uni id
	include_once("../util/mysql.php");
	
	$dao = new DAO(false);
	
	$uni_id = $dao->escape($_GET["university_id"]);
	$course = $dao->escape($_GET["course"]);
	$course = strtolower($course);
	
	//Take the query and return a json list of courses that might match this one

	$dao->myquery("SELECT course_id,course_name FROM course WHERE LOWER(course_name) LIKE '%$course%' AND university_id = '$uni_id';");
	echo $dao->fetch_json_part(array("course_id","course_name"));
?>
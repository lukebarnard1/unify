<?php
	include_once("../util/mysql.php");
	
	$dao = new DAO();
	$dao->myquery("SELECT * FROM university;");
	echo $dao->fetch_json();
?>
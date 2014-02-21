<?php
	include "../util/mysql.php";
	
	$dao = new DAO();
	$dao->myquery("SELECT * FROM university;");
	echo $dao->fetch_json();
?>
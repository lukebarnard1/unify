<?php
	include_once("../util/mysql.php");
	include "../util/pwd.php";

	$dao = new DAO(true);

	$user_password = $dao->escape(salt($_POST["user_password"]));

	$user->user_id = $dao->escape($_POST["user_id"]);
	$conf_rnd = $dao->escape($_POST["conf_rnd"]);

	$query = "SELECT * FROM reset_request WHERE user_id=\"$user->user_id\" AND conf_rnd=\"$conf_rnd\";";

	$dao->myquery($query);
	if ($dao->fetch_num_rows() == 1) {
		$query = "DELETE FROM reset_request WHERE user_id=\"$user->user_id\" AND conf_rnd=\"$conf_rnd\";";

		$dao->myquery($query);

		$new_password_query = "UPDATE user SET user_password=\"$user_password\" WHERE user_id=\"$user->user_id\";";

		$dao->myquery($new_password_query);
	}

?>	
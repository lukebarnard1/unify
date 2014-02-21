<?php
	include "../util/session.php";
	include "../util/mysql.php";
	
	$dao = new DAO(false);
	
	$my_lat = $dao->escape($_POST["my_lat"]);
	$my_lng = $dao->escape($_POST["my_lng"]);
	
	$query = "SELECT req_id,lat,lng FROM friend_request WHERE user_id1=\"$selected_user->user_id\" AND user_id2=\"$user->user_id\";";

	$dao->myquery($query);
	if ($dao->fetch_num_rows() > 0) {
		$row = $dao->fetch_one();
		$req_id = $row["req_id"];

		$lng1 = $row["lng"];
		$lng2 = $my_lng;
		$lat1 = $row["lat"];
		$lat2 = $my_lat;

		$dlng = $lng1  - $lng2;
		$distance = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($dlng)));
		$distance = acos($distance);
		$distance = rad2deg($distance);
		$distance = $distance * 60 * 1.1515 * 1.609344;
		
		$threshold = 0.01 + 0.01;//20m!
		if ($distance < $threshold) {
			$query = "INSERT INTO connection VALUES(NULL,\"$user->user_id\",\"$selected_user->user_id\");";
			$dao->myquery($query);
		} else {
			error_log("Distance too far for user ".$user->user_name." and ".$selected_user->user_name);
		}
		$query = "DELETE FROM friend_request WHERE req_id=\"$req_id\";";//Whether successful or not
		$dao->myquery($query);
	} else {
		$query = "INSERT INTO friend_request VALUES(NULL,\"$user->user_id\",\"$selected_user->user_id\",\"$my_lat\",\"$my_lng\");";
		$dao->myquery($query);
	}
?>
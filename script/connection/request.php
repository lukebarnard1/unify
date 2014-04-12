<?php
	// Attempt a unification:
	//  - If the other user has already requested one, then allow 
    //    them to be friends if they're close enough. If they are
    //    not close enough, delete the other user's attempt.
	//  - If the other user hasn't already requested (via this 
 	// 	  same script), then add the request.
	include "../util/session.php";
	include_once("../util/mysql.php");
	include_once("../util/status.php");
	
	$dao = new DAO(false);
	
	$connection_properties = array("user_id1"=>$user->user_id, "user_id2"=>$selected_user->user_id);

	$existing_request = DataObject::select_one($dao, "friend_request", array("req_id"), $connection_properties);

	if ($existing_request == NULL) {

		$existing_connection = DataObject::select_one($dao, "connection", array("connection_id"), $connection_properties);

		if ($existing_connection == NULL) {
			$my_lat = doubleval($dao->escape($_POST["my_lat"]));
			$my_lng = doubleval($dao->escape($_POST["my_lng"]));
			
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
				
				$threshold = 0.02;//20m!
				if ($distance < $threshold) {
					$new_connection = DataObject::create($dao, "connection", $connection_properties);
					$new_connection->commit();
					echo Status::json(0, "You are now unified!");
				} else {
					echo Status::json(3, "You were not close enough");
				}
				$query = "DELETE FROM friend_request WHERE req_id=\"$req_id\";";//Whether successful or not
				$dao->myquery($query);
			} else {
				$query = "INSERT INTO friend_request VALUES(NULL,\"$user->user_id\",\"$selected_user->user_id\",\"$my_lat\",\"$my_lng\");";
				$dao->myquery($query);
				echo Status::json(0, "Friend request made");
			}
		} else {
			echo Status::json(1, "You are already unified!");
		}
	} else {
		echo Status::json(2, "Request for unification already exists!");
	}

?>
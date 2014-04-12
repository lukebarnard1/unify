<?php
	//Confirm a user registration
	include "script/util/mysql.php";
	include "script/util/redirect.php";

	$dao = new DAO(false);
	$rnd = $dao->escape($_GET["rnd"]);

	//Delete the confirmation
	//Fix the users email!

	//Find the user id first
	$confirmation = DataObject::select_one($dao,"confirmation",
		array("conf_id","user_id"),
		array("conf_rnd"=>$rnd)
	);
	if ($confirmation != NULL) {
		$user_id = $confirmation->user_id;
		
		//Then delete the confirmation
		if ($confirmation->delete()) {
			//Find the user that it relates to
			$user = DataObject::select_one($dao, "user", 
				array("user_id", "user_email"),
				array("user_id"=>$user_id)
			);
			if ($user != NULL) {
				$user_email = $user->user_email;

				//Correct their email to enable login

				$space_pos = strpos($user_email, " ") + 1;
				$user_email = substr($user_email,$space_pos);//Take everything after space

				//Change and commit
				$user->user_email = $user_email;
				if ($user->commit()) {
					redirect("welcome/?m=10");
				} else { //Faliure to change the user's email
					//User should be deleted so they can register again

					$user->delete();

					redirect("welcome/?m=6"); //Register again
				}
			} else {
				//The user can feel free to register again
				redirect("welcome/?m=6"); //Register again
			}
		}
	} else {
		//The user may have already confirmed their account
		redirect("welcome/?m=15"); 
	}
?>

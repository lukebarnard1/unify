<?php
	//Register a user
	include "../util/pwd.php";
	include_once("../util/mysql.php");
	include "../util/redirect.php";
	
	include "../mail/send.php";
	
	$dao = new DAO(false);

	if (isset($_POST["user_name"]) && 
		isset($_POST["user_email"]) && 
		isset($_POST["user_password"]) && 
		isset($_POST["university_id"]) && 
		isset($_POST["course_id"]) && 
		isset($_POST["start_year"]) && 
		isset($_POST["start_month"])) {
		
		$user_name = $dao->escape($_POST["user_name"]);
		$user_email = $dao->escape($_POST["user_email"]);
		$user_password = $dao->escape(salt($_POST["user_password"]));
		
		$university_id = $dao->escape($_POST["university_id"]);
		$course_id = $dao->escape($_POST["course_id"]);
		$cohort_start = $dao->escape($_POST["start_year"]) . "-" . $dao->escape($_POST["start_month"]) . "-1"; 
		
		$dao->myquery("SELECT user_email FROM user WHERE user_email LIKE \"%$user_email\";");
		if ($dao->fetch_num_rows() == 0) {
			//Insert the user into the database, and retreive the user_id
			
			$cohort = DataObject::select_one($dao, "cohort", array("cohort_id","group_id"),array("cohort_start"=>$cohort_start,"course_id"=>$course_id));

			if (! $cohort) {
				//Cohort does not exist, insert it

				$group = DataObject::create($dao, "user_group", array("group_name"=>"Cohort Group"));
				$group->commit();
				$group_id = $group->get_primary_id();

				$cohort = DataObject::create($dao, "cohort", array("course_id"=>$course_id,"group_id"=>$group_id,"cohort_start"=>$cohort_start));
				$cohort->commit();			
			}

			$uncomfirmed = salt($user_email);

			$user = DataObject::create($dao,"user",
				array(
					"cohort_id" => $cohort->get_primary_id(),
					"user_name" => $user_name,
					"user_email" => "$uncomfirmed $user_email",
					"user_password" => $user_password,
					"user_picture" => "default"
				)
			);

			if ($user->commit()) {

				//Add the user to the cohort's group
				$grouping = DataObject::create($dao,"grouping",
					array(
						"group_id" => $cohort->group_id,
						"user_id" => $user->get_primary_id()
					)
				);

				$grouping->commit();

				$dao->myquery("SELECT MAX(conf_id) AS m FROM confirmation;");
				$maxid = $dao->fetch_one();
				if ($maxid) {
					$rnd = salt(",jag,wd873423%Ed.fkug".$maxid["m"]);
				} else {
					$rnd = salt(",jag,wd873423%Ed.fkug".rand());
				}
				//send rnd to the user and a link which will return rnd to the server for confirmation	
				
				$send_email = false;

				//If the confirmation has already been sent, just resend it. Don't craete a new confimation
				if (NULL != DataObject::select_one($dao, "confirmation", array("conf_id"), array("user_email"=>$user_email))){
					$send_email = true;
				} else {
					$conf = DataObject::create($dao, "confirmation", array("conf_rnd"=>$rnd,"user_id"=>$user->get_primary_id(),"user_email"=>$user_email));

					if ($conf->commit()){
						$send_email = true;
					} else {
						redirect("../../register/",array_merge(array("m" => "6"),$_POST));//This should never happen
					}
				}

				if($send_email){
					$subject = "Confirm your account";
					$body = "<p>Hello ".$user_name.",</p>".
								"<p>Thank you for joining Unify! Trust me, this is the best decision you've ever made.</p>".
								"<p>Click <a href=\"".$SITE_URL."confirm.php?rnd=$rnd\">CONFIRM</a> to confirm your account and to start using Unify.<br><br>".
								"Click <a href=\"".$SITE_URL."unconfirm.php?rnd=$rnd\">UNCONFIRM</a> if you have no idea why you are receiving this email.".
								" This will prevent this email address being used on Unify.</p>".
								"<p>Best Wishes,<br>".
								"The Unify Team</p>";
					
					$success = mail_message($user_email,$subject,$body);
					
					if (!$success) {
						redirect("../../register/",array_merge(array("m" => "5"),$_POST));//Could not send email
					} else {
						redirect("../../welcome/", array("m" => "4"));//Successful
					}
				} else {
					redirect("../../register/",array_merge(array("m" => "6"),$_POST));//Could not create account (insert into database)
				}
			} else {
				redirect("../../register/",array_merge(array("m" => "6"),$_POST));//Could not create account (insert into database)
			}
		} else {
			redirect("../../register/",array_merge(array("m" => "7"),$_POST));//Email already exists
		}
	} else {
		redirect("../../register/",array_merge(array("m" => "8"),$_POST));//Registration form incomplete
	}
?>
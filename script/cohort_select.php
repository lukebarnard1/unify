<?php
	if (isset($selected_cohort))unset($selected_cohort);
	if ($logged_in) {
		$dao = new DAO(false);
		if (isset($_GET["cohort_id"])) {
			$cohort_request = $dao->escape($_GET["cohort_id"]);
			if ($cohort_request == $user->cohort_id) {
				$dao->myquery("SELECT cohort_id,cohort.group_id,group_name,cohort_start,course.course_name,university.university_name FROM cohort 
					JOIN course ON cohort.course_id=course.course_id 
					JOIN university ON university.university_id=course.university_id
					JOIN user_group ON cohort.group_id=user_group.group_id WHERE cohort_id=\"$cohort_request\";");
				$row = $dao->fetch_one_obj();

				if ($dao->fetch_num_rows() > 0) {//It exists
					$selected_group = new stdClass();
					$selected_group->cohort_id = $row->cohort_id;
					$selected_group->course_name = $row->course_name;
					$selected_group->university_name = $row->university_name;
					$selected_group->group_id = $row->group_id;
					$selected_group->group_name = $row->group_name;

					$d = new DateTime($row->cohort_start);
					$selected_group->cohort_start = $d->format('jS F Y');

					$selected_group->posting_enabled = ($selected_group->cohort_id == $user->cohort_id);

					$_SESSION["selected_group"] = $selected_group;
					unset($_SESSION["selected_user"]);
				}
			} else {
				redirect("../");
			}
		}
	}
?>
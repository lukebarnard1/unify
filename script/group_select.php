<?php
	// if (isset($selected_group))unset($selected_group);
	if ($logged_in) {
		$dao = new DAO(false);
		if (isset($_GET["group_id"])) {
			$group_request = $dao->escape($_GET["group_id"]);

			$user_in_group = (NULL != DataObject::select_one($dao, "grouping", array("grouping_id"),array("group_id" => $group_request, "user_id" => $user->user_id)));

			if ($user_in_group) {
				$row = DataObject::select_one($dao, "user_group", array("group_id","group_name"), array("group_id"=>$group_request));
				if ($row) {//It exists

					$selected_group = new stdClass();
					$selected_group->group_id = $row->group_id;
					$selected_group->group_name = $row->group_name;
					$selected_group->posting_enabled = true;
					$selected_group->can_be_added_to = true;

					$_SESSION["selected_group"] = $selected_group;
					unset($_SESSION["selected_user"]);
				} else {
					redirect("../");
				}
			} else {
				redirect("../");
			}
		}
	}
?>
<?php
	function redirect($address,$vars = array()) {
		if (count($vars) > 0) {
			$text_vals = "?".http_build_query($vars);
		} else {
			$text_vals = "";
		}
		header("location:".$address.$text_vals);
	}
?>
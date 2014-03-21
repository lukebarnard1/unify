<?php
	/**
		This header file is terminated with </body></html>
	**/
	include_once "script/util/session.php";
	include_once "script/util/constants.php";

	include_once "script/util/redirect.php";
	if (!$logged_in) {
		redirect("../welcome/?r=".$_SERVER["REQUEST_URI"]);
	}
	include_once "script/util/mysql.php";

	include "script/user_select.php";
	include "script/cohort_select.php";

	if (! (isset($_GET["user_id"]) || isset($_GET["cohort_id"]))) {
		unset($selected_user);
		unset($_SESSION["selected_user"]);
		unset($selected_group);
		unset($_SESSION["selected_group"]);
	}

	//Reload variables again
	include "script/util/session_var.php";

	include "script/util/display_message.php";
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1"/>
	<link rel="stylesheet" href="style/header.css"/>
	<link rel="stylesheet" href="style/header_mobile.css" media="only screen and (max-width: 700px)"/>
	<title>Unify - Bringing students together</title>

	<script type="text/javascript" src="jquery.js"></script>
	<script type="text/javascript" src="Template.js"></script>
	<script type="text/javascript" src="header.js"></script>
	<script type="text/javascript" src="util.js"></script>
</head>
<body>
	<?php
		if ($user->user_id != $DEVELOPER_ID) {
	?>
	<script type="text/javascript">
		if (document.location.hostname == "unify.lukebarnard.co.uk") {
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-16927282-2']);
		  _gaq.push(['_trackPageview']);
		
 		 (function() {
  		  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
 		   ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  		  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		}
	</script>
	<?php 
		}
	?>
	<div class="nav_bar">
		<div class="inner">
			<?php
				$links = array("unify"=>"/",
					"chat"=>"/chat",
					"you"=>"/user/$user->user_id",
					"course"=>"/cohort/$user->cohort_id",
					"logout"=>"/script/user/logout.php");

				foreach($links as $text => $link) {
					$selected = ($link == $_SERVER["REQUEST_URI"]);
					?>
					<a id="nav_button_<?php echo $text ?>" class="nav_button <?php echo $selected?"selected":""?>" href="<?php echo $link?>"><?php echo $text?></a>
					<?php
				}
			?>
			<div class="nav_button notif" onmouseover="notif_container.style.display='block'" onmouseout="notif_container.style.display='none'">
				notifications
				<div id="notif_container" style="display:none">
					<div id="notifications" style="max-height:300px;width:100%;overflow:auto;position:absolute;left:0px;top:38px;z-index:1000"></div>
				</div>
			</div>
		</div>
	</div>
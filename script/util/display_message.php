<?php
	include_once("mysql.php");

	function message($message_id) {
		$dao = new DAO(false);
		$message = DataObject::select_one($dao, "message", array("message_id","message_title","message_description"), array("message_id"=>$message_id));

		if (! $message) {
			$message = DataObject::select_one($dao, "message", array("message_id","message_title","message_description"), array("message_id"=>1));
		}

		return $message;
	}

	function display_message($message_id) {
		$message = message($message_id);
		if (!$message)return;
		$message_title = $message->message_title;
		$message_description = $message->message_description;
?>
<style>
	#message_box a {
		color:#888;
	}
</style>
<div id="message_box_container" style="position:relative;z-index:1000;position:absolute;left:0px;top:0px;width:100%;height:100%;font-size:14px;background-color:rgba(255,255,255,0.5);">
	<div id="message_box" style="width:350px;font-family:Arial,sans-serif;background-color:#fdfdfd;border-width:1px;border-style:solid;border-color:#ddd;padding:5px;margin:auto;margin-top:20%;">
		<h1 style="font-size:20pt;margin:0px;"><?php echo $message_title; ?></h1>
		<p style="margin-bottom:10px;"><?php echo $message_description; ?></p>
		<a href="http://unify.lukebarnard.co.uk">Home</a> -
		<a id="close_link" href="javascript:return false;" onclick="c = document.getElementById('message_box_container');c.parentNode.removeChild(c);event.preventDefault();">Close this message box</a>
		<script>
			document.getElementById("close_link").focus();
		</script>
	</div>
</div>
<?php
	}
	// var_dump($_SERVER["REQUEST_URI"]);
	// var_dump($_GET);
	if (isset($_GET["m"])) {
		$m = $_GET["m"];
		display_message($m);
	}

?>

<?php
	include "header.php";
	include_once "script/view/chat_box_json.php";
?>
	<link rel="stylesheet" href="style/chat.css"/>
	<link rel="stylesheet" href="style/chat_mobile.css" media="only screen and (max-width: 700px)"/>
	<script type="text/javascript">
		<?php
			include "chat.js";
		?>
	</script>
	<audio id="snd_msg">
		<source src="sound/msg1.wav" preload="auto">
		<source src="sound/msg1.mp3" preload="auto">
	</audio>
	<div id="main">
		<div id="friend_selector">
			<div id="friends">
			<h1>friends</h1></div>
		</div>
		<table id="conversations_table">
			<tr id="conversations">
			</tr>
		</table>
	</div>
</body>
</html>
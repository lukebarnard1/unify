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
		<div style="width:400px;margin:auto">
			<table id="conversations_table">
				<tr id="conversations">
				</tr>
			</table>
		</div>
		<div id="friend_selector">
			<div id="friends"></div>
		</div>
		<div id="arrows">
			<div style="position:relative;top:-25px;left:-250px;">
				<a class="arrow left" href="javascript:;" onclick="move_current_conversation_left()"><img src="img/upvote.png"></a>
				<a class="arrow right" href="javascript:;" onclick="move_current_conversation_right()"><img src="img/downvote.png"></a>
			</div>
		</div>
	</div>
</body>
</html>
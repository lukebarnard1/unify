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
	<table id="main">
		<tr>
			<td style="vertical-align: top; padding-top: 30px">
				<div class="convo_table_container">
					<table id="conversations_table">
						<tr id="conversations">
						</tr>
					</table>
				</div>
			</td>
		</tr>
		<tr class="friends_row">
			<td id="friend_selector" style="height:100%">
				<table id="friends" style="height:100%"></table>
			</td>
		</tr>
	</table>
		<div id="arrows">
			<div>
				<a class="arrow left" href="javascript:;" onclick="move_current_conversation_left()"><img src="img/upvote.png"></a>
				<a class="arrow right" href="javascript:;" onclick="move_current_conversation_right()"><img src="img/downvote.png"></a>
			</div>
		</div>
</body>
</html>
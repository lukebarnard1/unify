<?php
	function view_chat_box($user, $user2) {
?>
<div class="chat_box">
	<h1>conversation with <?php echo $user2->user_name;?></h1>
	<audio id="snd_msg">
		<source src="sound/msg1.wav" preload="auto">
		<source src="sound/msg1.mp3" preload="auto">
	</audio>
	<script>
		var latest_pulled = -1;
		var latest_seen_by_u2 = -1;

		var title_flashed = false;
		var original_title = "Unify - Bringing students together";

		var view_chat_msg = new Template("../view/chat_msg.html");

		modifier_messages = function(data) {
			for (i = 0;i < data.length; i++) {
				message = data[i];
				if (message.user_id1 == "<?php echo $user->user_id?>") {
					data[i].msg_seen_indication = message.msg_seen=="1"?"(seen)":"(unseen)";
					if (message.msg_seen=="1") {
						latest_seen_by_u2 = parseInt(message.msg_id);
					}
				}
			}
			return data;
		}

		var user2 = <?php echo json_encode($user2)?>;
		var flashing_interval = 1000;

		load_messages(false);//Load messages to begin with, without sound

		function timed_pull() {
			load_messages(true);
		}

		setInterval(timed_pull,3000);
		
		function send_message(event) {
			event.preventDefault();
			message = sanitise_input(event.target.innerText);

			event.target.innerHTML = "";
			fd = new FormData();
			fd.append("user_id","<?php echo $user2->user_id?>");
			fd.append("msg_content",message);

			$.ajax({
				url: 'script/chat_msg/add.php',
				type: 'POST',
				processData: false,
				contentType: false,
				data: fd
			}).done(function (data) {
				load_messages(false);
			});
		}

		function play_message_sound() {
			document.getElementById('snd_msg').play();
		}

		function messages_loaded(data, timed_pull) {
			lp = -1;
			for (i = 0;i < data.length; i++) {
				j = parseInt(data[i].msg_id);
				if (j > lp) {
					lp = j;
				}
			}

			//If there are messages at all
				//There are more messages available or some messages "seen" needs updating
			if (lp != -1 && (latest_pulled != lp || lp > latest_seen_by_u2)) {
				//If more messages arrived and it is a timed pull
				if (latest_pulled != lp && timed_pull) {
					start_flashing();
					play_message_sound();
				}
				for (i = 0; i < data.length; i++) {
					element = id("chat_msg" + data[i].msg_id);
					if (element) {
						element.parentNode.removeChild(element);
					}
				}
				latest_pulled = lp;
			
				conv_div = $("#conversation<?php echo $user2->user_id?>");
				conversation = conv_div.html();
				conv_div.html(conversation + view(modifier_messages(data), view_chat_msg));

				conv_div.scrollTop(conv_div[0].scrollHeight);
			}
		}

		function load_messages(timed_pull) {
			$.ajax({
				url: 'script/chat_msg/get.php',
				type: 'POST',
				dataType: "json",
				data: {
					user_id:"<?php echo $user2->user_id;?>",
					latest_pulled: latest_pulled,
					latest_seen_by_u2: latest_seen_by_u2
				}
			}).done(function (data) {
				//A timed pull results in a sound being played, otherwise no sound is played
				messages_loaded(data,timed_pull);
			});
		}

		function reset_title() {
			document.title = original_title;
		}

		function flash_title() {
			if (!document.hidden){//id("conversation_input") == document.activeElement) {
				stop_flashing();
				return;
			}

			if (title_flashed) {
				document.title = "New message from " + user2.user_name;
			} else {
				reset_title();
			}
			title_flashed = !title_flashed;
		}

		function stop_flashing() {
			clearInterval(flashing_interval);
			reset_title();
		}

		function start_flashing() {
			title_flashed = true;
			clearInterval(flashing_interval);
			flashing_interval = setInterval(flash_title,flashing_interval);
			flash_title();
		}
	</script>
	<div class="conversation" id="conversation<?php echo $user2->user_id;?>">

	</div>
	<div id="conversation_input" class="box conversation_input" contenteditable="true" onClick="if(this.innerHTML=='Send a message...')this.innerHTML='';"
	 onkeydown="if(event.keyCode == 13 && !event.shiftKey)send_message(event);">Send a message...</div>
</div>
<?php
	}
?>
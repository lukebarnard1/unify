
user = <?php echo json_encode($user)?>;

var conversations = {};

//Cancel all ajax requests
// $(window).unload( function () { for (key in conversations) { conversations[key].a.abort(); }} );

// Each conversation has "latest_pulled", "latest_seen_by_u2" and messages
// This will be indexed by user id

var view_chat_msg = new Template("../view/chat_msg.html");
var view_conversation = new Template("../view/conversation.html");
var view_friend_select = new Template("../view/friend_select.html");

var global_latest_pulled = 0;

var current_conversation = 0;
var current_user = 0;

var flashing_interval = 0;
var animation_interval = 0;

var title_animation = [];
var title_animation_prefix = "";
var frame = 0;

modifier_messages = function(data) {
	for (i in data) {
		message = data[i];
		if (message.user_id1 == user.user_id) {
			data[i].msg_seen_indication = message.msg_seen=="1"?"(seen)":"(unseen)";
			data[i].user_name = "You";
			data[i].class_name = "sent";
		} else {
			data[i].class_name = "received";
		}
	}
	return data;
}

function reset_title() {
	document.title = original_title;
}

function flash_title() {
	if (!document.hidden) {
		stop_flashing();
		return;
	}

	if (title_flashed) {
		document.title = "Unify - New Messages";
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
	original_title = document.title;
	title_flashed = true;
	clearInterval(flashing_interval);
	flashing_interval = setInterval(flash_title,500);
	flash_title();
}

function animate_title () {
	if (!document.hidden) {
		clearInterval(animation_interval);
		reset_title();
	}else{
		mid_animation = frame<title_animation.length;

		document.title = title_animation_prefix + ": " + title_animation[mid_animation?(frame<0?0:frame):(title_animation.length-1)] + (mid_animation?"...":"");
		frame = frame + 1; // + 10 for the delay at the end
		if (frame == title_animation.length + 10) {
			frame = -10;
		}
	}
}

function start_ticking(prefix, text) {
	original_title = document.title;

	width = 20;//20 Character-wide animation
	frames = text.length + 1 - width;

	if (frames < 1)frames = 1;

	title_animation_prefix = prefix;
	title_animation = [];
	for (var i = 0; i < frames; i++) {
		title_animation[i] = text.substring(i, i + 20);
	}

	frame = -10;
	clearInterval(animation_interval);
	animation_interval = setInterval(animate_title, 100);
}

function alert_user(message_user_name, message_content) {
	document.getElementById('snd_msg').play();
	start_ticking(message_user_name, message_content);
}

function queue_jump(convo_div, follow) {
	convo_div.parent().parent().prependTo(convo_div.parent().parent().parent());
	if (follow) {
		set_current_conversation_first(false);
	} else {
		set_current_conversation(current_user, false);
	}
}

function Conversation(conversation) {
	for (key in conversation) {
		this[key] = conversation[key];
	}

	convo_html = view_conversation.render([this]);
	var user_id = this.user_id;

	v = $("#conversations");
	v.append(convo_html);

	this.convo_div = $("#conversation"+user_id);
	var convo_div = this.convo_div;
	id("conversation"+user_id).user_id = user_id;

	var messages_loaded = function (messages, timed_pull, animate) {
		pulled_so_far = latest_pulled;

		for (msg_id in messages) {
			message = messages[msg_id];
			msg_int_id = parseInt(msg_id);
			if (msg_int_id > latest_pulled) {
				latest_pulled = msg_int_id;
			}
			if (message.user_id1 == user.user_id && message.msg_seen == "1") {
				latest_seen_by_u2 = parseInt(msg_id);
			}

			message = messages[msg_id];
			element = id("chat_msg" + msg_id);
			if (element) {
				element.parentNode.removeChild(element);
			}
		}

		//Append the rendering of new messages
		convo_div.append(view_chat_msg.render( modifier_messages(messages) ));
  		
		if (pulled_so_far != latest_pulled) {
			if (global_latest_pulled < latest_pulled) {
				global_latest_pulled = latest_pulled;

				user_sent = (user_id == user.user_id);

				queue_jump(convo_div, user_sent || current_user == user_id);
	  		}
			convo_div.delay(timed_pull?0:100).animate({scrollTop: convo_div.prop("scrollHeight")}, 0);
			if(timed_pull) {
				msg = messages[global_latest_pulled];
				alert_user(msg.user_name, msg.msg_content);
			}
		}
	}

	var latest_pulled = -1;
	var latest_seen_by_u2 = -1;

	this.load_messages = function (timed_pull, animate) {
		var callback = this;
		// console.log("Loading more after "+latest_pulled);
		$.ajax({
			url: "script/chat_msg/get.php",
			type: "POST",
			dataType: "json",
			data: {
				user_id: user_id,
				latest_pulled: latest_pulled,
				latest_seen_by_u2: latest_seen_by_u2
			}
		}).done(function (data) {
			//A timed pull results in a sound being played, otherwise no sound is played	
			if (typeof data[user_id] !== "undefined") {
				messages_loaded(data[user_id].messages, timed_pull, animate);
			}
		});
	}

	messages_loaded(this.messages, false, false);

	this.add_message = function (message) {
		var user_id = this.user_id;

		$.ajax({
			url: "script/chat_msg/add.php",
			type: "POST",
			dataType: "json",
			async: false,
			data: {
				user_id: user_id,
				msg_content: message
			}
		}).done(function (data) {
			if (data.code == "0") {
				inp_div = id("conversation_input" + user_id);
				inp_div.innerHTML = "";
				$(inp_div).focus();
			} else {
				console.log(data);
			}
		});
		this.load_messages(false, true);
	}
}

function scroll_all() {
	for (key in conversations) {
		convo_div = $("#conversation" + key);
		convo_div.scrollTop(convo_div[0].scrollHeight);
	}
}

function update_current_conv(animate) {
	console.log("Update to " + current_conversation);
	left = -400 * current_conversation;

	$("#conversations_table").animate({marginLeft:left+"px"},animate?500:0);

	// console.log("Current user: " +current_user);
}

function is_current_user(user_id) {
	return current_conversation == $(".chat_msg_container").index(conversations[user_id].convo_div);
}

function set_current_conversation_first (animate) {
	current_conversation = 0;
	current_user = $(".chat_msg_container")[0].user_id;
	update_current_conv(animate);
}

function set_current_conversation(user_id, animate) {
	if (user_id > 0) {
		current_conversation = $(".chat_msg_container").index(conversations[user_id].convo_div);
		current_user = user_id;
		update_current_conv(animate);
	}
}

function move_current_conversation_right() {
	if (current_conversation < Object.keys(conversations).length - 1) {
		current_conversation++;
		current_user = $(".chat_msg_container")[current_conversation].user_id;
		update_current_conv(true);
	}
}

function move_current_conversation_left() {
	if (current_conversation > 0) {
		current_conversation--;
		current_user = $(".chat_msg_container")[current_conversation].user_id;
		update_current_conv(true);
	}
}

/**
 * Initial load of messages. This renders conversations too.
 */
function initial_load(data) {
	console.log("initial_load");
	conversations = data;

	conversations_exist = false;

	for (key in conversations) {
		conversations_exist = true;
		conversations[key] = new Conversation(conversations[key]);
	}

	id("friend_selector").style.display = "block";
	ajax_request(id("friends"), true, view_friend_select, modifier_relay, "script/user/friends.php");
}

function timed_load() {
	for (key in conversations) {
		conversations[key].load_messages(true,true);
	}
	// scroll_all();
}

setInterval(timed_load,3000);

/**
 * Load existing conversations on to the page.
 */
function load_conversations() {
	//Load the most recent 100 messages from all friends into this page
	//Each message comes with a user_id, so just direct it to the correct conversation
	$.ajax({
		url: "script/chat_msg/get.php",
		type: "POST",
		dataType: "json",
		data: {
			user_id: -1,
			latest_pulled: -1,
			latest_seen_by_u2: -1
		}
	}).done(function (data) {
		//A timed pull results in a sound being played, otherwise no sound is played
		initial_load(data);
	});
}

/**
 * Add a conversation with someone to the conversations box (or switch to it if it exists).
 */
function add_conversation(user_id) {
	if (conversations[user_id]) {
		set_current_conversation(user_id,true);
		return;
	}

	$.ajax({
		url: "script/chat_msg/get.php",
		type: "POST",
		dataType: "json",
		data: {
			user_id: user_id,
			latest_pulled: -1,
			latest_seen_by_u2: -1
		}
	}).done(function (data) {
		new_convo = new Conversation(data[user_id]);
		new_convo.load_messages(false,false);
		conversations[user_id] = new_convo;
	  	new_convo.convo_div.parent().parent().prependTo(new_convo.convo_div.parent().parent().parent());
		set_current_conversation(user_id,true);
	});
}

/**
 * Display a dialogue of the users friends allowing them to
 * select one of them, whose id is then returned.
 */
function select_friend(user_id) {
	add_conversation(user_id);
}

function send_message(event, user_id) {
	event.preventDefault();
	message = sanitise_input(event.target.innerText);

	event.target.innerHTML = "";
	fd = new FormData();
	fd.append("user_id",user_id);
}

window.addEventListener("load",load_conversations);

// $(".chat_msg_container").css({width:"10px"});

// $(".jq_up_hover").onmouseover(function () {$(this).css({position: "relative"}).animate({top: "-10px"},500)});

// function timed_pull() {
	
// }

// function play_message_sound() {
// 	document.getElementById('snd_msg').play();
// }

// function messages_loaded(data, user_id, timed_pull) {
// 	lp = -1;
// 	for (i = 0;i < data.length; i++) {
// 		j = parseInt(data[i].msg_id);
// 		if (j > lp) {
// 			lp = j;
// 		}
// 	}

// 	//If there are messages at all
// 	//There are more messages available or some messages' "seen" needs updating
// 	if (lp != -1 && (latest_pulled != lp || lp > latest_seen_by_u2)) {
// 		//If more messages arrived and it is a timed pull
// 		if (latest_pulled != lp && timed_pull) {
// 			start_flashing();
// 			play_message_sound();
// 		}
// 		for (i = 0; i < data.length; i++) {
// 			element = id("chat_msg" + user_id + "_" + data[i].msg_id);
// 			if (element) {
// 				element.parentNode.removeChild(element);
// 			}
// 		}
// 		latest_pulled = lp;
	
// 		conv_div = $("#conversation"+user_id);
// 		conversation = conv_div.html();
// 		conv_div.html(conversation + view(modifier_messages(data), view_chat_msg));

// 		conv_div.scrollTop(conv_div[0].scrollHeight);
// 	}
// }

// function load_messages(user_id, timed_pull) {
// 	$.ajax({
// 		url: "script/chat_msg/get.php",
// 		type: "POST",
// 		dataType: "json",
// 		data: {
// 			user_id: user_id,
// 			latest_pulled: latest_pulled,
// 			latest_seen_by_u2: latest_seen_by_u2
// 		}
// 	}).done(function (data) {
// 		//A timed pull results in a sound being played, otherwise no sound is played
// 		messages_loaded(data, user_id, timed_pull);
// 	});
// }


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

modifier_messages = function(data) {
	for (i in data) {
		message = data[i];
		if (message.user_id1 == user.user_id) {
			data[i].msg_seen_indication = message.msg_seen=="1"?"(seen)":"(unseen)";
		}
	}
	return data;
}

function alert_user() {
	document.getElementById('snd_msg').play();
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

	var messages_loaded = function (messages, user_id_d, timed_pull) {
		console.log("messages_loaded for " + user_id);

		pulled_so_far = latest_pulled;

		for (msg_id in messages) {
			message = messages[msg_id];
			if (msg_id > latest_pulled) {
				latest_pulled = msg_id;
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
	  			convo_div.parent().parent().prependTo(convo_div.parent().parent().parent());
	  		}
			console.log("Scrolling..."+user_id);
			convo_div.delay(timed_pull?0:100).animate({scrollTop: convo_div.prop("scrollHeight")}, 0);
			if(timed_pull) {
				alert_user();
			}
		}
	}

	var latest_pulled = -1;
	var latest_seen_by_u2 = -1;

	this.load_messages = function (timed_pull) {
		console.log("load_messages for " + user_id);
		var callback = this;
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
			// console.log("Got back this data: " );
			// console.log(data);
			//A timed pull results in a sound being played, otherwise no sound is played	
			if (typeof data[user_id] !== "undefined") {
				console.log("messages loaded...");
				messages_loaded(data[user_id].messages, user_id, timed_pull);
			}
		});
	}

	messages_loaded(this.messages, this.user_id, false);

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
				id("conversation_input" + user_id).innerHTML = "";

			} else {
				console.log(data);
			}
		});
		this.load_messages(false);
	}
}

function scroll_all() {
	for (key in conversations) {
		convo_div = $("#conversation" + key);
		convo_div.scrollTop(convo_div[0].scrollHeight);
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

	// if (!conversations_exist) {
		id("friend_selector").style.display = "block";
		ajax_request(id("friends"), true, view_friend_select, modifier_relay, "script/user/friends.php");
	// }
}

function timed_load() {
	for (key in conversations) {
		conversations[key].load_messages(true);
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
 * Add a conversation with someone to the conversations box.
 */
function add_conversation(user_id) {
	if (conversations[user_id])return;

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
		new_convo.load_messages(false);
		conversations[user_id] = new_convo;
	  	new_convo.convo_div.parent().parent().prependTo(new_convo.convo_div.parent().parent().parent());
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

// function reset_title() {
// 	document.title = original_title;
// }

// function flash_title() {
// 	if (!document.hidden){//id("conversation_input") == document.activeElement) {
// 		stop_flashing();
// 		return;
// 	}

// 	if (title_flashed) {
// 		document.title = "New message";
// 	} else {
// 		reset_title();
// 	}
// 	title_flashed = !title_flashed;
// }

// function stop_flashing() {
// 	clearInterval(flashing_interval);
// 	reset_title();
// }

// function start_flashing() {
// 	title_flashed = true;
// 	clearInterval(flashing_interval);
// 	flashing_interval = setInterval(flash_title,flashing_interval);
// 	flash_title();
// }
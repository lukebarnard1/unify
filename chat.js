
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

function queue_jump(follow) {
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

	this.latest_pulled = -1;
	this.latest_seen_by_u2 = -1;

	this.convo_div[0].user_id = user_id;
	this.convo_div[0].latest_pulled = this.latest_pulled;

	this.messages_loaded = function (messages, timed_pull, animate) {
		pulled_so_far = this.latest_pulled;

		for (msg_id in messages) {
			message = messages[msg_id];
			msg_int_id = parseInt(msg_id);
			if (msg_int_id > this.latest_pulled) {
				this.latest_pulled = msg_int_id;
			}
			if (message.user_id1 == user.user_id && message.msg_seen == "1") {
				this.latest_seen_by_u2 = parseInt(msg_id);
			}

			message = messages[msg_id];
			element = id("chat_msg" + msg_id);
			if (element) {
				element.parentNode.removeChild(element);
			}
		}

		convo_div[0].latest_pulled = this.latest_pulled;

		//Append the rendering of new messages
		convo_div.append(view_chat_msg.render( modifier_messages(messages) ));
  		
		if (pulled_so_far != this.latest_pulled) {
			if (global_latest_pulled < this.latest_pulled) {
				global_latest_pulled = this.latest_pulled;

				user_sent = (user_id == user.user_id);

				queue_jump(user_sent || current_user == user_id);
	  		}
			// convo_div.delay(timed_pull?0:100).animate({scrollTop: convo_div.prop("scrollHeight")}, 0);
			if(timed_pull) {
				msg = messages[global_latest_pulled];
				alert_user(msg.user_name, msg.msg_content);
			}
		}
	}

	this.load_messages = function (timed_pull, animate, callback) {
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
				if (callback) {
					callback();
				}
			}
		});
	}

	this.messages_loaded(this.messages, false, false);

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
			} else {
				console.log(data);
			}
		});
		this.load_messages(false, true, function () {
			sort_conversations();
			inp_div = id("conversation_input" + user_id);			
			$(inp_div).focus();
			inp_div.innerHTML = "";
		});
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
	conversations = data;

	conversations_exist = false;

	for (key in conversations) {
		conversations_exist = true;
		conversations[key] = new Conversation(conversations[key]);
	}

	id("friend_selector").style.display = "block";
	ajax_request(id("friends"), true, view_friend_select, modifier_relay, "script/user/friends.php");

	sort_conversations();
}

function sort_conversations() {
	//Sort the conversations

	compare = function (a, b) {
		p = a.childNodes[1].childNodes[3].latest_pulled;
		q = b.childNodes[1].childNodes[3].latest_pulled;

		return p < q?1:-1;
	}

	is_sorted = function (sorted) {
		for (var i = 0; i < sorted.length - 1; i++) {
			if (compare(sorted[i],sorted[i+1]) == 1 ) {
				return false;
			}
		}
		return true;
	}

	unsorted_cells = $(".conversation_cell").get();

	if (!is_sorted(unsorted_cells)) {
		sorted_cells = unsorted_cells.sort(compare);

		for (i in sorted_cells) {
			sorted_cells[i].parentNode.appendChild(sorted_cells[i]);
		}
	}
	scroll_all();
}

function multi_load(data) {
	for (user_id in data) {
		messages = data[user_id].messages;
		conversations[user_id].messages_loaded(messages, true, true);
	}
	sort_conversations();
}

function timed_load() {
	request_data = {};
	for (key in conversations) {
		c = conversations[key];
		request_data[key] = {};
		request_data[key].user_id = key;
		request_data[key].latest_pulled = c.latest_pulled;
		request_data[key].latest_seen_by_u2 = c.latest_seen_by_u2;
	}
	$.ajax({
		url: "script/chat_msg/get.php",
		type: "POST",
		dataType: "json",
		data: request_data
	}).done(function (data) {
		multi_load(data);
	});
}

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
		initial_load(data);
	});

	setInterval(timed_load,1000);
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



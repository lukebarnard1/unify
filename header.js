

view_nav_button_chat = new Template("view/nav_button_chat.html");
view_notification = new Template("view/notification.html");
view_group = new Template("view/group.html");

view_friend = new Template("view/friend.html");

modifier_friend_search = function (data, input_data) {
	query = input_data.q;
	for (var data_index in data) {
		friend = data[data_index];
		patt = new RegExp(query,"gi");
		friend.user_name = friend.user_name.replace(patt,"<span class='bold'>" + query + "</span>");
	}
	return data;
}

modifier_notifications = function (data, input_data) {
	n = data.length;
	for (var index in data) {
		notification = data[index];
		if(notification.notif_seen=="1") {
			notification.notif_class = "seen";
			n--;
		} else {
			notification.notif_class = "";
		}
		notification.notif_read_link = "<?php echo $SITE_URL;?>script/notification/see.php?notif_id=" + notification.notif_id;

		data[index] = notification;
	}
	notif_label = id("notif_label").innerHTML = "Notifications ("+n+")";
	return data;
}

function choose_friend(query, target_id, view) {
	target = id(target_id);
	if ($.trim(query) != "") {
		ajax_request(target, false, view, modifier_friend_search, "script/user/search.php", { q : query });
	} else {
		target.style.display = "none";
	}
}

function update_unread_messages() {
	ajax_request(id("nav_button_chat"), false, view_nav_button_chat, modifier_relay, "script/chat_msg/number_unseen.php");
}

function load_notifications() {
	ajax_request(notifications, false, view_notification, modifier_notifications, "script/notification/get.php",null);
}

function add_group(event) {
	group_name = event.target.innerText;
	event.preventDefault();

	window.location.replace("/script/user_group/add.php?group_name="+group_name);
}

function load_groups() {
	callback = function () {
		groups.innerHTML += "<div style=\"background-color:#fff;color:#000\" onkeydown=\"if(event.keyCode==13)add_group(event);\" onblur=\"this.innerHTML=(this.innerHTML==''?'New group':this.innerHTML)\" onclick=\"this.contentEditable='true';this.innerHTML=(this.innerHTML=='New group')?'':this.innerHTML;\">New group</div>";
	}

	ajax_request(groups, false, view_group, modifier_relay, "script/user_group/get.php",null,callback);
}

function load() {
	update_unread_messages();
	setInterval(update_unread_messages, 3000);
	load_notifications();
	setInterval(load_notifications, 3000);
	load_groups();
}

var nav_dropped = false;

function hide_dropdown(animate) {
	t = animate?500:0;
	$('#nav_dropdown').css({'height':'0px'});
	$('#main').animate({'padding-top':main_previous_padding + "px"},t);
	nav_dropped = false;
}

function resize() {
	if ($(document).width() > 700) {
		//Hide it if the screen is too big (without animating)
		if (parseInt($('#main').css('padding-top')) >= 190) {
			hide_dropdown(false);
		}
	}
}

window.addEventListener("resize",resize);
window.addEventListener("load",load);

var main_previous_padding = "0px";
function toggle_nav_dropdown() {
	var ndd = $('#nav_dropdown');

	if (!nav_dropped) {
		//Find out the true previous padding
		main_previous_padding = parseInt($("#main").css("padding-top"));

		//Assume #main exists and give it some added padding
		$("#main").animate({"padding-top":(215 + main_previous_padding) + "px"}, 500, function () {

			ndd.css({"display":"block","height":"0px"}).css({"height": "1000px"});

		});
		nav_dropped = true;
	} else {
		hide_dropdown(true);
	}
}

//Display a small message at the top of the page which will disappear automatically
//This is to improve feedback to the users
function display_quick_message(msg) {
	$("#quick_message").html(msg);
	$("#quick_message_spacer").css({height:"0px"}).animate({height:"28px"},200).delay(5000).animate({height:"0px"},200);
}

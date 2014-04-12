
//Templates for rendering
view_nav_button_chat = new Template("view/nav_button_chat.html");
view_notification = new Template("view/notification.html");
view_group = new Template("view/group.html");

view_friend = new Template("view/friend.html");

//Make the part of the query that matches the name, bold
modifier_friend_search = function (data, input_data) {
	query = input_data.q;
	for (var data_index in data) {
		friend = data[data_index];
		patt = new RegExp(query,"gi");
		friend.user_name = friend.user_name.replace(patt,"<span class='bold'>" + query + "</span>");
	}
	return data;
}

//Prepare notifications for rendering
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

//Search for a friend using a partial name, query
function choose_friend(query, target_id, view) {
	target = id(target_id);
	if ($.trim(query) != "") {
		ajax_request(target, false, view, modifier_friend_search, "script/user/search.php", { q : query });
	} else {
		target.style.display = "none";
	}
}

//Refresh the number of messages that the user has received but not read
function update_unread_messages() {
	ajax_request(id("nav_button_chat"), false, view_nav_button_chat, modifier_relay, "script/chat_msg/number_unseen.php");
}

//Refresh the user's notifications
function load_notifications() {
	ajax_request(notifications, false, view_notification, modifier_notifications, "script/notification/get.php",null);
}

//Add a group by redirecting
function add_group(event) {
	group_name = sanitise_input(event.target.innerText);
	event.preventDefault();

	window.location.replace("/script/user_group/add.php?group_name="+group_name);
}

//Load all of the user's groups and then add "New group" box on the end
function load_groups() {
	callback = function () {
		groups.innerHTML += ""+
			"<div "
				+ "style=\"background-color:#ddd;color:#333;border:1px solid #444\" "
				+ "onkeydown=\"if(event.keyCode==13)add_group(event);\" "
				+ "onblur=\"this.innerHTML=(this.innerHTML==''?'New group name':this.innerHTML)\" "
				+ "onclick=\"this.contentEditable='true';this.innerHTML=(this.innerHTML=='New group name')?'':this.innerHTML;\" "
				+ "title=\"Type the name of your new group here\">"
					+"New group name"
			+"</div>";
	}

	ajax_request(groups, false, view_group, modifier_relay, "script/user_group/get.php",null,callback);
}

//Called when the page loads
function load() {
	update_unread_messages();
	setInterval(update_unread_messages, 3000);
	load_notifications();
	setInterval(load_notifications, 3000);
	load_groups();
}

var nav_dropped = false;
//Hide the mobile dropdown navigation list
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
//Toggle the navigation drop down list
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
	$("#quick_message_spacer").stop().animate({height:"28px"},200).delay(2000).animate({height:"5px"},200);
}

//Delete a notification using ajax then refresh the notifications if it was a success
function delete_notification(notif_id) {
	ajax_push(
		"script/notification/delete.php",
		{notif_id: notif_id},
		function(data){
			if (data.code == "0") {
				load_notifications();
			}
		}
	);
}
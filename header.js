

view_nav_button_chat = new Template("view/nav_button_chat.html");
view_notification = new Template("view/notification.html");

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
	notif_label = id("notif_label").innerHTML = "notifications ("+n+")";
	return data;
}


function update_unread_messages() {
	ajax_request(id("nav_button_chat"), false, view_nav_button_chat, modifier_relay, "script/chat_msg/number_unseen.php");
}

function load_notifications_callback() {
	notifications = id("notifications");
}

function load_notifications() {
	ajax_request(notifications, false, view_notification, modifier_notifications, "script/notification/get.php",null,load_notifications_callback);
}

function load() {
	update_unread_messages();
	setInterval(update_unread_messages, 3000);
	load_notifications();
	setInterval(load_notifications, 3000);
}

window.addEventListener("load",load);
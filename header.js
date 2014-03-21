

view_nav_button_chat = new Template("view/nav_button_chat.html");

function update_unread_messages() {
	ajax_request(id("nav_button_chat"), false, view_nav_button_chat, modifier_relay, "script/chat_msg/number_unseen.php");
}

function load() {
	update_unread_messages();
	setInterval(update_unread_messages, 3000);
}

window.addEventListener("load",load);
get = <?php echo json_encode($_GET)?>;
user = <?php echo json_encode($user)?>;
group = <?php if(isset($selected_group)){echo json_encode($selected_group);}else{echo "null";}?>;

view_comment = new Template("view/comment.html");
view_post = new Template("view/post.html");
view_visible_post = new Template("view/post.html");
view_hidden_post = new Template("view/hidden_post.html");

view_friend = new Template("view/friend.html");
view_group_member = new Template("view/member_in_group.html");
view_member = new Template("view/member.html");

view_post.render = function (data) {
	result = "";
	for (var i in data) {
		if (data[i].post_is_hidden) {
			result += view(new Array(data[i]), view_hidden_post);
		} else {
			result += view(new Array(data[i]), view_visible_post);
		}
	}
	return result;
}

modifier_friend_search = function (data, input_data) {
	query = input_data.q;
	for (var data_index in data) {
		friend = data[data_index];
		patt = new RegExp(query,"gi");
		friend.user_name = friend.user_name.replace(patt,"<span class='bold'>" + query + "</span>");
	}
	return data;
}

//Current page in terms of posts avaiable to the user
page = 0;
//The last page loaded
last_page = -1;
//Can more posts be loaded or are we still waiting for posts?
can_load_more_posts = true;

modifier_post = function (post, input_data) {
	for (var comment_index in post.comments) {
		comment = post.comments[comment_index];
		post.comments[comment_index].display_delete = (comment.can_delete==1)?"inline":"none";
	}

	post.post_is_hidden = (post.post_is_hidden == 1);

	post.post_comments = view(post.comments, view_comment);
	t = post.post_time.split(/[: -]/);
	post.post_time = (new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5])).toLocaleString();

	post.upvote_img = ((post.can_vote==0)? "disable_" : "") + "upvote.png";
	post.downvote_img = ((post.can_vote==0)? "disable_" : "") + "downvote.png";

	can_delete = (user.user_id == post.user_id);
	post.hider_title = can_delete? "Delete post" : "Hide post";
	post.hider_image = can_delete? "cross.png" : "hide.png";
	post.post_rating = post.post_rating_up - post.post_rating_dn;
	return post;
}

modifier_posts = function (data, input_data) {
	for (var post_index in data) {
		data[post_index] = modifier_post(data[post_index], input_data);
	}
	last_page = page;
	if( data.length != 0) {
		can_load_more_posts = true;
	}
	return data;
}


function print_json(data) {
	console.log(JSON.stringify(data,null,2));
}

function get_args() {
	arguments = arguments.callee.caller.arguments;
	args = [];
	for ( i =0; i < arguments.length; i++) {
		args.push(arguments[i]);
	}
	s = {name : arguments.callee.name, arguments : args};
	return s;
}

function choose_friend(query, target_id, view) {
	target = id(target_id);
	if ($.trim(query) != "") {
		ajax_request(target, false, view, modifier_friend_search, "script/user/search.php", { q : query });
	} else {
		target.style.display = "none";
	}
}

function choose_member(query, target_id, view) {
	target = id(target_id);
	if ($.trim(query) != "") {
		ajax_request(target, false, view, modifier_friend_search, "script/user/search.php", { q : query, group_id: group.group_id });
	} else {
		target.style.display = "none";
	}
}

function load_friends() {
	ajax_request(id("friends"), false, view_friend, modifier_relay, "script/user/friends.php");
}

function load_members() {
	ajax_request(id("group_members"), false, view_group_member, modifier_relay, "script/grouping/members.php", {group_id : group.group_id});
}


function next_page() {
	if (get.post_id) {
		if (!id("posts0")){
			posts = id("posts");
			page_div = document.createElement("div");
			page_div.id = "posts0";
			posts.appendChild(page_div);
		}
		reload_page(0);
	}else{
		posts = id("posts");
		page_div = document.createElement("div");
		page_div.id = "posts"+page;

		posts.appendChild(page_div);
		reload_page(page);

		page++;
	}
}

function reload_page(p) {
	console.log("Reload page "+ p);
	page_div = id("posts"+p);
	get_send = {};
	if (get.post_id) {
		get_send.post_id = get.post_id;
	} else {
		get_send = {page_from : p, page_to : p + 1};
	}

	ajax_request(page_div, false, view_post, modifier_posts, "script/post/get.php", get_send);
}

function reload_feed() {
	console.log("Reload feed to "+page);

	for (i = 0; i <= last_page; i++) {
		reload_page(i);
	}
}

function more_posts() {
	if (can_load_more_posts && page != -1) {
		can_load_more_posts = false;
		next_page();
	}
}

function update_posts() {
	col2 = $("#column2");
    if (col2.scrollTop() + col2.innerHeight() >= col2[0].scrollHeight - 2000) {
        more_posts();
    }
    return true;
}

function load() {
	load_friends();
	if (group != null) {
		load_members();
	}

	more_posts();
	id("column2").onscroll = update_posts;
}

window.addEventListener("load",load);

function change_feed(link, p) {
	ajax_push(link,{},
		function(data) {
			reload_page(p);
		}
	);
}

function show_pp_button(show) {
	if (show) {
		id("prof_pic_button").style.display = "block";
	} else {
		id("prof_pic_button").style.display = "none";
	}
}

function change_prof_pic() {
	id("prof_pic_file").click();
}

function sub_prof_pic() {
	id('prof_pic_form').submit();
}

function add_post(e) {
	e.preventDefault();
	content_input = $("#post_content");

	content = content_input.text();
	content = sanitise_input(content);

	if (content!="" && content != "Write something...") {
		in_data = {
			post_content : content,
			group_id : $("#group_id").val()
		};

		ajax_push("script/post/add.php", in_data, reload_feed);

		$("#post_content").html("").blur();
	}
}

function add_comment(e, post_id, page) {
	e.preventDefault();

	content_input = $("#comment_input"+post_id);

	content = content_input.text();
	content = sanitise_input(content);

	if (content != "") {
		fd = {comment_content:content,post_id:post_id};
		ajax_push("script/comment/add.php",fd,function(){
			reload_page(page);
		});
	}
}

function add_member(member_id) {
	//Send a member a request to join this group by email
	ajax_push("script/grouping/request.php",{group_id:group.group_id,user_id:member_id},function(data){alert(data.message)});
}

function comment_enable(e, post_id) {
	e.preventDefault();
	comment_form = id("comment_form" + post_id);
	comment_form.style.display = "block";

	input = id("comment_input" + post_id);
	input.contentEditable = "true";
	input.focus();
}

function send_feedback(e) {
	e.preventDefault();
	feedback_content = $("#feedback_content");
	content = feedback_content.val();

	content = content.replace(/\r?\n/g, '<br>');

	if (content != "") {
		fd = {feedback_content: content};

		ajax_push("script/feedback/send.php", fd,
			function(){
				id("feedback_submit").value = "Feedback Sent!";
				$("#feedback_content").val("").blur();
			}
		);
	}
}

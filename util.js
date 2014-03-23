
function id(el_id) {
	return document.getElementById(el_id);
}

function sanitise_input(input) {
	input = input.replace(/<div>(.*)<\/div>/g, '$1<br>');
	input = input.replace(/\&(.*)\;/g, '');
	return input;
}

function ajax_request(target, append, view_template, modifier, url, in_data, callback) {
	var input_data = in_data;
	$.ajax({
		url: url,
		type: "POST",
		dataType: "json",
		data: in_data
	}).done(function(data) {
		if (data != null) {
			if (append) {
				target.innerHTML += view( modifier(data,input_data) , view_template );
			} else {
				target.innerHTML = view( modifier(data,input_data) , view_template );
			}
			target.style.display = "block";
		} else {
			target.style.display = "none";
		}
		if (typeof callback !== "undefined") {
			callback();
		}
	})
}

function ajax_push(url, in_data, callback) {
	a = {
		url: url,
		type: "POST",
		dataType: "json",
		data: in_data
	};
	if (in_data instanceof FormData) {
		a.contentType = false;
		a.processData = false;
	}
	$.ajax(a).done(
		function(data) {
			if (data.code == 0) {
				callback();
			}else{
				console.log("ajax_push returned failure: "+data.message);
			}
		}
	);
}

/**
 * Load and execute a script with the file name as 
 * the only argument.
 */
function exec_script(script_name) {
	$.getScript(script_name, function(){
		alert(script_name + " loaded and executed.");
	});
}
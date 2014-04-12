// Return the element with the id specified
function id(el_id) {
	return document.getElementById(el_id);
}

//Modify the input so that only the correct text is returned
// - This was required with editable divs
function sanitise_input(input) {
	input = input.replace(/<div>(.*)<\/div>/g, '$1<br>');
	input = input.replace(/\&(.*)\;/g, '');
	return input;
}

//Make an ajax request that returns JSON formatted data for rendering
// - target: The element to append/replace templated data
// - append: true/false whether the html rendered should be appended or written over
//	 the existing innerHTML.
// - view_template: The Template instance to use to render the HTML from the JSON
// - modifier: The function to pass the data through prior to rendering
// - url: The url to retrieve the JSON data from
// - in_data: The POST parameters to send to the URL on request
// - callback: The function to call when the request is finished and the data is displayed
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

//Make an ajax request that will only return a status (see status.php) when complete.
// - url: The url to make the request to
// - in_data: The POST variables to send to the url
// - callback: The function to call with the status as an argument when the request is
// - completed.
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
			display_quick_message(data.message);
			callback(data);
		}
	);
}
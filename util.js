
function id(el_id) {
	return document.getElementById(el_id);
}

function sanitise_input(input) {
	input = input.replace(/<div>(.*)<\/div>/g, '$1<br>');
	input = input.replace(/\&(.*)\;/g, '');
	return input;
}
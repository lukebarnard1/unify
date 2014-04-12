//Template for rendering json objects in a html format
function Template (file_name) {
	var file_name = file_name;
	var parts = new Array();
	var variables = new Array();

	this.load_template = function(data) {
		patt = /\{([a-z_]*)\}/g;
		last_match_end = 0;
		while (match = patt.exec(data)) {
			start = match.index;
			parts.push(data.substring(last_match_end , start));//Push the non-variable part
			parts.push("{}");//Push marker for the variable
			last_match_end = start + match[0].length;
			
			variables.push(data.substring(start + 1, last_match_end - 1));//Push the variable part
		}
		parts.push(data.substring(last_match_end));
		console.log("Loaded template '"+file_name+"'");
	}

	this.render = function (data) {
		result = "";
		for (var data_index in data) {
			row = data[data_index];
			var_index = 0;
			for (var part_index in parts) {

				part = parts[part_index];
				if (part == "{}") {
					variable = variables[var_index];

					if (row.hasOwnProperty(variable)) {
						result += row[variable];
					}
					var_index++;
				} else {
					result += part;
				}
			}
		}
		return result;
	}

	$.ajax({
		url: file_name,
		type: "GET",
		async: false,
		success: this.load_template
	});
}

function view(data, template) {
	return template.render(data);
}
modifier_relay = function (data, content_input_data) {return data;}

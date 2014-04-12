<?php
//A class to represent a small status code and message to be displayed to the user
// when they carry out an action.
class Status {
	private function __construct($code, $message) {
		$this->code = $code;
		$this->message = $message;
	}

	static function create($code, $message) {
		return new Status($code, $message);
	}

	static function json($code, $message) {
		$status = new Status($code, $message);
		return $status->format_json();
	}

	function format_json() {
		return json_encode($this);
	}
}
?>
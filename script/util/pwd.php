<?php
	//Salt a password
	function salt($p) {
		$rev = strrev($p);
		$len = strlen($p);
		return md5(substr($rev,0,$len/2) . $len*924 . $p . $len*184 . substr($rev,$len/2,$len));
	}

?>
<?php
	include "../util/session.php";
	include "../util/mysql.php";
	include "../util/redirect.php";

	ini_set("memory_limit","10000M");

	function img_create($type, $name) {
	    if ($type == "gif") {
	    	$im = imageCreateFromGif($name); 
	    } elseif ($type == "jpeg" || $type == "jpg") {
			$im = imagecreatefromjpeg($name);
		} elseif ($type == "png") {
			$im = imageCreateFromPng($name); 
		} elseif ($type == "bmp") {
	        $im = imageCreateFromBmp($name);
	    } else {
	    	return false;
	    }
	    return $im;  
	} 
	$valid_types = array("png","jpg","jpeg","gif","bmp");
	$nm = strtolower($_FILES["file"]["name"]);
	$type = substr($nm, strrpos($nm, ".") + 1);
	if (in_array($type, $valid_types)) {
		$tmp_name = $_FILES["file"]["tmp_name"];
		$im = img_create($type, $tmp_name);
		if ($im) {
			$w = imagesx($im);
			$h = imagesy($im);

			$s = ($w>$h) ? $h : $w;//Smallest out of height and width
			
			$im2 = imagecreatetruecolor(200,200);
			imagecopyresampled($im2, $im, 0, 0, 0, 0,200,200,$s,$s);

			$dao = new DAO(false);

			//Delete the previous profile picture
			$f_name = "../profile_pictures/".$user->user_picture;
			if (file_exists($f_name)) {
				unlink($f_name);
			}

			$user->user_picture = $user->user_id."-".date("U");

			$r = imagejpeg($im2, "../profile_pictures/".$user->user_picture, 100);

			$im3 = imagecreatetruecolor(1,1);
			imagecopyresampled($im3, $im2, 0, 0, 0, 0,1,1,200,200);
			$rgb = imagecolorat($im3, 0, 0);

			$colors = imagecolorsforindex($im3, $rgb);

			imagedestroy($im);
			imagedestroy($im2);
			imagedestroy($im3);

			$dao->myquery("UPDATE user SET user_picture=\"$user->user_picture\" WHERE user_id=\"$user->user_id\";");
		} else {
			echo "Could not create image";
		}
	} else {
		echo "Bad image type!";
	}
	header("Connection: close");
	redirect($_POST["r"]."?reload");
?>
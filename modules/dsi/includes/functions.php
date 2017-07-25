<?php
/*
 * Dynamic Server Image module for Open Game Panel
 * Copyright (C) 2012 SpiffyTek
 *
 * http://spiffytek.com/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published by
 * the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
 
/* Workaround for OGP function not included due to "echo". Needed by protocols *Monitor.php */
function print_player_list(){
	return false;
}

/* DSi functions */
function dsi_cleaninput($input){
	/* Some rules might be paranoid */
	$remove = array("#\\\\+#", "#/+#", "#\\+#", "#\s+#", "#http+#", "#ftp+#", "#%00+#", "#\\0+#", "#\\x00+#", "#\(+#", "#\)+#", "#\{+#", "#\}+#");
	
	$input = preg_replace($remove, "", $input); 
	$input = htmlspecialchars($input, ENT_QUOTES);
	
	return $input;
}

function dsi_error_img($msg0 = false, $msg1 = false, $type = false){
	if(empty($type)) { $type = "normal"; }
	$bgimg = DSI_BASEPATH."images/default_".$type.".png";
	
	$im = imagecreatefrompng($bgimg);
	$text_color = ImageColorAllocate($im,255,0,0);
	
	switch($type){
		case "normal":
			imagestring($im,6,2,5,"ERROR! ".$msg0,$text_color);
			imagestring($im,5,2,20,$msg1,$text_color);
		break;
		case "small":
			imagestring($im,6,2,0,"ERROR! ".$msg0,$text_color);
			imagestring($im,5,2,10,$msg1,$text_color);
		break;
		case "sky":
			$img_map = DSI_BASEPATH."images/offline_bg.png";
			$im_map_info = getimagesize($img_map);
			$im_map = imagecreatefrompng($img_map);
			$im_map_width  = 130;
			$im_map_height = 120;
			$im_map_posx   = 25;
			$im_map_posy   = 112;
		
			imagecopyresampled($im, $im_map, $im_map_posx, $im_map_posy, 0, 0, $im_map_width, $im_map_height, $im_map_info[0], $im_map_info[1]);
			imagestring($im,1,6,28,"ERROR! ".$msg0,$text_color);
			imagestring($im,1,6,45,$msg1,$text_color);
		break;
	}	
	dsi_make_img($im);
}

function dsi_make_img($im = false, $cache_on = false, $cache_data = false){
	header("Content-type: image/png");
	if($cache_on){
		$expire = gmdate("D, d M Y H:i:s", $cache_data["cache_expire"])." GMT";
		header("Expires: ".$expire);
		readfile($cache_data["file"]);
	}
	else
	{ 
		imagepng($im, $cache_data["file"], 9);
		readfile($cache_data["file"]);
	}
	
	imagedestroy($im);
	exit;
}

function dsi_get_bg($query_name, $mod, $type){
	$img = DSI_BASEPATH."images/".$query_name."/".$mod."_".$type.".png";
	if(file_exists($img)){ 
		return $img; 
	}
	else{ return DSI_BASEPATH."images/default_".$type.".png"; }
}

function pretty_text($im, $fontsize, $x, $y, $string, $color, $outline = false) {
	$black  = imagecolorallocate($im, 0, 0, 0);

	// Black outline
	if($outline){
		imagestring($im, $fontsize, $x - 1, $y - 1, $string, $black);
		imagestring($im, $fontsize, $x - 1, $y, $string, $black);
		imagestring($im, $fontsize, $x - 1, $y + 1, $string, $black);
		imagestring($im, $fontsize, $x, $y - 1, $string, $black);
		imagestring($im, $fontsize, $x, $y + 1, $string, $black);
		imagestring($im, $fontsize, $x + 1, $y - 1, $string, $black);
		imagestring($im, $fontsize, $x + 1, $y, $string, $black);
		imagestring($im, $fontsize, $x + 1, $y + 1, $string, $black);
	}

	// Your text
	imagestring($im, $fontsize, $x, $y, $string, $color);
	return $im;
}

function pretty_text_ttf($im, $fontsize, $angle, $x, $y, $color, $font, $string, $outline = false) {
	$black  = imagecolorallocate($im, 0, 0, 0);

	// Black outline
	if($outline){
		imagettftext($im, $fontsize, $angle, $x - 1, $y - 1, $black, $font, $string);
		imagettftext($im, $fontsize, $angle, $x - 1, $y, $black, $font, $string);
		imagettftext($im, $fontsize, $angle, $x - 1, $y + 1, $black, $font, $string);
		imagettftext($im, $fontsize, $angle, $x, $y - 1, $black, $font, $string);
		imagettftext($im, $fontsize, $angle, $x, $y + 1, $black, $font, $string);
		imagettftext($im, $fontsize, $angle, $x + 1, $y - 1, $black, $font, $string);
		imagettftext($im, $fontsize, $angle, $x + 1, $y, $black, $font, $string);
		imagettftext($im, $fontsize, $angle, $x + 1, $y + 1, $black, $font, $string);
	}

	// Your text
	imagettftext($im, $fontsize, $angle, $x, $y, $color, $font, $string);
	return $im;
}
?>

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


define("DSI_BASEPATH", "modules/dsi/");
require(DSI_BASEPATH . 'includes/functions_ui.php');
require(DSI_BASEPATH . 'includes/functions.php');
require("modules/gamemanager/home_handling_functions.php");
require('modules/config_games/server_config_parser.php'); 

function exec_ogp_module(){
	global $db,$view,$settings;
	
	if(isset($_GET["s"])){
		$s = dsi_cleaninput($_GET["s"]);
		//192.168.0.160_27015-sky.png
		if (strpos($s, ".png")){ list($s, $extension) = explode(".png", $s, 2); }
		//192.168.0.160_27015-sky
		if (strpos($s, "-")){ list($s, $img_type) = explode("-", $s, 2); }
		//192.168.0.160_27015
		if (strpos($s, "_")){ $s = explode("_", $s, 2); }
		if(empty($s) || empty($s[0]) || empty($s[1])){ unset($s); }
	}
		
	$valid_types = array("normal", "small", "sky");
	if (!isset($img_type) || !in_array($img_type, $valid_types)){
		$img_type = "normal";
	}
	
	if(!$s){ dsi_error_img(false, "No or incomplete server address given!", $img_type); }
	/* Cache handler */
	$cache = array();
	$cache["life"] = 60;
	$cache["file"] = DSI_BASEPATH."cache/".$s[0]."_".$s[1]."-".$img_type;
	if( file_exists( $cache["file"] ) )
	{
		$cache["cache_expire"] = filemtime( $cache["file"] ) + $cache["life"];
		if( time() >= $cache["cache_expire"])
			$do_new = true;
		else
			$do_new = false;
	}
	else
		$do_new = true;
			
	if($do_new){		
		$server_home = $db->getGameHomeByIP($s[0], $s[1]);
		if(!$server_home){ dsi_error_img("IP: ".$s[0].":".$s[1], "Server does not exist in DB.", $img_type); }
		
		$cfghome = $db->getCfgHomeById($server_home['home_cfg_id']);
		$server_xml = read_server_config(SERVER_CONFIG_LOCATION."/".$cfghome['home_cfg_file']);
		
		$name = "";
		$players = "";
		$playersmax = "";
		$map = "";
		
		if ($server_xml->protocol == "gameq"){
			require_once('protocol/GameQ/functions.php');
			require_once('protocol/GameQ/GameQMonitor.php');
			$query_name = $server_xml->gameq_query_name;
		}
		else if ($server_xml->protocol == "lgsl"){
			require_once('protocol/lgsl/LGSLMonitor.php');
			$query_name = $server_xml->lgsl_query_name;
		}
		else if ($server_xml->protocol == "teamspeak3"){
			ini_set('date.timezone', $settings['time_zone']);
			require_once('protocol/TeamSpeak3/TS3Monitor.php');
			$query_name = 'ts3';
			$mod = 'ts3';
		}
		else {
			$query_name = preg_replace("/[^a-z0-9_]/", "-", strtolower($server_xml->mods->mod['key']));
		}
		
		
		if(!isset($mod)){
			$mod = preg_replace("/[^a-z0-9_]/", "-", strtolower($server_xml->mods->mod['key']));
		}
		
		if (file_exists(DSI_BASEPATH."geoip.inc.php")){
			$geoip = false;
			require_once DSI_BASEPATH."geoip.inc.php";
			$gi = geoip_open( DSI_BASEPATH."GeoIP.dat", GEOIP_STANDARD );
			$clookup = geoip_country_code_by_addr($gi, $s[0]);
			if (empty($clookup)){ $clookup = geoip_country_code_by_name($gi, $s[0]); }
			$clookup = strtolower($clookup);
			$cimg = "images/countries/{$clookup}.png";
			if (!file_exists($cimg)) { $cimg = "images/countries/noflag.png"; }
			$cimage_info = getimagesize($cimg);
			$cimage = imagecreatefrompng($cimg);
		}
		
		$icon_paths = array("images/icons/$mod.png",
							"images/icons/$query_name.png",								
							"images/countries/noflag.png"); 
		$icoimg = get_first_existing_file($icon_paths);
		$icoimage_info = getimagesize($icoimg);
		$icoimage = imagecreatefrompng($icoimg);

		if($status == "half"){
			$status = "offline";
			$ip = $s[0];
			$port = $s[1];
		}
		
		/* Start image */
		$path = dsi_get_bg($query_name, $mod, $img_type);
		$im = imagecreatefrompng($path);
		
		/* Text formatting */
		$text_font0 = DSI_BASEPATH."fonts/Cyberbas.ttf";
		$size0 = 10; /* Normal */
		$size2 = 9; /* Small */
		$size4 = 10; /* Sky */

		$text_font1 = DSI_BASEPATH."fonts/Sansation_Regular.ttf";
		$size1 = 10; /* Normal */
		$size3 = 10; /* Small */
		$size5 = 9; /* Sky */
		
		$name_type_vertical = false; /* Display the hostname vertically on sky image */
				
		if (file_exists(DSI_BASEPATH."images/color_settings.php")){
			require_once(DSI_BASEPATH."images/color_settings.php");
		}

		if($status == "offline")
		{
			$text_color0 = ImageColorAllocate($im,125,125,125);
			$text_color1 = ImageColorAllocate($im,125,125,125);
		}
		else
		{
			$text_color0 = ImageColorAllocate($im,0,255,0);
			$text_color1 = ImageColorAllocate($im,0,255,0);
		}
		if ( ! isset( $txt_outline ) ){
			switch($img_type){
				case "normal":
					$txt_outline = true;
				break;
				case "small":
					$txt_outline = true;
				break;
				case "sky":
					$txt_outline = true;
				break;
			}
		}
		$ip = checkDisplayPublicIP($server_home['display_public_ip'],$server_home['ip'] != $server_home['agent_ip'] ? $server_home['ip'] : $server_home['agent_ip']);
		/* Render types */
		switch($img_type){
			case "normal":
				if($geoip){ imagecopyresampled($im, $cimage, 205, 35, 0, 0, 16, 11, $cimage_info[0], $cimage_info[1]); } // Country
				imagecopyresampled($im, $icoimage, 5, 5, 0, 0, 16, 16, $icoimage_info[0], $icoimage_info[1]); // Gameicon
				pretty_text_ttf($im,$size0,0,25,18,$text_color0,$text_font0,substr($name,0,47), $txt_outline); // Servername
				pretty_text_ttf($im,$size1,0,65,45,$text_color0,$text_font1,$ip.":".$port, $txt_outline); // IP:PORT
				pretty_text_ttf($im,$size1,0,65,63,$text_color0,$text_font1,$map, $txt_outline); // Map
				pretty_text_ttf($im,$size1,0,292,45,$text_color0,$text_font1,$players."/".$playersmax, $txt_outline); // Players
				pretty_text_ttf($im,$size1,0,293,63,$text_color0,$text_font1,$status, $txt_outline); // Status
			break;
			
			case "small":
				if($geoip){ imagecopyresampled($im, $cimage, 315, 1, 0, 0, 16, 11, $cimage_info[0], $cimage_info[1]); } // Country
				imagecopyresampled($im, $icoimage, 0, 0, 0, 0, 16, 16, $icoimage_info[0], $icoimage_info[1]); // Gameicon
				pretty_text_ttf($im,$size2,0,18,10,$text_color0,$text_font0,substr($name,0,45), $txt_outline); // Servername
				pretty_text_ttf($im,$size3,0,2,24,$text_color0,$text_font1,$ip.":".$port, $txt_outline); // IP:Port
				pretty_text_ttf($im,$size3,0,135,24,$text_color0,$text_font1,$map, $txt_outline); // Map
				pretty_text_ttf($im,$size3,0,240,24,$text_color0,$text_font1,$players."/".$playersmax, $txt_outline); // Players
				pretty_text_ttf($im,$size3,0,295,24,$text_color0,$text_font1,$status, $txt_outline); // Status
			break;
			
			case "sky":
				if($status != "offline"){ 
					$mapFile = get_map_path($query_name, $mod, $map);
					if(cURLEnabled()){
						$img_map = curlCacheImage(DSI_BASEPATH, $mapFile); 
					}else{
						stream_context_set_default(
							array(
								'http' => array(
									'method' => 'GET',
									'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:51.0) Gecko/20100101 Firefox/51.0',
									'header'=>'Referer: http://gametracker.com'
								)
							)
						);
						$img_map = $mapFile;
					}
				}
				else { $img_map = DSI_BASEPATH."images/offline_bg.png"; }
				$im_map_info = getimagesize($img_map);
				if ($im_map_info[2] == 1) { $im_map = imagecreatefromgif($img_map);  }
				if ($im_map_info[2] == 2) { $im_map = imagecreatefromjpeg($img_map); }
				if ($im_map_info[2] == 3) { $im_map = imagecreatefrompng($img_map);  }
			
				$im_map_width  = 130;
				$im_map_height = 120;
				$im_map_posx   = 25;
				$im_map_posy   = 120;

				$im_icon_width  = 16;
				$im_icon_height = 16;
				$im_icon_posx   = 26;
				$im_icon_posy   = 113;

				imagecopyresampled($im, $im_map, $im_map_posx, $im_map_posy, 0, 0, $im_map_width, $im_map_height, $im_map_info[0], $im_map_info[1]); // Mapimage
				imagecopyresampled($im, $icoimage, $im_icon_posx, $im_icon_posy, 0, 0, $im_icon_width, $im_icon_height, $icoimage_info[0], $icoimage_info[1]); // Gameicon
				if($geoip){ imagecopyresampled($im, $cimage, $im_icon_posx + 112, $im_icon_posy, 0, 0, 16, 11, $cimage_info[0], $cimage_info[1]); } // Country
				if($name_type_vertical){ pretty_text_ttf($im,$size4,270,5,20,$text_color1,$text_font0,substr($name,0,28), $txt_outline); } // Servername Vertical
				else{ pretty_text_ttf($im,$size4,0,5,15,$text_color1,$text_font0,substr($name,0,23), $txt_outline); } // Servername
				pretty_text_ttf($im,$size5,0,20,30,$text_color1,$text_font1,"IP:Port:  ".$ip.":".$port, $txt_outline); // IP:Port
				pretty_text_ttf($im,$size5,0,20,52,$text_color1,$text_font1,"Map    :  ".substr($map,0,19), $txt_outline); // Map
				pretty_text_ttf($im,$size5,0,20,74,$text_color1,$text_font1,"Players:  ".$players."/".$playersmax, $txt_outline); // Players
				pretty_text_ttf($im,$size5,0,20,96,$text_color1,$text_font1,"Status :  ".substr($status,0,19), $txt_outline); // Status
			break;
		}
		dsi_make_img($im, false, $cache);
	}
	else{
		dsi_make_img(false, true, $cache, true);
	}
}
?>

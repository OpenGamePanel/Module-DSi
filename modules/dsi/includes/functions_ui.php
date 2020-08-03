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

/* DSi functions */
function dsi_render_table($ip, $port, $url = false, $use_table = TRUE, $use_rows = TRUE, $show_codes = TRUE, $img_join_link = FALSE, $img_only = FALSE, $img_type = FALSE ){
	$link = false;
	$s = ( isset($_SERVER['HTTPS']) and get_true_boolean($_SERVER['HTTPS']) ) ? "s" : "";
	$base_url = "http$s://".implode('/', (explode('/', $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], -1)));
	if($url){
		$link["bb_close"] = "[/url]";
		$link["bb_type_a"] = "[url=".$url."]";
		$link["bb_type_b"] = "[url=\"".$url."\"]";	
		$link["href"] = "<a href=\"".$url."\">";
		$link["href_close"] = "</a>";
	}
	
	$types = array("normal", "small", "sky");
	if($img_type)
		$types = array($img_type);
	
	$output = "";
	if($use_table) $output .= "\n<table class='center' >\n";
	$image_td_align = $img_only ? "center" : "left";
	foreach($types as $type)
	{
		if($use_rows)	$output .=	"\t<tr>\n";
		if(!$img_only)	$output .=	"\t\t<td align='right' width=30px >\n".
									"\t\t\t<b>Banner $type</b>\n".
									"\t\t</td>\n";
		$output .=	"\t\t<td align='$image_td_align' >\n";
		if($img_join_link) $output .= "\t\t\t{$link['href']}\n";
		$output .=	"\t\t\t<img src='" . DSI_BASEPATH . "s-${ip}_${port}-${type}.png'/>\n";
		if($img_join_link) $output .= "\t\t\t{$link['href_close']}\n";
		$output .=	"\t\t</td>\n";
		if($show_codes)
			$output .=	"\t</tr>\n".
						"\t<tr>\n".
						"\t\t<td align='right' width=30px >\n".
						"\t\t\t<b>Codes</b>".
						"\t\t</td>\n".
						"\t\t<td align='left' ><input type='text' readonly='readonly' style='width:100%;' onclick='select()' value='".$link["bb_type_a"]."[img]$base_url/" . DSI_BASEPATH . "s-${ip}_${port}-${type}.png[/img]".$link["bb_close"]."' /><br />\n".
						"\t\t\t<input type='text' readonly='readonly' style='width:100%;' onclick='select()' value='".$link["bb_type_b"]."[img]$base_url/" . DSI_BASEPATH . "s-${ip}_${port}-${type}.png[/img]".$link["bb_close"]."' /><br />\n".
						"\t\t\t<input type='text' readonly='readonly' style='width:100%;' onclick='select()' value='".$link["href"]."<img src=\"$base_url/" . DSI_BASEPATH . "s-${ip}_${port}-${type}.png\">".$link["href_close"]."' />\n".
						"\t\t</td>\n";
		if($use_rows) $output .= "\t</tr>\n";
	}
		  
	if($use_table) $output .= "</table>\n";
	
	return $output;
}
?>

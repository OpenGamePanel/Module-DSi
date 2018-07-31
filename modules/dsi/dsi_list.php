<?php
/*
 *
 * OGP - Open Game Panel
 * Copyright (C) 2008 - 2018 The OGP Development Team
 *
 * http://www.opengamepanel.org/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 */

define("DSI_BASEPATH", "modules/dsi/");
require_once(DSI_BASEPATH . 'includes/functions_ui.php');
require_once('modules/gamemanager/home_handling_functions.php');
require_once("modules/config_games/server_config_parser.php");
require_once('includes/lib_remote.php');
require_once('protocol/lgsl/lgsl_protocol.php');
require_once('protocol/GameQ/GameQ.php');
function exec_ogp_module(){
	global $db;
	$online = isset( $_POST['online'] ) ? TRUE : FALSE;
	$server_homes = $db->getIpPorts();
	echo "<h2>".get_lang("dsi_list")."</h2>\n";
	if ( isset($_GET['home_id-mod_id-ip-port']) AND $_GET['home_id-mod_id-ip-port'] == "" )
		unset( $_GET['home_id-mod_id-ip-port'] );
	
	echo get_lang("dsi_select_server");
	if (!isset($_GET['home_id-mod_id-ip-port']) and !$online) 
	{	
		create_home_selector_address($_GET['m'], $_GET['p'], $server_homes);
		$show_all = TRUE;
	}
	else
	{
		create_home_selector_address($_GET['m'], $_GET['p'], $server_homes);
		create_home_selector($_GET['m'], $_GET['p'], "show_all");
		$show_all = FALSE;
	}

	$qty = count($server_homes);
	
	$cols = 1;
	
	if ( ( $qty >= 4 && $qty < 17 ) OR isset($_GET['home_id-mod_id-ip-port']) ) 
	{
		$cols = 4;
		$type = "sky";
	}
	else
	{
		$type = "normal";
	}
	if ( $qty >= 17 ) $cols = 2;
	$counter = 1;
	
	echo $show_all ?	"\n<form method='POST' >\n".
						"\t<button name='online' onClick='this.form.submit()' >".
						get_lang("online") .
						"</button>\n".
						"</form>\n".
						"<br>\n" : "";
	$servers = 0;
	$servers_running = 0;
	foreach ( $server_homes as $server_home )
	{
		$servers++;
		
		// Get display IP
		$public_ip = checkDisplayPublicIP($server_home['display_public_ip'],$server_home['ip'] != $server_home['agent_ip'] ? $server_home['ip'] : $server_home['agent_ip']);
		
		$remote = new OGPRemoteLibrary($server_home['agent_ip'], $server_home['agent_port'], $server_home['encryption_key'], $server_home['timeout']);
		$screen_running = $remote->is_screen_running(OGP_SCREEN_TYPE_HOME,$server_home['home_id']) === 1;
		if($screen_running) $servers_running++;
		if( ( $online and $screen_running ) OR ( isset( $_GET['home_id-mod_id-ip-port'] ) 
						 and $_GET['home_id-mod_id-ip-port'] == 
						 $server_home['home_id'].'-'.$server_home['mod_id'].'-'.
						 $server_home['ip'].'-'.$server_home['port'] ) OR ( !$online and $show_all ) )	
		{
			$port = $server_home['port'];
			
			$url = false;
			$server_xml = read_server_config(SERVER_CONFIG_LOCATION."/".$server_home['home_cfg_file']);
			if ($server_xml->protocol == "lgsl"){
				list($c_port, $q_port, $s_port) = lgsl_port_conversion($server_xml->lgsl_query_name, $port, "", "");
				$url = lgsl_software_link($server_xml->lgsl_query_name, $public_ip, $c_port, $q_port, $s_port);
			}
			else if ($server_xml->protocol == "gameq"){
				$query_port = get_query_port($server_xml, $port);
				$gq = new GameQ();
				$server = array(
									'id' => 'server',
									'type' => $server_xml->gameq_query_name,
									'host' => $public_ip . ":" . $query_port,
								);
				$gq->addServer($server);
				$gq->setOption('timeout', 1);
				$gq->setOption('debug', FALSE);
				$results = $gq->requestData();
				if(isset($results['gq_joinlink']) and $results['gq_joinlink'] != "")
				{
					$url = $results['gq_joinlink'];
				}
				else
				{
					if($server_xml->installer == "steamcmd")
						$url = "steam://connect/$public_ip:$port";
					else
						$url = "#Notavailable";
				}
			}
			else if ($server_xml->protocol == "teamspeak3"){
				$url = "ts3server://$public_ip:$port";
			}
			
				
			if ($cols != 4 && $cols != 1 && $counter == $cols) 
			{
				$side = "left";
			}
			elseif ($cols != 4 && $cols != 1)
			{
				$side = "right";
			}
			else
			{
				$side = "center";
			}
			
			if( isset($_GET['home_id-mod_id-ip-port']) )
			{
				$side = "center";
			}
			
			
			if( isset($_GET['home_id-mod_id-ip-port']) )
			{
				$output .= dsi_render_table($server_home["ip"], $server_home["port"], $url, FALSE, TRUE);
			}
			else
			{
				$output .= dsi_render_table($server_home["ip"], $server_home["port"], $url, FALSE, FALSE, FALSE, $screen_running, TRUE, $type);
			}
			if ($counter == $cols) 
			{
				$output .= "\t</tr>\n\t<tr>\n";
				$counter = 1;
			}
			else
			{
				$counter++;
			}
		}
	}
	
	if( ($online and $servers_running > 0) OR ( !$online and $servers > 0) )
		echo "<center>\n".
			 "<table class='center bloc' >\n".
			 $output .
			 "</table>\n".
			 "</center>\n";	
}
?>

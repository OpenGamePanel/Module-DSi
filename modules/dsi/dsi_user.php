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
require_once(DSI_BASEPATH . 'includes/functions_ui.php');
require_once('modules/gamemanager/home_handling_functions.php');
require_once('modules/config_games/server_config_parser.php');
require_once('protocol/lgsl/lgsl_protocol.php');
require_once('protocol/GameQ/GameQ.php');
function exec_ogp_module(){
	global $db;
	echo "<h2>".get_lang("dsi_long")."</h2>";
	
	$isAdmin = $db->isAdmin( $_SESSION['user_id'] );
	if ( $isAdmin )
		$server_homes = $db->getIpPorts();
	else
		$server_homes = $db->getIpPortsForUser($_SESSION['user_id']);
	
	if( $server_homes === FALSE )
    {
        // If there are no games, then there can not be any mods either.
        print_failure( no_game_homes_assigned );
        if ( $isAdmin )
        {
            echo "<p><a href='?m=user_games&amp;p=assign&amp;user_id=$_SESSION[user_id]'>".
                get_lang('assign_game_homes')."</a></p>";
        }
        return;
    }
		
	echo dsi_select_server;
	create_home_selector_address($_GET['m'], $_GET['p'], $server_homes);

	if( isset($_GET['home_id-mod_id-ip-port']) and $_GET['home_id-mod_id-ip-port'] != "")
	{
		list( $home_id,
			  $mod_id,
			  $ip,
			  $port) = explode( "-", $_GET['home_id-mod_id-ip-port'] );
		if( !is_numeric($home_id) or
			!is_numeric($mod_id)  or
			!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $ip) or
			!is_numeric($port) )
			return;
	}
	else
		return;
	
	echo "<br /><br />";
	
	foreach( $server_homes as $server_home )
	{
		if( $server_home['home_id'] == $home_id and
			$server_home['mod_id']  == $mod_id and
			$server_home['ip']      == $ip and
			$server_home['port']    == $port )
		{
			$public_ip = checkDisplayPublicIP($server_home['display_public_ip'],$server_home['ip']);
			$server_xml = read_server_config(SERVER_CONFIG_LOCATION."/".$server_home['home_cfg_file']);
			$url = false;
			if ($server_xml->protocol == "lgsl"){
				list($c_port, $q_port, $s_port) = lgsl_port_conversion($server_xml->lgsl_query_name, $server_home['port'], "", "");
				$url = lgsl_software_link($server_xml->lgsl_query_name, $public_ip, $c_port, $q_port, $s_port);
			}
			else if ($server_xml->protocol == "gameq"){
				$query_port = get_query_port($server_xml, $server_home['port']);
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
						$url = "steam://connect/$public_ip:$server_home[port]";
					else
						$url = "#Notavailable";
				}
			}
			else if ($server_xml->protocol == "teamspeak3"){
				$url = "ts3server://$public_ip:$server_home[port]";
			}
			echo dsi_render_table($server_home["ip"], $server_home["port"],$url);
			break;
		}
	}
}
?>
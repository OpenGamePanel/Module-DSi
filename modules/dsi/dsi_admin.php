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
require_once('modules/config_games/server_config_parser.php'); 
function exec_ogp_module(){
	global $db;
	global $view;
	
	echo "<h2>".get_lang("dsi_admin_long")."</h2>";

	$server_homes = $db->getIpPorts();
	
	if( $server_homes === FALSE )
    {
        // If there are no games, then there can not be any mods either.
        print_failure(get_lang('no_game_homes_assigned'));

        echo "<p><a href='?m=user_games&amp;p=assign&amp;user_id=$_SESSION[user_id]'>" .
			 get_lang("assign_game_homes") . "</a></p>";
        return;
    }
	
	echo get_lang("dsi_select_server");
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
					
	foreach( $server_homes as $server_home )
	{
		if( $server_home['home_id'] == $home_id and
			$server_home['mod_id']  == $mod_id and
			$server_home['ip']      == $ip and
			$server_home['port']    == $port )
		{
			$server_xml = read_server_config(SERVER_CONFIG_LOCATION."/".$server_home['home_cfg_file']);
			$public_ip = checkDisplayPublicIP($server_home['display_public_ip'],$server_home['ip'] != $server_home['agent_ip'] ? $server_home['ip'] : $server_home['agent_ip']);
			
			$mod = preg_replace("/[^a-z0-9_]/", "-", strtolower($server_home['mod_key']));
			
			if ($server_xml->protocol == "gameq"){
				$query_name = $server_xml->gameq_query_name;
			}
			elseif ($server_xml->protocol == "lgsl"){
				$query_name = $server_xml->lgsl_query_name;
			}
			elseif ($server_xml->protocol == "teamspeak3"){
				$query_name = 'ts3';
			}
			else{
				$query_name = $mod; /* If query name does not exist use mod key instead. */
			}
			
			$title = $server_home['game_name']." (Mod: ".$mod.")";
			
			$return = "";
			
			if( isset($_POST) && isset($_FILES["file"]) )
			{
				$types = array();
				if(isset($_POST['type1']))$types[] = $_POST['type1'];
				if(isset($_POST['type2']))$types[] = $_POST['type2'];
				if(isset($_POST['type3']))$types[] = $_POST['type3'];
				if ( $_FILES["file"]["type"] == "image/png" && $_FILES["file"]["size"] < 200000 )
				{
					if ($_FILES["file"]["error"] > 0)
						$return .= "Return Code: " . $_FILES["file"]["error"] . "<br />";
					else
					{
						if(isset($types))
						{
							include( DSI_BASEPATH.'includes/SimpleImage.php' );
							foreach($types as $type)
							{
								if( file_exists(DSI_BASEPATH . "cache/$server_home[ip]_$server_home[port]-$type") )
									unlink(DSI_BASEPATH . "cache/$server_home[ip]_$server_home[port]-$type");
								$bg_location = DSI_BASEPATH . "images/$query_name/";
								$bg_filename = $mod."_".$type.".png";
								if (file_exists($bg_location . $bg_filename))
								{
									unlink($bg_location . $bg_filename);
								}
								if( !file_exists($bg_location) )
									mkdir($bg_location);
								$image = new SimpleImage();
								$image->load($_FILES["file"]["tmp_name"]);
								if($type == "normal")
									$image->resize(359,76);
								elseif($type == "sky")
									$image->resize(180,260);
								elseif($type == "small")
									$image->resize(359,25);
								$image->save("$bg_location$bg_filename");
								$return .= "<p style='color:green;'>Saved as: $bg_location$bg_filename.</p>";
							}
						}
						unlink($_FILES["file"]["tmp_name"]);
					}
				}
				else
					$return .= "<p style='color:red;'>Invalid file, should be PNG format and file size should be less than 200Kb.</p>";
			}
			
			?>
			<br />
			<h2>Background for <?php echo $title; ?></h2>
			
			<table class="center" >
			 <tr>
			  <td colspan="2" >
				<br>
				<form action="" method="post" enctype="multipart/form-data">
				<label for="file">Filename:</label>
				<input type="file" name="file" id="file" accept="image/*" /><br />
				<br>
				<input type="checkbox" name="type1" value="normal" />Normal<br />
				<input type="checkbox" name="type2" value="small" />Small<br />
				<input type="checkbox" name="type3" value="sky" />Sky<br />
				<br>
				<input type="submit" name="submit" value="Submit" />
				</form>
				<?php
				echo $return;
				?>
				<br>
			  </td>
			 </tr>
			<?php
			echo dsi_render_table($server_home["ip"], $server_home["port"], FALSE, FALSE, TRUE, FALSE, TRUE);
			break;
		}
	}
	?>
	</table>
	<?php
} 
?>

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


// Module general information
$module_title = "Dynamic Server Image";
$module_version = "1.0.3";
$db_version = 0;
$module_required = false;
$module_menus = array( 
					   array( 'subpage' => 'list_dsi', 'name'=>'DSi List', 'group'=>'guest' ),
					   array( 'subpage' => 'admin_dsi', 'name'=>'DSi Settings', 'group'=>'admin' )
					 );
?>

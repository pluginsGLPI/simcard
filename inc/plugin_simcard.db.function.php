<?php
/*
   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/
   ----------------------------------------------------------------------

   LICENSE

   This file is part of GLPI.

   GLPI is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with GLPI; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
   ------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: El Sendero S.A.
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')){
	die("Sorry. You can't access directly to this file");
	}

function plugin_simcard_addDevice($conID,$ID,$type){
	$obj = new PluginSimcardDevice();
	$obj->add(array('FK_simcard'=>1,'FK_device'=>$ID,'device_type'=>$type));
	
}

function plugin_simcard_deleteDevice($ID){

	$obj = new PluginSimcardDevice();
	$obj->delete(array('ID'=>$ID));
}

//transfer of simcard and his dropdown glpi_dropdown_plugin_simcard_type
function plugin_simcard_transferDropdown($ID,$entity){
	global $DB;
		
	if ($ID>0){
			// Search init item
			$query="SELECT * 
					FROM `glpi_dropdown_plugin_simcard_type` 
					WHERE `ID` = '$ID'";
			if ($result=$DB->query($query)){
				if ($DB->numrows($result)){
					$data=$DB->fetch_array($result);
					$data=addslashes_deep($data);
					// Search if the location already exists in the destination entity
						$query="SELECT `ID` 
								FROM `glpi_dropdown_plugin_simcard_type` 
								WHERE `FK_entities` = '".$entity."' 
								AND `name` = '".$data['name']."'";
						if ($result_search=$DB->query($query)){
							// Found : -> use it
							if ($DB->numrows($result_search)>0){
								$newID=$DB->result($result_search,0,'ID');

								return $newID;
							}
						}
						// Not found : 
						$input=array();
						$input['tablename']='glpi_dropdown_plugin_simcard_type';
						$input['FK_entities']=$entity;
						$input['value']=$data['name'];
						$input['comments']=$data['comments'];
						$input['type']="under";
						$input['value2']=0; // parentID

						// add item
						$newID=addDropdown($input);

						return $newID;
				} 
			}
		//}
	}
	return 0;
}
	
?>
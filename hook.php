<?php
/*
 * @version $Id: hook.php 7355 2008-10-03 15:31:00Z moyo $
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copynetwork (C) 2003-2006 by the INDEPNET Development Team.

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

foreach (glob(GLPI_ROOT . '/plugins/simcard/inc/*.php') as $file)
	include_once ($file);

function plugin_simcard_AssignToTicket($types){
	global $LANG;
	
	if (plugin_simcard_haveRight("open_ticket","1"))
		$types[PLUGIN_SIMCARD_TYPE]=$LANG['plugin_simcard'][0];
	
	return $types;
}

function plugin_simcard_install(){
	global $DB, $LANG, $CFG_GLPI;
		
		include_once (GLPI_ROOT."/inc/profile.class.php");
		
		if(!TableExists("glpi_application") && !TableExists("glpi_plugin_simcard") ){
		
			plugin_simcard_installing("1.0.0");
		
		}
		
		plugin_simcard_createFirstAccess($_SESSION['glpiactiveprofile']['ID']);
		return true;
}

function plugin_simcard_uninstall(){
	global $DB;
	
	$tables = array("glpi_plugin_simcard",
					"glpi_plugin_simcard_profiles",
					"glpi_dropdown_plugin_simcard_types",
					"glpi_plugin_simcard_device");
					
	foreach($tables as $table)				
		$DB->query("DROP TABLE `$table`;");
	
	$query="DELETE FROM `glpi_display` WHERE `type` = '".PLUGIN_SIMCARD_TYPE."';";
	$DB->query($query);
	
	$query="DELETE FROM `glpi_doc_device` WHERE `device_type` = '".PLUGIN_SIMCARD_TYPE."';";
	$DB->query($query);
	
	$query="DELETE FROM `glpi_bookmark` WHERE `device_type` = '".PLUGIN_SIMCARD_TYPE."';";
	$DB->query($query);
	
	$query="DELETE FROM `glpi_history` WHERE `device_type` = '".PLUGIN_SIMCARD_TYPE."';";
	$DB->query($query);
	
	if (TableExists("glpi_plugin_data_injection_models"))
		$DB->query("DELETE FROM `glpi_plugin_data_injection_models`, `glpi_plugin_data_injection_mappings`, `glpi_plugin_data_injection_infos` USING `glpi_plugin_data_injection_models`, `glpi_plugin_data_injection_mappings`, `glpi_plugin_data_injection_infos`
		WHERE `glpi_plugin_data_injection_models`.`device_type` = '".PLUGIN_SIMCARD_TYPE."'
		AND `glpi_plugin_data_injection_mappings`.`model_id` = `glpi_plugin_data_injection_models`.`ID`
		AND `glpi_plugin_data_injection_infos`.`model_id` = `glpi_plugin_data_injection_models.ID` ");
	
	plugin_init_simcard();
	cleanCache("GLPI_HEADER_".$_SESSION["glpiID"]);

	return true;
}

// Define dropdown relations
function plugin_simcard_getDatabaseRelations(){
	
	$plugin = new Plugin();
	
	if ($plugin->isActivated("simcard"))
		return array("glpi_dropdown_plugin_simcard_plans"=>array("glpi_plugin_simcard_lines"=>"FK_plan"),
					 "glpi_plugin_simcard"=>array("glpi_plugin_simcard_lines"=>"FK_plan"),
					 "glpi_entities"=>array("glpi_plugin_simcard"=>"FK_entities",
											"glpi_dropdown_plugin_simcard_plans"=>"FK_entities"));
	else
		return array();
}

// Define Dropdown tables to be manage in GLPI :
function plugin_simcard_getDropdown(){
	// Table => Name
	global $LANG;
	
	$plugin = new Plugin();
	
	if ($plugin->isActivated("simcard"))
		return array("glpi_dropdown_plugin_simcard_plans"=>$LANG['plugin_simcard']['setup'][1]);
	else
		return array();
}

////// SEARCH FUNCTIONS ///////(){

// Define search option for types of the plugins
function plugin_simcard_getSearchOption(){
	global $LANG;
	$sopt=array();
	
	if (plugin_simcard_haveRight("simcard","r")){
		// Part header
		$sopt[PLUGIN_SIMCARD_TYPE]['common']=$LANG['plugin_simcard'][0];
	
		
		$sopt[PLUGIN_SIMCARD_TYPE][30]['table']='glpi_plugin_simcard';
		$sopt[PLUGIN_SIMCARD_TYPE][30]['field']='ID';
		$sopt[PLUGIN_SIMCARD_TYPE][30]['linkfield']='';
		$sopt[PLUGIN_SIMCARD_TYPE][30]['name']=$LANG['common'][2];
			
		
		$sopt[PLUGIN_SIMCARD_TYPE][1]['table']='glpi_plugin_simcard';
		$sopt[PLUGIN_SIMCARD_TYPE][1]['field']='ID_sim1';
		$sopt[PLUGIN_SIMCARD_TYPE][1]['linkfield']='ID_sim1';
		$sopt[PLUGIN_SIMCARD_TYPE][1]['name']=$LANG['plugin_simcard'][1];
		$sopt[PLUGIN_SIMCARD_TYPE][1]['datatype']='itemlink';
		
		$sopt[PLUGIN_SIMCARD_TYPE][2]['table']='glpi_plugin_simcard';
		$sopt[PLUGIN_SIMCARD_TYPE][2]['field']='ID_sim2';
		$sopt[PLUGIN_SIMCARD_TYPE][2]['linkfield']='ID_sim2';
		$sopt[PLUGIN_SIMCARD_TYPE][2]['name']=$LANG['plugin_simcard'][2];
			
		$sopt[PLUGIN_SIMCARD_TYPE][3]['table']='glpi_plugin_simcard';
		$sopt[PLUGIN_SIMCARD_TYPE][3]['field']='pin1';
		$sopt[PLUGIN_SIMCARD_TYPE][3]['linkfield']='pin1';
		$sopt[PLUGIN_SIMCARD_TYPE][3]['name']=$LANG['plugin_simcard'][3];
		
		$sopt[PLUGIN_SIMCARD_TYPE][4]['table']='glpi_plugin_simcard';
		$sopt[PLUGIN_SIMCARD_TYPE][4]['field']='puk';
		$sopt[PLUGIN_SIMCARD_TYPE][4]['linkfield']='puk';
		$sopt[PLUGIN_SIMCARD_TYPE][4]['name']=$LANG['plugin_simcard'][4];
	
		$sopt[PLUGIN_SIMCARD_TYPE][5]['table']='glpi_plugin_simcard';
		$sopt[PLUGIN_SIMCARD_TYPE][5]['field']='pin2';
		$sopt[PLUGIN_SIMCARD_TYPE][5]['linkfield']='pin2';
		$sopt[PLUGIN_SIMCARD_TYPE][5]['name']=$LANG['plugin_simcard'][5];

		$sopt[PLUGIN_SIMCARD_TYPE][6]['table']='glpi_plugin_simcard';
		$sopt[PLUGIN_SIMCARD_TYPE][6]['field']='comment';
		$sopt[PLUGIN_SIMCARD_TYPE][6]['linkfield']='comment';
		$sopt[PLUGIN_SIMCARD_TYPE][6]['name']=$LANG['plugin_simcard'][6];
		$sopt[PLUGIN_SIMCARD_TYPE][6]['datatype']='text';
			
		$sopt[PLUGIN_SIMCARD_TYPE][4]['table']='glpi_dropdown_plugin_simcard_types';
		$sopt[PLUGIN_SIMCARD_TYPE][4]['field']='name';
		$sopt[PLUGIN_SIMCARD_TYPE][4]['linkfield']='type';
		$sopt[PLUGIN_SIMCARD_TYPE][4]['name']=$LANG['plugin_simcard']['setup'][11];
			
		$sopt[PLUGIN_SIMCARD_TYPE][4]['table']='glpi_dropdown_plugin_simcard_server_type';
		$sopt[PLUGIN_SIMCARD_TYPE][4]['field']='name';
		$sopt[PLUGIN_SIMCARD_TYPE][4]['linkfield']='server';
		$sopt[PLUGIN_SIMCARD_TYPE][4]['name']=$LANG['plugin_simcard']['setup'][11];
		
		$sopt[PLUGIN_SIMCARD_TYPE][9]['table']='glpi_plugin_tlflines';
		$sopt[PLUGIN_SIMCARD_TYPE][9]['field']='number';
		$sopt[PLUGIN_SIMCARD_TYPE][9]['linkfield']='FK_line_sim_1';
		$sopt[PLUGIN_SIMCARD_TYPE][9]['name']=$LANG['plugin_simcard'][7];
		
		$sopt[PLUGIN_SIMCARD_TYPE][9]['table']='glpi_plugin_tlflines';
		$sopt[PLUGIN_SIMCARD_TYPE][9]['field']='number';
		$sopt[PLUGIN_SIMCARD_TYPE][9]['linkfield']='FK_line_sim_2';
		$sopt[PLUGIN_SIMCARD_TYPE][9]['name']=$LANG['plugin_simcard'][8];	
	
		$sopt[PLUGIN_SIMCARD_TYPE][18]['table']='glpi_plugin_simcard';
		$sopt[PLUGIN_SIMCARD_TYPE][18]['field']='helpdesk_visible';
		$sopt[PLUGIN_SIMCARD_TYPE][18]['linkfield']='helpdesk_visible';
		$sopt[PLUGIN_SIMCARD_TYPE][18]['name']=$LANG['software'][46];
		$sopt[PLUGIN_SIMCARD_TYPE][18]['datatype']='bool';		
		
	}
	return $sopt;
}

//for search
function plugin_simcard_addLeftJoin($type,$ref_table,$new_table,$linkfield,&$already_link_tables){

	switch ($new_table){
		
		case "glpi_plugin_simcard_device" : //from simcard list
			return " LEFT JOIN `$new_table` ON (`$ref_table`.`ID` = `$new_table`.`FK_simcard`) ";
			break;
		case "glpi_plugin_simcard" : // From items
			$out= " LEFT JOIN `glpi_plugin_simcard_device` ON (`$ref_table`.`ID` = `glpi_plugin_simcard_device`.`FK_device` AND `glpi_plugin_simcard_device`.`device_type`= '$type') ";
			$out.= " LEFT JOIN `glpi_plugin_simcard` ON (`glpi_plugin_simcard`.`ID` = `glpi_plugin_simcard_device`.`FK_simcard`) ";
			return $out;
			break;
		case "glpi_dropdown_plugin_simcard_type" : // From items
			$out=addLeftJoin($type,$ref_table,$already_link_tables,"glpi_plugin_simcard",$linkfield);
			$out.= " LEFT JOIN `glpi_dropdown_plugin_simcard_type` ON (`glpi_dropdown_plugin_simcard_type`.`ID` = `glpi_plugin_simcard`.`type`) ";
			return $out;
			break;
	}
	
	return "";
}

//force groupby for multible links to items
function plugin_simcard_forceGroupBy($type){

	return true;
	switch ($type){
		case PLUGIN_SIMCARD_TYPE:
			return true;
			break;
		
	}
	return false;
}

//display custom fields in the search
function plugin_simcard_giveItem($type,$ID,$data,$num){
	global $CFG_GLPI, $INFOFORM_PAGES, $LANG,$SEARCH_OPTION,$LINK_ID_TABLE,$DB;
	
	$table=$SEARCH_OPTION[$type][$ID]["table"];
	$field=$SEARCH_OPTION[$type][$ID]["field"];

	switch ($table.'.'.$field){
		//display associated items with simcard
		case "glpi_plugin_simcard_device.FK_device" :
			$query_device = "SELECT DISTINCT `device_type` 
							FROM `glpi_plugin_simcard_device` 
							WHERE `FK_simcard` = '".$data['ID']."' 
							ORDER BY `device_type`";
			$result_device = $DB->query($query_device);
			$number_device = $DB->numrows($result_device);
			$y = 0;
			$out='';
			$simcard=$data['ID'];
			if ($number_device>0){
				$ci=new CommonItem();
				while ($y < $number_device) {
					$column="name";
					if ($type==TRACKING_TYPE) $column="ID";
					$type=$DB->result($result_device, $y, "device_type");
					if (!empty($LINK_ID_TABLE[$type])){
						
						$query = "SELECT `".$LINK_ID_TABLE[$type]."`.*, `glpi_plugin_simcard_device`.`ID` AS IDD, `glpi_entities`.`ID` AS entity "
						." FROM `glpi_plugin_simcard_device`, `".$LINK_ID_TABLE[$type]
						."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`ID`=`".$LINK_ID_TABLE[$type]."`.`FK_entities`) "
						." WHERE `".$LINK_ID_TABLE[$type]."`.`ID` = `glpi_plugin_simcard_device`.`FK_device` 
							AND `glpi_plugin_simcard_device`.`device_type` = '$type' 
							AND `glpi_plugin_simcard_device`.`FK_simcard` = '".$simcard."' "
						. getEntitiesRestrictRequest(" AND ",$LINK_ID_TABLE[$type],'','',isset($CFG_GLPI["recursive_type"][$type])); 
	
						if (in_array($LINK_ID_TABLE[$type],$CFG_GLPI["template_tables"])){
							$query.=" AND ".$LINK_ID_TABLE[$type].".is_template='0'";
						}
						$query.=" ORDER BY `glpi_entities`.`completename`, `".$LINK_ID_TABLE[$type]."`.`$column` ";
						
						if ($result_linked=$DB->query($query))
							if ($DB->numrows($result_linked)){
								$ci->setType($type);
								while ($data=$DB->fetch_assoc($result_linked)){
									$out.=$ci->getType()." - ";
									$ID="";
									if($_SESSION["glpiview_ID"]||empty($data["name"])) $ID= " (".$data["ID"].")";
									$name= "<a href=\"".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[$type]."?ID=".$data["ID"]."\">"
									.$data["name"]."$ID</a>";
									$out.=$name."<br>";
									
								}
							}else
								$out.=' ';
						}else
							$out.=' ';
					$y++;
				}
			}
		return $out;
		break;
	}
	return "";
}


////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_simcard_MassiveActions($type){
	global $LANG;

	switch ($type){
		case PLUGIN_SIMCARD_TYPE:
			return array(
				// GLPI core one
				"add_document"=>$LANG['document'][16],
				// association with glpi items
				"plugin_simcard_install"=>$LANG['plugin_simcard']['setup'][23],
				"plugin_simcard_desinstall"=>$LANG['plugin_simcard']['setup'][24],
				//tranfer simcard to another entity
				"plugin_simcard_transfert"=>$LANG['buttons'][48],
				);
			break;
		default:
			//adding simcard from items lists
			if (in_array($type, plugin_simcard_getTypes())) {
				return array("plugin_simcard_add_item"=>$LANG['plugin_simcard']['setup'][25]);
			}
	}	
	return array();
}

function plugin_simcard_MassiveActionsDisplay($type,$action){
	global $LANG,$CFG_GLPI;

	switch ($type){		
		case PLUGIN_SIMCARD_TYPE:
			switch ($action){
				
				// No case for add_document : use GLPI core one
				case "plugin_simcard_install":
					dropdownAllItems("item_item",0,0,-1,plugin_simcard_getTypes());
					echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
				break;
				case "plugin_simcard_desinstall":
					dropdownAllItems("item_item",0,0,-1,plugin_simcard_getTypes());
				echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
				break;
				case "plugin_simcard_transfert":
					dropdownValue("glpi_entities", "FK_entities", '');
				echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
				break;
			}
		break;
	}
	if (in_array($type, plugin_simcard_getTypes())) {
				//usePlugin('simcard',true);
				plugin_simcard_dropdownSimcard("ID_sim1");
				echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
		}
	return "";
}

function plugin_simcard_MassiveActionsProcess($data){
	global $LANG,$DB;

	switch ($data['action']){
		
		case "plugin_simcard_add_item":
			$PluginSimcard=new PluginSimcard();
			$ci2=new CommonItem();
			if ($PluginSimcard->getFromDB($data['ID_sim1'])){
				foreach ($data["item"] as $key => $val){
					if ($val==1) {
						// Items exists ?
						if ($ci2->getFromDB($data["device_type"],$key)){
							// Entity security
							if (!isset($PluginSimcard->obj->fields["FK_entities"])
								||$ci2->obj->fields["FK_entities"]==$PluginSimcard->obj->fields["FK_entities"]
								||($ci2->obj->fields["recursive"] && in_array($ci2->obj->fields["FK_entities"], getEntityAncestors($PluginSimcard->obj->fields["FK_entities"])))){
								plugin_simcard_addDevice($data["ID_sim1"],$key,$data['device_type']);
							}
						}
					}
				}
			}
		break;
		
		case "plugin_simcard_install":
			if ($data['device_type']==PLUGIN_SIMCARD_TYPE){
			
			$PluginSimcard=new PluginSimcard();
			$ci=new CommonItem();
			foreach ($data["item"] as $key => $val){
				if ($val==1){
					// Items exists ?
					if ($PluginSimcard->getFromDB($key)){
						// Entity security
						if ($ci->getFromDB($data['type'],$data['item_item'])){
							if (!isset($PluginSimcard->obj->fields["FK_entities"])
								||$ci->obj->fields["FK_entities"]==$PluginSimcard->obj->fields["FK_entities"]
								||($ci->obj->fields["recursive"] && in_array($ci->obj->fields["FK_entities"], getEntityAncestors($PluginSimcard->obj->fields["FK_entities"])))){
								plugin_simcard_addDevice($key,$data["item_item"],$data['type']); 
							}
						}
					}
				}
			}
		}
		break;
		case "plugin_simcard_desinstall":
				if ($data['device_type']==PLUGIN_SIMCARD_TYPE){
					foreach ($data["item"] as $key => $val){
						if ($val==1){
							$query="DELETE FROM 
									glpi_plugin_simcard_device 
									WHERE device_type='".$data['type']."' 
									AND FK_device='".$data['item_item']."' 
									AND FK_simcard = '$key'";
							$DB->query($query);
					}
				}
			}
		break;
		case "plugin_simcard_transfert":
		if ($data['device_type']==PLUGIN_SIMCARD_TYPE){
			foreach ($data["item"] as $key => $val){
				if ($val==1){
					$PluginSimcard=new PluginSimcard;
					$PluginSimcard->getFromDB($key);

					$type=plugin_simcard_transferDropdown($PluginSimcard->fields["type"],$data['FK_entities']);
					$values["ID"] = $key;
					$values["type"] = $type;
					$PluginSimcard->update($values);
					unset($values);
					$values["ID"] = $key;
					$values["FK_entities"] = $data['FK_entities'];
					$PluginSimcard->update($values);
				}
			}
		}	
		break;
	}
}

//////////////////////////////

// Hook done on delete item case

function plugin_pre_item_delete_simcard($input){
	if (isset($input["_item_type_"]))
		switch ($input["_item_type_"]){
			case PROFILE_TYPE :
				// Manipulate data if needed 
				$PluginSimcardProfile=new PluginSimcardProfile;
				$PluginSimcardProfile->cleanProfiles($input["ID"]);
				break;
		}
	return $input;
}

function plugin_item_delete_simcard($parm){
	
	if (isset($parm["type"]))
		switch ($parm["type"]){

			case TRACKING_TYPE :
				$PluginSimcard=new PluginSimcard;
				$PluginSimcard->cleanItems($parm['ID'], $parm['type']);
				return true;
				break;
		}
		
	return false;
}

// Hook done on purge item case
function plugin_item_purge_simcard($parm){
		
	if (in_array($parm["type"], plugin_simcard_getTypes())
		&& $parm["type"]!=TRACKING_TYPE) {
		$PluginSimcard=new PluginSimcard;
		$PluginSimcard->cleanItems($parm["ID"],$parm["type"]);
		return true;
	}else
		return false;
	
}

// Define headings added by the plugin
function plugin_get_headings_simcard($type,$ID,$withtemplate){
	global $LANG;
	
	
	
	if (in_array($type,plugin_simcard_getTypes())||
		$type==PROFILE_TYPE||
		$type==ENTERPRISE_TYPE) {
		// template case
		if ($ID>0 && !$withtemplate){
				return array(
					1 => $LANG['plugin_simcard'][0],
					);
		}
	}
	
	return false;
}
		
// Define headings actions added by the plugin	 
function plugin_headings_actions_simcard($type){
		
	if (in_array($type,plugin_simcard_getTypes())||
		$type==PROFILE_TYPE||
		$type==ENTERPRISE_TYPE) {
		return array(
			1 => "plugin_headings_simcard",
		);
	}
	return false;
	
}

// action heading
function plugin_headings_simcard($type,$ID,$withtemplate=0){
	global $CFG_GLPI;
	
	switch ($type){
			case PROFILE_TYPE :
				$prof=new PluginSimcardProfile();	
				if (!$prof->GetfromDB($ID))
					plugin_simcard_createAccess($ID);
				$prof->showForm($CFG_GLPI["root_doc"]."/plugins/simcard/front/plugin_simcard.profile.php",$ID);
				break;
			case ENTERPRISE_TYPE :
				echo "<div align='center'>";
				plugin_simcard_showenterpriseAssociated(ENTERPRISE_TYPE,$ID);
				echo "</div>";
			break;
			default :
				if (in_array($type, plugin_simcard_getTypes())){
					echo "<div align='center'>";
					echo plugin_simcard_showAssociated($type,$ID);
					echo "</div>";
				}
			break;
		}
}

// Cron function : name= cron_plugin_PLUGINNAME
function cron_plugin_simcard(){

	$plugin = new Plugin();
	
	if($plugin->isActivated("simcard"))
		plugin_simcard_alerts();
}

//define data_injection variables to import
function plugin_simcard_data_injection_variables() {
	global $IMPORT_PRIMARY_TYPES, $DATA_INJECTION_MAPPING, $LANG, $IMPORT_TYPES,$DATA_INJECTION_INFOS;
	
	$plugin = new Plugin();
	
	if (plugin_simcard_haveRight("simcard","w") && $plugin->isActivated("simcard")){
	
		if (!in_array(PLUGIN_SIMCARD_TYPE, $IMPORT_PRIMARY_TYPES)) {
			
			//Add types of objects to be injected by data_injection plugin
			array_push($IMPORT_PRIMARY_TYPES, PLUGIN_SIMCARD_TYPE);
		
			
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['ID_sim1']['table'] = 'glpi_plugin_simcard';
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['ID_sim1']['field'] = 'ID_sim1';
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['ID_sim1'] = $LANG['plugin_simcard'][1];
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['ID_sim1']['type'] = "number";
			
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['ID_twin']['table'] = 'glpi_plugin_simcard';
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['ID_twin']['field'] = 'ID_sim2';
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['ID_twin'] = $LANG['plugin_simcard'][2];
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['ID_twin']['type'] = "text";
			
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['pin1']['table'] = 'glpi_plugin_simcard';
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['pin1']['field'] = 'pin1';
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['pin1'] = $LANG['plugin_simcard'][3];
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['pin1']['type'] = "text";
			
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['puk']['table'] = 'glpi_plugin_simcard';
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['puk']['field'] = 'puk';
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['puk'] = $LANG['plugin_simcard'][4];
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['puk']['type'] = "text";
			
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['pin2']['table'] = 'glpi_plugin_simcard';
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['pin2']['field'] = 'pin2';
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['pin2'] = $LANG['plugin_simcard'][5];
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['pin2']['type'] = "text";
			
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['FK_line_sim_1']['table'] = 'glpi_plugin_tlflines';
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['FK_line_sim_1']['field'] = 'number';
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['FK_line_sim_1']['linkfield'] = 'FK_line_sim_1';
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['FK_line_sim_1']['name'] = $LANG['plugin_simcard'][7];
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['FK_line_sim_1']['type'] = "text";
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['FK_line_sim_1']['table_type'] = "single";
			
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['FK_line_sim_2']['table'] = 'glpi_plugin_tlflines';
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['FK_line_sim_2']['field'] = 'number';
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['FK_line_sim_2']['linkfield'] = 'FK_line_sim_2';
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['FK_line_sim_2']['name'] = $LANG['plugin_simcard'][8];
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['FK_line_sim_2']['type'] = "text";
			$DATA_INJECTION_MAPPING[PLUGIN_SIMCARD_TYPE]['FK_line_sim_2']['table_type'] = "single";
			
			$DATA_INJECTION_MAPPING[PLUGIN_TLFLINES_TYPE]['FK_enterprise']['table'] = 'glpi_enterprises';
			$DATA_INJECTION_MAPPING[PLUGIN_TLFLINES_TYPE]['FK_enterprise']['field'] = 'name';
			$DATA_INJECTION_MAPPING[PLUGIN_TLFLINES_TYPE]['FK_enterprise']['linkfield'] = 'FK_enterprise';
			$DATA_INJECTION_MAPPING[PLUGIN_TLFLINES_TYPE]['FK_enterprise']['name'] = $LANG['plugin_simcard']['setup'][14];
			$DATA_INJECTION_MAPPING[PLUGIN_TLFLINES_TYPE]['FK_enterprise']['type'] = "text";
			$DATA_INJECTION_MAPPING[PLUGIN_TLFLINES_TYPE]['FK_enterprise']['table_type'] = "single";
			
		}
	}
}


// Define PDF informations added by the plugin	 
function plugin_headings_actionpdf_simcard($type){
	
	if (in_array($type,plugin_simcard_getTypes())) {
		return array(
				1 => "plugin_headings_simcard_PDF",
				);
	} else {
		return false;
	}	
}

// Genrerate PDF with informations added by the plugin
// Define headings actions added by the plugin	 
function plugin_headings_simcard_PDF($pdf,$ID,$type) {
	
	if (in_array($type, plugin_simcard_getTypes())){
		echo plugin_simcard_showAssociated_PDF($pdf,$ID,$type);
	}
	
}

/**
 * Hook : options for one type
 * 
 * @param $type of item
 * 
 * @return array of string which describe the options
 */
function plugin_simcard_prefPDF($type) {
	global $LANG;

	$tabs=array();	
	switch ($type) {
		case PLUGIN_SIMCARD_TYPE:
			$item = new PluginSimcard();
			$tabs = $item->defineTabs(1,'');
			break;
	}
	return $tabs;
}

/**
 * Hook to generate a PDF for a type
 * 
 * @param $type of item
 * @param $tab_id array of ID
 * @param $tab of option to be printed
 * @param $page boolean true for landscape
 */
function plugin_simcard_generatePDF($type, $tab_id, $tab, $page=0) {
	$pdf = new simplePDF('a4', ($page ? 'landscape' : 'portrait'));

	$nb_id = count($tab_id);

	foreach($tab_id as $key => $ID)	{
		
		if (plugin_pdf_add_header($pdf,$ID,$type)) {
			$pdf->newPage();
		} else {
			// Object not found or no right to read
			continue;
		}

	switch($type){
		case PLUGIN_SIMCARD_TYPE:		
			plugin_simcard_main_PDF($pdf,$ID);
			
			foreach($tab as $i)	{
				switch($i) { // See plugin_applicatif::defineTabs();
					case 1:
						plugin_simcard_showDevice_PDF($pdf,$ID);
						break;
					case 6:
						plugin_pdf_ticket($pdf,$ID,$type);
						plugin_pdf_oldticket($pdf,$ID,$type);
						break;
					case 8:
						plugin_pdf_contract ($pdf,$ID,$type);
						break;
					case 9:
						plugin_pdf_document($pdf,$ID,$type);
						break;
					case 10:
						plugin_pdf_note($pdf,$ID,$type);
						break;
					case 12:
						plugin_pdf_history($pdf,$ID,$type);
						break;
					default:
						plugin_pdf_pluginhook($i,$pdf,$ID,$type);
				}
			}
			break;
		} // Switch type
	} // Each ID
	$pdf->render();	
}

?>
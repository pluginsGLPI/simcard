<?php
/*
 * @version $Id: setup.php,v 1.2 2006/04/02 14:45:27 moyo Exp $
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

include_once ("inc/plugin_simcard.auth.function.php");
include_once ("inc/plugin_simcard.profile.class.php");

// Init the hooks of the plugins -Needed
function plugin_init_simcard() {
	
	global $PLUGIN_HOOKS,$CFG_GLPI,$LANG;
	
	// Params : plugin name - string type - number - class - table - form page
	registerPluginType('simcard', 'PLUGIN_SIMCARD_TYPE', 5746, array(
		'classname'  => 'PluginSimcard',
		'tablename'  => 'glpi_plugin_simcard',
		'formpage'   => 'front/plugin_simcard.form.php',
		'searchpage' => 'index.php',
		'typename'   => $LANG['plugin_simcard'][0],
		'deleted_tables' => true,
		'specif_entities_tables' => true,
		'linkgroup_types' => true,
		'linkuser_types' => true,
		'recursive_type' => true,
		'doc_types' => true,
		'contract_types' => true,	
		'helpdesk_visible_types' => true
		));

	//load changeprofile function
	$PLUGIN_HOOKS['change_profile']['simcard'] = 'plugin_simcard_changeProfile';
	$PLUGIN_HOOKS['assign_to_ticket']['simcard'] = true;
	
	//if glpi is loaded
	if (isset($_SESSION["glpiID"])){
		
		//add simcard to entities_tables (for 'by entities' using)
		array_push($CFG_GLPI["specif_entities_tables"],"glpi_dropdown_plugin_simcard_type");
		
		//if environment plugin is installed
		if (isset($_SESSION["glpi_plugin_environment_installed"]) && $_SESSION["glpi_plugin_environment_installed"]==1){	
			//init $_SESSION for environment using
			$_SESSION["glpi_plugin_environment_simcard"]=1;
			
			if(plugin_simcard_haveRight("simcard","r")){
				//no menu entry
				$PLUGIN_HOOKS['menu_entry']['simcard'] = false;
				//redirect link to search simcard
				$PLUGIN_HOOKS['submenu_entry']['environment']['search']['simcard'] = 'front/plugin_environment.form.php?plugin=simcard&search=1';
				//add simcard to items details
				$PLUGIN_HOOKS['headings']['simcard'] = 'plugin_get_headings_simcard';
				$PLUGIN_HOOKS['headings_action']['simcard'] = 'plugin_headings_actions_simcard';
				$PLUGIN_HOOKS['headings_actionpdf']['simcard'] = 'plugin_headings_actionpdf_simcard';
			}
			
			if (plugin_simcard_haveRight("simcard","w")){
				//redirect link to add simcard
				$PLUGIN_HOOKS['submenu_entry']['environment']['add']['simcard'] = 'front/plugin_environment.form.php?plugin=simcard&add=1';
				//load pre_item_delete function (delete simcard references from item before deleting item)
				$PLUGIN_HOOKS['pre_item_delete']['simcard'] = 'plugin_pre_item_delete_simcard';
				//load item_purge function (delete simcard references from item after purge item)
				$PLUGIN_HOOKS['item_purge']['simcard'] = 'plugin_item_purge_simcard';
				//use massiveaction in the plugin
				$PLUGIN_HOOKS['use_massive_action']['simcard']=1;

			}
		//if environment plugin isn't installed
		}else{
		
			// Display a menu entry ?
			if(plugin_simcard_haveRight("simcard","r")){
				//menu entry
				$PLUGIN_HOOKS['menu_entry']['simcard'] = true;
				//search link
				$PLUGIN_HOOKS['submenu_entry']['simcard']['search'] = 'index.php';
				//add simcard to items details
				$PLUGIN_HOOKS['headings']['simcard'] = 'plugin_get_headings_simcard';
				$PLUGIN_HOOKS['headings_action']['simcard'] = 'plugin_headings_actions_simcard';
				$PLUGIN_HOOKS['headings_actionpdf']['simcard'] = 'plugin_headings_actionpdf_simcard';
			}
			
			if (plugin_simcard_haveRight("simcard","w")){
				//add link
				$PLUGIN_HOOKS['submenu_entry']['simcard']['add'] = 'front/plugin_simcard.form.php';
				//load pre_item_delete function (delete simcard references from item before deleting item)
				$PLUGIN_HOOKS['pre_item_delete']['simcard'] = 'plugin_pre_item_delete_simcard';
				//load item_purge function (delete simcard references from item after purge item)
				$PLUGIN_HOOKS['item_purge']['simcard'] = 'plugin_item_purge_simcard';
				//use massiveaction in the plugin
				$PLUGIN_HOOKS['use_massive_action']['simcard']=1;
			}
		}
		
		// Import from Data_Injection plugin
		$PLUGIN_HOOKS['data_injection']['simcard'] = "plugin_simcard_data_injection_variables";
		// Define the type for which we know how to generate PDF, need :
		// - plugin_simcard_prefPDF($type)
		// - plugin_simcard_generatePDF($type, $tab_id, $tab, $page=0)
		$PLUGIN_HOOKS['plugin_pdf'][PLUGIN_SIMCARD_TYPE]='simcard';
	}
}

// Get the name and the version of the plugin - Needed
function plugin_version_simcard(){
	global $LANG;
	
	return array (
		'name' => $LANG['plugin_simcard'][0],
		'version' => '1.0.0',
		'author'=>'El Sendero',
		'homepage'=>'https://www.elsendero.es',
		'minGlpiVersion' => '0.72',// For compatibility / no install in version < 0.72
	);
	
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_simcard_check_prerequisites(){
	if (GLPI_VERSION>=0.72){
		return true;
	} else {
		echo "GLPI version not compatible need 0.72";
	}
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_simcard_check_config(){
	return true;
}

// Define rights for the plugin types
function plugin_simcard_haveTypeRight($type,$right){
	switch ($type){
		case PLUGIN_SIMCARD_TYPE :
			return plugin_simcard_haveRight("simcard",$right);
			break;
	}
}

?>
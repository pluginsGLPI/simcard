<?php
/*
 * @version $Id: HEADER 1 2010-03-03 21:49 Tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PAR/linkTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
// ----------------------------------------------------------------------
// Original Author of file: El Sendero
// Purpose of file: plugin simcards v1.1.0 - GLPI 0.78
// ----------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_simcards() {
	global $PLUGIN_HOOKS,$CFG_GLPI,$LANG;
   
   //load changeprofile function
	$PLUGIN_HOOKS['change_profile']['simcards'] = array('PluginSimcardsProfile','changeProfile');
	$PLUGIN_HOOKS['assign_to_ticket']['simcards'] = true;
	
	if (class_exists('PluginSimcardsSimcard_Item')) { // only if plugin activated
      $PLUGIN_HOOKS['pre_item_purge']['simcards'] = array('Profile'=>array('PluginSimcardsProfile', 'purgeProfiles'));
      $PLUGIN_HOOKS['plugin_datainjection_populate']['simcards'] = 'plugin_datainjection_populate_simcards';
      $PLUGIN_HOOKS['item_purge']['simcards'] = array();
      foreach (PluginSimcardsSimcard_Item::getClasses(true) as $type) {
         $PLUGIN_HOOKS['item_purge']['simcards'][$type] = 'plugin_item_purge_simcards';
      }
   }
   
   
   	if (haveRight("config","w"))
		$PLUGIN_HOOKS["config_page"]["simcards"] = "front/simcard.form.php";
		
   
	// Params : plugin name - string type - number - class - table - form page
	Plugin::registerClass('PluginSimcardsSimcard', array(
		'linkgroup_types' => true,
		'linkuser_types' => true,
		'doc_types' => true,
		'contract_types' => true,
		'helpdesk_types'         => true,
		'helpdesk_visible_types' => true
   ));

	//if glpi is loaded
	if (getLoginUserID()) {

		//if environment plugin is installed
		if (isset($_SESSION["glpi_plugin_environment_installed"]) && $_SESSION["glpi_plugin_environment_installed"]==1) {
			//init $_SESSION for environment using
			$_SESSION["glpi_plugin_environment_simcards"]=1;

			if (plugin_simcards_haveRight("simcards","r")) {
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['simcards']['title'] = $LANG['plugin_simcards'][4];
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['simcards']['page'] = '/plugins/simcards/front/simcards.php';
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['simcards']['links']['search'] = '/plugins/simcards/front/simcards.php';
				//add simcards to items details
				$PLUGIN_HOOKS['headings']['simcards'] = 'plugin_get_headings_simcards';
				$PLUGIN_HOOKS['headings_action']['simcards'] = 'plugin_headings_actions_simcards';
				$PLUGIN_HOOKS['headings_actionpdf']['simcards'] = 'plugin_headings_actionpdf_simcards';
			}

			if (plugin_simcards_haveRight("simcards","w")) {
				//redirect link to add simcards
				$PLUGIN_HOOKS['submenu_entry']['environment']['options']['simcards']['links']['add'] = '/plugins/simcards/front/simcard.form.php';
				//use massiveaction in the plugin
				$PLUGIN_HOOKS['use_massive_action']['simcards']=1;

			}
		//if environment plugin isn't installed
		} else {

			
			
			// Display a menu entry ?
			if (plugin_simcards_haveRight("simcards","r")) {
				//menu entry
				$PLUGIN_HOOKS['menu_entry']['simcards'] = 'front/simcard.php';
				//search link
				$PLUGIN_HOOKS['submenu_entry']['simcards']['search'] = 'front/simcard.php';
				//add simcards to items details
				$PLUGIN_HOOKS['headings']['simcards'] = 'plugin_get_headings_simcards';
				$PLUGIN_HOOKS['headings_action']['simcards'] = 'plugin_headings_actions_simcards';
				$PLUGIN_HOOKS['headings_actionpdf']['simcards'] = 'plugin_headings_actionpdf_simcards';
			}

			if (plugin_simcards_haveRight("simcards","w")) {
				//add link
				$PLUGIN_HOOKS['submenu_entry']['simcards']['add'] = 'front/simcard.form.php';
				//use massiveaction in the plugin
				$PLUGIN_HOOKS['use_massive_action']['simcards']=1;
			}
			
			
			
		}

		// Import from Data_Injection plugin
		$PLUGIN_HOOKS['migratetypes']['simcards'] = 'plugin_datainjection_migratetypes_simcards';
		$PLUGIN_HOOKS['plugin_pdf']['PluginSimcardsSimcard']='simcards';
		/////////////////////////////////////////////////////////////////
		$PLUGIN_HOOKS['menu']['simcards'] = 'inventory'; //Para el menu ISP de la aplicación base display.function.php
		/////////////////////////////////////////////////////////////////
	}
}

// Get the name and the version of the plugin - Needed
function plugin_version_simcards() {
	global $LANG;

	return array (
		'name' => $LANG['plugin_simcards'][4],
		'version' => '1.1.0',
		'author'=>"El Sendero  <a href='http://www.elsendero.es'><img src='".GLPI_ROOT."/plugins/simcards/pics/favicon.ico'></a>",
		'homepage'=>'https://forge.indepnet.net/projects/show/simcards',
		'minGlpiVersion' => '0.78',// For compatibility / no install in version < 0.72
	);

}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_simcards_check_prerequisites() {
	if (GLPI_VERSION>=0.78) {
		return true;
	} else {
		echo "GLPI version not compatible need 0.78";
	}
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_simcards_check_config() {
	return true;
}

function plugin_simcards_haveRight($module,$right) {
	$matches=array(
			""  => array("","r","w"), // ne doit pas arriver normalement
			"r" => array("r","w"),
			"w" => array("w"),
			"1" => array("1"),
			"0" => array("0","1"), // ne doit pas arriver non plus
		      );
	if (isset($_SESSION["glpi_plugin_simcards_profile"][$module])&&in_array($_SESSION["glpi_plugin_simcards_profile"][$module],$matches[$right]))
		return true;
	else return false;
}

function plugin_datainjection_migratetypes_simcards($types) {
   $types[1300] = 'PluginSimcardsSimcard';
   return $types;
}

?>
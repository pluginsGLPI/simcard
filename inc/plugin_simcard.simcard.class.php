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

class PluginSimcardConfig extends CommonDBTM {

	function __construct () {
		$this->table="glpi_plugin_simcard_config";
	}
}
	
class PluginSimcard extends CommonDBTM {

	function __construct () {
		$this->table="glpi_plugin_simcard";
		$this->type=PLUGIN_SIMCARD_TYPE;
		$this->entity_assign=true;
		$this->may_be_recursive=true;
		$this->dohistory=true;
	}
	//clean if simcard are deleted
	function cleanDBonPurge($ID) {
		
		global $DB;

		$query = "DELETE 
					FROM `glpi_plugin_simcard_device` 
					WHERE `FK_simcard` = '$ID'";
		$DB->query($query);
		$query = "DELETE 
					FROM `glpi_doc_device` 
					WHERE `FK_device` = '$ID' 
					AND `device_type` = '".PLUGIN_SIMCARD_TYPE."' ";
		$DB->query($query);
	}
	
	//clean simcard if items are deleted
	function cleanItems($ID,$type) {
	
		global $DB;
		
		$query = "DELETE 
					FROM `glpi_plugin_simcard_device` 
					WHERE `FK_device` = '$ID' 
					AND `device_type` = '$type'";
		$DB->query($query);
	}
	
	//define header form
	function defineTabs($ID,$withtemplate){
		global $LANG;
		//principal
		$ong[1]=$LANG['title'][26];
		if ($ID > 0){
			if (haveRight("show_all_ticket","1")) {
				$ong[6]=$LANG['title'][28];
			}
			if (haveRight("contract","r")) {
				$ong[8]=$LANG['Menu'][26];
			}
			//documents
			if (haveRight("document","r"))
				$ong[9]=$LANG['Menu'][27];
			//notes
			if (haveRight("notes","r"))
				$ong[10]=$LANG['title'][37];
			//History
			$ong[12]=$LANG['title'][38];
		}
		return $ong;
	}

	function showForm ($target,$ID,$withtemplate='') {
		
		GLOBAL  $CFG_GLPI, $LANG;

		if (!plugin_simcard_haveRight("simcard","r")) return false;
		
		$spotted = false;

		if ($ID>0){
			if($this->can($ID,'r')){
				$spotted = true;
			}
		}else{
			if($this->can(-1,'w')){
				$spotted = true;
				$this->getEmpty();
			}
		}
		
		if ($spotted){
			
			$this->showTabs($ID, $withtemplate,$_SESSION['glpi_tab']);
			
			$canedit=$this->can($ID,'w');
			$canrecu=$this->can($ID,'recursive');

			echo "<form method='post' name=form action=\"$target\">";
			if (empty($ID)||$ID<0){
					echo "<input type='hidden' name='FK_entities' value='".$_SESSION["glpiactive_entity"]."'>";
				}
			echo "<div class='center' id='tabsbody'>";
			echo "<table class='tab_cadre_fixe'>";
			$this->showFormHeader($ID,'',2);

			echo "<tr><td class='tab_bg_1' valign='top'>";
	
			echo "<table cellpadding='2' cellspacing='2' border='0'>\n";
			
			
			//simcard type
			echo "<tr><td>".$LANG['plugin_simcard']['setup'][11].": </td>";
			echo "<td>";
				if ($canedit)
					dropdownValue("glpi_dropdown_plugin_simcard_types", "type", $this->fields["type"],1,$this->fields["FK_entities"]);
				else
					echo getdropdownname("glpi_dropdown_plugin_simcard_types",$this->fields["type"]);
			echo "</td></tr>";
			
			
			
			//id sim 1
			echo "<tr><td>".$LANG['plugin_simcard'][1].": </td>";
			echo "<td>";
			autocompletionTextField("ID_sim1","glpi_plugin_simcard","ID_sim1",$this->fields["ID_sim1"],40,$this->fields["FK_entities"]);	
			echo "</td></tr>";
				
			//pin 1
			echo "<tr><td>".$LANG['plugin_simcard'][3].": </td>";
			echo "<td>";
			autocompletionTextField("pin1","glpi_plugin_simcard","pin1",$this->fields["pin1"],4,$this->fields["FK_entities"]);	
			echo "</td></tr>";
			
			//Linea 1
			$plugin = new Plugin;
			if ($plugin->isInstalled("tlflines") && $plugin->isActivated("tlflines")) {		
				echo "<tr><td>".$LANG['plugin_simcard'][7].": </td><td>";
				if ($canedit) {
					dropdownValue("glpi_plugin_tlflines", "FK_line_sim_1", $this->fields["FK_line_sim_1"],1,$this->fields["FK_entities"]);
				
				
				} else {
					echo getdropdownname("glpi_plugin_tlflines", $this->fields["FK_line_sim_1"]);
				}
				echo "</td></tr>";
			}
			
			
			
			

			
			echo "</table>";
			echo "</td>";	
			echo "<td class='tab_bg_1' valign='top'>";
			echo "<table cellpadding='2' cellspacing='2' border='0'>";
		
			
			//id sim 2 (twin)
			echo "<tr><td>".$LANG['plugin_simcard'][2].": </td>";
			echo "<td>";
			autocompletionTextField("ID_sim2","glpi_plugin_simcard","ID_sim2",$this->fields["ID_sim2"],40,$this->fields["FK_entities"]);	
			echo "</td></tr>";
			
			//pin 2 
			echo "<tr><td>".$LANG['plugin_simcard'][5].": </td>";
			echo "<td>";
			autocompletionTextField("pin2","glpi_plugin_simcard","pin2",$this->fields["pin2"],4,$this->fields["FK_entities"]);	
			echo "</td></tr>";
			
			//Linea 2
			$plugin = new Plugin;
			if ($plugin->isInstalled("tlflines") && $plugin->isActivated("tlflines")) {		
				echo "<tr><td>".$LANG['plugin_simcard'][8].": </td><td>";
				if ($canedit) {
					dropdownValue("glpi_plugin_tlflines", "FK_line_sim_2", $this->fields["FK_line_sim_2"],1,$this->fields["FK_entities"]);
				
				
				} else {
					echo getdropdownname("glpi_plugin_tlflines", $this->fields["FK_line_sim_2"]);
				}
				echo "</td></tr>";
			}			
			
			
			
			//puk
			echo "<tr><td>".$LANG['plugin_simcard'][4].": </td>";
			echo "<td>";
			autocompletionTextField("puk","glpi_plugin_simcard","puk",$this->fields["puk"],8,$this->fields["FK_entities"]);	
			echo "</td></tr>";

			
			//supplier of cardSim
			echo "<tr><td>".$LANG['plugin_simcard']['setup'][14].": </td>";
			echo "<td>";
			if ($canedit)
				dropdownValue("glpi_enterprises","FK_enterprise",$this->fields["FK_enterprise"],1,$this->fields["FK_entities"]);
			else
				echo getdropdownname("glpi_enterprises",$this->fields["FK_enterprise"]);
			echo "</td></tr>";
			
			echo "</table>";
			echo "</td>";	
			echo "<td class='tab_bg_1' valign='top'>";

			
			
	
			
			
			echo "</td></tr>";	
			
			echo "<tr><td class='tab_bg_1' valign='top' colspan='3'>";
			//comments of tlflines
			echo "<table cellpadding='2' cellspacing='2' border='0'><tr><td>";
			echo $LANG['plugin_simcard'][6].":	</td></tr>";
			echo "<tr><td align='center'><textarea cols='125' rows='3' name='comment' >".$this->fields["comment"]."</textarea>";
			echo "</td></tr>";
			echo "<tr><td>";
			$datestring = $LANG['common'][26].": ";
			$date = convDateTime($this->fields["date_mod"]);
			
			echo $datestring.$date."</td></tr></table>\n";

			echo "</td>";
			echo "</tr>";
			


			if ($canedit) {
			
				if (empty($ID)||$ID<0){
					echo "<tr>";
					echo "<td class='tab_bg_2' valign='top' colspan='3'>";
					echo "<div align='center'><input type='submit' name='add' value=\"".$LANG['buttons'][8]."\" class='submit'></div>";
					echo "</td>";
					echo "</tr>";
		
				} else {
		
					echo "<tr>";
					echo "<td class='tab_bg_2' valign='top' colspan='3'><div align='center'>";
					echo "<input type='hidden' name='ID' value=\"$ID\">\n";
					echo "<input type='submit' name='update' value=\"".$LANG['buttons'][7]."\" class='submit' >";
					if ($this->fields["deleted"]=='0'){
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='delete' value=\"".$LANG['buttons'][6]."\" class='submit'></div>";
					}else {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='restore' value=\"".$LANG['buttons'][21]."\" class='submit'>";
	
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='purge' value=\"".$LANG['buttons'][22]."\" class='submit'></div>";
					}
					
					echo "</td>";
					echo "</tr>";
					
				}	
			}
			
			
			echo "</table></div></form>";
			echo "<div id='tabcontent'></div>";
			echo "<script type='text/javascript'>loadDefaultTab();</script>";

		} else {
			echo "<div align='center'><b>".$LANG['plugin_simcard'][11]."</b></div>";
			return false;

		}
		return true;
	}

}

class PluginSimcardDevice extends CommonDBTM {

	function __construct () {
		$this->table="glpi_plugin_simcard_device";
	}
}

?>
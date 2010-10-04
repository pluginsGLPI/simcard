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

//show form of linking simcard to glpi items
function plugin_simcard_showDevice($instID,$search='') {
	global $DB,$CFG_GLPI, $LANG,$INFOFORM_PAGES,$LINK_ID_TABLE;

	if (!plugin_simcard_haveRight("simcard","r"))	return false;
	$rand=mt_rand();

	$PluginSimcard=new PluginSimcard();
	if ($PluginSimcard->getFromDB($instID)){

		$canedit=$PluginSimcard->can($instID,'w');

		
		$query = "SELECT DISTINCT `device_type`
					FROM `glpi_plugin_simcard_device`
					WHERE `FK_simcard` = '$instID'
					ORDER BY `device_type`";
		$result = $DB->query($query);
		
		$number = $DB->numrows($result);

		$i = 0;

		if (isMultiEntitiesMode()) {
			$colsup=1;
		}else {
			$colsup=0;
		}

		echo "<form method='post' name='simcard_form$rand' id='simcard_form$rand'  action=\"".$CFG_GLPI["root_doc"]."/plugins/simcard/front/plugin_simcard.form.php\">";

		echo "<div class='center'><table class='tab_cadrehov'>";
		echo "<tr><th colspan='".($canedit?(5+$colsup):(4+$colsup))."'>".$LANG['plugin_simcard']['setup'][15].":</th></tr><tr>";
		if ($canedit) {
			echo "<th>&nbsp;</th>";
		}
		echo "<th>".$LANG['common'][17]."</th>";
		echo "<th>".$LANG['common'][16]."</th>";
		if (isMultiEntitiesMode())
			echo "<th>".$LANG['entity'][0]."</th>";
		echo "<th>".$LANG['common'][19]."</th>";
		echo "<th>".$LANG['common'][20]."</th>";
		echo "</tr>";

		$ci=new CommonItem();
		while ($i < $number) {
		
			$type=$DB->result($result, $i, "device_type");
			if (haveTypeRight($type,"r")){
				$column="name";

				$query = "SELECT `".$LINK_ID_TABLE[$type]."`.*, `glpi_plugin_simcard_device`.`ID` AS IDD, `glpi_entities`.`ID` AS entity "
				." FROM `glpi_plugin_simcard_device`, `".$LINK_ID_TABLE[$type]
				."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`ID` = `".$LINK_ID_TABLE[$type]."`.`FK_entities`) "
				." WHERE `".$LINK_ID_TABLE[$type]."`.`ID` = `glpi_plugin_simcard_device`.`FK_device`
				AND `glpi_plugin_simcard_device`.`device_type` = '$type'
				AND `glpi_plugin_simcard_device`.`FK_simcard` = '$instID' "
				. getEntitiesRestrictRequest(" AND ",$LINK_ID_TABLE[$type],'','',isset($CFG_GLPI["recursive_type"][$type]));

				if (in_array($LINK_ID_TABLE[$type],$CFG_GLPI["template_tables"])){
					$query.=" AND ".$LINK_ID_TABLE[$type].".is_template='0'";
				}
				$query.=" ORDER BY `glpi_entities`.`completename`, `".$LINK_ID_TABLE[$type]."`.`$column` ";

				if ($result_linked=$DB->query($query))
					if ($DB->numrows($result_linked)){
						initNavigateListItems($type,$LANG['plugin_simcard'][0]." = ".$PluginSimcard->fields['ID_sim1']);
						$ci->setType($type);
						while ($data=$DB->fetch_assoc($result_linked)){
							addToNavigateListItems($type,$data["ID"]);
							$ID="";

							if($_SESSION["glpiview_ID"]||empty($data["ID_sim1"])) $ID= " (".$data["ID"].")";
							$name= "<a href=\"".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[$type]."?ID=".$data["ID"]."\">"
								.$data["name"]."$ID</a>";

							echo "<tr class='tab_bg_1'>";

							if ($canedit){
								echo "<td width='10'>";
								$sel="";
								if (isset($_GET["select"])&&$_GET["select"]=="all") $sel="checked";
								echo "<input type='checkbox' name='item[".$data["IDD"]."]' value='1' $sel>";
								echo "</td>";
							}
							echo "<td class='center'>".$ci->getType()."</td>";

							echo "<td class='center' ".(isset($data['deleted'])&&$data['deleted']?"class='tab_bg_2_2'":"").">".$name."</td>";
							if (isMultiEntitiesMode()){
								echo "<td class='center'>".getDropdownName("glpi_entities",$data['entity'])."</td>";
							}
							echo "<td class='center'>".(isset($data["serial"])? "".$data["serial"]."" :"-")."</td>";
							echo "<td class='center'>".(isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";

							echo "</tr>";
						}
					}
			}
			$i++;
			
		
		}

		if ($canedit)	{
			
			echo "<tr class='tab_bg_1'><td colspan='".(3+$colsup)."' class='center'>";
			echo "<input type='hidden' name='conID' value='$instID'>";
			dropdownAllItems("item",0,0,($PluginSimcard->fields['recursive']?-1:$PluginSimcard->fields['FK_entities']),plugin_simcard_getTypes());
			echo "</td>";
			echo "<td colspan='2' class='center' class='tab_bg_2'>";
			echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\" class='submit'>";
			echo "</td></tr>";
			echo "</table></div>" ;

			echo "<div class='center'>";
			echo "<table width='80%' class='tab_glpi'>";
			echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td class='center'><a onclick= \"if ( markCheckboxes('simcard_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$instID&amp;select=all'>".$LANG['buttons'][18]."</a></td>";

			echo "<td>/</td><td class='center'><a onclick= \"if ( unMarkCheckboxes('simcard_form$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?ID=$instID&amp;select=none'>".$LANG['buttons'][19]."</a>";
			echo "</td><td align='left' width='80%'>";
			echo "<input type='submit' name='deleteitem' value=\"".$LANG['buttons'][6]."\" class='submit'>";
			echo "</td>";
			echo "</table>";

			echo "</div>";
			

		}else{

			echo "</table></div>";
		}
		echo "</form>";
	}

}

function plugin_simcard_showTickets($ID) {
	global $DB, $LANG, $LINK_ID_TABLE, $INFOFORM_PAGES;

	$PluginSimcard=new PluginSimcard();
	if ($PluginSimcard->getFromDB($ID)){
		echo "<div class='center'><br><table class='tab_cadre_fixe'>";
		echo "<tr><th colspan='10'>".$LANG['plugin_simcard'][5].":</th></tr>\n";
		commonTrackingListHeader(HTML_OUTPUT,$_SERVER['PHP_SELF'],"ID=$ID","","",true);
		initNavigateListItems(TRACKING_TYPE,$LANG['plugin_simcard'][0]." = ".$PluginSimcard->fields['ID']);

		$sql = "SELECT DISTINCT device_type FROM glpi_plugin_simcard_device	WHERE FK_simcard = '$ID'";
		$nb=0;
		foreach ($DB->request($sql) as $data) {
			$type=$data['device_type'];
			if (!haveTypeRight($type,"r")) {
				continue;
			}
			if ($type==TRACKING_TYPE) {
				continue;
			}
			$table=$LINK_ID_TABLE[$type];
			$sql = "SELECT ".getCommonSelectForTrackingSearch().
				" FROM `glpi_tracking`
				LEFT JOIN `$table` ON (`glpi_tracking`.`computer` = `$table`.`ID`)".
				getCommonLeftJoinForTrackingSearch().
				"WHERE `glpi_tracking`.`device_type` = '$type'
					AND `glpi_tracking`.`computer` IN
						(SELECT DISTINCT `FK_device` FROM `glpi_plugin_simcard_device`
						 WHERE `device_type` = '$type' AND `FK_simcard` = '$ID')
					AND `glpi_tracking`.`status` IN ('new','assign','plan','waiting')".
					getEntitiesRestrictRequest(" AND ",'glpi_tracking');

			foreach ($DB->request($sql) AS $data) {
				addToNavigateListItems(TRACKING_TYPE,$data["ID"]);
				showJobShort($data, 0);
				$nb++;
			} // each ticket
		} // each type

		if (!$nb) {
			echo "<tr class='tab_bg_1'><td colspan='10' class='center'>".$LANG['joblist'][8]."</td></tr>\n";
		}
		echo "</table></div>";
	}
}

//show simcard linking from glpi items
function plugin_simcard_showAssociated($device_type,$ID,$withtemplate=''){

	GLOBAL $DB,$CFG_GLPI, $LANG;

	$ci=new CommonItem();
	$ci->getFromDB($device_type,$ID);
	$canread=$ci->obj->can($ID,'r');
	$canedit=$ci->obj->can($ID,'w');

	$query = "SELECT `glpi_plugin_simcard_device`.`ID` AS entID,`glpi_plugin_simcard`.* "
			." FROM `glpi_plugin_simcard_device`,`glpi_plugin_simcard` "
			." LEFT JOIN `glpi_entities` ON (`glpi_entities`.`ID` = `glpi_plugin_simcard`.`FK_entities`) "
			." WHERE `glpi_plugin_simcard_device`.`FK_device` = '".$ID."'
			AND `glpi_plugin_simcard_device`.`device_type` = '".$device_type."'
			AND `glpi_plugin_simcard_device`.`FK_simcard`=`glpi_plugin_simcard`.`ID` "
			. getEntitiesRestrictRequest(" AND ","glpi_plugin_simcard",'','',isset($CFG_GLPI["recursive_type"][PLUGIN_SIMCARD_TYPE]));
	$query.= " ORDER BY `glpi_plugin_simcard`.`ID_sim1` ";

	$result = $DB->query($query);
	$number = $DB->numrows($result);

	if ($withtemplate!=2) echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/simcard/front/plugin_simcard.form.php\">";

	if (isMultiEntitiesMode()) {
		$colsup=1;
	}else {
		$colsup=0;
	}

	if($number>0){
		echo "<div align='center'><table class='tab_cadre_fixe'>";
		echo "<tr><th colspan='".(7+$colsup)."'>".$LANG['plugin_simcard'][21].":</th></tr>";
		
		echo "<tr><th>".$LANG['plugin_simcard'][1]."</th>";
		echo "<th>".$LANG['plugin_simcard'][3]."</th>";
		$plugin = new Plugin;
		if ($plugin->isInstalled("tlflines") && $plugin->isActivated("tlflines")) {
			echo "<th>".$LANG['plugin_simcard'][7]."</th>";
		}
		echo "<th>".$LANG['plugin_simcard'][5]."</th>";
		if ($plugin->isInstalled("tlflines") && $plugin->isActivated("tlflines")) {
			echo "<th>".$LANG['plugin_simcard'][8]."</th>";
		}
		//echo "<th>".$LANG['plugin_simcard'][4]."</th>";
		echo "<th>".$LANG['plugin_simcard']['setup'][11]."</th>";
	}
	


	if(plugin_simcard_haveRight("simcard","w"))
		if ($withtemplate!=2) echo "<th>&nbsp;</th>";

	echo "</tr>";
		
	$used=array();
	while ($data=$DB->fetch_array($result)){
		$simcardID=$data["ID"];
		$used[]=$simcardID;
		echo "<tr class='tab_bg_1".($data["deleted"]=='1'?"_2":"")."'>";

		//Sim 1		
		if ($withtemplate!=3 && $canread && (in_array($data['FK_entities'],$_SESSION['glpiactiveentities']) || $data["recursive"])){
			echo "<td class='center'><a href='".$CFG_GLPI["root_doc"]."/plugins/simcard/front/plugin_simcard.form.php?ID=".$data["ID"]."'>".$data["ID_sim1"];
			if ($_SESSION["glpiview_ID"]) echo " (".$data["ID_sim1"].")";
			echo "</a></td>";
		} else {
			echo "<td class='center'>".$data["name"];
			if ($_SESSION["glpiview_ID"]) echo " (".$data["ID_sim1"].")";
			echo "</td>";
		}
		
		//pin1
		echo "<td>".$data["pin1"]."</td>";
		
		//linea 1
		if ($plugin->isInstalled("tlflines") && $plugin->isActivated("tlflines")) {
			echo "<td align='center'>".getdropdownname("glpi_plugin_tlflines",$data["FK_line_sim_1"])."</td>";
		}
		
		
		//pin2
		echo "<td>".$data["pin2"]."</td>";
		
		//linea 1
		if ($plugin->isInstalled("tlflines") && $plugin->isActivated("tlflines")) {
			echo "<td align='center'>".getdropdownname("glpi_plugin_tlflines",$data["FK_line_sim_2"])."</td>";
		}
		
		//puk
		//echo "<td>".$data["puk"]."</td>";
		
		//tipo sim
		echo "<td>".getDropdownName("glpi_dropdown_plugin_simcard_types",$data["type"])."</td>";
		

		if(plugin_simcard_haveRight("simcard","w"))
			echo "<td><a href='".$CFG_GLPI["root_doc"]."/plugins/simcard/front/plugin_simcard.form.php?deletesimcard=deletesimcard&amp;ID=".$data["entID"]."'><b>".$LANG['buttons'][6]."</b></a></td>";
		//echo "</tr>";
		
	

	}

	if ($canedit){

		$ci=new CommonItem();
		$entities="";
		if ($ci->getFromDB($device_type,$ID) && isset($ci->obj->fields["FK_entities"])) {

			if (isset($ci->obj->fields["recursive"]) && $ci->obj->fields["recursive"]) {
				$entities = getEntitySons($ci->obj->fields["FK_entities"]);
			} else {
				$entities = $ci->obj->fields["FK_entities"];
			}
		}
		$limit = getEntitiesRestrictRequest(" AND ","glpi_plugin_simcard",'',$entities,true);

		$q="SELECT count(*)
			FROM `glpi_plugin_simcard`
			WHERE `deleted` = '0' $limit";
		$result = $DB->query($q);
		$nb = $DB->result($result,0,0);

		if ($withtemplate<2&&$nb>count($used)){
			if(plugin_simcard_haveRight("simcard","w")){
				echo "<tr class='tab_bg_1'><td align='right' colspan='".(6+$colsup)."'>";
				echo "<input type='hidden' name='item' value='$ID'><input type='hidden' name='type' value='$device_type'>";
				plugin_simcard_dropdownsimcard("conID",$entities,$used);
				echo "</td><td align='center'>";
				echo "<input type='submit' name='additem' value=\"".$LANG['buttons'][8]."\" class='submit'>";
				echo "</td>";

				echo "</tr>";
			}
		}
	}
	if ($canedit)
		echo "</tr>";
		echo "<tr class='tab_bg_1'><td colspan='".(7+$colsup)."' class='right'><a href='".$CFG_GLPI["root_doc"]."/plugins/simcard/front/plugin_simcard.form.php'>".$LANG['plugin_simcard'][9]."</a></td></tr>";
	echo "</table></div>";
	echo "</form>";
}

//show simcard linking from glpi enterprises
function plugin_simcard_showenterpriseAssociated($device_type,$ID,$withtemplate=''){

	GLOBAL $DB,$CFG_GLPI, $LANG;

	$ci=new CommonItem();
	$ci->getFromDB($device_type,$ID);
	$canread=$ci->obj->can($ID,'r');
	$canedit=$ci->obj->can($ID,'w');

	$query = "SELECT `glpi_plugin_simcard`.* "
			."FROM `glpi_plugin_simcard` "
			." LEFT JOIN `glpi_entities` ON (`glpi_entities`.`ID` = `glpi_plugin_simcard`.`FK_entities`) "
			." WHERE `FK_enterprise` = '$ID' "
			. getEntitiesRestrictRequest(" AND ","glpi_plugin_simcard",'','',isset($CFG_GLPI["recursive_type"][PLUGIN_SIMCARD_TYPE]));
	$query.= " ORDER BY `glpi_plugin_simcard`.`ID` ";

	$result = $DB->query($query);
	$number = $DB->numrows($result);

	if ($withtemplate!=2) echo "<form method='post' action=\"".$CFG_GLPI["root_doc"]."/plugins/simcard/front/plugin_simcard.form.php\">";

	if (isMultiEntitiesMode()) {
		$colsup=1;
	}else {
		$colsup=0;
	}

	echo "<div align='center'><table class='tab_cadre_fixe'>";
	echo "<tr><th colspan='".(12+$colsup)."'>".$LANG['plugin_simcard'][21].":</th></tr>";
	echo "<tr><th>".$LANG['plugin_simcard'][1]."</th>";
	echo "<th>".$LANG['plugin_simcard'][2]."</th>";
//	if (isMultiEntitiesMode())
//		echo "<th>".$LANG['entity'][0]."</th>";
	
	echo "<th>".$LANG['plugin_simcard']['setup'][11]."</th>";
	echo "<th>".$LANG['plugin_simcard'][6]."</th>";

	echo "</tr>";

	while ($data=$DB->fetch_array($result)){

		echo "<tr class='tab_bg_1".($data["deleted"]=='1'?"_2":"")."'>";
		if ($withtemplate!=3 && $canread && (in_array($data['FK_entities'],$_SESSION['glpiactiveentities']) || $data["recursive"])){
			echo "<td class='center'><a href='".$CFG_GLPI["root_doc"]."/plugins/simcard/front/plugin_simcard.form.php?ID=".$data["ID"]."'>".$data["ID_sim1"];
			if ($_SESSION["glpiview_ID"]) echo " (".$data["ID"].")";
			echo "</a></td>";
		} else {
			echo "<td class='center'>".$data["ID_sim1"];
			if ($_SESSION["glpiview_ID"]) echo " (".$data["ID"].")";
			echo "</td>";
		}
		echo "</a></td>";
		
		
		if ($withtemplate!=3 && $canread && (in_array($data['FK_entities'],$_SESSION['glpiactiveentities']) || $data["recursive"])){
			echo "<td class='center'><a href='".$CFG_GLPI["root_doc"]."/plugins/simcard/front/plugin_simcard.form.php?ID=".$data["ID"]."'>".$data["ID_sim2"];
			if ($_SESSION["glpiview_ID"]) echo " (".$data["ID"].")";
			echo "</a></td>";
		} else {
			echo "<td class='center'>".$data["ID_sim1"];
			if ($_SESSION["glpiview_ID"]) echo " (".$data["ID"].")";
			echo "</td>";
		}
		echo "</a></td>";
//		if (isMultiEntitiesMode())
//			echo "<td class='center'>".getDropdownName("glpi_entities",$data['FK_entities'])."</td>";


		echo "<td>".getDropdownName("glpi_dropdown_plugin_simcard_types",$data["type"])."</td>";


		echo "<td>".$data["comment"]."</td></tr>";

	}
	echo "</table></div>";
	echo "</form>";
}

/**
 * Show for PDF an simcard
 *
 * @param $pdf object for the output
 * @param $ID of the simcard
 */
function plugin_simcard_main_PDF ($pdf, $ID) {
	global $LANG, $DB;

	$item=new PluginSimcard();
	if (!$item->getFromDB($ID)) return false;

	$pdf->setColumnsSize(50,50);
	$col1 = '<b>'.$LANG["common"][2].' '.$item->fields['ID'].'</b>';
	if (isset($item->fields["date_mod"])) {
		$col2 = $LANG["common"][26].' : '.convDateTime($item->fields["date_mod"]);
	} else {
		$col2 = '';
	}
	$pdf->displayTitle($col1, $col2);

	$pdf->displayLine(
		'<b><i>'.$LANG['plugin_simcard'][0].' :</i></b> '.$item->fields['name'],
		'<b><i>'.$LANG['plugin_simcard']['setup'][1].' :</i></b> '.html_clean(getDropdownName('glpi_dropdown_plugin_simcard_type',$item->fields['type'])));
	$pdf->displayLine(
		'<b><i>'.$LANG['plugin_simcard'][3].' :</i></b> '.getUserName($item->fields['FK_users']),
		'<b><i>'.$LANG["common"][35].' :</i></b> '.html_clean(getDropdownName('glpi_groups',$item->fields['FK_groups'])));
	$pdf->displayLine(
		'<b><i>'.$LANG['plugin_simcard'][25].' :</i></b> '.html_clean(getDropdownName('glpi_dropdown_locations',$item->fields['location'])),
		'<b><i>'.$LANG['plugin_simcard'][14].' :</i></b> '.html_clean(getDropdownName('glpi_dropdown_plugin_simcard_server_type',$item->fields["server"])));

	$pdf->displayLine(
		'<b><i>'.$LANG['plugin_simcard'][13].' :</i></b> '.html_clean(getDropdownName('glpi_dropdown_plugin_simcard_technic',$item->fields['technic'])),
		'<b><i>'.$LANG['plugin_simcard'][12].' :</i></b> '.$item->fields['version']);

	$pdf->displayLine(
		'<b><i>'.$LANG['plugin_simcard']['setup'][14].' :</i></b> '.html_clean(getDropdownName('glpi_enterprises',$item->fields['FK_enterprise'])),
		'<b><i>'.$LANG['plugin_simcard']['setup'][28].' :</i></b> '.html_clean(getDropdownName('glpi_dropdown_manufacturer',$item->fields["FK_glpi_enterprise"])));

	$pdf->displayLine(
		'<b><i>'.$LANG['plugin_simcard'][0].' :</i></b> '.$item->fields['address'],
		'');

	$pdf->setColumnsSize(100);

	$pdf->displayText('<b><i>'.$LANG['plugin_simcard'][2].' :</i></b>', $item->fields['comment']);

	$pdf->displaySpace();
}

function plugin_simcard_showDevice_PDF($pdf, $instID) {
	global $DB,$CFG_GLPI, $LANG,$INFOFORM_PAGES,$LINK_ID_TABLE;

	if (!plugin_simcard_haveRight("simcard","r"))	return false;

	$PluginSimcard=new PluginSimcard();
	if (!$PluginSimcard->getFromDB($instID)) return false;

	$pdf->setColumnsSize(100);
	$pdf->displayTitle('<b>'.$LANG['plugin_simcard'][21].'</b>');

	$query = "SELECT DISTINCT `device_type`
				FROM `glpi_plugin_simcard_device`
				WHERE `FK_simcard` = '$instID'
				ORDER BY `device_type`";
	$result = $DB->query($query);
	$number = $DB->numrows($result);

	if (isMultiEntitiesMode()) {
		$pdf->setColumnsSize(12,27,25,18,18);
		$pdf->displayTitle(
			'<b><i>'.$LANG['common'][17],
			$LANG['common'][16],
			$LANG['entity'][0],
			$LANG['common'][19],
			$LANG['common'][20].'</i></b>'
			);
	} else {
		$pdf->setColumnsSize(25,31,22,22);
		$pdf->displayTitle(
			'<b><i>'.$LANG['common'][17],
			$LANG['common'][16],
			$LANG['common'][19],
			$LANG['common'][20].'</i></b>'
			);
	}

	$ci=new CommonItem();
	if (!$number) {
		$pdf->displayLine($LANG['search'][15]);
	} else {
		for ($i=0 ; $i < $number ; $i++) {
			$type=$DB->result($result, $i, "device_type");

			if (haveTypeRight($type,"r")){
				$column="name";
				if ($type==TRACKING_TYPE) $column="ID";
				if ($type==KNOWBASE_TYPE) $column="question";

				$query = "SELECT `".$LINK_ID_TABLE[$type]."`.*, `glpi_plugin_simcard_device`.`ID` AS IDD, `glpi_entities`.`ID` AS entity "
				." FROM `glpi_plugin_simcard_device`, `".$LINK_ID_TABLE[$type]
				."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`ID` = `".$LINK_ID_TABLE[$type]."`.`FK_entities`) "
				." WHERE `".$LINK_ID_TABLE[$type]."`.`ID` = `glpi_plugin_simcard_device`.`FK_device`
					AND `glpi_plugin_simcard_device`.`device_type` = '$type'
					AND `glpi_plugin_simcard_device`.`FK_simcard` = '$instID' "
				. getEntitiesRestrictRequest(" AND ",$LINK_ID_TABLE[$type],'','',isset($CFG_GLPI["recursive_type"][$type]));

				if (in_array($LINK_ID_TABLE[$type],$CFG_GLPI["template_tables"])){
					$query.=" AND ".$LINK_ID_TABLE[$type].".is_template='0'";
				}
				$query.=" ORDER BY `glpi_entities`.`completename`, `".$LINK_ID_TABLE[$type]."`.`$column` ";

				if ($result_linked=$DB->query($query))
					if ($DB->numrows($result_linked)){

						while ($data=$DB->fetch_assoc($result_linked)){
							if (!$ci->getFromDB($type,$data["ID"])) continue;

							$ID="";
							if ($type==TRACKING_TYPE) $data["name"]=$LANG['job'][38]." ".$data["ID"];
							if ($type==KNOWBASE_TYPE) $data["name"]=$data["question"];

							if($_SESSION["glpiview_ID"]||empty($data["name"])) $ID= " (".$data["ID"].")";
							$name = $data["name"].$ID;

							if (isMultiEntitiesMode()) {
								$pdf->setColumnsSize(12,27,25,18,18);
								$pdf->displayLine(
									$ci->getType(),
									$name,
									html_clean(getDropdownName("glpi_entities",$data['entity'])),
									(isset($data["serial"])? "".$data["serial"]."" :"-"),
									(isset($data["otherserial"])? "".$data["otherserial"]."" :"-")
									);
							} else {
								$pdf->setColumnsSize(25,31,22,22);
								$pdf->displayTitle(
									$ci->getType(),
									$name,
									(isset($data["serial"])? "".$data["serial"]."" :"-"),
									(isset($data["otherserial"])? "".$data["otherserial"]."" :"-")
									);
							}
						} // Each device
					} // numrows device
			} // type right
		} // each type
	} // numrows type
}


/**
 * show for PDF the simcard associated with a device
 *
 * @param $ID of the device
 * @param $device_type : type of the device
 *
 */
function plugin_simcard_showAssociated_PDF($pdf, $ID, $device_type){

	GLOBAL $DB,$CFG_GLPI, $LANG;

	$pdf->setColumnsSize(100);
	$pdf->displayTitle('<b>'.$LANG['plugin_simcard'][21].'</b>');

	$ci=new CommonItem();
	$ci->getFromDB($device_type,$ID);

	$query = "SELECT `glpi_plugin_simcard_device`.`ID` AS entID,`glpi_plugin_simcard`.* "
	." FROM `glpi_plugin_simcard_device`,`glpi_plugin_simcard` "
	." LEFT JOIN `glpi_entities` ON (`glpi_entities`.`ID` = `glpi_plugin_simcard`.`FK_entities`) "
	." WHERE `glpi_plugin_simcard_device`.`FK_device` = '".$ID."'
		AND `glpi_plugin_simcard_device`.`device_type` = '".$device_type."'
		AND `glpi_plugin_simcard_device`.`FK_simcard` = `glpi_plugin_simcard`.`ID` "
	. getEntitiesRestrictRequest(" AND ","glpi_plugin_simcard",'','',isset($CFG_GLPI["recursive_type"][PLUGIN_SIMCARD_TYPE]));

	$result = $DB->query($query);
	$number = $DB->numrows($result);

	if (!$number) {
		$pdf->displayLine($LANG['search'][15]);
	} else {
		if (isMultiEntitiesMode()) {
			$pdf->setColumnsSize(25,25,15,15,20);
			$pdf->displayTitle(
				'<b><i>'.$LANG['plugin_simcard'][0],
				$LANG['entity'][0],
				$LANG['plugin_simcard'][3],
				$LANG['common'][35],
				$LANG['plugin_simcard']['setup'][1].'</i></b>'
				);
		} else {
			$pdf->setColumnsSize(30,30,20,20);
			$pdf->displayTitle(
				'<b><i>'.$LANG['plugin_simcard'][0],
				$LANG['plugin_simcard'][3],
				$LANG['common'][35],
				$LANG['plugin_simcard']['setup'][1].'</i></b>'
				);
		}
		while ($data=$DB->fetch_array($result)){
			$simcardID=$data["ID"];

			if (isMultiEntitiesMode()) {
				$pdf->setColumnsSize(25,25,15,15,20);
				$pdf->displayLine(
					$data["name"],
					html_clean(getDropdownName("glpi_entities",$data['FK_entities'])),
					html_clean(getUsername("glpi_users",$data["FK_users"])),
					html_clean(getDropdownName("glpi_groups",$data["FK_groups"])),
					html_clean(getDropdownName("glpi_dropdown_plugin_simcard_type",$data["type"]))
					);
			} else {
				$pdf->setColumnsSize(50,25,25);
				$pdf->displayLine(
					$data["name"],
					html_clean(getUsername("glpi_users",$data["FK_users"])),
					html_clean(getDropdownName("glpi_groups",$data["FK_groups"])),
					html_clean(getDropdownName("glpi_dropdown_plugin_simcard_type",$data["type"]))
					);
			}
		}
	}

}

?>
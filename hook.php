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
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
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

function plugin_simcards_install() {
   global $DB;
   
   include_once (GLPI_ROOT."/plugins/simcards/inc/profile.class.php");
   
   $update=false;
   if (!TableExists("glpi_plugin_simcards") && !TableExists("glpi_plugin_simcards_simcards")) {
      
      $DB->runFile(GLPI_ROOT ."/plugins/simcards/sql/empty-1.1.0.sql");

   } else if (TableExists("glpi_plugin_simcard")) {
      
      $update=true;
      $DB->runFile(GLPI_ROOT ."/plugins/simcards/sql/update-1.0.0_1.1.0.sql");
     

   }
   
//   if ($update) {
//      
//      $query_="SELECT *
//            FROM `glpi_plugin_simcards_profiles` ";
//      $result_=$DB->query($query_);
//      if ($DB->numrows($result_)>0) {
//
//         while ($data=$DB->fetch_array($result_)) {
//            $query="UPDATE `glpi_plugin_simcards_profiles`
//                  SET `profiles_id` = '".$data["id"]."'
//                  WHERE `id` = '".$data["id"]."';";
//            $result=$DB->query($query);
//
//         }
//      }
//      
//      $query="ALTER TABLE `glpi_plugin_simcards_profiles`
//               DROP `name` ;";
//      $result=$DB->query($query);
//      
//      Plugin::migrateItemType(
//         array(5745=>'PluginSimcardsSimcard'),
//         array("glpi_bookmarks", "glpi_bookmarks_users", "glpi_displaypreferences",
//               "glpi_documents_items", "glpi_infocoms", "glpi_logs", "glpi_tickets"),
//         array("glpi_plugin_simcards_simcards_items"));
//      
//      Plugin::migrateItemType(
//         array(1200 => "PluginAppliancesAppliance"),
//         array("glpi_plugin_simcards_simcards_items"));
//	}
 
   PluginSimcardsProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}

function plugin_simcards_uninstall() {
	global $DB;

	$tables = array("glpi_plugin_simcards_simcards",
					"glpi_plugin_simcards_simcardstypes",
					"glpi_plugin_simcards_simcards_items",
					"glpi_plugin_simcards_profiles");

   foreach($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");
   
   //old versions	
   $tables = array("glpi_plugin_simcard",
					"glpi_dropdown_plugin_simcard_types",	
					"glpi_plugin_simcard_device",
					"glpi_plugin_simcard_profiles");

	foreach($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");
		
   $tables_glpi = array("glpi_displaypreferences",
					"glpi_documents_items",
					"glpi_bookmarks",
					"glpi_logs");

	foreach($tables_glpi as $table_glpi)
		$DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` = 'PluginSimcardsSimcard';");

	if (class_exists('PluginDatainjectionModel')) {
      PluginDatainjectionModel::clean(array('itemtype'=>'PluginSimcardsSimcard'));
   }

	return true;
}

// Define dropdown relations
function plugin_simcards_getDatabaseRelations() {

	$plugin = new Plugin();

	if ($plugin->isActivated("simcards"))
		return array(
					"glpi_plugin_simcards_simcardstypes"=>array("glpi_plugin_simcards_simcards"=>"plugin_simcards_simcardstypes_id"),
					"glpi_users"=>array("glpi_plugin_simcards_simcards"=>"users_id"),
					"glpi_groups"=>array("glpi_plugin_simcards_simcards"=>"groups_id"),
					"glpi_suppliers"=>array("glpi_plugin_simcards_simcards"=>"suppliers_id"),
					"glpi_manufacturers"=>array("glpi_plugin_simcards_simcards"=>"manufacturers_id"),
					"glpi_plugin_simcards_simcards"=>array("glpi_plugin_simcards_simcards"=>"locations_id"),
					"glpi_locations"=>array("glpi_plugin_simcards_simcards_items"=>"plugin_simcards_simcards_id"),
					"glpi_profiles" => array ("glpi_plugin_simcards_profiles" => "profiles_id"));
	else
		return array();
}

// Define Dropdown tables to be manage in GLPI :
function plugin_simcards_getDropdown() {
	global $LANG;

	$plugin = new Plugin();

	if ($plugin->isActivated("simcards"))
		return array('PluginSimcardsSimcardsTypes'=>$LANG['plugin_simcards'][16],
					'$PluginSimcardsSimcardsLine1'=>$LANG['plugin_simcards'][8],
					'$PluginSimcardsSimcardsLine2'=>$LANG['plugin_simcards'][9]);
	else
		return array();
}

function plugin_simcards_AssignToTicket($types) {
	global $LANG;

	if (plugin_simcards_haveRight("open_ticket","1"))
		$types['PluginSimcardsSimcard']=$LANG['plugin_simcards'][4];

	return $types;
}

////// SEARCH FUNCTIONS ///////() {

function plugin_simcards_getAddSearchOptions($itemtype) {
	global $LANG;
    
   $sopt=array();

   if (in_array($itemtype, PluginSimcardsSimcard_Item::getClasses(true))) {
    
		$sopt[1310]['table']='glpi_plugin_simcards_simcards';
		$sopt[1310]['field']='name';
		$sopt[1310]['linkfield']='';
		$sopt[1310]['name']=$LANG['plugin_simcards'][4]." - ".$LANG['plugin_simcards'][0];
		$sopt[1310]['forcegroupby']='1';
		$sopt[1310]['datatype']='itemlink';
		$sopt[1310]['itemlink_type']='PluginSimcardsSimcard';

		$sopt[1311]['table']='glpi_plugin_simcards_simcardtypes';
		$sopt[1311]['field']='name';
		$sopt[1311]['linkfield']='';
		$sopt[1311]['name']=$LANG['plugin_simcards'][4]." - ".$LANG['plugin_simcards']['setup'][1];
		$sopt[1311]['forcegroupby']='1';
	}
	
	return $sopt;
}

//for search
function plugin_simcards_addLeftJoin($type,$ref_table,$new_table,$linkfield,&$already_link_tables) {

	switch ($new_table) {

		case "glpi_plugin_simcards_simcards_items" : //from simcards list
			return " LEFT JOIN `$new_table` ON (`$ref_table`.`id` = `$new_table`.`plugin_simcards_simcards_id`) ";
			break;
		case "glpi_plugin_simcards_simcards" : // From items
			$out= " LEFT JOIN `glpi_plugin_simcards_simcards_items` ON (`$ref_table`.`id` = `glpi_plugin_simcards_simcards_items`.`items_id` AND `glpi_plugin_simcards_simcards_items`.`itemtype`= '$type') ";
			$out.= " LEFT JOIN `glpi_plugin_simcards_simcards` ON (`glpi_plugin_simcards_simcards`.`id` = `glpi_plugin_simcards_simcards_items`.`plugin_simcards_simcards_id`) ";
			return $out;
			break;
		case "glpi_plugin_simcards_simcardtypes" : // From items
			$out=Search::addLeftJoin($type,$ref_table,$already_link_tables,"glpi_plugin_simcards_simcards",$linkfield);
			$out.= " LEFT JOIN `glpi_plugin_simcards_simcardtypes` ON (`glpi_plugin_simcards_simcardtypes`.`id` = `glpi_plugin_simcards_simcards`.`plugin_simcards_simcardtypes_id`) ";
			return $out;
			break;
	}

	return "";
}

//force groupby for multible links to items
function plugin_simcards_forceGroupBy($type) {

	return true;
	switch ($type) {
		case 'PluginSimcardsSimcard':
			return true;
			break;

	}
	return false;
}

//display custom fields in the search
function plugin_simcards_giveItem($type,$ID,$data,$num) {
	global $CFG_GLPI,$LANG,$DB;

	$searchopt=&Search::getOptions($type);
	$table=$searchopt[$ID]["table"];
	$field=$searchopt[$ID]["field"];

	switch ($table.'.'.$field) {
		//display associated items with simcards
		case "glpi_plugin_simcards_simcards_items.items_id" :
			$query_device = "SELECT DISTINCT `itemtype`
							FROM `glpi_plugin_simcards_simcards_items`
							WHERE `plugin_simcards_simcards_id` = '".$data['id']."'
							ORDER BY `itemtype`";
			$result_device = $DB->query($query_device);
			$number_device = $DB->numrows($result_device);
			$out='';
			$simcards=$data['id'];
			if ($number_device > 0) {
				for ($i=0 ; $i < $number_device ; $i++) {
					$column = "name";
					$itemtype = $DB->result($result_device, $i, "itemtype");
					
					if (!class_exists($itemtype)) {
                  continue;
               }
               $item = new $itemtype();
					if ($item->canView()) {
                  $table_item = getTableForItemType($itemtype);

                  if ($itemtype!='Entity') {
                     $query = "SELECT `".$table_item."`.*, `glpi_plugin_simcards_simcards_items`.`id` AS table_items_id, `glpi_entities`.`id` AS entity "
              ." FROM `glpi_plugin_simcards_simcards_items`, `".$table_item
              ."` LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` = `".$table_item."`.`entities_id`) "
              ." WHERE `".$table_item."`.`id` = `glpi_plugin_simcards_simcards_items`.`items_id`
              AND `glpi_plugin_simcards_simcards_items`.`itemtype` = '$itemtype'
              AND `glpi_plugin_simcards_simcards_items`.`plugin_simcards_simcards_id` = '".$simcards."' "
              . getEntitiesRestrictRequest(" AND ",$table_item,'','',$item->maybeRecursive());

                     if ($item->maybeTemplate()) {
                        $query.=" AND ".$table_item.".is_template='0'";
                     }
                     $query.=" ORDER BY `glpi_entities`.`completename`, `".$table_item."`.`$column` ";
                  } else {
                     $query = "SELECT `".$table_item."`.*, `glpi_plugin_simcards_simcards_items`.`id` AS table_items_id, `glpi_entities`.`id` AS entity "
              ." FROM `glpi_plugin_simcards_simcards_items`, `".$table_item
              ."` WHERE `".$table_item."`.`id` = `glpi_plugin_simcards_simcards_items`.`items_id`
              AND `glpi_plugin_simcards_simcards_items`.`itemtype` = '$itemtype'
              AND `glpi_plugin_simcards_simcards_items`.`plugin_simcards_simcards_id` = '".$simcards."' "
              . getEntitiesRestrictRequest(" AND ",$table_item,'','',$item->maybeRecursive());

                     if ($item->maybeTemplate()) {
                        $query.=" AND ".$table_item.".is_template='0'";
                     }
                     $query.=" ORDER BY `glpi_entities`.`completename`, `".$table_item."`.`$column` ";
                  }
        
               if ($result_linked=$DB->query($query))
                  if ($DB->numrows($result_linked)) {
                     $item = new $itemtype();
                     while ($data=$DB->fetch_assoc($result_linked)) {
                        if ($item->getFromDB($data['id'])) {
                           $out .= $item->getTypeName()." - ".$item->getLink()."<br>";
                        }
                     }
                  } else
                     $out.=' ';
               } else
                  $out.=' ';
				}
			}
		return $out;
		break;
	}
	return "";
}


////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_simcards_MassiveActions($type) {
	global $LANG;
  
	switch ($type) {
		case 'PluginSimcardsSimcard':
			return array(
				// association with glpi items
				"plugin_simcards_install"=>$LANG['plugin_simcards']['setup'][23],
				"plugin_simcards_desinstall"=>$LANG['plugin_simcards']['setup'][24],
				//tranfer simcards to another entity
				"plugin_simcards_transfert"=>$LANG['buttons'][48],
				);
			break;
		default:
			//adding simcards from items lists
			if (in_array($type, PluginSimcardsSimcard_Item::getClasses(true))) {
				return array("plugin_simcards_add_item"=>$LANG['plugin_simcards']['setup'][25]);
			}
			break;
	}
	return array();
}

function plugin_simcards_MassiveActionsDisplay($options=array()) {
	global $LANG,$CFG_GLPI;
  
   $PluginSimcardsSimcard=new PluginSimcardsSimcard();
  
	switch ($options['itemtype']) {
		case 'PluginSimcardsSimcard':
			
			switch ($options['action']) {
				// No case for add_document : use GLPI core one
				case "plugin_simcards_install":
					Dropdown::showAllItems("item_item",0,0,-1,PluginSimcardsSimcard_Item::getClasses(true));
					echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
				break;
				case "plugin_simcards_desinstall":
					Dropdown::showAllItems("item_item",0,0,-1,PluginSimcardsSimcard_Item::getClasses(true));
				echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
				break;
				case "plugin_simcards_transfert":
               Dropdown::show('Entity');
				echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
				break;
			}
		break;
	}
	if (in_array($options['itemtype'], PluginSimcardsSimcard_Item::getClasses(true))) {
      $PluginSimcardsSimcard->dropdownSimcards("plugin_simcards_simcards_id");
      echo "<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
   }
	return "";
}

function plugin_simcards_MassiveActionsProcess($data) {
	global $LANG,$DB;
   
   $PluginSimcardsSimcard = new PluginSimcardsSimcard;
   $PluginSimcardsSimcard_Item = new PluginSimcardsSimcard_Item();
  
   $PluginSimcardsSimcardsTypes = new PluginSimcardsSimcardsTypes();
			

   
	switch ($data['action']) {

		case "plugin_simcards_add_item":
			foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               $input = array('plugin_simcards_simcards_id' => $data['plugin_simcards_simcards_id'],
                              'items_id'      => $key,
                              'itemtype'      => $data['itemtype']);
               if ($PluginSimcardsSimcard_Item->can(-1,'w',$input)) {
                  $PluginSimcardsSimcard_Item->add($input);
               }
            }
         }
         break;
		case "plugin_simcards_install":
			foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               $input = array('plugin_simcards_simcards_id' => $key,
                              'items_id'      => $data["item_item"],
                              'itemtype'      => $data['itemtype']);
               if ($PluginSimcardsSimcard_Item->can(-1,'w',$input)) {
                  $PluginSimcardsSimcard_Item->add($input);
               }
            }
         }
         break;
		case "plugin_simcards_desinstall":
          foreach ($data["item"] as $key => $val) {
           if ($val == 1) {
               $PluginSimcardsSimcard_Item->deleteItemBySimcardsAndItem($key,$data['item_item'],$data['itemtype']);
            }
         }	
         break;
		case "plugin_simcards_transfert":
         if ($data['itemtype'] == 'PluginSimcardsSimcard') {
				foreach ($data["item"] as $key => $val) {
					if ($val == 1) {
                  $PluginSimcardsSimcard->getFromDB($key);

                  $type = PluginSimcardsSimcardsTypes::transfer($PluginSimcardsSimcard->fields["plugin_simcards_simcardsdataplans_id"],$data['entities_id']);
                  $values["id"] = $key;
                  $values["plugin_simcards_simcardsdataplans_id"] = $type;
                  $values["entities_id"] = $data['entities_id'];
                  $PluginSimcardsSimcard->update($values);
					}
				}
			}
         break;
	}
}

// Hook done on purge item case
function plugin_item_purge_simcards($item) {
  
   $temp = new PluginSimcardsSimcard_Item();
   $temp->clean(array('itemtype' => get_class($item),
                   'items_id' => $item->getField('id')));
   return true;
}

// Define headings added by the plugin
function plugin_get_headings_simcards($item,$withtemplate) {
	global $LANG;

	//DEP
	if (in_array(get_class($item),PluginSimcardsSimcard_Item::getClasses(true))||
		get_class($item)=='Profile'||
		get_class($item)=='Supplier') {
		// template case
		if ($item->getField('id') && !$withtemplate) {
				return array(
					1 => $LANG['plugin_simcards'][4],
					);
		}
	}

	return false;
}

// Define headings actions added by the plugin
function plugin_headings_actions_simcards($item) {
  
	if (in_array(get_class($item),PluginSimcardsSimcard_Item::getClasses(true))||
		get_class($item)=='Profile'||
		get_class($item)=='Supplier') {
		return array(
			1 => "plugin_headings_simcards",
		);
		
	}
	return false;

}

// action heading
function plugin_headings_simcards($item,$withtemplate=0) {
	global $CFG_GLPI;
  
   $PluginSimcardsProfile=new PluginSimcardsProfile();
   $PluginSimcardsSimcard_Item=new PluginSimcardsSimcard_Item();
  
	switch (get_class($item)) {

      case 'Profile' :
         if (!$PluginSimcardsProfile->getFromDBByProfile($item->getField('id')))
            $PluginSimcardsProfile->createAccess($item->getField('id'));
         $PluginSimcardsProfile->showForm($item->getField('id'), array('target' => $CFG_GLPI["root_doc"]."/plugins/simcards/front/profile.form.php"));
         break;
      case 'Supplier' :
         $PluginSimcardsSimcard_Item->showPluginFromSupplier('Supplier',$item->getField('id'));
         break;
     
      default :
         if (in_array(get_class($item), PluginSimcardsSimcard_Item::getClasses(true))) {
            $PluginSimcardsSimcard_Item->showPluginFromItems(get_class($item),$item->getField('id'));
         }
         break;
   }
}

function plugin_datainjection_populate_simcards() {
   global $INJECTABLE_TYPES;
   $INJECTABLE_TYPES['PluginSimcardsSimcardInjection'] = 'simcards';
}

// Define PDF informations added by the plugin
function plugin_headings_actionpdf_simcards($item) {
  
	if (in_array(get_class($item),PluginSimcardsSimcard_Item::getClasses(true))) {
      return array(1 => array('PluginSimcardsSimcard_Item', 'PdfFromItems'));
	} else {
		return false;
	}
}

/**
 * Hook : options for one type
 *
 * @param $type of item
 *
 * @return array of string which describe the options
 */
function plugin_simcards_prefPDF($item) {
	global $LANG;
   
   $tabs = array();
   switch (get_class($item)) {
      case 'PluginSimcardsSimcard' :
         $item->fields['id'] = 1; // really awfull :(
         $tabs = $item->defineTabs();
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
function plugin_simcards_generatePDF($options) {

   $item   = $options['item'];
   $tab_id = $options['tab_id'];
   $tab    = $options['tab'];
   $page   = $options['page'];

   $PluginSimcardsSimcard_Item=new PluginSimcardsSimcard_Item();
	$pdf = new PluginPdfSimplePDF('a4', ($page ? 'landscape' : 'portrait'));

	$nb_id = count($tab_id);

	foreach($tab_id as $key => $ID)	{

		if (plugin_pdf_add_header($pdf,$ID,$item)) {
			$pdf->newPage();
		} else {
			// Object not found or no right to read
			continue;
		}

	switch (get_class($item)) {
		case 'PluginSimcardsSimcard':
			$item->mainPdf($pdf);

			foreach($tab as $i)	{
				switch($i) { // See plugin_applicatif::defineTabs();
					case 1:
						$PluginSimcardsSimcard_Item->ItemsPdf($pdf,$item);
						break;
					case 6:
						plugin_pdf_ticket($pdf,$item);
						plugin_pdf_oldticket($pdf,$item);
						break;
					case 8:
						plugin_pdf_contract ($pdf,$item);
						break;
					case 9:
						plugin_pdf_document($pdf,$item);
						break;
					case 10:
						plugin_pdf_note($pdf,$item);
						break;
					case 12:
						plugin_pdf_history($pdf,$item);
						break;
					default:
						plugin_pdf_pluginhook($i,$pdf,$item);
				}
			}
			break;
		} // Switch type
	} // Each ID
	$pdf->render();
}

?>
<?php
/*
 * @version $Id$
 LICENSE

  This file is part of the simcard plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Simcard. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   simcard
 @author    the simcard plugin team
 @copyright Copyright (c) 2010-2011 Simcard plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://github.com/pluginsglpi/simcard
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

// Relation between Simcard and Items (computer, phone, peripheral only)
class PluginSimcardSimcard_Item extends CommonDBRelation{

   // From CommonDBRelation
   static public $itemtype_1 = 'PluginSimcardSimcard';
   static public $items_id_1 = 'plugin_simcard_simcards_id';

   static public $itemtype_2 = 'itemtype';
   static public $items_id_2 = 'items_id';
   
   // Itemtypes simcards may be linked to
   static protected $linkableClasses = array(
         'Computer', 
         'Peripheral', 
         'Phone', 
         'Printer', 
         'NetworkEquipment'
   );

   /**
    * Name of the type
    *
    * @param $nb  integer  number of item in the type (default 0)
   **/
   static function getTypeName($nb=0) {
      global $LANG;
      return __s('Direct Connections');
   }
   
   /**
    * Check right on an item - overloaded to check is_global
    *
    * @param $ID     ID of the item (-1 if new item)
    * @param $right  Right to check : r / w / recursive
    * @param $input  array of input data (used for adding item) (default NULL)
    *
    * @return boolean
   **/
   function can($ID, $right, array &$input=NULL) {

      if ($ID<0) {
         // Ajout
         if (!($item = new $input['itemtype'])) {
            return false;
         }

         if (!$item->getFromDB($input['items_id'])) {
            return false;
         }
         if ($item->getField('is_global')==0
             && self::countForItem($ID) > 0) {
               return false;
         }
      }
      return parent::can($ID, $right, $input);
   }

   static function countForItem($id) {
   	  return countElementsInTable(getTableForItemType(__CLASS__),
               "`plugin_simcard_simcards_id`='$id'");
   }

   /**
    * Count the number of relations having the itemtype of $item
    * 
    * @param CommonDBTM $item Item whose relations to simcards shall be counted
    * @return integer count of relations between the item and simcards
    */
   static function countForItemByItemtype(CommonDBTM $item) {
      $id = $item->getField('id');
      $itemtype = $item->getType();
   	  return countElementsInTable(getTableForItemType(__CLASS__),
   		 "`items_id`='$id' AND `itemtype`='$itemtype'");
   }
    
   /**
    * Hook called After an item is uninstall or purge
    */
   static function cleanForItem(CommonDBTM $item) {
      $temp = new self();
      $temp->deleteByCriteria(
         array('itemtype' => $item->getType(),
               'items_id' => $item->getField('id')));
   }
   
   static function getClasses() {
      return self::$linkableClasses;
   }
   
   /**
    * Declare a new itemtype to be linkable to a simcard
    */
   static function registerItemtype($itemtype) {
      if (!in_array($itemtype, self::$linkableClasses)) {
         array_push(self::$linkableClasses, $itemtype);
         Plugin::registerClass('PluginSimcardSimcard_Item',
               array('addtabon' => $itemtype));
      }
   }
   
   static function install(Migration $migration) {
      global $DB;
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `items_id` int(11) NOT NULL DEFAULT '0' COMMENT 'RELATION to various table, according to itemtype (id)',
              `plugin_simcard_simcards_id` int(11) NOT NULL DEFAULT '0',
              `itemtype` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
              PRIMARY KEY (`id`),
              KEY `plugin_simcard_simcards_id` (`plugin_simcard_simcards_id`),
              KEY `item` (`itemtype`,`items_id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      }
   }
   
   /**
    * 
    *
    * @since 1.3
    **/
   static function upgrade(Migration $migration) {
      global $DB;

   }
   
   static function uninstall() {
      global $DB;
      $table = getTableForItemType(__CLASS__);
      $DB->query("DROP TABLE IF EXISTS `$table`");
   }

   static function showForSimcard(PluginSimcardSimcard $simcard) {
      global $DB, $LANG;
      
      if (!$simcard->canView()) {
         return false;
      }
      $results = getAllDatasFromTable(getTableForItemType(__CLASS__),
                                     "`plugin_simcard_simcards_id` = '".$simcard->getID()."'");
      echo "<div class='spaced'>";
      echo "<form id='items' name='items' method='post' action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr><th colspan='6'>".__("Associated item")."</th></tr>";
      if (!empty($results)) {
         echo "<tr><th></th>";
         echo "<th>".__s("Type")."</th>";
         echo "<th>".__s("Entity")."</th>";
         echo "<th>".__s("Name")."</th>";
         echo "<th>".__s("Serial number")."</th>";
         echo "<th>".__s("Inventory number")."</th>";
         echo "</tr>";
         foreach ($results as $data) {
            $item = new $data['itemtype'];
            $item->getFromDB($data['items_id']);
            echo "<tr>";
            echo "<td>";
            if (PluginSimcardSimcard::canUpdate()) {
               echo "<input type='checkbox' name='todelete[".$data['id']."]'>";
            }
            echo "</td>";
            echo "<td>";
            echo call_user_func(array($data['itemtype'], 'getTypeName'));
            echo "</td>";
            echo "<td>";
            echo Dropdown::getDropdownName('glpi_entities', $item->fields['entities_id']);
            echo "</td>";
            echo "<td>";
            echo $item->getLink();
            echo "</td>";
            echo "<td>";
            if (isset($item->fields['serial'])) {
               echo $item->fields['serial'];
            } else {
               
            }
            echo "</td>";
            echo "<td>";
            if (isset($item->fields['otherserial'])) {
               echo $item->fields['otherserial'];
            }
            echo "</td>";
            echo "</tr>";
         }
      }
      
      if (PluginSimcardsimcard::canUpdate()) {
         echo "<tr class='tab_bg_1'><td colspan='4' class='center'>";
         if (empty($results)) {
            echo "<input type='hidden' name='plugin_simcard_simcards_id' value='".$simcard->getID()."'>";
            // TODO : Dropdown::showAllItems is deprecated, use Dropdown::showSelectItemFromItemtypes instead
            Dropdown::showAllItems("items_id",0,0,$simcard->fields['entities_id'], self::getClasses());
            echo "</td>";
            echo "<td colspan='2' class='center' class='tab_bg_2'>";
            echo "<input type='submit' name='additem' value=\""._sx('button', 'Add')."\" class='submit'>";
            echo "</td></tr>";
         }
   
         if (!empty($results)) {
            Html::openArrowMassives('items', true);
            Html::closeArrowMassives(array('delete_items' => _sx('button', 'Disconnect')));
         }
      }
      echo "</table>" ;
      Html::closeForm();
      echo "</div>";
   }
   
   static function showForItem(CommonDBTM $item) {
      global $DB, $LANG;
      
      if (!$item->canView()) {
         return false;
      }
      
//       if (PluginSimcardSimcard::canCreate()) {
//       	 if ($item->canEdit($item->getField('id'))) {
// 	         $url = Toolbox::getItemTypeFormURL('PluginSimcardSimcard');
// 	         $url.= "?itemtype=".$item->getType()."&items_id=".$item->getID()."&id=-1";
// 	         echo "<div class='center'><a href='$url'>".__s('New SIM card', 'simcard')."</a></div><br>";

//       	 }
//       }
      $results = getAllDatasFromTable(getTableForItemType(__CLASS__),
                                     "`items_id` = '".$item->getID()."' AND `itemtype`='".get_class($item)."'");
      echo "<div class='spaced'>";
      echo "<form id='items' name='items' method='post' action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr><th colspan='6'>".__s('Associated item')."</th></tr>";
      if (!empty($results)) {
         echo "<tr><th></th>";
         echo "<th>".__s('Entity')."</th>";
         echo "<th>".__s('Name')."</th>";
         echo "<th>".__s('IMSI')."</th>";
         echo "<th>".__s('Inventory number')."</th>";
         echo "</tr>";
         foreach ($results as $data) {
            $tmp = new PluginSimcardSimcard();
            $tmp->getFromDB($data['plugin_simcard_simcards_id']);
            echo "<tr>";
            echo "<td>";
            if (PluginSimcardSimcard::canDelete()) {
               echo "<input type='checkbox' name='todelete[".$data['id']."]'>";
            }
            echo "</td>";
            echo "<td>";
            echo Dropdown::getDropdownName('glpi_entities', $tmp->fields['entities_id']);
            echo "</td>";
            echo "<td>";
            echo $tmp->getLink();
            echo "</td>";
            echo "<td>";
            echo $tmp->fields['serial'];
            echo "</td>";
            echo "<td>";
            echo $tmp->fields['otherserial'];
            echo "</td>";
            echo "</tr>";
         }
      }
      
      if (PluginSimcardSimcard::canUpdate()) {
         echo "<tr class='tab_bg_1'><td colspan='4' class='center'>";
         echo "<input type='hidden' name='items_id' value='".$item->getID()."'>";
         echo "<input type='hidden' name='itemtype' value='".$item->getType()."'>";
         $used = array();
         $query = "SELECT `id`
                   FROM `glpi_plugin_simcard_simcards`
                   WHERE `is_template`='0'
                      AND `id` IN (SELECT `plugin_simcard_simcards_id`
                                   FROM `glpi_plugin_simcard_simcards_items`)";
         foreach ($DB->request($query) as $use) {
            $used[] = $use['id'];
         }
         Dropdown::show('PluginSimcardSimcard',
                        array ('name' => "plugin_simcard_simcards_id",
                               'entity' => $item->fields['entities_id'], 'used' => $used));
         echo "</td>";
         echo "<td colspan='2' class='center' class='tab_bg_2'>";
         echo "<input type='submit' name='additem' value=\""._sx('button', 'Save')."\" class='submit'>";
         echo "</td></tr>";
         
         if (!empty($results)) {
            Html::openArrowMassives('items', true);
            Html::closeArrowMassives(array ('delete_items' => _sx('button', 'Disconnect')));
         }
      }
      echo "</table>" ;
      Html::closeForm();
      echo "</div>";
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $CFG_GLPI;
      
      if (PluginSimcardSimcard::canView()) {
         switch ($item->getType()) {
            case 'PluginSimcardSimcard' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry(_n('Associated item', 'Associated items', 2), self::countForSimcard($item));
               }
               return _n('Associated item', 'Associated items', 2);

            default :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry(PluginSimcardSimcard::getTypeName(2), self::countForItemByItemtype($item));
               }
               return _n('SIM card', 'SIM cards', 2);

         }
      }
      return '';
   }

   
   /**
    * 
    * Count the number of associated items for a simcard item
    * 
    * @param $item   Simcard object
    **/
   static function countForSimcard(PluginSimcardSimcard $item) {
   
      $restrict = "`glpi_plugin_simcard_simcards_items`.`plugin_simcard_simcards_id` = '".$item->getField('id')."'";
   	
      return countElementsInTable(array('glpi_plugin_simcard_simcards_items'), $restrict);
   }
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      
      if (in_array(get_class($item), PluginSimcardSimcard_Item::getClasses())) {
         self::showForItem($item);
      } elseif (get_class($item) == 'PluginSimcardSimcard') {
         self::showForSimcard($item);
      }
      return true;
   }
}
?>

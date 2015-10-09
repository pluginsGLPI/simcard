<?php

/*
 * @version $Id: simcardvoltage.class.php 36 2012-08-31 13:59:28Z walid $
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

/// Class SimcardType
class PluginSimcardSimcardType extends CommonDropdown {

   static function getTypeName($nb = 0) {
      global $LANG;
      return __s('Type of SIM card', 'simcard');
   }

   static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
           `id` int(11) NOT NULL AUTO_INCREMENT,
           `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
           `comment` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
           PRIMARY KEY (`id`),
           KEY `name` (`name`)
         ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die("Error adding table $table");
      }
   }

   /**
    * 
    *
    * @since 1.3
    * */
   static function upgrade(Migration $migration) {
      global $DB;

      switch (plugin_simcard_currentVersion()) {
         case '1.2':
            self::install($migration);
            break;
      }
   }

   static function uninstall() {
      global $DB;
      
      foreach (array('DisplayPreference', 'Bookmark') as $itemtype) {
         $item = new $itemtype();
         $item->deleteByCriteria(array('itemtype' => __CLASS__));
      }

      // Remove dropdowns localization
      $dropdownTranslation = new DropdownTranslation();
      $dropdownTranslation->deleteByCriteria(array("itemtype = 'PluginSimcardSimcardType'"), 1);

      $table = getTableForItemType(__CLASS__);
      $DB->query("DROP TABLE IF EXISTS `$table`");
   }

   static function transfer($ID, $entity) {
      global $DB;
      
      $simcardType = new self();

      if ($ID > 0) {
         // Not already transfer
         // Search init item
         $query = "SELECT *
                   FROM `".$simcardType->getTable()."`
                   WHERE `id` = '$ID'";

         if ($result = $DB->query($query)) {
            if ($DB->numrows($result)) {
               $data                 = $DB->fetch_assoc($result);
               $data                 = Toolbox::addslashes_deep($data);
               $input['name']        = $data['name'];
               $input['entities_id'] = $entity;
               $newID                = $simcardType->getID($input);

               if ($newID < 0) {
                  $newID = $simcardType->import($input);
               }

               return $newID;
            }
         }
      }
      return 0;
   }

}

?>

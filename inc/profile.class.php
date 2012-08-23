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
 @link      https://forge.indepnet.net/projects/simcard
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginSimcardProfile extends CommonDBTM {

   static function purgeProfiles(Profile $prof) {
      $plugprof = new self();
      $plugprof->deleteByCriteria(array('profiles_id' => $prof->getField("id")));
   }
   
   function getFromDBByProfile($profiles_id) {
      global $DB;
      
      $query = "SELECT * FROM `".$this->getTable()."`
               WHERE `profiles_id` = '" . $profiles_id . "' ";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }

   function createAccess($ID) {
      $this->add(array('profiles_id' => $ID));
   }
   
   static function createFirstAccess($ID) {
      $myProf = new self();
      if (!$myProf->getFromDBByProfile($ID)) {
         $myProf->add(array('profiles_id' => $ID, 'simcard' => 'w', 'open_ticket'=>1));
      }
   }

   static function changeProfile() {
      $prof = new self();
      if ($prof->getFromDBByProfile($_SESSION['glpiactiveprofile']['id'])) {
         if ($prof->fields['simcard']) {
            $_SESSION["glpiactiveprofile"]['simcard'] = $prof->fields['simcard'];
            $_SESSION["glpiactiveprofile"]['simcard_open_ticket'] = $prof->fields['open_ticket'];
         }
      } else {
         unset($_SESSION["glpiactiveprofile"]['simcard']);
         unset($_SESSION["glpiactiveprofile"]['simcard_open_ticket']);
      }
   }
   
   //profiles modification
   function showForm($ID){
      global $LANG;

      if (!haveRight("profile", "r")) {
         return false;
      }
      $canedit = haveRight("profile", "w");
      $prof    = new Profile();
      if ($ID){
         $this->getFromDBByProfile($ID);
         $prof->getFromDB($ID);
      }

      echo "<form action='".getItemTypeFormURL(__CLASS__)."' method='post'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<th colspan='4' align='center'><strong>" .
         $LANG['plugin_simcard']['profile'][0] . " " . $prof->fields["name"] . "</strong></th>";
         
      echo "<tr class='tab_bg_2'>";
      echo "<td>".$LANG['plugin_simcard']['profile'][1].":</td><td>";
      Profile::dropdownNoneReadWrite("simcard", $this->fields["simcard"],1,1,1);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . $LANG['setup'][352] . " - " . $LANG['plugin_simcard']['profile'][1] . ":</td><td>";
      if ($prof->fields['create_ticket']) {
         Dropdown::showYesNo("open_ticket" , $this->fields["open_ticket"]);
      } else {
         echo Dropdown::getYesNo(0);
      }
      echo "</td>";
      echo "</tr>";

      if ($canedit) {
         echo "<tr class='tab_bg_1'>";
         echo "<td align='center' colspan='2'>";
         echo "<input type='hidden' name='id' value=".$this->getID().">";
         echo "<input type='submit' name='update' value=\"".$LANG['buttons'][7]."\" class='submit'>";
         echo "</td></tr>";
      }
      echo "</table></form>";
   }
    
   static function install(Migration $migration) {
      global $DB;
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
               `id` int(11) NOT NULL auto_increment,
               `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
               `simcard` char(1) collate utf8_unicode_ci default NULL,
               `open_ticket` char(1) collate utf8_unicode_ci default NULL,
               PRIMARY KEY  (`id`),
               KEY `profiles_id` (`profiles_id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());
      }
      PluginSimcardProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
      self::changeProfile();
   }
    
   static function uninstall() {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      $DB->query("DROP TABLE IF EXISTS `$table`");
      unset($_SESSION["glpiactiveprofile"]['simcard']);
      unset($_SESSION["glpiactiveprofile"]['simcard_open_ticket']);
   }
}

?>
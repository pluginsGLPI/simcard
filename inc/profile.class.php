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

class PluginSimcardProfile extends Profile {

   const RIGHT_SIMCARD_OPEN_TICKET = "simcard:open_ticket";
   const RIGHT_SIMCARD_SIMCARD = "simcard:simcard";
	
   static $rightname = 'profile'; 
	
   static function purgeProfiles(Profile $prof) {
      $plugprof = new self();
      $plugprof->deleteByCriteria(array('profiles_id' => $prof->getField("id")));
   }
   
//    function getFromDBByProfile($profiles_id) {
//       global $DB;
      
//       $query = "SELECT * FROM `".$this->getTable()."`
//                WHERE `profiles_id` = '" . $profiles_id . "' ";
//       if ($result = $DB->query($query)) {
//          if ($DB->numrows($result) != 1) {
//             return false;
//          }
//          $this->fields = $DB->fetch_assoc($result);
//          if (is_array($this->fields) && count($this->fields)) {
//             return true;
//          } else {
//             return false;
//          }
//       }
//       return false;
//    }

   function createAccess($ID) {
      $this->add(array('profiles_id' => $ID));
   }
   
   static function createFirstAccess($ID) {
      $profileRight = new ProfileRight();
      
      $currentRights = ProfileRight::getProfileRights(
      	$ID, 
      	array(self::RIGHT_SIMCARD_SIMCARD, self::RIGHT_SIMCARD_OPEN_TICKET)
      );
      $firstAccessRights = array_merge($currentRights, array(self::RIGHT_SIMCARD_SIMCARD => ALLSTANDARDRIGHT, self::RIGHT_SIMCARD_OPEN_TICKET => 1));
      $profileRight->updateProfileRights($ID, $firstAccessRights);

      //Add right to the current session
      $_SESSION['glpiactiveprofile'][self::RIGHT_SIMCARD_SIMCARD] = $firstAccessRights[self::RIGHT_SIMCARD_SIMCARD];
      $_SESSION['glpiactiveprofile'][self::RIGHT_SIMCARD_OPEN_TICKET] = $firstAccessRights[self::RIGHT_SIMCARD_OPEN_TICKET];
      
//       if (!$myProf->getFromDBByProfile($ID)) {
//          $myProf->add(array('profiles_id' => $ID, 'simcard' => CREATE, 'open_ticket'=>1));
//       }
   }

//    static function changeProfile() {
//       $profile = new self();
//       if ($profile->getFromDBByProfile($_SESSION['glpiactiveprofile']['id'])) {
//          if ($profile->fields[self::RIGHT_SIMCARD_SIMCARD]) {
//             $_SESSION["glpiactiveprofile"][self::RIGHT_SIMCARD_SIMCARD] = $profile->fields[self::RIGHT_SIMCARD_SIMCARD];
//             $_SESSION["glpiactiveprofile"][self::RIGHT_SIMCARD_OPEN_TICKET] = $profile->fields[self::RIGHT_SIMCARD_OPEN_TICKET];
//          }
//       } else {
//          unset($_SESSION["glpiactiveprofile"][self::RIGHT_SIMCARD_SIMCARD]);
//          unset($_SESSION["glpiactiveprofile"][self::RIGHT_SIMCARD_OPEN_TICKET]);
//       }
//    }
   
   //profiles modification
   function showForm($ID, $options = array()){
      global $LANG;

      if (!Profile::canView()) {
         return false;
      }
      $canedit = self::canUpdate();
      $profile    = new Profile();
      if ($ID){
         //$this->getFromDBByProfile($ID);
         $profile->getFromDB($ID);
      }
      if ($canedit) {
      	echo "<form action='".$profile->getFormURL()."' method='post'>";
      }
      
      $rights = $this->getAllRights();
      $profile->displayRightsChoiceMatrix($rights, array('canedit'       => $canedit,
                                                      'default_class' => 'tab_bg_2',
                                                      'title'         => __('General')));
      
      echo "<table class='tab_cadre_fixe'>";

      echo "<th colspan='4' align='center'><strong>" .
         $LANG['plugin_simcard']['profile'][0] . " " . $profile->fields["name"] . "</strong></th>";
       
      echo "<tr class='tab_bg_2'>";
      echo "<td width='20%'>" . __("Associable items to a ticket") . " - " . $LANG['plugin_simcard']['profile'][1] . ":</td>";
      echo "<td colspan='5'>";
      $effective_rights = ProfileRight::getProfileRights($ID, array(self::RIGHT_SIMCARD_OPEN_TICKET));

      Html::showCheckbox(array('name'    => '_' . self::RIGHT_SIMCARD_OPEN_TICKET . '[1_0]',
      	 'checked' => $effective_rights[self::RIGHT_SIMCARD_OPEN_TICKET],
      	 'readonly' => !( Ticket::canCreate() )
      ));
      echo "</td>";
      echo "</tr>";
      echo "</table>";
      
      if ($canedit) {
         echo "<div class='center'>";
         echo "<input type='hidden' name='id' value=".$ID.">";
         echo "<input type='submit' name='update' value=\""._sx('button', 'Save')."\" class='submit'>";
         echo "</div>";
      }
      Html::closeForm();
      $this->showLegend();
   }
    
   static function install(Migration $migration) {
      global $DB;
//       $table = getTableForItemType(__CLASS__);
//       if (!TableExists($table)) {
//          $query = "CREATE TABLE IF NOT EXISTS `$table` (
//                `id` int(11) NOT NULL auto_increment,
//                `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
//                `simcard` char(1) collate utf8_unicode_ci default NULL,
//                `open_ticket` char(1) collate utf8_unicode_ci default NULL,
//                PRIMARY KEY  (`id`),
//                KEY `profiles_id` (`profiles_id`)
//             ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
//          $DB->query($query) or die($DB->error());
//       }
      PluginSimcardProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
//      self::changeProfile();
   }
    
   /**
    * 
    *
    * @since 1.3
    **/
   static function upgrade(Migration $migration) {
      global $DB;
      
      $table = getTableForItemType(__CLASS__);
      switch (plugin_simcard_currentVersion()) {
      	case '1.3':           
            $matching = array('simcard'    => self::RIGHT_SIMCARD_SIMCARD, 
                           'open_ticket' => self::RIGHT_SIMCARD_OPEN_TICKET);
      		$query = "SELECT * FROM `glpi_plugin_simcard_profiles`";
      		$result = $DB->query($query);
      		while ($data = $DB->fetch_assoc($result)) {
      			// Lire les droits dans le nouveau système d'ACLs GLPI 0.85
      			$current_rights = ProfileRight::getProfileRights($data['profiles_id'], array_values($matching));
      			foreach ($matching as $old => $new) {
      				if (!isset($current_rights[$new])) {
      					$query = "INSERT INTO `glpi_profilerights`
                                  SET `rights`='" . self::translateARight($data[$old]) . "',
      			                  `profiles_id`='" . $data['profiles_id'] . "',
      			                  `name`='" . $new . "'";
      					$DB->query($query) or die($DB->error());
      				}
      			}
      		}
      		$query = "DROP TABLE `glpi_plugin_simcard_profiles`";
      		$DB->query($query) or die($DB->error());
      }
  }

   /**
    * Init profiles
    *
    **/
   
   static function translateARight($old_right) {
   	  switch ($old_right) {
   		 case '':
   			return 0;
   			
   		 case 'r' :
   			return READ;
   			
   		 case 'w':
   			return ALLSTANDARDRIGHT;
   			
   		 case '0':
   		 case '1':
   			return $old_right;
   
   		 default :
   			return 0;
   	  }
   }
      
   static function uninstall() {
      global $DB;

      ProfileRight::deleteProfileRights(array(
         self::RIGHT_SIMCARD_SIMCARD,
         self::RIGHT_SIMCARD_OPEN_TICKET
      ));
      unset($_SESSION["glpiactiveprofile"][self::RIGHT_SIMCARD_SIMCARD]);
      unset($_SESSION["glpiactiveprofile"][self::RIGHT_SIMCARD_OPEN_TICKET]);
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;
      if ($item->getType()=='Profile') {
         return $LANG['plugin_simcard']['profile'][1];
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      
      if ($item->getType() == 'Profile') {
         $profile = new self();
//          if (!$profile->getFromDBByProfile($item->getField('id'))) {
//             $profile->createAccess($item->getID());
//          }
         $profile->showForm($item->getField('id'));
      }
      return true;
   }

   function getAllRights() {
      $rights = array(
          array('itemtype'  => 'PluginSimcardSimcard',
                'label'     => _n('Simcard', 'Simcards', 2, 'simcard'),
                'field'     => 'simcard:simcard'
          ),
      );
      return $rights;
   }

}

?>
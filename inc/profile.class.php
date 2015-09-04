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

   const RIGHT_SIMCARD_SIMCARD = "simcard:simcard";
   const SIMCARD_ASSOCIATE_TICKET = 32;
   
   static $rightname = 'profile'; 
	
   static function purgeProfiles(Profile $prof) {
      $plugprof = new self();
      $plugprof->deleteByCriteria(array('profiles_id' => $prof->getField("id")));
   }
   


   function createAccess($ID) {
      $this->add(array('profiles_id' => $ID));
   }
   
   static function createFirstAccess($ID) {
      $profileRight = new ProfileRight();
      
      $currentRights = ProfileRight::getProfileRights(
      	$ID, 
      	array(self::RIGHT_SIMCARD_SIMCARD)
      );
      $firstAccessRights = array_merge($currentRights, array(self::RIGHT_SIMCARD_SIMCARD => ALLSTANDARDRIGHT + self::SIMCARD_ASSOCIATE_TICKET));
      $profileRight->updateProfileRights($ID, $firstAccessRights);

      //Add right to the current session
      $_SESSION['glpiactiveprofile'][self::RIGHT_SIMCARD_SIMCARD] = $firstAccessRights[self::RIGHT_SIMCARD_SIMCARD];
   
   }   
   
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
                                                         'default_class' => 'tab_bg_2'));
      
      
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

      PluginSimcardProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
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
      	case '1.3.1':
      		$query = "SELECT * FROM `glpi_plugin_simcard_profiles`";
      		$result = $DB->query($query);
      		while ($data = $DB->fetch_assoc($result)) {
      			// Write the access rights into the new ACLs system of GLPI 0.85 
      			$translatedRight = self::translateARight($data['simcard']) + self::translateARight($data['open_ticket']);
      			$query = "INSERT INTO `glpi_profilerights`
                            SET `rights`='" . $translatedRight . "',
			                  `profiles_id`='" . $data['profiles_id'] . "',
			                  `name`='" . self::RIGHT_SIMCARD_SIMCARD . "'";
      			$DB->query($query) or die($DB->error());
      		}
      		$query = "DROP TABLE `glpi_plugin_simcard_profiles`";
      		$DB->query($query) or die($DB->error());
      		break;
      		
      	case '1.4':
      	case '1.4.1':
      		
      }
   }

   /**
    * Init profiles
    *
    **/
   
   static function translateARight($old_right) {
   	  switch ($old_right) {
   		 case 'r' :
   			return READ;
   			
   		 case 'w':
   			return ALLSTANDARDRIGHT;
   			
   		 case '1':
   			return self::SIMCARD_ASSOCIATE_TICKET;
   
   		 case '0':
   		 case '':
   		 default:
   			return 0;
   	  }
   }
      
   static function uninstall() {
      global $DB;

      ProfileRight::deleteProfileRights(array(
         self::RIGHT_SIMCARD_SIMCARD
      ));
      unset($_SESSION["glpiactiveprofile"][self::RIGHT_SIMCARD_SIMCARD]);
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;
      if ($item->getType()=='Profile') {
         return _sn('SIM card', 'SIM cards', 2, 'simcard');
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      
      if ($item->getType() == 'Profile') {
         $profile = new self();
         $profile->showForm($item->getField('id'));
      }
      return true;
   }

   function getAllRights() {
      $rights = array(
          array('itemtype'  => 'PluginSimcardSimcard',
                'label'     => _n('SIM card', 'SIM cards', 2, 'simcard'),
                'field'     => 'simcard:simcard'
          ),
      );
      return $rights;
   }

}

?>
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

function plugin_simcard_install() {
   include_once (GLPI_ROOT."/plugins/simcard/inc/profile.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcard.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcardsize.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcardvoltage.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/phoneoperator.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcard_item.class.php");
    
   $migration = new Migration('1.2.0');
   PluginSimcardProfile::install($migration);
   PluginSimcardSimcard::install($migration);
   PluginSimcardSimcardSize::install($migration);
   PluginSimcardSimcardVoltage::install($migration);
   PluginSimcardPhoneOperator::install($migration);
   PluginSimcardSimcard_Item::install($migration);
   return true;
}

function plugin_simcard_uninstall() {
   include_once (GLPI_ROOT."/plugins/simcard/inc/profile.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcard.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcardsize.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcardvoltage.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/phoneoperator.class.php");
   include_once (GLPI_ROOT."/plugins/simcard/inc/simcard_item.class.php");
   
   PluginSimcardProfile::uninstall();
   PluginSimcardSimcard::uninstall();
   PluginSimcardSimcardSize::uninstall();
   PluginSimcardSimcardVoltage::uninstall();
   PluginSimcardPhoneOperator::uninstall();
   PluginSimcardSimcard_Item::uninstall();
   return true;
}

// Define dropdown relations
function plugin_simcard_getDatabaseRelations() {

   $plugin = new Plugin();

   if ($plugin->isActivated("simcard")) {
      return array(
                  "glpi_plugin_simcard_simcardsizes"
                     => array("glpi_plugin_simcard_simcards"=>"plugin_simcard_simcardsizes_id"),
                  "glpi_plugin_simcard_simcardvoltages"
                     => array("glpi_plugin_simcard_simcards"=>"plugin_simcard_simcardvoltages_id"),
                     "glpi_plugin_simcard_phoneoperators"
                     => array("glpi_plugin_simcard_simcards"=>"plugin_simcard_phoneoperators_id"),
                     "glpi_users" => array("glpi_plugin_simcard_simcards"=>"users_id"),
                   "glpi_groups" => array("glpi_plugin_simcard_simcards"=>"groups_id"),
                   "glpi_manufacturers" => array("glpi_plugin_simcard_simcards" => "manufacturers_id"),
                   "glpi_states" => array("glpi_plugin_simcard_simcards" => "states_id"),
                   "glpi_locations" => array("glpi_plugin_simcard_simcards"=>"locations_id"),
                   "glpi_profiles" => array ("glpi_plugin_simcard_profiles" => "profiles_id"));
   } else {
      return array();
   }
}


// Define Dropdown tables to be manage in GLPI :
function plugin_simcard_getDropdown() {
   global $LANG;

   $plugin = new Plugin();
   if ($plugin->isActivated("simcard")) {
     return array('PluginSimcardSimcardSize'    => $LANG['plugin_simcard'][6],
                  'PluginSimcardPhoneOperator'  => $LANG['plugin_simcard'][7],
                  'PluginSimcardSimcardVoltage' => $LANG['plugin_simcard'][9]);
   } else {
      return array();
   }

}

function plugin_simcard_AssignToTicket($types) {
   global $LANG;

   if (Session::haveRight("simcard_open_ticket", "1")) {
      $types['PluginSimcardSimcard'] = $LANG['plugin_simcard']['profile'][1];
   }

   return $types;
}

//force groupby for multible links to items

function plugin_simcard_forceGroupBy($type) {

   return true;
   switch ($type) {
      case 'PluginSimcardSimcard':
         return true;
         break;

   }
   return false;
}


function plugin_simcard_getAddSearchOptions($itemtype) {
   global $LANG;
    
   $sopt = array();

   if (in_array($itemtype,PluginSimcardSimcard_Item::getClasses())) {
      if (Session::haveRight("simcard","r")) {
         $sopt[1710]['table']         = 'glpi_plugin_simcard_simcards';
         $sopt[1710]['field']         = 'name';
         $sopt[1710]['name']          = $LANG['plugin_simcard']['profile'][1]." - ".$LANG['common'][16];
         $sopt[1710]['forcegroupby']  = true;
         $sopt[1710]['massiveaction'] = false;
         $sopt[1710]['datatype']      = 'itemlink';
         $sopt[1710]['itemlink_type'] = 'PluginSimcardSimcard';
         $sopt[1710]['joinparams']     = array('beforejoin'
                                                => array('table'      => 'glpi_plugin_simcard_simcards_items',
                                                         'joinparams' => array('jointype' => 'itemtype_item')));
         $sopt[1711]['table']         = 'glpi_plugin_simcard_simcards';
         $sopt[1711]['field']         = 'phonenumber';
         $sopt[1711]['name']          = $LANG['plugin_simcard']['profile'][1]." - ".$LANG['plugin_simcard'][1];
         $sopt[1711]['massiveaction'] = false;
         $sopt[1711]['forcegroupby']  = true;
         $sopt[1711]['joinparams']     = array('beforejoin'
                                                => array('table'      => 'glpi_plugin_simcard_simcards_items',
                                                         'joinparams' => array('jointype' => 'itemtype_item')));
      }
   }
   return $sopt;
}

// Hook done on purge item case

function plugin_item_purge_simcard($item) {

   $temp = new PluginSimcardSimcard_Item();
   $temp->deleteByCriteria(array('itemtype' => get_class($item),
                                 'items_id' => $item->getField('id')));
   return true;
}

// Define headings added by the plugin
/*
function plugin_get_headings_simcard($item, $withtemplate) {
   global $LANG;

   if ((in_array(get_class($item), PluginSimcardSimcard_Item::getClasses()) && $item->getID() > 0)
      || (get_class($item) == 'Profile'
         && $item->fields['interface']!='helpdesk')) {
      return array(1 => $LANG['plugin_simcard']['profile'][1]);
   }

   return false;
}

// Define headings actions added by the plugin

function plugin_headings_actions_simcard($item) {

   if (in_array(get_class($item), PluginSimcardSimcard_Item::getClasses())
       || get_class($item)=='Profile') {
      return array(1 => "plugin_headings_simcard");
   } else {
   return false;
   }
}


// action heading
function plugin_headings_simcard($item,$withtemplate=0) {
   global $CFG_GLPI;

   $profile      = new PluginSimcardProfile();
   $simcard_Item = new PluginSimcardSimcard_Item();

   switch (get_class($item)) {

      case 'Profile' :
         if (!$profile->getFromDBByProfile($item->getField('id'))) {
            $profile->createAccess($item->getField('id'));
         }
         $profile->showForm($item->getField('id'));
         break;

      default :
         if (in_array(get_class($item), PluginSimcardSimcard_Item::getClasses())) {
            PluginSimcardSimcard_Item::showForItem($item);
         }
         break;
   }
}*/


function plugin_datainjection_populate_simcard() {
   global $INJECTABLE_TYPES;
   $INJECTABLE_TYPES['PluginSimcardSimcardInjection']      = 'simcard';
   $INJECTABLE_TYPES['PluginSimcardSimcard_ItemInjection'] = 'simcard';
}

function plugin_simcard_postinit() {
   global $UNINSTALL_TYPES, $ORDER_TYPES, $ALL_CUSTOMFIELDS_TYPES, $DB;
   $plugin = new Plugin();
   if ($plugin->isInstalled('uninstall') && $plugin->isActivated('uninstall')) {
      array_push($UNINSTALL_TYPES, 'PluginSimcardSimcard');
   }
   if ($plugin->isInstalled('order') && $plugin->isActivated('order')) {
      array_push($ORDER_TYPES, 'PluginSimcardSimcard');
   }
   if ($plugin->isInstalled('customfields') && $plugin->isActivated('customfields')) {
      PluginCustomfieldsItemtype::registerItemtype('PluginSimcardSimcard');
   }
}
?>
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

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

if (!isset($_POST["id"])) {
   exit();
}

if (!isset($_REQUEST['glpi_tab'])) {
   exit();
}

if (!isset($_POST["withtemplate"])) {
   $_POST["withtemplate"] = "";
}

$simcard = new PluginSimcardSimcard();
if ($_POST['id']>0 && $simcard->can($_POST['id'],'r')) {
   switch($_REQUEST['glpi_tab']) {
      case -1 :
         PluginSimcardSimcard_Item::showForSimcard($simcard);
         Infocom::showForItem($simcard, $_POST["withtemplate"]);
         Contract::showAssociated($simcard, $_POST["withtemplate"]);
         Document::showAssociated($simcard);
         Ticket::showListForItem('PluginSimcardSimcard', $_POST["id"]);
         Reservation::showForItem('PluginSimcardSimcard', $_POST["id"]);
         Plugin::displayAction($simcard, $_REQUEST['glpi_tab'], $_POST["withtemplate"]);
         break;

      case 1 :
         PluginSimcardSimcard_Item::showForSimcard($simcard);
         break;
      case 2 :
         Infocom::showForItem($simcard, $_POST["withtemplate"]);
         Contract::showAssociated($simcard, $_POST["withtemplate"]);
         break;

      case 3 :
         Document::showAssociated($simcard);
         break;

      case 4 :
         Ticket::showListForItem('PluginSimcardSimcard', $_POST["id"]);
         break;

      case 5 :
         showNotesForm($_POST['target'],'PluginSimcardSimcard',$_POST["id"]);
         break;

      case 6 :
         Reservation::showForItem('PluginSimcardSimcard', $_POST["id"]);
         break;
         
      case 12 :
            Log::showForItem($simcard);
         break;

      default :
         if (!Plugin::displayAction($simcard, $_REQUEST['glpi_tab'], $_POST["withtemplate"])) {
         }
         break;
   }

}

ajaxFooter();

?>
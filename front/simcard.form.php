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

Session::checkRight("simcard", "r");

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

if (!isset($_GET["sort"])) {
   $_GET["sort"] = "";
}

if (!isset($_GET["order"])) {
   $_GET["order"] = "";
}

if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$simcard = new PluginSimcardSimcard();
//Add a new computer
if (isset($_POST["add"])) {
   $simcard->check(-1, 'w', $_POST);
   if ($newID = $simcard->add($_POST)) {
   }
   Html::back();

// delete a computer
} else if (isset($_POST["delete"])) {
   $simcard->check($_POST['id'], 'd');
   $ok = $simcard->delete($_POST);
   if ($ok) {
   }
   Html::redirect(getItemTypeSearchURL('PluginSimcardSimcard'));

} else if (isset($_POST["restore"])) {
   $simcard->check($_POST['id'], 'd');
   if ($simcard->restore($_POST)) {
      Event::log($_POST["id"],"computers", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s restores the item'), $_SESSION["glpiname"]));
   }
   html::redirect(getItemTypeSearchURL('PluginSimcardSimcard'));
   
} else if (isset($_REQUEST["purge"])) {
   $simcard->check($_REQUEST['id'], 'd');
   if ($simcard->delete($_REQUEST,1)) {
   }
   Html::redirect(getItemTypeSearchURL('PluginSimcardSimcard'));
   
//update a computer
} else if (isset($_POST["update"])) {
   $simcard->check($_POST['id'], 'w');
   $simcard->update($_POST);
   Html::back();

} else if (isset($_GET["unglobalize"])) {
   $simcard->check($_GET["id"],'w');


  Html::redirect(getItemTypeFormURL('PluginSimcardSimcard')."?id=".$_GET["id"]);
   
} else {//print computer information
   Html::header(PluginSimcardSimcard::getTypeName(2), $_SERVER['PHP_SELF'], "plugins", "simcard", "simcard");
   //show computer form to add
   $simcard->showForm($_GET["id"], $_GET);
   html::footer();
}
?>
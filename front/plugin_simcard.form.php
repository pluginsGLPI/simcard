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

// Original Author of file: El Sendero S.A.
// Purpose of file:
// ----------------------------------------------------------------------

$NEEDED_ITEMS=array("computer","printer","networking","monitor","software","peripheral","phone","tracking","document","user","enterprise","contract","infocom","group");
define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT."/inc/includes.php");

useplugin('simcard',true);

if(!isset($_GET["ID"])) $_GET["ID"] = "";
if(!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";

$PluginSimcard=new PluginSimcard();

//add simcard
if (isset($_POST["add"]))
{
	if(plugin_simcard_haveRight("simcard","w"))
		$newID=$PluginSimcard->add($_POST);
	glpi_header($_SERVER['HTTP_REFERER']);
}
//delete simcard
else if (isset($_POST["delete"]))
{
	if(plugin_simcard_haveRight("simcard","w"))
		$PluginSimcard->delete($_POST);
	glpi_header($CFG_GLPI["root_doc"]."/plugins/simcard/index.php");
}
//restore simcard
else if (isset($_POST["restore"]))
{
	if(plugin_simcard_haveRight("simcard","w"))
		$PluginSimcard->restore($_POST);
	glpi_header($CFG_GLPI["root_doc"]."/plugins/simcard/index.php");
}
//purge simcard
else if (isset($_POST["purge"]))
{
	if(plugin_simcard_haveRight("simcard","w"))
		$PluginSimcard->delete($_POST,1);
	glpi_header($CFG_GLPI["root_doc"]."/plugins/simcard/index.php");
}
//update simcard
else if (isset($_POST["update"]))
{
	if(plugin_simcard_haveRight("simcard","w"))
		$PluginSimcard->update($_POST);
	glpi_header($_SERVER['HTTP_REFERER']);
}
//link simcard to items of glpi 
else if (isset($_POST["additem"])){

	$template=0;
	if (isset($_POST["is_template"])) $template=1;

	if ($_POST['type']>0&&$_POST['item']>0){
		if(plugin_simcard_haveRight("simcard","w")){
			echo $_POST["conID"];
			echo "|";
			echo $_POST['item'];
			echo "|";
			echo $_POST['type'];
			echo $template;
			plugin_simcard_addDevice($_POST["conID"],$_POST['item'],$_POST['type'],$template);
		}
			
	}
	glpi_header($_SERVER['HTTP_REFERER']);

}
//unlink simcard to items of glpi 
else if (isset($_POST["deleteitem"])){

	if(plugin_simcard_haveRight("simcard","w"))
		foreach ($_POST["item"] as $key => $val){
		if ($val==1) {
			plugin_simcard_deleteDevice($key);
			}
		}

	glpi_header($_SERVER['HTTP_REFERER']);
//unlink simcard to items of glpi from the items form
}else if (isset($_GET["deletesimcard"])){

	if(plugin_simcard_haveRight("simcard","w"))
		plugin_simcard_deleteDevice($_GET["ID"]);
	glpi_header($_SERVER['HTTP_REFERER']);
}
else
{
	plugin_simcard_checkRight("simcard","r");

	if (!isset($_SESSION['glpi_tab'])) $_SESSION['glpi_tab']=1;
	if (isset($_GET['onglet'])) {
		$_SESSION['glpi_tab']=$_GET['onglet'];
		//		glpi_header($_SERVER['HTTP_REFERER']);
	}
	
	//check environment meta-plugin installtion for change header
	$plugin = new Plugin();
	if ($plugin->isActivated("environment"))
		commonHeader($LANG['plugin_simcard'][0],$_SERVER['PHP_SELF'],"plugins","environment","simcard");
	else
		commonHeader($LANG['plugin_simcard'][0],$_SERVER["PHP_SELF"],"plugins","simcard");
	
	//load simcard form
	$PluginSimcard->showForm($_SERVER["PHP_SELF"],$_GET["ID"]);

	commonFooter();
}

?>
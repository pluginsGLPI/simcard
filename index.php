<?php
/*
 * @version $Id: index.php,v 1.10 2006/02/23 03:29:53 moyo Exp $
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

// ----------------------------------------------------------------------
// Original Author of file: El Sendero S.A.
// Purpose of file:
// ----------------------------------------------------------------------

$NEEDED_ITEMS=array("computer","printer","networking","monitor","software","peripheral","phone","user","search","tracking");
define('GLPI_ROOT', '../..'); 
include (GLPI_ROOT."/inc/includes.php");

//check environment meta-plugin installation for change header
$plugin = new Plugin();
if ($plugin->isActivated("environment"))
	commonHeader($LANG['plugin_simcard'][0],$_SERVER['PHP_SELF'],"plugins","environment","simcard");
else
	commonHeader($LANG['plugin_simcard'][0],$_SERVER["PHP_SELF"],"plugins","simcard");

if(plugin_simcard_haveRight("simcard","r") || haveRight("config","w")){
	
	manageGetValuesInSearch(PLUGIN_SIMCARD_TYPE);
	
	searchForm(PLUGIN_SIMCARD_TYPE,$_GET);

	showList(PLUGIN_SIMCARD_TYPE,$_GET);	
	
}else{
	echo "<div align='center'><br><br><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br>";
	echo "<b>".$LANG['login'][5]."</b></div>";
}
commonFooter();

?>
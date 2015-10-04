<?php defined("_JEXEC") or die("Restricted access");
	/*
	This file is part of "Ozio Chat Joomla Extension".
	Author: Open Source solutions http://www.opensourcesolutions.es

	You can redistribute and/or modify it under the terms of the GNU
	General Public License as published by the Free Software Foundation,
	either version 2 of the License, or (at your option) any later version.

	GNU/GPL license gives you the freedom:
	* to use this software for both commercial and non-commercial purposes
	* to share, copy, distribute and install this software and charge for it if you wish.

	Under the following conditions:
	* You must attribute the work to the original author

	This software is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this software.  If not, see http://www.gnu.org/licenses/gpl-2.0.html.

	@copyright Copyright (C) 2015 Open Source Solutions S.L.U. All rights reserved.
	*/

jimport("joomla.application.component.view");

class OzioChatViewSmartLoader extends JViewLegacy
{
	function display($tpl = null)
	{
		//		parent::display($tpl);

		// Load module || component || plugin parameters. Defaults to plugin
		//$owner = JRequest::getVar("owner", "", "GET");
		//if (!in_array($owner,array('module','plugin'))){
		//	die();
		//}
		$owner = 'module';
		
		
		$jinput = JFactory::getApplication()->input;
		$type = $jinput->get->get('type', '', 'STR');
		if (!in_array($type,array('css','js'))){
			die();
		}
		
		$db = JFactory::getDbo();
		jimport("joomla.database.databasequery");
		$query = $db->getQuery(true);
		$this->$owner($query);
		$db->setQuery($query);

		// Load parameters from database
		$json = $db->loadResult();
		// Convert to JRegistry
		$params = new JRegistry($json);
		// $params = $params->toArray();

		// Import appropriate library
		jimport("oziochat.smartloader.smartloader") or die("smartloader library not found");
		// Type could be css, js or markers
		// Instantiate the loader
		$classname = $type . "SmartLoader";
		$loader = new $classname();
		$loader->Params = &$params;
		$loader->Show();
	}


	private function module(&$query)
	{
		$query->select('`params`');
		$query->from('`#__modules`');
		$query->where("`id` = " . intval(JRequest::getVar("id", 0, "GET")));
		$query->where("`module` = 'mod_oziochat'");
	}


	private function plugin(&$query)
	{
		$query->select("`params`");
		$query->from("`#__extensions`");
		$query->where("`element` = 'oziochat'");
		$query->where("`client_id` = 0");
		$query->where("`type` = 'plugin'");
	}


	private function component(&$query)
	{
	}


	private function article(&$query)
	{
		$query->select('`metadata`');
		$query->from('`#__content`');
		$query->where("`id` = " . intval(JRequest::getVar("id", 0, "GET")));
	}

}

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

class OzioChatViewChat extends JViewLegacy
{
	function display($tpl = null)
	{
		$db = JFactory::getDbo();
		jimport("joomla.database.databasequery");
		$query = $db->getQuery(true);
		$this->module($query);
		$db->setQuery($query);

		// Load parameters from database
		$json = $db->loadResult();
		// Convert to JRegistry
		$params = new JRegistry($json);
		// $params = $params->toArray();
		
		// Import appropriate library
		jimport("oziochat.chat.chat") or die("chat library not found");
		
		$loader = new OzioChatServerSideChat();
		$loader->Params = &$params;
		$loader->Process();
	}


	private function module(&$query)
	{
		$query->select('`params`');
		$query->from('`#__modules`');
		
		$jinput = JFactory::getApplication()->input;
		$id = $jinput->get->get('id', 0, 'INT');
		
		$query->where("`id` = " . intval($id));
		$query->where("`module` = 'mod_oziochat'");
	}

}

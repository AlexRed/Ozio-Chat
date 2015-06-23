<?php defined("_JEXEC") or die("Restricted access");

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
		
		
		$type = JRequest::getVar("type", "", "GET");
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

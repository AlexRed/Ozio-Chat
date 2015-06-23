<?php defined("_JEXEC") or die("Restricted access");

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
		$query->where("`id` = " . intval(JRequest::getVar("id", 0, "GET")));
		$query->where("`module` = 'mod_oziochat'");
	}

}

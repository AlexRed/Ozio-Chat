<?php defined("_JEXEC") or die("Restricted access");

jimport("joomla.application.component.view");

class OzioChatViewOzioChat extends JViewLegacy
{
	function display($tpl = null)
	{
		$this->msg = "OzioChat component is still under development";
		parent::display($tpl);
	}
}

<?php defined('_JEXEC') or die('Restricted access');
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

class com_oziochatInstallerScript
{
	protected $component_name;
	protected $extension_name;
	protected $event;


	function install($parent)
	{
		$this->event = "install";
		$this->chain_install($parent);
		$this->logo($parent);
	}


	function uninstall($parent)
	{
		$this->event = "uninstall";
		$this->results = array();
		//$this->logo($parent);
	}


	function update($parent)
	{
		$this->event = "update";

		$this->chain_install($parent);
		$this->logo($parent);

		$db = JFactory::getDBO();

		// Fixes a Joomla bug, wich adds a second repository rather than overwrite the first one if they are different
		$query = "DELETE FROM `#__update_sites` WHERE `name` = '" . $this->extension_name . " update site';";
		$db->setQuery($query);
		$db->query();

		// Clear updates cache related to this extension
		$query = "DELETE FROM `#__updates` WHERE `name` = '" . $this->extension_name . "';";
		$db->setQuery($query);
		$db->query();

	}


	function preflight($type, $parent)
	{
		$this->component_name = $parent->get("element");
		$this->extension_name = substr($this->component_name, 4);
	}


	function postflight($type, $parent)
	{
		if ($type == 'uninstall') return true;

		$installed = true;
		foreach ($this->results as $res){
			if ($res['result']!="INSTALLED"){
				$installed = false;
				break;
			}
		}
		if (!$installed){
			return true;
		}
		
		if (version_compare(JVERSION, '3.0', 'ge')){
			
			//style="width:34%;margin-left:-20%;top:25%;"
			$app = JFactory::getApplication();
			$html=array();			
			$html[] ='<div id="oziochat-modal" class="modal hide fade" >';
			$html[] ='';
			$html[] ='	<div class="modal-header">';
			$html[] ='		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
			$html[] ='		<h3>Ozio Chat</h3>';
			$html[] ='	</div>';
			$html[] ='	<div class="modal-body">';
			$html[] ='		<div class="progress progress-success progress-striped">';
			$html[] ='			<div class="bar" style="width: 0;"></div>';
			$html[] ='		</div>';
			
			
			require_once(JPATH_ROOT . '/' . "libraries" . '/' . "oziochat" . '/' . "language" . '/' . "oziochat.inc");
			$language = JFactory::getLanguage();
			$language->load("com_oziochat.sys", realpath(dirname(__FILE__)));
			$langcode = preg_replace("/-.*/", "", $language->get("tag"));

			if (isset($GLOBALS["oziochat"]) && isset($GLOBALS["oziochat"]["ha"]) && $GLOBALS["oziochat"]["ha"]) {
			}else{
				$html[] ='			<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">×</button> ';
				$html[] ='				<div style="float:left;margin-right:16px;margin-left:10px;">';
				$html[] ='					<a href="http://www.opensourcesolutions.es/ext/ozio-chat.html" target="_blank">';
				$html[] ='						<img src="'.JURI::root().'media/oziochat/images/buy_now.jpg" border="0" alt="Buy now">';
				$html[] ='					</a>';
				$html[] ='				</div>';
				$html[] ='				<p><strong>'.$language->_("COM_OZIOCHAT_PURCHASE").'</strong></p></div> ';
			}
			
			
			
			$html[] ='	</div>';
			$html[] ='	<div class="modal-footer">';
			$html[] ='		<button class="btn" data-dismiss="modal" aria-hidden="true">OK</button>';
			$html[] ='	</div>';
			$html[] ='</div>';
			$html[] ="<script>jQuery('#oziochat-modal').remove().prependTo('body').modal({keyboard: false});jQuery('#oziochat-modal .bar').animate({ width: '100%' },1000);</script>";
			$app->enqueueMessage('Installing Ozio Chat... '.implode("\n",$html));
		}
		
		
		return true;
	}


	private function chain_install(&$parent)
	{
		$manifest = $parent->get("manifest");
		if (!isset($manifest->chain->extension)) return;

		$result = array();
		foreach($manifest->chain->extension as $extension)
		{
			// We absolutely need to create a new installer instance each cycle,
			// otherwise the first failing extension would roll-back previuosly installed extensions.
			// Note that the library is the first in install chain and it contains the language files.
			$installer = new JInstaller();

			$attributes = $extension->attributes();
			$item = $parent->getParent()->getPath("source") . '/' . $attributes["directory"] . '/' . $attributes["name"];
			$result["type"] = (string)$attributes["type"];
			$result["result"] = $installer->install($item) ? "INSTALLED" : "NOT_INSTALLED";
			$this->results[(string)$attributes["name"]] = $result;
		}
		// If Installscript is running, the component is already installed
		$result["type"] = "COMPONENT";
		$result["result"] = "INSTALLED";
		$this->results[$this->component_name] = $result;

		// Language files are installed within the library
		$result["type"] = "LANGUAGES";
		$result["result"] = $this->results["lib_" . $this->extension_name]["result"];
		$this->results["lan_" . $this->extension_name] = $result;

		if ($this->event=='install'){
			
			$params=array();
			$params["bubble_url"]=JURI::root() . "media/oziochat/images/chat-bubble.png";

			$table = JTable::getInstance('Module');
			if ($table->load(array('module'=> 'mod_oziochat'))) {
				$dbParameter = new JRegistry;
				$dbParameter->loadString($table->params);
				
				$parameter = new JRegistry;
				$parameter->loadArray($params);
				
				$parameter->merge($dbParameter);
				
				$table->params = (string)$parameter;				
				
				if (!$table->store()) {
					//echo $table->getError().'<br />';
					//return;
				}
			}		
		}
	}


	private function logo(&$parent)
	{
		JFactory::getLanguage()->load($this->extension_name . ".admin", JPATH_ROOT . "/libraries/" . $this->extension_name);
		$manifest = $parent->get("manifest");
		//JFactory::getDocument()->addStyleSheet(JURI::base(true) . "/components/" . $this->component_name . "/css/install.css");
		// http://davidwalsh.name/create-html-elements-php-htmlelement-class
		echo(
		'<style type="text/css">' .
		'@import url("' . JURI::base(true) . "/components/" . $this->component_name . "/css/install.css" . '");' .
		'</style>' .
		'<div class="install_container">' .
		'<div class="install_row">' .
		'<h2 class="install_title">' . JText::_((string)$manifest->name) . '</h2>' .
		'</div>');

		foreach ($this->results as $name => $extension)
		{
			echo(
			'<div class="install_row">' .
			'<div class="install_' . strtolower($extension["type"]) . ' install_icon">' . JText::_(strtoupper($this->extension_name) . "_" . $extension["type"]) . '</div>' .
			'<div class="install_' . strtolower($extension["result"]) . ' install_icon">' . JText::_(strtoupper($this->extension_name) . "_" . $extension["result"]) . '</div>' .
			'</div>'
			);

		}
		echo('</div>');
	}

}


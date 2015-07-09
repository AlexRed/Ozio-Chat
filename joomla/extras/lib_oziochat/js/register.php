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
?>

window.onload = function()
{
	<?php
	$id = JRequest::getVar("id", "", "GET");

	require_once(JPATH_ROOT . '/' . "libraries" . '/' . "oziochat" . '/' . "language" . '/' . "oziochat.inc");
	$language = JFactory::getLanguage();
	$language->load("com_oziochat.sys", JPATH_ROOT . "/administrator/components/com_oziochat");
	$langcode = preg_replace("/-.*/", "", $language->get("tag"));
	?>
	// Articles manager
	var container = document.getElementById('content-sliders-<?php echo $id; ?>');
	// Plugins manager
	if (!container) container = document.getElementById('plugin-sliders-<?php echo $id; ?>');
	// Modules manager
	if (!container) container = document.getElementById('module-sliders');

    // J3 Article manager
    if(!container) var container = document.getElementById('metadata');
    // J3.2 Article manager
    if(!container) var container = document.getElementById('publishing');
    if(!container) var container = document.getElementById('details');

    if(!container) var container = document.getElementById('general');

	var new_element = document.createElement('div');
	new_element.className = 'oziochat_message oziochat_red';
	new_element.innerHTML =
	'<img style="margin:0; float:left' + ';" src="../media/oziochat/images/cross-circle-frame.png">' +
	'<span style="padding-left' + ':5px; line-height:16px;">' +
	'<?php echo($language->_("COM_OZIOCHAT_PURCHASE")); ?> <a href="http://www.opensourcesolutions.es/ext/oziochat.html" target="_blank"><?php echo($language->_("COM_OZIOCHAT_BUYNOW")); ?></a>' +
	'</span>';

	if (container) container.appendChild(new_element);

}


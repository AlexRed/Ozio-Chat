<?php defined('_JEXEC') or die('Restricted access'); ?>

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


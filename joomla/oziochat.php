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

	require_once(JPATH_ROOT . '/' . "libraries" . '/' . "oziochat" . '/' . "language" . '/' . "oziochat.inc");
	$language = JFactory::getLanguage();
	$language->load("com_oziochat.sys", realpath(dirname(__FILE__)));
	$langcode = preg_replace("/-.*/", "", $language->get("tag"));
?>

<h1><?php echo($language->_("COM_OZIOCHAT")); ?></h1>

<?php if ($GLOBALS["oziochat"]["version"][strlen($GLOBALS["oziochat"]["version"]) - 1] == " ") { ?>
	<div style="float:left;margin-right:16px;margin-left:10px;">
		<a href="http://www.opensourcesolutions.es/ext/ozio-chat.html" target="_blank">
			<img src="../media/oziochat/images/buy_now.jpg" border="0" alt="Buy now">
		</a>
	</div>
	<p><strong><?php echo($language->_("COM_OZIOCHAT_PURCHASE")); ?></strong></p>
	<br style="clear:both;" />
	<?php } ?>

<table  width="90%"><tr><td>
		<h1><?php echo($language->_("COM_OZIOCHAT_INSTRUCTIONS_LBL")); ?></h1>
		<h2><?php echo($language->_("COM_OZIOCHAT_INSTRUCTIONS_TITLE")); ?></h2>
		<?php echo($language->_("COM_OZIOCHAT_INSTRUCTIONS_DSC")); ?>
		<h2><?php echo($language->_("MOD_OZIOCHAT_INSTRUCTIONS_TITLE")); ?></h2>
		<?php echo($language->_("MOD_OZIOCHAT_INSTRUCTIONS_DSC")); ?>
	</td></tr>
</table>
<p><?php echo(sprintf($language->_("JGLOBAL_ISFREESOFTWARE"), "OzioChat")); ?></p>




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

// Avoid multiple instances of the same module when called by both template and content (using loadposition)
if (isset($GLOBALS["oziochat_mid_" . $module->id])) return;
else $GLOBALS["oziochat_mid_" . $module->id] = true;

// Load shared language files for frontend side
require_once(JPATH_ROOT . "/libraries/oziochat/language/oziochat.inc");

// Api key
//$api_key = $params->get('api_key', NULL);

$menu = JFactory::getApplication()->getMenu();
$itemid = $menu->getActive() or $itemid = $menu->getDefault();
$itemid = "&amp;Itemid=" . $itemid->id;

// Used by templates
$document = JFactory::getDocument();

echo "<!-- mod_oziochat " . $GLOBALS["oziochat"]["version"] . "-->";
$prefix = JURI::base(true) . "/index.php?option=com_oziochat&amp;view=smartloader";
$postfix = "&amp;owner=module&amp;id=" . $module->id . $itemid;

require JModuleHelper::getLayoutPath($app->scope, $params->get('layout', 'default'));
$icons = oc_icons_path(JPATH_ROOT . '/' . "media" . '/' . "oziochat") . '/' . "markers" . '/' . "icons";
echo '</div>';

<?php defined('JPATH_PLATFORM') or die();
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

jimport('joomla.form.formfield');

class JFormFieldBubbleUrl extends JFormField
{
	protected $type = 'BubbleUrl';

	protected function getInput()
	{
		return "";
	}

	protected function getLabel()
	{
		return "Eg. ". JURI::root() . "media/oziochat/images/chat-bubble.png";
	}
}

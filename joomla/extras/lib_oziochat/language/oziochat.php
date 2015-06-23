defined('JPATH_PLATFORM') or die("Restricted access");
JFactory::getLanguage()->load("oziochat", JPATH_ROOT . "/libraries/oziochat");

$version = new JVersion();
switch ($version->RELEASE)
{
	case "1.6":
		$GLOBALS["toSql"] = "toMySQL";
		$GLOBALS["quoteName"] = "nameQuote";
		break;

	case "1.7":
		$GLOBALS["toSql"] = "toMySQL";
		$GLOBALS["quoteName"] = "quoteName";
		break;

	default:
		$GLOBALS["toSql"] = "toSql";
		$GLOBALS["quoteName"] = "quoteName";
}

$xml = JFactory::getXML(JPATH_ADMINISTRATOR . "/components/com_oziochat/oziochat.xml");
$db = JFactory::getDBO();
$query = $db->getQuery(true);
$query = "SELECT `location` FROM `#__update_sites` WHERE `name` = 'OzioChat update site';";
$db->setQuery($query);
$GLOBALS["oziochat"]["version"] = (string)$xml->version . " " . (md5($db->loadResult()) == "ee67ec9d8d502927afaf79aa227c8d61");

if (!function_exists("oc_icons_path"))
{
	function oc_icons_path($dummy)
	{
		echo oc_copyrightchat("OzioChat");
		return "";
	}
}


if (!function_exists("oc_template"))
{
	function oc_template($id, $noscript, $streetview)
	{
		$html = "<div id=\"oziochat_wrapper_plugin_$id\">
					<div id=\"oziochat_container_plugin_$id\">
						<div id=\"oziochat_plugin_$id\">
							<noscript>$noscript</noscript>
						</div>
					</div>";
		$html.=oc_copyrightchat("OzioChat");
		$html .= "</div>";
		
		return $html;
	}
}


if (!function_exists("oc_copyrightchat"))
{
	function oc_copyrightchat($titolomap)
	{
		$astilemap = array();
		$astilemap[] = "text-decoration:none !important";
		$sstile_amap = implode(";", $astilemap);

		$astilemap = array();
		$astilemap[] = "clear:both !important";
		$astilemap[] = "padding:10px 0 !important";

		$astilemap[] = "font-family:arial,verdana,sans-serif !important";
		$astilemap[] = "font-size:10px !important";
		$astilemap[] = "font-variant:small-caps !important";

		$sstile_divmap = implode(";", $astilemap);

		$urlmap = "http://www.joomla.it";
		$testomap = "Joomla.it";

		return
		'<div style="' . $sstile_divmap . '">' .
		'made with love from <a style="' . $sstile_amap . '" ' .
		'href="' . $urlmap . '" target="_blank">' .
		$testomap .
		'</a></div>';
	}
}

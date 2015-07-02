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

		
	
	$stylesheet = pathinfo($params->get("css", "default"));
	$document->addStyleSheet($prefix . "&amp;type=css&amp;filename=" . $stylesheet["filename"] . $postfix);

	if (empty($GLOBALS["oziochat"]["loaded_css"])){
		$GLOBALS["oziochat"]["loaded_css"]=true;
		$document->addStyleSheet(JURI::base(true) . "/libraries/oziochat/css/font-awesome.css");
		$document->addStyleSheet(JURI::base(true) . "/libraries/oziochat/css/emoji.css");
		$document->addStyleSheet(JURI::base(true) . "/libraries/oziochat/css/bootstrap-social.css");
		$document->addStyleSheet(JURI::base(true) . "/libraries/oziochat/css/magnific-popup.css");
	}
	

	
	if (empty($GLOBALS["oziochat"]["loaded_js"])){
		$GLOBALS["oziochat"]["loaded_js"]=true;
		
		if(version_compare(JVERSION, '3.0', 'ge')){
			JHtml::_('jquery.framework');
		}else{
			$document->addScript("https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js");
			$document->addScript(JURI::base(true) . '/libraries/oziochat/js/jquery-noconflict.js');
		}
		$document->addScript(JURI::base(true) . "/libraries/oziochat/js/js.cookie-1.5.1.min.js");
		$document->addScriptDeclaration('var oc_cookie = Cookies.noConflict();');

		$document->addScript(JURI::base(true) . "/libraries/oziochat/js/Autolinker.min.js");
		
		$document->addScript(JURI::base(true) . "/libraries/oziochat/js/ion.sound.min.js");
		$document->addScriptDeclaration('var oc_sound_path='.json_encode(JURI::base(true).'/libraries/oziochat/js/sounds/').';');
		
		$document->addScriptDeclaration('
			ion.sound({
				sounds: [
					{name: "alert"},
					{name: "enter"},
					{name: "exit"}
				],

				// main config
				path: oc_sound_path,
				preload: true,
				multiplay: true,
				volume: 0.6
			});

			function oc_play_received_msg(){
				// play sound
				ion.sound.play("alert");  
			}
			function oc_play_enter_msg(){
				// play sound
				ion.sound.play("enter");  
			}
			function oc_play_exit_msg(){
				// play sound
				ion.sound.play("exit");  
			}
		
		');
		

		$document->addScript(JURI::base(true) . "/libraries/oziochat/js/jquery.magnific-popup.js");
		
		$document->addScript("http://js.pusher.com/1.12/pusher.min.js");
		$document->addScript(JURI::base(true) . "/libraries/oziochat/js/PusherChatWidget.js?v=8");
	}
	
	//$document->addScript($prefix . "&amp;type=js&amp;filename=map" . $postfix);
	
	$chat_end_point=JURI::base() . "index.php?option=com_oziochat&view=chat&id=" . $module->id;
	
	$ns='module_'.$module->id;
	
	$user = JFactory::getUser();
	$uri = JFactory::getURI();
	$absolute_url = $uri->toString();
	$return=base64_encode($absolute_url);
	$joomlaLoggedIn=array(
		"loggedIn"=>!$user->guest,
		"loginUrl"=>JRoute::_("index.php?option=com_users&view=login&return=".$return, false)
	);
	
	if (!$user->guest){
		$joomlaLoggedIn['actor']=array(
			'displayName' => $user->name,
			'objectType' => 'person',
			'image' => array('url'=>JURI::base(true) . "/media/oziochat/images/nophoto.png"),
			'link'=>''
		);
	}
	
	$fb_app_id=trim($params->get("facebook_app_id", ""));
	$pusher_app_key=trim($params->get("pusher_app_key", ""));
	
	
	$anonymous_login=intval($params->get("anonymous_login", "1"))==1;
	$joomla_login=intval($params->get("joomla_login", "1"))==1;
	$facebook_login=false;
	$googleplus_login=false;
	$twitter_login=false;
	
	$fb_app_id=trim($params->get("facebook_app_id", ""));
	$fb_app_secret=trim($params->get("facebook_app_secret", ""));
	if (!empty($fb_app_id) && !empty($fb_app_secret)){
		$facebook_login=true;
	}
	
	$tw_consumer_key=trim($params->get("twitter_consumer_key", ""));
	$tw_consumer_secret=trim($params->get("twitter_consumer_secret", ""));
	if (!empty($tw_consumer_key) && !empty($tw_consumer_secret)){
		$twitter_login=true;
	}
	
	
	$gp_client_id=trim($params->get("googleplus_client_id", ""));
	$gp_client_secret=trim($params->get("googleplus_client_secret", ""));
	if (!empty($gp_client_id) && !empty($gp_client_secret)){
		$googleplus_login=true;
	}
	if ($googleplus_login){
		//per google+
		$document->setMetaData( 'google-signin-callback', 'oc_gp_signin_callback' );
		
		$document->setMetaData( 'google-signin-clientid', $gp_client_id );
		$document->setMetaData( 'google-signin-cookiepolicy', 'single_host_origin' );
		//$document->setMetaData( 'google-signin-requestvisibleactions', 'https://schema.org/AddAction' );
		$document->setMetaData( 'google-signin-scope', 'https://www.googleapis.com/auth/plus.login' );
		// https://www.googleapis.com/auth/plus.me
		//$document->setMetaData( 'google-signin-redirecturi', 'postmessage' );
		//$document->setMetaData( 'google-signin-accesstype', 'offline' );
	}
	$infobox_msg=trim($params->get("infobox_msg", ""));
	$document->addScriptDeclaration('
	  jQuery( document ).ready(function( $ ) {
        var chatWidget = new OzioChatPusherChatWidget({
          cloneFrom: "#oziochat_'.$ns.'_chat_widget",
		  chatEndPoint: '.json_encode($chat_end_point).',
		  channelName:'.json_encode($params->get("pusher_channel_name", "channel1")).',
			i18n: {
				\'please supply a nickname\':'.json_encode(JText::_("OZIOCHAT_SUPPLY_NICKNAME")).',
				\'please supply a chat message\':'.json_encode(JText::_("OZIOCHAT_SUPPLY_CHATMESSAGE")).'
			},
		  joomlaLoggedIn:'.json_encode($joomlaLoggedIn).',
		  fbAppId:'.json_encode($fb_app_id).',
		  pusherAppKey:'.json_encode($pusher_app_key).',
		  anonymous_login:'.json_encode($anonymous_login).',
		  joomla_login:'.json_encode($joomla_login).',
		  facebook_login:'.json_encode($facebook_login).',
		  googleplus_login:'.json_encode($googleplus_login).',
		  twitter_login:'.json_encode($twitter_login).',
		  infobox_msg:'.json_encode($infobox_msg).',
		  halign:'.json_encode($params->get('align', 'left')).',
		  debug:false
		  
        });
		
		
		
		(function() {
			   var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;
			   po.src = \'https://apis.google.com/js/client:plusone.js\';
			   var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);
			 })();
		
		
		
      });

	');	
	
	
	$module_class='oziochat_module'.$params->get("moduleclass_sfx", "");
?>
<div id="fb-root"></div>
<noscript><?php echo JText::_("OZIOCHAT_JAVASCRIPT_REQUIRED"); ?></noscript>
<div id="oziochat_module_<?php echo $module->id; ?>_chat_widget" class="oziochat-chat-widget <?php echo $module_class;?>" style="display:none;">

<?php
$bubble_url = $params->get("bubble_url", "");
if (!empty($bubble_url)){
?>
	<img src="<?php echo htmlspecialchars($bubble_url,ENT_QUOTES,'UTF-8'); ?>" class="oziochat-chat-widget-bubble">
<?php
}
?>
<div class="oziochat-chat-widget-title">
<label><?php echo htmlspecialchars($params->get("pusher_channel_name", "channel1"),ENT_QUOTES,'UTF-8'); ?></label>

<div class="oziochat-chat-widget-wnd-btn oziochat-chat-widget-minimize oziochat-chat-widget-wnd-last">_</div>
<div class="oziochat-chat-widget-wnd-btn oziochat-chat-widget-maximize oziochat-chat-widget-wnd-last">+</div>
<div class="oziochat-chat-widget-wnd-btn oziochat-chat-widget-popout"><i class="fa fa-expand"></i> <span></span></div>
<div class="oziochat-chat-widget-wnd-btn oziochat-chat-widget-num-users" style="cursor: auto;"><i class="fa fa-users"></i> <span></span></div>

<div style="clear:both;"></div>

</div>


<div class="oziochat-chat-widget-content">


<div class="oziochat-chat-left">

<div class="oziochat-chat-widget-current-user">
	<div class="oziochat-chat-widget-current-user-image"><img width="32" height="32"></div>
	<div class="oziochat-chat-widget-current-user-name"></div>
	
  <a class="oziochat-btn btn-social-icon btn-github oziochat-chat-logout"><i class="fa fa-power-off"></i></a>
	
</div>


<div class="oziochat-chat-widget-login-buttons">

	<div class="oziochat-chat-login-loader oziochat-chat-loader-anonymous">
	</div>

<div class="oziochat-chat-anonymous-login">
  <label for="nickname"><?php echo JText::_("OZIOCHAT_NICKNAME"); ?></label>
  <input type="text" name="nickname" />
  <label for="email" title="<?php echo JText::_("OZIOCHAT_EMAIL_TITLE"); ?>"><?php echo JText::_("OZIOCHAT_EMAIL"); ?></label>
  <input type="email" name="email" />
  <a class="oziochat-btn oziochat-btn-block btn-social btn-reddit oziochat-chat-anonymous-login-button">
	<i class="fa fa-user"></i> <?php echo JText::_("OZIOCHAT_ANONYMOUS_LOGIN"); ?>
  </a>
</div>
<div class="oziochat-chat-widget-user oziochat-chat-widget-anonymous">
	<a class="oziochat-btn btn-social-icon btn-reddit oziochat-chat-enter"><i class="fa fa-user"></i></a>
	<div class="oziochat-chat-widget-user-image"><img width="32" height="32"></div>
	<div class="oziochat-chat-widget-user-name"></div>
	<a class="oziochat-btn btn-social-icon btn-github oziochat-chat-exit oziochat-chat-anonymous-logout"><i class="fa fa-times"></i></a>
</div>


	<div class="oziochat-chat-login-loader oziochat-chat-loader-joomla">
	</div>
  <a class="oziochat-btn oziochat-btn-block btn-social btn-microsoft oziochat-chat-joomla-login">
	<i class="fa fa-joomla"></i> <?php echo JText::_("OZIOCHAT_JOOMLA_LOGIN"); ?>
  </a>
	<div class="oziochat-chat-widget-user oziochat-chat-widget-joomla">
		<a class="oziochat-btn btn-social-icon btn-microsoft oziochat-chat-enter"><i class="fa fa-joomla"></i></a>
		<div class="oziochat-chat-widget-user-image"><img width="32" height="32"></div>
		<div class="oziochat-chat-widget-user-name"></div>
		<a class="oziochat-btn btn-social-icon btn-github oziochat-chat-exit oziochat-chat-joomla-logout"><i class="fa fa-times"></i></a>
	</div>
  
	<div class="oziochat-chat-login-loader oziochat-chat-loader-facebook">
	</div>
  <a class="oziochat-btn oziochat-btn-block btn-social btn-facebook oziochat-chat-facebook-login" >
	<i class="fa fa-facebook"></i> <?php echo JText::_("OZIOCHAT_FACEBOOK_LOGIN"); ?>
  </a>
	<div class="oziochat-chat-widget-user oziochat-chat-widget-facebook">
		<a class="oziochat-btn btn-social-icon btn-facebook oziochat-chat-enter"><i class="fa fa-facebook"></i></a>
		<div class="oziochat-chat-widget-user-image"><img width="32" height="32"></div>
		<div class="oziochat-chat-widget-user-name"></div>
		<a class="oziochat-btn btn-social-icon btn-github oziochat-chat-exit oziochat-chat-facebook-logout"><i class="fa fa-times"></i></a>
	</div>

	
	<div class="oziochat-chat-login-loader oziochat-chat-loader-googleplus">
	</div>
  <a class="oziochat-btn oziochat-btn-block btn-social btn-google oziochat-chat-googleplus-login" >
	<i class="fa fa-google-plus"></i> <?php echo JText::_("OZIOCHAT_GOOGLEPLUS_LOGIN"); ?>
  </a>
	<div class="oziochat-chat-widget-user oziochat-chat-widget-googleplus">
		<a class="oziochat-btn btn-social-icon btn-google oziochat-chat-enter"><i class="fa fa-google-plus"></i></a>
		<div class="oziochat-chat-widget-user-image"><img width="32" height="32"></div>
		<div class="oziochat-chat-widget-user-name"></div>
		<a class="oziochat-btn btn-social-icon btn-github oziochat-chat-exit oziochat-chat-googleplus-logout"><i class="fa fa-times"></i></a>
	</div>
	
	<div class="oziochat-chat-login-loader oziochat-chat-loader-twitter">
	</div>
  <a class="oziochat-btn oziochat-btn-block btn-social btn-twitter oziochat-chat-twitter-login" >
	<i class="fa fa-twitter"></i> <?php echo JText::_("OZIOCHAT_TWITTER_LOGIN"); ?>
  </a>
	<div class="oziochat-chat-widget-user oziochat-chat-widget-twitter">
		<a class="oziochat-btn btn-social-icon btn-twitter oziochat-chat-enter"><i class="fa fa-twitter"></i></a>
		<div class="oziochat-chat-widget-user-image"><img width="32" height="32"></div>
		<div class="oziochat-chat-widget-user-name"></div>
		<a class="oziochat-btn btn-social-icon btn-github oziochat-chat-exit oziochat-chat-twitter-logout"><i class="fa fa-times"></i></a>
	</div>


<form style="display:none;" class="oziochat-joomla-logout-form" action="<?php echo JRoute::_(JUri::getInstance()->toString(), true); ?>" method="post">
<input class="submit" type="submit" name="submit" value="logout" />
<input type="hidden" name="option" value="com_users" />
<input type="hidden" name="task" value="user.logout" />
<input type="hidden" name="return" value="<?php echo $return; ?>" />
<?php echo JHtml::_('form.token'); ?>
</form>



<!-- End Joomla -->

</div>
<div class="oziochat-chat-widget-messages">
<ul class="oziochat-chat-activity-stream">
<li class="waiting"><?php echo JText::_("OZIOCHAT_NO_MESSAGES"); ?></li>
</ul>
</div>
<div class="oziochat-chat-widget-input">
<label for="message"><?php echo JText::_("OZIOCHAT_MESSAGE"); ?>  
<div class="oziochat-chat-widget-msg-btn oziochat-chat-widget-sound-on oziochat-chat-widget-msg-btn-last"><i class="fa fa-volume-off"></i></div>
<div class="oziochat-chat-widget-msg-btn oziochat-chat-widget-sound-off oziochat-chat-widget-msg-btn-last"><i class="fa fa-volume-up"></i></div>
<?php
	if (!empty($infobox_msg)){
?>
<div class="oziochat-chat-widget-msg-btn oziochat-chat-widget-question"><i class="fa fa-question-circle"></i></div>
<?php
	}
?>

<div class="oziochat-chat-widget-msg-btn oziochat-chat-widget-emoji"><i class="fa fa-smile-o"></i></div>
</label>

<textarea name="message" placeholder="<?php echo JText::_("OZIOCHAT_MESSAGE_PLACEHOLDER"); ?>"></textarea>
<!--<button class="oziochat-chat-widget-send-btn"><?php echo JText::_("OZIOCHAT_SEND"); ?></button>-->

<div class="oziochat-chat-emoji">

	<h2>People Emoji</h2>

	<h3>Faces Emoji</h3>

	<p class="emoji">
	<span>😄</span>
	<span>😃</span>
	<span>😀</span>
	<span>😊</span>
	<span>☺</span>
	<span>😉</span>
	<span>😍</span>
	<span>😘</span>
	<span>😚</span>
	<span>😗</span>
	<span>😙</span>
	<span>😜</span>
	<span>😝</span>
	<span>😛</span>
	<span>😳</span>
	<span>😁</span>
	<span>😔</span>
	<span>😌</span>
	<span>😒</span>
	<span>😞</span>
	<span>😣</span>
	<span>😢</span>
	<span>😂</span>
	<span>😭</span>
	<span>😪</span>
	<span>😥</span>
	<span>😰</span>
	<span>😅</span>
	<span>😓</span>
	<span>😩</span>
	<span>😫</span>
	<span>😨</span>
	<span>😱</span>
	<span>😠</span>
	<span>😡</span>
	<span>😤</span>
	<span>😖</span>
	<span>😆</span>
	<span>😋</span>
	<span>😷</span>
	<span>😎</span>
	<span>😴</span>
	<span>😵</span>
	<span>😲</span>
	<span>😟</span>
	<span>😦</span>
	<span>😧</span>
	<span>😈</span>
	<span>👿</span>
	<span>😮</span>
	<span>😬</span>
	<span>😐</span>
	<span>😕</span>
	<span>😯</span>
	<span>😶</span>
	<span>😇</span>
	<span>😏</span>
	<span>😑</span>
	<span>👲</span>
	<span>👳</span>
	<span>👮</span>
	<span>👷</span>
	<span>💂</span>
	<span>👶</span>
	<span>👦</span>
	<span>👧</span>
	<span>👨</span>
	<span>👩</span>
	<span>👴</span>
	<span>👵</span>
	<span>👱</span>
	<span>👼</span>
	<span>👸</span>
	</p>
	<h3>Cat Faces Emoji</h3>
	<p class="emoji">
	<span>😺</span>
	<span>😸</span>
	<span>😻</span>
	<span>😽</span>
	<span>😼</span>
	<span>🙀</span>
	<span>😿</span>
	<span>😹</span>
	<span>😾</span>
	</p>
	<h3>Other Faces Emoji</h3>
	<p class="emoji">
	<span>👹</span>
	<span>👺</span>
	<span>🙈</span>
	<span>🙉</span>
	<span>🙊</span>
	<span>💀</span>
	<span>👽</span>
	<span>💩</span>
	</p>

	<h3>Misc Emoji</h3>
	<p class="emoji">
	<span>🔥</span>
	<span>✨</span>
	<span>🌟</span>
	<span>💫</span>
	<span>💥</span>
	<span>💢</span>
	<span>💦</span>
	<span>💧</span>
	<span>💤</span>
	<span>💨</span>
	<span>👂</span>
	<span>👀</span>
	<span>👃</span>
	<span>👅</span>
	<span>👄</span>
	<span>👍</span>
	<span>👎</span>
	<span>👌</span>
	<span>👊</span>
	<span>✊</span>
	<span>✌</span>
	<span>👋</span>
	<span>✋</span>
	<span>👐</span>
	<span>👆</span>
	<span>👇</span>
	<span>👉</span>
	<span>👈</span>
	<span>🙌</span>
	<span>🙏</span>
	<span>☝</span>
	<span>👏</span>
	<span>💪</span>
	<span>🚶</span>
	<span>🏃</span>
	<span>💃</span>
	<span>👫</span>
	<span>👪</span>
	<span>👬</span>
	<span>👭</span>
	<span>💏</span>
	<span>💑</span>
	<span>👯</span>
	<span>🙆</span>
	<span>🙅</span>
	<span>💁</span>
	<span>🙋</span>
	<span>💆</span>
	<span>💇</span>
	<span>💅</span>
	<span>👰</span>
	<span>🙎</span>
	<span>🙍</span>
	<span>🙇</span>
	</p>
	<h3>Clothes and Accessories Emoji</h3>
	<p class="emoji">
	<span>🎩</span>
	<span>👑</span>
	<span>👒</span>
	<span>👟</span>
	<span>👞</span>
	<span>👡</span>
	<span>👠</span>
	<span>👢</span>
	<span>👕</span>
	<span>👔</span>
	<span>👚</span>
	<span>👗</span>
	<span>🎽</span>
	<span>👖</span>
	<span>👘</span>
	<span>👙</span>
	<span>💼</span>
	<span>👜</span>
	<span>👝</span>
	<span>👛</span>
	<span>👓</span>
	<span>🎀</span>
	<span>🌂</span>
	<span>💄</span>
	</p>

	<h3>Hearts Emoji</h3>	</span>
	<p class="emoji">	</span>
	<span>💛</span>
	<span>💙</span>
	<span>💜</span>
	<span>💚</span>
	<span>❤</span>
	<span>💔</span>
	<span>💗</span>
	<span>💓</span>
	<span>💕</span>
	<span>💖</span>
	<span>💞</span>
	<span>💘</span>
	<span>💌</span>
	<span>💋</span>
	<span>💍</span>
	<span>💎</span>
	<span>👤</span>
	<span>👥</span>
	<span>💬</span>
	<span>👣</span>
	<span>💭</span>
	</p>

	<h2>Nature Emoji</h2>

	<h3>Animals Emoji</h3>
	<p class="emoji">
	<span>🐶</span>
	<span>🐺</span>
	<span>🐱</span>
	<span>🐭</span>
	<span>🐹</span>
	<span>🐰</span>
	<span>🐸</span>
	<span>🐯</span>
	<span>🐨</span>
	<span>🐻</span>
	<span>🐷</span>
	<span>🐽</span>
	<span>🐮</span>
	<span>🐗</span>
	<span>🐵</span>
	<span>🐒</span>
	<span>🐴</span>
	<span>🐑</span>
	<span>🐘</span>
	<span>🐼</span>
	<span>🐧</span>
	<span>🐦</span>
	<span>🐤</span>
	<span>🐥</span>
	<span>🐣</span>
	<span>🐔</span>
	<span>🐍</span>
	<span>🐢</span>
	<span>🐛</span>
	<span>🐝</span>
	<span>🐜</span>
	<span>🐞</span>
	<span>🐌</span>
	<span>🐙</span>
	<span>🐚</span>
	<span>🐠</span>
	<span>🐟</span>
	<span>🐬</span>
	<span>🐳</span>
	<span>🐋</span>
	<span>🐄</span>
	<span>🐏</span>
	<span>🐀</span>
	<span>🐃</span>
	<span>🐅</span>
	<span>🐇</span>
	<span>🐉</span>
	<span>🐎</span>
	<span>🐐</span>
	<span>🐓</span>
	<span>🐕</span>
	<span>🐖</span>
	<span>🐁</span>
	<span>🐂</span>
	<span>🐲</span>
	<span>🐡</span>
	<span>🐊</span>
	<span>🐫</span>
	<span>🐪</span>
	<span>🐆</span>
	<span>🐈</span>
	<span>🐩</span>
	<span>🐾</span>
	</p>

	<h3>Plants and Flowers Emoji</h3>
	<p class="emoji">
	<span>💐</span>
	<span>🌸</span>
	<span>🌷</span>
	<span>🍀</span>
	<span>🌹</span>
	<span>🌻</span>
	<span>🌺</span>
	<span>🍁</span>
	<span>🍃</span>
	<span>🍂</span>
	<span>🌿</span>
	<span>🌾</span>
	<span>🍄</span>
	<span>🌵</span>
	<span>🌴</span>
	<span>🌲</span>
	<span>🌳</span>
	<span>🌰</span>
	<span>🌱</span>
	<span>🌼</span>
	</p>
	<h3>Science and Weather Emoji</h3>
	<p class="emoji">
	<span>🌐</span>
	<span>🌞</span>
	<span>🌝</span>
	<span>🌚</span>
	<span>🌑</span>
	<span>🌒</span>
	<span>🌓</span>
	<span>🌔</span>
	<span>🌕</span>
	<span>🌖</span>
	<span>🌗</span>
	<span>🌘</span>
	<span>🌜</span>
	<span>🌛</span>
	<span>🌙</span>
	<span>🌍</span>
	<span>🌎</span>
	<span>🌏</span>
	<span>🌋</span>
	<span>🌌</span>
	<span>🌠</span>
	<span>⭐</span>
	<span>☀</span>
	<span>⛅</span>
	<span>☁</span>
	<span>⚡</span>
	<span>☔</span>
	<span>❄</span>
	<span>⛄</span>
	<span>🌀</span>
	<span>🌁</span>
	<span>🌈</span>
	<span>🌊</span>
	</p>

	<h2>Objects Emoji</h2>

	<h3>Toys, Tools and Technology Emoji</h3>
	<p class="emoji">
	<span>🎍</span>
	<span>💝</span>
	<span>🎎</span>
	<span>🎒</span>
	<span>🎓</span>
	<span>🎏</span>
	<span>🎆</span>
	<span>🎇</span>
	<span>🎐</span>
	<span>🎑</span>
	<span>🎃</span>
	<span>👻</span>
	<span>🎅</span>
	<span>🎄</span>
	<span>🎁</span>
	<span>🎋</span>
	<span>🎉</span>
	<span>🎊</span>
	<span>🎈</span>
	<span>🎌</span>
	<span>🔮</span>
	<span>🎥</span>
	<span>📷</span>
	<span>📹</span>
	<span>📼</span>
	<span>💿</span>
	<span>📀</span>
	<span>💽</span>
	<span>💾</span>
	<span>💻</span>
	<span>📱</span>
	<span>☎</span>
	<span>📞</span>
	<span>📟</span>
	<span>📠</span>
	<span>📡</span>
	<span>📺</span>
	<span>📻</span>
	<span>🔊</span>
	<span>🔉</span>
	<span>🔈</span>
	<span>🔇</span>
	<span>🔔</span>
	<span>🔕</span>
	<span>📢</span>
	<span>📣</span>
	<span>⏳</span>
	<span>⌛</span>
	<span>⏰</span>
	<span>⌚</span>
	<span>🔓</span>
	<span>🔒</span>
	<span>🔏</span>
	<span>🔐</span>
	<span>🔑</span>
	<span>🔎</span>
	<span>💡</span>
	<span>🔦</span>
	<span>🔆</span>
	<span>🔅</span>
	<span>🔌</span>
	<span>🔋</span>
	<span>🔍</span>
	<span>🛁</span>
	<span>🛀</span>
	<span>🚿</span>
	<span>🚽</span>
	<span>🔧</span>
	<span>🔩</span>
	<span>🔨</span>
	<span>🚪</span>
	<span>🚬</span>
	<span>💣</span>
	<span>🔫</span>
	<span>🔪</span>
	<span>💊</span>
	<span>💉</span>
	<span>💰</span>
	<span>💴</span>
	<span>💵</span>
	<span>💷</span>
	<span>💶</span>
	<span>💳</span>
	<span>💸</span>
	<span>📲</span>
	</p>
	<h3>Books, Envelopes and Stationery Emoji</h3>
	<p class="emoji">
	<span>📧</span>
	<span>📥</span>
	<span>📤</span>
	<span>✉</span>
	<span>📩</span>
	<span>📨</span>
	<span>📯</span>
	<span>📫</span>
	<span>📪</span>
	<span>📬</span>
	<span>📭</span>
	<span>📮</span>
	<span>📦</span>
	<span>📝</span>
	<span>📄</span>
	<span>📃</span>
	<span>📑</span>
	<span>📊</span>
	<span>📈</span>
	<span>📉</span>
	<span>📜</span>
	<span>📋</span>
	<span>📅</span>
	<span>📆</span>
	<span>📇</span>
	<span>📁</span>
	<span>📂</span>
	<span>✂</span>
	<span>📌</span>
	<span>📎</span>
	<span>✒</span>
	<span>✏</span>
	<span>📏</span>
	<span>📐</span>
	<span>📕</span>
	<span>📗</span>
	<span>📘</span>
	<span>📙</span>
	<span>📓</span>
	<span>📔</span>
	<span>📒</span>
	<span>📚</span>
	<span>📖</span>
	<span>🔖</span>
	<span>📛</span>
	<span>🔬</span>
	<span>🔭</span>
	<span>📰</span>
	</p>
	<h3>Music and Arts Emoji</h3>
	<p class="emoji">
	<span>🎨</span>
	<span>🎬</span>
	<span>🎤</span>
	<span>🎧</span>
	<span>🎼</span>
	<span>🎵</span>
	<span>🎶</span>
	<span>🎹</span>
	<span>🎻</span>
	<span>🎺</span>
	<span>🎷</span>
	<span>🎸</span>
	</p>
	<h3>Sports and Games Emoji</h3>
	<p class="emoji">
	<span>👾</span>
	<span>🎮</span>
	<span>🃏</span>
	<span>🎴</span>
	<span>🀄</span>
	<span>🎲</span>
	<span>🎯</span>
	<span>🏈</span>
	<span>🏀</span>
	<span>⚽</span>
	<span>⚾</span>
	<span>🎾</span>
	<span>🎱</span>
	<span>🏉</span>
	<span>🎳</span>
	<span>⛳</span>
	<span>🚵</span>
	<span>🚴</span>
	<span>🏁</span>
	<span>🏇</span>
	<span>🏆</span>
	<span>🎿</span>
	<span>🏂</span>
	<span>🏊</span>
	<span>🏄</span>
	<span>🎣</span>
	</p>
	<h3>Food and Drink Emoji</h3>
	<p class="emoji">
	<span>☕</span>
	<span>🍵</span>
	<span>🍶</span>
	<span>🍼</span>
	<span>🍺</span>
	<span>🍻</span>
	<span>🍸</span>
	<span>🍹</span>
	<span>🍷</span>
	<span>🍴</span>
	<span>🍕</span>
	<span>🍔</span>
	<span>🍟</span>
	<span>🍗</span>
	<span>🍖</span>
	<span>🍝</span>
	<span>🍛</span>
	<span>🍤</span>
	<span>🍱</span>
	<span>🍣</span>
	<span>🍥</span>
	<span>🍙</span>
	<span>🍘</span>
	<span>🍚</span>
	<span>🍜</span>
	<span>🍲</span>
	<span>🍢</span>
	<span>🍡</span>
	<span>🍳</span>
	<span>🍞</span>
	<span>🍩</span>
	<span>🍮</span>
	<span>🍦</span>
	<span>🍨</span>
	<span>🍧</span>
	<span>🎂</span>
	<span>🍰</span>
	<span>🍪</span>
	<span>🍫</span>
	<span>🍬</span>
	<span>🍭</span>
	<span>🍯</span>
	</p>

	<h3>Fruit and Vegetables Emoji</h3>
	<p class="emoji">
	<span>🍎</span>
	<span>🍏</span>
	<span>🍊</span>
	<span>🍋</span>
	<span>🍒</span>
	<span>🍇</span>
	<span>🍉</span>
	<span>🍓</span>
	<span>🍑</span>
	<span>🍈</span>
	<span>🍌</span>
	<span>🍐</span>
	<span>🍍</span>
	<span>🍠</span>
	<span>🍆</span>
	<span>🍅</span>
	<span>🌽</span>
	</p>

	<h2>Places Emoji</h2>


	<h3>Buildings, Locations and Landmarks Emoji</h3>
	<p class="emoji">
	<span>🏠</span>
	<span>🏡</span>
	<span>🏫</span>
	<span>🏢</span>
	<span>🏣</span>
	<span>🏥</span>
	<span>🏦</span>
	<span>🏪</span>
	<span>🏩</span>
	<span>🏨</span>
	<span>💒</span>
	<span>⛪</span>
	<span>🏬</span>
	<span>🏤</span>
	<span>🌇</span>
	<span>🌆</span>
	<span>🏯</span>
	<span>🏰</span>
	<span>⛺</span>
	<span>🏭</span>
	<span>🗼</span>
	<span>🗾</span>
	<span>🗻</span>
	<span>🌄</span>
	<span>🌅</span>
	<span>🌃</span>
	<span>🗽</span>
	<span>🌉</span>
	<span>🎠</span>
	<span>🎡</span>
	<span>⛲</span>
	<span>🎢</span>
	<span>🚢</span>
	</p>
	<h3>Transport Emoji</h3>
	<p class="emoji">
	<span>⛵</span>
	<span>🚤</span>
	<span>🚣</span>
	<span>⚓</span>
	<span>🚀</span>
	<span>✈</span>
	<span>💺</span>
	<span>🚁</span>
	<span>🚂</span>
	<span>🚊</span>
	<span>🚉</span>
	<span>🚞</span>
	<span>🚆</span>
	<span>🚄</span>
	<span>🚅</span>
	<span>🚈</span>
	<span>🚇</span>
	<span>🚝</span>
	<span>🚋</span>
	<span>🚃</span>
	<span>🚎</span>
	<span>🚌</span>
	<span>🚍</span>
	<span>🚙</span>
	<span>🚘</span>
	<span>🚗</span>
	<span>🚕</span>
	<span>🚖</span>
	<span>🚛</span>
	<span>🚚</span>
	<span>🚨</span>
	<span>🚓</span>
	<span>🚔</span>
	<span>🚒</span>
	<span>🚑</span>
	<span>🚐</span>
	<span>🚲</span>
	<span>🚡</span>
	<span>🚟</span>
	<span>🚠</span>
	<span>🚜</span>
	<span>💈</span>
	<span>🚏</span>
	<span>🎫</span>
	<span>🚦</span>
	<span>🚥</span>
	<span>⚠</span>
	<span>🚧</span>
	<span>🔰</span>
	<span>⛽</span>
	<span>🏮</span>
	<span>🎰</span>
	<span>♨</span>
	<span>🗿</span>
	<span>🎪</span>
	<span>🎭</span>
	<span>📍</span>
	<span>🚩</span>
	</p>


	<h3>Flags Emoji</h3>
	<p class="emoji">
	<span>🇯🇵</span>
	<span>🇰🇷</span>
	<span>🇩🇪</span>
	<span>🇨🇳</span>
	<span>🇺🇸</span>
	<span>🇫🇷</span>
	<span>🇪🇸</span>
	<span>🇮🇹</span>
	<span>🇷🇺</span>
	<span>🇬🇧</span>
	</p>

	<h3>Numbers and Arrows Emoji</h3>
	<p class="emoji">
	<span>1⃣</span>
	<span>2⃣</span>
	<span>3⃣</span>
	<span>4⃣</span>
	<span>5⃣</span>
	<span>6⃣</span>
	<span>7⃣</span>
	<span>8⃣</span>
	<span>9⃣</span>
	<span>0⃣</span>
	<span>🔟</span>
	<span>🔢</span>
	<span>#⃣</span>
	<span>🔣</span>
	<span>⬆</span>
	<span>⬇</span>
	<span>⬅</span>
	<span>➡</span>
	<span>🔠</span>
	<span>🔡</span>
	<span>🔤</span>
	<span>↗</span>
	<span>↖</span>
	<span>↘</span>
	<span>↙</span>
	<span>↔</span>
	<span>↕</span>
	<span>🔄</span>
	<span>◀</span>
	<span>▶</span>
	<span>🔼</span>
	<span>🔽</span>
	<span>↩</span>
	<span>↪</span>
	<span>ℹ</span>
	<span>⏪</span>
	<span>⏩</span>
	<span>⏫</span>
	<span>⏬</span>
	<span>⤵</span>
	<span>⤴</span>
	</p>
	<h3>Text and Labels Emoji</h3>
	<p class="emoji">
	<span>🆗</span>
	<span>🔀</span>
	<span>🔁</span>
	<span>🔂</span>
	<span>🆕</span>
	<span>🆙</span>
	<span>🆒</span>
	<span>🆓</span>
	<span>🆖</span>
	<span>📶</span>
	<span>🎦</span>
	<span>🈁</span>
	<span>🈯</span>
	<span>🈳</span>
	<span>🈵</span>
	<span>🈴</span>
	<span>🈲</span>
	<span>🉐</span>
	<span>🈹</span>
	<span>🈺</span>
	<span>🈶</span>
	<span>🈚</span>
	<span>🚻</span>
	<span>🚹</span>
	<span>🚺</span>
	<span>🚼</span>
	<span>🚾</span>
	<span>🚰</span>
	<span>🚮</span>
	<span>🅿</span>
	<span>♿</span>
	<span>🚭</span>
	<span>🈷</span>
	<span>🈸</span>
	<span>🈂</span>
	<span>Ⓜ</span>
	<span>🛂</span>
	<span>🛄</span>
	<span>🛅</span>
	<span>🛃</span>
	<span>🉑</span>
	<span>㊙</span>
	<span>㊗</span>
	<span>🆑</span>
	<span>🆘</span>
	<span>🆔</span>
	<span>🚫</span>
	<span>🔞</span>
	<span>📵</span>
	<span>🚯</span>
	<span>🚱</span>
	<span>🚳</span>
	<span>🚷</span>
	<span>🚸</span>
	<span>⛔</span>
	<span>✳</span>
	<span>❇</span>
	<span>❎</span>
	<span>✅</span>
	<span>✴</span>
	<span>💟</span>
	<span>🆚</span>
	<span>📳</span>
	<span>📴</span>
	<span>🅰</span>
	<span>🅱</span>
	<span>🆎</span>
	<span>🅾</span>
	<span>💠</span>
	<span>➿</span>
	<span>♻</span>
	</p>
	<h3>Astrological Zodiac Signs Emoji</h3>
	<p class="emoji">
	<span>♈</span>
	<span>♉</span>
	<span>♊</span>
	<span>♋</span>
	<span>♌</span>
	<span>♍</span>
	<span>♎</span>
	<span>♏</span>
	<span>♐</span>
	<span>♑</span>
	<span>♒</span>
	<span>♓</span>
	<span>⛎</span>
	</p>
	<h3>Other Symbols and Characters Emoji</h3>
	<p class="emoji">
	<span>🔯</span>
	<span>🏧</span>
	<span>💹</span>
	<span>💲</span>
	<span>💱</span>
	<span>©</span>
	<span>®</span>
	<span>™</span>
	<span>❌</span>
	<span>‼</span>
	<span>⁉</span>
	<span>❗</span>
	<span>❓</span>
	<span>❕</span>
	<span>❔</span>
	<span>⭕</span>
	<span>🔝</span>
	<span>🔚</span>
	<span>🔙</span>
	<span>🔛</span>
	<span>🔜</span>
	<span>🔃</span>
	<span>🕛</span>
	<span>🕧</span>
	<span>🕐</span>
	<span>🕜</span>
	<span>🕑</span>
	<span>🕝</span>
	<span>🕒</span>
	<span>🕞</span>
	<span>🕓</span>
	<span>🕟</span>
	<span>🕔</span>
	<span>🕠</span>
	<span>🕕</span>
	<span>🕖</span>
	<span>🕗</span>
	<span>🕘</span>
	<span>🕙</span>
	<span>🕚</span>
	<span>🕡</span>
	<span>🕢</span>
	<span>🕣</span>
	<span>🕤</span>
	<span>🕥</span>
	<span>🕦</span>
	<span>✖</span>
	<span>➕</span>
	<span>➖</span>
	<span>➗</span>
	<span>♠</span>
	<span>♥</span>
	<span>♣</span>
	<span>♦</span>
	<span>💮</span>
	<span>💯</span>
	<span>✔</span>
	<span>☑</span>
	<span>🔘</span>
	<span>🔗</span>
	<span>➰</span>
	<span>〰</span>
	<span>〽</span>
	<span>🔱</span>
	<span>◼</span>
	<span>◻</span>
	<span>◾</span>
	<span>◽</span>
	<span>▪</span>
	<span>▫</span>
	<span>🔺</span>
	<span>🔲</span>
	<span>🔳</span>
	<span>⚫</span>
	<span>⚪</span>
	<span>🔴</span>
	<span>🔵</span>
	<span>🔻</span>
	<span>⬜</span>
	<span>⬛</span>
	<span>🔶</span>
	<span>🔷</span>
	<span>🔸</span>
	<span>🔹</span>
	</p>
</div><!-- fine emoji -->

</div>

</div>

<div class="oziochat-chat-right oziochat-chat-widget-users">
<ul class="oziochat-chat-users-stream">
</ul>
</div>


</div>



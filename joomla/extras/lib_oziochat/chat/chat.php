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

jimport('joomla.log.log');

//require_once('lib/squeeks-Pusher-PHP/lib/Pusher.php');
require_once('lib/pusher-http-php-2.2.1/lib/Pusher.php');
//require_once('PusherActivity.php');
/*
define('FACEBOOK_SDK_V4_SRC_DIR', __DIR__.'/lib/Facebook/');
require FACEBOOK_SDK_V4_SRC_DIR . 'autoload.php';

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
use Facebook\FacebookJavaScriptLoginHelper;
*/



class OzioChatServerSideChat
{
	protected $cachedir;
	public $CacheLifetime;
	
	public function returnError($msg,$http_code=403){
		header('Cache-Control: no-cache, must-revalidate');
		if ($http_code==400){
			header("HTTP/1.0 400 Bad Request");
		}else{
			header("HTTP/1.0 403 Forbidden");
		}
		echo($msg);
		JFactory::getApplication()->close();
		die();
		
	}
	public function redirect($redirect_url,$error=""){
		JFactory::getApplication()->redirect($redirect_url);
		JFactory::getApplication()->close();
		die();
	}
	public function twitter_verify_and_die($tw_consumer_key,$tw_consumer_secret){
		//twitter_verify
		//error_log(var_export($_REQUEST,true)."\n",3,'C:\workspace\php-errors.log');
		
		$redirect_url=base64_decode($_REQUEST['return']);
		
		$session =JFactory::getSession();
		$oauth_response=$session->get("oc_twitter_oauth_response");
		if (empty($oauth_response) || empty($oauth_response['oauth_token']) || empty($oauth_response['oauth_token_secret'])){
			$this->redirect($redirect_url,"Twitter: missing oc_twitter_oauth_response");
		}
		//error_log(var_export($oauth_response,true)."\n",3,'C:\workspace\php-errors.log');
		if (empty($_REQUEST['oauth_token']) || $oauth_response['oauth_token']!=$_REQUEST['oauth_token']){
			$this->redirect($redirect_url,"Twitter: not matching oauth_token");
		}
		//access_token
		
		require_once __DIR__.'/lib/TwitterOAuth-2.1.2/autoloader.php'; // or wherever autoload.php is located
		
					
		date_default_timezone_set('UTC');

		$credentials = array(
			'consumer_key' => $tw_consumer_key,
			'consumer_secret' => $tw_consumer_secret,
			'oauth_token'=>$oauth_response['oauth_token'],
			//'oauth_token_secret' => $oauth_response['oauth_token_secret'],
		);

		$serializer = new TwitterOAuth\Serializer\ArraySerializer();

		$auth = new TwitterOAuth\Auth\SingleUserAuth($credentials, $serializer);

		$params = array(
			'oauth_verifier' => $_REQUEST['oauth_verifier'],
		);
		//error_log("go with post access_token\n",3,'C:\workspace\php-errors.log');

		try{
			$response = $auth->post('oauth/access_token', $params);
		}catch(Exception $e){
			$this->redirect($redirect_url,"Twitter error:  ".$e->getCode()." - ".$e->getMessage());
		}			
		//error_log(var_export($response,true)."\n",3,'C:\workspace\php-errors.log');
		
		if (empty($response) || empty($response['oauth_token']) || empty($response['oauth_token_secret'])){
			$this->redirect($redirect_url,"Twitter error:  missing access_token response");
		}
		$session->clear("oc_twitter_oauth_response");
		$session->set("oc_twitter_oauth_access_token_response",$response);
		
		$this->redirect($redirect_url);
		
	}
	
	public function twitter_get_and_die($tw_consumer_key,$tw_consumer_secret){
		//twitter_get
		//error_log("twitter_get\n",3,'C:\workspace\php-errors.log');
		require_once __DIR__.'/lib/TwitterOAuth-2.1.2/autoloader.php'; // or wherever autoload.php is located
		
					
		date_default_timezone_set('UTC');

		$credentials = array(
			'consumer_key' => $tw_consumer_key,
			'consumer_secret' => $tw_consumer_secret,
		);

		$serializer = new TwitterOAuth\Serializer\ArraySerializer();

		$auth = new TwitterOAuth\Auth\SingleUserAuth($credentials, $serializer);

		$params = array(
			'oauth_callback' => $_POST['tw_endpoint'].'&tw_verify=1&return='.base64_encode($_POST['tw_return']),
		);
		//error_log($params['oauth_callback']."\n",3,'C:\workspace\php-errors.log');
		//error_log("go with post\n",3,'C:\workspace\php-errors.log');

		try{
			$response = $auth->post('oauth/request_token', $params);
		}catch(Exception $e){
			$this->returnError("Twitter error: ".$e->getCode()." - ".$e->getMessage());
		}
		if (!$response['oauth_callback_confirmed']){
			$this->returnError("Twitter error: oauth callback not confirmed");
		}
		//error_log(var_export($response,true)."\n",3,'C:\workspace\php-errors.log');
		
		//metto in sessione il token e il secret
		$session =JFactory::getSession();
		$session->set("oc_twitter_oauth_response",$response);
		
		
		$return=array(
			'loginurl'=>'https://api.twitter.com/oauth/authenticate?oauth_token='.$response['oauth_token']
		);
		
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		echo(json_encode($return));
		//die();
		JFactory::getApplication()->close();
		return;		
	}
	
	public function Process()
	{
		
		$this->cachedir = JPATH_ROOT . "/cache/" . get_class($this);
		file_exists($this->cachedir) or mkdir($this->cachedir);
		//$this->CacheLifetime = intval($this->Params->get("cache_lifetime", 2)) * 60;  // Converte da minuti in secondi
		$this->CacheLifetime = 2 * 60;  // Converte da minuti in secondi 2 minuti
		
		$tw_consumer_key=trim($this->Params->get("twitter_consumer_key", ""));
		$tw_consumer_secret=trim($this->Params->get("twitter_consumer_secret", ""));
		
		
		
		$app_id=trim($this->Params->get("pusher_app_id", ""));
		$app_key=trim($this->Params->get("pusher_app_key", ""));
		$app_secret=trim($this->Params->get("pusher_app_secret", ""));
		
		/*
		if (isset($_POST['channel_name']) && isset($_POST['socket_id']) && isset($_POST['only_get_num']) && $_POST['only_get_num']){
			$verify_channel=$_POST['channel_name'];//private- presence-
			$verify_socketid=$_POST['socket_id'];
			
			$parts=explode('-',$verify_channel);
			$channel_type=$parts[0];
			
			if ($channel_type == 'presence'){
			
				$pusher = new Pusher($app_key, $app_secret, $app_id);
				$presence_data = array(
					'displayName' => 'hidden',
					'objectType' => 'person',
					'image' => array('url'=>''),
					'userid'=>'hidden-hidden',
					'link'=>''
				);
				
				echo $pusher->presence_auth($verify_channel, $verify_socketid, $presence_data['userid'], $presence_data);

				JFactory::getApplication()->close();
			}
		}
		*/
		if (isset($_POST['only_get_num']) && $_POST['only_get_num']){
			if (!isset($_POST['channel_name'])){
				$this->returnError("Invalid channel");
			}
			$verify_channel=$_POST['channel_name'];//private- presence-
			
			$parts=explode('-',$verify_channel);
			$channel_type=$parts[0];
			
			if ($channel_type == 'presence'){
			
				//Caching! QUi
				
				$cache_expired = true;
				$num_users_connected= 0;
				
				$cachefile=$this->cachedir . "/" .$this->getHash(array($app_key,$verify_channel),array($app_secret,$app_id));
				if (file_exists($cachefile)){

					$age = time() - filemtime($cachefile);
					$lt=$this->CacheLifetime;
					
					if ($age < $lt){
						$num_users_connected = file_get_contents($cachefile);
						$cache_expired = false;
					}
				}
				if ($cache_expired){
					
				
					$pusher = new Pusher($app_key, $app_secret, $app_id);
					
					//echo json_encode($pusher->get('/channels/'.$verify_channel.'/members'));
					
					$users = $pusher->get('/channels/'.$verify_channel.'/users');
					$users = json_decode($users['body'],true);
					
					$num_users_connected = count($users['users']);
					
					file_put_contents($cachefile,$num_users_connected);
				}
				
				//error_log(var_export($users,true)."\n",3,'C:\workspace\php-errors.log');
				echo json_encode(array('c'=>$num_users_connected));

				JFactory::getApplication()->close();
			}else{
				$this->returnError("Invalid channel");
			}
		}
		
		

		$anonymous_login=intval($this->Params->get("anonymous_login", "1"))==1;
		$joomla_login=intval($this->Params->get("joomla_login", "1"))==1;
		$facebook_login=false;
		$googleplus_login=false;
		$twitter_login=false;
		
		$fb_app_id=trim($this->Params->get("facebook_app_id", ""));
		$fb_app_secret=trim($this->Params->get("facebook_app_secret", ""));
		if (!empty($fb_app_id) && !empty($fb_app_secret)){
			$facebook_login=true;
		}
		
		$gp_client_id=trim($this->Params->get("googleplus_client_id", ""));
		$gp_client_secret=trim($this->Params->get("googleplus_client_secret", ""));
		if (!empty($gp_client_id) && !empty($gp_client_secret)){
			$googleplus_login=true;
		}

		if (!empty($tw_consumer_key) && !empty($tw_consumer_secret)){
			$twitter_login=true;
		}

		
		$available_logins=array();
		if ($anonymous_login){
			$available_logins[]='anonymous';
		}
		if ($joomla_login){
			$available_logins[]='joomla';
		}
		if ($facebook_login){
			$available_logins[]='facebook';
		}
		if ($googleplus_login){
			$available_logins[]='googleplus';
		}
		if ($twitter_login){
			$available_logins[]='twitter';
		}
		

		if ($twitter_login && isset($_REQUEST['tw_verify'])){
			$this->twitter_verify_and_die($tw_consumer_key,$tw_consumer_secret);

		}
		
		if ($twitter_login && isset($_POST['tw_get'])){
			$this->twitter_get_and_die($tw_consumer_key,$tw_consumer_secret);
		}
		
		if (isset($_POST['tw_get_status'])){
			$this->twitterLogin(true);
			if ($this->logged_in){
				header('Cache-Control: no-cache, must-revalidate');
				header('Content-type: application/json');
				echo(json_encode($this->actor));
				JFactory::getApplication()->close();
				die();
			}else{
				$this->returnError("Not logged in");
			}			
		}
				
		
		$this->logged_in=false;
		$this->actor=array();
		$session = JFactory::getSession();
		if (isset($_POST['logout'])){
			$session_var_name='oc_user_id_'.$_POST['logout'];
			if ($session->has($session_var_name)){
				$session->clear($session_var_name);
			}
			if ($_POST['logout']=='twitter' && $session->has("oc_twitter_oauth_access_token_response")){
				$session->clear("oc_twitter_oauth_access_token_response");
			}
			
			JFactory::getApplication()->close();
			return;
			
		}
		$oc_logged_in='';
		if (!empty($_COOKIE['oc_logged_in'])){
			$oc_logged_in=$_COOKIE['oc_logged_in'];
		}
		if (!empty($_POST['loginonly'])){
			$oc_logged_in=$_POST['loginonly'];
		}
		
		if (!empty($oc_logged_in) && in_array($oc_logged_in,$available_logins)){
			$session_var_name='oc_user_id_'.$oc_logged_in;
			if (isset($_COOKIE[$session_var_name]) && $session->has($session_var_name) ){
				$actor=$session->get($session_var_name);
				if ($actor['userid']==$_COOKIE[$session_var_name]){
					$this->logged_in=true;
					$this->actor=$actor;
				}
			}
					
			if (!$this->logged_in){
				if ($oc_logged_in=='googleplus'){
					$this->googleplusLogin();
				}else if ($oc_logged_in=='facebook'){
					$this->facebookLogin();
				}else if ($oc_logged_in=='joomla'){
					$this->joomlaLogin();
				}else if ($oc_logged_in=='twitter'){
					$this->twitterLogin();
				}else if ($oc_logged_in=='anonymous'){
					$this->anonymousLogin($_COOKIE['oc_anon_name'],$_COOKIE['oc_anon_email']);
				}
				if ($this->logged_in){
					$session->set($session_var_name,$this->actor);
				}
			}
		}
		
		if (isset($_POST['loginonly'])){
			if ($this->logged_in){
				header('Cache-Control: no-cache, must-revalidate');
				header('Content-type: application/json');
				echo(json_encode(array('OK')));
				//die();
				JFactory::getApplication()->close();
				return;
			}else{
				$this->returnError("Not logged in");
			}
			
		}
					
		
		if (isset($_POST['channel_name']) && isset($_POST['socket_id'])){
			//authendpoint private or presence 

			$verify_channel=$_POST['channel_name'];//private- presence-
			$verify_socketid=$_POST['socket_id'];
			
			$parts=explode('-',$verify_channel);
			$channel_type=$parts[0];
			
			if (!in_array($channel_type,array('private','presence'))){
				$this->returnError('invalid private or presence channel');
			}
			
			
			if ($this->logged_in){
				$pusher = new Pusher($app_key, $app_secret, $app_id);
				$presence_data = $this->actor;
				
				if ($channel_type=='presence'){
					echo $pusher->presence_auth($verify_channel, $verify_socketid, $this->actor['userid'], $presence_data);
				}else{
					echo $pusher->socket_auth($verify_channel, $verify_socketid);
				}

				JFactory::getApplication()->close();
  
			}else{
				$this->returnError("Forbidden");
			}
			
			return;
		}
		
		if (isset($_POST['chat_info'])){
			//send message
			$channel_name="private-".$this->get_channel_name($this->Params->get("pusher_channel_name", "channel1"));
			

			$result = array();//'activity' => $data, 'pusherResponse' => $response);
			
			
			//if (isset($_POST['chat_info']['logout']) && isset($_POST['chat_info']['logout']['logout_from'])){
				//non devo fare nulla
			//	$this->logged_in=false;
			//}
			
			
			if ($this->logged_in && isset($_POST['chat_info']['text']) && !empty($_POST['chat_info']['text'])){
				//creo il messaggio
				
				date_default_timezone_set('UTC');
				$data = array(
				  'id' => uniqid(),
				  'body' => mb_substr($_POST['chat_info']['text'],0,300),
				  'published' => date('r'),
				  'type' => 'chat-message',
				  'actor' => $this->actor
				);
				
				$data['actor']['displayName']=mb_substr($data['actor']['displayName'],0,30);

				//invio del messaggio
				
				$pusher = new Pusher($app_key, $app_secret, $app_id);
				$response = $pusher->trigger($channel_name, 'chat_message', $data);

				$result = array('activity' => $data, 'pusherResponse' => $response);
				
			}
			
			//output result
			
			header('Cache-Control: no-cache, must-revalidate');
			header('Content-type: application/json');
			echo(json_encode($result));
			//die();
			JFactory::getApplication()->close();
			return;
		}

		//se arrivo qui non è ne una presence ne una send
		$this->returnError('invalid data',400);		
			
	}
	
	private function getHash($public_array,$pivate_array=array()){
		$parts=array();
		foreach($public_array as $p){
			$parts[]=substr(preg_replace('/[^a-zA-Z0-9-]/', '_', $p),0,4);
		}
		$parts[]=substr(md5(implode('',array_merge($public_array,$pivate_array))),0,10);
		$hash=implode('-',$parts);
		return $hash;
	}
	
	
	private function anonymousLogin($nickname,$email){
		if (!empty($nickname)){
			$this->logged_in=true;
			
			$this->actor=array(
				'displayName' => $nickname,
				'objectType' => 'person',
				'image' => array('url'=>$this->get_gravatar($email)),
				'userid'=> 'anonymous-'.$nickname,
				'link'=>'',
			);
		}
		
	}
	private function joomlaLogin(){
		$user = JFactory::getUser();
		$this->logged_in=!$user->guest;
		if ($this->logged_in){
			$this->actor=array(
				'displayName' => $user->name,
				'objectType' => 'person',
				'image' => array('url'=>JURI::base(true) . "/media/oziochat/images/nophoto.png"),
				'userid'=>'joomla-'.$user->id,
				'link'=>'',
			);
		}else{
			$this->returnError("Joomla Not logged in");
		}
		
	}
	private function twitterLogin($tw_get_status=false){
		//verifico le credenziali
		$tw_consumer_key=trim($this->Params->get("twitter_consumer_key", ""));
		$tw_consumer_secret=trim($this->Params->get("twitter_consumer_secret", ""));
		$session =JFactory::getSession();
		
		$response=$session->get("oc_twitter_oauth_access_token_response");
		
		if (empty($response) || empty($response['oauth_token']) || empty($response['oauth_token_secret'])){
			if ($tw_get_status){
				header('Cache-Control: no-cache, must-revalidate');
				header('Content-type: application/json');
				echo(json_encode(array('error'=>"Twitter Not logged in")));
				JFactory::getApplication()->close();
				return;		
			}else{
				$this->returnError("Twitter Not logged in");
			}
		}
		
		require_once __DIR__.'/lib/TwitterOAuth-2.1.2/autoloader.php'; // or wherever autoload.php is located
		date_default_timezone_set('UTC');
		
		//ottengo le informazioni sull'utente
		
		
		
		$credentials_user = array(
			'consumer_key' => $tw_consumer_key,
			'consumer_secret' => $tw_consumer_secret,
			'oauth_token'=>$response['oauth_token'],
			'oauth_token_secret' => $response['oauth_token_secret'],
		);

		$serializer_user = new TwitterOAuth\Serializer\ArraySerializer();

		$auth_user = new TwitterOAuth\Auth\SingleUserAuth($credentials_user, $serializer_user);


		$params_user = array();
		//error_log("go with get account/verify_credentials\n",3,'C:\workspace\php-errors.log');

		try{
			$response_user = $auth_user->get('account/verify_credentials', $params_user);
		}catch(Exception $e){
			//TODO redirect error
			$this->returnError("Twitter error: ".$e->getCode()." - ".$e->getMessage());
		}			
		//error_log(var_export($response_user,true)."\n",3,'C:\workspace\php-errors.log');
		
		$this->logged_in=true;
		$this->actor=array(
			'displayName' => $response_user['screen_name'],
			'objectType' => 'person',
			'image' => array('url'=>$response_user['profile_image_url']),
			'userid'=>'twitter-'.$response_user['id_str'],
			'link'=>'https://twitter.com/'.$response_user['screen_name'],
		);
		
	}
	private function googleplusLogin(){
		if (empty($_COOKIE['oc_googleplus_code'])){
			return;
		}
		
		$googleplus_code=$_COOKIE['oc_googleplus_code'];
		
		$client_id=trim($this->Params->get("googleplus_client_id", ""));
		$client_secret=trim($this->Params->get("googleplus_client_secret", ""));
		
		require_once __DIR__.'/lib/google-api-php-client-1.1.2/autoload.php'; // or wherever autoload.php is located

		  $client = new Google_Client();
		
			$client->setClientId($client_id);
			$client->setClientSecret($client_secret);	
			$client->setRedirectUri('postmessage');

			//if (true){
				try{
					$client->authenticate($googleplus_code);
					
					//$token = json_decode($client->getAccessToken());
					
					$googlePlus = new Google_Service_Plus($client);
					$userProfile = $googlePlus->people->get('me');			
			    } catch (Exception $e) {
					$this->returnError("GPlus error: ".$e->getCode()." - ".$e->getMessage());
				}

				$this->logged_in=true;

				//error_log(var_export($userProfile,true)."\n",3,'C:\workspace\php-errors.log');
				
				$this->actor=array(
					'displayName' => $userProfile->displayName,
					'objectType' => 'person',
					'image' => array('url'=>$userProfile->getImage()->url),
					'userid'=>'googleplus-'.$userProfile->id,
					'link'=>$userProfile->url,
					//'link'=>'https://plus.google.com/'.$userProfile->id.'/posts',
				);
				
			//}else{
				//cookie error
			//}
	}
	
	private function facebookLogin(){
		require __DIR__.'/lib/facebook-php-sdk-3.2.3/src/facebook.php';

		
		$app_id=trim($this->Params->get("facebook_app_id", ""));
		$app_secret=trim($this->Params->get("facebook_app_secret", ""));
		
		$facebook = new Facebook(array(
		  'appId'  => $app_id,
		  'secret' => $app_secret,
		));

		// See if there is a user from a cookie
		$user = $facebook->getUser();
		
		$user_profile=null;
		$picture_profile=null;

		if ($user) {
		  try {
			// Proceed knowing you have a logged in user who's authenticated.
			$user_profile = $facebook->api('/me/?fields=id,name,picture,link');
		  } catch (FacebookApiException $e) {
			$this->returnError("FB error: ".$e->getCode()." - ".$e->getMessage());
		  }
		}else{
			$this->returnError("FB Not logged in");
		}
		
		$this->logged_in=true;
		//error_log(var_export($user_profile,true)."\n",3,'C:\workspace\php-errors.log');

		$this->actor=array(
			'displayName' => $user_profile['name'],
			'objectType' => 'person',
			'image' => array('url'=>$user_profile['picture']['data']['url']),
			'userid'=>'facebook-'.$user_profile['id'],
			'link'=>$user_profile['link']
		);
		
	}
	/*
	private function facebookLogin($login){

		FacebookSession::setDefaultApplication('APPID','SECRET');//APP_ID e APP_SECRET !!!

		$helper = new FacebookJavaScriptLoginHelper();
		try {
			$session = $helper->getSession();
		} catch(FacebookRequestException $ex) {
			// When Facebook returns an error
		} catch(\Exception $ex) {
			// When validation fails or other local issues
		}
		if ($session) {
			// Logged in
			// Get the GraphUser object for the current user:

			try {
				$fbreq=new FacebookRequest($session, 'GET', '/me');
				$me = $fbreq->execute()->getGraphObject(GraphUser::className());
				
				//error_log(var_export($me,true),3,'C:\workspace\php-errors.log');
			} catch (FacebookRequestException $e) {
				// The Graph API returned an error
				echo "Exception occured, code: " . $e->getCode();
				echo " with message: " . $e->getMessage();
			} catch (\Exception $e) {
				// Some other error occurred
				echo "Exception occured, code: " . $e->getCode();
				echo " with message: " . $e->getMessage();
			}
		}
	}
	*/
	/*
	public function ProcessOld()
	{
		
		$app_id=$this->Params->get("pusher_app_id", "");
		$app_key=$this->Params->get("pusher_app_key", "");
		$app_secret=$this->Params->get("pusher_app_secret", "");
		$channel_name=$this->get_channel_name($this->Params->get("pusher_channel_name", "channel1"));
		
		
		date_default_timezone_set('UTC');

		$chat_info = $_POST['chat_info'];

		//$channel_name = null;

		if( !isset($_POST['chat_info']) ){
		  header("HTTP/1.0 400 Bad Request");
		  echo('chat_info must be provided');
		}

		//if( !isset($_SERVER['HTTP_REFERER']) ) {
		//  header("HTTP/1.0 400 Bad Request");
		//  echo('channel name could not be determined from HTTP_REFERER');
		//}

		//$channel_name = $this->get_channel_name($_SERVER['HTTP_REFERER']);
		$options = $this->sanitise_input($chat_info);

		$activity = new PusherActivity('chat-message', $options['text'], $options);
		$data = $activity->getMessage();

		$pusher = new Pusher($app_key, $app_secret, $app_id, false);//debug a false
		$response = $pusher->trigger($channel_name, 'chat_message', $data, null, true);

		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$result = array('activity' => $data, 'pusherResponse' => $response);
		echo(json_encode($result));

		//die();
		JFactory::getApplication()->close();
	}

	

	function sanitise_input($chat_info) {
	  $email = isset($chat_info['email'])?$chat_info['email']:'';
	  
	  $options = array();
	  $options['displayName'] = substr(htmlspecialchars($chat_info['nickname']), 0, 30);
	  $options['text'] = substr(htmlspecialchars($chat_info['text']), 0, 300);
	  $options['email'] = substr(htmlspecialchars($email), 0, 100);
	  $options['get_gravatar'] = true;
	  return $options;
	}
	*/
  // from: http://en.gravatar.com/site/implement/images/php/
  /**
   * Get either a Gravatar URL or complete image tag for a specified email address.
   *
   * @param string $email The email address
   * @param string $s Size in pixels, defaults to 80px [ 1 - 512 ]
   * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
   * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
   * @param boole $img True to return a complete IMG tag False for just the URL
   * @param array $atts Optional, additional key/value attributes to include in the IMG tag
   * @return String containing either just a URL or a complete image tag
   * @source http://gravatar.com/site/implement/images/php/
   */
  private function get_gravatar( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {
  	$url = 'http://www.gravatar.com/avatar/';
  	$url .= $this->get_email_hash( $email );
  	$url .= "?s=$s&d=$d&r=$r";
  	if ( $img ) {
  		$url = '<img src="' . $url . '"';
  		foreach ( $atts as $key => $val )
  			$url .= ' ' . $key . '="' . $val . '"';
  		$url .= ' />';
  	}
  	return $url;
  }
  private function get_email_hash($email) {
    return md5( strtolower( trim( $email ) ) );
  }
	function get_channel_name($http_referer) {
	  // not allowed :, / % #
	  $pattern = "/(\W)+/";
	  $channel_name = preg_replace($pattern, '-', $http_referer);
	  return $channel_name;
	}
  
	
}



?>
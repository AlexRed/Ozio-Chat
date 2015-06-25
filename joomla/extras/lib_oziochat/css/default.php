<?php defined('_JEXEC') or die('Restricted access');
/*
Do not edit this file or it will be overwritten at the first upgrade
Copy from this source using another file name and select your own new created css in the module or plugin options
*/
$owner = JRequest::getVar("owner", "", "GET");
$id = JRequest::getVar("id", "", "GET");
?>

.oziochat-chat-widget-bubble{
	position:absolute;
	height: 78px;
	top: -78px;
	right: 0;
	cursor: pointer;
	
}

.oziochat-chat-widget-title {
  border-bottom: 1px solid #EBEBEB;
  margin-left:-10px;
  margin-right:-10px;
}


.oziochat-chat-widget-title > label {
	white-space: nowrap;
	float:left;
	text-overflow: ellipsis;
	margin-left:10px;
	margin-right:10px;
}

.oziochat-chat-widget-wnd-btn{
	margin-right:5px;
	border: 1px solid #EBEBEB;
	background-color: #EBEBEB;
	float: right;

	font-weight: bold;
	text-align: center;
	line-height: 18px;
	min-height: 18px;

	padding-left: 8px;
	padding-right: 8px;
	
	cursor: pointer;
	
	-webkit-border-radius: 0 0 6px 6px;
	-moz-border-radius: 0 0 6px 6px;
	border-radius: 0 0 6px 6px;
	
}
.oziochat-chat-widget-wnd-btn.oziochat-chat-widget-wnd-last{
	margin-right:10px;
}

.oziochat-chat-widget-wnd-btn:hover{
  color: #fff;
  background-color: #2b2b2b;
  border-color: rgba(0,0,0,0.2);
}


.oziochat-chat-widget-msg-btn{
	margin-right:5px;
	border: 1px solid #EBEBEB;
	background-color: #EBEBEB;
	float: right;

	font-weight: bold;
	text-align: center;
	line-height: 18px;
	
	width: 24px;
	cursor: pointer;
	
	-webkit-border-radius: 3px;
	-moz-border-radius: 3px;
	border-radius: 3px;
	
	margin-top: -1px;
	
}

.oziochat-chat-widget-msg-btn.oziochat-chat-widget-msg-btn-last{
	margin-right:0;
}

.oziochat-chat-widget-msg-btn:hover{
  color: #fff;
  background-color: #2b2b2b;
  border-color: rgba(0,0,0,0.2);
}

.oziochat-chat-widget {
  position: fixed;
  bottom: 0;
  z-index: <?php echo intval($this->Params->get('zindex', '999')); ?>;
  
  font-size: 13px;
  
  width: 300px;
  
  background-color: whiteSmoke;
  border: 1px solid #EEE;
  color: #333;
  margin-bottom: 0;
  -webkit-border-radius: 6px 6px 6px 6px;
  -moz-border-radius: 6px 6px 6px 6px;
  border-radius: 6px 6px 6px 6px;
  padding-left: 10px;
  padding-right: 10px;
}

.oziochat-chat-widget input {
  width: 286px;
  margin:0;
}


.oziochat-chat-widget label {
  font-weight: bold;
  text-align: left;
  padding-top: 6px;
  line-height: 18px;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space: nowrap;
}


.oziochat-chat-widget-current-user,.oziochat-chat-widget-user  {
  border-bottom: 1px solid #EBEBEB;
  margin-bottom: 5px;
  padding-bottom: 5px;
  margin-top: 5px;
  overflow: auto;
  clear: both;
}

.oziochat-chat-widget-current-user-image {
  float: left;
  height: 32px;
  overflow: hidden;
  width: 32px;
  margin-top: 3px;
}

.oziochat-chat-widget-current-user-name {
  margin-left: 42px;
  font-weight: bold;
  font-size: 20px;
  line-height: 40px;
}

.oziochat-chat-widget-header input {
  display: block;
}

.oziochat-chat-widget-input {
  position: relative;
}

.oziochat-chat-widget textarea {
  width: 286px;
  height: 40px;
}

.oziochat-chat-widget-send-btn {
  width: 50px;
  height: 40px;
  
  font-size: 11px;
  line-height: normal;
  padding: 5px 10px 5px;
  -webkit-border-radius: 6px;
  -moz-border-radius: 6px;
  border-radius: 6px;
  vertical-align: top;
  
  color: white;
  background-color: #0064CD;

  -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
  -moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
  border: 1px solid #CCC;
}

.oziochat-chat-widget-footer {
  border-top: 1px solid #EBEBEB;
  margin-top: 5px;
  margin-bottom: 5px;
}

/* activity streams - messages */
.oziochat-chat-widget ul.oziochat-chat-activity-stream {
  list-style: none;
  margin: 0;
  padding: 0;
  
  background-color: white;
  min-height: 80px;
  max-height: 300px;
  /*margin-right: 10px;*/
}

.oziochat-chat-widget .oziochat-chat-emoji {
  background-color: white;
  overflow:auto;
  max-height:100px;
  padding: 4px 6px 4px 6px;

}
.oziochat-chat-widget .oziochat-chat-emoji span{
	-webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;  
	cursor: pointer;
	display: inline-block;
	width:20px;
	height:20px;
	vertical-align:middle;
	position:relative;
}
.oziochat-chat-widget .oziochat-chat-emoji span:hover{
	font-size:20px;	
}

.oziochat-chat-widget .oziochat-chat-emoji h2{
	font-size: 18px;
	line-height:20px;
}
.oziochat-chat-widget .oziochat-chat-emoji h3{
	font-size: 14px;
	line-height:16px;
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream li.waiting {
  height: 100%;
  font-weight: bold;
  text-align: center;
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream {
  overflow: auto;
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream li {
  position: relative;
  border-bottom: 1px solid #EBEBEB;
  clear: both;
  display: block;
  outline: none;
  margin-top: -1px;
  border-top: 1px solid transparent;
  overflow: auto;
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream li.chat-message {
  background-color: #DDF4FB;
  border-color: #C6EDF9;
  color: #404040;
  text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream li div.oc-stream-item-content {
  padding: 5px 15px;
  font-size: 12px;
  position: relative;
  zoom: 1;
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream li .oc-image {
  float: left;
  height: 48px;
  overflow: hidden;
  width: 48px;
  margin-top: 3px;
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream li .oc-screen-name {
  font-weight: bold;
  color: #333!important;
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream li .oc-linked-text {
  color: #333!important;
  text-decoration: underline;
}


.oziochat-chat-widget ul.oziochat-chat-activity-stream li .oc-content {
  margin-left: 58px;
  min-height: 48px;
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream li .oc-activity-row {
  display: block;
  position: relative;
  line-height: 15px;
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream li .oc-text {
  padding: 0;
  font-family: Arial,"Helvetica Neue",sans-serif;
  line-height: 19px;
  word-wrap: break-word;
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream li .oc-timestamp {
  color: #999!important;
  font-size: 9px;
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream li .oc-activity-actions span:first-child {
  margin-left: 15px;
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream li .oc-activity-actions {
  color: #999!important;
  font-size: 9px;
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream li .oc-activity-actions span b {
  font-weight: normal;
}


/* users streams - messages */
.oziochat-chat-widget ul.oziochat-chat-users-stream {
  list-style: none;
  margin: 0;
  padding: 0;
  
  background-color: white;
  /*min-height: 100px;
  max-height: 300px;*/
  /*margin-right: 10px;*/
  position:absolute;
  top:0;
  bottom:0;
  left:0;
  right:0;
}

.oziochat-chat-widget ul.oziochat-chat-users-stream li.waiting {
  height: 100%;
  font-weight: bold;
  text-align: center;
}

.oziochat-chat-widget ul.oziochat-chat-users-stream {
  overflow: auto;
}

.oziochat-chat-widget ul.oziochat-chat-users-stream li {
  position: relative;
  border-bottom: 1px solid #EBEBEB;
  clear: both;
  display: block;
  outline: none;
  margin-top: -1px;
  border-top: 1px solid transparent;
  overflow: auto;
}

.oziochat-chat-widget ul.oziochat-chat-users-stream li.chat-message {
  background-color: #DDF4FB;
  border-color: #C6EDF9;
  color: #404040;
  text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
}

.oziochat-chat-widget ul.oziochat-chat-users-stream li div.oc-stream-item-content {
  padding: 5px 5px;
  font-size: 12px;
  position: relative;
  zoom: 1;
}

.oziochat-chat-widget ul.oziochat-chat-users-stream li .oc-image {
  float: left;
  height: 32px;
  overflow: hidden;
  width: 32px;
}



.oziochat-chat-widget ul.oziochat-chat-users-stream li .oc-screen-name {
  font-weight: bold;
  color: #333!important;
}

.oziochat-chat-widget ul.oziochat-chat-users-stream li .oc-content {
  margin-left: 37px;
  min-height: 32px;
}

.oziochat-chat-widget ul.oziochat-chat-users-stream li .oc-activity-row {
  display: block;
  position: relative;
  line-height: 32px;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  
}

.oziochat-chat-widget ul.oziochat-chat-users-stream li .oc-text {
  padding: 0;
  font-family: Arial,"Helvetica Neue",sans-serif;
  line-height: 19px;
  word-wrap: break-word;
}

.oziochat-chat-widget ul.oziochat-chat-users-stream li .oc-timestamp {
  color: #999!important;
  font-size: 9px;
}

.oziochat-chat-widget ul.oziochat-chat-users-stream li .oc-activity-actions span:first-child {
  margin-left: 15px;
}

.oziochat-chat-widget ul.oziochat-chat-users-stream li .oc-activity-actions {
  color: #999!important;
  font-size: 9px;
}

.oziochat-chat-widget ul.oziochat-chat-users-stream li .oc-activity-actions span b {
  font-weight: normal;
}


a.oziochat-chat-logout {
	float:right;
  color: black;
  background-color: #EBEBEB;
  border:0;
}

.oziochat-chat-widget-current-user-name{
  max-width: 214px;
  white-space: nowrap;
  text-overflow: ellipsis;
  margin-left: 10px;	
  overflow: hidden;
  float: left;
}


.oziochat-chat-widget-messages{
	clear:both;
}

.oziochat-chat-enter {
	float:left;
}
.oziochat-chat-widget-user-image{
	float:left;
	margin: 1px;
	margin-left: 5px;
}
.oziochat-chat-exit {
	float:right;
}

.oziochat-chat-widget-user-name{
	line-height:34px;
	float:left;
	margin-left:5px;
	max-width:180px;
	text-overflow:ellipsis;
	overflow:hidden;
	white-space:nowrap;
}

.oziochat-chat-left{
	width:300px;
}
.oziochat-chat-right{
	width:140px;
	position: absolute;
	top:0;
	right:0;
	bottom:0;
}
.oziochat-chat-widget-content{
	position: relative;
}
.oziochat-chat-widget.oc-logged-in{
	width:450px;
}
.oziochat-chat-login-loader{
	cursor: wait;
    background:transparent url('<?php echo JURI::base(true) . '/media/oziochat/images/progress.gif'; ?>') no-repeat center center;	
	height: 16px;
	margin-top:5px;
	margin-bottom:5px;
}

.oziochat-chat-info-popup{
  position: relative;
  background: #FFF;
  padding: 20px;
  width: auto;
  max-width: 500px;
  margin: 20px auto;
}
.oziochat-chat-widget textarea, .oziochat-chat-widget input{
	background-color: white;
	color: black;
	border-color: #ccc;
}


.oziochat-chat-widget{
	background-color: <?php echo $this->Params->get('bgcolor','#F5F5F5'); ?>; /* whiteSmoke */
}

/*
Hides all "Google's Welcome Back Message" pop up
http://stackoverflow.com/questions/17159743/how-to-stop-google-sign-in-button-from-popping-up-the-message-welcome-back-yo
*/
iframe[src^="https://apis.google.com/u/0/_/widget/oauthflow/toast"] {display: none;}

<?php 


if (!function_exists('oziochat_hex2rgb')){
	function oziochat_hex2rgb($hex) {
	   $hex = str_replace("#", "", $hex);

	   if(strlen($hex) == 3) {
		  $r = hexdec(substr($hex,0,1).substr($hex,0,1));
		  $g = hexdec(substr($hex,1,1).substr($hex,1,1));
		  $b = hexdec(substr($hex,2,1).substr($hex,2,1));
	   } else {
		  $r = hexdec(substr($hex,0,2));
		  $g = hexdec(substr($hex,2,2));
		  $b = hexdec(substr($hex,4,2));
	   }
	   $rgb = array($r, $g, $b);
	   return $rgb; // returns an array with the rgb values
	}
	
}

$rgb=oziochat_hex2rgb($this->Params->get('bgcolor','#F5F5F5'));
if ( (($rgb[0]+$rgb[1]+$rgb[2])/3.0)>180.0){
?>

/*
 * Light
 */

.oziochat-chat-widget{
	border-color: rgba(0,0,0,0.05); /* #eee */
	color: #000; /* #333 */
}

a.oziochat-chat-exit, a.oziochat-chat-logout, .oziochat-chat-widget-msg-btn, .oziochat-chat-widget-wnd-btn{
	color: #000; /* black */
	background-color: rgba(0,0,0,0.05); /* #EBEBEB */
	border-color: rgba(0,0,0,0.01); /* #EBEBEB */
}

a.oziochat-chat-exit:hover, a.oziochat-chat-logout:hover, .oziochat-chat-widget-msg-btn:hover, .oziochat-chat-widget-wnd-btn:hover{
	color: #fff; /* #fff */
	background-color: rgba(0,0,0,0.9); /* #2b2b2b */
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream,
.oziochat-chat-widget .oziochat-chat-emoji,
.oziochat-chat-widget ul.oziochat-chat-users-stream{
	background-color: rgba(255,255,255,0.9); /* #fff */
}
.oziochat-chat-widget ul.oziochat-chat-activity-stream li .oc-screen-name,
.oziochat-chat-widget ul.oziochat-chat-users-stream li .oc-screen-name,
.oziochat-chat-widget ul.oziochat-chat-activity-stream li .oc-linked-text{
	color: rgba(0,0,0,0.9) !important;
}


.oziochat-chat-widget ul.oziochat-chat-activity-stream li, 
.oziochat-chat-widget ul.oziochat-chat-users-stream li{
	border-color: rgba(0,0,0,0.05);/*#EBEBEB*/
}
.oziochat-chat-widget-title{
	border-color: rgba(0,0,0,0.05);/*#EBEBEB*/
}
.oziochat-chat-widget-current-user, .oziochat-chat-widget-user {
  border-color: rgba(0,0,0,0.05);/*#EBEBEB*/
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream li .oc-timestamp {
  color: rgba(0,0,0,0.6)!important; /*#999*/
}

.oziochat-chat-widget textarea, .oziochat-chat-widget input{
	background-color: rgba(255,255,255,0.9); /* white */
	color: black; /* black */
	border-color: rgba(0,0,0,0.2); /* #ccc */
}

.oziochat-chat-widget textarea::-webkit-input-placeholder { /* WebKit browsers */
    color:    rgba(0,0,0,0.4);
}
.oziochat-chat-widget textarea:-moz-placeholder { /* Mozilla Firefox 4 to 18 */
   color:    rgb(0,0,0);
   opacity:  0.4;
}
.oziochat-chat-widget textarea::-moz-placeholder { /* Mozilla Firefox 19+ */
   color:    rgb(0,0,0);
   opacity:  0.4;
}
.oziochat-chat-widget textarea:-ms-input-placeholder { /* Internet Explorer 10+ */
   color:    rgba(0,0,0,0.4);
}

 
<?php
} else{ 
?>

/*
 * Dark
 */
.oziochat-chat-widget{
	border-color: rgba(255,255,255,0.2); /* #eee */
	color: #fff; /* #333 */
}

a.oziochat-chat-exit, a.oziochat-chat-logout, .oziochat-chat-widget-msg-btn, .oziochat-chat-widget-wnd-btn{
	color: #fff; /* black */
	background-color: rgba(255,255,255,0.2); /* #EBEBEB */
	border-color: rgba(255,255,255,0.1); /* #EBEBEB */
}

a.oziochat-chat-exit:hover, a.oziochat-chat-logout:hover, .oziochat-chat-widget-msg-btn:hover, .oziochat-chat-widget-wnd-btn:hover{
	color: #000; /* #fff */
	background-color: rgba(255,255,255,0.9); /* #2b2b2b */
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream,
.oziochat-chat-widget .oziochat-chat-emoji,
.oziochat-chat-widget ul.oziochat-chat-users-stream{
	background-color: rgba(255,255,255,0.2); /* #fff */
}
.oziochat-chat-widget ul.oziochat-chat-activity-stream li .oc-screen-name,
.oziochat-chat-widget ul.oziochat-chat-users-stream li .oc-screen-name,
.oziochat-chat-widget ul.oziochat-chat-activity-stream li .oc-linked-text{
	color: rgba(255,255,255,0.9) !important;
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream li, 
.oziochat-chat-widget ul.oziochat-chat-users-stream li{
	border-color: rgba(255,255,255,0.2);/*#EBEBEB*/
}
.oziochat-chat-widget-title{
	border-color: rgba(255,255,255,0.2);/*#EBEBEB*/
}
.oziochat-chat-widget-current-user, .oziochat-chat-widget-user {
  border-color: rgba(255,255,255,0.2);/*#EBEBEB*/
}

.oziochat-chat-widget ul.oziochat-chat-activity-stream li .oc-timestamp {
  color: rgba(255,255,255,0.6)!important; /*#999*/
}

.oziochat-chat-widget textarea, .oziochat-chat-widget input{
	background-color: rgba(255,255,255,0.2); /* white */
	color: white; /* black */
	border-color: rgba(255,255,255,0.2); /* #ccc */
}

.oziochat-chat-widget textarea::-webkit-input-placeholder { /* WebKit browsers */
    color:    rgba(255,255,255,0.6);
}
.oziochat-chat-widget textarea:-moz-placeholder { /* Mozilla Firefox 4 to 18 */
   color:    rgb(255,255,255);
   opacity:  0.6;
}
.oziochat-chat-widget textarea::-moz-placeholder { /* Mozilla Firefox 19+ */
   color:    rgb(255,255,255);
   opacity:  0.6;
}
.oziochat-chat-widget textarea:-ms-input-placeholder { /* Internet Explorer 10+ */
   color:    rgba(255,255,255,0.6);
}

<?php
}//fine dark
?>
if (!window.console)
{
    var names = ["log", "debug", "info", "warn", "error", "assert", "dir", "dirxml",
    "group", "groupEnd", "time", "timeEnd", "count", "trace", "profile", "profileEnd"];

    window.console = {};
    for (var i = 0; i < names.length; ++i)
        window.console[names[i]] = function() {}
}

function OzioChatPusherChatWidget(options) {
  OzioChatPusherChatWidget.instances.push(this);
  var self = this;
  
    var searchString = window.location.search.substring(1),
      i, val, params = searchString.split("&");
	  
	 self.oc_popout = false;
	for (i=0;i<params.length;i++) {
		val = params[i].split("=");
		if (val[0] == 'oc_popout') {
			self.oc_popout = val[1]==1;
			break;
		}
	}
  
  
  this._autoScroll = true;
  
  options = options || {};
  this.settings = jQuery.extend({
    maxItems: 50, // max items to show in the UI. Items beyond this limit will be removed as new ones come in.
    chatEndPoint: 'php/chat.php', // the end point where chat messages should be sanitized and then triggered
    channelName: document.location.href, // the name of the channel the chat will take place on
    appendTo: document.body, // A jQuery selector or object. Defines where the element should be appended to
	i18n: {
		'please supply a nickname':'please supply a nickname',
		'please supply a chat message':'please supply a chat message'
	},
    debug: true
  }, options);
  
  if(this.settings.debug && !Pusher.log) {
    Pusher.log = function(msg) {
      if(console && console.log) {
        console.log(msg);
      }
    }
  }
  this.prefix=this.settings.prefix;
  // remove any unsupported characters from the chat channel name
  // see: http://pusher.com/docs/client_api_guide/client_channels#naming-channels
  this.settings.channelName = OzioChatPusherChatWidget.getValidChannelName(this.settings.channelName);
  
	  
    
  this._itemCount = 0;
  
  this._widget = OzioChatPusherChatWidget._createHTML(this.settings.appendTo,this.settings.cloneFrom, self.oc_popout);
  this._nicknameEl = this._widget.find('input[name=nickname]');
  this._emailEl = this._widget.find('input[name=email]');  
  this._messageInputEl = this._widget.find('textarea');
  this._messagesEl = this._widget.find('ul.oziochat-chat-activity-stream');
  this._membersEl = this._widget.find('ul.oziochat-chat-users-stream');
  
  this._widget_left = this._widget.find('.oziochat-chat-left');
  this._widget_right = this._widget.find('.oziochat-chat-right');
  
  this._widget_title_label = this._widget.find('.oziochat-chat-widget-title > label');
  
  this._wnd_sound='on';
  
  if (self.oc_popout){
	  this._wnd_state='M';
  }else{
	  this._wnd_state='m';//minimized
	  
	  var wnd_state=oc_cookie.get('oc_wnd_state');
	  if (typeof wnd_state !== 'undefined' && (wnd_state=='m' || wnd_state=='M' )) {
		  this._wnd_state=wnd_state;
	  }
  }

   var wnd_sound=oc_cookie.get('oc_wnd_sound');
  if (typeof wnd_sound !== 'undefined' && (wnd_sound=='on' || wnd_sound=='off' )) {
	  this._wnd_sound=wnd_sound;
  }

  
  this._elemets = [];
  
  this._elemets.push(this._widget.find('.oziochat-chat-widget-content'));
  //this._elemets.push(this._widget.find('.oziochat-chat-widget-content').next());

  this._wnd_min = this._widget.find('.oziochat-chat-widget-minimize');
  this._wnd_popout = this._widget.find('.oziochat-chat-widget-popout');
  this._wnd_max = this._widget.find('.oziochat-chat-widget-maximize');  
  this._wnd_bubble = this._widget.find('.oziochat-chat-widget-bubble');  
  
  if (self.oc_popout){
	  this._wnd_min.hide();
	  this._wnd_popout.hide();
	  this._wnd_max.hide();
	  this._wnd_bubble.hide();
  }

  this._wnd_info = this._widget.find('.oziochat-chat-widget-question');  
  this._wnd_emoji = this._widget.find('.oziochat-chat-widget-emoji');  

  this._wnd_sn_off = this._widget.find('.oziochat-chat-widget-sound-off');
  this._wnd_sn_on = this._widget.find('.oziochat-chat-widget-sound-on');  

  this._widget_user=this._widget.find('.oziochat-chat-widget-current-user');
  this._widget_login=this._widget.find('.oziochat-chat-widget-login-buttons');
  this._widget_messages=this._widget.find('.oziochat-chat-widget-messages');
  this._widget_input=this._widget.find('.oziochat-chat-widget-input');
  this._widget_users_list=this._widget.find('.oziochat-chat-widget-users');
  this._widget_emoj_panel = this._widget.find('.oziochat-chat-emoji');
  this._widget_emoj_panel.hide();

  this._wnd_num_users = this._widget.find('.oziochat-chat-widget-num-users'); 

  this._show_login_widget_button('anonymous','none');
  this._show_login_widget_button('joomla','none');
  this._show_login_widget_button('facebook','none');
  this._show_login_widget_button('googleplus','none');
  this._show_login_widget_button('twitter','none');
  this._show_loggedin(false);
  
  //this._widget_state='init';
  //this._last_login={};
  this.actors_ready={};
  this.fb_sdk_added=false;
  this.fb_sdk_inited=false;
  
  this.gp_sdk_inited=false;

  this.loginfb_click_pending=false;
  this.logingp_click_pending=false;
  this.logintw_click_pending=false;	
  
  
  this._messageInputEl.keydown(function(e){
		if (e.keyCode == 13 && !e.shiftKey)
		{
			e.preventDefault();
			self._sendChatButtonClicked();			
		}
  });
  
  //this._widget.find('button').click(function() {
  //  self._sendChatButtonClicked();
  //});
  
  this._widget.find('.oziochat-chat-emoji span').click(function(){
		var caretPos = self._messageInputEl[0].selectionStart;
		var textAreaTxt = self._messageInputEl.val();
		var txtToAdd = jQuery(this).text();
		self._messageInputEl.val(textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt.substring(caretPos) );
  });
  this._wnd_sn_off.click(function() {
	self._wnd_sound='off';
	oc_cookie.set('oc_wnd_sound',self._wnd_sound, { path: '/' });
    self._upadateWnd();
  });
  this._wnd_sn_on.click(function() {
	self._wnd_sound='on';
	oc_cookie.set('oc_wnd_sound',self._wnd_sound, { path: '/' });
    self._upadateWnd();
  });
  
  self.emoj_visible = false;
  
  
  this._wnd_emoji.click(function(){
	  self.emoj_visible = !self.emoj_visible;
	  if (self.emoj_visible){
			self._widget_emoj_panel.slideDown(200);
	  }else{
			self._widget_emoj_panel.slideUp(200);
	  }
  });
  
  this._wnd_info.click(function() {
	  //alert("show info");
	  
	  jQuery.magnificPopup.open({
		  items: {
			src: '<div class="oziochat-chat-info-popup"> '+self.settings.infobox_msg+' </div>',
			type: 'inline'
		  }
		});
	  
  });
  this._wnd_bubble.click(function() {
	  self._animateMaximize();
  });
  this._wnd_max.click(function() {
	  self._animateMaximize();
  });
  this._wnd_min.click(function() {
	  self._animateMinimize();
  });
  this._wnd_popout.click(function() {
	  var param = 'oc_popout=1';
	  if (window.location.search==''){
		  param = '?'+param;
	  }else{
		  param = '&'+param;
	  }
	  
	var w =window.open(window.location.href+param, '', 'width=450,height=500');
  });
  this._logout_btn=this._widget.find('.oziochat-chat-logout');
  this._logout_btn.click(function(){
	  self._logout_click();
  });


  this._tw_login_btn=this._widget.find('.oziochat-chat-twitter-login');
  this._tw_login_btn.click(function(){
	  self._loginTW_click();
  });
  this._tw_logout_btn=this._widget.find('.oziochat-chat-twitter-logout');
  this._tw_logout_btn.click(function(){
	  self._logoutTW_click();
  });

  
  this._gp_login_btn=this._widget.find('.oziochat-chat-googleplus-login');
  this._gp_login_btn.click(function(){
	  self._loginGP_click();
  });
  this._gp_logout_btn=this._widget.find('.oziochat-chat-googleplus-logout');
  this._gp_logout_btn.click(function(){
	  self._logoutGP_click();
  });
  
  
  this._fb_login_btn=this._widget.find('.oziochat-chat-facebook-login');
  this._fb_login_btn.click(function(){
	  self._loginFB_click();
  });
  this._fb_logout_btn=this._widget.find('.oziochat-chat-facebook-logout');
  this._fb_logout_btn.click(function(){
	  self._logoutFB_click();
  });
  
  this._joomla_login_btn=this._widget.find('.oziochat-chat-joomla-login');
  this._joomla_login_btn.click(function(){
	  self._loginJoomla_click();
  });
  this._joomla_logout_btn=this._widget.find('.oziochat-chat-joomla-logout');
  this._joomla_logout_btn.click(function(){
	  self._logoutJoomla_click();
  });

  this._anonymous_login_btn=this._widget.find('.oziochat-chat-anonymous-login-button');
  this._anonymous_login_btn.click(function(){
	  self._loginAnonymous_click();
  });
  this._anonymous_logout_btn=this._widget.find('.oziochat-chat-anonymous-logout');
  this._anonymous_logout_btn.click(function(){
	  self._logoutAnonymous_click();
  });
  
  this._bind_enter_click('facebook');
  this._bind_enter_click('googleplus');
  this._bind_enter_click('anonymous');
  this._bind_enter_click('joomla');
  this._bind_enter_click('twitter');
  
  
  var messageEl = this._messagesEl;
  messageEl.scroll(function() {
    var el = messageEl.get(0);
    var scrollableHeight = (el.scrollHeight - messageEl.height());
    self._autoScroll = ( scrollableHeight === messageEl.scrollTop() );
  });
  
  
    self._upadateWnd();
  
    self._last_messages={};
	self.obj_storage=true;
	self.json_available=true;
	if (typeof JSON.stringify !== 'function') {
		self.json_available=false;
	}
	if (typeof JSON.parse !== 'function') {  
		self.json_available=false;
	}
	if(typeof(Storage) === "undefined") {
		self.obj_storage=false;
	}
	
	self._restoreMessageReceived();  
	self.gp_pending=false;
	self.fb_pending=false;
	self.tw_pending=false;
	
  this._num_logged_in = 0;
  this._get_num_connected();
	
	self._loadGP();
	self._loadFB();
	self._loadTW();
	self._loadJoomla();
	self._loadAnonymous();
  //});
  
  
  this._startTimeMonitor();
  
  
  
  jQuery(window).resize(function(){
	self.responsiveAdjust(self._wnd_state,true);
  });
  self.responsiveAdjust(self._wnd_state,true);
  
 
	for (var i=0;i<OzioChatPusherChatWidget.oc_gp_signin_callback_to_process.length;i++){
		console.log('gp_signin_callback after init call');
		self.gp_signin_callback(OzioChatPusherChatWidget.oc_gp_signin_callback_to_process[i]);
	}
 
  
};
OzioChatPusherChatWidget.instances = [];
OzioChatPusherChatWidget.oc_gp_signin_callback_to_process = [];

function oc_gp_signin_callback(response){
	console.log('oc_gp_signin_callback');
	for (var i=0;i<OzioChatPusherChatWidget.instances.length;i++){
		OzioChatPusherChatWidget.instances[i].gp_signin_callback(response);
	}
	OzioChatPusherChatWidget.oc_gp_signin_callback_to_process.push(response);
}
OzioChatPusherChatWidget.prototype._animateMaximize = function(){
	
	var self=this;
	var wh=self.responsiveAdjust('M',false);
	self._widget.animate({width:wh[0]+'px'}, 200,'swing',function(){
		self._wnd_state='M';
		oc_cookie.set('oc_wnd_state',self._wnd_state, { path: '/' });
		self._upadateWnd();
		self._widget.animate({'max-height':wh[1]+'px'}, 200,'swing', function(){
			self._widget.css('max-height','none');
			self.responsiveAdjust(self._wnd_state,true);
		});
		
	});
	
}
OzioChatPusherChatWidget.prototype._animateMinimize = function(){
	var self=this;
	var wh=self.responsiveAdjust('m',false);
	
	self._widget.animate({width:wh[0]+'px'}, 200,'swing',function(){
		var outer_height = self._widget.outerHeight(true);
		self._widget.css('max-height',outer_height+"px");
		self._widget.animate({'max-height': '30px'}, 200,'swing', function(){
			self._wnd_state='m';
			oc_cookie.set('oc_wnd_state',self._wnd_state, { path: '/' });
			self._upadateWnd();
			self._widget.css('max-height','none');
			
			self.responsiveAdjust(self._wnd_state,true);
			
		});
		
	});
}

OzioChatPusherChatWidget.prototype.responsiveAdjust = function(wnd_state,width_set){
	var self=this;
	 var w = jQuery(window).width(); 
	 var h = jQuery(window).height(); 

	 //WIDTH
	 var left_default_width = 300;
	 var full_widget_width = 450;
	 var max_messages_height=300;
	if (self.oc_popout){
		full_widget_width = w+100;
		max_messages_height = h+100;
		if (self._widget.hasClass('oc-logged-in')){
			left_default_width = Math.max(w - 300,left_default_width);
		}else{
			left_default_width = w;
		}
	}else{
		 if (wnd_state=='m'){
			 full_widget_width = 320;
		 }else{

			 var full_widget_width = 450;
			if (self._widget.hasClass('oc-logged-in')){
				full_widget_width= 450;
			}else{
				full_widget_width= 320;
			}
		 }
	}
	var offset_align = Math.max(Math.min(30,w - (full_widget_width+10+10+1+1)),0);
	if (self.oc_popout){
		offset_align=0;
	}
	 if (self.settings.halign=='left'){
		 self._widget.css('left',offset_align+'px');
	 }else{
		 self._widget.css('right',offset_align+'px');
	 }
	 
	 var widget_width = Math.min(full_widget_width, w-(10+10+1+1+offset_align));
	 if (width_set){
		 self._widget.width(widget_width);
	 }
	 self._widget_title_label.css('max-width',Math.max(widget_width-135,10)+'px');
	 
	 var right_width = widget_width - 10 - left_default_width;
	 if (right_width < 70){
		 right_width = -10;
	 }
	 var left_width = widget_width - 10 - right_width;
	 
	 self._widget_left.width(left_width);
	 self._messageInputEl.width(left_width-15);
	 
	 self._nicknameEl.width(left_width-15);
	 self._emailEl.width(left_width-15);
	 
	 if (right_width<0){
		 self._widget_right.hide();
	 }else{
			self._widget_right.width(right_width);
			self._widget_right.show();
	 }
	 //HEIGHT
	 var bnnr = self._widget.find('.oziochat-chat-widget-content').next().outerHeight(true);
	 
	 var messagesEl_height = Math.min(Math.max(80,h-(17+30+46+25+self._messageInputEl.outerHeight(true)+bnnr)),max_messages_height);
	 var widgetLogin_height = Math.min(Math.max(80,h-(7+30+bnnr)),max_messages_height);
	if (self.oc_popout){
		 self._messagesEl.css('height',messagesEl_height+"px");
		 self._widget_login.css('height',widgetLogin_height+"px");
		 self._messagesEl.css('max-height',"none");
	}else{
		 self._messagesEl.css('max-height',messagesEl_height+"px");
	}
	 
	var content_height=30;
	for (var i=0;i<self._elemets.length;i++){
		content_height+=self._elemets[i].outerHeight(true);
	}
	 
	 return [widget_width,  content_height];
}


OzioChatPusherChatWidget.prototype._storeMessageReceived = function(data){
	var self=this;
	if (typeof self._last_messages[self.settings.channelName] === "undefined") 	{
		self._last_messages[self.settings.channelName]=[];
	}
	self._last_messages[self.settings.channelName].push(data);
	while (self._last_messages[self.settings.channelName].length>self.settings.maxItems){
		self._last_messages[self.settings.channelName].shift();
	}
	if (self.obj_storage && self.json_available){
		localStorage.setItem("oc_last_messages", JSON.stringify(self._last_messages));
	}else{
		//no local storage
	}
	
}

OzioChatPusherChatWidget.prototype._restoreMessageReceived = function() {
	var self=this;
	if (self.obj_storage && self.json_available){
		var s=JSON.parse(localStorage.getItem('oc_last_messages'));
		if (typeof(s) !== "undefined" && s!=null){
			self._last_messages=s;
		}
	}else{
		//no local storage
	}
	if (typeof self._last_messages[self.settings.channelName] !== "undefined") 	{
		for (var i=0;i<self._last_messages[self.settings.channelName].length;i++){
			//self._chatMessageReceived(self._last_messages[self.settings.channelName][i]);
			
				var data=self._last_messages[self.settings.channelName][i];
			  if(this._itemCount === 0) {
				this._messagesEl.html('');
			  }
			  
			  var messageEl = OzioChatPusherChatWidget._buildListItem(data);
			  this._messagesEl.append(messageEl);
			  
			  ++this._itemCount;
			  
			  if(this._itemCount > this.settings.maxItems) {
				/* get first li of list */
				this._messagesEl.children(':first').remove();
			  }
			
			
		}
		
	}
}


OzioChatPusherChatWidget.prototype._upadateWnd = function() {
	if (this._wnd_sound=='on'){
		this._wnd_sn_off.show();
		this._wnd_sn_on.hide();
	}else{
		//off
		this._wnd_sn_off.hide();
		this._wnd_sn_on.show();
	}	
	
	if (this._wnd_state=='m'){
		//minimized
		for (var i=0;i<this._elemets.length;i++){
			this._elemets[i].hide();
		}
		if (!this.oc_popout){
			this._wnd_min.hide();
			this._wnd_max.show();
			this._wnd_bubble.show();
		}
	}else{
		//maximized
		for (var i=0;i<this._elemets.length;i++){
			this._elemets[i].show();
		}
		if (!this.oc_popout){
			this._wnd_min.show();
			this._wnd_max.hide();
			this._wnd_bubble.hide();
		}
	}
	//this.responsiveAdjust(self._wnd_state,true);

};

OzioChatPusherChatWidget.prototype._show_login_widget_button = function(method,what) {
	var self=this;
	if (what=='none'){
	  jQuery('.oziochat-chat-loader-'+method,self._widget_login).hide();
	  jQuery('.oziochat-chat-'+method+'-login',self._widget_login).hide();
	  jQuery('.oziochat-chat-widget-'+method,self._widget_login).hide();
	  return;
	}
	if (what=='load'){
	  jQuery('.oziochat-chat-loader-'+method,self._widget_login).show();
	  jQuery('.oziochat-chat-'+method+'-login',self._widget_login).hide();
	  jQuery('.oziochat-chat-widget-'+method,self._widget_login).hide();
	  return;
	}
	if (what=='login'){
	  jQuery('.oziochat-chat-loader-'+method,self._widget_login).hide();
	  jQuery('.oziochat-chat-'+method+'-login',self._widget_login).show();
	  jQuery('.oziochat-chat-widget-'+method,self._widget_login).hide();
	  return;
	}
	if (what=='widget'){
	  jQuery('.oziochat-chat-loader-'+method,self._widget_login).hide();
	  jQuery('.oziochat-chat-'+method+'-login',self._widget_login).hide();
	  jQuery('.oziochat-chat-widget-'+method,self._widget_login).show();
	  return;
	}
}

OzioChatPusherChatWidget.prototype._bind_enter_click = function(method){
	var self=this;
  jQuery('.oziochat-chat-widget-'+method+' > a.oziochat-chat-enter',self._widget_login).click(function(){
	  self._enter_click(method);
  });
}


OzioChatPusherChatWidget.prototype._show_loggedin = function(loggedin) {
	var self=this;
	if (loggedin){
		self._widget.addClass('oc-logged-in');
		self._widget_user.show();
		self._widget_users_list.show();
		self._widget_messages.show();
		self._widget_input.show();

		self._wnd_num_users.hide();
		
		self._widget_login.hide();
		//if(self._autoScroll) {
		  var messageEl = self._messagesEl.get(0);
		  //var scrollableHeight = (messageEl.scrollHeight - self._messagesEl.height());
		  self._messagesEl.scrollTop(messageEl.scrollHeight);
		//}
		
		//start vitality timer
		self.restart_vitality_timer();
	}else{
		self._widget.removeClass('oc-logged-in');
		self._widget_user.hide();
		self._widget_users_list.hide();
		self._widget_messages.hide();
		self._widget_input.hide();

		self._wnd_num_users.show();

		self._widget_login.show();

		//stop vitality timer
		if (self.vitality_timer!==null){
			clearTimeout(self.vitality_timer);
			self.vitality_timer=null;
		}
	}
	self.responsiveAdjust(self._wnd_state,true);
}
OzioChatPusherChatWidget.prototype.restart_vitality_timer = function(){
	var self=this;
	if (self.vitality_timer!==null){
		clearTimeout(self.vitality_timer);
		self.vitality_timer=null;
	}
	self.vitality_timer=setTimeout(function(){self.refresh_vitality();},60*1000);
}
	
OzioChatPusherChatWidget.prototype.refresh_vitality = function(){
	var self=this;
	
  jQuery.ajax({
	url: self.settings.chatEndPoint,
	type: 'post',
	dataType: 'json',
	data: {
	  'loginonly': oc_cookie.get('oc_logged_in')
	},
	complete:function(){
		//prossimo
		self.vitality_timer=null;
		self.restart_vitality_timer();
	},
	error:function(){
		//effettuo il logout
		self._logout_click();
	},
	success:function(){
		console.log("refreshed vitality");
	}
  });	
	
}
OzioChatPusherChatWidget.prototype._update_login_avatar = function(method){
	var actor=this.actors_ready[method];
	jQuery('.oziochat-chat-widget-'+method+' img',this._widget_login).attr('src',actor.image.url);
	jQuery('.oziochat-chat-widget-'+method+' > .oziochat-chat-widget-user-name',this._widget_login).text(actor.displayName);
}

OzioChatPusherChatWidget.prototype._logout_server = function(logout_from){
	
  jQuery.ajax({
    url: this.settings.chatEndPoint,
    type: 'post',
    dataType: 'json',
    data: {
      'logout': logout_from,
    }
  });
}

OzioChatPusherChatWidget.prototype._logout_click = function() {
	//alert("_logout_click");
	oc_cookie.set('oc_logged_in','', { path: '/' });
	this._show_loggedin(false);
	this._membersEl.html('');

	
	//decarico la chat
	if (this._pusher){
		this._pusher.unsubscribe("presence-"+this.settings.channelName);
		this._pusher.unsubscribe("private-"+this.settings.channelName);
	}
	//this._pusher.disconnect();
	this._pusher = null;
	this._chatChannel = null;
	this._presenceChannel = null;
	
	this._get_num_connected();
	//ricarico lo stato delle varie login in teoria (solo per facebook le altre si aggiornano in automatico)
	this._loadFB();
}

OzioChatPusherChatWidget.prototype._get_num_connected = function(){
  var self= this;
  jQuery.ajax({
	url: self.settings.chatEndPoint,
	type: 'post',
	dataType: 'json',
	data: {
	  'only_get_num': 1,
	  'channel_name': 'presence-'+self.settings.channelName
	},
	complete:function(){
	},
	error:function(){
	},
	success:function(data){
		self._num_logged_in = parseInt(data['c']);
		jQuery('span',self._wnd_num_users).text('('+self._num_logged_in+')');
	}
  });		
	
}
/*
OzioChatPusherChatWidget.prototype._get_num_connected = function(){
	var self=this;
	var gn_pusher = null;
	var gn_presence_channel = null;
	
	var oziochat_get_num_connected_disconnect = function (num_connected, do_update){
		gn_pusher.unsubscribe("presence-"+self.settings.channelName);
		gn_pusher = null;
		gn_presence_channel = null;
		
		if (do_update){
			self._num_logged_in = num_connected;
			jQuery('span',self._wnd_num_users).text('('+self._num_logged_in+')');
		}
	}
	
	Pusher.channel_auth_endpoint=self.settings.chatEndPoint;
	Pusher.channel_auth_transport='ajax';
	gn_pusher = new Pusher(self.settings.pusherAppKey,{ authTransport:'ajax', authEndpoint: self.settings.chatEndPoint, auth: {  params: {  only_get_num: '1' }  } });
	
	gn_pusher.connection.bind( 'error', function( err ) { 
		if( err.data.code === 4004 ) {
			console.log("chat piena");
		}else{
			console.log("errore generico");
		}
		oziochat_get_num_connected_disconnect(0,false);
	});	
	gn_pusher.connection.bind( 'unavailable', function( ) { 
		console.log("connessione momentaneamente saltata");
		oziochat_get_num_connected_disconnect(0,false);
	});
	gn_pusher.connection.bind( 'failed', function( ) { 
		console.log("pusher non supportato dal browser");
		oziochat_get_num_connected_disconnect(0,false);
	});
	var first_time=true;
	gn_pusher.connection.bind( 'connected', function( ) { 
		//connessione avvenuta
		if (first_time){
			first_time=false;
			
			gn_presence_channel = gn_pusher.subscribe("presence-"+self.settings.channelName);
			gn_presence_channel.bind('pusher:subscription_succeeded', function(members) {
				console.log(members);
				oziochat_get_num_connected_disconnect(members.count-1,true);
			});	
		}
	});	
}
*/

OzioChatPusherChatWidget.prototype._enter_click = function(method) {
	var self=this;
	oc_cookie.set('oc_logged_in',method, { path: '/' });
	self._show_loggedin(true);
	
	//aggiorno l'avatar dell'utente principale
	var actor=self.actors_ready[method];
	jQuery('img',self._widget_user).attr('src',actor.image.url);
	jQuery('.oziochat-chat-widget-current-user-name',self._widget_user).text(actor.displayName);
	
	
	//carico la chat! qui  
	Pusher.channel_auth_endpoint=self.settings.chatEndPoint;
	Pusher.channel_auth_transport='ajax';
	self._pusher = new Pusher(self.settings.pusherAppKey,{ authTransport:'ajax', authEndpoint: self.settings.chatEndPoint});
	self._pusher.connection.bind( 'error', function( err ) { 
		if( err.data.code === 4004 ) {
			console.log("chat piena");
		}else{
			console.log("errore generico");
		}
		self._logout_click();
	});	
	self._pusher.connection.bind( 'unavailable', function( ) { 
		console.log("connessione momentaneamente saltata");
	});
	self._pusher.connection.bind( 'failed', function( ) { 
		console.log("pusher non supportato dal browser");
		self._logout_click();
	});
	var first_time=true;
	self._pusher.connection.bind( 'connected', function( ) { 
		//connessione avvenuta
		if (first_time){
			first_time=false;
			
			self._pusher.subscribe(self.settings.channelName);//canale pubblico
			
			self._presenceChannel = self._pusher.subscribe("presence-"+self.settings.channelName);
			self._presenceChannel.bind('pusher:subscription_succeeded', function(members) {
				self._num_logged_in=0;
				members.each(function(member) {
					self.add_member(member.id, member.info);
				});
			});	
			self._presenceChannel.bind('pusher:member_added', function(member) {
				self.add_member(member.id, member.info);
				if (self._wnd_sound=='on'){
					oc_play_enter_msg();
				}
			});	
			self._presenceChannel.bind('pusher:member_removed', function(member) {
				self.remove_member(member.id, member.info);
				if (self._wnd_sound=='on'){
					oc_play_exit_msg();
				}
			});	
			self._chatChannel = self._pusher.subscribe("private-"+self.settings.channelName);

			self._chatChannel.bind('chat_message', function(data) {
				self._storeMessageReceived(data);
				self._chatMessageReceived(data);
			});

		}
	
	
	});
	
	
	
	
	
	
}

OzioChatPusherChatWidget.prototype.add_member=function(id,info){
  var self=this;
  if (info.userid!='hidden-hidden'){
	  var memberEl = OzioChatPusherChatWidget._buildMemberItem(id,info);
	  memberEl.hide();
	  this._membersEl.append(memberEl);
	  memberEl.slideDown(function() {
		//if(self._autoScroll) {
		  var memberEl = self._membersEl.get(0);
		  //var scrollableHeight = (memberEl.scrollHeight - self._membersEl.height());
		  self._membersEl.scrollTop(memberEl.scrollHeight);
		//}
	  });
  
		self._num_logged_in++;
		jQuery('span',self._wnd_num_users).text('('+self._num_logged_in+')');
  }
	//console.log("add_member "+id);
}
OzioChatPusherChatWidget.prototype.remove_member=function(id,info){
	var self=this;
	//console.log("remove_member "+id);
	
	jQuery('li.oc-activity',self._membersEl).each(function( index ) {
		var memberEl=jQuery(this);
		if (memberEl.attr('oc-data-user-id')==id){
			memberEl.slideUp(function() {
			  jQuery(this).remove();
			});
			
			if (id!='hidden-hidden'){
				self._num_logged_in--;
			}
			
			
		}
	});
  jQuery('span',self._wnd_num_users).text('('+self._num_logged_in+')');
	
}

OzioChatPusherChatWidget.prototype._logoutFB_click = function() {
	var self=this;
	//alert("_logoutFB_click");
	oc_cookie.set('oc_logged_in','', { path: '/' });
	oc_cookie.remove('oc_user_id_facebook',{ path: '/' });
	
	FB.logout(function(response) {
		// Person is now logged out
		self._fbStatusChangeCallback(response);
	});
	
}

OzioChatPusherChatWidget.prototype._loginFB_click = function() {
	var self=this;
	
	if (self.loginfb_click_pending){
		return;
	}
	self.loginfb_click_pending=true;
	
	
	//alert("_loginFB_click");
	oc_cookie.set('oc_logged_in','', { path: '/' });
	FB.login(function(response) {
		self.loginfb_click_pending=false;
		oc_cookie.set('oc_logged_in','facebook', { path: '/' });
		self._fbStatusChangeCallback(response);
	}, {scope: 'public_profile,email'});
	
}

OzioChatPusherChatWidget.prototype._fbStatusChangeCallback = function(response) {
	var self=this;

	if (!self.settings.facebook_login){
		return;
	}
	console.log("FB "+response.status);
	if (response.status === 'connected') {
	  // Logged into your app and Facebook.
		if (self.fb_pending){
			 return;
		 }
	     self.fb_pending=true;	  
	  
	  //ottengo l'actor
		FB.api('/me?fields=id,name,picture,link', function(response) {
			if (!response || response.error) {
				//errore strano
				//Visualiza Bottone Login FB
				self._show_login_widget_button('facebook','login');
			}else{
				oc_cookie.set('oc_user_id_facebook','facebook-'+response.id, { path: '/' });

				  jQuery.ajax({
					url: self.settings.chatEndPoint,
					type: 'post',
					dataType: 'json',
					data: {
					  'loginonly': 'facebook'
					},
					complete:function(){
						console.log("FB complete");
						self.fb_pending=false;	  
					},
					error:function(){
						//effettuo il logout
						self._logout_click();
					},
					success:function(){
						console.log("FB success");
						self.actors_ready['facebook']={
							displayName: response.name,
							objectType: 'person',
							image: {url: response.picture.data.url},
							link: response.link
							
						};
						//aggiorno avatar
						self._update_login_avatar('facebook');
						//self._last_login['facebook']={login_with:'facebook'};				
						
						self._show_login_widget_button('facebook','widget');
						if (oc_cookie.get('oc_logged_in')=='facebook'){
						  //entra nella chat
							self._enter_click('facebook');
						}				
						
					}
				  });				
				
			}
		});

	  
	  
	} else if (response.status === 'not_authorized') {
	  // The person is logged into Facebook, but not your app.
	  //Visualiza Bottone Login FB
		self._show_login_widget_button('facebook','login');
		self._logout_server('facebook');
	} else {
	  // The person is not logged into Facebook, so we're not sure if
	  // they are logged into this app or not.
	  //Visualiza Bottone Login FB
		self._show_login_widget_button('facebook','login');
		self._logout_server('facebook');
	}
}

/*OzioChatPusherChatWidget.prototype._fbDoLogoutCheck = function(response) {
	var self=this;
	  console.log(response.status);
		if (response.status === 'connected') {
			self.fb_last_status='connected';
		}else{
			//logged out
			if (self.fb_last_status=='connected'){
				self._show_login_widget_button('facebook','login');
				self._logout_server('facebook');
				console.log("logout facebook");
			}
			self.fb_last_status='disconnected';
		}
}
*/
OzioChatPusherChatWidget.prototype._loadFB = function() {
	var self=this;
    if (self.settings.fbAppId!=''){
		
		self._show_login_widget_button('facebook','load');
		var fbDoUpdateStatus=function(){
			if (!self.fb_sdk_inited){
				return;
			}
		  // Now that we've initialized the JavaScript SDK, we call 
		  // FB.getLoginStatus().  This function gets the state of the
		  // person visiting this page and can return one of three states to
		  // the callback you provide.  They can be:
		  //
		  // 1. Logged into your app ('connected')
		  // 2. Logged into Facebook, but not your app ('not_authorized')
		  // 3. Not logged into Facebook and can't tell if they are logged into
		  //    your app or not.
		  //
		  // These three cases are handled in the callback function.

		  FB.getLoginStatus(function(response) {
			self._fbStatusChangeCallback(response);
			
			/*
			if (self.fb_last_status=='init'){
			  self.fb_last_status=response.status;
			  
			  //timer to monitor FB status
			  setInterval(function() {
					FB.getLoginStatus(function(resp) {
						self._fbDoLogoutCheck(resp);
					});
			  }, 10 * 1000);
				
				
			}
			*/
		  });
		}
		
	  fbDoUpdateStatus();
	  
	  if (!self.fb_sdk_added){
		  self.fb_sdk_added=true;

		  window.fbAsyncInit = function() {
			  FB.init({
				  
				appId      : self.settings.fbAppId,
				cookie     : true,  // enable cookies to allow the server to access 
									// the session
				xfbml      : false,  // parse social plugins on this page
				version    : 'v2.3' // use version 2.3
			  });
		      self.fb_sdk_inited=true;
			  fbDoUpdateStatus();
			  self.fb_last_status='init';
			  
			  
			  
		  };
		  
		  // Load the SDK asynchronously
		  (function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = "//connect.facebook.net/en_US/sdk.js";
			fjs.parentNode.insertBefore(js, fjs);
		  }(document, 'script', 'facebook-jssdk'));  
		  //End Facebook Load
	  }

  }
	
	
}

//Google Plus Start
OzioChatPusherChatWidget.prototype.gp_signin_callback = function(authResult) {
	var self=this;
	if (!self.settings.googleplus_login){
		return;
	}
	self.gp_sdk_inited=true;
	
	self.logingp_click_pending=false;	
	//self._gbStatusChangeCallback(authResult);
	
	console.log("GP "+authResult['status']['signed_in']);
	if (authResult['status']['signed_in'] ) {
		
      // Your user is signed in. You can use the access token to perform
      // calls or if you get a `code`, you could send that to your API
      // server to get server-side access to the APIs.
	  
		//var token = authResult.access_token; 
		//gapi.client.load('plus', 'v1', function(){                           
		 //});  
		 if (self.gp_pending){
			console.log("GP pending");
			 return;
		 }
	     self.gp_pending=true;
			console.log("GP set to pending");

		//OR to see the Public Posts result in console

		var request =   gapi.client.request({'path':'/plus/v1/people/me'});
		 request.execute(function(response) {                                       
		 
			if (!response || response.error) {
				console.log("GP error");
				console.log(response);
				
				//errore strano
				self._show_login_widget_button('googleplus','login');
			}else{
				console.log("GP ok");
				  oc_cookie.set('oc_googleplus_code',authResult['code'], { path: '/' });
				  oc_cookie.set('oc_user_id_googleplus','googleplus-'+response.id, { path: '/' });
				  jQuery.ajax({
					url: self.settings.chatEndPoint,
					type: 'post',
					dataType: 'json',
					data: {
					  'loginonly': 'googleplus'
					},
					complete:function(){
						console.log("GP complete");
						self.gp_pending=false;
					},
					error:function(){
						console.log("GP ajax error");
						//effettuo il logout
						self._logout_click();
					},
					success:function(){
						console.log("GP success");
						oc_cookie.remove('oc_googleplus_code',{ path: '/' });
						

						self.actors_ready['googleplus']={
							displayName: response.displayName,
							objectType: 'person',
							image: {url: response.image.url},
							link: response.url
							
						};
						//aggiorno avatar
						self._update_login_avatar('googleplus');
						
						self._show_login_widget_button('googleplus','widget');
						if (oc_cookie.get('oc_logged_in')=='googleplus'){
						  //entra nella chat
							self._enter_click('googleplus');
						}
				
						
					}
				  });
				
				
				
				
			}
				
		 
		 
		 });
 
	  
	  
    } else {
      // User is not signed in to your app, handle any user interface
      // changes or other aspects of your design based on this condition.
		self._show_login_widget_button('googleplus','login');
		self._logout_server('googleplus');
		if (oc_cookie.get('oc_logged_in')=='googleplus'){
			self._logout_click();
		}
    }	
	
}
OzioChatPusherChatWidget.prototype._loadGP = function() {
	var self=this;
	if (!self.settings.googleplus_login){
		return;
	}
	self._show_login_widget_button('googleplus','load');
}

OzioChatPusherChatWidget.prototype._logoutGP_click = function() {
	var self=this;
	//alert("_logoutGP_click");
	oc_cookie.set('oc_logged_in','', { path: '/' });
	oc_cookie.remove('oc_user_id_googleplus',{ path: '/' });
	
	gapi.auth.signOut();
}

OzioChatPusherChatWidget.prototype._loginGP_click = function() {
	var self=this;
	if (self.logingp_click_pending){
		return;
	}
	self.logingp_click_pending=true;

	oc_cookie.set('oc_logged_in','googleplus', { path: '/' });
	//gapi.auth.signIn({'redirecturi': 'postmessage','accesstype':'offline' });
	gapi.auth.signIn();
}

//Google Plus End

//Twitter Start
OzioChatPusherChatWidget.prototype._loadTW = function() {
	var self=this;
	if (!self.settings.twitter_login){
		return;
	}
	self._show_login_widget_button('twitter','load');
	if (self.tw_pending){
		return;
	}
	self.tw_pending=true;
	//GO
  jQuery.ajax({
	url: self.settings.chatEndPoint,
	type: 'post',
	dataType: 'json',
	data: {
	  'tw_get_status': '1'
	},
	complete:function(){
	},
	error:function(){
		self._show_login_widget_button('twitter','login');
		self.tw_pending=false;
	},
	success:function(response){
		if (typeof response!=='undefined' && typeof response.error!=='undefined'){
			//Twitter Not logged in
			self._show_login_widget_button('twitter','login');
			self.tw_pending=false;
			return;
		}
		  oc_cookie.set('oc_user_id_twitter',response.userid, { path: '/' });
		  jQuery.ajax({
			url: self.settings.chatEndPoint,
			type: 'post',
			dataType: 'json',
			data: {
			  'loginonly': 'twitter'
			},
			complete:function(){
				console.log("TW complete");
				self.tw_pending=false;
			},
			error:function(){
				//effettuo il logout
				self._logout_click();
			},
			success:function(){
				console.log("TW success");

				self.actors_ready['twitter']={
					displayName: response.displayName,
					objectType: 'person',
					image: {url: response.image.url},
					link: response.link
					
				};
				//aggiorno avatar
				self._update_login_avatar('twitter');
				
				self._show_login_widget_button('twitter','widget');
				if (oc_cookie.get('oc_logged_in')=='twitter'){
				  //entra nella chat
					self._enter_click('twitter');
				}
		
				
			}
		  });
		
		
		
		
	}
  });
	
}


OzioChatPusherChatWidget.prototype._logoutTW_click = function() {
	var self=this;
	//alert("_logoutTW_click");
	oc_cookie.set('oc_logged_in','', { path: '/' });
	oc_cookie.remove('oc_user_id_twitter',{ path: '/' });
	self._logout_server('twitter');
	self._show_login_widget_button('twitter','login');
}

OzioChatPusherChatWidget.prototype._addParameter = function(url, parameterName, parameterValue, atStart/*Add param before others*/) {
    replaceDuplicates = true;
    if(url.indexOf('#') > 0){
        var cl = url.indexOf('#');
        urlhash = url.substring(url.indexOf('#'),url.length);
    } else {
        urlhash = '';
        cl = url.length;
    }
    sourceUrl = url.substring(0,cl);

    var urlParts = sourceUrl.split("?");
    var newQueryString = "";

    if (urlParts.length > 1)
    {
        var parameters = urlParts[1].split("&");
        for (var i=0; (i < parameters.length); i++)
        {
            var parameterParts = parameters[i].split("=");
            if (!(replaceDuplicates && parameterParts[0] == parameterName))
            {
                if (newQueryString == "")
                    newQueryString = "?";
                else
                    newQueryString += "&";
                newQueryString += parameterParts[0] + "=" + (parameterParts[1]?parameterParts[1]:'');
            }
        }
    }
    if (newQueryString == "")
        newQueryString = "?";

    if(atStart){
        newQueryString = '?'+ parameterName + "=" + parameterValue + (newQueryString.length>1?'&'+newQueryString.substring(1):'');
    } else {
        if (newQueryString !== "" && newQueryString != '?')
            newQueryString += "&";
        newQueryString += parameterName + "=" + (parameterValue?parameterValue:'');
    }
    return urlParts[0] + newQueryString + urlhash;
};

function oc_tw_signin_callback(wnd){
	wnd.close();
	console.log('oc_tw_signin_callback');
	//window.location.reload();
	for (var i=0;i<OzioChatPusherChatWidget.instances.length;i++){
		OzioChatPusherChatWidget.instances[i]._loadTW();
	}
	
}

OzioChatPusherChatWidget.prototype._loginTW_click = function() {
	var self=this;
	if (self.logintw_click_pending){
		return;
	}
	self.logintw_click_pending=true;

	oc_cookie.set('oc_logged_in','twitter', { path: '/' });

	var newWindow = window.open("", '', 'width=700,height=500');
	
	jQuery.ajax({
		url: self.settings.chatEndPoint,
		type: 'post',
		dataType: 'json',
		data: {
		  'tw_get': 'loginurl',
		  'tw_return': self._addParameter(window.location.href,"oc_close_window",1,false),
		  'tw_endpoint': self.settings.chatEndPoint
		},
		complete:function(){
			console.log("TW twitter_get complete");
			self.logintw_click_pending=false;
		},
		error:function(){
			newWindow.close();
			//effettuo il logout
			self._logout_click();
		},
		success:function(result){
			console.log("TW twitter_get success");
			//redirect alla login
			//window.location.replace(result.loginurl);
			newWindow.location = result.loginurl;
		}
	});			
	
	
}


//Twitter End

OzioChatPusherChatWidget.prototype._logoutJoomla_click = function() {
	var self=this;
	//alert("_logoutJoomla_click");
	oc_cookie.set('oc_logged_in','', { path: '/' });
	oc_cookie.remove('oc_user_id_joomla',{ path: '/' });
	self._logout_server('joomla');
	this._widget.find('form.oziochat-joomla-logout-form > .submit').click();	
}


OzioChatPusherChatWidget.prototype._loginJoomla_click = function() {
	var self=this;
	oc_cookie.set('oc_logged_in','joomla', { path: '/' });

	window.location=this.settings.joomlaLoggedIn.loginUrl;
}



OzioChatPusherChatWidget.prototype._loadJoomla = function() {
	var self=this;

	if (!self.settings.joomla_login){
		return;
	}
	
	self._show_login_widget_button('joomla','load');
	if (this.settings.joomlaLoggedIn.loggedIn){
		//ok loggato in joomla

		self.actors_ready['joomla']=this.settings.joomlaLoggedIn.actor;
		oc_cookie.set('oc_user_id_joomla','joomla-'+this.settings.joomlaLoggedIn.actor.userid, { path: '/' });
		
		//aggiorno avatar
		self._update_login_avatar('joomla');
	    //self._last_login['joomla']={login_with:'joomla'};				
		
		self._show_login_widget_button('joomla','widget');
		if (oc_cookie.get('oc_logged_in')=='joomla'){
		  //entra nella chat
			self._enter_click('joomla');
		}
		
		
	}else{
		//ko non loggato in joomla
		//Visualiza Bottone Login Joomla
		self._show_login_widget_button('joomla','login');
	}
	
}

OzioChatPusherChatWidget.prototype._logoutAnonymous_click = function() {
	var self=this;
	//alert("_logoutAnonymous_click");
	oc_cookie.set('oc_logged_in','', { path: '/' });
	oc_cookie.remove('oc_user_id_anonymous',{ path: '/' });
	
	oc_cookie.set('oc_anon_name','', { path: '/' });
	oc_cookie.set('oc_anon_email','', { path: '/' });
	oc_cookie.remove('oc_anon_name', { path: '/' });
	oc_cookie.remove('oc_anon_email', { path: '/' });
	self._logout_server('anonymous');
	this._loadAnonymous();
	
}
OzioChatPusherChatWidget.prototype._loginAnonymous_click = function() {
	var self=this;
	//alert("_loginAnonymous_click");
	oc_cookie.set('oc_logged_in','', { path: '/' });
	
	var nickname = jQuery.trim(this._nicknameEl.val()); // optional
	var email = jQuery.trim(this._emailEl.val()); // optional
	if(!nickname) {
		alert(this.settings.i18n['please supply a nickname']);
		return;
	}
	oc_cookie.set('oc_anon_name',nickname, { path: '/' });
	oc_cookie.set('oc_anon_email',email, { path: '/' });
	oc_cookie.set('oc_logged_in','anonymous', { path: '/' });
	this._loadAnonymous();
}

OzioChatPusherChatWidget.prototype._loadAnonymous = function() {
	var self=this;
	if (!self.settings.anonymous_login){
		return;
	}
	//prendo il cookie!
	self._show_login_widget_button('anonymous','load');
	
	var cookie_anonymous_login_name=oc_cookie.get('oc_anon_name');
	var cookie_anonymous_login_email=oc_cookie.get('oc_anon_email');
	
	if (typeof cookie_anonymous_login_name !== 'undefined' && cookie_anonymous_login_name!='') {
		//ok loggato in anonymous
		
		// MD5 (Message-Digest Algorithm) by WebToolkit
 
		var MD5=function(s){function L(k,d){return(k<<d)|(k>>>(32-d))}function K(G,k){var I,d,F,H,x;F=(G&2147483648);H=(k&2147483648);I=(G&1073741824);d=(k&1073741824);x=(G&1073741823)+(k&1073741823);if(I&d){return(x^2147483648^F^H)}if(I|d){if(x&1073741824){return(x^3221225472^F^H)}else{return(x^1073741824^F^H)}}else{return(x^F^H)}}function r(d,F,k){return(d&F)|((~d)&k)}function q(d,F,k){return(d&k)|(F&(~k))}function p(d,F,k){return(d^F^k)}function n(d,F,k){return(F^(d|(~k)))}function u(G,F,aa,Z,k,H,I){G=K(G,K(K(r(F,aa,Z),k),I));return K(L(G,H),F)}function f(G,F,aa,Z,k,H,I){G=K(G,K(K(q(F,aa,Z),k),I));return K(L(G,H),F)}function D(G,F,aa,Z,k,H,I){G=K(G,K(K(p(F,aa,Z),k),I));return K(L(G,H),F)}function t(G,F,aa,Z,k,H,I){G=K(G,K(K(n(F,aa,Z),k),I));return K(L(G,H),F)}function e(G){var Z;var F=G.length;var x=F+8;var k=(x-(x%64))/64;var I=(k+1)*16;var aa=Array(I-1);var d=0;var H=0;while(H<F){Z=(H-(H%4))/4;d=(H%4)*8;aa[Z]=(aa[Z]|(G.charCodeAt(H)<<d));H++}Z=(H-(H%4))/4;d=(H%4)*8;aa[Z]=aa[Z]|(128<<d);aa[I-2]=F<<3;aa[I-1]=F>>>29;return aa}function B(x){var k="",F="",G,d;for(d=0;d<=3;d++){G=(x>>>(d*8))&255;F="0"+G.toString(16);k=k+F.substr(F.length-2,2)}return k}function J(k){k=k.replace(/rn/g,"n");var d="";for(var F=0;F<k.length;F++){var x=k.charCodeAt(F);if(x<128){d+=String.fromCharCode(x)}else{if((x>127)&&(x<2048)){d+=String.fromCharCode((x>>6)|192);d+=String.fromCharCode((x&63)|128)}else{d+=String.fromCharCode((x>>12)|224);d+=String.fromCharCode(((x>>6)&63)|128);d+=String.fromCharCode((x&63)|128)}}}return d}var C=Array();var P,h,E,v,g,Y,X,W,V;var S=7,Q=12,N=17,M=22;var A=5,z=9,y=14,w=20;var o=4,m=11,l=16,j=23;var U=6,T=10,R=15,O=21;s=J(s);C=e(s);Y=1732584193;X=4023233417;W=2562383102;V=271733878;for(P=0;P<C.length;P+=16){h=Y;E=X;v=W;g=V;Y=u(Y,X,W,V,C[P+0],S,3614090360);V=u(V,Y,X,W,C[P+1],Q,3905402710);W=u(W,V,Y,X,C[P+2],N,606105819);X=u(X,W,V,Y,C[P+3],M,3250441966);Y=u(Y,X,W,V,C[P+4],S,4118548399);V=u(V,Y,X,W,C[P+5],Q,1200080426);W=u(W,V,Y,X,C[P+6],N,2821735955);X=u(X,W,V,Y,C[P+7],M,4249261313);Y=u(Y,X,W,V,C[P+8],S,1770035416);V=u(V,Y,X,W,C[P+9],Q,2336552879);W=u(W,V,Y,X,C[P+10],N,4294925233);X=u(X,W,V,Y,C[P+11],M,2304563134);Y=u(Y,X,W,V,C[P+12],S,1804603682);V=u(V,Y,X,W,C[P+13],Q,4254626195);W=u(W,V,Y,X,C[P+14],N,2792965006);X=u(X,W,V,Y,C[P+15],M,1236535329);Y=f(Y,X,W,V,C[P+1],A,4129170786);V=f(V,Y,X,W,C[P+6],z,3225465664);W=f(W,V,Y,X,C[P+11],y,643717713);X=f(X,W,V,Y,C[P+0],w,3921069994);Y=f(Y,X,W,V,C[P+5],A,3593408605);V=f(V,Y,X,W,C[P+10],z,38016083);W=f(W,V,Y,X,C[P+15],y,3634488961);X=f(X,W,V,Y,C[P+4],w,3889429448);Y=f(Y,X,W,V,C[P+9],A,568446438);V=f(V,Y,X,W,C[P+14],z,3275163606);W=f(W,V,Y,X,C[P+3],y,4107603335);X=f(X,W,V,Y,C[P+8],w,1163531501);Y=f(Y,X,W,V,C[P+13],A,2850285829);V=f(V,Y,X,W,C[P+2],z,4243563512);W=f(W,V,Y,X,C[P+7],y,1735328473);X=f(X,W,V,Y,C[P+12],w,2368359562);Y=D(Y,X,W,V,C[P+5],o,4294588738);V=D(V,Y,X,W,C[P+8],m,2272392833);W=D(W,V,Y,X,C[P+11],l,1839030562);X=D(X,W,V,Y,C[P+14],j,4259657740);Y=D(Y,X,W,V,C[P+1],o,2763975236);V=D(V,Y,X,W,C[P+4],m,1272893353);W=D(W,V,Y,X,C[P+7],l,4139469664);X=D(X,W,V,Y,C[P+10],j,3200236656);Y=D(Y,X,W,V,C[P+13],o,681279174);V=D(V,Y,X,W,C[P+0],m,3936430074);W=D(W,V,Y,X,C[P+3],l,3572445317);X=D(X,W,V,Y,C[P+6],j,76029189);Y=D(Y,X,W,V,C[P+9],o,3654602809);V=D(V,Y,X,W,C[P+12],m,3873151461);W=D(W,V,Y,X,C[P+15],l,530742520);X=D(X,W,V,Y,C[P+2],j,3299628645);Y=t(Y,X,W,V,C[P+0],U,4096336452);V=t(V,Y,X,W,C[P+7],T,1126891415);W=t(W,V,Y,X,C[P+14],R,2878612391);X=t(X,W,V,Y,C[P+5],O,4237533241);Y=t(Y,X,W,V,C[P+12],U,1700485571);V=t(V,Y,X,W,C[P+3],T,2399980690);W=t(W,V,Y,X,C[P+10],R,4293915773);X=t(X,W,V,Y,C[P+1],O,2240044497);Y=t(Y,X,W,V,C[P+8],U,1873313359);V=t(V,Y,X,W,C[P+15],T,4264355552);W=t(W,V,Y,X,C[P+6],R,2734768916);X=t(X,W,V,Y,C[P+13],O,1309151649);Y=t(Y,X,W,V,C[P+4],U,4149444226);V=t(V,Y,X,W,C[P+11],T,3174756917);W=t(W,V,Y,X,C[P+2],R,718787259);X=t(X,W,V,Y,C[P+9],O,3951481745);Y=K(Y,h);X=K(X,E);W=K(W,v);V=K(V,g)}var i=B(Y)+B(X)+B(W)+B(V);return i.toLowerCase()};
		
		//@param string $s Size in pixels, defaults to 80px [ 1 - 512 ]
		//@param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
		//@param string $r Maximum rating (inclusive) [ g | pg | r | x ]

	 
		var img_url='http://www.gravatar.com/avatar/' + MD5(cookie_anonymous_login_email.trim().toLowerCase()) + '?s=80&d=mm&r=g';
		
		self.actors_ready['anonymous']={
			displayName: cookie_anonymous_login_name,
			objectType: 'person',
			image: {url: img_url},
			link:''
			
		};
		oc_cookie.set('oc_user_id_anonymous','anonymous-'+cookie_anonymous_login_name, { path: '/' });
		
		
		//aggiorno avatar
		self._update_login_avatar('anonymous');
		
	    //self._last_login['anonymous']={login_with:'anonymous',nickname:cookie_anonymous_login_name,email:cookie_anonymous_login_email};				
		
		
		self._show_login_widget_button('anonymous','widget');
		if (oc_cookie.get('oc_logged_in')=='anonymous'){
		  //entra nella chat
			self._enter_click('anonymous');
		}
		
	}else{
		//ko non loggato in anonymous
		//Visualizza Bottone/Pannello Login anonymous
		self._show_login_widget_button('anonymous','login');
	}
	
}


OzioChatPusherChatWidget.prototype._chatMessageReceived = function(data) {
  var self = this;
  if (this._wnd_sound=='on'){
	oc_play_received_msg();
  }
  
  if(this._itemCount === 0) {
    this._messagesEl.html('');
  }
  
  var messageEl = OzioChatPusherChatWidget._buildListItem(data);
  messageEl.hide();
  this._messagesEl.append(messageEl);
  messageEl.slideDown(function() {
    //if(self._autoScroll) {
      var messageEl = self._messagesEl.get(0);
      //var scrollableHeight = (messageEl.scrollHeight - self._messagesEl.height());
      self._messagesEl.scrollTop(messageEl.scrollHeight);
    //}
  });
  
  ++this._itemCount;
  
  if(this._itemCount > this.settings.maxItems) {
    /* get first li of list */
    this._messagesEl.children(':first').slideUp(function() {
      jQuery(this).remove();
    });
  }
};

/* @private */


/* @private */
OzioChatPusherChatWidget.prototype._sendChatButtonClicked = function() {
  var message = jQuery.trim(this._messageInputEl.val());
  if(!message) {
    alert(this.settings.i18n['please supply a chat message']);
    return;
  }

  var chatInfo = {
    text: message
  };
  this._sendChatMessage(chatInfo);
};

/* @private */
OzioChatPusherChatWidget.prototype._sendChatMessage = function(data) {
  var self = this;
  //var method=oc_cookie.get('oc_logged_in');
  //data['auth']=this._last_login[method];
  //data['actor']=this.actors_ready[method];
  
  self.restart_vitality_timer();
  
  this._messageInputEl.attr('readonly', 'readonly');
  
  jQuery.ajax({
    url: this.settings.chatEndPoint,
    type: 'post',
    dataType: 'json',
    data: {
      'chat_info': data
    },
	error: function(jqXHR, textStatus, errorThrown){
		console.log("OzioChat protocol error: textStatus: "+textStatus+" errorThrown: "+errorThrown+" responseText: "+jqXHR.responseText);
		self._messageInputEl.removeAttr('readonly');
		//logout!
		self._logout_click();
	},
    success: function(result) {
		self._messageInputEl.val('');
		self._messageInputEl.removeAttr('readonly');
    }
  })
};

/* @private */
OzioChatPusherChatWidget.prototype._startTimeMonitor = function() {
	return;//disabilitato
	
  var self = this;
  
  setInterval(function() {
    self._messagesEl.children('.oc-activity').each(function(i, el) {
      var timeEl = jQuery(el).find('a.oc-timestamp span[data-activity-published]');
      var time = timeEl.attr('oc-data-activity-published');
      var newDesc = OzioChatPusherChatWidget.timeToDescription(time);
      timeEl.text(newDesc);
    });
  }, 10 * 1000)
};

/* @private */
OzioChatPusherChatWidget._createHTML = function(appendTo,cloneFrom, oc_popout) {
  if (oc_popout){
	  var widget = jQuery(cloneFrom).clone();
	  //jQuery('body').empty();
	  jQuery('body > div').hide();
	  jQuery('body > a').hide();
	  jQuery(appendTo).append(widget);
  }else{
	
	  var widget = jQuery(cloneFrom);
	  jQuery(appendTo).append(widget);
  }
  widget.show();
  return widget;
};

/* @private */
OzioChatPusherChatWidget._buildMemberItem = function(id,info) {
  var li = jQuery('<li class="oc-activity"></li>');
  li.attr('oc-data-user-id', id);
  var item = jQuery('<div class="oc-stream-item-content"></div>');
  li.append(item);
  
  var imageInfo = info.image;

  var image_img=jQuery('<img />');
  image_img.attr('src', imageInfo.url);
  //image_img.attr('width', imageInfo.width);
  //image_img.attr('height', imageInfo.height);
  
  var image = jQuery('<div class="oc-image"></div>');
  image.append(image_img);

  item.append(image);
  
  var content = jQuery('<div class="oc-content"></div>');
  item.append(content);

  var user_a=jQuery('<a class="oc-screen-name"></a>');
  user_a.text(info.displayName);
  user_a.attr("title",info.displayName);
  
  if (info.link!=''){
	  user_a.attr("href",info.link);
	  user_a.attr("target","_blank");
  }
  
  var user_span=jQuery('<span class="oc-user-name"></span>');
  var user =jQuery('<div class="oc-activity-row"></div>');
  
  user_span.append(user_a);
  user.append(user_span);
  
  content.append(user);
  
  
                
  
  
  return li;
};

/* @private */
OzioChatPusherChatWidget._buildListItem = function(activity) {
  var li = jQuery('<li class="oc-activity"></li>');
  li.attr('oc-data-activity-id', activity.id);
  var item = jQuery('<div class="oc-stream-item-content"></div>');
  li.append(item);
  
  var imageInfo = activity.actor.image;

  var image_img=jQuery('<img />');
  image_img.attr('src', imageInfo.url);
  image_img.attr('width', imageInfo.width);
  image_img.attr('height', imageInfo.height);
  
  var image = jQuery('<div class="oc-image"></div>');
  image.append(image_img);

  //var image = jQuery('<div class="oc-image">' +
  //                '<img src="' + imageInfo.url + '" width="' + imageInfo.width + '" height="' + imageInfo.height + '" />' +
  //              '</div>');
  item.append(image);
  
  var content = jQuery('<div class="oc-content"></div>');
  item.append(content);

  var user_a=jQuery('<a class="oc-screen-name"></a>');
  user_a.text(activity.actor.displayName);
  user_a.attr("title",activity.actor.displayName);
  
  if (activity.actor.link!=''){
	  user_a.attr("href",activity.actor.link);
	  user_a.attr("target","_blank");
  }
  
  
  var user_span=jQuery('<span class="oc-user-name"></span>');
  var user =jQuery('<div class="oc-activity-row"></div>');
  
  user_span.append(user_a);
  user.append(user_span);
  
  //var user = jQuery('<div class="activity-row">' +
  //              '<span class="user-name">' +
  //                '<a class="screen-name" title="' + activity.actor.displayName.replace(/\\'/g, "'") + '">' + activity.actor.displayName.replace(/\\'/g, "'") + '</a>' +
  //              '</span>' +
  //            '</div>');
  content.append(user);
  
  
  var message_text=jQuery('<div class="oc-text"></div>');
  message_text.text(activity.body);
  
  var linkedText = Autolinker.link(message_text.html(), { newWindow: true, className: "oc-linked-text" } );
  message_text.html(linkedText);
  
  var message = jQuery('<div class="oc-activity-row"></div>');
  message.append(message_text);
  
  //var message = jQuery('<div class="activity-row">' +
  //                  '<div class="text">' + activity.body.replace(/\\('|&quot;)/g, '$1') + '</div>' +
  //                '</div>');
  content.append(message);
  
  var span_time=jQuery('<span></span>');
  span_time.attr("title",activity.published);
  span_time.attr("oc-data-activity-published",activity.published);
  //span_time.text(OzioChatPusherChatWidget.timeToDescription(activity.published));
  var timestamp = new Date(Date.parse(activity.published));
  span_time.text(timestamp.toLocaleString());
  
  var time = jQuery('<div class="oc-activity-row">' + 
                '<a class="oc-timestamp">' +
                '</a>' +
                '<span class="oc-activity-actions">' +
                '</span>' +
              '</div>');
			  
	jQuery("a",time).append(span_time);
  
  //var time = jQuery('<div class="activity-row">' + 
  //              '<a ' + (activity.link?'href="' + activity.link + '" ':'') + ' class="timestamp">' +
  //                '<span title="' + activity.published + '" data-activity-published="' + activity.published + '">' + OzioChatPusherChatWidget.timeToDescription(activity.published) + '</span>' +
  //              '</a>' +
  //              '<span class="activity-actions">' +
  //                /*'<span class="tweet-action action-favorite">' +
  //                  '<a href="#" class="like-action" data-activity="like" title="Like"><span><i></i><b>Like</b></span></a>' +
  //                '</span>' +*/
  //              '</span>' +
  //            '</div>');
  content.append(time);
                
  
  return li;
};

/**
 * converts a string into something which can be used as a valid channel name in Pusher.
 * @param {String} from The string to be converted.
 *
 * @see http://pusher.com/docs/client_api_guide/client_channels#naming-channels
 */
OzioChatPusherChatWidget.getValidChannelName = function(from) {
  var pattern = /(\W)+/g;
  return from.replace(pattern, '-');
}

/**
 * converts a string or date parameter into a 'social media style'
 * time description.
 */
OzioChatPusherChatWidget.timeToDescription = function(time) {
  if(time instanceof Date === false) {
    time = new Date(Date.parse(time));
  }
  var desc = "dunno";
  var now = new Date();
  var howLongAgo = (now - time);
  var seconds = Math.round(howLongAgo/1000);
  var minutes = Math.round(seconds/60);
  var hours = Math.round(minutes/60);
  if(seconds === 0) {
    desc = "just now";
  }
  else if(minutes < 1) {
    desc = seconds + " second" + (seconds !== 1?"s":"") + " ago";
  }
  else if(minutes < 60) {
    desc = "about " + minutes + " minute" + (minutes !== 1?"s":"") + " ago";
  }
  else if(hours < 24) {
    desc = "about " + hours + " hour"  + (hours !== 1?"s":"") + " ago";
  }
  else {
    desc = time.getDay() + " " + ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sept", "Oct", "Nov", "Dec"][time.getMonth()]
  }
  return desc;
};



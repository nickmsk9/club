<?php
		if (file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php")) {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/".$lang.".php";
		} else {
			include dirname(__FILE__).DIRECTORY_SEPARATOR."lang/en.php";
		}

		foreach ($jabber_language as $i => $l) {
			$jabber_language[$i] = str_replace("'", "\'", $l);
		}

		include dirname(__FILE__).DIRECTORY_SEPARATOR."config.php";
?>

(function($){   
  
	$.ccjabber = (function () {

		var title = 'Jabber Extension';
		var server = '<?php echo $jabberServer;?>';
		var login = '<a href="javascript:void(0);" onclick="javascript:jqcc.ccjabber.login();"><?php echo $jabber_language[0];?></a>';
		var logout = {};
		logout['facebook'] = '<a href="javascript:void(0);" onclick="javascript:jqcc.ccjabber.logout();"><?php echo $jabber_language[8];?></a>';
		logout['gtalk'] = '<a href="javascript:void(0);" onclick="javascript:jqcc.ccjabber.logout();"><?php echo $jabber_language[13];?></a>';
		var hash = '';
		var messageTimer;
		var friendsTimer;
		var minHeartbeat = 3000;
		var maxHeartbeat = 30000;
		var heartbeatTime = minHeartbeat;
		var heartbeatCount = 1;
		var crossDomain = '<?php echo CROSS_DOMAIN;?>';
		var longNameLength = '<?php echo $longNameLength;?>';

        return {

			getTitle: function() {
				return title;	
			},

			init: function () {
				$('<div class="cometchat_tabsubtitle" id="jabber_login">'+login+'</div>').insertBefore('#cometchat_searchbar');
				var list = '<div id="cometchat_userslist_jabber"></div>';
				$(list).insertAfter('#cometchat_userslist_offline');

				if (jqcc.cookie('<?php echo $cookiePrefix;?>jabber') && jqcc.cookie('<?php echo $cookiePrefix;?>jabber') == 'true') {
					jqcc.ccjabber.process();
				}
			},

			login: function () {
				hash = '';
				baseUrl = $.cometchat.getBaseUrl();
				baseData = $.cometchat.getBaseData();
				baseDomain = document.domain;
				window.open (baseUrl+'extensions/jabber/init.php?basedata='+baseData+'&basedomain='+baseDomain, 'jabber',"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=500,height=190"); 

				if (crossDomain == 1) {
					jqcc.ccjabber.waitForProcessCookie();
				}
			},

			waitForProcessCookie: function () {
				if (jqcc.cookie('cc_jabber_process') == 'true') {
					jqcc.cookie('cc_jabber_process','false');
					jqcc.ccjabber.process();
				} else {
					setTimeout(function() { jqcc.ccjabber.waitForProcessCookie(); }, 1000);
				}
			},

			logout: function () {
				$.cometchat.updateJabberOnlineNumber(0);
				$('.cometchat_subsubtitle').remove();
				hash = '';
				jqcc.cookie('<?php echo $cookiePrefix;?>jabber','false',{ path: '/' });
				$('#jabber_login').html(login);
				$('#cometchat_userslist_jabber').html('');
				clearTimeout(messageTimer);
				$.getJSON(server+"?json_callback=?", {'action':'logout'});
			},

			process: function () {
				
				var head = '<div class="cometchat_subsubtitle"><hr class="hrleft"><?php echo $jabber_language[11];?><hr class="hrright"></div>';
				
				if (jqcc.cookie('<?php echo $cookiePrefix;?>jabber_type') == 'gtalk') {
					head = '<div class="cometchat_subsubtitle"><hr class="hrleft"><?php echo $jabber_language[12];?><hr class="hrright"></div>';
				}
				
				$(head).insertBefore('#cometchat_userslist_jabber');

				head = '<div class="cometchat_subsubtitle cometchat_subsubtitle_top"><hr class="hrleft"><?php echo $jabber_language[10];?><hr class="hrright"></div>';
				$(head).insertBefore('#cometchat_userslist_available');
				
				$('#cometchat_searchbar').css('display','block');

				hash = '';
				$('#jabber_login').html(logout[jqcc.cookie('<?php echo $cookiePrefix;?>jabber_type')]);
				jqcc.ccjabber.getFriendsList(1);
			},

			sendMessage: function (id,message) {
				
				var currenttime = new Date();
				currenttime = parseInt(currenttime.getTime()/1000);
				$.cometchat.addMessage(id,message,1,0,currenttime,1,null);

				id = jqcc.ccjabber.decodeName(id);
				$.getJSON(server+"?json_callback=?", {'action':'sendMessage',to:id,msg:message} , function(data){	
					heartbeatCount = 1;
						
					if (heartbeatTime > minHeartbeat) {
						heartbeatCount = 1;
						clearTimeout(messageTimer);
						heartbeatTime = minHeartbeat;
						messageTimer = setTimeout( function() { jqcc.ccjabber.getMessages(); }, minHeartbeat);
					}
				});
			},

			getRecentData: function(id) {
				var originalid = id;

				id = jqcc.ccjabber.decodeName(id);

				$.getJSON(server+"?json_callback=?", {'action':'getAllMessages',user:id} , function(data){
					if (data) {
						
						var temp = '';

						$.each(data, function(id,message) {
							
							var sent = 0;
							if (message.type == 'sent') { sent = 1; }

							var selfstyle = '';
							if (message.type == 'sent') {
								fromname = '<?php echo $language[10];?>';
								selfstyle = ' cometchat_self';
							} else {
								fromname = $.cometchat.getName(jqcc.ccjabber.encodeName(message.from));
							}
						
							if (fromname.indexOf(" ") != -1) {
								fromname = fromname.slice(0,fromname.indexOf(" "));
							}

							fromname = fromname.split("@")[0];

							message.from = jqcc.ccjabber.encodeName(message.from);

							message.msg = message.msg.replace(/</g, '&lt;').replace(/>/g, '&gt;');

															
							temp += ($.cometchat.processMessage('<div class="cometchat_chatboxmessage" id="cometchat_message_'+message.time+'"><span class="cometchat_chatboxmessagefrom'+selfstyle+'"><strong>'+fromname+'</strong>:&nbsp;&nbsp;</span><span class="cometchat_chatboxmessagecontent'+selfstyle+'">'+message.msg+'</span></div>',selfstyle));


						});

						if (temp != '') {
							$.cometchat.updateHtml(originalid,temp);
						}
					}
				});
			},

			getMessages: function () {

				$.ajax({
					url: server+"?json_callback=?",
					data: {'action':'getRecentMessages'},
					dataType: 'jsonp',
					timeout: 6000,
					error: function() {
						clearTimeout(messageTimer);
						messageTimer = setTimeout( function() { jqcc.ccjabber.getMessages(); }, heartbeatTime);
					},
					success: function(data) {
						if (data) {
							if (data[0] && data[0].error == '1') {
								jqcc.ccjabber.logout();
							} else {

								$.each(data, function(id,message) {
									message.from = jqcc.ccjabber.encodeName(message.from);

									$.cometchat.addMessage(message.from,message.msg,0,0,message.time,1,null);

									heartbeatTime = minHeartbeat;
								});

								heartbeatCount++;

								if (heartbeatTime != maxHeartbeat) {
									if (heartbeatCount > 4) {
										heartbeatTime *= 2;
										heartbeatCount = 1;
									}

									if (heartbeatTime > maxHeartbeat) {
										heartbeatTime = maxHeartbeat;
									}
								} else {
									if (heartbeatCount > 20) {
										jqcc.ccjabber.logout();
									}
								}

								clearTimeout(messageTimer);
								messageTimer = setTimeout( function() { jqcc.ccjabber.getMessages(); }, heartbeatTime);

							}
						}
					}
				});		

			},

			getFriendsList: function (first) {
				$.getJSON(server+"?json_callback=?", {'action':'getOnlineBuddies', md5: hash} , function(data){
					
					if (data[0] && data[0].error == '1') {
						jqcc.ccjabber.logout();
					} else {

						var buddylisttemp = '';
						var buddylisttempavatar = '';
						var md5updated = 0;
						var onlineNumber = 0;

						$.each(data, function(id,user) {

							if (user.id) {	
								++onlineNumber;
								user.id = jqcc.ccjabber.encodeName(user.id);
								user.n = user.n.split("@")[0];

								if (user.n.length > longNameLength) {
									shortname = user.n.substr(0,longNameLength)+'...';
								} else {
									shortname = user.n;
								}
								
								buddylisttemp += '<div id="cometchat_userlist_'+user.id+'" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentname">'+shortname+'</span><span class="cometchat_userscontentdot cometchat_'+user.s+'"></span></div>';

								buddylisttempavatar += '<div id="cometchat_userlist_'+user.id+'" class="cometchat_userlist" onmouseover="jqcc(this).addClass(\'cometchat_userlist_hover\');" onmouseout="jqcc(this).removeClass(\'cometchat_userlist_hover\');"><span class="cometchat_userscontentavatar"><img class="cometchat_userscontentavatarimage" original="'+user.a+'"></span><span class="cometchat_userscontentname">'+shortname+'</span><span class="cometchat_userscontentdot cometchat_'+user.s+'"></span></div>';

								$.cometchat.userAdd(user.id,user.s,user.m,user.n,user.a,'');
							}

							if (user.md5) {
								hash = user.md5;
								md5updated = 1;
							}

						});

						if (onlineNumber == 0) {
							buddylisttempavatar = ('<div class="cometchat_nofriends" style="margin-bottom:10px"><?php echo $jabber_language[14];?></div>');
						}
						
						if (md5updated) {
							if (jqcc.cookie('<?php echo $cookiePrefix;?>jabber') && jqcc.cookie('<?php echo $cookiePrefix;?>jabber') == 'true') {
								$.cometchat.updateJabberOnlineNumber(onlineNumber);
								$.cometchat.replaceHtml('cometchat_userslist_jabber', '<div>'+buddylisttempavatar+'</div>');
								$('.cometchat_userlist').unbind('click');
								$('.cometchat_userlist').bind('click', function(e) {
									$.cometchat.userClick(e.target); 
								});

								if ($.cometchat.getSessionVariable('buddylist') == 1) {
									$(".cometchat_userscontentavatar img").each(function() {
										if ($(this).attr('original')) {
											$(this).attr("src", $(this).attr('original'));
											$(this).removeAttr('original');
										}
									});
								}

								$('#cometchat_search').keyup();
							}
						}

						clearTimeout(friendsTimer);
						friendsTimer = setTimeout( function() { jqcc.ccjabber.getFriendsList(); }, 60000);

						if (first) {
							jqcc.ccjabber.getMessages();
						}

					}
				});
	
			},

			encodeName: function(name) {
				name = name.toLowerCase();
				name = name.replace('-','M');
				name = name.replace('@','A');
				name = name.replace(/\./g,'D');
				return name;
			},

			decodeName: function(name) {
				name = name.replace('M','-');
				name = name.replace('A','@');
				name = name.replace(/D/g,'\.');
				return name;
			}


        };
    })();
 
})(jqcc);
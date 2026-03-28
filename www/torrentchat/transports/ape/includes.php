<?php

/*

CometChat
Copyright (c) 2011 Inscripts

CometChat ('the Software') is a copyrighted work of authorship. Inscripts 
retains ownership of the Software and any copies of it, regardless of the 
form in which the copies may exist. This license is not a sale of the 
original Software or any copies.

By installing and using CometChat on your server, you agree to the following
terms and conditions. Such agreement is either on your own behalf or on behalf
of any corporate entity which employs you or which you represent
('Corporate Licensee'). In this Agreement, 'you' includes both the reader
and any Corporate Licensee and 'Inscripts' means Inscripts (I) Private Limited:

CometChat license grants you the right to run one instance (a single installation)
of the Software on one web server and one web site for each license purchased.
Each license may power one instance of the Software on one domain. For each 
installed instance of the Software, a separate license is required. 
The Software is licensed only to you. You may not rent, lease, sublicense, sell,
assign, pledge, transfer or otherwise dispose of the Software in any form, on
a temporary or permanent basis, without the prior written consent of Inscripts. 

The license is effective until terminated. You may terminate it
at any time by uninstalling the Software and destroying any copies in any form. 

The Software source code may be altered (at your risk) 

All Software copyright notices within the scripts must remain unchanged (and visible). 

The Software may not be used for anything that would represent or is associated
with an Intellectual Property violation, including, but not limited to, 
engaging in any activity that infringes or misappropriates the intellectual property
rights of others, including copyrights, trademarks, service marks, trade secrets, 
software piracy, and patents held by individuals, corporations, or other entities. 

If any of the terms of this Agreement are violated, Inscripts reserves the right 
to revoke the Software license at any time. 

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

?>

var APE={Config:{identifier:'ape',init:true,frequency:0,scripts:[]},Client:function(core){if(core)this.core=core;}}
APE.Client.prototype.eventProxy=[];APE.Client.prototype.fireEvent=function(type,args,delay){this.core.fireEvent(type,args,delay);}
APE.Client.prototype.addEvent=function(type,fn,internal){var newFn=fn.bind(this),ret=this;if(this.core==undefined){this.eventProxy.push([type,fn,internal]);}else{var ret=this.core.addEvent(type,newFn,internal);this.core.$originalEvents[type]=this.core.$originalEvents[type]||[];this.core.$originalEvents[type][fn]=newFn;}
return ret;}
APE.Client.prototype.removeEvent=function(type,fn){return this.core.removeEvent(type,fn);}
APE.Client.prototype.onRaw=function(type,fn,internal){this.addEvent('raw_'+type.toLowerCase(),fn,internal);}
APE.Client.prototype.onCmd=function(type,fn,internal){this.addEvent('cmd_'+type.toLowerCase(),fn,internal);}
APE.Client.prototype.onError=function(type,fn,internal){this.addEvent('error_'+type,fn,internal);}
APE.Client.prototype.cookie={};APE.Client.prototype.cookie.write=function(name,value){document.cookie=name+"="+encodeURIComponent(value)+"; domain="+document.domain;}
APE.Client.prototype.cookie.read=function(name){var nameEQ=name+"=";var ca=document.cookie.split(';');for(var i=0;i<ca.length;i++){var c=ca[i];while(c.charAt(0)==' ')c=c.substring(1,c.length);if(c.indexOf(nameEQ)==0){return decodeURIComponent(c.substring(nameEQ.length,c.length));}}
return null;}
APE.Client.prototype.load=function(config){config=config||{};config.transport=config.transport||APE.Config.transport||0;config.frequency=config.frequency ||0;config.domain=config.domain||APE.Config.domain||document.domain;config.scripts=config.scripts||APE.Config.scripts;config.server=config.server||APE.Config.server;config.init=function(core){this.core=core;for(var i=0;i<this.eventProxy.length;i++){this.addEvent.apply(this,this.eventProxy[i]);}}.bind(this);if(config.transport!=2&&config.domain!='auto')document.domain=config.domain;if(config.domain=='auto')document.domain=document.domain;var cookie=this.cookie.read('APE_Cookie');var tmp=eval('('+cookie+')');if(tmp){config.frequency=tmp.frequency+1;}else{cookie='{"frequency":0}';}
var reg=new RegExp('"frequency":([ 0-9]+)',"g")
cookie=cookie.replace(reg,'"frequency":'+config.frequency);this.cookie.write('APE_Cookie',cookie);var iframe=document.createElement('iframe');iframe.setAttribute('id','ape_'+config.identifier);iframe.style.display='none';iframe.style.position='absolute';iframe.style.left='-300px';iframe.style.top='-300px';document.body.appendChild(iframe);if(config.transport==2){var doc=iframe.contentDocument;if(!doc)doc=iframe.contentWindow.document;doc.open();var theHtml='<html><head></head>';for(var i=0;i<config.scripts.length;i++){theHtml+='<script src="'+config.scripts[i]+'"></script>';}
theHtml+='<body></body></html>';doc.write(theHtml);doc.close();}else{iframe.setAttribute('src','http://'+config.frequency+'.'+config.server+'/?[{"cmd":"script","params":{"domain":"'+document.domain+'","scripts":["'+config.scripts.join('","')+'"]}}]');if(navigator.product=='Gecko'){iframe.contentWindow.location.href=iframe.getAttribute('src');}}
iframe.onload=function(){if(!iframe.contentWindow.APE)setTimeout(iframe.onload,100);else iframe.contentWindow.APE.init(config);}}
if(Function.prototype.bind==null){Function.prototype.bind=function(bind,args){return this.create({'bind':bind,'arguments':args});}}
if(Function.prototype.create==null){Function.prototype.create=function(options){var self=this;options=options||{};return function(){var args=options.arguments||arguments;if(args&&!args.length){args=[args];}
var returns=function(){return self.apply(options.bind||null,args);};return returns();};}}

APE.Config.baseUrl = '<?php echo $ape_baseurl;?>';
APE.Config.domain = '<?php echo $ape_domain;?>'; 
APE.Config.server = '<?php echo $ape_server;?>';
APE.Config.transport = 2; 

(function(){
	for (var i = 0; i < arguments.length; i++)
		APE.Config.scripts.push(APE.Config.baseUrl + '/Source/' + arguments[i] + '.js');
})('mootools-core', 'Core/APE', 'Core/Events', 'Core/Core', 'Pipe/Pipe', 'Pipe/PipeProxy', 'Pipe/PipeMulti', 'Pipe/PipeSingle', 'Request/Request','Request/Request.Stack', 'Request/Request.CycledStack', 'Transport/Transport.longPolling','Transport/Transport.SSE', 'Transport/Transport.XHRStreaming', 'Transport/Transport.JSONP', 'Core/Utility', 'Core/JSON');
 
var apeServer = null;

jqcc(document).ready(function () {   
		var client = new APE.Client;		
		client.load({
			'domain': APE.Config.domain,
			'server': APE.Config.server,
			'identifier': 'jquery',
			'complete': function(ape){
				apeServer = ape;
				ape.start({'name': String((new Date()).getTime()).replace(/\D/gi,'')});
				cometready();
			},
			'scripts': APE.Config.scripts
		});
});

function cometcall_function(id,td,callbackfn) {

	apeServer.join(id);
	apeServer.addEvent('onRaw', function(args) {
		if (args.raw == 'postmsg') {
			var incoming = args.data.message;

			incoming.message = unescape(incoming.message);

			if (callbackfn != '') {
				jqcc[callbackfn].newMessage(incoming);
			}

			var ts = Math.round(new Date().getTime() / 1000)+''+Math.floor(Math.random()*1000000)
			jqcc.cometchat.addMessage(incoming.from, incoming.message, incoming.self, 0, ts, 0, incoming.sent+td);
		}
	});
}
/**
 * @author http://www.cosmosfarm.com/
 */

var COSMOSFARM = {
	app_id:'',
	access_token:'',
	api_url:'http://www.cosmosfarm.com/apis',
	init:function(app_id, access_token){
		this.app_id = app_id;
		this.access_token = access_token;
	},
	ajaxGet:function(url, data, callback){
		var xmlhttp;
		if(window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
		else xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		xmlhttp.onreadystatechange = function(){
			if(xmlhttp.readyState==4 && xmlhttp.status==200){
				alert('test');
				if(typeof callback === 'function'){
		            callback(xmlhttp.responseText);
		        }
			}
		}
		xmlhttp.open('GET', url + (data?'?'+data:''), true);
		xmlhttp.send();
	},
	ajaxPost:function(url, data, callback){
		var xmlhttp;
		if(window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
		else xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		xmlhttp.onreadystatechange = function(){
			if(xmlhttp.readyState==4 && xmlhttp.status==200){
				if(typeof callback === 'function'){
		            callback(xmlhttp.responseText);
		        }
			}
		}
		xmlhttp.open('POST', url, true);
		xmlhttp.send(data);
	},
	api:function(command, data, callback, error){
		callback_name = "_COSMOSFARM_callback_" + (new Date()).getTime();
		error_name = "_COSMOSFARM_error_" + (new Date()).getTime();
		if(typeof callback !== 'function') callback = function(res){};
		if(typeof error !== 'function') error = function(res){};
		window[callback_name] = callback;
		window[error_name] = error;
		js = document.createElement('script');
		js.src = this.api_url + escape(command) + '?' + data + '&callback=' + callback_name + '&error=' + error_name + '&app_id=' + this.app_id + '&access_token=' + this.access_token;
		js.type = 'text/javascript';
		document.getElementsByTagName('head')[0].appendChild(js);
	},
	oauthStatus:function(callback, error){
		this.api('/oauth_status', '', callback, error);
	},
	loginStatus:function(callback, error){
		this.api('/login_status', '', callback, error);
	},
	getLoginUrl:function(redirect_url){
		return this.api_url + '/request_access_token?app_id=' + this.app_id + '&redirect_url=' + redirect_url;
	}
}
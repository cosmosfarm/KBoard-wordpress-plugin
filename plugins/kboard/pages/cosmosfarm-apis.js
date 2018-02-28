/**
 * @author http://www.cosmosfarm.com/
 */

var cosmosfarm = {
	app_id:'',
	access_token:'',
	api_url:'//www.cosmosfarm.com/apis',
	callback_index:0,
	init:function(app_id, access_token){
		this.app_id = app_id;
		this.access_token = access_token;
	},
	api:function(command, data, callback, error){
		callback_name = "_COSMOSFARM_callback_" + (new Date()).getTime() + '_' + this.callback_index;
		error_name = "_COSMOSFARM_error_" + (new Date()).getTime() + '_' + this.callback_index++;
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
	getProfile:function(callback, error){
		this.api('/me', '', callback, error);
	},
	getLoginUrl:function(redirect_url){
		return this.api_url + '/request_access_token?app_id=' + this.app_id + '&redirect_url=' + redirect_url;
	},
	getWpstoreProducts:function(category, page, rpp, callback, error){
		this.api('/wpstore_products/'+category, 'page='+page+'&rpp='+rpp, callback, error);
	}
}
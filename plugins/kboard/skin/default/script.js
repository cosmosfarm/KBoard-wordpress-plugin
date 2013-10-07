/**
 * @author http://www.cosmosfarm.com/
 */

var console = window.console || { log: function() {} };
jQuery.fn.exists = function(){
	return this.length>0;
}

function kboard_editor_execute(form){
	var $ = jQuery;
	
	if(!$('input[name=title]', form).val()){
		alert(kboard_localize.please_enter_a_title);
		$('input[name=title]', form).focus();
		return false;
	}
	else if($('input[name=member_display]', form).eq(1).exists() && !$('input[name=member_display]', form).eq(1).val()){
		alert(kboard_localize.please_enter_a_author);
		$('[name=member_display]', form).eq(1).focus();
		return false;
	}
	else if($('input[name=password]', form).exists() && !$('input[name=password]', form).val()){
		alert(kboard_localize.please_enter_a_password);
		$('input[name=password]', form).focus();
		return false;
	}
	else if($('input[name=captcha]', form).exists() && !$('input[name=captcha]', form).val()){
		alert(kboard_localize.please_enter_the_CAPTCHA_code);
		$('input[name=captcha]', form).focus();
		return false;
	}
	
	return true;
}
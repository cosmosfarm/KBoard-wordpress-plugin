/**
 * @author http://www.cosmosfarm.com/
 */

var console = window.console || { log: function() {} };

function kboard_comments_execute(form){
	jQuery.fn.exists = function(){
		return this.length>0;
	};
	
	if(jQuery('input[name=member_display]', form).exists() && !jQuery('input[name=member_display]', form).val()){
		alert(kboard_comments_localize.please_enter_a_author);
		jQuery('[name=member_display]', form).focus();
		return false;
	}
	else if(jQuery('input[name=password]', form).exists() && !jQuery('input[name=password]', form).val()){
		alert(kboard_comments_localize.please_enter_a_password);
		jQuery('input[name=password]', form).focus();
		return false;
	}
	else if(jQuery('input[name=captcha]', form).exists() && !jQuery('input[name=captcha]', form).val()){
		alert(kboard_comments_localize.please_enter_the_CAPTCHA_code);
		jQuery('input[name=captcha]', form).focus();
		return false;
	}
	else if(jQuery('textarea[name=content]', form).exists() && !jQuery('textarea[name=content]', form).val()){
		alert(kboard_comments_localize.type_the_content_of_the_comment);
		jQuery('textarea[name=content]', form).focus();
		return false;
	}
	
	return true;
}

function kboard_comments_open_confirm(url){
	var width = 300;
	var height = 150;
	window.open(url, 'kboard_comments_password_confirm', 'top='+(screen.availHeight*0.5-height*0.5)+',left='+(screen.availWidth*0.5-width*0.5)+',width='+width+',height='+height+',resizable=0,scrollbars=1');
	return false;
}

function kboard_comments_reply(obj, form_id, cancel_id){
	if(jQuery(obj).hasClass('kboard-reply-active')){
		jQuery(cancel_id).append(jQuery('.kboard-comments-form'));
		jQuery('.kboard-reply').text(kboard_comments_localize.reply).removeClass('kboard-reply-active');
	}
	else{
		jQuery(form_id).append(jQuery('.kboard-comments-form'));
		jQuery('textarea[name=content]').focus();
		jQuery('.kboard-reply').text(kboard_comments_localize.reply).removeClass('kboard-reply-active');
		jQuery(obj).text(kboard_comments_localize.cancel).addClass('kboard-reply-active');
	}
	return false;
}
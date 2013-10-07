/**
 * @author http://www.cosmosfarm.com/
 */

var console = window.console || { log: function() {} };
jQuery.fn.exists = function(){
	return this.length>0;
}

function kboard_comments_execute(form){
	var $ = jQuery;
	
	if($('input[name=member_display]', form).exists() && !$('input[name=member_display]', form).val()){
		alert(kboard_comments_localize.please_enter_a_author);
		$('[name=member_display]', form).focus();
		return false;
	}
	else if($('input[name=password]', form).exists() && !$('input[name=password]', form).val()){
		alert(kboard_comments_localize.please_enter_a_password);
		$('input[name=password]', form).focus();
		return false;
	}
	else if($('input[name=captcha]', form).exists() && !$('input[name=captcha]', form).val()){
		alert(kboard_comments_localize.please_enter_the_CAPTCHA_code);
		$('input[name=captcha]', form).focus();
		return false;
	}
	else if($('textarea[name=content]', form).exists() && !$('textarea[name=content]', form).val()){
		alert(kboard_comments_localize.type_the_content_of_the_comment);
		$('textarea[name=content]', form).focus();
		return false;
	}
	
	return true;
}

function kboard_comments_open_confirm(url){
	var width = 300;
	var height = 150;
	window.open(url, '', 'top='+(screen.availHeight*0.5-height*0.5)+',left='+(screen.availWidth*0.5-width*0.5)+',width='+width+',height='+height+',resizable=0,scrollbars=1');
	return false;
}

function kboard_comments_reply(obj, form_id, cancel_id){
	var $ = jQuery;
	
	if($(obj).hasClass('kboard-reply-active')){
		$(cancel_id).append($('.kboard-comments-form'));
		$('.kboard-reply').text(kboard_comments_localize.reply).removeClass('kboard-reply-active');
	}
	else{
		$(form_id).append($('.kboard-comments-form'));
		$('textarea[name=content]').focus();
		$('.kboard-reply').text(kboard_comments_localize.reply).removeClass('kboard-reply-active');
		$(obj).text(kboard_comments_localize.cancel).addClass('kboard-reply-active');
	}
	
	return false;
}
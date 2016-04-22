/**
 * @author http://www.cosmosfarm.com/
 */

function kboard_comments_execute(form){
	jQuery.fn.exists = function(){
		return this.length>0;
	};
	
	if(jQuery('input[name=member_display]', form).exists() && !jQuery('input[name=member_display]', form).val()){
		alert(kboard_comments_localize_strings.please_enter_the_author);
		jQuery('[name=member_display]', form).focus();
		return false;
	}
	else if(jQuery('input[name=password]', form).exists() && !jQuery('input[name=password]', form).val()){
		alert(kboard_comments_localize_strings.please_enter_the_password);
		jQuery('input[name=password]', form).focus();
		return false;
	}
	else if(jQuery('input[name=captcha]', form).exists() && !jQuery('input[name=captcha]', form).val()){
		alert(kboard_comments_localize_strings.please_enter_the_CAPTCHA);
		jQuery('input[name=captcha]', form).focus();
		return false;
	}
	else if(jQuery('textarea[name=content]', form).exists() && !jQuery('textarea[name=content]', form).val()){
		alert(kboard_comments_localize_strings.please_enter_the_content);
		jQuery('textarea[name=content]', form).focus();
		return false;
	}
	
	return true;
}

function kboard_comments_delete(url){
	if(confirm(kboard_comments_localize_strings.are_you_sure_you_want_to_delete)){
		window.location.href = url;
	}
	return false;
}

function kboard_comments_open_confirm(url){
	var width = 500;
	var height = 250;
	window.open(url, 'kboard_comments_password_confirm', 'top='+(screen.availHeight*0.5-height*0.5)+',left='+(screen.availWidth*0.5-width*0.5)+',width='+width+',height='+height+',resizable=0,scrollbars=1');
	return false;
}

function kboard_comments_open_edit(url){
	var width = 500;
	var height = 250;
	window.open(url, 'kboard_comments_edit', 'top='+(screen.availHeight*0.5-height*0.5)+',left='+(screen.availWidth*0.5-width*0.5)+',width='+width+',height='+height);
	return false;
}

function kboard_comments_reply(obj, form_id, cancel_id){
	if(jQuery(obj).hasClass('kboard-reply-active')){
		jQuery(cancel_id).append(jQuery('.kboard-comments-form'));
		jQuery('.kboard-reply').text(kboard_comments_localize_strings.reply).removeClass('kboard-reply-active');
	}
	else{
		jQuery(form_id).append(jQuery('.kboard-comments-form'));
		jQuery('textarea[name=content]').focus();
		jQuery('.kboard-reply').text(kboard_comments_localize_strings.reply).removeClass('kboard-reply-active');
		jQuery(obj).text(kboard_comments_localize_strings.cancel).addClass('kboard-reply-active');
	}
	return false;
}
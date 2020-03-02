/**
 * @author https://www.cosmosfarm.com/
 */

function kboard_comments_execute(form){
	jQuery.fn.exists = function(){
		return this.length>0;
	};
	
	/*
	 * 잠시만 기다려주세요.
	 */
	if(jQuery(form).data('submitted')){
		alert(kboard_comments_localize_strings.please_wait);
		return false;
	}
	
	/*
	 * 폼 유효성 검사
	 */
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
	
	jQuery(form).data('submitted', 'submitted');
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

function kboard_comments_reply(obj, form_id, cancel_id, content_uid){
	var parents = jQuery(obj).parents('#kboard-comments-'+content_uid);
	if(jQuery(obj).hasClass('kboard-reply-active')){
		jQuery(cancel_id).append(jQuery('.kboard-comments-form', parents));
		jQuery('.kboard-reply', parents).text(kboard_comments_localize_strings.reply).removeClass('kboard-reply-active');
	}
	else{
		jQuery(form_id).append(jQuery('.kboard-comments-form', parents));
		jQuery('textarea[name=comment_content]', parents).focus();
		jQuery('.kboard-reply', parents).text(kboard_comments_localize_strings.reply).removeClass('kboard-reply-active');
		jQuery(obj).text(kboard_comments_localize_strings.cancel).addClass('kboard-reply-active');
	}
	if(typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor){
		tinyMCE.EditorManager.execCommand('mceFocus', false, 'comment_content_'+content_uid);      
		tinyMCE.EditorManager.execCommand('mceRemoveEditor', true, 'comment_content_'+content_uid);
		tinyMCE.EditorManager.execCommand('mceAddEditor', true, 'comment_content_'+content_uid);
	}
	return false;
}

function kboard_comments_field_show(form){
	if(typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor){
		form = jQuery(form.target.formElement);
	}

	jQuery('.comments-field-wrap').hide();
	jQuery('.comments-submit-button').hide();
	
	jQuery('.comments-field-wrap', form).show();
	jQuery('.comments-submit-button', form).show();
}

jQuery(document).ready(function(){
	jQuery(document).on('focus', 'textarea[name=comment_content]', function(){
		kboard_comments_field_show(jQuery(this).parents('.kboard-comments-form'));
	});
});
/**
 * @author http://www.cosmosfarm.com/
 */

function kboard_editor_execute(form){
	jQuery.fn.exists = function(){
		return this.length>0;
	};
	
	if(!jQuery('input[name=title]', form).val()){
		alert(kboard_localize_strings.please_enter_the_title);
		jQuery('input[name=title]', form).focus();
		return false;
	}
	else if(jQuery('input[name=member_display]', form).eq(1).exists() && !jQuery('input[name=member_display]', form).eq(1).val()){
		alert(kboard_localize_strings.please_enter_the_author);
		jQuery('[name=member_display]', form).eq(1).focus();
		return false;
	}
	else if(jQuery('input[name=password]', form).exists() && !jQuery('input[name=password]', form).val()){
		alert(kboard_localize_strings.please_enter_the_password);
		jQuery('input[name=password]', form).focus();
		return false;
	}
	else if(jQuery('input[name=captcha]', form).exists() && !jQuery('input[name=captcha]', form).val()){
		alert(kboard_localize_strings.please_enter_the_CAPTCHA);
		jQuery('input[name=captcha]', form).focus();
		return false;
	}
	
	return true;
}
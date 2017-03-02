/**
 * @author http://www.cosmosfarm.com/
 */

function kboard_editor_execute(form){
	jQuery.fn.exists = function(){
		return this.length>0;
	};
	
	/*
	 * 잠시만 기다려주세요.
	 */
	if(jQuery(form).data('submitted')){
		alert(kboard_localize_strings.please_wait);
		return false;
	}
	
	/*
	 * 폼 유효성 검사
	 */
	if(jQuery('input[name=member_display]', form).eq(1).exists() && !jQuery('input[name=member_display]', form).eq(1).val()){
		// 이름 필드가 있을 경우 필수로 입력합니다.
		alert(kboard_localize_strings.please_enter_the_name);
		jQuery('[name=member_display]', form).eq(1).focus();
		return false;
	}
	if(jQuery('input[name=kboard_option_email]', form).eq(1).exists() && !jQuery('input[name=kboard_option_email]', form).eq(1).val()){
		// 이메일 필드가 있을 경우 필수로 입력합니다.
		alert(kboard_localize_strings.please_enter_the_email);
		jQuery('[name=kboard_option_email]', form).eq(1).focus();
		return false;
	}
	if(!jQuery('input[name=title]', form).val()){
		// 제목 필드는 항상 필수로 입력합니다.
		alert(kboard_localize_strings.please_enter_the_title);
		jQuery('input[name=title]', form).focus();
		return false;
	}
	if(jQuery('input[name=captcha]', form).exists() && !jQuery('input[name=captcha]', form).val()){
		// 캡차 필드가 있을 경우 필수로 입력합니다.
		alert(kboard_localize_strings.please_enter_the_CAPTCHA);
		jQuery('input[name=captcha]', form).focus();
		return false;
	}
	
	jQuery(form).data('submitted', 'submitted');
	jQuery('[type=submit]', form).text(kboard_localize_strings.please_wait);
	jQuery('[type=submit]', form).val(kboard_localize_strings.please_wait);
	return true;
}
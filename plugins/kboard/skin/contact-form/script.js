/**
 * @author https://www.cosmosfarm.com
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
	var validation = '';
	kboard_fields_validation(form, function(fields){
		if(fields){
			validation = fields;
			jQuery(fields).focus();
		}
	});
	
	if(!validation){
		if(parseInt(jQuery('input[name=user_id]', form).val()) > 0){
			// 로그인 사용자의 경우 비밀글 체크시에만 비밀번호를 필수로 입력합니다.
			if(jQuery('input[name=secret]', form).prop('checked') && !jQuery('input[name=password]', form).val()){
				alert(kboard_localize_strings.please_enter_the_password);
				jQuery('input[name=password]', form).focus();
				return false;
			}
		}
		else{
			// 비로그인 사용자는 반드시 비밀번호를 입력해야 합니다.
			if(!jQuery('input[name=password]', form).val()){
				alert(kboard_localize_strings.please_enter_the_password);
				jQuery('input[name=password]', form).focus();
				return false;
			}
		}
		if(jQuery('input[name=captcha]', form).exists() && !jQuery('input[name=captcha]', form).val()){
			// 캡차 필드가 있을 경우 필수로 입력합니다.
			alert(kboard_localize_strings.please_enter_the_CAPTCHA);
			jQuery('input[name=captcha]', form).focus();
			return false;
		}
		
		jQuery(form).data('validation', 'ok');
	}
	
	if(jQuery(form).data('validation') == 'ok'){
		jQuery(form).data('submitted', 'submitted');
		jQuery('[type=submit]', form).text(kboard_localize_strings.please_wait);
		jQuery('[type=submit]', form).val(kboard_localize_strings.please_wait);
		return true;
	}
	
	return false;
}

function kboard_radio_reset(obj){
	jQuery(obj).parents('.kboard-attr-row').find('input[type=radio]').each(function(){
		jQuery(this).prop('checked',false);
	});
}
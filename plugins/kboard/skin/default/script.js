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
		
		if(jQuery('input[name=notice]').prop('checked')){
			var noticeExpiredDate = jQuery('input[name=notice_expired_date]', form).val();
			if(noticeExpiredDate){
				var today = new Date();
				today.setHours(0, 0, 0, 0); // 시간 무시
		
				var selectedDate = new Date(noticeExpiredDate);
		
				if(selectedDate < today){
					alert('공지사항 만료 기간은 오늘 이후로 설정해 주세요.');
					jQuery('input[name=notice_expired_date]', form).focus();
					return false;
				}
			}
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

function kboard_toggle_password_field(checkbox){
	var form = jQuery(checkbox).parents('.kboard-form');
	if(jQuery(checkbox).prop('checked')){
		jQuery('.secret-password-row', form).show();
		setTimeout(function(){
			jQuery('.secret-password-row input[name=password]', form).focus();
		}, 0);
	}
	else{
		jQuery('.secret-password-row', form).hide();
		jQuery('.secret-password-row input[name=password]', form).val('');
	}
}

function kboard_toggle_notice_expired_date(checkbox){
	var form = jQuery(checkbox).closest('.kboard-form');
	
	var use_notice_expiration = jQuery('input[name=use_notice_expiration]', form).val(); // 숨겨진 input으로 받아와야 해

	if(jQuery(checkbox).prop('checked') && use_notice_expiration == '1'){
		jQuery('.notice-expired-date-row', form).show();
		setTimeout(function(){
			jQuery('.notice-expired-date-row input[name=notice_expired_date]', form).focus();
		}, 0);
	}
	else{
		jQuery('.notice-expired-date-row', form).hide();
		jQuery('.notice-expired-date-row input[name=notice_expired_date]', form).val('');
	}
}

function kboard_radio_reset(obj){
	jQuery(obj).parents('.kboard-attr-row').find('input[type=radio]').each(function(){
		jQuery(this).prop('checked',false);
	});
}

jQuery(window).bind('beforeunload',function(e){
	e = e || window.event;
	if(jQuery('.kboard-form').data('submitted') != 'submitted'){
		var dialogText = kboard_localize_strings.changes_you_made_may_not_be_saved;
		e.returnValue = dialogText;
		return dialogText;
	}
});
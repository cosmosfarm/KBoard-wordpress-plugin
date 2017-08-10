<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1>
		<?php echo __('KBoard : 대시보드', 'kboard')?>
		<a href="http://www.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
		<a href="http://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
		<a href="http://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
		<a href="http://blog.cosmosfarm.com/" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
	</h1>
	<div id="welcome-panel" class="welcome-panel">
		<?php include 'welcome.php'?>
	</div>
	<h2 class="nav-tab-wrapper">
		<a href="#" class="nav-tab nav-tab-active" onclick="return false;"><?php echo __('시스템 설정', 'kboard')?></a>
	</h2>
	<ul id="kboard-dashboard-options">
		<li id="kboard_xssfilter">
			<h4><?php echo get_option('kboard_xssfilter')?'해킹 차단 옵션이 비활성화 되어 있습니다.':'해킹으로 부터 보호되고 있습니다.'?></h4>
			<p>
			서버에 ModSecurity등의 방화벽이 설치되어 있으면 이 옵션을 비활성화 가능합니다.<br>
			이 옵션을 100% 신뢰하지 마세요, 서버와 네트워크에 방화벽 설치를 권장합니다.<br>
			이 옵션을 비활성화 하면 시스템 속도가 빨라집니다.
			</p>
			<p><button class="button-secondary" onclick="kboard_system_option_update('kboard_xssfilter', '<?php echo get_option('kboard_xssfilter')?'':'1'?>')">XSS공격 차단 <?php echo get_option('kboard_xssfilter')?'활성화':'비활성화'?></button></p>
		</li>
		<li id="kboard_fontawesome">
			<h4><?php echo get_option('kboard_fontawesome')?'Font Awesome 사용 중지되었습니다.':'Font Awesome 사용 가능합니다.'?></h4>
			<p>
			Font Awesome은 오픈소스 아이콘 폰트 입니다.<br>
			KBoard의 게시판 스킨에 사용되고 있습니다.<br>
			테마의 레이아웃 또는 버튼이 깨지거나 다른 플러그인과 충돌이 발생되면 이 옵션을 비활성화해보세요.
			</p>
			<p><button class="button-secondary" onclick="kboard_system_option_update('kboard_fontawesome', '<?php echo get_option('kboard_fontawesome')?'':'1'?>')">Font Awesome <?php echo get_option('kboard_fontawesome')?'활성화':'비활성화'?></button></p>
		</li>
		<li id="kboard_attached_copy_download">
			<h4><?php echo get_option('kboard_attached_copy_download')?'첨부파일 다운로드 깨짐 방지가 활성화 되어 있습니다.':'기본적인 방법으로 첨부파일이 다운로드 되고 있습니다.'?></h4>
			<p>
			다운로드 받은 첨부파일이 깨져 사용자가 읽을 수 없다면 이 옵션을 활성화 하세요.<br>
			이 옵션을 활성화 하면 새로운 방법으로 첨부파일을 다운로드 받습니다.<br>
			시스템 성능이 저하될 수 있으니 서버에 첨부파일에 대한 MIME Type 설정을 추가할 것을 권장합니다.
			</p>
			<p><button class="button-secondary" onclick="kboard_system_option_update('kboard_attached_copy_download', '<?php echo get_option('kboard_attached_copy_download')?'':'1'?>')">첨부파일 다운로드 깨짐 방지 <?php echo get_option('kboard_attached_copy_download')?'비활성화':'활성화'?></button></p>
		</li>
		<li id="kboard_limit_file_size">
			<h4>첨부파일의 최대 크기를 제한합니다.</h4>
			<p>
			서버에서 설정한 최대 크기를 넘을 수 없습니다.<br>
			최대 크기는 <?php echo kboard_upload_max_size()?> 바이트(B) 입니다. <a href="https://search.naver.com/search.naver?query=<?php echo kboard_upload_max_size()?>+%EB%B0%94%EC%9D%B4%ED%8A%B8+%EB%A9%94%EA%B0%80%EB%B0%94%EC%9D%B4%ED%8A%B8+%EB%B3%80%ED%99%98" onclick="window.open(this.href);return false;">네이버 단위변환 보기</a><br>
			첨부파일 업로드에 문제가 있다면 먼저 호스팅 관리자에게 문의 해보세요.
			</p>
			<p>
				<input type="number" name="kboard_limit_file_size" value="<?php echo kboard_limit_file_size()?>"> 바이트(B)
				<button class="button-secondary" onclick="kboard_system_option_update('kboard_limit_file_size', jQuery('input[name=kboard_limit_file_size]').val())">변경</button>
			</p>
		</li>
		<li id="kboard_allow_file_extensions">
			<h4>첨부파일의 종류를 제한합니다.</h4>
			<p>
			보안의 이유로 첨부파일의 종류를 제한합니다.<br>
			허용할 파일의 확장자를 콤마(,)로 구분해서 추가해주세요.<br>
			첨부파일 업로드에 문제가 있다면 먼저 호스팅 관리자에게 문의 해보세요.
			</p>
			<p>
				<input type="text" name="kboard_allow_file_extensions" value="<?php echo kboard_allow_file_extensions()?>" style="width:100%">
				<button class="button-secondary" onclick="kboard_system_option_update('kboard_allow_file_extensions', jQuery('input[name=kboard_allow_file_extensions]').val())">확장자 업데이트</button>
			</p>
		</li>
		<li id="kboard_new_document_notify_time">
			<h4>새글 알림 아이콘을 리스트에서 보여줍니다.</h4>
			<p>
			리스트에서 정해진 시간 이내로 등록된 글에 NEW 표시가 나타나도록 설정합니다.<br>
			일부 스킨에서는 적용되지 않습니다.
			</p>
			<p>
				<select name="kboard_new_document_notify_time">
					<option value="1">비활성화</option>
					<option value="3600"<?php if(kboard_new_document_notify_time() == '3600'):?> selected<?php endif?>>1시간</option>
					<option value="10800"<?php if(kboard_new_document_notify_time() == '10800'):?> selected<?php endif?>>3시간</option>
					<option value="21600"<?php if(kboard_new_document_notify_time() == '21600'):?> selected<?php endif?>>6시간</option>
					<option value="43200"<?php if(kboard_new_document_notify_time() == '43200'):?> selected<?php endif?>>12시간</option>
					<option value="86400"<?php if(kboard_new_document_notify_time() == '86400'):?> selected<?php endif?>>하루</option>
					<option value="172800"<?php if(kboard_new_document_notify_time() == '172800'):?> selected<?php endif?>>2일</option>
					<option value="259200"<?php if(kboard_new_document_notify_time() == '259200'):?> selected<?php endif?>>3일</option>
					<option value="345600"<?php if(kboard_new_document_notify_time() == '345600'):?> selected<?php endif?>>4일</option>
					<option value="432000"<?php if(kboard_new_document_notify_time() == '432000'):?> selected<?php endif?>>5일</option>
					<option value="518400"<?php if(kboard_new_document_notify_time() == '518400'):?> selected<?php endif?>>6일</option>
					<option value="604800"<?php if(kboard_new_document_notify_time() == '604800'):?> selected<?php endif?>>1주일</option>
				</select>
				<button class="button-secondary" onclick="kboard_system_option_update('kboard_new_document_notify_time', jQuery('select[name=kboard_new_document_notify_time]').val())">변경</button>
			</p>
		</li>
		<li id="kboard_captcha_stop">
			<h4>모든 게시판에서 <?php echo get_option('kboard_captcha_stop')?'비로그인 사용자 CAPTCHA 기능이 중지되었습니다.':'비로그인 사용자 CAPTCHA 기능을 사용중입니다.'?></h4>		
			<p>
			CAPTCHA(캡챠)란 기계는 인식 할 수없는 임의의 문자를 생성하여 입력 받아, 스팸을 차단하는 기능입니다.<br>
			게시판과 댓글 작성시 비로그인 사용자는 CAPTCHA 보안코드를 입력하도록 합니다.<br>
			비활성화 하게되면 스팸이 등록될 확률이 높아집니다.
			</p>
			<p><button class="button-secondary" onclick="kboard_system_option_update('kboard_captcha_stop', '<?php echo get_option('kboard_captcha_stop')?'':'1'?>')">모든 게시판에서 비로그인 사용자 CAPTCHA 기능 <?php echo get_option('kboard_captcha_stop')?'사용하기':'중지하기'?></button></p>
		</li>
		<li id="kboard_recaptcha">
			<h4>구글 reCAPTCHA</h4>
			<p>
			구글 reCAPTCHA는 게시판에서 스팸을 막기 위한 효과적인 솔루션입니다.<br>
			구글 reCAPTCHA를 활성화하면 KBoard에 내장된 CAPTCHA 보안코드 대신 구글 reCAPTCHA를 사용하게 됩니다.<br>
			<a href="https://www.google.com/recaptcha/admin" onclick="window.open(this.href);return false;">https://www.google.com/recaptcha/admin</a> 에서 발급받은 Site key와 Secret key를 입력하면 자동으로 활성화됩니다.<br>
			구글 reCAPTCHA 기능이 없는 일부 스킨에서는 동작하지 않습니다.
			</p>
			<p>
				Site key <input type="text" name="kboard_recaptcha_site_key" value="<?php echo get_option('kboard_recaptcha_site_key')?>" placeholder="Site key"><br>
				Secret key <input type="text" name="kboard_recaptcha_secret_key" value="<?php echo get_option('kboard_recaptcha_secret_key')?>" placeholder="Secret key"><br>
				<button class="button-secondary" onclick="kboard_recaptcha_update(this)">구글 reCAPTCHA 정보 업데이트</button>
			</p>
		</li>
		<li id="kboard_custom_css">
			<h4>커스텀 CSS</h4>
			<p>
			스킨파일 수정없이 새로운 디자인 속성을 추가할 수 있습니다.<br>
			잘못된 CSS를 입력하게 되면 사이트 레이아웃이 깨질 수 있습니다.<br>
			CSS 수정 관련 질문은 커뮤니티를 이용해 주세요. <a href="http://www.cosmosfarm.com/threads" onclick="window.open(this.href);return false;"><?php echo __('커뮤니티로 이동', 'kboard')?></a>
			</p>
			<p>
				<textarea rows="10" name="kboard_custom_css"><?php echo get_option('kboard_custom_css')?></textarea>
				<button class="button-secondary" onclick="kboard_system_option_update('kboard_custom_css', jQuery('textarea[name=kboard_custom_css]').val())">커스텀 CSS 업데이트</button>
			</p>
		</li>
		<li id="kboard_iframe_whitelist">
			<h4>아이프레임 화이트리스트, 아래 등록된 iframe 주소를 허가합니다.</h4>
			<p>
			게시글 작성시 등록되지 않은 iframe 주소는 보안을 위해 차단됩니다.<br>
			형식에 맞춰서 한줄씩 도메인 주소를 입력해주세요.
			</p>
			<p>
				<textarea rows="10" name="kboard_iframe_whitelist"><?php echo kboard_iframe_whitelist()?></textarea>
				<button class="button-secondary" onclick="kboard_system_option_update('kboard_iframe_whitelist', jQuery('textarea[name=kboard_iframe_whitelist]').val())">아이프레임 화이트리스트 업데이트</button>
			</p>
		</li>
		<li id="kboard_name_filter">
			<h4>작성자 금지단어</h4>
			<p>
			작성자 이름으로 사용할 수 없는 단어를 입력해주세요.<br>
			관리자가 아닌 경우에 포함된 단어가 존재하면 게시판 글 작성을 중단합니다.<br>
			단어를 콤마(,)로 구분해서 추가해주세요.
			</p>
			<p>
				<input type="text" name="kboard_name_filter" value="<?php echo kboard_name_filter()?>" style="width:100%">
				<button class="button-secondary" onclick="kboard_system_option_update('kboard_name_filter', jQuery('input[name=kboard_name_filter]').val())">금지단어 업데이트</button>
			</p>
		</li>
		<li id="kboard_content_filter">
			<h4>본문/제목/댓글 금지단어</h4>
			<p>
			게시글 본문과 제목 그리고 댓글에 사용할 수 없는 단어를 입력해주세요.<br>
			관리자가 아닌 경우에 포함된 단어가 존재하면 게시판 글 작성을 중단합니다.<br>
			단어를 콤마(,)로 구분해서 추가해주세요.
			</p>
			<p>
				<input type="text" name="kboard_content_filter" value="<?php echo kboard_content_filter()?>" style="width:100%">
				<button class="button-secondary" onclick="kboard_system_option_update('kboard_content_filter', jQuery('input[name=kboard_content_filter]').val())">금지단어 업데이트</button>
			</p>
		</li>
		<li id="kboard_content_delete_immediately">
			<h4>게시글 바로 삭제</h4>
			<p>
			기본적으로 게시글을 지우면 해당 게시글은 휴지통으로 이동합니다.<br>
			경우에 따라서 이 휴지통 기능이 필요 없을 수 있으며 휴지통 기능이 필요 없다면 이 기능을 활성화해주세요.<br>
			현재상태 : <?php echo get_option('kboard_content_delete_immediately')?'바로 삭제':'휴지통으로 이동'?>
			</p>
			<p><button class="button-secondary" onclick="kboard_system_option_update('kboard_content_delete_immediately', '<?php echo get_option('kboard_content_delete_immediately')?'':'1'?>')">게시글 바로 삭제 <?php echo get_option('kboard_content_delete_immediately')?'비활성화':'활성화'?></button></p>
		</li>
		<!--
		<li id="kboard_iamport">
			<h4>아임포트</h4>
			<p>
			아임포트는 국내외 주요 PG사와의 연동을 지원합니다.<br>
			KBoard에서는 아임포트 서비스와 연동해 쉽고 편리하게 결제 기능을 제공합니다.<br>
			실제 결제 기능을 사용하기 위해서 아임포트와 PG사 가입이 필요합니다.<br>
			PG사 가입은 아임포트에 문의해주세요. <a href="http://www.iamport.kr/" onclick="window.open(this.href);return false;">http://www.iamport.kr/</a><br>
			아임포트에 로그인 후 <a href="https://admin.iamport.kr/settings" onclick="window.open(this.href);return false;">시스템설정</a>에 있는 정보를 입력하시면 테스트 결제 또는 실제 결제 기능을 사용할 수 있습니다.<br>
			</p>
			<p>
				가맹점 식별코드 <input type="text" name="kboard_iamport_id" value="<?php echo get_option('kboard_iamport_id')?>" placeholder="가맹점 식별코드"><br>
				REST API 키 <input type="text" name="kboard_iamport_api_key" value="<?php echo get_option('kboard_iamport_api_key')?>" placeholder="REST API 키"><br>
				REST API secret <input type="text" name="kboard_iamport_api_secret" value="<?php echo get_option('kboard_iamport_api_secret')?>" placeholder="REST API secret"><br>
				<button class="button-secondary" onclick="kboard_iamport_update(this)">아임포트 정보 업데이트</button>
			</p>
		</li>
		-->
	</ul>
</div>
<script>
function kboard_system_option_update(option, value){
	jQuery.post(ajaxurl, {'action':'kboard_system_option_update', 'option':option, 'value':value}, function(res){
		window.location.reload();
	});
	return false;
}
function kboard_recaptcha_update(button){
	if(jQuery(button).data('submitted')){
		alert('<?php echo __('Please wait.', 'kboard')?>');
		return false;
	}
	
	jQuery.post(ajaxurl, {'action':'kboard_system_option_update', 'option':'kboard_recaptcha_site_key', 'value':jQuery('input[name=kboard_recaptcha_site_key]').val()}, function(res){
		jQuery.post(ajaxurl, {'action':'kboard_system_option_update', 'option':'kboard_recaptcha_secret_key', 'value':jQuery('input[name=kboard_recaptcha_secret_key]').val()}, function(res){
			window.location.reload();
		});
	});
	
	jQuery(button).data('submitted', 'submitted');
	jQuery(button).text('<?php echo __('Please wait.', 'kboard')?>');
	return false;
}
function kboard_iamport_update(button){
	if(jQuery(button).data('submitted')){
		alert('<?php echo __('Please wait.', 'kboard')?>');
		return false;
	}
	
	jQuery.post(ajaxurl, {'action':'kboard_system_option_update', 'option':'kboard_iamport_id', 'value':jQuery('input[name=kboard_iamport_id]').val()}, function(res){
		jQuery.post(ajaxurl, {'action':'kboard_system_option_update', 'option':'kboard_iamport_api_key', 'value':jQuery('input[name=kboard_iamport_api_key]').val()}, function(res){
			jQuery.post(ajaxurl, {'action':'kboard_system_option_update', 'option':'kboard_iamport_api_secret', 'value':jQuery('input[name=kboard_iamport_api_secret]').val()}, function(res){
				window.location.reload();
			});
		});
	});
	
	jQuery(button).data('submitted', 'submitted');
	jQuery(button).text('<?php echo __('Please wait.', 'kboard')?>');
	return false;
}
</script>
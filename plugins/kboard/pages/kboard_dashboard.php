<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1>
		<?php echo __('KBoard : 대시보드', 'kboard')?>
		<a href="http://www.cosmosfarm.com/products/kboard" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
		<a href="http://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
		<a href="http://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
		<a href="http://blog.cosmosfarm.com/" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
	</h1>
	<div id="welcome-panel" class="welcome-panel">
		<?php include 'welcome.php';?>
	</div>
</div>

<h2 class="nav-tab-wrapper">
	<a href="#" class="nav-tab nav-tab-active" onclick="return false;"><?php echo __('시스템 설정', 'kboard')?></a>
</h2>

<ul id="kboard-dashboard-options">
	<li>
		<h4><?php echo get_option('kboard_xssfilter')?'해킹 차단 옵션이 비활성화 되어 있습니다.':'해킹으로 부터 보호되고 있습니다.'?></h4>
		<p>
		서버에 ModSecurity등의 방화벽이 설치되어 있으면 이 옵션을 비활성화 가능합니다.<br>
		이 옵션을 100% 신뢰하지 마세요, 서버와 네트워크에 방화벽 설치를 권장합니다.<br>
		이 옵션을 비활성화 하면 시스템 속도가 빨라집니다.
		</p>
		<p><button class="button-secondary" onclick="kboard_system_option_update('kboard_xssfilter', '<?php echo get_option('kboard_xssfilter')?'':'1'?>')">XSS공격 차단 <?php echo get_option('kboard_xssfilter')?'활성화':'비활성화'?></button></p>
	</li>
	<li>
		<h4><?php echo get_option('kboard_fontawesome')?'Font Awesome 사용 중지되었습니다.':'Font Awesome 사용 가능합니다.'?></h4>
		<p>
		Font Awesome은 오픈소스 아이콘 폰트 입니다.<br>
		KBoard의 게시판 스킨에 사용되고 있습니다.<br>
		테마의 다른 버튼과 충돌이 발생되면 이 옵션을 비활성화 해보세요.
		</p>
		<p><button class="button-secondary" onclick="kboard_system_option_update('kboard_fontawesome', '<?php echo get_option('kboard_fontawesome')?'':'1'?>')">Font Awesome <?php echo get_option('kboard_fontawesome')?'활성화':'비활성화'?></button></p>
	</li>
	<li>
		<h4><?php echo get_option('kboard_attached_copy_download')?'첨부파일 다운로드 깨짐 방지가 활성화 되어 있습니다.':'기본적인 방법으로 첨부파일이 다운로드 되고 있습니다.'?></h4>
		<p>
		다운로드 받은 첨부파일이 깨져 사용자가 읽을 수 없다면 이 옵션을 활성화 하세요.<br>
		이 옵션을 활성화 하면 새로운 방법으로 첨부파일을 다운로드 받습니다.<br>
		시스템 성능이 저하될 수 있으니 서버에 첨부파일에 대한 MIME Type 설정을 추가할 것을 권장합니다.
		</p>
		<p><button class="button-secondary" onclick="kboard_system_option_update('kboard_attached_copy_download', '<?php echo get_option('kboard_attached_copy_download')?'':'1'?>')">첨부파일 다운로드 깨짐 방지 <?php echo get_option('kboard_attached_copy_download')?'비활성화':'활성화'?></button></p>
	</li>
	<li>
		<h4>첨부파일의 최대 크기를 제한합니다.</h4>
		<p>
		서버에서 설정한 최대 크기를 넘을 수 없습니다.<br>
		최대 크기는 <?php echo kboard_upload_max_size()?> 바이트(B) 입니다. <a href="https://search.naver.com/search.naver?query=<?php echo kboard_upload_max_size()?>+%EB%B0%94%EC%9D%B4%ED%8A%B8+%EB%A9%94%EA%B0%80%EB%B0%94%EC%9D%B4%ED%8A%B8+%EB%B3%80%ED%99%98" onclick="window.open(this.href);return false;">네이버 단위변환 보기</a><br>
		첨부파일 업로드에 문제가 있다면 먼저 호스팅 관리자에게 문의 해보세요.
		</p>
		<p>
			<input type="text" name="kboard_limit_file_size" value="<?php echo kboard_limit_file_size()?>"> 바이트(B)
			<button class="button-secondary" onclick="kboard_system_option_update('kboard_limit_file_size', jQuery('input[name=kboard_limit_file_size]').val())">변경</button>
		</p>
	</li>
	<li>
		<h4>첨부파일의 종류를 제한합니다.</h4>
		<p>
		보안의 이유로 첨부파일의 종류를 제한합니다.<br>
		허용할 파일의 확장자를 콤마(,)로 구분해서 추가해주세요.<br>
		첨부파일 업로드에 문제가 있다면 먼저 호스팅 관리자에게 문의 해보세요.
		</p>
		<p>
			<input type="text" name="kboard_allow_file_extensions" value="<?php echo kboard_allow_file_extensions()?>" style="width:100%;">
			<button class="button-secondary" onclick="kboard_system_option_update('kboard_allow_file_extensions', jQuery('input[name=kboard_allow_file_extensions]').val())">확장자 업데이트</button>
		</p>
	</li>
	<li>
		<h4>새글 알림 아이콘을 리스트에서 보여줍니다.</h4>
		<p>
		리스트에서 정해진 시간 이내로 등록된 글에 NEW 표시가 나타나도록 설정합니다.<br>
		일부 스킨에서는 적용되지 않습니다.
		</p>
		<p>
			<select name="kboard_new_document_notify_time">
				<option value="0">비활성화</option>
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
	<li>
		<h4>모든 게시판에서 <?php echo get_option('kboard_captcha_stop')?'비로그인 사용자 CAPTCHA 기능이 중지되었습니다.':'비로그인 사용자 CAPTCHA 기능을 사용중입니다.'?></h4>		
		<p>
		CAPTCHA(캡챠)란 기계는 인식 할 수없는 임의의 문자를 생성하여 입력 받아, 스팸을 차단하는 기능입니다.<br>
		게시판과 댓글 작성시 비로그인 사용자는 CAPTCHA 보안코드를 입력하도록 합니다.<br>
		비활성화 하게되면 스팸이 등록될 확률이 높아집니다.
		</p>
		<p><button class="button-secondary" onclick="kboard_system_option_update('kboard_captcha_stop', '<?php echo get_option('kboard_captcha_stop')?'':'1'?>')">모든 게시판에서 비로그인 사용자 CAPTCHA 기능 <?php echo get_option('kboard_captcha_stop')?'사용하기':'중지하기'?></button></p>
	</li>
	<li>
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
	<li>
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
</ul>

<script>
function kboard_system_option_update(option, value){
	jQuery.post('<?php echo admin_url('admin-ajax.php')?>', {'action':'kboard_system_option_update', 'option':option, 'value':value}, function(res){
		window.location.reload();
	});
	return false;
}
</script>
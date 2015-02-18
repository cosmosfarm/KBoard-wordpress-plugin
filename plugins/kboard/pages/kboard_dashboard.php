<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h2>
		KBoard : 대시보드
		<a href="http://www.cosmosfarm.com/products/kboard" class="add-new-h2" onclick="window.open(this.href); return false;">홈페이지</a>
		<a href="http://www.cosmosfarm.com/threads" class="add-new-h2" onclick="window.open(this.href); return false;">커뮤니티</a>
		<a href="http://www.cosmosfarm.com/support" class="add-new-h2" onclick="window.open(this.href); return false;">고객지원</a>
	</h2>
	<div id="welcome-panel" class="welcome-panel">
		<?php include 'welcome.php';?>
	</div>
</div>

<h2 class="nav-tab-wrapper">
	<a href="#" class="nav-tab nav-tab-active" onclick="return false;">시스템 설정</a>
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
</ul>

<script>
function kboard_system_option_update(option, value){
	jQuery.post('<?php echo admin_url('/admin-ajax.php')?>', {'action':'kboard_system_option_update', 'option':option, 'value':value}, function(res){
		location.reload();
	});
	return false;
}
</script>
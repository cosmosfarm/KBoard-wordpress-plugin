<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div style="float: left; margin: 7px 8px 0 0; width: 36px; height: 34px; background: url(<?php echo plugins_url('kboard/images/icon-big.png')?>) left top no-repeat;"></div>
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
	<a href="#" class="nav-tab" onclick="return false;">시스템 설정</a>
</h2>

<ul id="kboard-dashboard-options">
	<li>
		<h4>XSS공격 차단 옵션이 <?php echo get_option('kboard_xssfilter')?'비활성화':'활성화'?> 되어 있습니다.</h4>
		<p>
		해커로부터 웹 사이트를 안전하게 지키려면 이 옵션이 활성화 되어 있어야 합니다.<br>
		서버에 ModSecurity등의 방화벽이 설치되어 있으면 이 옵션을 비활성화 해주세요.<br>
		이 옵션을 비활성화 하면 시스템 속도가 빨라집니다.
		</p>
		<p><button class="button-secondary" onclick="kboard_system_option_update('kboard_xssfilter', '<?php echo get_option('kboard_xssfilter')?'':'1'?>')">XSS공경 차단 <?php echo get_option('kboard_xssfilter')?'활성화':'비활성화'?></button></p>
	</li>
</ul>

<script>
function kboard_system_option_update(option, value){
	var $ = jQuery;
	var form = $('.kboard-dashboard-options-form');
	$.post('<?php echo admin_url('/admin-ajax.php')?>', {'action':'kboard_system_option_update', 'option':option, 'value':value}, function(res){
		location.reload();
	});
	return false;
}
</script>
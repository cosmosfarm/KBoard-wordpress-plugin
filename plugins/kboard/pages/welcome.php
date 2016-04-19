<?php if(!defined('ABSPATH')) exit;?>
<div class="welcome-panel-content">
	<div style="float:left;">
		<h2><?php echo __('코스모스팜 대시보드 입니다.', 'kboard')?></h2>
		<p class="about-description"><?php echo __('최신버전 확인 및 운영관련 기능을 사용할 수 있습니다.', 'kboard')?></p>
	</div>
	<div style="float:right;"><iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fcosmosfarm.sns&amp;width&amp;layout=standard&amp;action=like&amp;show_faces=true&amp;share=true&amp;height=60" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width: 500px; height:60px;" allowTransparency="true"></iframe></div>
	<div class="welcome-panel-column-container">
		<div class="welcome-panel-column" style="overflow:hidden;">
			<h4><?php echo __('KBoard 버전', 'kboard')?></h4>
			<ul>
				<li>
					<?php echo __('설치된 게시판 플러그인 버전', 'kboard')?>: <?php echo KBOARD_VERSION?> (<?php echo __('최신', 'kboard')?>: <?php echo $upgrader->getLatestVersion()->kboard?>)
					<?php if(current_user_can('activate_plugins') && version_compare(KBOARD_VERSION, $upgrader->getLatestVersion()->kboard, '<')):?><br><a class="button" href="<?php echo KBOARD_UPGRADE_ACTION?>&action=kboard" onclick="return cf_upgrade();"><?php echo $upgrader->getLatestVersion()->kboard?> 버전 업데이트</a> <a class="button" href="https://github.com/cosmosfarm/KBoard-wordpress-plugin/blob/master/plugins/kboard/history.md" onclick="window.open(this.href);return false;"><?php echo __('변경사항', 'kboard')?></a><?php endif?>
				</li>
				<li>
					<?php if(defined('KBOARD_COMMNETS_VERSION')):?>
					<?php echo __('설치된 댓글 플러그인 버전', 'kboard')?>: <?php echo KBOARD_COMMNETS_VERSION?> (<?php echo __('최신', 'kboard')?>: <?php echo $upgrader->getLatestVersion()->comments?>)
					<?php if(current_user_can('activate_plugins') && version_compare(KBOARD_COMMNETS_VERSION, $upgrader->getLatestVersion()->comments, '<')):?><br><a class="button" href="<?php echo KBOARD_UPGRADE_ACTION?>&action=comments" onclick="return cf_upgrade();"><?php echo $upgrader->getLatestVersion()->comments?> 버전 업데이트</a> <a class="button" href="https://github.com/cosmosfarm/KBoard-wordpress-plugin/blob/master/plugins/kboard-comments/history.md" onclick="window.open(this.href);return false;"><?php echo __('변경사항', 'kboard')?></a><?php endif?>
					<?php else:?>
					<ul>
						<li><a class="button" href="http://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href);return false;">댓글 플러그인 홈페이지에서 다운로드하기</a></li>
						<li><a class="button" href="<?php echo KBOARD_UPGRADE_ACTION?>&action=comments" onclick="return cf_upgrade();">댓글 플러그인 <?php echo $upgrader->getLatestVersion()->comments?> 버전 자동으로 설치하기</a></li>
					</ul>
					<?php endif?>
				</li>
			</ul>
			<iframe src="//www.cosmosfarm.com/display/size/320_100" frameborder="0" scrolling="no" style="margin-top:20px;width:320px;height:100px;border:none;"></iframe>
		</div>
		<div class="welcome-panel-column">
			<h4><?php echo __('스토어', 'kboard')?></h4>
			<ul id="cf-wpstore-products">
				<li><?php echo __('등록된 상품이 없습니다.', 'kboard')?></li>
			</ul>
		</div>
		<div class="welcome-panel-column">
			<iframe src="//www.cosmosfarm.com/display/size/300_250" frameborder="0" scrolling="no" style="margin-top:20px;width:300px;height:250px;border:none;"></iframe>
		</div>
	</div>
</div>

<script>
window.onload = function(){
	cosmosfarm.init('<?php echo KBOARD_WORDPRESS_APP_ID?>', '<?php echo get_option('cosmosfarm_access_token')?>');
	cosmosfarm.getWpstoreProducts('', 1, 7, function(res){
		if(res.length > 0){
			var products = document.getElementById('cf-wpstore-products');
			products.innerHTML = '';
		}
		for(var i=0; i<res.length; i++){
			cf_addWpstoreProduct(res[i].title, res[i].created, res[i].link);
		}
	});
};
function cf_upgrade(){
	<?php if(defined('KBOARD_DOWNLOADER_VERSION')):?>
	if(confirm('<?php echo __('다음 페이지에서 게시판과 댓글 플러그인 모두를 업데이트해주세요.', 'kboard')?>')){
		window.location.href = '<?php echo admin_url('admin.php?page=kboard_downloader_main');?>';
	}
	<?php else:?>
	if(confirm('<?php echo __('KBoard 게시판 설치도구 플러그인을 먼저 설치해주세요.', 'kboard')?>')){
		window.location.href = '<?php echo admin_url('plugin-install.php?tab=search&s=kboard-downloader');?>';
	}
	<?php endif?>
	return false;
}
function cf_oauthStatus(upgrade_url){
	cosmosfarm.oauthStatus(function(res){
		if(res.status == 'valid'){
			if(confirm('업그레이드전에 플러그인을 백업하세요. 모두 최신 파일로 교체됩니다. 계속 할까요?')){
				window.location.href = upgrade_url;
			}
		}
		else{
			if(confirm('Access Token이 만료되어 재발급 받아야합니다. 코스모스팜 홈페이지로 이동합니다.')){
				window.location.href = cosmosfarm.getLoginUrl('<?php echo admin_url('admin.php?page=kboard_dashboard')?>');
			}
		}
	}, function(res){
		if(confirm('자동 업그레이드를 진행 하시려면 코스모스팜에 로그인 해야합니다. 코스모스팜 홈페이지로 이동합니다.')){
			window.location.href = cosmosfarm.getLoginUrl('<?php echo admin_url('admin.php?page=kboard_dashboard')?>');
		}
	});
	return false;
}
function cf_addWpstoreProduct(title, created, link){
	var products = document.getElementById('cf-wpstore-products');
	var a = document.createElement('a');
	a.innerHTML = title;
	a.setAttribute('href', link);
	a.onclick = function(){
		window.open(this.href); return false;
	}
	var li = document.createElement('li');
	li.appendChild(a);
	products.appendChild(li);
}
</script>
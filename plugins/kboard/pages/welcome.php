<?php if(!defined('ABSPATH')) exit;?>
<div class="welcome-panel-content">
	<div class="welcome-panel-column-container">
		<div class="kboard-panel-column">
			<h2><?php echo __('코스모스팜 대시보드 입니다.', 'kboard')?></h2>
			<p class="about-description"><?php echo __('최신버전 확인 및 운영관련 기능을 사용할 수 있습니다.', 'kboard')?></p>
		</div>
		<div class="kboard-panel-column"><iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fcosmosfarm.sns&amp;width&amp;layout=standard&amp;action=like&amp;show_faces=true&amp;share=true&amp;height=60" scrolling="no" frameborder="0" style="border:none;overflow:hidden;width:500px;height:60px" allowTransparency="true"></iframe></div>
	</div>
	<div class="welcome-panel-column-container">
		<div class="welcome-panel-column" style="overflow:hidden">
			<h4><?php echo __('KBoard 버전', 'kboard')?> <!--<a class="button button-small" href="<?php echo admin_url('admin.php?page=kboard_updates')?>" style="font-weight:normal">최신버전 확인</a>--></h4>
			<ul>
				<li>
					현재 설치된 게시판 플러그인 버전은 <strong><?php echo KBOARD_VERSION?></strong> 입니다. 
				</li>
				<li>
					<?php if(defined('KBOARD_COMMNETS_VERSION')):?> 
						현재 설치된 댓글 플러그인 버전은 <strong><?php echo KBOARD_COMMNETS_VERSION?></strong> 입니다.
					<?php else:?>
						<a class="button" href="<?php echo admin_url('admin.php?page=kboard_updates')?>">KBoard 댓글 플러그인 설치하기</a>
					<?php endif?>
				</li>
			</ul>
			<h4><?php echo __('Events and News', 'kboard')?></h4>
			<ul>
				<?php
				$upgrader = KBUpgrader::getInstance();
				foreach($upgrader->getLatestNews() as $row):?>
				<li>
					<a href="<?php echo esc_url($row->url)?>" target="<?php echo esc_attr($row->target)?>"><?php echo esc_html($row->title)?></a>
				</li>
				<?php endforeach?>
			</ul>
		</div>
		<div class="welcome-panel-column">
			<h4><?php echo __('스토어', 'kboard')?></h4>
			<ul id="cf-wpstore-products">
				<li><?php echo __('등록된 상품이 없습니다.', 'kboard')?></li>
			</ul>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function(){
	cosmosfarm.init('<?php echo KBOARD_WORDPRESS_APP_ID?>', '<?php echo KBStore::getAccessToken()?>');
	cosmosfarm.getWpstoreProducts('', 1, 7, function(res){
		if(res.length > 0){
			var products = document.getElementById('cf-wpstore-products');
			products.innerHTML = '';
		}
		for(var i=0; i<res.length; i++){
			cf_addWpstoreProduct(res[i].title, res[i].created, res[i].link);
		}
	});
});
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
/*
function cf_upgrade(upgrade_url){
	if(confirm('업데이트전에 플러그인을 백업하세요. 모두 최신 파일로 교체됩니다. 계속 할까요?')){
		window.location.href = upgrade_url;
	}
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
*/
</script>
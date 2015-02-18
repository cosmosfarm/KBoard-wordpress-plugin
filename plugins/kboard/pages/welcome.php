<?php if(!defined('ABSPATH')) exit;?>
<div class="welcome-panel-content">
	<div style="float: left;">
		<h3>코스모스팜 대시보드 입니다.</h3>
		<p class="about-description">최신버전 확인 및 운영관련 기능을 사용할 수 있습니다.</p>
	</div>
	<div style="float: right;"><iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fcosmosfarm.sns&amp;width&amp;layout=standard&amp;action=like&amp;show_faces=true&amp;share=true&amp;height=60" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width: 500px; height:60px;" allowTransparency="true"></iframe></div>
	<div class="welcome-panel-column-container">
		<div class="welcome-panel-column">
			<h4>KBoard 버전</h4>
			<ul>
				<li>
					설치된 게시판 플러그인 버전: <?php echo KBOARD_VERSION?> (최신: <?php echo $upgrader->getLatestVersion()->kboard?>)
					<?php if(KBOARD_VERSION < $upgrader->getLatestVersion()->kboard):?><br><a class="button" href="<?php echo KBOARD_UPGRADE_ACTION?>&action=kboard" onclick="return cf_oauthStatus(this.href);"><?php echo $upgrader->getLatestVersion()->kboard?> 버전으로 업그레이드</a> <a class="button" href="https://github.com/cosmosfarm/KBoard-wordpress-plugin/blob/master/plugins/kboard/history.md" onclick="window.open(this.href); return false;">히스토리</a><?php endif?>
				</li>
				<li>
					<?php if(defined('KBOARD_COMMNETS_VERSION')):?>
					설치된 댓글 플러그인 버전: <?php echo KBOARD_COMMNETS_VERSION?> (최신: <?php echo $upgrader->getLatestVersion()->comments?>)
					<?php if(KBOARD_COMMNETS_VERSION < $upgrader->getLatestVersion()->comments):?><br><a class="button" href="<?php echo KBOARD_UPGRADE_ACTION?>&action=comments" onclick="return cf_oauthStatus(this.href);"><?php echo $upgrader->getLatestVersion()->comments?> 버전으로 업그레이드</a> <a class="button" href="https://github.com/cosmosfarm/KBoard-wordpress-plugin/blob/master/plugins/kboard-comments/history.md" onclick="window.open(this.href); return false;">히스토리</a><?php endif?>
					<?php else:?>
					<ul>
						<li><a class="button" href="http://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href); return false;">댓글 플러그인 홈페이지에서 다운로드하기</a></li>
						<li><a class="button" href="<?php echo KBOARD_UPGRADE_ACTION?>&action=comments" onclick="return cf_oauthStatus(this.href);">댓글 플러그인 <?php echo $upgrader->getLatestVersion()->comments?> 버전 자동으로 설치하기</a></li>
					</ul>
					<?php endif?>
				</li>
			</ul>
			<h4>KBoard 백업</h4>
			<ul>
				<li><a href="<?php echo KBOARD_BACKUP_ACTION?>" class="button">데이터 백업</a></li>
				<li><a href="<?php echo KBOARD_BACKUP_PAGE?>" class="button">데이터 복구</a></li>
			</ul>
		</div>
		<div class="welcome-panel-column">
			<h4>워드프레스 스토어</h4>
			<ul id="cf-wpstore-products">
				<li>등록된 상품이 없습니다.</li>
			</ul>
		</div>
		<div class="welcome-panel-column">
			<h4>코스모스팜 고객지원</h4>
			<ul>
				<li><a href="http://www.cosmosfarm.com/support" onclick="window.open(this.href); return false;">새로운 기능 및 오류 수정 기술지원 받기</a></li>
				<li><a href="http://www.cosmosfarm.com/threads" onclick="window.open(this.href); return false;">다른 사용자에게서 문제 해결 방법을 확인하기</a></li>
				<li><a href="http://blog.cosmosfarm.com/" onclick="window.open(this.href); return false;">최신 정보 및 새로운 사용법 알아보기</a></li>
			</ul>
			<h4>스토어 신규 등록</h4>
			<p>
				KBoard 스킨, 플러그인, 테마등 등록 접수 받습니다.<br>
				<a href="mailto:support@cosmosfarm.com" onclick="window.open(this.href); return false;" class="button">이메일로 등록 접수 및 제휴 문의하기</a>
			</p>
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
function cf_oauthStatus(upgrade_url){
	cosmosfarm.oauthStatus(function(res){
		if(res.status == 'valid'){
			if(confirm('업그레이드전에 플러그인을 백업하세요. 모두 최신 파일로 교체됩니다. 계속 할까요?')){
				location.href = upgrade_url;
			}
		}
		else{
			if(confirm('Access Token이 만료되어 재발급 받아야합니다. 코스모스팜 홈페이지로 이동합니다.')){
				location.href = cosmosfarm.getLoginUrl('<?php echo admin_url('/admin.php?page=kboard_dashboard')?>');
			}
		}
	}, function(res){
		if(confirm('자동 업그레이드를 진행 하시려면 코스모스팜에 로그인 해야합니다. 코스모스팜 홈페이지로 이동합니다.')){
			location.href = cosmosfarm.getLoginUrl('<?php echo admin_url('/admin.php?page=kboard_dashboard')?>');
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
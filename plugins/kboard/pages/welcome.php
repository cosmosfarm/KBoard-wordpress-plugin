<?php if(!defined('ABSPATH')) exit;?><div class="welcome-panel-content">	<h3>코스모스팜 대시보드 입니다.</h3>	<p class="about-description">최신버전 확인 및 운영관련 기능을 사용할 수 있습니다.</p>	<div class="welcome-panel-column-container">		<div class="welcome-panel-column">			<h4>KBoard 버전</h4>			<p>				설치된 게시판 플러그인: <?=KBOARD_VERSION?> (최신: <?=$upgrader->getLatestVersion()->kboard?>)				<?php if(KBOARD_VERSION < $upgrader->getLatestVersion()->kboard):?><br><a class="button" href="<?=KBOARD_UPGRADE_ACTION?>&action=kboard" onclick="return CF_oauthStatus(this.href);"><?=$upgrader->getLatestVersion()->kboard?> 버전으로 업데이트하기</a><?php endif?>			</p>			<p>				<?php if(defined('KBOARD_COMMNETS_VERSION')):?>				설치된 댓글 플러그인: <?=KBOARD_COMMNETS_VERSION?> (최신: <?=$upgrader->getLatestVersion()->comments?>)				<?php if(KBOARD_COMMNETS_VERSION < $upgrader->getLatestVersion()->comments):?><br><a class="button" href="<?=KBOARD_UPGRADE_ACTION?>&action=comments" onclick="return CF_oauthStatus(this.href);"><?=$upgrader->getLatestVersion()->comments?> 버전으로 업데이트하기</a><?php endif?>				<?php else:?>				<a href="http://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href); return false;">댓글 플러그인 홈페이지에서 다운로드</a><br>				<a class="button" href="<?=KBOARD_UPGRADE_ACTION?>&action=comments" onclick="return CF_oauthStatus(this.href);"><?=$upgrader->getLatestVersion()->comments?> 버전으로 설치하기</a>				<?php endif?>			</p>			<h4>KBoard 백업</h4>			<ul>				<li><a href="<?=KBOARD_BACKUP_ACTION?>">데이터 백업</a></li>				<li><a href="<?=KBOARD_BACKUP_PAGE?>">데이터 복구</a></li>			</ul>		</div>		<div class="welcome-panel-column">			<h4>워드프레스 스토어</h4>			<ul>				<li>등록된 KBoard 스킨이 없습니다.</li>			</ul>		</div>		<div class="welcome-panel-column">			<h4>코스모스팜 고객지원</h4>			<ul>				<li><a href="http://www.cosmosfarm.com/support" onclick="window.open(this.href); return false;">새로운 기능 및 오류 수정 기술지원 받기</a></li>				<li><a href="http://www.cosmosfarm.com/threads" onclick="window.open(this.href); return false;">다른 사용자에게서 문제 해결 방법을 확인하기</a></li>				<li><a href="http://blog.cosmosfarm.com/" onclick="window.open(this.href); return false;">최신 정보 및 새로운 사용법 알아보기</a></li>			</ul>		</div>	</div></div><script src="<?=plugins_url('cosmosfarm-apis.js', __FILE__)?>"></script><script>COSMOSFARM.init('083d136637c09572c3039778d8667b27', '<?=$_SESSION['cosmosfarm_access_token']?>');function CF_oauthStatus(upgrade_url){	COSMOSFARM.oauthStatus(function(res){		console.log(res);		if(res.status == 'valid'){			location.href = upgrade_url;		}		else{			if(confirm('access_token이 만료되어 재발급 받아야 합니다. 코스모스팜 홈페이지로 이동합니다.')){				location.href = COSMOSFARM.getLoginUrl('<?=admin_url('/admin.php?page=kboard_dashboard')?>');			}		}	}, function(res){		if(confirm('업데이트를 진행 하시려면 코스모스팜에 로그인 해야 합니다. 코스모스팜 홈페이지로 이동합니다.')){			location.href = COSMOSFARM.getLoginUrl('<?=admin_url('/admin.php?page=kboard_dashboard')?>');		}	});	return false;}</script>
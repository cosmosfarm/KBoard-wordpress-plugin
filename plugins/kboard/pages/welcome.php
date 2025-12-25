<?php if(!defined('ABSPATH')) exit;?>

<style>
.kboard-welcome-grid {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 24px;
	margin-top: 24px;
}

.kboard-welcome-card {
	background: #f8fafc;
	border: 1px solid var(--kboard-border, #e2e8f0);
	border-radius: 10px;
	padding: 20px;
}

.kboard-welcome-card h4 {
	font-size: 17px;
	font-weight: 600;
	color: #2c3e50;
	margin: 0 0 12px 0;
	padding-bottom: 10px;
	border-bottom: 2px solid #e2e8f0;
}

.kboard-welcome-card ul {
	margin: 0;
	padding: 0;
	list-style: none;
}

.kboard-welcome-card li {
	padding: 6px 0;
	font-size: 15px;
	color: #475569;
	border-bottom: 1px solid #f1f5f9;
	line-height: 1.4;
}

.kboard-welcome-card li:last-child {
	border-bottom: none;
	padding-bottom: 0;
}

.kboard-welcome-card li:first-child {
	padding-top: 0;
}

.kboard-welcome-card a {
	color: #007cba;
	text-decoration: none;
	font-weight: 500;
}

.kboard-welcome-card a:hover {
	text-decoration: underline;
}

.kboard-welcome-header {
	text-align: left;
}

.kboard-welcome-header h2 {
	font-size: 28px;
	font-weight: 700;
	color: #1e293b;
	margin: 0 0 8px 0;
}

.kboard-welcome-header p {
	margin: 0 0 16px 0;
	color: #64748b;
	font-size: 17px;
	line-height: 1.5;
}

.kboard-welcome-header .kboard-facebook-wrap {
	margin-top: 12px;
}

.kboard-welcome-header .kboard-facebook-wrap iframe {
	max-width: 100%;
}

/* Mobile Responsive for Welcome Panel */
@media (max-width: 900px) {
	.kboard-welcome-grid {
		grid-template-columns: 1fr;
		gap: 16px;
		margin-top: 20px;
	}
	
	.kboard-welcome-header h2 {
		font-size: 24px;
	}
	
	.kboard-welcome-header p {
		font-size: 15px;
	}
}

@media (max-width: 600px) {
	.kboard-welcome-header h2 {
		font-size: 20px;
	}
	
	.kboard-welcome-header p {
		font-size: 14px;
		margin-bottom: 12px;
	}
	
	.kboard-welcome-header .kboard-facebook-wrap {
		margin-top: 8px;
		overflow-x: auto;
	}
	
	.kboard-welcome-card {
		padding: 16px;
	}
	
	.kboard-welcome-card h4 {
		font-size: 15px;
		margin-bottom: 10px;
		padding-bottom: 8px;
	}
	
	.kboard-welcome-card li {
		font-size: 14px;
		padding: 5px 0;
	}
}

@media (max-width: 480px) {
	.kboard-welcome-grid {
		gap: 12px;
		margin-top: 16px;
	}
	
	.kboard-welcome-header h2 {
		font-size: 18px;
	}
	
	.kboard-welcome-header p {
		font-size: 13px;
	}
	
	.kboard-welcome-card {
		padding: 14px;
		border-radius: 8px;
	}
	
	.kboard-welcome-card h4 {
		font-size: 14px;
	}
	
	.kboard-welcome-card li {
		font-size: 13px;
	}
}
</style>

<div class="welcome-panel-content">
	<div class="kboard-welcome-header">
		<h2><?php echo __('코스모스팜 대시보드 입니다.', 'kboard')?></h2>
		<p><?php echo __('최신버전 확인 및 운영관련 기능을 사용할 수 있습니다.', 'kboard')?></p>
		<div class="kboard-facebook-wrap">
			<iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Fcosmosfarm.sns&amp;width=500&amp;layout=standard&amp;action=like&amp;show_faces=true&amp;share=true&amp;height=60" scrolling="no" frameborder="0" style="border:none;overflow:hidden;width:550px;height:60px" allowTransparency="true"></iframe>
		</div>
	</div>
	
	<div class="kboard-welcome-grid">
		<!-- Events and News -->
		<div class="kboard-welcome-card">
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
		
		<!-- Store -->
		<div class="kboard-welcome-card">
			<h4><?php echo __('스토어', 'kboard')?></h4>
			<ul id="cf-wpstore-products">
				<li><?php echo __('등록된 상품이 없습니다.', 'kboard')?></li>
			</ul>
		</div>
		
		<!-- KBoard Version -->
		<div class="kboard-welcome-card">
			<h4><?php echo __('KBoard 버전', 'kboard')?></h4>
			<ul>
				<li>
					게시판 플러그인: <strong><?php echo KBOARD_VERSION?></strong>
				</li>
				<li>
					<?php if(defined('KBOARD_COMMNETS_VERSION')):?> 
						댓글 플러그인: <strong><?php echo KBOARD_COMMNETS_VERSION?></strong>
					<?php else:?>
						<a class="button button-small" href="<?php echo admin_url('admin.php?page=kboard_updates')?>">KBoard 댓글 플러그인 설치하기</a>
					<?php endif?>
				</li>
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
</script>
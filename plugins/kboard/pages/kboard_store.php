<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1>
		<?php echo __('KBoard : 스토어', 'kboard')?>
		<a href="#" class="page-title-action kbstore-login-button"></a>
		<a href="https://www.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
		<a href="https://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
		<a href="https://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
		<a href="https://blog.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
		<a href="https://www.cosmosfarm.com/wpstore/manage/license" class="page-title-action" onclick="window.open(this.href);return false;">라이센스 도메인 등록 및 관리</a>
	</h1>
	
	<div class="wp-filter">
		<ul class="filter-links">
			<li class="<?php if($category==''):?>current<?php endif?>"><a href="<?php echo admin_url('admin.php?page=kboard_store')?>">모두</a></li>
			<li class="<?php if($category=='kboard'):?>current<?php endif?>"><a href="<?php echo admin_url('admin.php?page=kboard_store&kbstore_category=kboard')?>">KBoard 스킨</a></li>
			<li class="<?php if($category=='theme'):?>current<?php endif?>"><a href="<?php echo admin_url('admin.php?page=kboard_store&kbstore_category=theme')?>">테마</a></li>
			<li class="<?php if($category=='plugin'):?>current<?php endif?>"><a href="<?php echo admin_url('admin.php?page=kboard_store&kbstore_category=plugin')?>">플러그인</a></li>
			<li class="<?php if($category=='widget'):?>current<?php endif?>"><a href="<?php echo admin_url('admin.php?page=kboard_store&kbstore_category=widget')?>">위젯</a></li>
			<li class="<?php if($category=='mobile'):?>current<?php endif?>"><a href="<?php echo admin_url('admin.php?page=kboard_store&kbstore_category=mobile')?>">모바일</a></li>
			<li class="<?php if($category=='admin'):?>current<?php endif?>"><a href="<?php echo admin_url('admin.php?page=kboard_store&kbstore_category=admin')?>">관리자용</a></li>
			<li class="<?php if($category=='social'):?>current<?php endif?>"><a href="<?php echo admin_url('admin.php?page=kboard_store&kbstore_category=social')?>">소셜</a></li>
			<li class="<?php if($category=='design'):?>current<?php endif?>"><a href="<?php echo admin_url('admin.php?page=kboard_store&kbstore_category=design')?>">디자인소스</a></li>
		</ul>
	</div>
	
	<table class="wp-list-table widefat fixed">
		<thead>
			<tr>
				<th class="manage-column kbstore-thumbnail">
					<a><span>미리보기</span></a>
				</th>
				<th class="manage-column kbstore-title">
					<a><span>이름</span></a>
				</th>
				<th class="manage-column kbstore-category">
					<a><span>카테고리</span></a>
				</th>
				<th class="manage-column kbstore-version">
					<a><span>버전</span></a>
				</th>
				<th class="manage-column kbstore-description">
					<a><span>설명</span></a>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th class="manage-column kbstore-thumbnail">
					<a><span>미리보기</span></a>
				</th>
				<th class="manage-column kbstore-title">
					<a><span>이름</span></a>
				</th>
				<th class="manage-column kbstore-category">
					<a><span>카테고리</span></a>
				</th>
				<th class="manage-column kbstore-version">
					<a><span>버전</span></a>
				</th>
				<th class="manage-column kbstore-description">
					<a><span>설명</span></a>
				</th>
			</tr>
		</tfoot>
		<tbody id="kbstore-products-list"></tbody>
	</table>
</div>

<script>
var cf_login_status;
var cf_list_page = 1;
var cf_list_continue = true;
var cf_list_lock = false;
jQuery(document).ready(function(){
	cosmosfarm.init('<?php echo KBOARD_WORDPRESS_APP_ID?>', '<?php echo KBStore::getAccessToken()?>');
	cosmosfarm.oauthStatus(function(res){
		if(res.status == 'valid'){
			cosmosfarm.getProfile(function(res){
				if(res.profile.username){
					cf_login_status = 'connected';
					jQuery('.kbstore-login-button').text(res.profile.username+'님 환영합니다');
				}
				else{
					jQuery('.kbstore-login-button').text('<?php echo __('로그인', 'kboard')?>');
				}
			});
		}
		else{
			jQuery('.kbstore-login-button').text('<?php echo __('로그인', 'kboard')?>');
		}
	}, function(res){
		jQuery('.kbstore-login-button').text('<?php echo __('로그인', 'kboard')?>');
	});
	jQuery('.kbstore-login-button').attr('href', cosmosfarm.getLoginUrl('<?php echo admin_url('admin.php?page=kboard_store')?>'));
	cf_get_kbstore_list(cf_list_page);
});
jQuery(window).scroll(function(){
	var scroll_height = jQuery(window).height() + jQuery(window).scrollTop();
	var document_height = jQuery(document).height();
	
	if(document_height - scroll_height <= 100 && cf_list_lock == false && cf_list_continue){
		cf_list_lock = true;
		cf_get_kbstore_list(cf_list_page);
	}
});
function cf_get_kbstore_list(page){
	var current_scroll_top = jQuery(window).scrollTop();
	cosmosfarm.getWpstoreProducts('<?php echo esc_attr($category)?>', page, 10, function(res){
		if(res.length <= 0) cf_list_continue = false;
		for(var i=0; i<res.length; i++){
			cf_add_kbstore_product(res[i].thumbnail, res[i].title, res[i].link, res[i].download, res[i].formatted_category, res[i].category, res[i].version, res[i].description, res[i].price, res[i].purchased);
		}
		cf_list_page+=1;
		setTimeout(function(){
			jQuery(window).scrollTop(current_scroll_top);
			cf_list_lock = false;
		});
	});
}
function cf_add_kbstore_product(thumbnail, title, link, download, formatted_category, category, version, description, price, purchased){
	var row_id = encodeURIComponent(title);
	
	var td1 = document.createElement('td');
	var img = document.createElement('img');
	img.setAttribute('class', 'kbstore-thumbnail-img');
	img.setAttribute('src', thumbnail);
	td1.appendChild(img);
	
	var td2 = document.createElement('td');
	var strong = document.createElement('strong');
	strong.className = 'kbstore-product-title';
	strong.innerHTML = title;
	td2.appendChild(strong);
	
	var action_links = document.createElement('div');
	action_links.setAttribute('class', 'action-links');
	
	var a_detail = document.createElement('a');
	a_detail.className = 'button';
	a_detail.innerHTML = '세부사항';
	a_detail.setAttribute('href', link);
	a_detail.onclick = function(){
		window.open(this.href); return false;
	}
	
	var a_purchase = document.createElement('a');
	a_purchase.className = 'button';
	a_purchase.innerHTML = '구매하기';
	a_purchase.setAttribute('href', link);
	a_purchase.onclick = function(){
		if(confirm('제품 구매는 스토어 홈페이지에서 가능합니다.')){
			window.open(this.href);
		}
		return false;
	}
	
	var a_download = document.createElement('a');
	a_download.className = 'button';
	a_download.innerHTML = '설치하기';
	a_download.setAttribute('href', download+'?app_id='+cosmosfarm.app_id+'&access_token='+cosmosfarm.access_token);
	a_download.onclick = function(){
		cosmosfarm.oauthStatus(function(res){
			if(res.status == 'valid' && cf_login_status == 'connected'){
				window.location.href = download+'?app_id='+cosmosfarm.app_id+'&access_token='+cosmosfarm.access_token;
			}
			else{
				if(confirm('코스모스팜에 로그인해야 합니다. 코스모스팜 홈페이지로 이동합니다.')){
					window.location.href = cosmosfarm.getLoginUrl('<?php echo admin_url('admin.php?page=kboard_store')?>');
				}
			}
		}, function(res){
			if(confirm('코스모스팜에 로그인해야 합니다. 코스모스팜 홈페이지로 이동합니다.')){
				window.location.href = cosmosfarm.getLoginUrl('<?php echo admin_url('admin.php?page=kboard_store')?>');
			}
		});
		return false;
	}
	
	action_links.appendChild(a_detail);
	action_links.appendChild(document.createTextNode(' '));
	if(category=='design'){
		if(purchased=='1' || price<=0) action_links.appendChild(a_download);
		else action_links.appendChild(a_download);
	}
	else if(category=='kboard'){
		if(purchased=='1' || price<=0) action_links.appendChild(cf_get_a_install('kboard-skin', download, version));
		else action_links.appendChild(a_download);
	}
	else if(category=='theme'){
		if(purchased=='1' || price<=0) action_links.appendChild(cf_get_a_install('theme', download, version));
		else action_links.appendChild(a_download);
	}
	else{
		if(purchased=='1' || price<=0) action_links.appendChild(cf_get_a_install('plugin', download, version));
		else action_links.appendChild(a_download);
	}
	td2.appendChild(action_links);
	
	var td3 = document.createElement('td');
	td3.innerHTML = formatted_category;
	
	var td4 = document.createElement('td');
	td4.innerHTML = version;
	
	var td5 = document.createElement('td');
	var td5_wrap = document.createElement('div');
	var td5_more = document.createElement('p');
	td5.className = 'manage-column kbstore-description show-little';
	td5.appendChild(td5_wrap);
	td5.appendChild(td5_more);
	td5_wrap.setAttribute('id', row_id);
	td5_wrap.className = 'kbstore-description-wrap';
	td5_wrap.innerHTML = description;
	td5_more.className = 'kbstore-description-more';
	td5_more.innerHTML = '<a href="#'+row_id+'" class="button button-small" onclick="return cf_kbstore_description_full(this)">설명 펼치기</a>';
	
	var tr = document.createElement('tr');
	tr.appendChild(td1);
	tr.appendChild(td2);
	tr.appendChild(td3);
	tr.appendChild(td4);
	tr.appendChild(td5);
	
	var list = document.getElementById('kbstore-products-list');
	list.appendChild(tr);
}
function cf_get_a_install(action, download, version){
	var a_install = document.createElement('a');
	a_install.className = 'button-primary';
	a_install.innerHTML = '설치하기';
	a_install.setAttribute('href', '<?php echo admin_url('admin.php?page=kboard_updates')?>' + '&action='+action+'&download_url='+download+'&download_version='+version);
	a_install.onclick = function(){
		cosmosfarm.oauthStatus(function(res){
			if(res.status == 'valid' && cf_login_status == 'connected'){
				if(confirm('설치를 계속 할까요? 이미 설치되어 있다면 새로운 파일로 교체됩니다.')){
					window.location.href = '<?php echo admin_url('admin.php?page=kboard_updates')?>' + '&action='+action+'&download_url='+download+'&download_version='+version;
				}
			}
			else{
				if(confirm('코스모스팜에 로그인해야 합니다. 코스모스팜 홈페이지로 이동합니다.')){
					window.location.href = cosmosfarm.getLoginUrl('<?php echo admin_url('admin.php?page=kboard_store')?>');
				}
			}
		}, function(res){
			if(confirm('코스모스팜에 로그인해야 합니다. 코스모스팜 홈페이지로 이동합니다.')){
				window.location.href = cosmosfarm.getLoginUrl('<?php echo admin_url('admin.php?page=kboard_store')?>');
			}
		});
		return false;
	}
	return a_install;
}
function cf_kbstore_description_full(obj){
	var column = jQuery(obj).parents('.kbstore-description');
	if(column.hasClass('show-little')){
		jQuery('#kbstore-products-list .kbstore-description').addClass('show-little');
		jQuery('#kbstore-products-list .kbstore-description-more .button').text('설명 펼치기');

		column.find('.kbstore-description-more .button').text('설명 닫기');
		column.removeClass('show-little');
	}
	else{
		column.find('.kbstore-description-more .button').text('설명 펼치기');
		column.addClass('show-little');
	}
}
</script>
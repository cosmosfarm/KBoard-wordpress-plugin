<?php if(!defined('ABSPATH')) exit;?>
<!DOCTYPE html>
<html <?php language_attributes()?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta name="robots" content="noindex,follow">
	<title><?php echo __('KBoard Add Media', 'kboard')?></title>
	<script type="text/javascript" src="//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
	<style>
	/* Global Corporate Style Reset & Variables */
	:root {
		--primary-color: #2563eb;
		--primary-hover: #1d4ed8;
		--bg-color: #f8fafc;
		--surface-white: #ffffff;
		--text-main: #1e293b;
		--text-sub: #64748b;
		--border-color: #e2e8f0;
	}

	* { box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
	html, body { margin: 0; padding: 0; background-color: var(--bg-color); color: var(--text-main); height: 100%; }
	img { border: 0; display: block; }
	
	/* Header */
	.kboard-media-header {
		background: var(--surface-white);
		padding: 12px 24px;
		border-bottom: 1px solid var(--border-color);
		display: flex;
		justify-content: space-between;
		align-items: center;
		position: sticky;
		top: 0;
		z-index: 100;
		box-shadow: 0 1px 3px rgba(0,0,0,0.05);
	}
	.kboard-media-header .title {
		font-size: 18px;
		font-weight: 600;
		color: var(--text-main);
	}
	.kboard-media-header .controller {
		display: flex;
		gap: 8px;
		align-items: center;
	}
	
	/* Buttons */
	.kboard-media-header .header-button {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		height: 36px;
		padding: 0 16px;
		border-radius: 6px;
		font-size: 14px;
		font-weight: 500;
		cursor: pointer;
		text-decoration: none;
		transition: all 0.2s ease;
		border: 1px solid transparent;
		line-height: 1;
		white-space: nowrap;
	}
	.kboard-media-header .header-button img { margin-right: 6px; height: 14px; width: auto; vertical-align: middle; }
	
	/* Primary Button (Upload) */
	.kboard-media-header .header-button.upload-button {
		background-color: var(--primary-color);
		color: white;
		border-color: var(--primary-color);
		box-shadow: 0 1px 2px rgba(0,0,0,0.1);
	}
	.kboard-media-header .header-button.upload-button:hover {
		background-color: var(--primary-hover);
		border-color: var(--primary-hover);
		transform: translateY(-1px);
	}

	/* Secondary Button (Insert) */
	.kboard-media-header .header-button.btn-secondary {
		background-color: white;
		border-color: var(--border-color);
		color: var(--text-main);
	}
	.kboard-media-header .header-button.btn-secondary:hover {
		background-color: #f1f5f9;
		border-color: #cbd5e1;
	}

	/* Text Button (Controls) */
	.kboard-media-header .header-button.btn-text {
		background-color: transparent;
		color: var(--text-sub);
	}
	.kboard-media-header .header-button.btn-text:hover {
		background-color: rgba(0,0,0,0.05);
		color: var(--text-main);
	}

	/* Media Grid */
	.media-wrap {
		padding: 24px;
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
		gap: 20px;
	}
	
	/* Media Item */
	.media-wrap .media-item {
		display: block;
		background: var(--surface-white);
		border-radius: 12px;
		border: 1px solid var(--border-color);
		overflow: hidden;
		position: relative;
		cursor: pointer;
		transition: all 0.2s ease;
		margin: 0; /* Override float margins */
		float: none; /* Override float */
		padding: 0;
	}
	.media-wrap .media-item:hover {
		transform: translateY(-4px);
		box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
		border-color: #cbd5e1;
	}
	
	/* Selection */
	.media-wrap .media-item.selected-item {
		border: 2px solid var(--primary-color);
		box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
	}
	
	.media-wrap .media-item .selected-media {
		position: absolute;
		top: 10px;
		left: 10px;
		width: 28px;
		height: 28px;
		display: none;
		z-index: 10;
		background: var(--primary-color) url('<?php echo KBOARD_URL_PATH?>/images/selected-media.png') no-repeat center center;
		background-size: 14px;
		border-radius: 50%;
		border: 2px solid white;
		box-shadow: 0 2px 4px rgba(0,0,0,0.2);
	}
	/* Use CSS for checkmark if image fails or for cleaner look, but keeping image logic for now */
	.media-wrap .media-item .selected-media img { display: none; } /* hide original img tag inside if we use bg */
	
	.media-wrap .media-item:hover .selected-media,
	.media-wrap .media-item.selected-item .selected-media { display: block; }
	.media-wrap .media-item:hover .selected-media { opacity: 0.5; background-color: #aaa; }
	.media-wrap .media-item.selected-item .selected-media { opacity: 1; background-color: var(--primary-color); }

	.media-wrap .media-item .media-image-wrap {
		width: 100%;
		padding-top: 100%; /* 1:1 Square */
		position: relative;
		background-color: #f1f5f9;
	}
	.media-wrap .media-item .media-image-wrap .media-image {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background-size: cover;
		background-position: center;
		transition: transform 0.3s ease;
	}
	.media-wrap .media-item:hover .media-image-wrap .media-image {
		transform: scale(1.05); /* Subtle Toggle zoom */
	}

	/* Controls Grid */
	.media-wrap .media-item .media-control {
		display: flex;
		border-top: 1px solid var(--border-color);
		background: var(--surface-white);
	}
	.media-wrap .media-item .media-control button {
		flex: 1;
		padding: 12px 0;
		border: 0;
		background: transparent;
		color: var(--text-sub);
		font-size: 13px;
		font-weight: 500;
		cursor: pointer;
		transition: all 0.2s;
	}
	.media-wrap .media-item .media-control button:hover {
		background-color: #f8fafc;
		color: var(--primary-color);
	}
	.media-wrap .media-item .media-control button:first-of-type {
		border-right: 1px solid var(--border-color);
	}
	
	.media-wrap .media-item .media-control input { display: none; }

	/* No Media State */
	.media-wrap .no-media {
		grid-column: 1 / -1;
		text-align: center;
		padding: 80px 20px;
		color: var(--text-sub);
		background: var(--surface-white);
		border-radius: 16px;
		border: 2px dashed var(--border-color);
		margin: 0;
	}
	.media-wrap .no-media .kboard-media-poweredby {
		display: inline-block;
		margin-top: 24px;
		font-size: 12px;
		color: #cbd5e1;
		text-decoration: none;
		transition: color 0.2s;
	}
	.media-wrap .no-media .kboard-media-poweredby:hover { color: var(--text-sub); }

	.kboard-loading {
		position: fixed; left: 0; top: 0; width: 100%; height: 100%;
		background-color: rgba(255,255,255,0.8);
		backdrop-filter: blur(4px);
		z-index: 1000;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.kboard-loading img { margin: 0; }
	.kboard-hide { display: none !important; }
	
	@media screen and (max-width: 600px) {
		.kboard-media-header {
			flex-direction: column;
			gap: 12px;
			padding: 16px;
		}
		.kboard-media-header .title { width: 100%; text-align: center; margin-bottom: 4px; }
		.kboard-media-header .controller { width: 100%; justify-content: center; flex-wrap: wrap; }
		.kboard-media-header .header-button { flex: 1; min-width: 100px; }
		
		.media-wrap {
			grid-template-columns: repeat(2, 1fr);
			gap: 12px;
			padding: 12px;
		}
	}
	</style>
	
	<?php
	do_action('kboard_add_media_head');
	?>
</head>
<body>
<form id="kboard-media-form" enctype="multipart/form-data" method="post" onsubmit="return kboard_media_form_execute(this)" data-allow="gif|jpg|jpeg|png|pjp|pjpeg|jfif|svg|bmp|webp|ico">
	<?php wp_nonce_field('kboard-media-upload', 'kboard-media-upload-nonce');?>
	<input type="hidden" name="action" value="kboard_media_upload">
	<input type="hidden" name="board_id" value="<?php echo $media->board_id?>">
	<input type="hidden" name="content_uid" value="<?php echo $media->content_uid?>">
	<input type="hidden" name="media_group" value="<?php echo $media->media_group?>">
	<input type="hidden" name="media_uid" value="">
	
	<div class="kboard-media-header">
		<div class="title"><?php echo __('KBoard Add Media', 'kboard')?></div>
		<div class="controller">
			<a href="javascript:void(0)" class="header-button upload-button" data-name="kboard_media_file[]" title="<?php echo __('이미지 선택하기', 'kboard')?>"><img src="<?php echo KBOARD_URL_PATH?>/images/icon-upload.png" style="filter: brightness(0) invert(1);"> <?php echo __('업로드', 'kboard')?></a>
			<a href="javascript:void(0)" class="header-button btn-secondary" onclick="kboard_selected_media_insert();return false;" title="<?php echo __('선택된 이미지 삽입하기', 'kboard')?>"><img src="<?php echo KBOARD_URL_PATH?>/images/icon-add.png" style="opacity: 0.6"> <?php echo __('선택 삽입', 'kboard')?></a>
			<a href="javascript:void(0)" class="header-button btn-text" onclick="kboard_media_select_all();return false;" title="<?php echo __('전체선택', 'kboard')?>"><?php echo __('전체선택', 'kboard')?></a>
			<a href="javascript:void(0)" class="header-button btn-text" onclick="kboard_media_close();return false;" title="<?php echo __('창닫기', 'kboard')?>"><?php echo __('창닫기', 'kboard')?></a>
		</div>
	</div>
</form>

<div class="media-wrap">
	<?php $index=0; foreach($media->getList() as $key=>$item): $index++;?>
	<label class="media-item" data-media-uid="<?php echo $item->uid?>">
		<div class="selected-media"></div>
		<div class="media-image-wrap">
			<div class="media-image" style="background-image:url(<?php echo $item->thumbnail_url?>)"></div>
		</div>
		<div class="media-control">
			<input type="checkbox" name="media_src" value="<?php echo $item->thumbnail_url?>" data-media-uid="<?php echo $item->uid?>" onchange="kboard_media_select()">
			<button type="button" onclick="kboard_media_insert('<?php echo $item->thumbnail_url?>');" title="<?php echo __('삽입', 'kboard')?>"><?php echo __('삽입', 'kboard')?></button>
			<button type="button" onclick="kboard_media_delete('<?php echo $item->uid?>');" title="<?php echo __('삭제', 'kboard')?>"><?php echo __('삭제', 'kboard')?></button>
		</div>
	</label>
	<?php endforeach?>
	
	<?php if(!$index):?>
	<div class="no-media">
		<div style="font-size: 16px; margin-bottom: 10px; font-weight: 500; color: #1e293b;"><?php echo __('업로드된 이미지가 없습니다.', 'kboard')?></div>
		<div style="font-size: 14px;"><?php echo __('업로드 버튼을 눌러 이미지 파일을 선택하면 이곳에 표시됩니다.', 'kboard')?></div>
		
		<?php if($board->contribution()):?>
		<a class="kboard-media-poweredby" href="https://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href);return false;" title="<?php echo __('KBoard is the best community software available for WordPress', 'kboard')?>">Powered by KBoard</a>
		<?php endif?>
	</div>
	<?php endif?>
</div>

<div class="kboard-loading kboard-hide">
	<img src="<?php echo KBOARD_URL_PATH?>/images/loading2.gif" alt="<?php echo __('로딩중', 'kboard')?>">
</div>

<script>
function kboard_media_select_all(){
	if(jQuery('.media-item').length){
		jQuery('.media-item').each(function(){
			if(jQuery('.media-wrap').hasClass('media-all-selected')){
				if(jQuery(this).find('input[type=checkbox]').is(':checked')){
					jQuery(this).find('input[type=checkbox]').click();
				}
			}
			else{
				if(!jQuery(this).find('input[type=checkbox]').is(':checked')){
					jQuery(this).find('input[type=checkbox]').click();
				}
			}
		});
		setTimeout(function(){
			if(jQuery('.media-wrap').hasClass('media-all-selected')){
				jQuery('.media-wrap').removeClass('media-all-selected');
			}
			else{
				jQuery('.media-wrap').addClass('media-all-selected');
			}
		}, 0);
	}
}
function kboard_media_select(){
	jQuery('.media-item').removeClass('selected-item');
	jQuery('input[name=media_src]:checked').each(function(){
		var media_uid = jQuery(this).data('media-uid');
		jQuery('.media-item[data-media-uid='+media_uid+']').addClass('selected-item');
	});
}
function kboard_selected_media_insert(){
	var total = jQuery('input[name=media_src]:checked').length;
	var index = 0;
	if(!total){
		alert('<?php echo __('선택한 이미지가 없습니다.', 'kboard')?>');
	}
	else{
		jQuery('input[name=media_src]:checked').each(function(){
			var media_src = jQuery(this).val();
			if(media_src){
				parent.kboard_editor_insert_media(media_src);
			}
			if(++index == total){
				if(confirm('<?php echo __('선택한 이미지를 본문에 삽입했습니다. 창을 닫을까요?', 'kboard')?>')){
					kboard_media_close();
				}
			}
		});
	}
}
function kboard_media_insert(media_src){
	if(media_src){
		parent.kboard_editor_insert_media(media_src);
		if(confirm('<?php echo __('선택한 이미지를 본문에 삽입했습니다. 창을 닫을까요?', 'kboard')?>')){
			kboard_media_close();
		}
	}
}
function kboard_media_delete(media_uid){
	if(media_uid){
		if(confirm('<?php echo __('Are you sure you want to delete?', 'kboard')?>')){
			jQuery('input[name=action]', '#kboard-media-form').val('kboard_media_delete');
			jQuery('input[name=media_uid]', '#kboard-media-form').val(media_uid);
			jQuery('#kboard-media-form').submit();
		}
	}
}
function kboard_media_form_execute(form){
	jQuery('.kboard-loading').removeClass('kboard-hide');
	setTimeout(function(){
		alert('<?php echo __('Network connection with the server is unstable. Please proceed to upload again.', 'kboard')?>');
		jQuery('.kboard-loading').addClass('kboard-hide');
	}, (1000 * 30));
	return true;
}
function kboard_media_close(){
	parent.kboard_media_close();
}
function kboard_media_upload_button(button){
	jQuery('input[type=file]', button).remove();
	
	var allow = jQuery('form').attr('data-allow');
	var extension = "\.("+allow+")$";
	
	var input = function(){
		var obj = jQuery('<input type="file" accept="image/*" multiple>').attr('name', jQuery(button).attr('data-name')).css({'position':'absolute', 'cursor':'pointer', 'opacity':0, 'outline':0}).change(function(){
			var files = jQuery(this).get(0).files;
			
			if(files){
				var total = files.length;
				var index = 0;
				
				jQuery.each(files, function(i, file){
					if(!(new RegExp(extension, "i")).test(file.name)){
						alert('<?php echo __('이미지 파일만 업로드 가능합니다.', 'kboard')?>');

						kboard_media_upload_button(button);
						return false;
					}
					else{
						index++;
					}
					if(index == total){
						jQuery('#kboard-media-form').submit();
					}
				});
			}
			else{
				if(!(new RegExp(extension, "i")).test(jQuery(this).val())){
					alert('<?php echo __('이미지 파일만 업로드 가능합니다.', 'kboard')?>');

					kboard_media_upload_button(button);
					return false;
				}
				else{
					jQuery('#kboard-media-form').submit();
				}
			}
		});
		return obj;
	}
	
	var event = function(event_input){
		jQuery(button).css({'position':'relative', 'overflow':'hidden'}).append(event_input).on('mousemove', function(event){
			var left = event.pageX - jQuery(this).offset().left - jQuery(event_input).width() + 10;
			var top = event.pageY - jQuery(this).offset().top - 10;
			event_input.css({'left':left, 'top':top});
		}).hover(function(){
			event_input.show();
		}, function(){
			event_input.hide();
		}).keydown(function(e){
			if(e.keyCode == 13){
				e.preventDefault();
				jQuery('input[type=file]', button)[0].click();
			}
		});
	}
	event(input());
}
jQuery(document).ready(function($){
	var allow = jQuery('form').attr('data-allow');
	var extension = "\.("+allow+")$";
	
	jQuery('.upload-button').each(function(){
		kboard_media_upload_button(this);
	});
	
	jQuery(document).on('dragover drop', function(e){
		e.preventDefault();
		e.stopPropagation();
	}).on('drop', function(e){
		if(e.originalEvent && e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files){
			jQuery('input[type=file]').prop('files', e.originalEvent.dataTransfer.files);
			
			var total = e.originalEvent.dataTransfer.files.length;
			var index = 0;
			
			jQuery.each(e.originalEvent.dataTransfer.files, function(i, file){
				if(!(new RegExp(extension, "i")).test(file.name)){
					alert('<?php echo __('이미지 파일만 업로드 가능합니다.', 'kboard')?>');
					
					jQuery('.upload-button').each(function(){
						kboard_media_upload_button(this);
					});
					return false;
				}
				else{
					index++;
				}
				if(index == total){
					jQuery('#kboard-media-form').submit();
				}
			});
		}
	});
});
</script>
<script>
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-23680192-8']);
_gaq.push(['_setAllowLinker', true]);
_gaq.push(['_trackPageview']);
_gaq.push(['_trackEvent', 'location_host', window.location.host]);
_gaq.push(['_trackEvent', 'location_href', window.location.href]);
(function(){
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>
</body>
</html>
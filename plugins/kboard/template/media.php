<?php if(!defined('ABSPATH')) exit;?>
<!DOCTYPE html>
<html <?php language_attributes()?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta name="robots" content="noindex">
	<title><?php echo __('KBoard 이미지 삽입하기', 'kboard')?></title>
	<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
	<script type="text/javascript" src="https://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/flick/jquery-ui.css">
	<style>
	* { font-family: Apple SD Gothic Neo,Malgun Gothic,arial,sans-serif,arial,sans-serif; }
	html,body { margin: 0; padding: 0; }
	img { border: 0; }
	.kboard-media-header { padding: 0 20px; font-size: 20px; overflow: hidden; }
	.kboard-media-header .title { float: left; padding-right: 10px; line-height: 64px; }
	.kboard-media-header .controller { float: left; line-height: 64px; }
	.kboard-media-header .header-button { display: inline-block; *display: inline; zoom: 1; vertical-align: middle; margin: 0; padding: 0; padding: 0 10px; line-height: 40px; border: 0; background-color: white; color: #757575; font-size: 12px; cursor: pointer; text-decoration: none; }
	.kboard-media-header .header-button img { vertical-align: middle; }
	.media-wrap { padding: 0 10px; overflow: hidden; }
	.media-wrap .no-media { margin: 20px 10px; padding: 30px 10px; overflow: hidden; line-height: 30px; border: 1px solid #eeeeee; color: #757575; }
	.media-wrap .no-media a { color: #757575; text-decoration: none; }
	.media-wrap .media-item { position: relative; display: block; float: left; margin: 5px; padding: 5px; cursor: pointer; }
	.media-wrap .media-item .selected-media { display: none; position: absolute; left: 0; top: 0; }
	.media-wrap .media-item .media-image-wrap { width: 150px; }
	.media-wrap .media-item .media-image-wrap .media-image { width: 100%; height: 150px; }
	.media-wrap .media-item .media-control { text-align: center; }
	.media-wrap .media-item .media-control input { display: none; }
	.media-wrap .media-item .media-control button { margin: 0; padding: 5px 10px; border: 0; background-color: white; color: #757575; font-size: 12px; cursor: pointer; text-decoration: none; }
	.media-wrap .media-item.selected-item { padding: 5px; border: 0px solid #0073ea; }
	.media-wrap .media-item.selected-item .selected-media { display: block; }
	.media-wrap .media-item.selected-item .media-image-wrap { width: 130px; padding: 10px; background-color: #eeeeee; }
	.media-wrap .media-item.selected-item .media-image-wrap .media-image { height: 130px; }
	.kboard-loading { position: fixed; left: 0; top: 0; width: 100%; height: 100%; background-color: black; opacity: 0.5; text-align: center; }
	.kboard-loading img { position: relative; top: 50%; margin-top: -32px; border: 0; }
	.kboard-hide { display: none !important; }
	
	@media screen and (max-width: 600px) {
		.kboard-media-header { line-height: normal; }
		.kboard-media-header .title { float: none; padding-right: 0; text-align: center; }
		.kboard-media-header .controller { float: none; line-height: 30px; text-align: center; }
		.media-wrap .media-item { float: none; }
		.media-wrap .media-item .media-image-wrap { width: auto; }
		.media-wrap .media-item .media-image-wrap .media-image { height: 200px; }
		.media-wrap .media-item.selected-item .media-image-wrap { width: auto; }
		.media-wrap .media-item.selected-item .media-image-wrap .media-image { height: 180px; }
	}
	</style>
</head>
<body>
<form id="kboard-media-form" enctype="multipart/form-data" method="post" onsubmit="return kboard_media_form_execute(this)" data-allow="gif|jpg|jpeg|png">
	<?php wp_nonce_field('kboard-media-upload', 'kboard-media-upload-nonce');?>
	<input type="hidden" name="action" value="kboard_media_upload">
	<input type="hidden" name="board_id" value="<?php echo $media->board_id?>">
	<input type="hidden" name="content_uid" value="<?php echo $media->content_uid?>">
	<input type="hidden" name="media_group" value="<?php echo $media->media_group?>">
	<input type="hidden" name="media_uid" value="">
	
	<div class="kboard-media-header">
		<div class="title"><?php echo __('KBoard 이미지 삽입하기', 'kboard')?></div>
		<div class="controller">
			<a href="javascript:void(0)" class="header-button upload-button" data-name="kboard_media_file[]" title="<?php echo __('이미지 선택하기', 'kboard')?>"><img src="<?php echo KBOARD_URL_PATH?>/images/icon-upload.png"> <?php echo __('업로드', 'kboard')?></a>
			<a href="javascript:void(0)" class="header-button selected-insert-button kboard-hide" onclick="kboard_selected_media_insert();return false;" title="<?php echo __('선택된 이미지 삽입하기', 'kboard')?>"><img src="<?php echo KBOARD_URL_PATH?>/images/icon-add.png"> <?php echo __('선택 삽입', 'kboard')?></a>
			<a href="javascript:void(0)" class="header-button" onclick="window.close();return false;" title="<?php echo __('창닫기', 'kboard')?>"><?php echo __('창닫기', 'kboard')?></a>
		</div>
	</div>
</form>

<div class="media-wrap">
	<?php $index=0; foreach($media->getList() as $key=>$row): $index++;?>
	<label class="media-item" data-media-uid="<?php echo $row->uid?>">
		<img class="selected-media" src="<?php echo KBOARD_URL_PATH?>/images/selected-media.png" alt="<?php echo __('선택됨', 'kboard')?>">
		<div class="media-image-wrap">
			<div class="media-image" style="background-image:url(<?php echo site_url($row->file_path)?>);background-size:cover"></div>
		</div>
		<div class="media-control">
			<input type="checkbox" name="media_src" value="<?php echo site_url($row->file_path)?>" data-media-uid="<?php echo $row->uid?>" onchange="kboard_media_select()">
			<button type="button" onclick="kboard_media_insert('<?php echo site_url($row->file_path)?>');" title="<?php echo __('삽입', 'kboard')?>"><?php echo __('삽입', 'kboard')?></button>
			<button type="button" onclick="kboard_media_delete('<?php echo $row->uid?>');" title="<?php echo __('삭제', 'kboard')?>"><?php echo __('삭제', 'kboard')?></button>
		</div>
	</label>
	<?php endforeach?>
	
	<?php if(!$index):?>
	<div class="no-media"><?php echo __('업로드된 이미지가 없습니다.', 'kboard')?><br><?php echo __('업로드 버튼을 눌러 이미지 파일을 선택하면 이곳에 표시됩니다 :D', 'kboard')?><br><a href="http://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href);return false;" title="<?php echo __('KBoard is the best community software available for WordPress', 'kboard')?>">Powered by KBoard</a></div>
	<?php endif?>
</div>

<div class="kboard-loading kboard-hide">
	<img src="<?php echo KBOARD_URL_PATH?>/images/loading2.gif" alt="<?php echo __('로딩중', 'kboard')?>">
</div>

<script>
function kboard_media_select(){
	if(jQuery('input[name=media_src]:checked').length){
		jQuery('.selected-insert-button').removeClass('kboard-hide');
	}
	else{
		jQuery('.selected-insert-button').addClass('kboard-hide');
	}
	jQuery('.media-item').removeClass('selected-item');
	jQuery('input[name=media_src]:checked').each(function(){
		var media_uid = jQuery(this).data('media-uid');
		jQuery('.media-item[data-media-uid='+media_uid+']').addClass('selected-item');
	});
}
function kboard_selected_media_insert(){
	var total = jQuery('input[name=media_src]:checked').length;
	var index = 0;
	jQuery('input[name=media_src]:checked').each(function(){
		var media_src = jQuery(this).val();
		if(media_src) opener.kboard_editor_insert_media(media_src);
		if(++index == total){
			if(confirm('<?php echo __('선택한 이미지를 본문에 삽입했습니다. 창을 닫을까요?', 'kboard')?>')) window.close();
		}
	});
}
function kboard_media_insert(media_src){
	if(media_src){
		opener.kboard_editor_insert_media(media_src);
		if(confirm('<?php echo __('선택한 이미지를 본문에 삽입했습니다. 창을 닫을까요?', 'kboard')?>')) window.close();
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
	return true;
}
jQuery(document).ready(function($){
	$('.upload-button').each(function(){
		var button = $(this);
		var allow = $('form').attr('data-allow');
		var input = function(){
			var obj = $('<input type="file" accept="image/*" multiple>').attr('name', $(button).attr('data-name')).css({'position':'absolute', 'cursor':'pointer', 'opacity':0, 'outline':0}).hide().change(function(){
				var extension = "\.("+allow+")$";
				var files = $(this).get(0).files;
				if(files){
					var total = files.length;
					var index = 0;
					$.each(files, function(i, file){
						if(!(new RegExp(extension, "i")).test(file.name)){
							alert('<?php echo __('이미지 파일만 업로드 가능합니다.', 'kboard')?>');
							$(input).remove();
							event(input());
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
					if(!(new RegExp(extension, "i")).test($(this).val())){
						alert('<?php echo __('이미지 파일만 업로드 가능합니다.', 'kboard')?>');
						$(input).remove();
						event(input());
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
			$(button).css({'position':'relative', 'overflow':'hidden'}).append(event_input).on('mousemove', function(event){
				var left = event.pageX - $(this).offset().left - $(event_input).width() + 10;
				var top = event.pageY - $(this).offset().top - 10;
				event_input.css({'left':left, 'top':top});
			}).hover(function(){
				event_input.show();
			}, function(){
				event_input.hide();
			}).keydown(function(e){
				if(e.keyCode == 13){
					e.preventDefault();
					$('input[type=file]', button)[0].click();
				}
			});
		}
		event(input());
	});
});
</script>
</body>
</html>
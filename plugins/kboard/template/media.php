<?php if(!defined('ABSPATH')) exit;?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>관리자 페이지</title>
	<meta name="viewport" content="width=device-width">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
	<script type="text/javascript" src="https://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/flick/jquery-ui.css">
	<style>
		fieldset { border: 1px solid gray; }
		.media-wrap { overflow: hidden; }
		.media-item { display: block; float: left; margin: 5px; padding: 5px; cursor: pointer; }
		.media-item.selected-item { padding: 4px; border: 1px solid #0073ea; }
		.media-control { padding-top: 5px; text-align: center; }
		.kboard-loading { position: fixed; left: 0; top: 0; width: 100%; height: 100%; background-color: black; opacity: 0.5; text-align: center; }
		.kboard-loading img { position: relative; top: 50%; margin-top: -32px; border: 0; }
		.kboard-hide { display: none; }
	</style>
</head>
<body>
<form id="kboard-media-form" enctype="multipart/form-data" method="post" onsubmit="return kboard_media_form_execute(this)">
	<?php wp_nonce_field('kboard-media-upload', 'kboard-media-upload-nonce');?>
	<input type="hidden" name="action" value="kboard_media_upload">
	<input type="hidden" name="board_id" value="<?php echo $media->board_id?>">
	<input type="hidden" name="content_uid" value="<?php echo $media->content_uid?>">
	<input type="hidden" name="media_group" value="<?php echo $media->media_group?>">
	<input type="hidden" name="media_uid" value="">
	<fieldset>
		<legend>이미지 업로드</legend>
		<input type="file" name="kboard_media_file[]" onchange="jQuery('#kboard-media-form').submit()" accept="image/*" multiple>
		<button class="ui-button ui-state-default ui-button-text-only" role="button"><span class="ui-button-text">업로드</span></button>
		<button class="ui-button ui-state-default ui-button-text-only selected-insert-button kboard-hide" role="button" onclick="kboard_selected_media_insert();return false;"><span class="ui-button-text">선택 삽입</span></button>
	</fieldset>
</form>

<div class="media-wrap">
	<?php foreach($media->getList() as $key=>$row):?>
	<label class="media-item" data-media-uid="<?php echo $row->uid?>">
		<div class="media-image" style="background-image:url(<?php echo site_url($row->file_path)?>);background-size:cover;width:150px;height:150px"></div>
		<div class="media-control">
			<input type="checkbox" name="media_src" value="<?php echo site_url($row->file_path)?>" data-media-uid="<?php echo $row->uid?>" onchange="kboard_media_select()">
			<button class="ui-button ui-state-default ui-button-text-only" role="button" onclick="kboard_media_insert('<?php echo site_url($row->file_path)?>');return false;"><span class="ui-button-text">삽입</span></button>
			<button class="ui-button ui-state-default ui-button-text-only" role="button" onclick="kboard_media_delete('<?php echo $row->uid?>');return false;"><span class="ui-button-text">삭제</span></button>
		</div>
	</label>
	<?php endforeach?>
</div>

<div class="kboard-loading kboard-hide">
	<img src="<?php echo KBOARD_URL_PATH?>/images/loading2.gif">
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
	jQuery('input[name=media_src]:checked').each(function(){
		var media_src = jQuery(this).val();
		if(media_src) opener.kboard_editor_insert_media(media_src);
	});
	alert('선택한 이미지를 본문에 삽입했습니다.');
}
function kboard_media_insert(media_src){
	if(media_src){
		opener.kboard_editor_insert_media(media_src);
		alert('선택한 이미지를 본문에 삽입했습니다.');
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
	if(jQuery('input[name=kboard_media_file]', form).val() && !kboard_image_checker(jQuery('input[name=kboard_media_file]', form).val())){
		alert('이미지 파일만 첨부하실 수 있습니다.');
		return false;
	}
	jQuery('.kboard-loading').removeClass('kboard-hide');
	return true;
}
function kboard_image_checker(value){
	var extension = "\.(bmp|gif|jpg|jpeg|png)$";
	if((new RegExp(extension, "i")).test(value)) return true;
	return false;
}
</script>
</body>
</html>
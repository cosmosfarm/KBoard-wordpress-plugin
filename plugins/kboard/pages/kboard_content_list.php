<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1>
		<?php echo __('KBoard : 전체 게시글', 'kboard')?>
		<a href="https://www.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
		<a href="https://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
		<a href="https://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
		<a href="https://blog.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
	</h1>
	<?php $table->views()?>
	<form method="get">
		<input type="hidden" name="page" value="kboard_content_list">
		<input type="hidden" name="filter_view" value="<?php echo $table->filter_view?>">
		<input type="hidden" name="filter_board_id" value="<?php echo $table->filter_board_id?>">
		<?php $table->search_box(__('Search', 'kboard'), 'kboard_content_list_search')?>
	</form>
	<form id="kboard-content-list" method="post">
		<?php $table->display()?>
	</form>
</div>

<script>
function kboard_content_list_update(){
	jQuery('#kboard-content-list').find('.spinner').addClass('is-active');
	jQuery.post(ajaxurl, jQuery('#kboard-content-list').serialize()+'&action=kboard_content_list_update', function(res){
		jQuery('#kboard-content-list').find('.spinner').removeClass('is-active');
	});
	return false;
}
function kboard_content_list_filter(form){
	var url = '<?php echo admin_url('admin.php?page=kboard_content_list')?>'
	var filter_view = jQuery('input[name=filter_view]', form).val();
	var board_id = jQuery('select[name=filter_board_id]', form).val();
	if(filter_view){
		url += '&filter_view='+filter_view;
	}
	if(board_id){
		url += '&filter_board_id='+board_id;
	}
	window.location.href = url;
}
jQuery(document).ready(function(){
	jQuery('.kboard-content-datepicker').datepicker({
		closeText : '닫기',
		prevText : '이전달',
		nextText : '다음달',
		currentText : '오늘',
		monthNames : [ '1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월' ],
		monthNamesShort : [ '1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월' ],
		dayNames : [ '일', '월', '화', '수', '목', '금', '토' ],
		dayNamesShort : [ '일', '월', '화', '수', '목', '금', '토' ],
		dayNamesMin : [ '일', '월', '화', '수', '목', '금', '토' ],
		weekHeader : 'Wk',
		dateFormat : 'yy-mm-dd',
		firstDay : 0,
		isRTL : false,
		duration : 0,
		showAnim : 'show',
		showMonthAfterYear : true,
		yearSuffix : '년'
	});
	jQuery('.kboard-content-timepicker').timepicker({'timeFormat': 'H:i:s'});
});
</script>
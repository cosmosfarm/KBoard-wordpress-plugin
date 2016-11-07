<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1>
		<?php echo __('KBoard : 전체 게시글', 'kboard')?>
		<a href="http://www.cosmosfarm.com/products/kboard" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
		<a href="http://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
		<a href="http://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
		<a href="http://blog.cosmosfarm.com/" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
	</h1>
	<?php $table->views()?>
	<form method="get">
		<input type="hidden" name="page" value="kboard_content_list">
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
function kboard_content_list_filter(){
	var board_id = jQuery('select[name=filter_board_id]').val();
	if(board_id){
		window.location.href='<?php echo admin_url('admin.php?page=kboard_content_list&filter_board_id=')?>'+board_id;
	}
	else{
		window.location.href='<?php echo admin_url('admin.php?page=kboard_content_list')?>';
	}
}
</script>
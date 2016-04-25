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
	<form method="get">
		<input type="hidden" name="page" value="kboard_content_list">
		<input type="hidden" name="filter_board_id" value="<?php echo $table->filter_board_id?>">
		<?php $table->search_box(__('검색', 'kboard'), 'kboard_content_list_search')?>
	</form>
	<form method="post">
		<?php $table->display()?>
	</form>
</div>
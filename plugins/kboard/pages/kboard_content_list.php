<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1>
		<?php echo __('KBoard : 전체 게시글', 'kboard')?>
		<a href="http://www.cosmosfarm.com/products/kboard" class="add-new-h2" onclick="window.open(this.href);return false;"><?php echo __('홈페이지', 'kboard')?></a>
		<a href="http://www.cosmosfarm.com/threads" class="add-new-h2" onclick="window.open(this.href);return false;"><?php echo __('커뮤니티', 'kboard')?></a>
		<a href="http://www.cosmosfarm.com/support" class="add-new-h2" onclick="window.open(this.href);return false;"><?php echo __('고객지원', 'kboard')?></a>
		<a href="http://blog.cosmosfarm.com/" class="add-new-h2" onclick="window.open(this.href);return false;"><?php echo __('블로그', 'kboard')?></a>
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
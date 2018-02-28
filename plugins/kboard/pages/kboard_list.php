<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1>
		<?php echo __('KBoard : 게시판 목록', 'kboard')?>
		<a href="http://www.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
		<a href="http://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
		<a href="http://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
		<a href="http://blog.cosmosfarm.com/" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
	</h1>
	<form method="get">
		<input type="hidden" name="page" value="kboard_list">
		<?php $table->search_box(__('Search', 'kboard'), 'kboard_list_search')?>
	</form>
	<form method="post">
		<?php $table->display()?>
	</form>
</div>
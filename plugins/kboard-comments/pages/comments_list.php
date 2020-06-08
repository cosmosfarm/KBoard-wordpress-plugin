<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1 class="wp-heading-inline"><?php echo KBOARD_COMMENTS_PAGE_TITLE?></h1>
	<a href="https://www.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard-comments')?></a>
	<a href="https://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard-comments')?></a>
	<a href="https://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard-comments')?></a>
	<a href="https://blog.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard-comments')?></a>
	
	<hr class="wp-header-end">

	<div class="kboard-comments-list-search">
		<form method="get">
			<input type="hidden" name="page" value="kboard_comments_list">
			<input type="hidden" name="filter_board_id" value="<?php echo $table->filter_board_id?>">
			
			<select name="target">
				<option value=""<?php if(kboard_target() == 'content'):?> selected<?php endif?>><?php echo __('Content', 'kboard-comments')?></option>
				<option value="user_display"<?php if(kboard_target() == 'user_display'):?> selected<?php endif?>><?php echo __('Author', 'kboard-comments')?></option>
			</select>
			
			<?php $table->search_box(__('Search', 'kboard-comments'), 'kboard_comments_list_search')?>
		</form>
	</div>

	<form method="post">
		<?php $table->display()?>
	</form>
</div>
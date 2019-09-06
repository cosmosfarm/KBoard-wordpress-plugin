<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1 class="wp-heading-inline">KBoard : <?php echo $board->board_name?></h1>
	<a href="https://www.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
	<a href="https://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
	<a href="https://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
	<a href="https://blog.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
	
	<hr class="wp-header-end">
	
	<div class="metabox-holder">
		<div class="post-body">
			<div class="postbox">
				<div class="inside"><?php echo kboard_builder(array('id'=>$board_id))?></div>
			</div>
		</div>
	</div>
</div>
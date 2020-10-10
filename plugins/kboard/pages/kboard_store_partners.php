<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1 class="wp-heading-inline">KBoard : <?php echo __('Partners', 'kboard')?></h1>
	<a href="https://www.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
	<a href="https://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
	<a href="https://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
	<a href="https://blog.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
	
	<hr class="wp-header-end">
	
	<style>
	.card .title { line-height: 40px; }
	.card .title img { vertical-align: middle; border: 0; }
	.card .title a { color: black; text-decoration: none; }
	</style>
	
	<?php foreach($store_partners_list as $partner):?>
	<div class="card">
		<h2 class="title">
			<a href="<?php echo esc_url($partner->url)?>" target="_blank">
				<?php if($partner->logo_url):?><img src="<?php echo esc_url($partner->logo_url)?>" alt="" style="height:40px;"><?php endif?>
				<?php echo $partner->title?>
			</a>
		</h2>
		<?php
		echo wpautop($partner->description);
		?>
		<p><a class="button" href="<?php echo esc_url($partner->url)?>" target="<?php echo esc_attr($partner->target)?>">웹사이트로 이동</a></p>
	</div>
	<?php endforeach?>
</div>
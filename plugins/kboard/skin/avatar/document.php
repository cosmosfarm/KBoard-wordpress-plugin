<link rel="stylesheet" href="<?=$skin_path?>/style.css">

<div id="kboard-document">
	<div class="kboard-header"></div>
	
	<div class="kboard-document-wrap" itemscope itemtype="http://schema.org/Article">
		<div class="kboard-title" itemprop="name">
			<p><?=$content->title?></p>
		</div>
		
		<div class="kboard-detail">
			<?php if($content->category1):?>
			<div class="detail-attr detail-category1">
				<div class="detail-name"><?=$content->category1?></div>
			</div>
			<?php endif?>
			<?php if($content->category2):?>
			<div class="detail-attr detail-category2">
				<div class="detail-name"><?=$content->category2?></div>
			</div>
			<?php endif?>
			<div class="detail-attr detail-writer">
				<div class="detail-name"><?=__('Author', 'kboard')?></div>
				<div class="detail-value">
					<?php if($content->member_uid):?>
						<span title="<?=$content->member_display?>"><?=get_avatar($content->member_display, 32, $default, $content->member_display);?></span>
					<?php else:?>
						<?=$content->member_display?>
					<?php endif?>
				</div>
			</div>
			<div class="detail-attr detail-date">
				<div class="detail-name"><?=__('Date', 'kboard')?></div>
				<div class="detail-value"><?=date("Y-m-d H:i", strtotime($content->date))?></div>
			</div>
			<div class="detail-attr detail-view">
				<div class="detail-name"><?=__('Views', 'kboard')?></div>
				<div class="detail-value"><?=$content->view?></div>
			</div>
		</div>
		
		<div class="kboard-content" itemprop="description">
			<div class="content-view">
				<?php if($content->thumbnail_file):?><p class="thumbnail-area"><img src="<?=get_site_url() . $content->thumbnail_file?>" alt=""></p><?php endif;?>
				
				<?=$content->content?>
			</div>
		</div>
		
		<?php if($content->attach->file1[0]):?>
		<div class="kboard-attach">
			<?=__('Attachment', 'kboard')?> : <a href="<?=$url->getDownloadURLWithAttach($content->uid, 'file1')?>"><?=$content->attach->file1[1]?></a>
		</div>
		<?php endif?>
		
		<?php if($content->attach->file2[0]):?>
		<div class="kboard-attach">
			<?=__('Attachment', 'kboard')?> : <a href="<?=$url->getDownloadURLWithAttach($content->uid, 'file2')?>"><?=$content->attach->file2[1]?></a>
		</div>
		<?php endif?>
	</div>
	
	<?php if($board->isComment()):?>
	<div class="kboard-comments-area"><?=$board->buildComment($content->uid)?></div>
	<?php endif?>
	
	<div class="kboard-control">
		<div class="left">
			<a href="<?=$url->toString()?>" class="kboard-button-small"><?=__('List', 'kboard')?></a>
			<a href="<?=$url->getDocumentURLWithUID($content->getPrevUID())?>" class="kboard-button-small"><?=__('Prev', 'kboard')?></a>
			<a href="<?=$url->getDocumentURLWithUID($content->getNextUID())?>" class="kboard-button-small"><?=__('Next', 'kboard')?></a>
		</div>
		<?php if($board->isEditor($content->member_uid) || $board->permission_write=='all'):?>
		<div class="right">
			<a href="<?=$url->set('uid', $content->uid)->set('mod', 'editor')->toString()?>" class="kboard-button-small"><?=__('Edit', 'kboard')?></a>
			<a href="<?=$url->set('uid', $content->uid)->set('mod', 'remove')->toString()?>" class="kboard-button-small" onclick="return confirm('<?=__('Are you sure you want to delete?', 'kboard')?>');"><?=__('Delete', 'kboard')?></a>
		</div>
		<?php endif?>
	</div>
	
	<div class="kboard-poweredby">
		<a href="http://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href); return false;" title="<?=__('KBoard is the best community software available for WordPress', 'kboard')?>">Powered by KBoard</a>
	</div>
</div>
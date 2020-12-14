<?php while($content = $list->hasNextReply()):?>
<tr class="<?php if($content->uid == kboard_uid()):?>kboard-list-selected<?php endif?>">
	<td class="kboard-list-uid"></td>
	<td class="kboard-list-title" style="padding-left:<?php echo ($depth+1)*5?>px">
		<a href="<?php echo esc_url($url->getDocumentURLWithUID($content->uid))?>">
			<div class="kboard-default-cut-strings">
				<img src="<?php echo $skin_path?>/images/icon-reply.png" alt="">
				<?php if($content->isNew()):?><span class="kboard-default-new-notify">New</span><?php endif?>
				<?php if($content->secret):?><img src="<?php echo $skin_path?>/images/icon-lock.png" alt="<?php echo __('Secret', 'kboard')?>"><?php endif?>
				<?php echo $content->title?>
				<span class="kboard-comments-count"><?php echo $content->getCommentsCount()?></span>
			</div>
		</a>
		<div class="kboard-mobile-contents">
			<span class="contents-item kboard-user"><?php echo $content->getUserDisplay()?></span>
			<span class="contents-separator kboard-date">|</span>
			<span class="contents-item kboard-date"><?php echo $content->getDate()?></span>
			<span class="contents-separator kboard-vote">|</span>
			<span class="contents-item kboard-vote"><?php echo __('Votes', 'kboard')?> <?php echo $content->vote?></span>
			<span class="contents-separator kboard-view">|</span>
			<span class="contents-item kboard-view"><?php echo __('Views', 'kboard')?> <?php echo $content->view?></span>
		</div>
	</td>
	<td class="kboard-list-user"><?php echo $content->getUserDisplay()?></td>
	<td class="kboard-list-date"><?php echo $content->getDate()?></td>
	<td class="kboard-list-vote"><?php echo $content->vote?></td>
	<td class="kboard-list-view"><?php echo $content->view?></td>
</tr>
<?php $boardBuilder->builderReply($content->uid, $depth+1)?>
<?php endwhile?>
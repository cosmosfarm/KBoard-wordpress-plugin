<?php while($content = $list->hasNextReply()):?>
<tr>
	<td class="kboard-list-uid"></td>
	<td class="kboard-list-title"><div class="cut_strings">
			<a href="<?php echo $url->set('uid', $content->uid)->set('mod', 'document')->toString()?>"><i class="icon-hand-right" style="padding-left: <?php echo intval($depth*10)?>px"></i> <?php echo $content->title?>
			<?php if($content->secret):?><img src="<?php echo $skin_path?>/images/icon_lock.png" alt="<?php echo __('Secret', 'kboard')?>"><?php endif?>
			</a>
			<?php echo $content->getCommentsCount()?>
		</div></td>
	<td class="kboard-list-user"><?php echo $content->member_display?></td>
	<td class="kboard-list-date"><?php echo date("Y.m.d", strtotime($content->date))?></td>
	<td class="kboard-list-view"><?php echo $content->view?></td>
</tr>
<?php $boardBuilder->builderReply($content->uid, $depth+1)?>
<?php endwhile?>
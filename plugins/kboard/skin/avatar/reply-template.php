<?php while($content = $list->hasNextReply()):?>
<tr>
	<td class="kboard-list-uid"></td>
	<td class="kboard-list-title" style="padding-left: <?php echo intval($depth*10)?>px"><div class="cut_strings">
			<a href="<?php echo $url->set('uid', $content->uid)->set('mod', 'document')->toString()?>"><img src="<?php echo $skin_path?>/images/icon_reply.png"> <?php echo $content->title?>
			<?php if($content->secret):?><img src="<?php echo $skin_path?>/images/icon_lock.png" alt="<?php echo __('Secret', 'kboard')?>"><?php endif?>
			</a>
			<?php echo $content->getCommentsCount()?>
		</div></td>
	<td class="kboard-list-user">
		<?php if($content->member_uid):?>
			<span title="<?php echo $content->member_display?>"><?php echo get_avatar($content->member_display, 32, $default, $content->member_display)?></span>
		<?php else:?>
			<?php echo $content->member_display?>
		<?php endif?>
	</td>
	<td class="kboard-list-date"><?php echo date("Y.m.d", strtotime($content->date))?></td>
	<td class="kboard-list-view"><?php echo $content->view?></td>
</tr>
<?php $boardBuilder->builderReply($content->uid, $depth+1)?>
<?php endwhile?>
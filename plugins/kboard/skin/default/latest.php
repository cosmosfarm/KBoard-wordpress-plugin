<div id="kboard-default-latest">
	<table>
		<?php $latest_columns = $board->getLatestListColumns(); ?>
		<thead>
			<tr>
				<th class="kboard-latest-title"><?php echo __('Title', 'kboard')?></th>
				<?php if(in_array('author', $latest_columns)): ?>
					<th class="kboard-latest-author"><?php echo __('Author', 'kboard')?></th>
				<?php endif; ?>
				<th class="kboard-latest-date"><?php echo __('Date', 'kboard')?></th>
			</tr>
		</thead>
		<tbody>
			<?php while($content = $list->hasNext()):?>
			<tr>
				<td class="kboard-latest-title">
					<a href="<?php echo $url->getDocumentURLWithUID($content->uid)?>">
						<div class="kboard-default-cut-strings">
							<?php if($content->isNew()):?><span class="kboard-default-new-notify">N</span><?php endif?>
							<?php if($content->secret):?><img src="<?php echo $skin_path?>/images/icon-lock.png" alt="<?php echo __('Secret', 'kboard')?>"><?php endif?>
							<?php echo $content->title?>
							<span class="kboard-comments-count"><?php echo $content->getCommentsCount()?></span>
						</div>
					</a>
				</td>
				<?php if(in_array('author', $latest_columns)): ?>
					<td class="kboard-latest-author"><?php echo $content->getUserDisplay()?></td>
				<?php endif; ?>
				<td class="kboard-latest-date"><?php echo $content->getDate()?></td>
			</tr>
			<?php endwhile?>
		</tbody>
	</table>
</div>
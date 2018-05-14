<div id="kboard-avatar-list">
	
	<!-- 게시판 정보 시작 -->
	<div class="kboard-list-header">
		<?php if(!$board->isPrivate()):?>
			<div class="kboard-total-count">
				<?php echo __('Total', 'kboard')?> <?php echo number_format($board->getListTotal())?>
			</div>
		<?php endif?>
		
		<div class="kboard-sort">
			<form id="kboard-sort-form-<?php echo $board->id?>" method="get" action="<?php echo $url->toString()?>">
				<?php echo $url->set('pageid', '1')->set('category1', '')->set('category2', '')->set('target', '')->set('keyword', '')->set('mod', 'list')->set('kboard_list_sort_remember', $board->id)->toInput()?>
				
				<select name="kboard_list_sort" onchange="jQuery('#kboard-sort-form-<?php echo $board->id?>').submit();">
					<option value="newest"<?php if($list->getSorting() == 'newest'):?> selected<?php endif?>><?php echo __('Newest', 'kboard')?></option>
					<option value="best"<?php if($list->getSorting() == 'best'):?> selected<?php endif?>><?php echo __('Best', 'kboard')?></option>
					<option value="viewed"<?php if($list->getSorting() == 'viewed'):?> selected<?php endif?>><?php echo __('Viewed', 'kboard')?></option>
					<option value="updated"<?php if($list->getSorting() == 'updated'):?> selected<?php endif?>><?php echo __('Updated', 'kboard')?></option>
				</select>
			</form>
		</div>
	</div>
	<!-- 게시판 정보 끝 -->
	
	<!-- 카테고리 시작 -->
	<?php
	if($board->use_category == 'yes'){
		if($board->isTreeCategoryActive()){
			$category_type = 'tree-select';
		}
		else{
			$category_type = 'default';
		}
		$category_type = apply_filters('kboard_skin_category_type', $category_type, $board, $boardBuilder);
		echo $skin->load($board->skin, "list-category-{$category_type}.php", $vars);
	}
	?>
	<!-- 카테고리 끝 -->
	
	<!-- 리스트 시작 -->
	<div class="kboard-list">
		<table>
			<thead>
				<tr>
					<td class="kboard-list-uid"><?php echo __('Number', 'kboard')?></td>
					<td class="kboard-list-title"><?php echo __('Title', 'kboard')?></td>
					<td class="kboard-list-user"><?php echo __('Author', 'kboard')?></td>
					<td class="kboard-list-date"><?php echo __('Date', 'kboard')?></td>
					<td class="kboard-list-vote"><?php echo __('Votes', 'kboard')?></td>
					<td class="kboard-list-view"><?php echo __('Views', 'kboard')?></td>
				</tr>
			</thead>
			<tbody>
				<?php while($content = $list->hasNextNotice()):?>
				<tr class="kboard-list-notice<?php if($content->uid == kboard_uid()):?> kboard-list-selected<?php endif?>">
					<td class="kboard-list-uid"><?php echo __('Notice', 'kboard')?></td>
					<td class="kboard-list-title">
						<a href="<?php echo $url->getDocumentURLWithUID($content->uid)?>">
							<div class="kboard-avatar-cut-strings">
								<?php if($content->isNew()):?><span class="kboard-avatar-new-notify">New</span><?php endif?>
								<?php if($content->secret):?><img src="<?php echo $skin_path?>/images/icon-lock.png" alt="<?php echo __('Secret', 'kboard')?>"><?php endif?>
								<?php echo $content->title?>
								<span class="kboard-comments-count"><?php echo $content->getCommentsCount()?></span>
							</div>
						</a>
						<div class="kboard-mobile-contents">
							<span class="contents-item kboard-user">
								<?php echo apply_filters('kboard_user_display', get_avatar($content->member_uid, 24, '', $content->member_display).' '.$content->member_display, $content->member_uid, $content->member_display, 'kboard', $boardBuilder)?>
							</span>
							<span class="contents-separator kboard-date">|</span>
							<span class="contents-item kboard-date"><?php echo $content->getDate()?></span>
							<span class="contents-separator kboard-vote">|</span>
							<span class="contents-item kboard-vote"><?php echo __('Votes', 'kboard')?> <?php echo $content->vote?></span>
							<span class="contents-separator kboard-view">|</span>
							<span class="contents-item kboard-view"><?php echo __('Views', 'kboard')?> <?php echo $content->view?></span>
						</div>
					</td>
					<td class="kboard-list-user">
						<?php echo apply_filters('kboard_user_display', get_avatar($content->member_uid, 24, '', $content->member_display).'<br>'.$content->member_display, $content->member_uid, $content->member_display, 'kboard', $boardBuilder)?>
					</td>
					<td class="kboard-list-date"><?php echo $content->getDate()?></td>
					<td class="kboard-list-vote"><?php echo $content->vote?></td>
					<td class="kboard-list-view"><?php echo $content->view?></td>
				</tr>
				<?php endwhile?>
				<?php while($content = $list->hasNext()):?>
				<tr class="<?php if($content->uid == kboard_uid()):?>kboard-list-selected<?php endif?>">
					<td class="kboard-list-uid"><?php echo $list->index()?></td>
					<td class="kboard-list-title">
						<a href="<?php echo $url->getDocumentURLWithUID($content->uid)?>">
							<div class="kboard-avatar-cut-strings">
								<?php if($content->isNew()):?><span class="kboard-avatar-new-notify">New</span><?php endif?>
								<?php if($content->secret):?><img src="<?php echo $skin_path?>/images/icon-lock.png" alt="<?php echo __('Secret', 'kboard')?>"><?php endif?>
								<?php echo $content->title?>
								<span class="kboard-comments-count"><?php echo $content->getCommentsCount()?></span>
							</div>
						</a>
						<div class="kboard-mobile-contents">
							<span class="contents-item kboard-user">
								<?php echo apply_filters('kboard_user_display', get_avatar($content->member_uid, 24, '', $content->member_display).' '.$content->member_display, $content->member_uid, $content->member_display, 'kboard', $boardBuilder)?>
							</span>
							<span class="contents-separator kboard-date">|</span>
							<span class="contents-item kboard-date"><?php echo $content->getDate()?></span>
							<span class="contents-separator kboard-vote">|</span>
							<span class="contents-item kboard-vote"><?php echo __('Votes', 'kboard')?> <?php echo $content->vote?></span>
							<span class="contents-separator kboard-view">|</span>
							<span class="contents-item kboard-view"><?php echo __('Views', 'kboard')?> <?php echo $content->view?></span>
						</div>
					</td>
					<td class="kboard-list-user">
						<?php echo apply_filters('kboard_user_display', get_avatar($content->member_uid, 24, '', $content->member_display).'<br>'.$content->member_display, $content->member_uid, $content->member_display, 'kboard', $boardBuilder)?>
					</td>
					<td class="kboard-list-date"><?php echo $content->getDate()?></td>
					<td class="kboard-list-vote"><?php echo $content->vote?></td>
					<td class="kboard-list-view"><?php echo $content->view?></td>
				</tr>
				<?php $boardBuilder->builderReply($content->uid)?>
				<?php endwhile?>
			</tbody>
		</table>
	</div>
	<!-- 리스트 끝 -->
	
	<!-- 페이징 시작 -->
	<div class="kboard-pagination">
		<ul class="kboard-pagination-pages">
			<?php echo kboard_pagination($list->page, $list->total, $list->rpp)?>
		</ul>
	</div>
	<!-- 페이징 끝 -->
	
	<!-- 검색폼 시작 -->
	<div class="kboard-search">
		<form id="kboard-search-form-<?php echo $board->id?>" method="get" action="<?php echo $url->toString()?>">
			<?php echo $url->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->toInput()?>
			
			<select name="target">
				<option value=""><?php echo __('All', 'kboard')?></option>
				<option value="title"<?php if(kboard_target() == 'title'):?> selected<?php endif?>><?php echo __('Title', 'kboard')?></option>
				<option value="content"<?php if(kboard_target() == 'content'):?> selected<?php endif?>><?php echo __('Content', 'kboard')?></option>
				<option value="member_display"<?php if(kboard_target() == 'member_display'):?> selected<?php endif?>><?php echo __('Author', 'kboard')?></option>
			</select>
			<input type="text" name="keyword" value="<?php echo kboard_keyword()?>">
			<button type="submit" class="kboard-avatar-button-small"><?php echo __('Search', 'kboard')?></button>
		</form>
	</div>
	<!-- 검색폼 끝 -->
	
	<?php if($board->isWriter()):?>
	<!-- 버튼 시작 -->
	<div class="kboard-control">
		<a href="<?php echo $url->getContentEditor()?>" class="kboard-avatar-button-small"><?php echo __('New', 'kboard')?></a>
	</div>
	<!-- 버튼 끝 -->
	<?php endif?>
	
	<?php if($board->contribution()):?>
	<div class="kboard-avatar-poweredby">
		<a href="http://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href);return false;" title="<?php echo __('KBoard is the best community software available for WordPress', 'kboard')?>">Powered by KBoard</a>
	</div>
	<?php endif?>
</div>
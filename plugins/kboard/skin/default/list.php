<div id="kboard-default-list">

	<!-- 검색폼 시작 -->
	<div class="kboard-header">
		<form id="kboard-search-form" method="get" action="<?php echo $url->toString()?>">
			<?php echo $url->set('category1', '')->set('category2', '')->set('pageid', '1')->set('target', '')->set('keyword', '')->set('mod', 'list')->toInput()?>
			
			<?php if($board->use_category == 'yes'):?>
			<div class="kboard-category">
				<?php if($board->initCategory1()):?>
					<select name="category1" onchange="jQuery('#kboard-search-form').submit();">
						<option value=""><?php echo __('All', 'kboard')?></option>
						<?php while($board->hasNextCategory()):?>
						<option value="<?php echo $board->currentCategory()?>"<?php if($_GET['category1'] == $board->currentCategory()):?> selected="selected"<?php endif?>><?php echo $board->currentCategory()?></option>
						<?php endwhile?>
					</select>
				<?php endif?>
				
				<?php if($board->initCategory2()):?>
					<select name="category2" onchange="jQuery('#kboard-search-form').submit();">
						<option value=""><?php echo __('All', 'kboard')?></option>
						<?php while($board->hasNextCategory()):?>
						<option value="<?php echo $board->currentCategory()?>"<?php if($_GET['category2'] == $board->currentCategory()):?> selected="selected"<?php endif?>><?php echo $board->currentCategory()?></option>
						<?php endwhile?>
					</select>
				<?php endif?>
			</div>
			<?php endif?>
			
			<div class="kboard-search">
				<select name="target">
					<option value=""><?php echo __('All', 'kboard')?></option>
					<option value="title"<?php if($_GET['target'] == 'title'):?> selected="selected"<?php endif?>><?php echo __('Title', 'kboard')?></option>
					<option value="content"<?php if($_GET['target'] == 'content'):?> selected="selected"<?php endif?>><?php echo __('Content', 'kboard')?></option>
					<option value="member_display"<?php if($_GET['target'] == 'member_display'):?> selected="selected"<?php endif?>><?php echo __('Author', 'kboard')?></option>
				</select>
				<input type="text" name="keyword" value="<?php echo $_GET['keyword']?>">
				<button type="submit" class="kboard-default-button-small"><?php echo __('Search', 'kboard')?></button>
			</div>
		</form>
	</div>
	<!-- 검색폼 끝 -->
	
	<!-- 리스트 시작 -->
	<div class="kboard-list">
		<table>
			<thead>
				<tr>
					<td class="kboard-list-uid"><?php echo __('Number', 'kboard')?></td>
					<td class="kboard-list-title"><?php echo __('Title', 'kboard')?></td>
					<td class="kboard-list-user"><?php echo __('Author', 'kboard')?></td>
					<td class="kboard-list-date"><?php echo __('Date', 'kboard')?></td>
					<td class="kboard-list-view"><?php echo __('Views', 'kboard')?></td>
				</tr>
			</thead>
			<tbody>
				<?php while($content = $list->hasNextNotice()):?>
				<tr class="kboard-list-notice">
					<td class="kboard-list-uid"><?php echo __('Notice', 'kboard')?></td>
					<td class="kboard-list-title">
						<a href="<?php echo $url->set('uid', $content->uid)->set('mod', 'document')->toString()?>">
							<div class="kboard-default-cut-strings">
								<?php echo $content->title?>
								<?php echo $content->getCommentsCount()?>
								<?php if($content->secret):?><img src="<?php echo $skin_path?>/images/icon-lock.png" alt="<?php echo __('Secret', 'kboard')?>"><?php endif?>
								<?php if($content->isNew()):?><span class="kboard-default-new-notify">New</span><?php endif?>
							</div>
							<div class="kboard-mobile-contents">
								<span class="contents-item"><img src="<?php echo $skin_path?>/images/icon-user.png" alt="<?php echo __('Author', 'kboard')?>"> <?php echo $content->member_display?></span>
								<span class="contents-item"><img src="<?php echo $skin_path?>/images/icon-date.png" alt="<?php echo __('Date', 'kboard')?>"> <?php echo date("Y.m.d", strtotime($content->date))?></span>
								<span class="contents-item"><img src="<?php echo $skin_path?>/images/icon-view.png" alt="<?php echo __('Views', 'kboard')?>"> <?php echo $content->view?></span>
							</div>
						</a>
					</td>
					<td class="kboard-list-user"><?php echo $content->member_display?></td>
					<td class="kboard-list-date"><?php echo date("Y.m.d", strtotime($content->date))?></td>
					<td class="kboard-list-view"><?php echo $content->view?></td>
				</tr>
				<?php endwhile?>
				<?php while($content = $list->hasNext()):?>
				<tr>
					<td class="kboard-list-uid"><?php echo $list->index()?></td>
					<td class="kboard-list-title">
						<a href="<?php echo $url->set('uid', $content->uid)->set('mod', 'document')->toString()?>">
							<div class="kboard-default-cut-strings">
								<?php echo $content->title?>
								<?php echo $content->getCommentsCount()?>
								<?php if($content->secret):?><img src="<?php echo $skin_path?>/images/icon-lock.png" alt="<?php echo __('Secret', 'kboard')?>"><?php endif?>
								<?php if($content->isNew()):?><span class="kboard-default-new-notify">New</span><?php endif?>
							</div>
							<div class="kboard-mobile-contents">
								<span class="contents-item"><img src="<?php echo $skin_path?>/images/icon-user.png" alt="<?php echo __('Author', 'kboard')?>"> <?php echo $content->member_display?></span>
								<span class="contents-item"><img src="<?php echo $skin_path?>/images/icon-date.png" alt="<?php echo __('Date', 'kboard')?>"> <?php echo date("Y.m.d", strtotime($content->date))?></span>
								<span class="contents-item"><img src="<?php echo $skin_path?>/images/icon-view.png" alt="<?php echo __('Views', 'kboard')?>"> <?php echo $content->view?></span>
							</div>
						</a>
					</td>
					<td class="kboard-list-user"><?php echo $content->member_display?></td>
					<td class="kboard-list-date"><?php echo date("Y.m.d", strtotime($content->date))?></td>
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
	
	<?php if($board->isWriter()):?>
	<!-- 버튼 시작 -->
	<div class="kboard-control">
		<a href="<?php echo $url->set('mod', 'editor')->toString()?>" class="kboard-default-button-small"><?php echo __('New', 'kboard')?></a>
	</div>
	<!-- 버튼 끝 -->
	<?php endif?>
	
	<div class="kboard-default-poweredby">
		<a href="http://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href);return false;" title="<?php echo __('KBoard is the best community software available for WordPress', 'kboard')?>">Powered by KBoard</a>
	</div>
</div>
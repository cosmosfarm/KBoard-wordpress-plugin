<link rel="stylesheet" href="<?=$skin_path?>/style.css">

<div id="kboard-list">

	<!-- 검색폼 시작 -->
	<div class="kboard-header">
		<form method="get" action="<?=$url->set('mod', 'list')->toString()?>">
			<?=$url->set('category1', '')->set('category2', '')->set('target', '')->set('keyword', '')->set('mod', 'list')->toInput()?>
			
			<?php if($board->use_category == 'yes'):?>
			<div class="kboard-category">
				<?php if($board->initCategory1()):?>
					<select name="category1">
						<option value="">카테고리1</option>
						<?php while($board->hasNextCategory()):?>
						<option value="<?=$board->currentCategory()?>"<?php if($_GET['category1'] == $board->currentCategory()):?> selected="selected"<?php endif?>><?=$board->currentCategory()?></option>
						<?php endwhile?>
					</select>
				<?php endif;?>
				
				<?php if($board->initCategory2()):?>
					<select name="category2">
						<option value="">카테고리2</option>
						<?php while($board->hasNextCategory()):?>
						<option value="<?=$board->currentCategory()?>"<?php if($_GET['category2'] == $board->currentCategory()):?> selected="selected"<?php endif?>><?=$board->currentCategory()?></option>
						<?php endwhile?>
					</select>
				<?php endif;?>
			</div>
			<?php endif?>
			
			<div class="kboard-search">
				<select name="target">
					<option value="">전체</option>
					<option value="title"<?php if($_GET['target'] == 'title'):?> selected="selected"<?php endif?>>제목</option>
					<option value="content"<?php if($_GET['target'] == 'content'):?> selected="selected"<?php endif?>>내용</option>
					<option value="member_display"<?php if($_GET['target'] == 'member_display'):?> selected="selected"<?php endif?>>작성자</option>
				</select>
				<input type="text" name="keyword" value="<?=$_GET['keyword']?>">
				<button type="submit" class="kboard-button-small">검색</button>
			</div>
		</form>
	</div>
	<!-- 검색폼 끝 -->
	
	<!-- 리스트 시작 -->
	<div class="kboard-list">
		<table>
			<thead>
				<tr>
					<td class="kboard-list-uid"><?=__('Number', 'kboard')?></td>
					<td class="kboard-list-thumbnail"><?=__('Thumbnail', 'kboard')?></td>
					<td class="kboard-list-title"><?=__('Title', 'kboard')?></td>
					<td class="kboard-list-user"><?=__('Author', 'kboard')?></td>
					<td class="kboard-list-date"><?=__('Date', 'kboard')?></td>
					<td class="kboard-list-view"><?=__('Views', 'kboard')?></td>
				</tr>
			</thead>
			<tbody>
				<?php while($content = $list->hasNextNotice()):?>
				<tr class="kboard-list-notice">
					<td class="kboard-list-uid">공지</td>
					<td class="kboard-list-thumbnail"></td>
					<td class="kboard-list-title"><div class="cut_strings">
							<a href="<?=$url->set('uid', $content->uid)->set('mod', 'document')->toString()?>"><?=$content->title?></a>
							<?=$content->getCommentsCount();?>
						</div></td>
					<td class="kboard-list-user"><?=$content->member_display?></td>
					<td class="kboard-list-date"><?=date("Y.m.d", strtotime($content->date))?></td>
					<td class="kboard-list-view"><?=$content->view?></td>
				</tr>
				<?php endwhile;?>
				<?php while($content = $list->hasNext()):?>
				<tr>
					<td class="kboard-list-uid"><?=$list->index()?></td>
					<td class="kboard-list-thumbnail"><?php if($content->thumbnail_file):?><img src="<?=get_site_url() . $content->thumbnail_file?>" style="max-width: 100px;" alt="<?=$content->thumbnail_name?>"><?php else:?>이미지 없음<?php endif?></td>
					<td class="kboard-list-title"><div class="cut_strings">
							<a href="<?=$url->set('uid', $content->uid)->set('mod', 'document')->toString()?>"><?=$content->title?>
							<?php if($content->secret):?><img src="<?=$skin_path?>/images/icon_lock.png" alt="비밀글"><?php endif?>
							</a>
							<?=$content->getCommentsCount();?>
						</div></td>
					<td class="kboard-list-user"><?=$content->member_display?></td>
					<td class="kboard-list-date"><?=date("Y.m.d", strtotime($content->date))?></td>
					<td class="kboard-list-view"><?=$content->view?></td>
				</tr>
				<?php endwhile;?>
			</tbody>
		</table>
	</div>
	<!-- 리스트 끝 -->
	
	<!-- 페이징 시작 -->
	<div class="kboard-pagination">
		<ul class="kboard-pagination-pages">
			<?=kboard_pagination($list->page, $list->total, $list->rpp)?>
		</ul>
	</div>
	<!-- 페이징 끝 -->
	
	<?php if($board->isWriter()):?>
	<!-- 버튼 시작 -->
	<div class="kboard-control">
		<a href="<?=$url->set('mod', 'editor')->toString()?>" class="kboard-button-small">글쓰기</a>
	</div>
	<!-- 버튼 끝 -->
	<?php endif?>
	
	<div class="kboard-poweredby">
		<a href="http://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href); return false;" title="의미있는 워드프레스 게시판">Powered by KBoard</a>
	</div>
</div>
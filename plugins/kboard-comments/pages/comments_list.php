<div class="wrap">
	<div style="float: left; margin: 7px 8px 0 0; width: 36px; height: 34px; background: url(<?php echo plugins_url('kboard/images/icon-big.png')?>) left top no-repeat;"></div>
	<h1>
		<?php echo KBOARD_COMMENTS_PAGE_TITLE?>
		<a href="http://www.cosmosfarm.com/products/kboard" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
		<a href="http://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
		<a href="http://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
		<a href="http://blog.cosmosfarm.com/" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
	</h1>
	
	<ul class="subsubsub">
		<li class="all"><a href="<?php echo KBOARD_COMMENTS_LIST_PAGE?>" class="current">모두 <span class="count">(<?php echo $commentList->getCount()?>)</span></a></li>
	</ul>
	
	<form action="<?php echo KBOARD_COMMENTS_LIST_PAGE?>" method="post">
		<div class="tablenav top">
			<div class="alignleft actions">
				<select name="action">
					<option value="-1">일괄 작업</option>
					<option value="remove">댓글 삭제</option>
				</select>
				<input type="submit" name="" id="doaction" class="button-secondary action" value="적용">
			</div>
			<div class="alignright">
				<ul class="kboard-admin-pagination">
					<?php echo kboard_pagination($commentList->page, $commentList->getCount(), $commentList->rpp)?>
				</ul>
			</div>
		</div>
		
		<table class="wp-list-table widefat striped fixed">
			<thead>
				<tr>
					<th class="manage-column check-column"><input type="checkbox"></th>
					<th class="manage-column">
						<a><span>게시판</span></a>
					</th>
					<th class="manage-column">
						<a><span>작성자</span></a>
					</th>
					<th class="manage-column" style="width: 50%;">
						<a><span>내용</span></a>
					</th>
					<th class="manage-column">
						<a><span>일자</span></a>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="manage-column check-column"><input type="checkbox"></th>
					<th class="manage-column">
						<a><span>게시판</span></a>
					</th>
					<th class="manage-column">
						<a><span>작성자</span></a>
					</th>
					<th class="manage-column">
						<a><span>내용</span></a>
					</th>
					<th class="manage-column">
						<a><span>일자</span></a>
					</th>
				</tr>
			</tfoot>
			<tbody>
				<?php if(!$commentList->getCount()):?>
				<tr>
					<th class="check-column"></th>
					<td>댓글이 없습니다.</td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<?php endif?>
				
				<?php while($comment = $commentList->hasNext()):?>
				<?php 
					$content = new KBContent();
					$content->initWithUID($comment->content_uid);
					$board = new KBoard($content->board_id);
					$url = new KBUrl();
				?>
				<tr>
					<th class="check-column"><input type="checkbox" name="comment_uid[]" value="<?php echo $comment->uid?>"></th>
					<td><a class="row-title" href="<?php echo KBOARD_SETTING_PAGE?>&board_id=<?php echo $board->uid?>" title="편집"><?php echo $board->board_name?></a></td>
					<td>
						<?php if($comment->user_uid):?>
						<a href="<?php echo admin_url('/user-edit.php?user_id='.$comment->user_uid)?>"><?php echo $comment->user_display?></a>
						<?php else:?>
						<?php echo $comment->user_display?>
						<?php endif?>
					</td>
					<td><?php echo $comment->content?> - <a href="<?php echo $url->getDocumentRedirect($comment->content_uid)?>" titlt="페이지에서 보기" onclick="window.open(this.href); return false;">페이지에서 보기</a></td>
					<td><abbr title="<?php echo date("Y-m-d H:i:s", strtotime($comment->created))?>"><?php echo date("Y-m-d H:i:s", strtotime($comment->created))?></abbr></td>
				</tr>
				<?php endwhile?>
			</tbody>
		</table>
		
		<div class="tablenav bottom">
			<div class="alignleft actions">
				<select name="action2">
					<option value="-1">일괄 작업</option>
					<option value="remove">댓글 삭제</option>
				</select>
				<input type="submit" name="" id="doaction" class="button-secondary action" value="적용">
			</div>
			<div class="alignright">
				<ul class="kboard-admin-pagination">
					<?php echo kboard_pagination($commentList->page, $commentList->getCount(), $commentList->rpp)?>
				</ul>
			</div>
		</div>
	</form>
</div>
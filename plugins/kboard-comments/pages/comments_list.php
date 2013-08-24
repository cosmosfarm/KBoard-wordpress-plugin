<div class="wrap">
	<div style="float: left; margin: 7px 8px 0 0; width: 36px; height: 34px; background: url(<?=plugins_url('kboard/images/icon-big.png')?>) left top no-repeat;"></div>
	<h2>
		<?=KBOARD_COMMENTS_PAGE_TITLE?>
		<a href="http://www.cosmosfarm.com/products/kboard" class="add-new-h2" onclick="window.open(this.href); return false;">홈페이지</a>
		<a href="http://www.cosmosfarm.com/threads" class="add-new-h2" onclick="window.open(this.href); return false;">질문하기</a>
		<a href="http://www.cosmosfarm.com/support" class="add-new-h2" onclick="window.open(this.href); return false;">기능추가 및 기술지원</a>
	</h2>
	
	<ul class="subsubsub">
		<li class="all"><a href="<?=KBOARD_COMMENTS_LIST_PAGE?>" class="current">모두 <span class="count">(<?=$commentList->getCount()?>)</span></a></li>
	</ul>
	
	<form action="<?=KBOARD_COMMENTS_LIST_PAGE?>" method="post">
		<div class="tablenav top">
			<div class="alignleft actions">
				<select name="action">
					<option value="-1">일괄 작업</option>
					<option value="remove">댓글 삭제</option>
				</select>
				<input type="submit" name="" id="doaction" class="button-secondary action" value="적용">
			</div>
		</div>
		
		<table class="wp-list-table widefat fixed">
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
					$content = new Content();
					$content->initWithUID($comment->content_uid);
					$board = new KBoard($content->board_id);
				?>
				<tr>
					<th class="check-column"><input type="checkbox" name="comment_uid[]" value="<?=$comment->uid?>"></th>
					<td><a class="row-title" href="<?=KBOARD_SETTING_PAGE?>&board_id=<?=$board->uid?>" title="편집"><?=$board->board_name?></a></td>
					<td>
						<?php if($comment->user_uid):?>
						<a href="/wp-admin/user-edit.php?user_id=<?=$comment->user_uid?>"><?=$comment->user_display?></a>
						<?php else:?>
						<?=$comment->user_display?>
						<?php endif?>
					</td>
					<td><?=$comment->content?></td>
					<td><abbr title="<?=date("Y-m-d H:i:s", strtotime($comment->created))?>"><?=date("Y-m-d H:i:s", strtotime($comment->created))?></abbr></td>
				</tr>
				<?php endwhile;?>
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
		</div>
	</form>
</div>
<div class="wrap">
	<div id="icon-themes" class="icon32"><br></div>
	<h2>
		<?=KBOARD_PAGE_TITLE?> <a href="<?=KBOARD_NEW_PAGE?>" class="add-new-h2">게시판 추가하기</a>
	</h2>
	
	<ul class="subsubsub">
		<li class="all"><a href="<?=KBOARD_LIST_PAGE?>" class="current">모두 <span class="count">(<?=$board->getCount()?>)</span></a></li>
	</ul>
	
	<form action="<?=KBOARD_LIST_PAGE?>" method="post">
		<div class="tablenav top">
			<div class="alignleft actions">
				<select name="action">
					<option value="-1">일괄 작업</option>
					<option value="remove">게시판 삭제</option>
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
						<a><span>입력코드(Shortcode)</span></a>
					</th>
					<th class="manage-column">
						<a><span>스킨</span></a>
					</th>
					<th class="manage-column">
						<a><span>읽기권한</span></a>
					</th>
					<th class="manage-column">
						<a><span>쓰기권한</span></a>
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
						<a><span>입력코드(Shortcode)</span></a>
					</th>
					<th class="manage-column">
						<a><span>스킨</span></a>
					</th>
					<th class="manage-column">
						<a><span>읽기권한</span></a>
					</th>
					<th class="manage-column">
						<a><span>쓰기권한</span></a>
					</th>
					<th class="manage-column">
						<a><span>일자</span></a>
					</th>
				</tr>
			</tfoot>
			<tbody>
				<?php if(!$board->getCount()):?>
				<tr>
					<th class="check-column"></th>
					<td><a href="<?=KBOARD_NEW_PAGE?>">게시판을 생성하세요.</a></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<?php endif?>
				
				<?php while($board->hasNext()):?>
				<tr>
					<th class="check-column"><input type="checkbox" name="board_id[]" value="<?=$board->uid?>"></th>
					<td><a class="row-title" href="<?=KBOARD_SETTING_PAGE?>&board_id=<?=$board->uid?>#board_name" title="편집"><?=$board->board_name?></a></td>
					<td><input type="text" value="[kboard id=<?=$board->uid?>]"></td>
					<td><a href="<?=KBOARD_SETTING_PAGE?>&board_id=<?=$board->uid?>#skin" title="편집"><?=$board->skin?></a></td>
					<td><a href="<?=KBOARD_SETTING_PAGE?>&board_id=<?=$board->uid?>#permission_read" title="편집"><?=kboard_permission($board->permission_read)?></a></td>
					<td><a href="<?=KBOARD_SETTING_PAGE?>&board_id=<?=$board->uid?>#permission_write" title="편집"><?=kboard_permission($board->permission_write)?></a></td>
					<td><abbr title="<?=date("Y-m-d H:i", strtotime($board->created))?>"><?=date("Y-m-d", strtotime($board->created))?></abbr></td>
				</tr>
				<?php endwhile;?>
			</tbody>
		</table>
		
		<div class="tablenav bottom">
			<div class="alignleft actions">
				<select name="action2">
					<option value="-1">일괄 작업</option>
					<option value="remove">게시판 삭제</option>
				</select>
				<input type="submit" name="" id="doaction" class="button-secondary action" value="적용">
			</div>
		</div>
	</form>
</div>
<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h2>
		KBoard : 게시판 목록
		<a href="http://www.cosmosfarm.com/products/kboard" class="add-new-h2" onclick="window.open(this.href); return false;">홈페이지</a>
		<a href="http://www.cosmosfarm.com/threads" class="add-new-h2" onclick="window.open(this.href); return false;">커뮤니티</a>
		<a href="http://www.cosmosfarm.com/support" class="add-new-h2" onclick="window.open(this.href); return false;">고객지원</a>
	</h2>
	
	<ul class="subsubsub">
		<li class="all"><a href="<?php echo KBOARD_LIST_PAGE?>" class="current">모두 <span class="count">(<?php echo $board->getCount()?>)</span></a></li>
	</ul>
	
	<form action="" method="post">
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
						<a><span>미리보기</span></a>
					</th>
					<th class="manage-column">
						<a><span>게시판</span></a>
					</th>
					<th class="manage-column">
						<a><span>스킨</span></a>
					</th>
					<th class="manage-column">
						<a><span>설치된 페이지</span></a>
					</th>
					<th class="manage-column">
						<a><span>읽기권한</span></a>
					</th>
					<th class="manage-column">
						<a><span>쓰기권한</span></a>
					</th>
					<th class="manage-column">
						<a><span>댓글쓰기권한</span></a>
					</th>
					<th class="manage-column">
						<a><span>생성일자</span></a>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="manage-column check-column"><input type="checkbox"></th>
					<th class="manage-column">
						<a><span>미리보기</span></a>
					</th>
					<th class="manage-column">
						<a><span>게시판</span></a>
					</th>
					<th class="manage-column">
						<a><span>스킨</span></a>
					</th>
					<th class="manage-column">
						<a><span>설치된 페이지</span></a>
					</th>
					<th class="manage-column">
						<a><span>읽기권한</span></a>
					</th>
					<th class="manage-column">
						<a><span>쓰기권한</span></a>
					</th>
					<th class="manage-column">
						<a><span>댓글쓰기권한</span></a>
					</th>
					<th class="manage-column">
						<a><span>생성일자</span></a>
					</th>
				</tr>
			</tfoot>
			<tbody>
				<?php if(!$board->getCount()):?>
				<tr>
					<th class="check-column"></th>
					<td><a href="<?php echo KBOARD_NEW_PAGE?>">게시판을 생성하세요</a></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<?php endif?>
				
				<?php while($board->hasNext()):?>
				<tr>
					<th class="check-column"><input type="checkbox" name="board_id[]" value="<?php echo $board->uid?>"></th>
					<td><a href="<?php echo KBOARD_SETTING_PAGE?>&board_id=<?php echo $board->uid?>" title="편집"><img src="<?php echo KBOARD_URL_PATH . "/skin/{$board->skin}/thumbnail.png"?>" style="width: 100px; height: 100px;"></a></td>
					<td><a class="row-title" href="<?php echo KBOARD_SETTING_PAGE?>&board_id=<?php echo $board->uid?>" title="편집"><?php echo $board->board_name?></a></td>
					<td><a href="<?php echo KBOARD_SETTING_PAGE?>&board_id=<?php echo $board->uid?>" title="편집"><?php echo $board->skin?></a></td>
					<td><a href="<?php echo KBOARD_SETTING_PAGE?>&board_id=<?php echo $board->uid?>" title="편집"><?php 
					$meta->setBoardID($board->uid);
					if($meta->auto_page){
						$post = get_post($meta->auto_page);
						echo $post->post_title;
					}
					else echo '페이지 연결 없음';
					?></a></td>
					<td><a href="<?php echo KBOARD_SETTING_PAGE?>&board_id=<?php echo $board->uid?>" title="편집"><?php echo kboard_permission($board->permission_read)?></a></td>
					<td><a href="<?php echo KBOARD_SETTING_PAGE?>&board_id=<?php echo $board->uid?>" title="편집"><?php echo kboard_permission($board->permission_write)?></a></td>
					<td><a href="<?php echo KBOARD_SETTING_PAGE?>&board_id=<?php echo $board->uid?>" title="편집"><?php echo $meta->permission_comment_write?kboard_permission('author'):kboard_permission('all')?></a></td>
					<td><abbr title="<?php echo date("Y-m-d H:i", strtotime($board->created))?>"><?php echo date("Y-m-d", strtotime($board->created))?></abbr></td>
				</tr>
				<?php endwhile?>
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
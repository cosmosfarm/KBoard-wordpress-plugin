<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h2>
		KBoard : 최신글 뷰
		<a href="http://www.cosmosfarm.com/products/kboard" class="add-new-h2" onclick="window.open(this.href); return false;">홈페이지</a>
		<a href="http://www.cosmosfarm.com/threads" class="add-new-h2" onclick="window.open(this.href); return false;">커뮤니티</a>
		<a href="http://www.cosmosfarm.com/support" class="add-new-h2" onclick="window.open(this.href); return false;">고객지원</a>
	</h2>
	
	<ul class="subsubsub">
		<li class="all"><a href="<?php echo KBOARD_LATESTVIEW_PAGE?>" class="current">모두 <span class="count">(<?php echo $latestviewList->total?>)</span></a></li>
	</ul>
	
	<form action="" method="post">
		<div class="tablenav top">
			<div class="alignleft actions">
				<select name="action">
					<option value="-1">일괄 작업</option>
					<option value="remove">최신글 뷰 삭제</option>
				</select>
				<input type="submit" name="" id="doaction" class="button-secondary action" value="적용">
			</div>
		</div>
		
		<table class="wp-list-table widefat fixed">
			<thead>
				<tr>
					<th class="manage-column check-column"><input type="checkbox"></th>
					<th class="manage-column">
						<a><span>최신글 뷰</span></a>
					</th>
					<th class="manage-column">
						<a><span>스킨</span></a>
					</th>
					<th class="manage-column">
						<a><span>표시 리스트 수</span></a>
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
						<a><span>최신글 뷰</span></a>
					</th>
					<th class="manage-column">
						<a><span>스킨</span></a>
					</th>
					<th class="manage-column">
						<a><span>표시 리스트 수</span></a>
					</th>
					<th class="manage-column">
						<a><span>일자</span></a>
					</th>
				</tr>
			</tfoot>
			<tbody>
				<?php if(!$latestviewList->total):?>
				<tr>
					<th class="check-column"></th>
					<td><a href="<?php echo KBOARD_LATESTVIEW_NEW_PAGE?>">최신글 뷰를 생성하세요.</a></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				<?php endif?>
				
				<?php while($latestview = $latestviewList->hasNext()):?>
				<tr>
					<th class="check-column"><input type="checkbox" name="latestview_uid[]" value="<?php echo $latestview->uid?>"></th>
					<td><a class="row-title" href="<?php echo KBOARD_LATESTVIEW_PAGE?>&latestview_uid=<?php echo $latestview->uid?>" title="편집"><?php echo $latestview->name?></a></td>
					<td><a href="<?php echo KBOARD_LATESTVIEW_PAGE?>&latestview_uid=<?php echo $latestview->uid?>" title="편집"><?php echo $latestview->skin?></a></td>
					<td><a href="<?php echo KBOARD_LATESTVIEW_PAGE?>&latestview_uid=<?php echo $latestview->uid?>" title="편집"><?php echo $latestview->rpp?></a></td>
					<td><abbr title="<?php echo date("Y-m-d H:i", strtotime($board->created))?>"><?php echo date("Y-m-d", strtotime($latestview->created))?></abbr></td>
				</tr>
				<?php endwhile;?>
			</tbody>
		</table>
		
		<div class="tablenav bottom">
			<div class="alignleft actions">
				<select name="action2">
					<option value="-1">일괄 작업</option>
					<option value="remove">최신글 뷰 삭제</option>
				</select>
				<input type="submit" name="" id="doaction" class="button-secondary action" value="적용">
			</div>
		</div>
	</form>
</div>
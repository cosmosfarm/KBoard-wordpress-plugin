<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h2>
		KBoard : 백업 및 복구
		<a href="http://www.cosmosfarm.com/products/kboard" class="add-new-h2" onclick="window.open(this.href); return false;">홈페이지</a>
		<a href="http://www.cosmosfarm.com/threads" class="add-new-h2" onclick="window.open(this.href); return false;">커뮤니티</a>
		<a href="http://www.cosmosfarm.com/support" class="add-new-h2" onclick="window.open(this.href); return false;">고객지원</a>
	</h2>
	
	<form action="<?php echo KBOARD_BACKUP_PAGE?>&action=upload" method="post" enctype="multipart/form-data">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label for="board_name">복원파일 다운로드</label></th>
					<td>
						<a href="<?php echo KBOARD_BACKUP_ACTION?>" class="button-primary">xml 파일 다운로드</a>
						<p class="description">KBoard 데이터 파일을 다운로드 받습니다. 파일은 xml 파일이며 복구하기를 통해 백업된 상태로 되돌릴 수 있습니다.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="board_name">xml 파일 선택</label></th>
					<td>
						<input type="file" name="kboard_backup_xml_file"> <input type="submit" name="submit" id="submit" class="button-primary" value="복구하기">
						<p class="description">xml 파일을 선택하고 복구하기 버튼을 누르세요. 지금까지의 데이터는 삭제되고 복원파일 데이터를 입력합니다.</p>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
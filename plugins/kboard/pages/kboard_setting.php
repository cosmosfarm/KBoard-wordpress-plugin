<?php
if(!defined('KBOARD_COMMNETS_VERSION')){
	echo '<script>alert("KBoard 댓글 플러그인을 설치해주세요.\nhttp://www.cosmosfarm.com/ 에서 다운로드 가능합니다.");history.go(-1);</script>';
	exit;
}
?>
<div class="wrap">
	<div id="icon-themes" class="icon32">
		<br>
	</div>
	<h2>
		KBoard : 게시판 정보수정 <a href="<?=KBOARD_LIST_PAGE?>" class="add-new-h2">게시판 목록보기</a>
	</h2>
	<form action="<?=KBOARD_UPDATE_ACTION?>" method="post">
		<input type="hidden" name="board_id" value="<?=$board->uid?>">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label for="board_name">게시판 이름</label></th>
					<td><input type="text" name="board_name" size="30" tabindex="1" value="<?php if(!$board->board_name):?>무명게시판 <?=date("Y-m-d", current_time('timestamp'))?><?php else:?><?=$board->board_name?><?php endif?>" id="board_name"></td>
				</tr>
				<?php if($board->uid):?>
				<tr valign="top">
					<th scope="row"><label for="">고유주소</label></th>
					<td>
						<a href="<?=plugins_url()?>/kboard/board.php?board_id=<?=$board->uid?>" onclick="window.open(this.href); return false;"><?=plugins_url()?>/kboard/board.php?board_id=<?=$board->uid?></a>
						<p class="description">고유주소는 독립적 레이아웃 편집 및 아이프레임 삽입 등 고급 사용자를 위한 편의 기능입니다. 일반 사용자는 입력코드(Shortcode)를 사용해 게시판을 생성하세요.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="shortcode">게시판 입력코드(Shortcode)</label></th>
					<td><textarea style="width: 350px" id="shortcode">[kboard id=<?=$board->uid?>]</textarea>
						<p class="description">이 코드를 포스팅 또는 페이지에 입력하세요. 자동으로 게시판이 추가됩니다.</p></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="shortcode">최신글 입력코드(Shortcode)</label></th>
					<td><textarea style="width: 350px" id="shortcode">[kboard_latest id=<?=$board->uid?> url=게시판주소 rpp=5]</textarea>
						<p class="description">이 코드를 메인페이지 또는 사이드바에 입력하세요. 최신글 리스트를 생성합니다.</p></td>
				</tr>
				<?php endif?>
				<tr valign="top">
					<th scope="row"><label for="skin">스킨</label></th>
					<td><select name="skin" id="skin" class="">
							<?php foreach($skin->list AS $key => $value):?>
							<option value="<?=$value?>" <?php if($board->skin == $value):?> selected <?php endif?>>
								<?=$value?>
							</option>
							<?php endforeach;?>
					</select>
						<p class="description">게시판 스킨에 따라 모양과 기능이 변합니다.</p></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="page_rpp">게시물 표시</label></th>
					<td><select name="page_rpp" id="page_rpp" class="">
							<option value="1"<?php if($board->page_rpp == 1):?> selected<?php endif?>>1개</option>
							<option value="4"<?php if($board->page_rpp == 4):?> selected<?php endif?>>4개</option>
							<option value="6"<?php if($board->page_rpp == 6):?> selected<?php endif?>>6개</option>
							<option value="10"<?php if($board->page_rpp == 10):?> selected<?php endif?>>10개</option>
							<option value="12"<?php if($board->page_rpp == 12):?> selected<?php endif?>>12개</option>
							<option value="15"<?php if($board->page_rpp == 15):?> selected<?php endif?>>15개</option>
							<option value="20"<?php if($board->page_rpp == 20):?> selected<?php endif?>>20개</option>
							<option value="30"<?php if($board->page_rpp == 30):?> selected<?php endif?>>30개</option>
							<option value="50"<?php if($board->page_rpp == 50):?> selected<?php endif?>>50개</option>
							<option value="100"<?php if($board->page_rpp == 100):?> selected<?php endif?>>100개</option>
					</select>
						<p class="description">한 페이지에 보여지는 게시물 숫자를 정합니다.</p></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="use_comment">댓글쓰기</label></th>
					<td>
					<?php if(KBOARD_COMMNETS_VERSION != 'KBOARD_COMMNETS_VERSION'):?>
					<select name="use_comment" id="use_comment" class="">
							<option value="">no</option>
							<option value="yes"<?php if($board->use_comment == 'yes'):?> selected<?php endif?>>yes</option>
					</select>
						<p class="description">게시글에 댓글쓰기를 활성화 합니다. (KBoard 댓글 플러그인 사용)</p></td>
					<?php else:?>
					<select name="use_comment" id="use_comment" class="">
							<option value="no" selected>no</option>
					</select>
						<p class="description">KBoard 댓글 플러그인을 설치하세요.</p></td>
					<?php endif?>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="use_editor">에디터사용</label></th>
					<td><select name="use_editor" id="use_editor" class="">
							<option value="">no</option>
							<option value="yes"<?php if($board->use_editor == 'yes'):?> selected<?php endif?>>yes</option>
					</select>
						<p class="description">에디터를 사용해 게시물을 작성할 수 있습니다.</p></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="use_category">카테고리사용</label></th>
					<td><select name="use_category" id="use_category" class="">
							<option value="">no</option>
							<option value="yes"<?php if($board->use_category == 'yes'):?> selected<?php endif?>>yes</option>
					</select>
						<p class="description">카테고리를 사용해서 게시물을 분리할 수 있습니다.</p></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="category1_list">카테고리1</label></th>
					<td><input type="text" style="width: 350px;" name="category1_list" id="category1_list" value="<?=$board->category1_list?>">
						<p class="description">카테고리를 입력하세요. 여러 카테고리를 입력하실 경우 콤마(,)로 구분됩니다.</p></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="category2_list">카테고리2</label></th>
					<td><input type="text" style="width: 350px;" name="category2_list" id="category2_list" value="<?=$board->category2_list?>">
						<p class="description">카테고리를 입력하세요. 여러 카테고리를 입력하실 경우 콤마(,)로 구분됩니다.</p></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="permission_read">읽기권한</label></th>
					<td><select name="permission_read" id="permission_read" class="">
							<option value="all"<?php if($board->permission_read == 'all'):?> selected<?php endif?>>
								<?=kboard_permission('all')?>
							</option>
							<option value="author"<?php if($board->permission_read == 'author'):?> selected<?php endif?>>
								<?=kboard_permission('author')?>
							</option>
							<option value="editor"<?php if($board->permission_read == 'editor'):?> selected<?php endif?>>
								<?=kboard_permission('editor')?>
							</option>
							<option value="administrator"<?php if($board->permission_read == 'administrator'):?> selected<?php endif?>>
								<?=kboard_permission('administrator')?>
							</option>
					</select></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="permission_write">쓰기권한</label></th>
					<td><select name="permission_write" id="permission_write" class="">
							<option value="all"<?php if($board->permission_read == 'all'):?> selected<?php endif?>>
								<?=kboard_permission('all')?>
							</option>
							<option value="author"<?php if($board->permission_write == 'author'):?> selected<?php endif?>>
								<?=kboard_permission('author')?>
							</option>
							<option value="editor"<?php if($board->permission_write == 'editor'):?> selected<?php endif?>>
								<?=kboard_permission('editor')?>
							</option>
							<option value="administrator"<?php if($board->permission_write == 'administrator'):?> selected<?php endif?>>
								<?=kboard_permission('administrator')?>
							</option>
					</select></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="admin_user">선택된 관리자</label></th>
					<td><input type="text" style="width: 350px;" name="admin_user" id="admin_user" value="<?=$board->admin_user?>">
						<p class="description">사용자 아이디를 입력하세요. 여러명을 입력하실 경우 콤마(,)로 구분됩니다.</p></td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button-primary" value="변경 사항 저장">
		</p>
	</form>
</div>
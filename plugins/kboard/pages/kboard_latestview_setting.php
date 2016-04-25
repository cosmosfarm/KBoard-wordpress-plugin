<?php
if(!defined('ABSPATH')) exit;
if(!defined('KBOARD_COMMNETS_VERSION')){
	die('<script>alert("KBoard 댓글 플러그인을 설치해주세요.\nhttp://www.cosmosfarm.com/ 에서 다운로드 가능합니다.");history.go(-1);</script>');
}
?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1>
		<?php echo __('KBoard : 최신글 뷰 관리', 'kboard')?>
		<a href="http://www.cosmosfarm.com/products/kboard" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
		<a href="http://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
		<a href="http://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
		<a href="http://blog.cosmosfarm.com/" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
	</h1>
	<form action="<?php echo admin_url('admin-post.php')?>" method="post" onsubmit="return latestview_submit()">
		<input type="hidden" name="action" value="kboard_latestview_action">
		<input type="hidden" name="latestview_uid" value="<?php echo $latestview->uid?>">
		<input type="hidden" name="latestview_link">
		<input type="hidden" name="latestview_unlink">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label for="name">최신글 뷰 이름</label></th>
					<td>
						<input type="text" id="name" name="name" size="30" tabindex="1" value="<?php if(!$latestview->name):?>무명 최신글 뷰 <?php echo date("Y-m-d", current_time('timestamp'))?><?php else:?><?php echo $latestview->name?><?php endif?>">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="skin">최신글 스킨 선택</label></th>
					<td>
						<select name="skin" id="skin">
							<?php
							if(!$latestview->skin) $latestview->skin = 'default';
							foreach($skin->getList() as $key => $value):
							?>
							<option value="<?php echo $value?>"<?php if($latestview->skin == $value):?> selected<?php endif?>>
								<?php echo $value?>
							</option>
							<?php endforeach;?>
						</select>
						<p class="description">최신글 스킨에 따라 모양과 기능이 변합니다. 디자인 수정은 스킨 폴더의 latest.php 파일을 수정해 주세요.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="rpp">표시 리스트 수</label></th>
					<td>
						<select name="rpp" id="rpp" class="">
							<?php if(!$latestview->rpp) $latestview->rpp=10;?>
							<option value="1"<?php if($latestview->rpp == 1):?> selected<?php endif?>>1개</option>
							<option value="2"<?php if($latestview->rpp == 2):?> selected<?php endif?>>2개</option>
							<option value="3"<?php if($latestview->rpp == 3):?> selected<?php endif?>>3개</option>
							<option value="4"<?php if($latestview->rpp == 4):?> selected<?php endif?>>4개</option>
							<option value="5"<?php if($latestview->rpp == 5):?> selected<?php endif?>>5개</option>
							<option value="6"<?php if($latestview->rpp == 6):?> selected<?php endif?>>6개</option>
							<option value="7"<?php if($latestview->rpp == 7):?> selected<?php endif?>>7개</option>
							<option value="8"<?php if($latestview->rpp == 8):?> selected<?php endif?>>8개</option>
							<option value="9"<?php if($latestview->rpp == 9):?> selected<?php endif?>>9개</option>
							<option value="10"<?php if($latestview->rpp == 10):?> selected<?php endif?>>10개</option>
							<option value="12"<?php if($latestview->rpp == 12):?> selected<?php endif?>>12개</option>
							<option value="15"<?php if($latestview->rpp == 15):?> selected<?php endif?>>15개</option>
							<option value="17"<?php if($latestview->rpp == 17):?> selected<?php endif?>>17개</option>
							<option value="20"<?php if($latestview->rpp == 20):?> selected<?php endif?>>20개</option>
							<option value="25"<?php if($latestview->rpp == 25):?> selected<?php endif?>>25개</option>
							<option value="30"<?php if($latestview->rpp == 30):?> selected<?php endif?>>30개</option>
							<option value="50"<?php if($latestview->rpp == 50):?> selected<?php endif?>>50개</option>
							<option value="100"<?php if($latestview->rpp == 100):?> selected<?php endif?>>100개</option>
						</select>
						<p class="description">최신글 리스트에 보여지는 게시물 숫자를 정합니다.</p>
					</td>
				</tr>
				<?php if($latestview->uid):?>
				<tr valign="top">
					<th scope="row"><label for="link">모아볼 게시판</label></th>
					<td>
						<div class="link-control-area">
							<div class="link-control-area-left">
								<p>모아볼 게시판</p>
								<select name="link" id="link" size="10" multiple="multiple">
									<?php $board_list->init(); while($board = $board_list->hasNext()):?>
										<?php if(in_array($board->uid, $linkedBoard)):?>
											<option value="<?php echo $board->uid?>"><?php echo $board->board_name?></option>
										<?php endif?>
									<?php endwhile?>
								</select>
							</div>
							<div class="link-control-area-center">
								<button class="button" onclick="return push_board();">&lt;-</button>
								<br>
								<br>
								<button class="button" onclick="return pop_board();">-&gt;</button>
							</div>
							<div class="link-control-area-right">
								<p>제외된 게시판</p>
								<select name="unlink" id="unlink" size="10" multiple="multiple">
									<?php $board_list->init(); while($board = $board_list->hasNext()):?>
										<?php if(!in_array($board->uid, $linkedBoard)):?>
											<option value="<?php echo $board->uid?>"><?php echo $board->board_name?></option>
										<?php endif?>
									<?php endwhile?>
								</select>
							</div>
						</div>
						<p class="description">모아볼 게시판들을 선택합니다.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="shortcode">모아보기 숏코드(Shortcode)</label></th>
					<td>
						<textarea style="width: 350px" id="shortcode">[kboard_latestview id=<?php echo $latestview->uid?>]</textarea>
						<p class="description">이 코드를 메인페이지 또는 사이드바에 입력하세요. 최신글 모아보기를 생성합니다.</p>
					</td>
				</tr>
				<?php endif?>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button-primary" value="변경 사항 저장">
		</p>
	</form>
</div>
<script>
function push_board(){
	jQuery('option:selected', '#unlink').each(function(){
		jQuery('#link').append(this);
	});
	return false;
}
function pop_board(){
	jQuery('option:selected', '#link').each(function(){
		jQuery('#unlink').append(this);
	});
	return false;
}
function latestview_submit(){
	var link = '';
	var unlink = '';
	
	jQuery('option', '#link').each(function(){
		link += ',' + jQuery(this).val();
	});
	jQuery('input[name=latestview_link]').val(link);
	
	jQuery('option', '#unlink').each(function(){
		unlink += ',' + jQuery(this).val();
	});
	jQuery('input[name=latestview_unlink]').val(unlink);
	
	return true;
}
</script>
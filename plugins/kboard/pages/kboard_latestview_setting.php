<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1 class="wp-heading-inline"><?php echo __('KBoard : 최신글 뷰 관리', 'kboard')?></h1>
	<a href="https://www.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
	<a href="https://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
	<a href="https://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
	<a href="https://blog.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
	
	<hr class="wp-header-end">
	
	<form action="<?php echo admin_url('admin-post.php')?>" method="post" onsubmit="return latestview_submit()">
		<input type="hidden" name="action" value="kboard_latestview_action">
		<input type="hidden" name="latestview_uid" value="<?php echo $latestview->uid?>">
		<input type="hidden" name="latestview_link">
		<input type="hidden" name="latestview_unlink">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label for="name">최신글 모아보기 이름</label></th>
					<td>
						<input type="text" id="name" name="name" class="regular-text" value="<?php if(!$latestview->name):?>무명 최신글 모아보기 <?php echo date("Y-m-d", current_time('timestamp'))?><?php else:?><?php echo $latestview->name?><?php endif?>">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="skin">최신글 스킨 선택</label></th>
					<td>
						<select name="skin" id="skin">
							<?php
							if(!$latestview->skin) $latestview->skin = 'default';
							foreach($skin->getLatestviewList() as $skin_item):
							?>
							<option value="<?php echo $skin_item->name?>"<?php if($latestview->skin == $skin_item->name):?> selected<?php endif?>><?php echo $skin_item->name?></option>
							<?php endforeach?>
						</select>
						<p class="description">최신글 스킨에 따라 모양과 기능이 변합니다.</p>
						<p class="description">디자인 수정은 스킨 폴더의 latest.php 파일을 수정해 주세요.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="rpp">게시글 표시 수(PC)</label></th>
					<td>
						<?php if(!$latestview->rpp) $latestview->rpp=10;?>
						<input type="number" name="rpp" id="rpp" value="<?php echo $latestview->rpp?>">
						<p class="description">최신글 리스트에 보여지는 게시글 개수를 정합니다.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="mobile_rpp">게시글 표시 수(모바일)</label></th>
					<td>
						<?php if(!$latestview->mobile_rpp) $latestview->mobile_rpp=$latestview->rpp;?>
						<input type="number" name="mobile_rpp" id="mobile_rpp" value="<?php echo $latestview->mobile_rpp?>">
						<p class="description">최신글 리스트에 보여지는 게시글 개수를 정합니다.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="sort">정렬 순서</label></th>
					<td>
						<select name="sort" id="sort">
							<option value="newest"<?php if(!$latestview->sort || $latestview->sort == 'newest'):?> selected<?php endif?>><?php echo __('Newest', 'kboard')?></option>
							<option value="best"<?php if($latestview->sort == 'best'):?> selected<?php endif?>><?php echo __('Best', 'kboard')?></option>
							<option value="viewed"<?php if($latestview->sort == 'viewed'):?> selected<?php endif?>><?php echo __('Viewed', 'kboard')?></option>
							<option value="updated"<?php if($latestview->sort == 'updated'):?> selected<?php endif?>><?php echo __('Updated', 'kboard')?></option>
						</select>
						<p class="description">최신글 모아보기에 표시되는 게시글 정렬순서를 정합니다.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="link">모아볼 게시판</label></th>
					<td>
						<div class="link-control-area">
							<div class="link-control-area-left">
								<p>모아볼 게시판</p>
								<select name="link" id="link" size="10" multiple="multiple">
									<?php $board_list->init(); while($board = $board_list->hasNext()):?>
										<?php if(in_array($board->uid, $linked_board)):?>
											<option value="<?php echo $board->uid?>"><?php echo $board->board_name?></option>
										<?php endif?>
									<?php endwhile?>
								</select>
							</div>
							<div class="link-control-area-center">
								<button type="button" class="button" onclick="return push_board();">◀</button>
								<br>
								<br>
								<button type="button" class="button" onclick="return pop_board();">▶</button>
							</div>
							<div class="link-control-area-right">
								<p>제외된 게시판</p>
								<select name="unlink" id="unlink" size="10" multiple="multiple">
									<?php $board_list->init(); while($board = $board_list->hasNext()):?>
										<?php if(!in_array($board->uid, $linked_board)):?>
											<option value="<?php echo $board->uid?>"><?php echo $board->board_name?></option>
										<?php endif?>
									<?php endwhile?>
								</select>
							</div>
						</div>
						<p class="description">모아볼 게시판들을 선택합니다.</p>
					</td>
				</tr>
				<?php if($latestview->uid):?>
				<tr valign="top">
					<th scope="row"><label for="shortcode">최신글 모아보기 숏코드(Shortcode)</label></th>
					<td>
						<textarea style="width: 350px" id="shortcode">[kboard_latestview id="<?php echo $latestview->uid?>"]</textarea>
						<p class="description">이 코드를 메인페이지 또는 사이드바에 입력하세요. 최신글 모아보기를 출력합니다.</p>
						<p class="description"><a href="https://blog.cosmosfarm.com/?p=1145" onclick="window.open(this.href);return false;">최신글 숏코드 사용 예제 알아보기</a></p>
					</td>
				</tr>
				<?php endif?>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php echo __('변경 사항 저장', 'kboard')?>">
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
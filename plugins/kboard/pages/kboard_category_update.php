<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1 class="wp-heading-inline"><?php echo __('KBoard : 카테고리 변경', 'kboard')?></h1>
	<a href="https://www.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
	<a href="https://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
	<a href="https://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
	<a href="https://blog.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
	
	<hr class="wp-header-end">
	<form action="<?php echo admin_url('admin-post.php')?>" method="post">
		<?php wp_nonce_field('kboard-category-execute', 'kboard-category-execute-nonce');?>
		<input type="hidden" name="action" value="kboard_category_update">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"></th>
					<td>게시글의 카테고리 이름을 일괄 변경 합니다.</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="skin">게시판 선택</label></th>
					<td>
						<select name="board_id" name="board_id" required>
							<option value="">— 선택하기 —</option>
							<?php foreach($board_list as $board):?>
								<option value="<?php echo intval($board->uid)?>"><?php echo esc_html($board->board_name)?></option>
							<?php endforeach?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="target">카테고리 선택</label></th>
					<td>
						<select name="target" id="target" required>
							<option value="">— 선택하기 —</option>
							<option value="category1">카테고리1</option>
							<option value="category2">카테고리2</option>
							<option value="category3">카테고리3</option>
							<option value="category4">카테고리4</option>
							<option value="category5">카테고리5</option>
						</select>
						<p class="description">카테고리명을 바꿀 카테고리를 선택합니다.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="before_category">기존 카테고리</label></th>
					<td>
						<input type="text" name="before_category" id="before_category" value="" class="regular-text" required>
						<p class="description">기존에 입력된 카테고리명을 입력합니다.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="after_category">바꿀 카테고리</label></th>
					<td>
						<input type="text" name="after_category" id="after_category" value="" class="regular-text" required>
						<p class="description">바꿀 카테고리명을 입력합니다.</p>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php echo __('변경 하기', 'kboard')?>">
		</p>
	</form>
</div>
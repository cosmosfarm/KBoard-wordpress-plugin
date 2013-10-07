<link rel="stylesheet" href="<?=$skin_path?>/style.css">
<script type="text/javascript" src="<?=$skin_path?>/script.js"></script>

<div class="kboard-comments">
	<div class="kboard-comments-wrap">
		
		<!-- 등록된 댓글 수 시작 -->
		<div class="comments-count">
			<?=__('Total Reply', 'kboard-comments')?> <span class="comments-total-count"><?=$commentList->getCount()?></span><?=__('Count', 'kboard-comments')?>
			<hr>
		</div>
		<!-- 등록된 댓글 수 끝 -->
		
		<!-- 댓글 리스트 시작 -->
		<?php $commentBuilder->buildTreeList('list-template.php')?>
		<!-- 댓글 리스트 끝 -->
		
		<!-- 댓글 입력 폼 시작 -->
		<form action="<?=$commentURL->getInsertURL()?>" method="post" id="kboard_comments_form" onsubmit="return kboard_comments_execute(this);">
			<input type="hidden" name="content_uid" value="<?=$commentList->content_uid?>">
			<input type="hidden" name="member_uid" value="<?=$userdata->data->ID?>">
			<div class="kboard-comments-form">
				<?php if($userdata->data->ID):?>
				<input type="hidden" name="member_display" value="<?=$userdata->data->display_name?>">
				<?php else:?>
				<div class="comments-username">
					<label class="comments-username-label" for="comments_member_display"><?=__('Author', 'kboard-comments')?></label> <input type="text" id="comments_member_display" name="member_display" value="<?=$userdata->data->display_name?>">
				</div>
				<div class="comments-password">
					<label class="comments-password-label" for="comments_password"><?=__('Password', 'kboard-comments')?></label> <input type="password" id="comments_password" name="password" value="">
				</div>
				<div class="comments-captcha">
					<label class="comments-captcha-label" for="comments_captcha"><img src="<?=kboard_captcha()?>" alt=""></label> <input type="text" id="comments_captcha" name="captcha" value="">
				</div>
				<?php endif?>
				
				<div class="comments-submit">
					<div class="comments-submit-text"><textarea name="content"></textarea></div>
					<div class="comments-submit-button"><input type="submit" value="<?=__('Submit', 'kboard-comments')?>"></div>
				</div>
			</div>
		</form>
		<!-- 댓글 입력 폼 끝 -->
		
	</div>
</div>
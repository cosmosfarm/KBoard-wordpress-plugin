<div class="kboard-comments">
	<div class="kboard-comments-wrap">
		
		<!-- 등록된 댓글 수 시작 -->
		<div class="comments-count">
			<?php echo __('Total Reply', 'kboard-comments')?> <span class="comments-total-count"><?php echo $commentList->getCount()?></span><?php echo __('Count', 'kboard-comments')?>
			<hr>
		</div>
		<!-- 등록된 댓글 수 끝 -->
		
		<!-- 댓글 리스트 시작 -->
		<?php $commentBuilder->buildTreeList('list-template.php')?>
		<!-- 댓글 리스트 끝 -->
		
		<?php if($commentBuilder->isWriter()):?>
		<!-- 댓글 입력 폼 시작 -->
		<form action="<?php echo $commentURL->getInsertURL()?>" method="post" id="kboard_comments_form" onsubmit="return kboard_comments_execute(this);">
			<input type="hidden" name="content_uid" value="<?php echo $commentList->content_uid?>">
			<input type="hidden" name="member_uid" value="<?php echo $member_uid?>">
			<div class="kboard-comments-form">
				<?php if(is_user_logged_in()):?>
				<input type="hidden" name="member_display" value="<?php echo $userdata->data->display_name?>">
				<?php else:?>
				<div class="comments-username">
					<label class="comments-username-label" for="comments_member_display"><?php echo __('Author', 'kboard-comments')?></label> <input type="text" id="comments_member_display" name="member_display" value="<?php echo $member_display?>">
				</div>
				<div class="comments-password">
					<label class="comments-password-label" for="comments_password"><?php echo __('Password', 'kboard-comments')?></label> <input type="password" id="comments_password" name="password" value="">
				</div>
				<div class="comments-captcha">
					<label class="comments-captcha-label" for="comments_captcha"><img src="<?php echo kboard_captcha()?>" alt=""></label> <input type="text" id="comments_captcha" name="captcha" value="">
				</div>
				<?php endif?>
				
				<div class="comments-submit">
					<div class="comments-submit-text"><textarea name="content"></textarea></div>
					<div class="comments-submit-button"><input type="submit" value="<?php echo __('Submit', 'kboard-comments')?>"></div>
				</div>
			</div>
		</form>
		<!-- 댓글 입력 폼 끝 -->
		<?php endif?>
	</div>
</div>

<script type="text/javascript">
var kboard_comments_localize = {
	please_enter_a_author:'<?php echo __('Please enter a author.', 'kboard-comments')?>',
	please_enter_a_password:'<?php echo __('Please enter a password.', 'kboard-comments')?>',
	please_enter_the_CAPTCHA_code:'<?php echo __('Please enter the CAPTCHA code.', 'kboard-comments')?>',
	type_the_content_of_the_comment:'<?php echo __('Type the content of the comment.', 'kboard-comments')?>',
	reply:'<?php echo __('Reply', 'kboard-comments')?>',
	cancel:'<?php echo __('Cancel', 'kboard-comments')?>'
}
</script>
<script type="text/javascript" src="<?php echo $skin_path?>/script.js"></script>
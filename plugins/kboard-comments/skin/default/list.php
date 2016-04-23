<div id="kboard-comments" class="kboard-comments-default">
	<div class="kboard-comments-wrap">
		
		<div class="comments-header">
			<div class="comments-count">
				<?php echo __('Total Reply', 'kboard-comments')?> <span class="comments-total-count"><?php echo $commentList->getCount()?></span><?php echo __('Count', 'kboard-comments')?>
			</div>
			
			<div class="comments-sort">
				<form id="kboard-comments-sort-form" method="get" action="<?php echo $url->toString()?>#kboard-comments">
					<?php echo $url->set('uid', $commentList->content_uid)->set('mod', 'document')->toInput()?>
					<select name="kboard_comments_sort" onchange="jQuery('#kboard-comments-sort-form').submit();">
						<option value="best"<?php if($commentList->getSorting() == 'best'):?> selected<?php endif?>><?php echo __('Best', 'kboard-comments')?></option>
						<option value="oldest"<?php if($commentList->getSorting() == 'oldest'):?> selected<?php endif?>><?php echo __('Oldest', 'kboard-comments')?></option>
						<option value="newest"<?php if($commentList->getSorting() == 'newest'):?> selected<?php endif?>><?php echo __('Newest', 'kboard-comments')?></option>
					</select>
				</form>
			</div>
			
			<hr>
		</div>
		
		<!-- 댓글 리스트 시작 -->
		<?php $commentBuilder->buildTreeList('list-template.php')?>
		<!-- 댓글 리스트 끝 -->
		
		<?php if($commentBuilder->isWriter()):?>
		<!-- 댓글 입력 폼 시작 -->
		<form id="kboard_comments_form" method="post" action="<?php echo $commentURL->getInsertURL()?>" onsubmit="return kboard_comments_execute(this);">
			<input type="hidden" name="content_uid" value="<?php echo $commentList->content_uid?>">
			<input type="hidden" name="member_uid" value="<?php echo $member_uid?>">
			<div class="kboard-comments-form">
			
				<?php if(is_user_logged_in()):?>
				<input type="hidden" name="member_display" value="<?php echo $member_display?>">
				<?php else:?>
				<div class="comments-field">
					<label class="comments-field-label" for="comments_member_display"><?php echo __('Author', 'kboard-comments')?></label>
					<input type="text" id="comments_member_display" name="member_display" value="" placeholder="<?php echo __('Author', 'kboard-comments')?>...">
				</div>
				<div class="comments-field">
					<label class="comments-field-label" for="comments_password"><?php echo __('Password', 'kboard-comments')?></label>
					<input type="password" id="comments_password" name="password" value="" placeholder="<?php echo __('Password', 'kboard-comments')?>...">
				</div>
				<?php endif?>
				
				<?php if($board->useCAPTCHA()):?>
				<div class="comments-field">
					<label class="comments-field-label" for="comments_captcha"><img src="<?php echo kboard_captcha()?>" alt=""></label>
					<input type="text" id="comments_captcha" name="captcha" value="" placeholder="CAPTCHA...">
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

<script type="text/javascript" src="<?php echo $skin_path?>/script.js?<?php echo KBOARD_COMMNETS_VERSION?>"></script>
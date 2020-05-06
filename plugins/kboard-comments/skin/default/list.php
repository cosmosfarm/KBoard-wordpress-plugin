<div id="kboard-comments-<?php echo $content_uid?>" class="kboard-comments-default">
	<div class="kboard-comments-wrap">
		
		<div class="comments-header">
			<div class="comments-count">
				<?php echo __('Total Reply', 'kboard-comments')?> <span class="comments-total-count"><?php echo $commentList->getCount()?></span>
			</div>
			
			<div class="comments-sort">
				<form id="kboard-comments-sort-form-<?php echo $content_uid?>" method="get" action="<?php echo $url->toString()?>#kboard-comments-<?php echo $content_uid?>">
					<?php echo $url->set('uid', $commentList->content_uid)->set('mod', 'document')->toInput()?>
					
					<select name="kboard_comments_sort" onchange="jQuery('#kboard-comments-sort-form-<?php echo $content_uid?>').submit();">
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
		<form id="kboard-comments-form-<?php echo $content_uid?>" method="post" action="<?php echo $commentURL->getInsertURL()?>" enctype="multipart/form-data" onsubmit="return kboard_comments_execute(this);">
			<input type="hidden" name="content_uid" value="<?php echo $content_uid?>">
			<input type="hidden" name="member_uid" value="<?php echo $member_uid?>">
			
			<div class="kboard-comments-form">
				<?php wp_nonce_field('kboard-comments-execute', 'kboard-comments-execute-nonce-'.$content_uid, !wp_doing_ajax())?>
				<input type="hidden" name="media_group" value="<?php echo kboard_media_group()?>">
				
				<div class="comments-field field-content">
					<?php if(defined('KBOARD_COMMENTS_WP_EDITOR') && KBOARD_COMMENTS_WP_EDITOR):?>
						<?php wp_editor($temporary->content, 'comment_content_'.$content_uid, array('media_buttons'=>$board->isAdmin(), 'textarea_name'=>'comment_content', 'tinymce'=>array('init_instance_callback' => 'function(editor){editor.on(\'focus\', kboard_comments_field_show)}')))?>
					<?php else:?>
						<textarea class="comment-textarea" name="comment_content" placeholder="<?php echo __('Add a comment', 'kboard-comments')?>..." required><?php echo esc_textarea($temporary->content)?></textarea>
					<?php endif?>
				</div>
				
				<div class="comments-field-wrap">
					<?php
					// 댓글 입력 필드 시작
					ob_start();
					?>
					
					<?php if(is_user_logged_in()):?>
					<input type="hidden" name="member_display" value="<?php echo $member_display?>">
					<?php else:?>
					<div class="comments-field field-member-display">
						<label class="comments-field-label" for="comment_member_display_<?php echo $content_uid?>"><?php echo __('Author', 'kboard-comments')?></label>
						<input type="text" id="comment_member_display_<?php echo $content_uid?>" name="member_display" value="<?php echo $temporary->member_display?>" placeholder="<?php echo __('Author', 'kboard-comments')?>..." required>
					</div>
					<div class="comments-field field-password">
						<label class="comments-field-label" for="comment_password_<?php echo $content_uid?>"><?php echo __('Password', 'kboard-comments')?></label>
						<input type="password" id="comment_password_<?php echo $content_uid?>" name="password" value="" placeholder="<?php echo __('Password', 'kboard-comments')?>..." required>
					</div>
					<?php endif?>
					
					<div class="comments-field field-image1">
						<label class="comments-field-label" for="comment_image1_<?php echo $content_uid?>"><?php echo __('Photos', 'kboard-comments')?></label>
						<input type="file" id="comment_image1_<?php echo $content_uid?>" name="comment_attach_image1" accept="image/*">
					</div>
					
					<div class="comments-field field-file1">
						<label class="comments-field-label" for="comment_file1_<?php echo $content_uid?>"><?php echo __('Attachment', 'kboard-comments')?></label>
						<input type="file" id="comment_file1_<?php echo $content_uid?>" name="comment_attach_file1">
					</div>
					
					<?php if($board->useCAPTCHA()):?>
						<?php if(kboard_use_recaptcha()):?>
							<div class="comments-field field-recaptcha">
								<div class="g-recaptcha" data-sitekey="<?php echo kboard_recaptcha_site_key()?>"></div>
							</div>
						<?php else:?>
							<div class="comments-field field-captcha">
								<label class="comments-field-label" for="comment_captcha"><img src="<?php echo kboard_captcha()?>" alt=""></label>
								<input type="text" id="comment_captcha" name="captcha" value="" placeholder="CAPTCHA..." required>
							</div>
						<?php endif?>
					<?php endif?>
					
					<?php
					// 댓글 입력 필드 출력
					$field_html = ob_get_clean();
					do_action('kboard_comments_field', $field_html, $board, $content_uid, $commentBuilder);
					?>
				</div>
				
				<div class="comments-submit-button"><input type="submit" value="<?php echo __('Submit', 'kboard-comments')?>"></div>
			</div>
		</form>
		<!-- 댓글 입력 폼 끝 -->
		<?php elseif(is_user_logged_in()):?>
		<div class="kboard-comments-login">
			<?php echo __('You do not have permission to add comments.', 'kboard-comments')?>
		</div>
		<?php else:?>
		<div class="kboard-comments-login">
			<?php
			// 로그인 메시지 출력
			do_action('kboard_comments_login_content', $board, $content_uid, $commentBuilder);
			?>
		</div>
		<?php endif?>
	</div>
</div>

<?php wp_enqueue_script('kboard-comments-default-script', "{$skin_path}/script.js", array(), KBOARD_COMMNETS_VERSION, true)?>
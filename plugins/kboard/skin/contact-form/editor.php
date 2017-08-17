<div id="kboard-contact-form-editor">
	<form class="kboard-form" method="post" action="<?php echo $url->getContentEditorExecute()?>" enctype="multipart/form-data" onsubmit="return kboard_editor_execute(this);">
		<?php wp_nonce_field('kboard-editor-execute', 'kboard-editor-execute-nonce')?>
		<input type="hidden" name="action" value="kboard_editor_execute">
		<input type="hidden" name="mod" value="editor">
		<input type="hidden" name="uid" value="<?php echo $content->uid?>">
		<input type="hidden" name="board_id" value="<?php echo $content->board_id?>">
		<input type="hidden" name="parent_uid" value="<?php echo $content->parent_uid?>">
		<input type="hidden" name="member_uid" value="<?php echo $content->member_uid?>">
		<input type="hidden" name="member_display" value="<?php echo $content->member_display?>">
		<input type="hidden" name="date" value="<?php echo $content->date?>">
		<input type="hidden" name="user_id" value="<?php echo get_current_user_id()?>">
		<input type="hidden" name="secret" value="true">
		<input type="hidden" name="wordpress_search" value="3">
		<input type="hidden" name="password" value="<?php echo uniqid()?>">
		
		<?php if($board->use_category):?>
			<?php if($board->initCategory1()):?>
			<div class="kboard-attr-row">
				<label class="attr-name" for="kboard-select-category1"><?php echo __('Category', 'kboard')?>1</label>
				<div class="attr-value">
					<select id="kboard-select-category1" name="category1">
						<option value=""><?php echo __('Category', 'kboard')?> <?php echo __('Select', 'kboard')?></option>
						<?php while($board->hasNextCategory()):?>
						<option value="<?php echo $board->currentCategory()?>"<?php if($content->category1 == $board->currentCategory()):?> selected<?php endif?>><?php echo $board->currentCategory()?></option>
						<?php endwhile?>
					</select>
				</div>
			</div>
			<?php endif?>
			
			<?php if($board->initCategory2()):?>
			<div class="kboard-attr-row">
				<label class="attr-name" for="kboard-select-category2"><?php echo __('Category', 'kboard')?>2</label>
				<div class="attr-value">
					<select id="kboard-select-category2" name="category2">
						<option value=""><?php echo __('Category', 'kboard')?> <?php echo __('Select', 'kboard')?></option>
						<?php while($board->hasNextCategory()):?>
						<option value="<?php echo $board->currentCategory()?>"<?php if($content->category2 == $board->currentCategory()):?> selected<?php endif?>><?php echo $board->currentCategory()?></option>
						<?php endwhile?>
					</select>
				</div>
			</div>
			<?php endif?>
		<?php endif?>
		
		<div class="kboard-attr-row">
			<label class="attr-name" for="kboard-input-member-display"><?php echo __('Name', 'kboard')?> <span class="attr-required-text">*</span></label>
			<div class="attr-value"><input type="text" id="kboard-input-member-display" name="member_display" value="<?php echo $content->member_display?>" placeholder="<?php echo __('Name', 'kboard')?>..."></div>
		</div>
		
		<div class="kboard-attr-row">
			<label class="attr-name" for="kboard-input-email-display"><?php echo __('Email', 'kboard')?> <span class="attr-required-text">*</span></label>
			<div class="attr-value"><input type="email" id="kboard-input-email-display" name="kboard_option_email" value="<?php echo $content->option->email?>" placeholder="<?php echo __('Email', 'kboard')?>..."></div>
		</div>
		
		<div class="kboard-attr-row">
			<label class="attr-name" for="kboard-input-tel-display"><?php echo __('Phone number', 'kboard')?></label>
			<div class="attr-value"><input type="text" id="kboard-input-tel-display" name="kboard_option_tel" value="<?php echo $content->option->tel?>" placeholder="<?php echo __('Phone number', 'kboard')?>..."></div>
		</div>
		
		<div class="kboard-attr-row kboard-attr-title">
			<label class="attr-name" for="kboard-input-title"><?php echo __('Title', 'kboard')?> <span class="attr-required-text">*</span></label>
			<div class="attr-value"><input type="text" id="kboard-input-title" name="title" value="<?php echo $content->title?>" placeholder="<?php echo __('Title', 'kboard')?>..."></div>
		</div>
		
		<?php if($board->meta->max_attached_count > 0):?>
			<!-- 첨부파일 시작 -->
			<?php for($attached_index=1; $attached_index<=$board->meta->max_attached_count; $attached_index++):?>
			<div class="kboard-attr-row">
				<label class="attr-name" for="kboard-input-file<?php echo $attached_index?>"><?php echo __('Attachment', 'kboard')?><?php echo $attached_index?></label>
				<div class="attr-value">
					<?php if(isset($content->attach->{"file{$attached_index}"})):?><?php echo $content->attach->{"file{$attached_index}"}[1]?> - <a href="<?php echo $url->getDeleteURLWithAttach($content->uid, "file{$attached_index}")?>" onclick="return confirm('<?php echo __('Are you sure you want to delete?', 'kboard')?>');"><?php echo __('Delete file', 'kboard')?></a><?php endif?>
					<input type="file" id="kboard-input-file<?php echo $attached_index?>" name="kboard_attach_file<?php echo $attached_index?>">
				</div>
			</div>
			<?php endfor?>
			<!-- 첨부파일 끝 -->
		<?php endif?>
		
		<div class="kboard-attr-row">
			<label class="attr-name" for="kboard_content"><?php echo __('Your Message', 'kboard')?> <span class="attr-required-text">*</span></label>
			<div class="attr-value">
				<?php if($board->use_editor):?>
					<?php wp_editor($content->content, 'kboard_content', array('media_buttons'=>$board->isAdmin(), 'editor_height'=>400))?>
				<?php else:?>
					<textarea name="kboard_content" id="kboard_content" placeholder="<?php echo __('Your Message', 'kboard')?>..."><?php echo $content->content?></textarea>
				<?php endif?>
			</div>
		</div>
		
		<?php if($board->useCAPTCHA() && !$content->uid):?>
			<?php if(kboard_use_recaptcha()):?>
				<div class="kboard-attr-row">
					<label class="attr-name"></label>
					<div class="attr-value"><div class="g-recaptcha" data-sitekey="<?php echo kboard_recaptcha_site_key()?>"></div></div>
				</div>
			<?php else:?>
				<div class="kboard-attr-row">
					<label class="attr-name" for="kboard-input-captcha"><img src="<?php echo kboard_captcha()?>" alt=""></label>
					<div class="attr-value"><input type="text" id="kboard-input-captcha" name="captcha" value="" placeholder="<?php echo __('CAPTCHA', 'kboard')?>..."></div>
				</div>
			<?php endif?>
		<?php endif?>
		
		<div class="kboard-control">
			<div class="left">
				<?php if($content->uid):?>
				<a href="<?php echo $url->set('uid', $content->uid)->set('mod', 'document')->toString()?>" class="kboard-contact-form-button-small"><?php echo __('Back', 'kboard')?></a>
				<a href="<?php echo $url->set('mod', 'list')->toString()?>" class="kboard-contact-form-button-small"><?php echo __('List', 'kboard')?></a>
				<?php elseif($board->isWriter()):?>
				<button type="submit" class="kboard-contact-form-button-large"><?php echo __('Send', 'kboard')?></button>
				<?php endif?>
			</div>
			<div class="right">
				<?php if($content->uid && $board->isWriter()):?>
				<button type="submit" class="kboard-contact-form-button-small"><?php echo __('Save', 'kboard')?></button>
				<?php endif?>
			</div>
		</div>
	</form>
	
	<div class="kboard-contact-form-poweredby">
		<a href="http://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href);return false;" title="<?php echo __('KBoard is the best community software available for WordPress', 'kboard')?>">Powered by KBoard</a>
	</div>
</div>

<?php if(kboard_execute_uid()):?>
<script>alert('<?php echo __('Your message was sent successfully. Thanks.', 'kboard')?>');</script>
<?php endif?>

<?php wp_enqueue_script('kboard-contact-form-script', "{$skin_path}/script.js", array(), KBOARD_VERSION, true)?>
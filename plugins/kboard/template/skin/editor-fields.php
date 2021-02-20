<?php if($field['field_type'] == 'ip'):?>
	<input type="hidden" name="kboard_option_ip" value="<?php echo kboard_user_ip()?>">
<?php elseif($field['field_type'] == 'content'):?>
	<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> <?php echo esc_attr($required)?>">
		<label class="attr-name" for="<?php echo esc_attr($meta_key)?>"><span class="field-name"><?php echo esc_html($field_name)?></span><?php if($required):?> <span class="attr-required-text">*</span><?php endif?></label>
		<div class="kboard-content">
			<?php
			echo kboard_content_editor(array(
				'board' => $board,
				'content' => $content,
				'required' => $required,
				'placeholder' => $placeholder,
				'editor_height' => '400',
			));
			?>
		</div>
	</div>
<?php elseif($field['field_type'] == 'author'):?>
	<?php if($field['permission'] == 'always_visible' || (!$field['permission'] && $board->viewUsernameField())):?>
		<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> required">
			<label class="attr-name" for="kboard-input-member-display"><span class="field-name"><?php echo esc_html($field_name)?></span> <span class="attr-required-text">*</span></label>
			<div class="attr-value"><input type="text" id="kboard-input-member-display" name="member_display" class="required" value="<?php echo $content->member_display?esc_attr($content->member_display):esc_attr($default_value)?>"<?php if($placeholder):?> placeholder="<?php echo esc_attr($placeholder)?>"<?php endif?>></div>
		</div>
	<?php elseif($field['permission'] == 'always_hide'):?>
		<input type="hidden" id="kboard-input-member-display" name="member_display" value="<?php echo $content->member_display?esc_attr($content->member_display):esc_attr($default_value)?>">
	<?php endif?>
	<?php if($board->viewUsernameField()):?>
		<div class="kboard-attr-row kboard-attr-password">
			<label class="attr-name" for="kboard-input-password"><?php echo __('Password', 'kboard')?> <span class="attr-required-text">*</span></label>
			<div class="attr-value"><input type="password" id="kboard-input-password" name="password" value="<?php echo esc_attr($content->password)?>" placeholder="<?php echo __('Password', 'kboard')?>..."></div>
		</div>
	<?php endif?>
<?php elseif($field['field_type'] == 'captcha'):?>
	<?php if($board->useCAPTCHA() && !$content->uid):?>
		<?php if(kboard_use_recaptcha()):?>
		<div class="kboard-attr-row <?php echo esc_attr($field['class'])?>">
			<label class="attr-name"></label>
			<div class="attr-value"><div class="g-recaptcha" data-sitekey="<?php echo kboard_recaptcha_site_key()?>"></div>
			<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?></div>
		</div>
		<?php else:?>
		<div class="kboard-attr-row <?php echo esc_attr($field['class'])?>">
			<label class="attr-name" for="kboard-input-captcha"><img src="<?php echo kboard_captcha()?>" alt=""></label>
			<div class="attr-value"><input type="text" id="kboard-input-captcha" name="captcha" value="" placeholder="<?php echo __('CAPTCHA', 'kboard')?>...">
			<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?></div>
		</div>
		<?php endif?>
	<?php endif?>
<?php elseif($field['field_type'] == 'attach'):?>
	<?php if($board->meta->max_attached_count > 0):?>
		<!-- 첨부파일 시작 -->
		<?php for($attached_index=1; $attached_index<=$board->meta->max_attached_count; $attached_index++):?>
		<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> attach-<?php echo $attached_index?>">
			<label class="attr-name" for="kboard-input-file<?php echo $attached_index?>"><span class="field-name"><?php echo esc_html($field_name)?></span><?php echo $attached_index?></label>
			<div class="attr-value">
				<?php if(isset($content->attach->{"file{$attached_index}"})):?><?php echo $content->attach->{"file{$attached_index}"}[1]?> - <a href="<?php echo $url->getDeleteURLWithAttach($content->uid, "file{$attached_index}")?>" onclick="return confirm('<?php echo __('Are you sure you want to delete?', 'kboard')?>');"><?php echo __('Delete file', 'kboard')?></a><?php endif?>
				<input type="file" id="kboard-input-file<?php echo $attached_index?>" name="kboard_attach_file<?php echo $attached_index?>">
				<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
			</div>
		</div>
		<?php endfor?>
		<!-- 첨부파일 끝 -->
	<?php endif?>
<?php elseif($field['field_type'] == 'category1'):?>
	<?php if(!$board->isTreeCategoryActive()):?>
		<?php if($board->initCategory1()):?>
			<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> <?php echo esc_attr($required)?>">
				<label class="attr-name" for="<?php echo esc_attr($meta_key)?>"><span class="field-name"><?php echo esc_html($field_name)?></span><?php if($required):?> <span class="attr-required-text">*</span><?php endif?></label>
				<div class="attr-value">
					<select id="<?php echo esc_attr($meta_key)?>" name="category1" class="<?php echo esc_attr($required)?>">
						<option value=""><?php echo __('Category', 'kboard')?> <?php echo __('Select', 'kboard')?></option>
						<?php while($board->hasNextCategory()):?>
						<option value="<?php echo $board->currentCategory()?>"<?php if($content->category1 == $board->currentCategory()):?> selected<?php endif?>><?php echo $board->currentCategory()?></option>
						<?php endwhile?>
					</select>
					<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
				</div>
			</div>
		<?php endif?>
	<?php endif?>
<?php elseif($field['field_type'] == 'category2'):?>
	<?php if(!$board->isTreeCategoryActive()):?>
		<?php if($board->initCategory2()):?>
			<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> <?php echo esc_attr($required)?>">
				<label class="attr-name" for="<?php echo esc_attr($meta_key)?>"><span class="field-name"><?php echo esc_html($field_name)?></span><?php if($required):?> <span class="attr-required-text">*</span><?php endif?></label>
				<div class="attr-value">
					<select id="<?php echo esc_attr($meta_key)?>" name="category2" class="<?php echo esc_attr($required)?>">
						<option value=""><?php echo __('Category', 'kboard')?> <?php echo __('Select', 'kboard')?></option>
						<?php while($board->hasNextCategory()):?>
						<option value="<?php echo $board->currentCategory()?>"<?php if($content->category2 == $board->currentCategory()):?> selected<?php endif?>><?php echo $board->currentCategory()?></option>
						<?php endwhile?>
					</select>
					<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
				</div>
			</div>
		<?php endif?>
	<?php endif?>
<?php elseif($field['field_type'] == 'tree_category'):?>
	<?php if($board->isTreeCategoryActive()):?>
		<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> <?php echo esc_attr($required)?>">
			<label class="attr-name" for="<?php echo esc_attr($meta_key)?>"><span class="field-name"><?php echo esc_html($field_name)?></span></label>
			<div class="attr-value">
				<?php for($i=1; $i<=$content->getTreeCategoryDepth(); $i++):?>
				<input type="hidden" id="tree-category-check-<?php echo $i?>" value="<?php echo $content->option->{'tree_category_'.$i}?>">
				<input type="hidden" name="kboard_option_tree_category_<?php echo $i?>" value="">
				<?php endfor?>
				<div class="kboard-tree-category-wrap"></div>
				<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
			</div>
		</div>
	<?php endif?>
<?php elseif($field['field_type'] == 'title'):?>
	<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> required">
		<label class="attr-name" for="<?php echo esc_attr($meta_key)?>"><span class="field-name"><?php echo esc_html($field_name)?></span> <span class="attr-required-text">*</span></label>
		<div class="attr-value">
			<input type="text" id="<?php echo esc_attr($meta_key)?>" name="title" class="required" value="<?php echo $content->title?esc_attr($content->title):esc_attr($default_value)?>"<?php if($placeholder):?> placeholder="<?php echo esc_attr($placeholder)?>"<?php endif?>>
			<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
		</div>
	</div>
<?php elseif($field['field_type'] == 'option'):?>
	<div class="kboard-attr-row <?php echo esc_attr($field['class'])?>">
		<label class="attr-name" for="<?php echo esc_attr($meta_key)?>"><span class="field-name"><?php echo esc_html($field_name)?></span></label>
		<div class="attr-value">
			<?php if($fields->isUseFields($field['secret_permission'], $field['secret'])):?>
				<label class="attr-value-option"><input type="checkbox" name="secret" value="true" onchange="kboard_toggle_password_field(this)"<?php if($content->secret):?> checked<?php endif?>> <?php echo __('Secret', 'kboard')?></label>
			<?php endif?>
			<?php if($fields->isUseFields($field['notice_permission'], $field['notice'])):?>
				<label class="attr-value-option"><input type="checkbox" name="notice" value="true"<?php if($content->notice):?> checked<?php endif?>> <?php echo __('Notice', 'kboard')?></label>
			<?php endif?>
			<?php do_action('kboard_skin_editor_option', $content, $board, $boardBuilder)?>
			<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
		</div>
	</div>
	<?php if(!$board->viewUsernameField()):?>
	<div style="overflow:hidden;width:0;height:0;">
		<input style="width:0;height:0;background:transparent;color:transparent;border:none;" type="text" name="fake-autofill-fields">
		<input style="width:0;height:0;background:transparent;color:transparent;border:none;" type="password" name="fake-autofill-fields">
	</div>
	<!-- 비밀글 비밀번호 필드 시작 -->
	<div class="kboard-attr-row kboard-attr-password secret-password-row"<?php if(!$content->secret):?> style="display:none"<?php endif?>>
		<label class="attr-name" for="kboard-input-password"><?php echo __('Password', 'kboard')?> <span class="attr-required-text">*</span></label>
		<div class="attr-value"><input type="password" id="kboard-input-password" name="password" value="<?php echo esc_attr($content->password)?>" placeholder="<?php echo __('Password', 'kboard')?>..."></div>
	</div>
	<!-- 비밀글 비밀번호 필드 끝 -->
	<?php endif?>
<?php elseif($field['field_type'] == 'media'):?>
	<div class="kboard-attr-row <?php echo esc_attr($field['class'])?>">
		<label class="attr-name" onclick="kboard_editor_open_media();return false;"><span class="field-name"><?php echo esc_html($field_name)?></span></label>
		<div class="attr-value">
			<a href="#" onclick="kboard_editor_open_media();return false;"><?php echo __('KBoard Add Media', 'kboard')?></a>
			<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
		</div>
	</div>
<?php elseif($field['field_type'] == 'thumbnail'):?>
	<div class="kboard-attr-row <?php echo esc_attr($field['class'])?>">
		<label class="attr-name" for="kboard-input-thumbnail"><span class="field-name"><?php echo esc_html($field_name)?></span></label>
		<div class="attr-value">
			<?php if($content->thumbnail_file):?><?php echo $content->thumbnail_name?> - <a href="<?php echo $url->getDeleteURLWithAttach($content->uid);?>" onclick="return confirm('<?php echo __('Are you sure you want to delete?', 'kboard')?>');"><?php echo __('Delete file', 'kboard')?></a><?php endif?>
			<input type="file" id="kboard-input-thumbnail" name="thumbnail" accept="image/*">
			<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
		</div>
	</div>
<?php elseif($field['field_type'] == 'search'):?>
	<?php if(isset($field['hidden']) && $field['hidden'] == '1'):?>
		<input type="hidden" name="wordpress_search" value="<?php echo esc_attr($default_value)?>">
	<?php else:?>
		<div class="kboard-attr-row <?php echo esc_attr($field['class'])?>">
			<label class="attr-name" for="kboard-select-wordpress-search"><span class="field-name"><?php echo esc_html($field_name)?></span></label>
			<div class="attr-value">
				<select id="kboard-select-wordpress-search" name="wordpress_search">
					<option value="1"<?php if($wordpress_search == '1'):?> selected<?php endif?>><?php echo __('Public', 'kboard')?></option>
					<option value="2"<?php if($wordpress_search == '2'):?> selected<?php endif?>><?php echo __('Only title (secret document)', 'kboard')?></option>
					<option value="3"<?php if($wordpress_search == '3'):?> selected<?php endif?>><?php echo __('Exclusion', 'kboard')?></option>
				</select>
				<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
			</div>
		</div>
	<?php endif?>
<?php elseif($field['field_type'] == 'text'):?>
	<?php if(isset($field['hidden']) && $field['hidden']):?>
		<input type="hidden" id="<?php echo esc_attr($meta_key)?>" class="<?php echo esc_attr($required)?>" name="<?php echo esc_attr($fields->getOptionFieldName($meta_key))?>" value="<?php echo $content->option->{$meta_key}?esc_attr($content->option->{$meta_key}):esc_attr($default_value)?>">
	<?php else:?>
	<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> meta-key-<?php echo esc_attr($meta_key)?> <?php echo esc_attr($required)?>">
		<label class="attr-name" for="<?php echo esc_attr($meta_key)?>"><span class="field-name"><?php echo esc_html($field_name)?></span><?php if($required):?> <span class="attr-required-text">*</span><?php endif?></label>
		<div class="attr-value">
			<input type="text" id="<?php echo esc_attr($meta_key)?>" class="<?php echo esc_attr($required)?>" name="<?php echo esc_attr($fields->getOptionFieldName($meta_key))?>" value="<?php echo $content->option->{$meta_key}?esc_attr($content->option->{$meta_key}):esc_attr($default_value)?>"<?php if($placeholder):?> placeholder="<?php echo esc_attr($placeholder)?>"<?php endif?>>
			<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
		</div>
	</div>
	<?php endif?>
<?php elseif($field['field_type'] == 'select' && $row):?>
	<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> meta-key-<?php echo esc_attr($meta_key)?> <?php echo esc_attr($required)?>">
		<label class="attr-name" for="<?php echo esc_attr($meta_key)?>"><span class="field-name"><?php echo esc_html($field_name)?></span><?php if($required):?> <span class="attr-required-text">*</span><?php endif?></label>
		<div class="attr-value">
			<select id="<?php echo esc_attr($meta_key)?>" name="<?php echo esc_attr($fields->getOptionFieldName($meta_key))?>"class="<?php echo esc_attr($required)?>">
				<option value=""><?php echo __('Select', 'kboard')?></option>
				<?php foreach($field['row'] as $option_key=>$option_value):?>
					<?php if(isset($option_value['label']) && $option_value['label']):?>
						<?php if($content->option->{$meta_key}):?>
							<option value="<?php echo esc_attr($option_value['label'])?>"<?php if($fields->isSavedOption($content->option->{$meta_key}, $option_value['label'])):?> selected<?php endif?>><?php echo esc_html($option_value['label'])?></option>
						<?php else:?>
							<option value="<?php echo esc_attr($option_value['label'])?>"<?php if($default_value && $default_value==$option_key):?> selected<?php endif?>><?php echo esc_html($option_value['label'])?></option>
						<?php endif?>
					<?php endif?>
				<?php endforeach?>
			</select>
			<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
		</div>
	</div>
<?php elseif($field['field_type'] == 'radio' && $row):?>
	<?php if(isset($field['row']) && $field['row']):?>
	<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> meta-key-<?php echo esc_attr($meta_key)?> <?php echo esc_attr($required)?>">
		<label class="attr-name" for="<?php echo esc_attr($meta_key)?>"><span class="field-name"><?php echo esc_html($field_name)?></span><?php if($required):?> <span class="attr-required-text">*</span><?php endif?></label>
		<div class="attr-value">
			<input type="hidden" name="<?php echo esc_attr($fields->getOptionFieldName($meta_key))?>" value="">
			<?php foreach($field['row'] as $option_key=>$option_value):?>
				<?php if(isset($option_value['label']) && $option_value['label']):?>
					<?php if($content->option->{$meta_key}):?>
						<label class="attr-value-label"><input type="radio" name="<?php echo esc_attr($fields->getOptionFieldName($meta_key))?>"class="<?php echo esc_attr($required)?>"<?php if($fields->isSavedOption($content->option->{$meta_key}, $option_value['label'])):?> checked<?php endif?> value="<?php echo esc_attr($option_value['label'])?>"> <?php echo esc_html($option_value['label'])?></label>
					<?php else:?>
						<label class="attr-value-label"><input type="radio" name="<?php echo esc_attr($fields->getOptionFieldName($meta_key))?>"class="<?php echo esc_attr($required)?>"<?php if($default_value && $default_value==$option_key):?> checked<?php endif?> value="<?php echo esc_attr($option_value['label'])?>"> <?php echo esc_html($option_value['label'])?></label>
					<?php endif?>
				<?php endif?>
			<?php endforeach?>
			<label class="attr-reset-button" style="cursor:pointer" onclick="kboard_radio_reset(this)"><?php echo __('Reset', 'kboard')?></label>
			<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
		</div>
	</div>
	<?php endif?>
<?php elseif($field['field_type'] == 'checkbox' && $row):?>
	<?php if(isset($field['row']) && $field['row']):?>
	<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> meta-key-<?php echo esc_attr($meta_key)?> <?php echo esc_attr($required)?>">
		<label class="attr-name" for="<?php echo esc_attr($meta_key)?>"><span class="field-name"><?php echo esc_html($field_name)?></span><?php if($required):?> <span class="attr-required-text">*</span><?php endif?></label>
		<div class="attr-value">
			<input type="hidden" name="<?php echo esc_attr($fields->getOptionFieldName($meta_key))?>" value="">
			<?php foreach($field['row'] as $option_key=>$option_value):?>
				<?php if(isset($option_value['label']) && $option_value['label']):?>
					<?php if($content->option->{$meta_key}):?>
						<label class="attr-value-label"><input type="checkbox" name="<?php echo esc_attr($fields->getOptionFieldName($meta_key))?>[]"class="<?php echo esc_attr($required)?>"<?php if($fields->isSavedOption($content->option->{$meta_key}, $option_value['label'])):?> checked<?php endif?> value="<?php echo esc_attr($option_value['label'])?>"> <?php echo esc_html($option_value['label'])?></label>
					<?php else:?>
						<label class="attr-value-label"><input type="checkbox" name="<?php echo esc_attr($fields->getOptionFieldName($meta_key))?>[]"class="<?php echo esc_attr($required)?>"<?php if($default_value && in_array($option_value['label'], $default_value)):?> checked<?php endif?> value="<?php echo esc_attr($option_value['label'])?>"> <?php echo esc_html($option_value['label'])?></label>
					<?php endif?>
				<?php endif?>
			<?php endforeach?>
			<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
		</div>
	</div>
	<?php endif?>
<?php elseif($field['field_type'] == 'textarea'):?>
	<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> meta-key-<?php echo esc_attr($meta_key)?> <?php echo esc_attr($required)?>">
		<label class="attr-name" for="<?php echo esc_attr($meta_key)?>"><span class="field-name"><?php echo esc_html($field_name)?></span><?php if($required):?> <span class="attr-required-text">*</span><?php endif?></label>
		<div class="attr-value">
			<textarea id="<?php echo esc_attr($meta_key)?>" name="<?php echo esc_attr($fields->getOptionFieldName($meta_key))?>"class="editor-textarea <?php echo esc_attr($required)?>"<?php if($placeholder):?> placeholder="<?php echo esc_attr($placeholder)?>"<?php endif?>><?php echo $content->option->{$meta_key}?esc_textarea($content->option->{$meta_key}):esc_textarea($default_value)?></textarea>
			<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
		</div>
	</div>
<?php elseif($field['field_type'] == 'file'):?>
	<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> meta-key-<?php echo esc_attr($meta_key)?>">
		<label class="attr-name" for="<?php echo esc_attr($meta_key)?>"><span class="field-name"><?php echo esc_html($field_name)?></span></label>
		<div class="attr-value">
			<?php if(isset($content->attach->{$meta_key})):?><?php echo $content->attach->{$meta_key}[1]?> - <a href="<?php echo $url->getDeleteURLWithAttach($content->uid, $meta_key)?>" onclick="return confirm('<?php echo __('Are you sure you want to delete?', 'kboard')?>');"><?php echo __('Delete file', 'kboard')?></a><?php endif?>
				<input type="file" id="kboard-input-<?php echo esc_attr($meta_key)?>" name="kboard_attach_<?php echo esc_attr($meta_key)?>">
			<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
		</div>
	</div>
<?php elseif($field['field_type'] == 'wp_editor'):?>
	<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> meta-key-<?php echo esc_attr($meta_key)?> <?php echo esc_attr($required)?>">
		<label class="attr-name" for="<?php echo esc_attr($meta_key)?>"><span class="field-name"><?php echo esc_html($field_name)?></span><?php if($required):?> <span class="attr-required-text">*</span><?php endif?></label>
		<div class="attr-value">
			<?php wp_editor($content->option->{$meta_key}?$content->option->{$meta_key}:$default_value, $fields->getOptionFieldName($meta_key), array('media_buttons'=>$board->isAdmin(), 'editor_height'=>400, 'editor_class'=>$required))?>
			<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
		</div>
	</div>
<?php elseif($field['field_type'] == 'html'):?>
	<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> meta-key-<?php echo esc_attr($meta_key)?>">
		<?php echo $html?>
	</div>
<?php elseif($field['field_type'] == 'shortcode'):?>
	<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> meta-key-<?php echo esc_attr($meta_key)?>">
		<?php echo do_shortcode($shortcode)?>
	</div>
<?php elseif($field['field_type'] == 'date'):?>
	<?php
	wp_enqueue_style('kboard-jquery-flick-style');
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('kboard-field-date');
	?>
	<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> meta-key-<?php echo esc_attr($meta_key)?> <?php echo esc_attr($required)?>">
		<label class="attr-name" for="<?php echo esc_attr($meta_key)?>"><span class="field-name"><?php echo esc_html($field_name)?></span><?php if($required):?> <span class="attr-required-text">*</span><?php endif?></label>
		<div class="attr-value">
			<input type="text" id="<?php echo esc_attr($meta_key)?>" class="<?php echo esc_attr($required)?> datepicker" name="<?php echo esc_attr($fields->getOptionFieldName($meta_key))?>" value="<?php echo $content->option->{$meta_key}?esc_attr($content->option->{$meta_key}):esc_attr($default_value)?>"<?php if($placeholder):?> placeholder="<?php echo esc_attr($placeholder)?>"<?php endif?>>
			<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
		</div>
	</div>
<?php elseif($field['field_type'] == 'time'):?>
	<?php
	wp_enqueue_style('jquery-timepicker');
	wp_enqueue_script('jquery-timepicker');
	wp_enqueue_script('kboard-field-time');
	?>
	<div class="kboard-attr-row <?php echo esc_attr($field['class'])?> meta-key-<?php echo esc_attr($meta_key)?> <?php echo esc_attr($required)?>">
		<label class="attr-name" for="<?php echo esc_attr($meta_key)?>"><span class="field-name"><?php echo esc_html($field_name)?></span><?php if($required):?> <span class="attr-required-text">*</span><?php endif?></label>
		<div class="attr-value">
			<input type="text" id="<?php echo esc_attr($meta_key)?>" class="<?php echo esc_attr($required)?> timepicker" name="<?php echo esc_attr($fields->getOptionFieldName($meta_key))?>" value="<?php echo $content->option->{$meta_key}?esc_attr($content->option->{$meta_key}):esc_attr($default_value)?>"<?php if($placeholder):?> placeholder="<?php echo esc_attr($placeholder)?>"<?php endif?>>
			<?php if(isset($field['description']) && $field['description']):?><div class="description"><?php echo esc_html($field['description'])?></div><?php endif?>
		</div>
	</div>
<?php endif?>
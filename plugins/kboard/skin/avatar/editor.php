<link rel="stylesheet" href="<?=$skin_path?>/style.css">
<script type="text/javascript" src="<?=$skin_path?>/script.js"></script>

<div id="kboard-editor">
	<form method="post" action="<?=$url->toString()?>" enctype="multipart/form-data" onsubmit="return kboard_editor_execute(this);">
		<input type="hidden" name="mod" value="editor">
		<input type="hidden" name="uid" value="<?=$content->uid?>">
		<input type="hidden" name="member_uid" value="<?=$content->member_uid?>">
		<input type="hidden" name="member_display" value="<?=$content->member_display?>">
		<input type="hidden" name="date" value="<?=$content->date?>">
		<div class="kboard-header"></div>
		
		<div class="kboard-attr-row kboard-attr-title">
			<label class="attr-name"><?=__('Title')?></label>
			<div class="attr-value"><input type="text" name="title" value="<?=$content->title?>"></div>
		</div>
		
		<?php if($board->use_category):?>
			<?php if($board->initCategory1()):?>
			<div class="kboard-attr-row">
				<label class="attr-name"><?=__('Category', 'kboard')?>1</label>
				<div class="attr-value">
					<select name="category1">
						<?php while($board->hasNextCategory()):?>
						<option value="<?=$board->currentCategory()?>"<?php if($content->category1 == $board->currentCategory()):?> selected="selected" <?php endif?>><?=$board->currentCategory()?></option>
						<?php endwhile?>
					</select>
				</div>
			</div>
			<?php endif?>
			
			<?php if($board->initCategory2()):?>
			<div class="kboard-attr-row">
				<label class="attr-name"><?=__('Category', 'kboard')?>2</label>
				<div class="attr-value">
					<select name="category2">
						<?php while($board->hasNextCategory()):?>
						<option value="<?=$board->currentCategory()?>"<?php if($content->category2 == $board->currentCategory()):?> selected="selected" <?php endif?>><?=$board->currentCategory()?></option>
						<?php endwhile?>
					</select>
				</div>
			</div>
			<?php endif?>
		<?php endif?>
		
		<div class="kboard-attr-row">
			<label class="attr-name"><?=__('Secret', 'kboard')?></label>
			<div class="attr-value"><input type="checkbox" name="secret" value="true"<?php if($content->secret == 'true'):?> checked<?php endif?>></div>
		</div>
		
		<?php if($board->isAdmin()):?>
		<div class="kboard-attr-row">
			<label class="attr-name"><?=__('Notice', 'kboard')?></label>
			<div class="attr-value"><input type="checkbox" name="notice" value="true"<?php if($content->notice == 'true'):?> checked<?php endif?>></div>
		</div>
		<?php elseif($board->isWriter() && $board->permission_write=='all'):?>
		<div class="kboard-attr-row">
			<label class="attr-name"><?=__('Author', 'kboard')?></label>
			<div class="attr-value"><input type="text" name="member_display" value="<?=$content->member_display?$content->member_display:$userdata->data->display_name?>"></div>
		</div>
		<div class="kboard-attr-row">
			<label class="attr-name"><?=__('Password', 'kboard')?></label>
			<div class="attr-value"><input type="password" name="password" value="<?=$content->password?>"></div>
		</div>
		<div class="kboard-attr-row">
			<label class="attr-name"><img src="<?=kboard_captcha()?>" alt=""></label>
			<div class="attr-value"><input type="text" name="captcha" value=""></div>
		</div>
		<?php endif?>
		
		<div class="kboard-content">
			<?php if($board->use_editor):?>
				<?php wp_editor($content->content, 'kboard_content'); ?>
			<?php else:?>
				<textarea name="kboard_content" id="kboard_content"><?=$content->content?></textarea>
			<?php endif?>
		</div>
		
		<div class="kboard-attr-row">
			<label class="attr-name"><?=__('Thumbnail', 'kboard')?></label>
			<div class="attr-value">
				<?php if($content->thumbnail_file):?><?=$content->thumbnail_name?> - <a href="<?=$url->getDeleteURLWithAttach($content->uid);?>" onclick="return confirm('<?=__('Are you sure you want to delete?', 'kboard')?>');"><?=__('Delete', 'kboard')?></a><?php endif?>
				<input type="file" name="thumbnail">
			</div>
		</div>
		
		<div class="kboard-attr-row">
			<label class="attr-name"><?=__('Attachment', 'kboard')?></label>
			<div class="attr-value">
				<?php if($content->attach->file1[0]):?><?=$content->attach->file1[1]?> - <a href="<?=$url->getDeleteURLWithAttach($content->uid, 'file1');?>" onclick="return confirm('<?=__('Are you sure you want to delete?', 'kboard')?>');"><?=__('Delete', 'kboard')?></a><?php endif?>
				<input type="file" name="kboard_attach_file1">
			</div>
		</div>
		<div class="kboard-attr-row">
			<label class="attr-name"><?=__('Attachment', 'kboard')?></label>
			<div class="attr-value">
				<?php if($content->attach->file2[0]):?><?=$content->attach->file2[1]?> - <a href="<?=$url->getDeleteURLWithAttach($content->uid, 'file2');?>" onclick="return confirm('<?=__('Are you sure you want to delete?', 'kboard')?>');"><?=__('Delete', 'kboard')?></a><?php endif?>
				<input type="file" name="kboard_attach_file2">
			</div>
		</div>
		
		<div class="kboard-attr-row">
			<label class="attr-name"><?=__('WP Search', 'kboard')?></label>
			<div class="attr-value">
				<select name="wordpress_search">
					<option value="1"<?php if($content->search == '1'):?> selected<?php endif?>><?=__('Public', 'kboard')?></option>
					<option value="2"<?php if($content->search == '2'):?> selected<?php endif?>><?=__('Only title (secret document)', 'kboard')?></option>
					<option value="3"<?php if($content->search == '3'):?> selected<?php endif?>><?=__('Exclusion', 'kboard')?></option>
				</select>
			</div>
		</div>
		
		<div class="kboard-control">
			<div class="left">
				<?php if($content->uid):?>
				<a href="<?=$url->set('uid', $content->uid)->set('mod', 'document')->toString()?>" class="kboard-button-small"><?=__('Back', 'kboard')?></a>
				<a href="<?=$url->toString()?>" class="kboard-button-small"><?=__('List', 'kboard')?></a>
				<?php else:?>
				<a href="<?=$url->toString()?>" class="kboard-button-small"><?=__('Back', 'kboard')?></a>
				<?php endif?>
			</div>
			<div class="right">
				<?php if($board->isWriter()):?>
				<button type="submit" class="kboard-button-small"><?=__('Save', 'kboard')?></button>
				<?php endif?>
			</div>
		</div>
	</form>
</div>
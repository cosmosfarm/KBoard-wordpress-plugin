<link rel="stylesheet" href="<?=$skin_path?>/style.css">
<script type="text/javascript" src="<?=$skin_path?>/script.js"></script>

<div id="kboard-editor">
	<form method="post" action="<?=$url->toString()?>" enctype="multipart/form-data" onsubmit="return kboard_editor_execute(this);">
		<input type="hidden" name="mod" value="editor">
		<input type="hidden" name="uid" value="<?=$content->uid?>">
		<input type="hidden" name="member_uid" value="<?=$content->member_uid?>">
		<input type="hidden" name="member_display" value="<?=$content->member_display?>">
		<input type="hidden" name="date" value="<?=$content->date?>">
		<input type="hidden" name="next" value="<?=$next_url?>">
		<div class="kboard-header"></div>
		
		<div class="kboard-attr-row kboard-attr-title">
			<label class="attr-name">제목</label>
			<div class="attr-value"><input type="text" name="title" value="<?=$content->title?>"></div>
		</div>
		
		<?php if($board->use_category):?>
			<?php if($board->initCategory1()):?>
			<div class="kboard-attr-row">
				<label class="attr-name">카테고리1</label>
				<div class="attr-value">
					<select name="category1">
						<?php while($board->hasNextCategory()):?>
						<option value="<?=$board->currentCategory()?>"<?php if($content->category1 == $board->currentCategory()):?> selected="selected" <?php endif?>><?=$board->currentCategory()?></option>
						<?php endwhile?>
					</select>
				</div>
			</div>
			<?php endif;?>
			
			<?php if($board->initCategory2()):?>
			<div class="kboard-attr-row">
				<label class="attr-name">카테고리2</label>
				<div class="attr-value">
					<select name="category2">
						<?php while($board->hasNextCategory()):?>
						<option value="<?=$board->currentCategory()?>"<?php if($content->category2 == $board->currentCategory()):?> selected="selected" <?php endif?>><?=$board->currentCategory()?></option>
						<?php endwhile?>
					</select>
				</div>
			</div>
			<?php endif;?>
		<?php endif?>
		
		<div class="kboard-attr-row">
			<label class="attr-name">비밀글</label>
			<div class="attr-value"><input type="checkbox" name="secret" value="true"<?php if($content->secret == 'true'):?> checked<?php endif?>></div>
		</div>
		
		<?php if($board->isAdmin()):?>
		<div class="kboard-attr-row">
			<label class="attr-name">공지사항</label>
			<div class="attr-value"><input type="checkbox" name="notice" value="true"<?php if($content->notice == 'true'):?> checked<?php endif?>></div>
		</div>
		<?php elseif($board->isWriter() && $board->permission_write=='all'):?>
		<div class="kboard-attr-row">
			<label class="attr-name">작성자</label>
			<div class="attr-value"><input type="text" name="member_display" value="<?=$content->member_display?$content->member_display:$userdata->data->display_name?>"></div>
		</div>
		<div class="kboard-attr-row">
			<label class="attr-name">비밀번호</label>
			<div class="attr-value"><input type="password" name="password" value="<?=$content->password?>"></div>
		</div>
		<div class="kboard-attr-row">
			<label class="attr-name"><img src="<?=kboard_captcha()?>" alt=""></label>
			<div class="attr-value"><input type="text" name="captcha" value=""></div>
		</div>
		<?php endif;?>
		
		<div class="kboard-attr-row">
			<label class="attr-name">이름</label>
			<div class="attr-value"><input type="text" name="kboard_option_name" value="<?=$content->option->name?>"></div>
		</div>
		<div class="kboard-attr-row">
			<label class="attr-name">연락처</label>
			<div class="attr-value"><input type="text" name="kboard_option_tel" value="<?=$content->option->tel?>"></div>
		</div>
		
		<div class="kboard-content">
			<?php if($board->use_editor):?>
				<?php wp_editor($content->content, 'kboard_content'); ?>
			<?php else:?>
				<textarea name="kboard_content" id="kboard_content"><?=$content->content?></textarea>
			<?php endif;?>
		</div>
		
		<div class="kboard-attr-row">
			<label class="attr-name">이미지</label>
			<div class="attr-value">
				<?php if($content->thumbnail_file):?><?=$content->thumbnail_name?> - <a href="<?=$url->getDeleteURLWithAttach($content->uid);?>" onclick="return confirm('삭제 하시겠습니까?');">삭제</a><?php endif?>
				<input type="file" name="thumbnail">
			</div>
		</div>
		
		<div class="kboard-attr-row">
			<label class="attr-name">첨부파일</label>
			<div class="attr-value">
				<?php if($content->attach->file1[0]):?><?=$content->attach->file1[1]?> - <a href="<?=$url->getDeleteURLWithAttach($content->uid, 'file1');?>" onclick="return confirm('삭제 하시겠습니까?');">삭제</a><?php endif?>
				<input type="file" name="kboard_attach_file1">
			</div>
		</div>
		<div class="kboard-attr-row">
			<label class="attr-name">첨부파일</label>
			<div class="attr-value">
				<?php if($content->attach->file2[0]):?><?=$content->attach->file2[1]?> - <a href="<?=$url->getDeleteURLWithAttach($content->uid, 'file2');?>" onclick="return confirm('삭제 하시겠습니까?');">삭제</a><?php endif?>
				<input type="file" name="kboard_attach_file2">
			</div>
		</div>
		
		<div class="kboard-attr-row">
			<label class="attr-name">통합검색</label>
			<div class="attr-value">
				<select name="search">
					<option value="1"<?php if($content->search == '1'):?> selected<?php endif?>>제목과 내용 검색허용</option>
					<option value="2"<?php if($content->search == '2'):?> selected<?php endif?>>제목만 검색허용 (비밀글)</option>
					<option value="3"<?php if($content->search == '3'):?> selected<?php endif?>>통함검색 제외</option>
				</select>
			</div>
		</div>
		
		<div class="kboard-control">
			<div class="left">
				<?php if($content->uid):?>
				<a href="<?=$url->set('uid', $content->uid)->set('mod', 'document')->toString()?>" class="kboard-button-small">돌아가기</a>
				<?php else:?>
				<a href="<?=$url->toString()?>" class="kboard-button-small">돌아가기</a>
				<?php endif?>
			</div>
			<div class="right">
				<?php if($board->isWriter()):?>
				<button type="submit" class="kboard-button-small">글저장</button>
				<?php endif;?>
			</div>
		</div>
	</form>
</div>
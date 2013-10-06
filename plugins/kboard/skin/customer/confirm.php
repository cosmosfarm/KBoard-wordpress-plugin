<link rel="stylesheet" href="<?=$skin_path?>/style.css">

<div id="kboard-editor">
	<form method="post" action="<?=$url->set('mod', $_GET['mod'])->set('uid', $_GET['uid'])->toString()?>">
		<div class="kboard-header"></div>
		
		<div class="kboard-attr-row kboard-attr-title">
			<label class="attr-name"><?=__('Password', 'kboard')?></label>
			<div class="attr-value"><input type="password" name="password"></div>
		</div>
		
		<div class="kboard-control">
			<div class="left">
				<?php if($content->uid):?>
				<a href="<?=$url->set('uid', $content->uid)->set('mod', 'document')->toString()?>" class="kboard-button-small"><?=__('Document', 'kboard')?></a>
				<?php endif?>
				<a href="<?=$url->toString()?>" class="kboard-button-small"><?=__('List', 'kboard')?></a>
			</div>
			<div class="right">
				<button type="submit" class="kboard-button-small"><?=__('Password confirm', 'kboard')?></button>
			</div>
		</div>
	</form>
</div>
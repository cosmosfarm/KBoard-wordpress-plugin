<link rel="stylesheet" href="<?=$skin_path?>/style.css">

<div id="kboard-editor">
	<form method="post" action="<?=$url->set('mod', $_GET['mod'])->set('uid', $_GET['uid'])->toString()?>">
		<div class="kboard-header"></div>
		
		<div class="kboard-attr-row kboard-attr-title">
			<label class="attr-name">비밀번호</label>
			<div class="attr-value"><input type="password" name="password"></div>
		</div>
		
		<div class="kboard-control">
			<div class="left">
				<?php if($content->uid):?>
				<a href="<?=$url->set('uid', $content->uid)->set('mod', 'document')->toString()?>" class="kboard-button-small">본문으로</a>
				<?php endif?>
				<a href="<?=$url->toString()?>" class="kboard-button-small">목록보기</a>
			</div>
			<div class="right">
				<button type="submit" class="kboard-button-small">비밀번호 확인</button>
			</div>
		</div>
	</form>
</div>
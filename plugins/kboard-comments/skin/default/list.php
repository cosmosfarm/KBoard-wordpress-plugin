<link rel="stylesheet" href="<?=$skin_path?>/style.css">
<script type="text/javascript" src="<?=$skin_path?>/script.js"></script>

<div class="kboard-comments">
	<form action="<?=plugins_url().'/kboard-comments/execute/insert.php'?>" method="post" id="kboard_comments_form" onsubmit="return kboard_comments_execute(this);">
		<input type="hidden" name="content_uid" value="<?=$_REQUEST['uid']?>">
		<input type="hidden" name="member_uid" value="<?=$userdata->data->ID?>">
		
		<div class="kboard-comments-wrap">
			<div class="social-icon"></div>
			
			<?php if($userdata->data->ID):?>
			<input type="hidden" name="member_display" value="<?=$userdata->data->display_name?>">
			<?php else:?>
			<div class="comments-username">
				<span class="username">Author</span> <input type="text" name="member_display" value="<?=$userdata->data->display_name?>">
				<span class="password">Password</span> <input type="password" name="password" value="">
			</div>
			<?php endif?>
			
			<div class="comments-content">
				<div class="comments-content-text"><textarea name="content"></textarea></div>
				<div class="comments-content-button"><input type="submit" value="Write"></div>
			</div>
			<div class="comments-count">
				Comment <span class="total-count"><?=$commentList->getCount()?></span>개
				<hr>
			</div>
			<div class="comments-list">
				<ul>
					<?php while($comment = $commentList->hasNext()):?>
					<li>
						<div class="username"><?=$comment->user_display?></div>
						<div class="create"><?=date("Y-m-d H:i", strtotime($comment->created))?></div>
						<div class="content">
							<?=nl2br($comment->content)?>
							
							<?php if($comment->isEditor()):?>
							 - <a href="<?=plugins_url().'/kboard-comments/execute/delete.php?uid='.$comment->uid?>" onclick="return confirm('Are your sure to Delete?');">Delete</a>
							<?php else:?>
							 - <a href="<?=plugins_url().'/kboard-comments/execute/confirm.php?uid='.$comment->uid?>" onclick="open_confirm(this.href); return false;">Delete</a>
							<?php endif?>
						</div>
					</li>
					<?php endwhile;?>
				</ul>
			</div>
		</div>
	</form>
</div>
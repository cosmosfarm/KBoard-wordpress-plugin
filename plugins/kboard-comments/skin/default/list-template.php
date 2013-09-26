<div class="comments-list">
	<ul>
		<?php while($comment = $commentList->hasNext()): $commentURL->setCommentUID($comment->uid);?>
		<li>
			<div class="comments-list-username"><?=$comment->user_display?></div>
			<div class="comments-list-create"><?=date("Y-m-d H:i", strtotime($comment->created))?></div>
			<div class="comments-list-content">
				<?=nl2br($comment->content)?>
			</div>
			<div class="comments-list-controller">
				<span>
					<?php if($comment->isEditor()):?>
					<a href="<?=$commentURL->getDeleteURL()?>" onclick="return confirm('삭제 하시겠습니까?');">삭제</a>
					<?php else:?>
					<a href="<?=$commentURL->getConfirmURL()?>" onclick="return kboard_comments_open_confirm(this.href);">삭제</a>
					<?php endif?>
				</span>
				<span>
					<a href="#" onclick="return kboard_comments_reply(this, '#kboard_comments_reply_form_<?=$comment->uid?>', '#kboard_comments_form');" class="kboard-reply">댓글</a>
				</span>
			</div>
			<hr>
			
			<!-- 댓글 리스트 시작 -->
			<?php $commentBuilder->buildTreeList('list-template.php', $comment->uid)?>
			<!-- 댓글 리스트 끝 -->
			
			<!-- 댓글 입력 폼 시작 -->
			<form action="<?=$commentURL->getInsertURL()?>" method="post" id="kboard_comments_reply_form_<?=$comment->uid?>" class="comments-reply-form" onsubmit="return kboard_comments_execute(this);">
				<input type="hidden" name="content_uid" value="<?=$comment->content_uid?>">
				<input type="hidden" name="parent_uid" value="<?=$comment->uid?>">
				<input type="hidden" name="member_uid" value="<?=$userdata->data->ID?>">
			</form>
			<!-- 댓글 입력 폼 끝 -->
		</li>
		<?php endwhile?>
	</ul>
</div>
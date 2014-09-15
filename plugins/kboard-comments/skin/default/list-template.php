<div class="comments-list">
	<ul>
		<?php while($comment = $commentList->hasNext()): $commentURL->setCommentUID($comment->uid);?>
		<li itemscope itemtype="http://schema.org/Comment" class="kboard-comments-item" data-username="<?php echo $comment->user_display?>" data-created="<?php echo $comment->created?>">
			<div class="comments-list-username" itemprop="author"><?php echo $comment->user_display?></div>
			<div class="comments-list-create" itemprop="dateCreated"><?php echo date("Y-m-d H:i", strtotime($comment->created))?></div>
			<div class="comments-list-content" itemprop="description">
				<?php echo nl2br($comment->content)?>
			</div>
			
			<?php if($commentBuilder->isWriter()):?>
			<div class="comments-list-controller">
				<span>
					<?php if($comment->isEditor()):?>
					<a href="<?php echo $commentURL->getDeleteURL()?>" onclick="return confirm('<?php echo __('Are you sure you want to delete?', 'kboard-comments')?>');"><?php echo __('Delete', 'kboard-comments')?></a>
					<?php else:?>
					<a href="<?php echo $commentURL->getConfirmURL()?>" onclick="return kboard_comments_open_confirm(this.href);"><?php echo __('Delete', 'kboard-comments')?></a>
					<?php endif?>
				</span>
				<span style="color: #a0a0a0;">|</span>
				<span>
					<a href="#" onclick="return kboard_comments_reply(this, '#kboard_comments_reply_form_<?php echo $comment->uid?>', '#kboard_comments_form');" class="kboard-reply"><?php echo __('Reply', 'kboard-comments')?></a>
				</span>
				
			</div>
			<?php endif?>
			
			<hr>
			
			<!-- 댓글 리스트 시작 -->
			<?php $commentBuilder->buildTreeList('list-template.php', $comment->uid)?>
			<!-- 댓글 리스트 끝 -->
			
			<!-- 댓글 입력 폼 시작 -->
			<form action="<?php echo $commentURL->getInsertURL()?>" method="post" id="kboard_comments_reply_form_<?php echo $comment->uid?>" class="comments-reply-form" onsubmit="return kboard_comments_execute(this);">
				<input type="hidden" name="content_uid" value="<?php echo $comment->content_uid?>">
				<input type="hidden" name="parent_uid" value="<?php echo $comment->uid?>">
				<input type="hidden" name="member_uid" value="<?php echo $userdata->data->ID?>">
			</form>
			<!-- 댓글 입력 폼 끝 -->
		</li>
		<?php endwhile?>
	</ul>
</div>
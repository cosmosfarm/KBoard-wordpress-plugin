<div class="comments-list">
	<ul>
		<?php while($comment = $commentList->hasNext()): $commentURL->setCommentUID($comment->uid);?>
		<li itemscope itemtype="http://schema.org/Comment" class="kboard-comments-item" data-username="<?php echo $comment->user_display?>" data-created="<?php echo $comment->created?>">
			<div class="comments-list-username" itemprop="author">
				<?php echo get_avatar($comment->user_uid, 24, '', $comment->user_display, array('class'=>'kboard-avatar'))?>
				<?php echo $comment->user_display?>
			</div>
			<div class="comments-list-create" itemprop="dateCreated"><?php echo date("Y-m-d H:i", strtotime($comment->created))?></div>
			<div class="comments-list-content" itemprop="description">
				<?php echo nl2br($comment->content)?>
			</div>
			
			<?php if($commentBuilder->isWriter()):?>
			<div class="comments-list-controller">
				<div class="left">
					<?php if($comment->isEditor()):?>
					<button type="button" class="comments-button-action comments-button-delete" onclick="kboard_comments_delete('<?php echo $commentURL->getDeleteURL()?>');" title="<?php echo __('Delete', 'kboard-comments')?>"><?php echo __('Delete', 'kboard-comments')?></button>
					<?php else:?>
					<button type="button" class="comments-button-action comments-button-delete" onclick="kboard_comments_open_confirm('<?php echo $commentURL->getConfirmURL()?>');" title="<?php echo __('Delete', 'kboard-comments')?>"><?php echo __('Delete', 'kboard-comments')?></button>
					<?php endif?>
					<button type="button" class="comments-button-action comments-button-edit" onclick="kboard_comments_open_edit('<?php echo $commentURL->getEditURL()?>');" title="<?php echo __('Edit', 'kboard-comments')?>"><?php echo __('Edit', 'kboard-comments')?></button>
					<button type="button" class="comments-button-action comments-button-reply kboard-reply" onclick="kboard_comments_reply(this, '#kboard_comments_reply_form_<?php echo $comment->uid?>', '#kboard_comments_form');" title="<?php echo __('Reply', 'kboard-comments')?>"><?php echo __('Reply', 'kboard-comments')?></button>
				</div>
				<div class="right">
					<button type="button" class="comments-button-action comments-button-like" onclick="kboard_comment_like(this)" data-uid="<?php echo $comment->uid?>" title="<?php echo __('Like', 'kboard-comments')?>"><?php echo __('Like', 'kboard-comments')?> <span class="kboard-comment-like-count"><?php echo intval($comment->like)?></span></button>
					<button type="button" class="comments-button-action comments-button-unlike" onclick="kboard_comment_unlike(this)" data-uid="<?php echo $comment->uid?>" title="<?php echo __('Unlike', 'kboard-comments')?>"><?php echo __('Unlike', 'kboard-comments')?> <span class="kboard-comment-unlike-count"><?php echo intval($comment->unlike)?></span></button>
				</div>
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
				<input type="hidden" name="member_uid" value="<?php echo $member_uid?>">
			</form>
			<!-- 댓글 입력 폼 끝 -->
		</li>
		<?php endwhile?>
	</ul>
</div>
<div class="comments-list">
	<ul>
		<?php while($comment = $commentList->hasNext()): $commentURL->setCommentUID($comment->uid);?>
		<li itemscope itemtype="http://schema.org/Comment" class="kboard-comments-item" data-username="<?php echo $comment->user_display?>" data-created="<?php echo $comment->created?>">
			<div class="comments-list-username" itemprop="author">
				<?php echo $comment->getUserDisplay()?>
			</div>
			<div class="comments-list-create" itemprop="dateCreated"><?php echo date('Y-m-d H:i', strtotime($comment->created))?></div>
			<div class="comments-list-content" itemprop="description">
				<?php if($comment->isReader()):?>
					<?php echo wpautop($comment->content)?>
					
					<?php if(isset($comment->attach->image1)):?>
						<p class="comments-list-image1"><img src="<?php echo esc_url(site_url($comment->attach->image1[0]))?>" alt="<?php echo esc_attr($comment->attach->image1[1])?>"></p>
					<?php endif?>
					
					<?php if(isset($comment->attach->file1)):?>
						<p class="comments-list-file1"><?php echo __('Attachment', 'kboard-comments')?> : <a href="<?php echo $comment->attach->file1[2]?>" title="<?php echo __('Download', 'kboard-comments')?>"><?php echo $comment->attach->file1[1]?></a></p>
					<?php endif?>
				<?php else:?>
					<?php if($comment->remaining_time_for_reading):?>
						<div class="remaining_time_for_reading"><?php echo sprintf(__('You can read comments after %d minutes. <a href="%s">Login</a> and you can read it right away.', 'kboard-comments'), round($comment->remaining_time_for_reading/60), wp_login_url($_SERVER['REQUEST_URI']))?></div>
					<?php elseif($comment->login_is_required_for_reading):?>
						<div class="login_is_required_for_reading"><?php echo sprintf(__('You do not have permission to read this comment. Please <a href="%s">login</a>.', 'kboard-comments'), wp_login_url($_SERVER['REQUEST_URI']))?></div>
					<?php else:?>
						<div class="you_do_not_have_permission"><?php echo __('You do not have permission to read this comment.', 'kboard-comments')?></div>
					<?php endif?>
				<?php endif?>
			</div>
			
			<div class="comments-list-controller">
				<?php if($commentBuilder->isWriter()):?>
				<div class="left">
					<?php if($comment->isEditor()):?>
					<button type="button" class="comments-button-action comments-button-delete" onclick="kboard_comments_delete('<?php echo $commentURL->getDeleteURL()?>');" title="<?php echo __('Delete', 'kboard-comments')?>"><?php echo __('Delete', 'kboard-comments')?></button>
					<button type="button" class="comments-button-action comments-button-edit" onclick="kboard_comments_open_edit('<?php echo $commentURL->getEditURL()?>');" title="<?php echo __('Edit', 'kboard-comments')?>"><?php echo __('Edit', 'kboard-comments')?></button>
					<?php elseif($comment->password && !is_user_logged_in()):?>
					<button type="button" class="comments-button-action comments-button-delete" onclick="kboard_comments_open_confirm('<?php echo $commentURL->getConfirmURL()?>');" title="<?php echo __('Delete', 'kboard-comments')?>"><?php echo __('Delete', 'kboard-comments')?></button>
					<button type="button" class="comments-button-action comments-button-edit" onclick="kboard_comments_open_edit('<?php echo $commentURL->getEditURL()?>');" title="<?php echo __('Edit', 'kboard-comments')?>"><?php echo __('Edit', 'kboard-comments')?></button>
					<?php endif?>
					<button type="button" class="comments-button-action comments-button-reply kboard-reply" onclick="kboard_comments_reply(this, '#kboard-comment-reply-form-<?php echo $comment->uid?>', '#kboard-comments-form-<?php echo $content_uid?>', '<?php echo $content_uid?>');" title="<?php echo __('Reply', 'kboard-comments')?>"><?php echo __('Reply', 'kboard-comments')?></button>
				</div>
				<?php endif?>
				
				<div class="right">
					<button type="button" class="comments-button-action comments-button-like" onclick="kboard_comment_like(this)" data-uid="<?php echo $comment->uid?>" title="<?php echo __('Like', 'kboard-comments')?>"><?php echo __('Like', 'kboard-comments')?> <span class="kboard-comment-like-count"><?php echo intval($comment->like)?></span></button>
					<button type="button" class="comments-button-action comments-button-unlike" onclick="kboard_comment_unlike(this)" data-uid="<?php echo $comment->uid?>" title="<?php echo __('Unlike', 'kboard-comments')?>"><?php echo __('Unlike', 'kboard-comments')?> <span class="kboard-comment-unlike-count"><?php echo intval($comment->unlike)?></span></button>
				</div>
			</div>
			
			<hr>
			
			<!-- 답글 리스트 시작 -->
			<?php $commentBuilder->buildTreeList('list-template.php', $comment->uid, $depth+1)?>
			<!-- 답글 리스트 끝 -->
			
			<!-- 댓글 입력 폼 시작 -->
			<form id="kboard-comment-reply-form-<?php echo $comment->uid?>" method="post" action="<?php echo $commentURL->getInsertURL()?>" class="comments-reply-form" enctype="multipart/form-data" onsubmit="return kboard_comments_execute(this);">
				<input type="hidden" name="content_uid" value="<?php echo $comment->content_uid?>">
				<input type="hidden" name="parent_uid" value="<?php echo $comment->uid?>">
				<input type="hidden" name="member_uid" value="<?php echo $member_uid?>">
			</form>
			<!-- 댓글 입력 폼 끝 -->
		</li>
		<?php endwhile?>
	</ul>
</div>
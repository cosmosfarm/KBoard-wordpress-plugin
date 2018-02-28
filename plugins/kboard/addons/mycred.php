<?php
add_filter('mycred_setup_hooks', 'kboard_document_mycred_setup_hook');
function kboard_document_mycred_setup_hook($installed){
	$installed['kboard_document'] = array(
			'title'       => __('KBoard 게시판 포인트', 'kboard'),
			'description' => __('KBoard 게시판의 활동 포인트를 관리합니다.', 'kboard'),
			'callback'    => array('myCRED_KBoard_Document')
	);
	return $installed;
}

add_filter('mycred_setup_hooks', 'kboard_comments_mycred_setup_hook');
function kboard_comments_mycred_setup_hook($installed){
	$installed['kboard_comments'] = array(
			'title'       => __('KBoard 댓글 포인트', 'kboard'),
			'description' => __('KBoard 댓글 포인트를 관리합니다.', 'kboard'),
			'callback'    => array('myCRED_KBoard_Comments')
	);
	return $installed;
}

add_action('mycred_load_hooks', 'kboard_document_mycred_load_hook');
function kboard_document_mycred_load_hook(){
	class myCRED_KBoard_Document extends myCRED_Hook {
		function __construct($hook_prefs, $type){
			parent::__construct(array(
					'id'       => 'kboard_document',
					'defaults' => array(
							'insert' => array('creds'=>2, 'log'=>'KBoard 게시글 작성'),
							'delete' => array('creds'=>-2, 'log'=>'KBoard 게시글 삭제'),
					)
			), $hook_prefs, $type);
		}
		
		public function run(){
			if($this->prefs['insert']['creds'] != 0){
				add_action('kboard_document_insert', array($this, 'kboard_document_insert'), 10, 4);
			}
			if($this->prefs['delete']['creds'] != 0){
				add_action('kboard_document_delete', array($this, 'kboard_document_delete'), 10, 4);
			}
		}
		
		public function kboard_document_insert($content_uid, $board_id, $content, $board){
			$content = new KBContent();
			$content->initWithUID($content_uid);
			if($content->member_uid){
				$point = intval(get_user_meta($content->member_uid, 'kboard_document_mycred_point', true));
				update_user_meta($content->member_uid, 'kboard_document_mycred_point', $point + $this->prefs['insert']['creds']);
				
				$this->core->add_creds('kboard_document_insert', $content->member_uid, $this->prefs['insert']['creds'], $this->prefs['insert']['log'], $content_uid, 'kboard_document', $this->mycred_type);
			}
		}
		
		public function kboard_document_delete($content_uid, $board_id, $content, $board){
			$content = new KBContent();
			$content->initWithUID($content_uid);
			if($content->member_uid){
				$point = intval(get_user_meta($content->member_uid, 'kboard_document_mycred_point', true));
				update_user_meta($content->member_uid, 'kboard_document_mycred_point', $point + $this->prefs['delete']['creds']);
				
				$this->core->add_creds('kboard_document_delete', $content->member_uid, $this->prefs['delete']['creds'], $this->prefs['delete']['log'], $content_uid, 'kboard_document', $this->mycred_type);
			}
		}
		
		public function preferences(){
			$prefs = $this->prefs;
			?>
			<ol>
				<li>
					<label class="subheader" for="<?php echo $this->field_id(array('insert', 'creds'))?>"><?php _e('신규 게시글 작성', 'kboard')?></label>
					<input type="text" name="<?php echo $this->field_name(array('insert', 'creds'))?>" id="<?php echo $this->field_id(array('insert', 'creds'))?>" value="<?php echo $this->core->format_number($prefs['insert']['creds'])?>" size="8">
				</li>
				<li>
					<label for="<?php echo $this->field_id(array('insert', 'log'))?>"><?php _e('로그 템플릿', 'kboard')?></label>
					<input type="text" name="<?php echo $this->field_name(array('insert', 'log'))?>" id="<?php echo $this->field_id(array('insert', 'log'))?>" value="<?php echo esc_attr($prefs['insert']['log'])?>" class="long">
				</li>
				<li class="empty">&nbsp;</li>
			</ol>
			<ol>
				<li>
					<label class="subheader" for="<?php echo $this->field_id(array('delete', 'log'))?>"><?php _e('기존 게시글 삭제', 'kboard')?></label>
					<input type="text" name="<?php echo $this->field_name(array('delete', 'creds'))?>" id="<?php echo $this->field_id(array('delete', 'creds'))?>" value="<?php echo $this->core->format_number($prefs['delete']['creds'])?>" size="8">
				</li>
				<li>
					<label for="<?php echo $this->field_id(array('delete', 'log'))?>"><?php _e('로그 템플릿', 'kboard')?></label>
					<input type="text" name="<?php echo $this->field_name(array('delete', 'log'))?>" id="<?php echo $this->field_id(array('delete', 'log'))?>" value="<?php echo esc_attr($prefs['delete']['log'])?>" class="long">
				</li>
			</ol>
			<?php
		}
	}
}

add_action('mycred_load_hooks', 'kboard_comments_mycred_load_hook');
function kboard_comments_mycred_load_hook(){
	class myCRED_KBoard_Comments extends myCRED_Hook {
		function __construct($hook_prefs, $type){
			parent::__construct(array(
					'id'       => 'kboard_comments',
					'defaults' => array(
							'insert' => array('creds'=>1, 'log'=>'KBoard 댓글 작성'),
							'delete' => array('creds'=>-1, 'log'=>'KBoard 댓글 삭제'),
					)
			), $hook_prefs, $type);
		}
		
		public function run(){
			if($this->prefs['insert']['creds'] != 0){
				add_action('kboard_comments_insert', array($this, 'kboard_comments_insert'), 10, 3);
			}
			if($this->prefs['delete']['creds'] != 0){
				add_action('kboard_comments_delete', array($this, 'kboard_comments_delete'), 10, 3);
			}
		}
		
		public function kboard_comments_insert($comment_uid, $content_uid, $board){
			$comment = new KBComment();
			$comment->initWithUID($comment_uid);
			if($comment->user_uid){
				$point = intval(get_user_meta($comment->user_uid, 'kboard_comments_mycred_point', true));
				update_user_meta($comment->user_uid, 'kboard_comments_mycred_point', $point + $this->prefs['insert']['creds']);
				
				$this->core->add_creds('kboard_comments_insert', $comment->user_uid, $this->prefs['insert']['creds'], $this->prefs['insert']['log'], $comment_uid, 'kboard_comments', $this->mycred_type);
			}
		}
		
		public function kboard_comments_delete($comment_uid, $content_uid, $board){
			$comment = new KBComment();
			$comment->initWithUID($comment_uid);
			if($comment->user_uid){
				$point = intval(get_user_meta($comment->user_uid, 'kboard_comments_mycred_point', true));
				update_user_meta($comment->user_uid, 'kboard_comments_mycred_point', $point + $this->prefs['delete']['creds']);
				
				$this->core->add_creds('kboard_comments_delete', $comment->user_uid, $this->prefs['delete']['creds'], $this->prefs['delete']['log'], $comment_uid, 'kboard_comments', $this->mycred_type);
			}
		}
		
		public function preferences(){
			$prefs = $this->prefs;
			?>
			<ol>
				<li>
					<label class="subheader" for="<?php echo $this->field_id(array('insert', 'creds'))?>"><?php _e('신규 댓글 작성', 'kboard')?></label>
					<input type="text" name="<?php echo $this->field_name(array('insert', 'creds'))?>" id="<?php echo $this->field_id(array('insert', 'creds'))?>" value="<?php echo $this->core->format_number($prefs['insert']['creds'])?>" size="8">
				</li>
				<li>
					<label for="<?php echo $this->field_id(array('insert', 'log'))?>"><?php _e('로그 템플릿', 'kboard')?></label>
					<input type="text" name="<?php echo $this->field_name(array('insert', 'log'))?>" id="<?php echo $this->field_id(array('insert', 'log'))?>" value="<?php echo esc_attr($prefs['insert']['log'])?>" class="long">
				</li>
				<li class="empty">&nbsp;</li>
			</ol>
			<ol>
				<li>
					<label class="subheader" for="<?php echo $this->field_id(array('delete', 'log'))?>"><?php _e('기존 댓글 삭제', 'kboard')?></label>
					<input type="text" name="<?php echo $this->field_name(array('delete', 'creds'))?>" id="<?php echo $this->field_id(array('delete', 'creds'))?>" value="<?php echo $this->core->format_number($prefs['delete']['creds'])?>" size="8">
				</li>
				<li>
					<label for="<?php echo $this->field_id(array('delete', 'log'))?>"><?php _e('로그 템플릿', 'kboard')?></label>
					<input type="text" name="<?php echo $this->field_name(array('delete', 'log'))?>" id="<?php echo $this->field_id(array('delete', 'log'))?>" value="<?php echo esc_attr($prefs['delete']['log'])?>" class="long">
				</li>
			</ol>
			<?php
		}
	}
}
?>
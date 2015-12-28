<?php
if(defined('myCRED_VERSION') && class_exists('myCRED_Hook')){
	
	function kboard_document_myCRED_Hook($installed){
		$installed['kboard_document'] = array(
				'title'       => __('KBoard 게시판 포인트', 'kboard'),
				'description' => __('KBoard 게시판의 활동 포인트를 관리합니다.', 'kboard'),
				'callback'    => array('myCRED_KBoard_Document')
		);
		return $installed;
	}
	add_filter('mycred_setup_hooks', 'kboard_document_myCRED_Hook');
	
	if(!class_exists('myCRED_KBoard_Document')){
		class myCRED_KBoard_Document extends myCRED_Hook {
			
			function __construct($hook_prefs, $type = 'mycred_default'){
				parent::__construct( array(
						'id'       => 'kboard_document',
						'defaults' => array(
								'insert'=>array(
										'creds'  => 2,
										'log'    => 'KBoard 게시글 작성'
								),
								'delete'=>array(
										'creds'  => -2,
										'log'    => 'KBoard 게시글 삭제'
								)
						)
				), $hook_prefs, $type);
			}
			
			public function run(){
				if($this->prefs['insert']['creds'] != 0){
					add_action('kboard_document_insert', array($this, 'kboard_document_insert'), 10, 2);
				}
				if($this->prefs['delete']['creds'] != 0){
					add_action('kboard_document_delete', array($this, 'kboard_document_delete'), 10, 2);
				}
			}
			
			public function kboard_document_insert($content_uid, $board_id){
				$content = new KBContent();
				$content->initWithUID($content_uid);
				if($content->member_uid){
					$this->core->add_creds(
							'kboard_document_insert',
							$content->member_uid,
							$this->prefs['insert']['creds'],
							$this->prefs['insert']['log'],
							0,
							'',
							$this->mycred_type
					);
				}
			}
			
			public function kboard_document_delete($content_uid, $board_id){
				$content = new KBContent();
				$content->initWithUID($content_uid);
				if($content->member_uid){
					$this->core->add_creds(
							'kboard_document_delete',
							$content->member_uid,
							$this->prefs['delete']['creds'],
							$this->prefs['delete']['log'],
							0,
							'',
							$this->mycred_type
					);
				}
			}
			
			public function preferences(){
				$prefs = $this->prefs;
				?>
				<label class="subheader" for="<?php echo $this->field_id( 'creds' ); ?>"><?php _e('신규 게시글 작성', 'kboard'); ?></label>
				<ol>
					<li>
						<div class="h2"><input type="text" name="<?php echo $this->field_name('creds'); ?>" id="<?php echo $this->field_id('creds'); ?>" value="<?php echo $this->core->format_number( $prefs['insert']['creds'] ); ?>" size="8"></div>
					</li>
					<li class="empty">&nbsp;</li>
					<li>
						<label for="<?php echo $this->field_id( 'log' ); ?>"><?php _e('로그 템플릿', 'kboard'); ?></label>
						<div class="h2"><input type="text" name="<?php echo $this->field_name('log'); ?>" id="<?php echo $this->field_id('log'); ?>" value="<?php echo esc_attr( $prefs['insert']['log'] ); ?>" class="long"></div>
					</li>
				</ol>
				<label class="subheader" for="<?php echo $this->field_id( 'creds' ); ?>"><?php _e('기존 게시글 삭제', 'kboard'); ?></label>
				<ol>
					<li>
						<div class="h2"><input type="text" name="<?php echo $this->field_name('creds'); ?>" id="<?php echo $this->field_id('creds'); ?>" value="<?php echo $this->core->format_number( $prefs['delete']['creds'] ); ?>" size="8"></div>
					</li>
					<li class="empty">&nbsp;</li>
					<li>
						<label for="<?php echo $this->field_id( 'log' ); ?>"><?php _e('로그 템플릿', 'kboard'); ?></label>
						<div class="h2"><input type="text" name="<?php echo $this->field_name('log'); ?>" id="<?php echo $this->field_id('log'); ?>" value="<?php echo esc_attr( $prefs['delete']['log'] ); ?>" class="long"></div>
					</li>
				</ol>
				<?php
			}
		}
	}
	
	if(defined('KBOARD_COMMNETS_VERSION')){
		
		function kboard_comments_myCRED_Hook($installed){
			$installed['kboard_comments'] = array(
					'title'       => __('KBoard 댓글 포인트', 'kboard'),
					'description' => __('KBoard 댓글 포인트를 관리합니다.', 'kboard'),
					'callback'    => array('myCRED_KBoard_Comments')
			);
			return $installed;
		}
		add_filter('mycred_setup_hooks', 'kboard_comments_myCRED_Hook');
		
		if(!class_exists('myCRED_KBoard_Comments')){
			class myCRED_KBoard_Comments extends myCRED_Hook {
		
				function __construct($hook_prefs, $type = 'mycred_default'){
					parent::__construct( array(
							'id'       => 'kboard_comments',
							'defaults' => array(
									'insert'=>array(
											'creds'  => 1,
											'log'    => 'KBoard 댓글 작성'
									),
									'delete'=>array(
											'creds'  => -1,
											'log'    => 'KBoard 댓글 삭제'
									)
							)
					), $hook_prefs, $type);
				}
		
				public function run(){
					if($this->prefs['insert']['creds'] != 0){
						add_action('kboard_comments_insert', array($this, 'kboard_comments_insert'), 10, 2);
					}
					if($this->prefs['delete']['creds'] != 0){
						add_action('kboard_comments_delete', array($this, 'kboard_comments_delete'), 10, 2);
					}
				}
		
				public function kboard_comments_insert($comment_uid, $board_id){
					$comment = new KBComment();
					$comment->initWithUID($comment_uid);
					if($comment->user_uid){
						$this->core->add_creds(
								'kboard_comments_insert',
								$comment->user_uid,
								$this->prefs['insert']['creds'],
								$this->prefs['insert']['log'],
								0,
								'',
								$this->mycred_type
						);
					}
				}
		
				public function kboard_comments_delete($comment_uid, $board_id){
					$comment = new KBComment();
					$comment->initWithUID($comment_uid);
					if($comment->user_uid){
						$this->core->add_creds(
								'kboard_comments_delete',
								$comment->user_uid,
								$this->prefs['delete']['creds'],
								$this->prefs['delete']['log'],
								0,
								'',
								$this->mycred_type
						);
					}
				}
		
				public function preferences(){
					$prefs = $this->prefs;
					?>
					<label class="subheader" for="<?php echo $this->field_id( 'creds' ); ?>"><?php _e('신규 댓글 작성', 'kboard'); ?></label>
					<ol>
						<li>
							<div class="h2"><input type="text" name="<?php echo $this->field_name('creds'); ?>" id="<?php echo $this->field_id('creds'); ?>" value="<?php echo $this->core->format_number( $prefs['insert']['creds'] ); ?>" size="8"></div>
						</li>
						<li class="empty">&nbsp;</li>
						<li>
							<label for="<?php echo $this->field_id( 'log' ); ?>"><?php _e('로그 템플릿', 'kboard'); ?></label>
							<div class="h2"><input type="text" name="<?php echo $this->field_name('log'); ?>" id="<?php echo $this->field_id('log'); ?>" value="<?php echo esc_attr( $prefs['insert']['log'] ); ?>" class="long"></div>
						</li>
					</ol>
					<label class="subheader" for="<?php echo $this->field_id( 'creds' ); ?>"><?php _e('기존 댓글 삭제', 'kboard'); ?></label>
					<ol>
						<li>
							<div class="h2"><input type="text" name="<?php echo $this->field_name('creds'); ?>" id="<?php echo $this->field_id('creds'); ?>" value="<?php echo $this->core->format_number( $prefs['delete']['creds'] ); ?>" size="8"></div>
						</li>
						<li class="empty">&nbsp;</li>
						<li>
							<label for="<?php echo $this->field_id( 'log' ); ?>"><?php _e('로그 템플릿', 'kboard'); ?></label>
							<div class="h2"><input type="text" name="<?php echo $this->field_name('log'); ?>" id="<?php echo $this->field_id('log'); ?>" value="<?php echo esc_attr( $prefs['delete']['log'] ); ?>" class="long"></div>
						</li>
					</ol>
					<?php
				}
			}
		}
	}
}
?>
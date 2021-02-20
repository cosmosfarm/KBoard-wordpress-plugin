<?php
/**
 * KBoard 워드프레스 게시판 댓글 빌더
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentsBuilder {
	
	var $board;
	var $board_id;
	var $content_uid;
	var $skin;
	var $skin_name;
	var $permission_comment_write;
	
	public function __construct(){
		global $kboard_comment_builder;
		$kboard_comment_builder = $this;
		
		$this->setSkin('default');
	}
	
	/**
	 * 스킨을 지정한다.
	 * @param string $skin_name
	 * @return KBCommentsBuilder
	 */
	public function setSkin($skin_name){
		$this->skin = KBCommentSkin::getInstance();
		$this->skin_name = $skin_name;
		return $this;
	}
	
	/**
	 * 댓글창 화면을 생성한다.
	 * @return string
	 */
	public function create(){
		if(!$this->content_uid) return 'KBoard 댓글 알림 :: content_uid=null, content_uid값은 필수 입니다.';
		
		$current_user = wp_get_current_user();
		$commentList = new KBCommentList($this->content_uid);
		$commentList->board = $this->board;
		
		$url = new KBUrl();
		$url->setBoard($this->board);
		$comment_url = new KBCommentUrl();
		$comment_url->setBoard($this->board);
		
		$vars = array(
			'content_uid' => $this->content_uid,
			'commentList' => $commentList,
			'temporary' => kboard_comments_get_temporary(),
			'url' => $url,
			'commentURL' => $comment_url,
			'member_uid' => $current_user->ID,
			'member_display' => $current_user->display_name,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $this->board,
			'commentBuilder' => $this,
		);
		
		do_action('kboard_comments_skin_header', $this);
		
		echo $this->skin->load($this->skin_name, 'list.php', $vars);
		
		do_action('kboard_comments_skin_footer', $this);
	}
	
	/**
	 * 댓글 리스트 트리를 생성한다.
	 * @param string $template
	 * @param int $parent_uid
	 * @param int $depth
	 */
	public function buildTreeList($template, $parent_uid='', $depth=0){
		
		$current_user = wp_get_current_user();
		$commentList = new KBCommentList();
		$commentList->board = $this->board;
		
		if($parent_uid){
			$commentList->initWithParentUID($parent_uid);
		}
		else{
			$commentList->initWithUID($this->content_uid);
		}
		
		$url = new KBUrl();
		$url->setBoard($this->board);
		$comment_url = new KBCommentUrl();
		$comment_url->setBoard($this->board);
		
		$vars = array(
			'content_uid' => $this->content_uid,
			'commentList' => $commentList,
			'depth' => $depth,
			'url' => $url,
			'commentURL' => $comment_url,
			'member_uid' => $current_user->ID,
			'member_display' => $current_user->display_name,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $this->board,
			'commentBuilder' => $this,
		);
		
		echo $this->skin->load($this->skin_name, $template, $vars);
	}
	
	/**
	 * 댓글 쓰기 권한이 있는 사용자인지 확인한다.
	 * @return boolean
	 */
	public function isWriter(){
		$is_writer = false;
		
		if(!$this->permission_comment_write){
			$is_writer = true;
		}
		else if(is_user_logged_in()){
			if($this->permission_comment_write == '1'){
				$is_writer = true;
			}
			else if($this->board->isAdmin()){
				$is_writer = true;
			}
			else if($this->permission_comment_write == 'roles'){
				if(array_intersect($this->board->getCommentRoles(), kboard_current_user_roles())){
					$is_writer = true;
				}
			}
		}
		return apply_filters('kboard_comments_is_writer', $is_writer, $this);
	}
}
<?php
/**
 * KBoard 워드프레스 게시판 댓글 빌더
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentsBuilder {
	
	var $board;
	var $board_id;
	var $content_uid;
	var $skin;
	var $skin_path;
	var $permission_comment_write;
	
	public function __construct(){
		$this->setSkin('default');
	}
	
	/**
	 * 스킨을 지정한다.
	 * @param string $skin
	 * @return KBCommentsBuilder
	 */
	public function setSkin($skin){
		$this->skin = $skin;
		$this->skin_path = KBOARD_COMMENTS_URL_PATH . "/skin/{$skin}";
		return $this;
	}
	
	/**
	 * 댓글창 화면을 생성한다.
	 * @return string
	 */
	public function create(){
		$board = $this->board;
		$content_uid = $this->content_uid;
		$skin_path = $this->skin_path;
		
		if(!$this->content_uid) return 'KBoard 댓글 알림 :: content_uid=null, content_uid값은 필수 입니다.';
		
		$url = new KBUrl();
		$commentURL = new KBCommentUrl();
		$commentList = new KBCommentList($this->content_uid);
		$commentBuilder = $this;
		
		$current_user = wp_get_current_user();
		$member_uid = $current_user->ID;
		$member_display = $current_user->display_name;
		
		include KBOARD_COMMENTS_DIR_PATH . "/skin/{$this->skin}/list.php";
	}
	
	/**
	 * 댓글 리스트 트리를 생성한다.
	 * @param string $template
	 * @param int $parent_uid
	 */
	public function buildTreeList($template, $parent_uid=''){
		$board = $this->board;
		$content_uid = $this->content_uid;
		$skin_path = $this->skin_path;
		
		$url = new KBUrl();
		$commentURL = new KBCommentUrl();
		$commentList = new KBCommentList();
		$commentBuilder = $this;
		
		$current_user = wp_get_current_user();
		$member_uid = $current_user->ID;
		$member_display = $current_user->display_name;
		
		if($parent_uid) $commentList->initWithParentUID($parent_uid);
		else $commentList->initWithUID($this->content_uid);
		
		include KBOARD_COMMENTS_DIR_PATH . "/skin/{$this->skin}/{$template}";
	}
	
	/**
	 * 댓글 쓰기 권한이 있는 사용자인지 확인한다.
	 * @return boolean
	 */
	public function isWriter(){
		if(!$this->permission_comment_write){
			return true;
		}
		else if(is_user_logged_in()){
			if($this->permission_comment_write=='1'){
				return true;
			}
			else if($this->permission_comment_write=='roles'){
				$current_user = wp_get_current_user();
				if(isset($current_user->roles) && array_intersect($this->board->getCommentRoles(), $current_user->roles)){
					return true;
				}
			}
		}
		return false;
	}
}
?>
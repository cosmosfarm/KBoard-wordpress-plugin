<?php
/**
 * KBoard 워드프레스 게시판 댓글 빌더
 * @author www.cosmosfarm.com
 */
class CommentsBuilder {
	
	var $content_uid;
	var $skin;
	var $skin_path;
	var $userdata;
	
	public function __construct(){
		global $user_ID;
		$this->userdata = get_userdata($user_ID);
		$this->setSkin('default');
	}
	
	public function setSkin($skin){
		$this->skin = $skin;
		$this->skin_path = KBOARD_COMMENTS_URL_PATH . "/skin/$skin";
		return $this;
	}
	
	public function create(){
		global $user_ID;
		$userdata = get_userdata($user_ID);
		$content_uid = $this->content_uid;
		$skin_path = $this->skin_path;
		
		if(!$this->content_uid) return 'KBoard 댓글 알림 :: content_uid=null, content_uid값은 필수 입니다.';
		
		$commentList = new CommentList($this->content_uid);
		include_once KBOARD_COMMENTS_DIR_PATH . "/skin/$this->skin/list.php";
	}
}
?>
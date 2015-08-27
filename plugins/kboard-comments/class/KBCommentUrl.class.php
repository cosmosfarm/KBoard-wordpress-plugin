<?php
/**
 * KBoard 댓글 URL
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentUrl {
	
	var $comment_uid;
	
	public function __construct($comment_uid=''){
		if($comment_uid) $this->setCommentUID($comment_uid);
	}
	
	/**
	 * 댓글 UID를 입력한다.
	 * @param string $comment_uid
	 */
	public function setCommentUID($comment_uid){
		$this->comment_uid = intval($comment_uid);
	}
	
	/**
	 * 댓글 입력 URL
	 * @return string
	 */
	public function getInsertURL(){
		return site_url("?action=kboard_comment_insert");
		//return plugins_url("/kboard-comments/execute/insert.php");
	}
	
	/**
	 * 댓글 삭제 URL
	 * @return string
	 */
	public function getDeleteURL(){
		return site_url("?action=kboard_comment_delete&uid={$this->comment_uid}");
		//return plugins_url("/kboard-comments/execute/delete.php?uid={$this->comment_uid}");
	}
	
	/**
	 * 댓글 비밀번호 확인 URL
	 * @return string
	 */
	public function getConfirmURL(){
		return site_url("?action=kboard_comment_confirm&uid={$this->comment_uid}");
	}
}
?>
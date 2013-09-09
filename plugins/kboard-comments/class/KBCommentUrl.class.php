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
		$this->comment_uid = $comment_uid;
	}
	
	/**
	 * 댓글 입력 URL
	 * @return string
	 */
	public function getInsertURL(){
		return plugins_url().'/kboard-comments/execute/insert.php';
	}
	
	/**
	 * 댓글 삭제 URL
	 * @return string
	 */
	public function getDeleteURL(){
		return plugins_url().'/kboard-comments/execute/delete.php?uid='.$this->comment_uid;
	}
	
	/**
	 * 댓글 비밀번호 확인 URL
	 * @return string
	 */
	public function getConfirmURL(){
		return plugins_url().'/kboard-comments/execute/confirm.php?uid='.$this->comment_uid;
	}
}
?>
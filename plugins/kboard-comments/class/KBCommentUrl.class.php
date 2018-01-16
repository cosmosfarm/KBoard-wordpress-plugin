<?php
/**
 * KBoard 댓글 URL
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentUrl {
	
	var $comment_uid;
	var $board;
	
	public function __construct($comment_uid=''){
		if($comment_uid) $this->setCommentUID($comment_uid);
	}
	
	/**
	 * 댓글 UID를 입력한다.
	 * @param string $comment_uid
	 * @return KBCommentUrl
	 */
	public function setCommentUID($comment_uid){
		$this->comment_uid = intval($comment_uid);
		return $this;
	}
	
	/**
	 * 게시판을 입력 받는다.
	 * @param int|KBoard $board
	 */
	public function setBoard($board){
		if(is_numeric($board)){
			$this->board = new KBoard($board);
		}
		else{
			$this->board = $board;
		}
	}
	
	/**
	 * 댓글 입력 실행 URL
	 * @return string
	 */
	public function getInsertURL(){
		return apply_filters('kboard_comments_url_insert', site_url("?action=kboard_comment_insert"), $this->board);
	}
	
	/**
	 * 댓글 삭제 실행 URL
	 * @return string
	 */
	public function getDeleteURL(){
		return apply_filters('kboard_comments_url_delete', site_url("?action=kboard_comment_delete&uid={$this->comment_uid}"), $this->comment_uid, $this->board);
	}
	
	/**
	 * 댓글 비밀번호 확인 페이지 URL
	 * @return string
	 */
	public function getConfirmURL(){
		return apply_filters('kboard_comments_url_confirm', site_url("?action=kboard_comment_confirm&uid={$this->comment_uid}"), $this->comment_uid, $this->board);
	}
	
	/**
	 * 댓글 수정 페이지 URL
	 * @return string
	 */
	public function getEditURL(){
		return apply_filters('kboard_comments_url_edit', site_url("?action=kboard_comment_edit&uid={$this->comment_uid}"), $this->comment_uid, $this->board);
	}
	
	/**
	 * 댓글 업데이트 실행 URL
	 * @return string
	 */
	public function getUpdateURL(){
		return apply_filters('kboard_comments_url_update', site_url("?action=kboard_comment_update&uid={$this->comment_uid}"), $this->comment_uid, $this->board);
	}
}
?>
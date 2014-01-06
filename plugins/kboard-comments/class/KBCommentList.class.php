<?php
/**
 * KBoard 워드프레스 게시판 댓글 리스트
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentList {
	
	var $total;
	var $userdata;
	var $content_uid;
	var $parent_uid;
	var $resource;
	var $row;
	var $order = 'ASC';
	
	public function __construct($content_uid=''){
		global $user_ID;
		$this->userdata = get_userdata($user_ID);
		if($content_uid) $this->setContentUID($content_uid);
	}
	
	/**
	 * 댓글 목록을 초기화 한다.
	 */
	public function init(){
		if($this->content_uid){
			$this->resource = kboard_query("SELECT * FROM `".KBOARD_DB_PREFIX."kboard_comments` WHERE `content_uid`='$this->content_uid' AND `parent_uid`<=0 ORDER BY `uid` $this->order");
		}
		else{
			$this->resource = kboard_query("SELECT * FROM `".KBOARD_DB_PREFIX."kboard_comments` WHERE 1 ORDER BY `uid` $this->order");
		}
		return $this->resource;
	}
	
	/**
	 * 고유번호로 댓글 목록을 초기화 한다.
	 * @param int $content_uid
	 */
	public function initWithUID($content_uid){
		$this->setContentUID($content_uid);
		$this->init();
	}
	
	/**
	 * 부모 고유번호로 초기화 한다.
	 * @param int $parent_uid
	 */
	public function initWithParentUID($parent_uid){
		$this->parent_uid = $parent_uid;
		$this->resource = kboard_query("SELECT * FROM `".KBOARD_DB_PREFIX."kboard_comments` WHERE `parent_uid`='$this->parent_uid' ORDER BY `uid` $this->order");
		$resource = kboard_query("SELECT COUNT(*) FROM `".KBOARD_DB_PREFIX."kboard_comments` WHERE `parent_uid`='$this->parent_uid'");
		list($this->total) = mysql_fetch_row($resource);
	}
	
	/**
	 * 게시물 고유번호를 입력받는다.
	 * @param int $content_uid
	 */
	public function setContentUID($content_uid){
		$this->content_uid = $content_uid;
	}
	
	/**
	 * 총 댓글 개수를 반환한다.
	 */
	public function getCount(){
		if(is_null($this->total)){
			if($this->content_uid){
				$resource = kboard_query("SELECT COUNT(*) FROM `".KBOARD_DB_PREFIX."kboard_comments` WHERE `content_uid`='$this->content_uid'");
				list($this->total) = mysql_fetch_row($resource);
			}
			else{
				$resource = kboard_query("SELECT COUNT(*) FROM `".KBOARD_DB_PREFIX."kboard_comments` WHERE 1");
				list($this->total) = mysql_fetch_row($resource);
			}
		}
		return intval($this->total);
	}
	
	/**
	 * 다음 댓글을 반환한다.
	 * @return Comment
	 */
	public function hasNext(){
		if(!$this->resource) $this->init();
		$this->row = mysql_fetch_object($this->resource);
		
		if($this->row){
			$comment = new KBComment();
			$comment->initWithRow($this->row);
			return $comment;
		}
		else{
			return $this->row;
		}
	}
	
	/**
	 * 댓글 고유번호를 입력받아 해당 댓글을 반환한다.
	 * @param int $uid
	 * @return Comment
	 */
	public function getComment($uid){
		$uid = intval($uid);
		$resource = kboard_query("SELECT * FROM `".KBOARD_DB_PREFIX."kboard_comments` WHERE `uid`='$uid' LIMIT 1");
		$row = mysql_fetch_object($resource);
		
		if($row){
			$comment = new KBComment();
			$comment->initWithRow($row);
			return $comment;
		}
		else{
			return $row;
		}
	}
	
	/**
	 * 댓글 정보를 입력한다.
	 * @param int $parent_uid
	 * @param int $user_uid
	 * @param string $user_display
	 * @param string $content
	 * @param string $password
	 */
	public function add($parent_uid, $user_uid, $user_display, $content, $password=''){
		$content_uid = intval($this->content_uid);
		$parent_uid = intval($parent_uid);
		$user_uid = intval($user_uid);
		$user_display = addslashes(kboard_xssfilter(kboard_htmlclear(trim($user_display))));
		$content = addslashes(kboard_xssfilter(trim($content)));
		$password = addslashes(kboard_xssfilter(kboard_htmlclear(trim($password))));
		
		$created = date("YmdHis", current_time('timestamp'));
		kboard_query("INSERT INTO `".KBOARD_DB_PREFIX."kboard_comments` (`content_uid`, `parent_uid`, `user_uid`, `user_display`, `content`, `created`, `password`) VALUE ('$content_uid', '$parent_uid', '$user_uid', '$user_display', '$content', '$created', '$password')");
		
		$insert_id = mysql_insert_id();
		if(!$insert_id) list($insert_id) = mysql_fetch_row(kboard_query("SELECT LAST_INSERT_ID()"));
		
		//댓글 입력 액션 훅 실행
		do_action('kboard_comments_insert', $insert_id);
		
		return $insert_id;
	}
	
	/**
	 * 댓글을 삭제한다.
	 * @param int $uid
	 */
	public function delete($uid){
		$uid = intval($uid);
		
		//댓글 삭제 액션 훅 실행
		do_action('kboard_comments_delete', $uid);
		
		kboard_query("DELETE FROM `".KBOARD_DB_PREFIX."kboard_comments` WHERE `uid`='$uid'");
	}
}
?>
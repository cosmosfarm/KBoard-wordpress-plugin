<?php
/**
 * KBoard 워드프레스 게시판 댓글 리스트
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class CommentList {
	
	var $total;
	var $userdata;
	var $content_uid;
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
			$this->resource = kboard_query("SELECT * FROM ".KBOARD_DB_PREFIX."kboard_comments WHERE content_uid=$this->content_uid ORDER BY uid $this->order");
		}
		else{
			$this->resource = kboard_query("SELECT * FROM ".KBOARD_DB_PREFIX."kboard_comments WHERE 1 ORDER BY uid $this->order");
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
				$this->total = @reset(mysql_fetch_row(kboard_query("SELECT COUNT(*) FROM ".KBOARD_DB_PREFIX."kboard_comments WHERE content_uid=$this->content_uid")));
			}
			else{
				$this->total = @reset(mysql_fetch_row(kboard_query("SELECT COUNT(*) FROM ".KBOARD_DB_PREFIX."kboard_comments WHERE 1")));
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
			$comment = new Comment();
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
	 * @return Comment|unknown
	 */
	public function getComment($uid){
		$row = mysql_fetch_object(kboard_query("SELECT * FROM ".KBOARD_DB_PREFIX."kboard_comments WHERE uid=$uid LIMIT 1"));
		
		if($row){
			$comment = new Comment();
			$comment->initWithRow($row);
			return $comment;
		}
		else{
			return $row;
		}
	}
	
	/**
	 * 댓글 정보를 입력한다.
	 * @param int $user_uid
	 * @param string $user_display
	 * @param string $content
	 * @param string $password
	 */
	public function add($user_uid, $user_display, $content, $password=''){
		$user_uid = intval($user_uid);
		$user_display = addslashes(kboard_htmlclear(trim($user_display)));
		$content = addslashes(kboard_xssfilter(trim($content)));
		$password = addslashes($password);
		
		$created = date("YmdHis", current_time('timestamp'));
		kboard_query("INSERT INTO ".KBOARD_DB_PREFIX."kboard_comments (content_uid, user_uid, user_display, content, created, password) VALUE ('$this->content_uid', '$user_uid', '$user_display', '$content', '$created', '$password')");
	}
	
	/**
	 * 댓글을 삭제한다.
	 * @param int $uid
	 */
	public function delete($uid){
		kboard_query("DELETE FROM ".KBOARD_DB_PREFIX."kboard_comments WHERE uid=$uid");
	}
}
?>
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
	
	public function init(){
		if($this->content_uid){
			$this->resource = mysql_query("SELECT * FROM kboard_comments WHERE content_uid=$this->content_uid ORDER BY uid $this->order");
		}
		else{
			$this->resource = mysql_query("SELECT * FROM kboard_comments WHERE 1 ORDER BY uid $this->order");
		}
		return $this->resource;
	}
	
	public function initWithUID($content_uid){
		$this->setContentUID($content_uid);
		$this->init();
	}
	
	public function setContentUID($content_uid){
		$this->content_uid = $content_uid;
	}

	public function getCount(){
		if(is_null($this->total)){
			if($this->content_uid){
				$this->total = @reset(mysql_fetch_row(mysql_query("SELECT COUNT(*) FROM kboard_comments WHERE content_uid=$this->content_uid")));
			}
			else{
				$this->total = @reset(mysql_fetch_row(mysql_query("SELECT COUNT(*) FROM kboard_comments WHERE 1")));
			}
		}
		return intval($this->total);
	}
	
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
	
	public function getComment($uid){
		$row = mysql_fetch_object(mysql_query("SELECT * FROM kboard_comments WHERE uid=$uid LIMIT 1"));
		
		if($row){
			$comment = new Comment();
			$comment->initWithRow($row);
			return $comment;
		}
		else{
			return $row;
		}
	}

	public function add($user_uid, $user_display, $content, $password=''){
		
		$user_display = addslashes(kboard_htmlclear(trim($user_display)));
		$content = addslashes(kboard_xssfilter(trim($content)));
		$password = addslashes($password);
		
		$created = date("YmdHis", current_time('timestamp'));
		mysql_query("INSERT INTO kboard_comments (content_uid, user_uid, user_display, content, created, password) VALUE ('$this->content_uid', '$user_uid', '$user_display', '$content', '$created', '$password')");
	}
	
	public function delete($uid){
		mysql_query("DELETE FROM kboard_comments WHERE uid=$uid");
	}
}
?>
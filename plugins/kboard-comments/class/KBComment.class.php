<?php
/**
 * KBoard 워드프레스 게시판 댓글
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBComment {
	
	var $row;
	
	public function __construct(){
		$this->row = new stdClass();
	}

	public function __get($name){
		if(isset($this->row->{$name})){
			if($name == 'content'){
				return apply_filters('kboard_comments_content', stripslashes($this->row->{$name}), $this->row->uid, $this->row->content_uid);
			}
			else{
				return stripslashes($this->row->{$name});
			}
		}
		else{
			return '';
		}
	}
	
	public function __set($name, $value){
		$this->row->{$name} = $value;
	}
	
	/**
	 * 댓글 고유번호를 입력받아 정보를 초기화한다.
	 * @param int $uid
	 * @return KBComment
	 */
	public function initWithUID($uid){
		global $wpdb;
		$uid = intval($uid);
		$this->row = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_comments` WHERE `uid`='$uid'");
		return $this;
	}
	
	/**
	 * 댓글 정보를 입력받아 초기화한다.
	 * @param object $comment
	 * @return KBComment
	 */
	public function initWithRow($comment){
		$this->row = $comment;
		return $this;
	}
	
	/**
	 * 관리 권한이 있는지 확인한다.
	 * @return boolean
	 */
	public function isEditor(){
		global $wpdb;
		$board_id = $wpdb->get_var("SELECT `board_id` FROM `{$wpdb->prefix}kboard_board_content` WHERE `uid`='{$this->content_uid}'");
		$board = new KBoard($board_id);
		
		if(is_user_logged_in() && $this->user_uid == get_current_user_id()){
			// 본인인 경우
			return true;
		}
		else if($board->isAdmin()){
			// 게시판 관리자 허용
			return true;
		}
		else{
			return false;
		}
	}
	
	/**
	 * 댓글 정보를 업데이트 한다.
	 */
	public function update(){
		global $wpdb;
		if($this->uid){
			foreach($this->row as $key=>$value){
				if($key == 'uid') continue;
				$value = esc_sql($value);
				$update[] = "`$key`='$value'";
			}
			$wpdb->query("UPDATE `{$wpdb->prefix}kboard_comments` SET ".implode(',', $update)." WHERE `uid`='$this->uid'");
		}
	}
}
?>
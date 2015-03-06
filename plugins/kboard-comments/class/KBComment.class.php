<?php
/**
 * KBoard 워드프레스 게시판 댓글
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBComment {
	
	var $userdata;
	var $row;
	
	public function __construct(){
		global $user_ID;
		$this->row = new stdClass();
		$this->userdata = get_userdata($user_ID);
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
	
	/**
	 * 댓글 고유번호를 입력받아 정보를 초기화한다.
	 * @param int $uid
	 * @return KBComment
	 */
	public function initWithUID($uid){
		global $wpdb;
		$uid = intval($uid);
		$this->row = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_comments` WHERE `uid`='$uid' LIMIT 1");
		return $this;
	}
	
	/**
	 * 댓글 정보를 입력받아 초기화 한다.
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
		
		if(isset($this->userdata->data->ID) && $this->user_uid == $this->userdata->data->ID){
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
}
?>
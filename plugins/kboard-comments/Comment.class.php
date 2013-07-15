<?php
/**
 * KBoard 워드프레스 게시판 댓글
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class Comment {
	
	var $userdata;
	var $row;
	
	public function __construct(){
		global $user_ID;
		$this->row = new stdClass();
		$this->userdata = get_userdata($user_ID);
	}

	public function __get($name){
		return stripslashes($this->row->{$name});
	}
	
	public function initWithRow($comment){
		$this->row = $comment;
		return $this;
	}
	
	public function isEditor(){
		if($this->user_uid == $this->userdata->data->ID && $this->userdata->data->ID){
			// 본인일경우 허용
			return true;
		}
		else if(@in_array('administrator' , $this->userdata->roles) || @in_array('editor', $this->userdata->roles)){
			// 최고관리자 허용
			return true;
		}
		else{
			return false;
		}
	}
}
?>
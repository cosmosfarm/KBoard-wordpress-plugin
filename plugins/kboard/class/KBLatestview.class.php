<?php
/**
 * KBoard 최신글 모아보기
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBLatestview {
	
	private $row;
	
	public function __construct($uid=''){
		$uid = intval($uid);
		if($uid){
			$this->initWithUID($uid);
		}
		else{
			$this->row = new stdClass();
		}
	}
	
	public function __get($name){
		return stripslashes($this->row->{$name});
	}
	
	public function __set($name, $value){
		$this->row->{$name} = $value;
	}
	
	/**
	 * 고유번호로 초기화 한다.
	 * @param int $uid
	 * @return Latestview
	 */
	public function initWithUID($uid){
		$uid = intval($uid);
		$resource = kboard_query("SELECT * FROM `".KBOARD_DB_PREFIX."kboard_board_latestview` WHERE `uid`='$uid'");
		$this->row = mysql_fetch_object($resource);
		return $this;
	}
	
	/**
	 * 값을 입력받으 초기화 한다.
	 * @param unknown $row
	 */
	public function initWithRow($row){
		$this->row = $row;
		return $this;
	}
	
	/**
	 * 모아보기를 생성한다.
	 */
	public function create(){
		$date = date("YmdHis", current_time('timestamp'));
		$result = kboard_query("INSERT INTO `".KBOARD_DB_PREFIX."kboard_board_latestview` (`name`, `skin`, `rpp`, `created`) VALUE ('', '', '0', '$date')");
		
		$insert_id = mysql_insert_id();
		if(!$insert_id) list($insert_id) = mysql_fetch_row(kboard_query("SELECT LAST_INSERT_ID()"));
		
		$this->uid = $insert_id;
		return $this->uid;
	}
	
	/**
	 * 모아보기 정보를 수정한다.
	 */
	public function update(){
		if($this->uid){
			foreach($this->row AS $key => $value){
				if($key != 'uid'){
					$value = addslashes($value);
					$data[] = "`$key`='$value'";
				}
			}
			if($data) kboard_query("UPDATE `".KBOARD_DB_PREFIX."kboard_board_latestview` SET ".implode(',', $data)." WHERE `uid`='$this->uid' LIMIT 1");
		}
	}
	
	/**
	 * 모아보기 정보를 삭제한다.
	 */
	public function delete(){
		if($this->uid){
			kboard_query("DELETE FROM `".KBOARD_DB_PREFIX."kboard_board_latestview` WHERE `uid`='$this->uid' LIMIT 1");
			kboard_query("DELETE FROM `".KBOARD_DB_PREFIX."kboard_board_latestview_link` WHERE `latestview_uid`='$this->uid'");
		}
	}
	
	/**
	 * 모아볼 게시판을 추가한다.
	 * @param int $board_id
	 */
	public function pushBoard($board_id){
		$board_id = intval($board_id);
		if($this->uid && !$this->isLinked($board_id)){
			kboard_query("INSERT INTO `".KBOARD_DB_PREFIX."kboard_board_latestview_link` (`latestview_uid`, `board_id`) VALUE ('$this->uid', '$board_id')");
		}
	}
	
	/**
	 * 게시판을 제거한다.
	 * @param int $board_id
	 */
	public function popBoard($board_id){
		$board_id = intval($board_id);
		if($this->uid){
			kboard_query("DELETE FROM `".KBOARD_DB_PREFIX."kboard_board_latestview_link` WHERE `latestview_uid`='$this->uid' AND `board_id`='$board_id' LIMIT 1");
		}
	}
	
	/**
	 * 모아볼 게시판들을 반환한다.
	 */
	public function getLinkedBoard(){
		$list = array();
		$resource = kboard_query("SELECT * FROM `".KBOARD_DB_PREFIX."kboard_board_latestview_link` WHERE `latestview_uid`='$this->uid'");
		while($row = mysql_fetch_object($resource)){
			$list[] = $row->board_id;
		}
		return $list;
	}
	
	/**
	 * 연결된 게시판인지 확인한다.
	 */
	public function isLinked($board_id){
		$board_id = intval($board_id);
		if($this->uid){
			list($count) = mysql_fetch_row(kboard_query("SELECT COUNT(*) FROM `".KBOARD_DB_PREFIX."kboard_board_latestview_link` WHERE `latestview_uid`='$this->uid' AND `board_id`='$board_id'"));
		}
		if(intval($count)) return true;
		else return false;
	}
}
?>
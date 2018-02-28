<?php
/**
 * KBoard 최신글 모아보기
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBLatestview {
	
	var $row;
	
	public function __construct($uid=''){
		$this->row = new stdClass();
		if($uid) $this->initWithUID($uid);
	}
	
	public function __get($name){
		if(isset($this->row->{$name})){
			if($name == 'sort' && !$this->row->{$name}) return 'newest';
			return stripslashes($this->row->{$name});
		}
		return '';
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
		global $wpdb;
		$uid = intval($uid);
		$this->row = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_board_latestview` WHERE `uid`='$uid'");
		return $this;
	}
	
	/**
	 * 값을 입력받으 초기화 한다.
	 * @param object $row
	 */
	public function initWithRow($row){
		$this->row = $row;
		return $this;
	}
	
	/**
	 * 모아보기를 생성한다.
	 */
	public function create(){
		global $wpdb;
		$date = date('YmdHis', current_time('timestamp'));
		$result = $wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_board_latestview` (`name`, `skin`, `rpp`, `sort`, `created`) VALUES ('', '', '0', '', '$date')");
		$this->uid = $wpdb->insert_id;
		return $this->uid;
	}
	
	/**
	 * 모아보기 정보를 수정한다.
	 */
	public function update(){
		global $wpdb;
		if($this->uid){
			foreach($this->row as $key=>$value){
				if($key != 'uid'){
					$key = sanitize_key($key);
					$value = esc_sql($value);
					$data[] = "`$key`='$value'";
				}
			}
			if($data) $wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_latestview` SET ".implode(',', $data)." WHERE `uid`='$this->uid'");
		}
	}
	
	/**
	 * 모아보기 정보를 삭제한다.
	 */
	public function delete(){
		global $wpdb;
		if($this->uid){
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_latestview` WHERE `uid`='$this->uid'");
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_latestview_link` WHERE `latestview_uid`='$this->uid'");
		}
	}
	
	/**
	 * 모아볼 게시판을 추가한다.
	 * @param int $board_id
	 */
	public function pushBoard($board_id){
		global $wpdb;
		$board_id = intval($board_id);
		if($this->uid && !$this->isLinked($board_id)){
			$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_board_latestview_link` (`latestview_uid`, `board_id`) VALUES ('$this->uid', '$board_id')");
		}
	}
	
	/**
	 * 게시판을 제거한다.
	 * @param int $board_id
	 */
	public function popBoard($board_id){
		global $wpdb;
		$board_id = intval($board_id);
		if($this->uid){
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_latestview_link` WHERE `latestview_uid`='$this->uid' AND `board_id`='$board_id'");
		}
	}
	
	/**
	 * 모아볼 게시판들을 반환한다.
	 */
	public function getLinkedBoard(){
		global $wpdb;
		if($this->uid){
			$result = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_latestview_link` WHERE `latestview_uid`='$this->uid'");
			foreach($result as $row){
				$list[] = $row->board_id;
			}
		}
		return isset($list)?$list:array();
	}
	
	/**
	 * 연결된 게시판인지 확인한다.
	 * @return boolean
	 */
	public function isLinked($board_id){
		global $wpdb;
		$board_id = intval($board_id);
		if($this->uid){
			$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_latestview_link` WHERE `latestview_uid`='$this->uid' AND `board_id`='$board_id'");
			if(intval($count)){
				return true;
			}
		}
		return false;
	}
}
?>
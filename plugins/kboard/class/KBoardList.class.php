<?php
/**
 * KBoard 게시판 리스트
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBoardList {
	
	var $resource;
	var $rpp = 10;
	var $page = 1;
	var $total;
	var $row;
	
	/**
	 * 게시판 리스트를 초기화한다.
	 * @return KBoardList
	 */
	public function init(){
		global $wpdb;
		$this->total = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_setting` WHERE 1");
		$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_setting` WHERE 1 ORDER BY `uid` DESC");
		return $this;
	}
	
	/**
	 * 게시판 이름을 검색해 리스트를 초기화한다.
	 * @param string $keyword
	 * @return KBoardList
	 */
	public function initWithKeyword($keyword=''){
		global $wpdb;
		if($keyword){
			$keyword = esc_sql($keyword);
			$where = "`board_name` LIKE '%$keyword%'";
		}
		else{
			$where = '1=1';
		}
		$this->total = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_setting` WHERE {$where}");
		$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_setting` WHERE {$where} ORDER BY `uid` DESC LIMIT ".($this->page-1)*$this->rpp.",$this->rpp");
		return $this;
	}
	
	/**
	 * 생성된 게시판 숫자를 반환한다.
	 * @return int
	 */
	public function getCount(){
		return $this->total;
	}
	
	/**
	 * 다음 게시판 정보를 불러온다.
	 * @return object
	 */
	public function hasNext(){
		if(!$this->resource) return '';
		$this->row = current($this->resource);
		if($this->row){
			next($this->resource);
			$board = new KBoard();
			$board->initWithRow($this->row);
			return $board;
		}
		else{
			unset($this->resource);
			return '';
		}
	}
	
	/**
	 * 관리자 페이지에서 게시판 보기 리스트를 반환한다.
	 * @return array
	 */
	public function getActiveAdmin(){
		global $wpdb;
		$results = $wpdb->get_results("SELECT `board_id` FROM `{$wpdb->prefix}kboard_board_meta` WHERE `key`='add_menu_page'");
		foreach($results as $row){
			$active[] = $row->board_id;
		}
		return isset($active) ? $active : array();
	}
}
?>
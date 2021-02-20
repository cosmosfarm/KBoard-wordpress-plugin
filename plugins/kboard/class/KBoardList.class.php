<?php
/**
 * KBoard 게시판 리스트
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
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
		$this->total = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_setting` WHERE 1=1");
		$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_setting` WHERE 1=1 ORDER BY `uid` DESC");
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
			$where = "`board_name` LIKE '%{$keyword}%'";
		}
		else{
			$where = '1=1';
		}
		$this->total = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_setting` WHERE {$where}");
		$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_setting` WHERE {$where} ORDER BY `uid` DESC LIMIT " . ($this->page-1)*$this->rpp . ",{$this->rpp}");
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
	 * 관리자 페이지에서 게시판 보기 목록을 반환한다.
	 * @return array
	 */
	public function getActiveAdmin(){
		global $wpdb;
		$list = array();
		$results = $wpdb->get_results("SELECT `board_id` FROM `{$wpdb->prefix}kboard_board_meta` WHERE `key`='add_menu_page'");
		foreach($results as $row){
			$list[] = $row->board_id;
		}
		return $list;
	}
	
	/**
	 * 우커머스 상품 탭에 표시 게시판 목록을 반환한다.
	 * @return array
	 */
	public function getWoocommerceProductTabsAdd(){
		global $wpdb;
		$list = array();
		$results = $wpdb->get_results("SELECT `{$wpdb->prefix}kboard_board_setting`.* FROM `{$wpdb->prefix}kboard_board_setting` LEFT JOIN `{$wpdb->prefix}kboard_board_meta` ON `{$wpdb->prefix}kboard_board_setting`.`uid`=`{$wpdb->prefix}kboard_board_meta`.`board_id` WHERE `{$wpdb->prefix}kboard_board_meta`.`key`='woocommerce_product_tabs_add'");
		foreach($results as $row){
			$list[] = (object) array(
				'board_id' => $row->uid,
				'board_name' => $row->board_name,
				'priority' => $wpdb->get_var("SELECT `value` FROM `{$wpdb->prefix}kboard_board_meta` WHERE `board_id`='{$row->uid}' AND `key`='woocommerce_product_tabs_priority'"),
			);
		}
		return $list;
	}
}
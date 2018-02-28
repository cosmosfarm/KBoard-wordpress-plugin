<?php
/**
 * KBoard 최신글 모아보기 리스트
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBLatestviewList {
	
	var $total;
	var $index;
	var $rpp = 10;
	var $page = 1;
	var $resource;
	var $row;
	
	/**
	 * 모아보기 리스트를 초기화 한다.
	 * @return LatestviewList
	 */
	public function init(){
		global $wpdb;
		$this->total = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_latestview` WHERE 1");
		$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_latestview` WHERE 1 ORDER BY `uid` DESC LIMIT ".($this->page-1)*$this->rpp.",$this->rpp");
		$this->index = $this->total;
		return $this;
	}
	
	/**
	 * 모아보기 리스트 이름을 검색해 리스트를 초기화한다.
	 * @param string $keyword
	 * @return KBLatestviewList
	 */
	public function initWithKeyword($keyword=''){
		global $wpdb;
		if($keyword){
			$keyword = esc_sql($keyword);
			$where = "`name` LIKE '%$keyword%'";
		}
		else{
			$where = '1=1';
		}
		$this->total = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_latestview` WHERE $where");
		$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_latestview` WHERE $where ORDER BY `uid` DESC LIMIT ".($this->page-1)*$this->rpp.",$this->rpp");
		return $this;
	}
	
	/**
	 * 리스트에서 다음 게시물을 반환한다.
	 * @return Latestview
	 */
	public function hasNext(){
		if(!$this->resource) $this->init();
		$this->row = current($this->resource);
		
		if($this->row){
			next($this->resource);
			$latestview = new KBLatestview();
			$latestview->initWithRow($this->row);
			return $latestview;
		}
		else{
			unset($this->resource);
			return '';
		}
	}
	
	/**
	 * 리스트의 현재 인덱스를 반환한다.
	 * @return int
	 */
	public function index(){
		return $this->index--;
	}
}
?>
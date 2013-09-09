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
	
	private $resource;
	private $row;
	
	/**
	 * 모아보기 리스트를 초기화 한다.
	 * @return LatestviewList
	 */
	public function init(){
		$resource = kboard_query("SELECT COUNT(*) FROM ".KBOARD_DB_PREFIX."kboard_board_latestview WHERE 1");
		list($this->total) = mysql_fetch_row($resource);
		$this->index = $this->total;
		
		$this->resource = kboard_query("SELECT * FROM ".KBOARD_DB_PREFIX."kboard_board_latestview WHERE 1 ORDER BY uid DESC LIMIT ".($this->page-1)*$this->rpp.",$this->rpp");
		
		return $this;
	}
	
	/**
	 * 리스트에서 다음 게시물을 반환한다.
	 * @return Latestview
	 */
	public function hasNext(){
		if(!$this->resource) $this->init();
		$this->row = mysql_fetch_object($this->resource);
		
		if($this->row){
			$latestview = new KBLatestview();
			$latestview->initWithRow($this->row);
			return $latestview;
		}
		else{
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
<?php
/**
 * KBoard 워드프레스 게시판 게시물 리스트
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBContentList {
	
	var $board_id;
	
	var $col_category1;
	var $col_category2;
	var $rpp = 10;
	var $page = 1;
	
	private $resource;
	private $resource_notice;
	private $row;
	
	var $total;
	var $index;
	
	public function __construct($board_id=''){
		if($board_id) $this->setBoardID($board_id);
	}
	
	/**
	 * 모든 게시판의 내용을 반환한다.
	 * @return resource
	 */
	public function init(){
		$where[] = 1;
		
		$this->total = reset(mysql_fetch_row(kboard_query("SELECT COUNT(*) FROM ".KBOARD_DB_PREFIX."kboard_board_content WHERE " . implode(" AND ", $where))));
		$this->resource = kboard_query("SELECT * FROM ".KBOARD_DB_PREFIX."kboard_board_content WHERE " . implode(" AND ", $where) . " ORDER BY date DESC LIMIT ".($this->page-1)*$this->rpp.",$this->rpp");
		
		$this->index = $this->total;
		
		return $this->resource;
	}
	
	/**
	 * RSS 피드 출력을 위한 리스트를 반환한다.
	 * @return resource
	 */
	public function initWithRSS(){
		$resource = kboard_query("SELECT uid FROM ".KBOARD_DB_PREFIX."kboard_board_setting WHERE permission_read='all'");
		while($row = mysql_fetch_row($resource)){
			$read[] = $row[0];
		}
		if($read) $where[] = 'board_id IN(' . implode(',', $read) . ')';
		
		$where[] = "secret LIKE ''";
		
		$this->total = reset(mysql_fetch_row(kboard_query("SELECT COUNT(*) FROM ".KBOARD_DB_PREFIX."kboard_board_content WHERE " . implode(" AND ", $where))));
		$this->resource = kboard_query("SELECT * FROM ".KBOARD_DB_PREFIX."kboard_board_content WHERE " . implode(" AND ", $where) . " ORDER BY date DESC LIMIT ".($this->page-1)*$this->rpp.",$this->rpp");
		
		$this->index = $this->total;
		
		return $this->resource;
	}
	
	/**
	 * 게시판 아이디를 입력한다.
	 * @param int $board_id
	 * @return ContentList
	 */
	public function setBoardID($board_id){
		$this->board_id = $board_id;
		return $this;
	}
	
	/**
	 * 페이지 번호를 입력한다.
	 * @param int $page
	 * @return ContentList
	 */
	public function page($page){
		if($page) $this->page = $page;
		return $this;
	}
	
	/**
	 * 한 페이지에 표시될 게시물 숫자를 입력한다. 
	 * @param int $rpp
	 * @return ContentList
	 */
	public function rpp($rpp){
		if($rpp) $this->rpp = $rpp;
		return $this;
	}
	
	/**
	 * 카테고리1을 입력한다.
	 * @param string $category
	 * @return ContentList
	 */
	public function category1($category){
		if($category) $this->col_category1 = $category;
		return $this;
	}
	
	/**
	 * 카테고리2를 입력한다.
	 * @param string $category
	 * @return ContentList
	 */
	public function category2($category){
		if($category) $this->col_category2 = $category;
		return $this;
	}
	
	/**
	 * 게시판의 리스트를 반환한다.
	 * @param string $keyword
	 * @param string $search
	 * @return resource
	 */
	public function getList($keyword='', $search='title'){
		if(is_array($this->board_id)){
			foreach($this->board_id AS $key => $value){
				$board_ids[] = "'$value'";
			}
			$board_ids = implode(',', $board_ids);
			$where[] = "board_id IN ($board_ids)";
		}
		else $where[] = "board_id LIKE '$this->board_id'";
		
		$where[] = "notice LIKE ''";
		if($keyword && $search) $where[] = "$search LIKE '%$keyword%'";
		else if($keyword && !$search) $where[] = "(title LIKE '%$keyword%' OR content LIKE '%$keyword%')";
		if($this->col_category1) $where[] = "category1 LIKE '$this->col_category1'";
		if($this->col_category2) $where[] = "category2 LIKE '$this->col_category2'";
		
		$this->total = reset(mysql_fetch_row(kboard_query("SELECT COUNT(*) FROM ".KBOARD_DB_PREFIX."kboard_board_content WHERE " . implode(" AND ", $where))));
		$this->resource = kboard_query("SELECT * FROM ".KBOARD_DB_PREFIX."kboard_board_content WHERE " . implode(" AND ", $where) . " ORDER BY date DESC LIMIT ".($this->page-1)*$this->rpp.",$this->rpp");
		
		$this->index = $this->total - (($this->page-1) * $this->rpp);
		
		return $this->resource;
	}
	
	/**
	 * 게시판의 모든 리스트를 반환한다.
	 * @return resource
	 */
	public function getAllList(){
		if(is_array($this->board_id)){
			foreach($this->board_id AS $key => $value){
				$board_ids[] = "'$value'";
			}
			$board_ids = implode(',', $board_ids);
			$where = "board_id IN ($board_ids)";
		}
		else $where = "board_id LIKE '$this->board_id'";
		
		$this->total = reset(mysql_fetch_row(kboard_query("SELECT COUNT(*) FROM ".KBOARD_DB_PREFIX."kboard_board_content WHERE $where")));
		$this->resource = kboard_query("SELECT * FROM ".KBOARD_DB_PREFIX."kboard_board_content WHERE $where ORDER BY date DESC");
		
		$this->index = $this->total;
		
		return $this->resource;
	}
	
	/**
	 * 리스트에서 다음 게시물을 반환한다.
	 * @return Content
	 */
	public function hasNext(){
		if(!$this->resource) $this->getList();
		$this->row = mysql_fetch_object($this->resource);
		
		if($this->row){
			$content = new KBContent();
			$content->initWithRow($this->row);
			return $content;
		}
		else{
			return $this->row;
		}
	}
	
	/**
	 * 리스트의 현재 인덱스를 반환한다.
	 * @return int
	 */
	public function index(){
		return $this->index--;
	}
	
	/**
	 * 공지사항 리스트를 반환한다.
	 * @return resource
	 */
	public function getNoticeList(){
		if(is_array($this->board_id)){
			foreach($this->board_id AS $key => $value){
				$board_ids[] = "'$value'";
			}
			$board_ids = implode(',', $board_ids);
			$where[] = "board_id IN ($board_ids)";
		}
		else $where[] = "board_id LIKE '$this->board_id'";
		
		$where[] = "notice LIKE 'true'";
		
		$this->resource_notice = kboard_query("SELECT * FROM ".KBOARD_DB_PREFIX."kboard_board_content WHERE " . implode(" AND ", $where) . " ORDER BY date DESC");
		return $this->resource_notice;
	}
	
	/**
	 * 공지사항 리스트에서 다음 게시물을 반환한다.
	 * @deprecated
	 * @see ContentList::hasNextNotice()
	 * @return Content
	 */
	public function hasNoticeNext(){
		return $this->hasNextNotice();
	}
	
	/**
	 * 공지사항 리스트에서 다음 게시물을 반환한다.
	 * @return Content
	 */
	public function hasNextNotice(){
		if(!$this->resource_notice) $this->getNoticeList();
		$this->row = mysql_fetch_object($this->resource_notice);
	
		if($this->row){
			$content = new KBContent();
			$content->initWithRow($this->row);
			return $content;
		}
		else{
			return $this->row;
		}
	}
}
?>
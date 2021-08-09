<?php
/**
 * KBoard 워드프레스 게시판 댓글 리스트
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentList {
	
	private $next_list_page = 1;
	
	var $board;
	var $total;
	var $content_uid;
	var $parent_uid;
	var $resource;
	var $row;
	var $sort = 'vote';
	var $order = 'DESC';
	var $rpp = 20;
	var $page = 1;
	
	public function __construct($content_uid=''){
		$this->board = new KBoard();
		
		if($this->getSorting() == 'best'){
			// 인기순서
			$this->sort = 'vote';
			$this->order = 'DESC';
		}
		else if($this->getSorting() == 'oldest'){
			// 작성순서
			$this->sort = 'created';
			$this->order = 'ASC';
		}
		else if($this->getSorting() == 'newest'){
			// 최신순서
			$this->sort = 'created';
			$this->order = 'DESC';
		}
		
		if($content_uid) $this->setContentUID($content_uid);
	}
	
	/**
	 * 댓글 목록을 초기화 한다.
	 * @return KBCommentList
	 */
	public function init(){
		global $wpdb;
		if($this->content_uid){
			$orderby = apply_filters('kboard_comments_list_orderby', "`{$this->sort}` {$this->order}", $this);
			
			$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_comments` WHERE `content_uid`='{$this->content_uid}' AND (`parent_uid`<=0 OR `parent_uid` IS NULL) ORDER BY {$orderby}");
		}
		else{
			// 전체 댓글을 불러올땐 최신순서로 정렬한다.
			$this->sort = 'created';
			$this->order = 'DESC';
			$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_comments` WHERE 1 ORDER BY `{$this->sort}` {$this->order} LIMIT ".($this->page-1)*$this->rpp.",{$this->rpp}");
		}
		$wpdb->flush();
		return $this;
	}
	
	/**
	 * 고유번호로 댓글 목록을 초기화 한다.
	 * @param int $content_uid
	 * @return KBCommentList
	 */
	public function initWithUID($content_uid){
		$this->setContentUID($content_uid);
		$this->init();
		return $this;
	}
	
	/**
	 * 부모 고유번호로 초기화 한다.
	 * @param int $parent_uid
	 * @return KBCommentList
	 */
	public function initWithParentUID($parent_uid){
		global $wpdb;
		$this->parent_uid = $parent_uid;
		
		$orderby = apply_filters('kboard_comments_list_orderby', "`{$this->sort}` {$this->order}", $this);
		
		$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_comments` WHERE `parent_uid`='{$this->parent_uid}' ORDER BY {$orderby}");
		$this->total = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_comments` WHERE `parent_uid`='{$this->parent_uid}'");
		$wpdb->flush();
		return $this;
	}
	
	/**
	 * 한 페이지에 표시될 댓글 개수를 입력한다.
	 * @param int $rpp
	 * @return KBCommentList
	 */
	public function rpp($rpp){
		$rpp = intval($rpp);
		if($rpp <= 0){
			$this->rpp = 10;
		}
		else{
			$this->rpp = $rpp;
		}
		return $this;
	}
	
	/**
	 * 댓글을 검색해 리스트를 초기화한다.
	 * @param string $keyword
	 * @return KBCommentList
	 */
	public function initWithKeyword($keyword='', $search){
		global $wpdb;
		
		if($keyword){
			$keyword = esc_sql($keyword);
			if($search){
				$search = esc_sql($search);
				$where = "(`{$search}` LIKE '%{$keyword}%')";
			}
			else{
				$where = "`content` LIKE '%$keyword%'";
			}
		}
		else{
			$where = '1=1';
		}
		
		$offset = ($this->page-1)*$this->rpp;
		
		$results = $wpdb->get_results("SELECT `uid` FROM `{$wpdb->prefix}kboard_comments` WHERE $where ORDER BY `uid` DESC LIMIT $offset,$this->rpp");
		foreach($results as $row){
			$select_uid[] = intval($row->uid);
		}
		
		if(!isset($select_uid)){
			$this->total = 0;
			$this->resource = array();
		}
		else{
			$this->total = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_comments` WHERE $where");
			$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_comments` WHERE `uid` IN(".implode(',', $select_uid).") ORDER BY `uid` DESC");
		}
		
		$wpdb->flush();
		return $this;
	}
	
	/**
	 * 게시판 정보를 반환한다.
	 * @return KBoard
	 */
	public function getBoard(){
		if(isset($this->board->id) && $this->board->id){
			return $this->board;
		}
		else if($this->content_uid){
			$this->board = new KBoard();
			$this->board->initWithContentUID($this->content_uid);
			return $this->board;
		}
		return new KBoard();
	}
	
	/**
	 * 게시물 고유번호를 입력받는다.
	 * @param int $content_uid
	 */
	public function setContentUID($content_uid){
		$this->content_uid = intval($content_uid);
	}
	
	/**
	 * 총 댓글 개수를 반환한다.
	 * @return int
	 */
	public function getCount(){
		global $wpdb;
		if(is_null($this->total)){
			if($this->content_uid){
				$this->total = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_comments` WHERE `content_uid`='{$this->content_uid}'");
			}
			else{
				$this->total = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_comments` WHERE 1");
			}
		}
		return intval($this->total);
	}
	
	/**
	 * 리스트를 초기화한다.
	 */
	public function initFirstList(){
		$this->next_list_page = 1;
	}
	
	/**
	 * 다음 리스트를 반환한다.
	 * @return array
	 */
	public function hasNextList(){
		global $wpdb;
		
		$offset = ($this->next_list_page-1)*$this->rpp;
		
		$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_comments` WHERE `content_uid`='{$this->content_uid}' ORDER BY `{$this->sort}` {$this->order} LIMIT {$offset},{$this->rpp}");
		$wpdb->flush();
		
		if($this->resource){
			$this->next_list_page++;
		}
		else{
			$this->next_list_page = 1;
		}
		
		return $this->resource;
	}
	
	/**
	 * 다음 댓글을 반환한다.
	 * @return Comment
	 */
	public function hasNext(){
		if(!$this->resource) return '';
		$this->row = current($this->resource);
		
		if($this->row){
			next($this->resource);
			$comment = new KBComment();
			$comment->initWithRow($this->row);
			$comment->board = $this->board;
			return $comment;
		}
		else{
			unset($this->resource);
			return '';
		}
	}
	
	/**
	 * 댓글 고유번호를 입력받아 해당 댓글을 반환한다.
	 * @param int $uid
	 * @return KBComment
	 */
	public function getComment($uid){
		$comment = new KBComment();
		$comment->initWithUID($row);
		return $comment;
	}
	
	/**
	 * 댓글 정보를 입력한다.
	 * @param int $parent_uid
	 * @param int $user_uid
	 * @param string $user_display
	 * @param string $content
	 * @param string $status
	 * @param string $password
	 * @return int
	 */
	public function add($parent_uid, $user_uid, $user_display, $content, $status='', $password=''){
		global $wpdb;
		
		// WordPress calculates offsets from UTC.
		date_default_timezone_set('UTC');
		
		$data = array();
		$data['content_uid'] = $this->content_uid;
		$data['parent_uid'] = $parent_uid;
		$data['user_uid'] = $user_uid;
		$data['user_display'] = $user_display;
		$data['content'] = $content;
		$data['like'] = 0;
		$data['unlike'] = 0;
		$data['vote'] = 0;
		$data['created'] = date('YmdHis', current_time('timestamp'));
		$data['status'] = $status;
		$data['password'] = $password;
		
		$board = $this->getBoard();
		
		// 입력할 데이터 필터
		$data = apply_filters('kboard_comments_insert_data', $data, $board->id);
		
		// 댓글 입력 전에 액션 훅 실행
		do_action('kboard_pre_comments_insert', 0, $data['content_uid'], $board);
		
		$data['content_uid'] = isset($data['content_uid'])?intval($data['content_uid']):0;
		$data['parent_uid'] = isset($data['parent_uid'])?intval($data['parent_uid']):0;
		$data['user_uid'] = isset($data['user_uid'])?intval($data['user_uid']):get_current_user_id();
		$data['user_display'] = isset($data['user_display'])?esc_sql(sanitize_text_field($data['user_display'])):'';
		$data['content'] = isset($data['content'])?esc_sql(kboard_safeiframe(kboard_xssfilter($data['content']))):'';
		$data['like'] = isset($data['like'])?intval($data['like']):0;
		$data['unlike'] = isset($data['unlike'])?intval($data['unlike']):0;
		$data['vote'] = isset($data['vote'])?intval($data['vote']):0;
		$data['created'] = isset($data['created'])?esc_sql($data['created']):date('YmdHis', current_time('timestamp'));
		$data['status'] = isset($data['status'])?esc_sql(sanitize_key($data['status'])):'';
		$data['password'] = isset($data['password'])?esc_sql(sanitize_text_field($data['password'])):'';
		
		$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_comments` (`content_uid`, `parent_uid`, `user_uid`, `user_display`, `content`, `like`, `unlike`, `vote`, `created`, `status`, `password`) VALUES ('{$data['content_uid']}', '{$data['parent_uid']}', '{$data['user_uid']}', '{$data['user_display']}', '{$data['content']}', '{$data['like']}', '{$data['unlike']}', '{$data['vote']}', '{$data['created']}', '{$data['status']}', '{$data['password']}')");
		$comment_uid = $wpdb->insert_id;
		
		// 댓글 숫자를 게시물에 등록한다.
		$update = date('YmdHis', current_time('timestamp'));
		$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `comment`=`comment`+1, `update`='{$update}' WHERE `uid`='{$this->content_uid}'");
		
		// 댓글 입력 액션 훅 실행
		do_action('kboard_comments_insert', $comment_uid, $this->content_uid, $board);
		do_action("kboard_comments_insert_{$board->id}", $comment_uid, $this->content_uid, $board);
		
		if($data['parent_uid']){
			$comment = new KBComment();
			$comment->initWithUID($comment_uid);
			
			$parent = new KBComment();
			$parent->initWithUID($data['parent_uid']);
			
			do_action('kboard_comments_reply_insert', $comment, $parent, $board);
			do_action("kboard_comments_reply_insert_{$board->id}", $comment, $parent, $board);
		}
		
		return $comment_uid;
	}
	
	/**
	 * 댓글을 삭제한다.
	 * @param int $comment_uid
	 */
	public function delete($comment_uid){
		$comment = new KBComment();
		$comment->initWithUID($comment_uid);
		$comment->delete();
	}
	
	/**
	 * 정렬 순서를 반환한다.
	 * @return string
	 */
	public function getSorting(){
		static $kboard_comments_sort;
		
		if($kboard_comments_sort){
			return $kboard_comments_sort;
		}
		
		$kboard_comments_sort = isset($_COOKIE['kboard_comments_sort']) ? $_COOKIE['kboard_comments_sort'] : 'best';
		
		if(!in_array($kboard_comments_sort, array('best', 'oldest', 'newest'))){
			$kboard_comments_sort = 'best';
		}
		
		return $kboard_comments_sort;
	}
}
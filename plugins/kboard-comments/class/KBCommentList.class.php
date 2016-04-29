<?php
/**
 * KBoard 워드프레스 게시판 댓글 리스트
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentList {
	
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
	 */
	public function init(){
		global $wpdb;
		if($this->content_uid){
			$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_comments` WHERE `content_uid`='{$this->content_uid}' AND (`parent_uid`<=0 OR `parent_uid` IS NULL) ORDER BY `{$this->sort}` {$this->order}");
		}
		else{
			// 전체 댓글을 불러올땐 최신순서로 정렬한다.
			$this->sort = 'created';
			$this->order = 'DESC';
			$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_comments` WHERE 1 ORDER BY `{$this->sort}` {$this->order} LIMIT ".($this->page-1)*$this->rpp.",{$this->rpp}");
		}
		return $this->resource;
	}
	
	/**
	 * 고유번호로 댓글 목록을 초기화 한다.
	 * @param int $content_uid
	 */
	public function initWithUID($content_uid){
		$this->setContentUID($content_uid);
		$this->init();
	}
	
	/**
	 * 부모 고유번호로 초기화 한다.
	 * @param int $parent_uid
	 */
	public function initWithParentUID($parent_uid){
		global $wpdb;
		$this->parent_uid = $parent_uid;
		$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_comments` WHERE `parent_uid`='{$this->parent_uid}' ORDER BY `{$this->sort}` {$this->order}");
		$this->total = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_comments` WHERE `parent_uid`='{$this->parent_uid}'");
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
			return $comment;
		}
		else{
			unset($this->resource);
			return $this->row;
		}
	}
	
	/**
	 * 댓글 고유번호를 입력받아 해당 댓글을 반환한다.
	 * @param int $uid
	 * @return Comment
	 */
	public function getComment($uid){
		global $wpdb;
		$uid = intval($uid);
		$row = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_comments` WHERE `uid`='{$uid}' LIMIT 1");
		
		if($row){
			$comment = new KBComment();
			$comment->initWithRow($row);
			return $comment;
		}
		else{
			return $row;
		}
	}
	
	/**
	 * 댓글 정보를 입력한다.
	 * @param int $parent_uid
	 * @param int $user_uid
	 * @param string $user_display
	 * @param string $content
	 * @param string $password
	 */
	public function add($parent_uid, $user_uid, $user_display, $content, $password=''){
		global $wpdb;
		$content_uid = $this->content_uid;
		$parent_uid = intval($parent_uid);
		$user_uid = intval($user_uid);
		$user_display = esc_sql(kboard_htmlclear(trim($user_display)));
		$content = esc_sql(kboard_safeiframe(kboard_xssfilter(trim($content))));
		$like = 0;
		$unlike = 0;
		$vote = 0;
		$created = date('YmdHis', current_time('timestamp'));
		$password = esc_sql(kboard_htmlclear(trim($password)));
		
		$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_comments` (`content_uid`, `parent_uid`, `user_uid`, `user_display`, `content`, `like`, `unlike`, `vote`, `created`, `password`) VALUE ('$content_uid', '$parent_uid', '$user_uid', '$user_display', '$content', '$like', '$unlike', '$vote', '$created', '$password')");
		$insert_id = $wpdb->insert_id;
		
		// 댓글 숫자를 게시물에 등록한다.
		$update = date('YmdHis', current_time('timestamp'));
		$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `comment`=`comment`+1, `update`='{$update}' WHERE `uid`='{$content_uid}'");
		
		// 댓글 입력 액션 훅 실행
		do_action('kboard_comments_insert', $insert_id, $content_uid);
		
		return $insert_id;
	}
	
	/**
	 * 댓글을 삭제한다.
	 * @param int $uid
	 */
	public function delete($uid){
		global $wpdb;
		$uid = intval($uid);
		
		if($this->content_uid){
			$content_uid = $this->content_uid;
		}
		else{
			$comment = new KBComment();
			$comment->initWithUID($uid);
			$content_uid = $comment->content_uid;
			$this->setContentUID($content_uid);
		}
		
		// 댓글 삭제 액션 훅 실행
		do_action('kboard_comments_delete', $uid, $content_uid);
		
		$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_comments` WHERE `uid`='{$uid}'");
		
		// 게시물의 댓글 숫자를 변경한다.
		$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `comment`=`comment`-1 WHERE `uid`='{$content_uid}'");
		
		// 자식 댓글을 삭제한다.
		$this->deleteChildren($uid);
	}
	
	/**
	 * 자식 댓글을 삭제한다.
	 * @param int $parent_uid
	 */
	public function deleteChildren($parent_uid){
		global $wpdb;
		$parent_uid = intval($parent_uid);
		
		if($this->content_uid){
			$content_uid = $this->content_uid;
		}
		else{
			$comment = new KBComment();
			$comment->initWithUID($uid);
			$content_uid = $comment->content_uid;
			$this->setContentUID($content_uid);
		}
		
		$resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_comments` WHERE `parent_uid`='{$parent_uid}'");
		foreach($resource as $key=>$child){
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_comments` WHERE `uid`='{$child->uid}'");
			
			// 게시물의 댓글 숫자를 변경한다.
			$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `comment`=`comment`-1 WHERE `uid`='{$content_uid}'");
			
			// 자식 댓글을 삭제한다.
			$this->deleteChildren($child->uid);
		}
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
		
		$kboard_comments_sort = isset($_SESSION['kboard_comments_sort'])?$_SESSION['kboard_comments_sort']:'best';
		$kboard_comments_sort = isset($_GET['kboard_comments_sort'])?$_GET['kboard_comments_sort']:$kboard_comments_sort;
		
		if(!in_array($kboard_comments_sort, array('best', 'oldest', 'newest'))){
			$kboard_comments_sort = 'best';
		}
		
		$_SESSION['kboard_comments_sort'] = $kboard_comments_sort;
		
		return $kboard_comments_sort;
	}
}
?>
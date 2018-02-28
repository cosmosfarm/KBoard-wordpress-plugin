<?php
/**
 * KBoard Router
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBRouter {
	
	/**
	 * 라우터 시작
	 */
	public function process(){
		$content_uid = isset($_GET['kboard_content_redirect'])?intval($_GET['kboard_content_redirect']):'';
		$board_id = isset($_GET['kboard_redirect'])?intval($_GET['kboard_redirect']):'';
		
		if($content_uid){
			$this->contentRedirect($content_uid);
		}
		else if($board_id){
			$this->boardRedirect($board_id);
		}
		
		register_post_type('kboard', array(
			'labels' => array('name'=>'KBoard'),
			'show_ui'=> false,
			'show_in_menu'=> false,
			'rewrite' => false,
			'query_var' => 'kboard_content_redirect',
			'public'=> true
		));
	}
	
	/**
	 * 게시물 본문 페이지로 이동한다.
	 * @param int $content_uid
	 */
	public function contentRedirect($content_uid){
		$content_url = $this->getContentURL($content_uid);
		if($content_url){
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: {$content_url}", true, 301);
			exit;
		}
		$this->error();
	}
	
	/**
	 * 게시물 본문 페이지 주소를 반환한다.
	 * @param int $content_uid
	 * @return string
	 */
	public function getContentURL($content_uid){
		global $wpdb;
		
		$content_uid = intval($content_uid);
		$content = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE `uid`='{$content_uid}'");
		
		if(!$content){
			return '';
		}
		
		if(!empty($content->board_id)){
			$board_id = $content->board_id;
		}
		else if(!empty($content->parent_uid)){
			$parent_content = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE `uid`='{$content->parent_uid}'");
			$board_id = $parent_content->board_id;
		}
		else{
			$board_id = 0;
		}
		
		if($board_id && $content->uid){
			$meta = new KBoardMeta($board_id);
			
			if($meta->latest_target_page){
				$page_id = $meta->latest_target_page;
			}
			else if($meta->auto_page){
				$page_id = $meta->auto_page;
			}
			else {
				$page_id = $wpdb->get_var("SELECT `ID` FROM `{$wpdb->prefix}posts` WHERE `post_content` LIKE '%[kboard id={$board_id}]%' AND `post_type`='page'");
			}
			
			if($page_id){
				$url = new KBUrl();
				$board_url = $url->set('kboard_content_redirect', '')->set('kboard_redirect', '')->set('uid', $content->uid)->set('mod', 'document')->toStringWithPath(get_permalink($page_id));
			}
			else{
				$board_url = home_url("?kboard_id={$board_id}&mod=document&uid={$content->uid}");
			}
			return $board_url;
		}
		return '';
	}
	
	/**
	 * 게시판 목록 페이지로 이동한다.
	 * @param int $board_id
	 */
	public function boardRedirect($board_id){
		$board_url = $this->getBoardURL($board_id);
		if($board_url){
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: {$board_url}", true, 301);
			exit;
		}
		$this->error();
	}
	
	/**
	 * 게시판 목록 페이지 주소를 반환한다.
	 * @param int $board_id
	 * @return string
	 */
	public function getBoardURL($board_id){
		global $wpdb;
		
		$board_id = intval($board_id);
		$board = new KBoard($board_id);
		
		if($board->uid){
			if($board->meta->auto_page){
				$page_id = $board->meta->auto_page;
			}
			else if($board->meta->latest_target_page){
				$page_id = $board->meta->latest_target_page;
			}
			else{
				$page_id = $wpdb->get_var("SELECT `ID` FROM `{$wpdb->prefix}posts` WHERE `post_content` LIKE '%[kboard id={$board_id}]%' AND `post_type`='page'");
			}
			
			if($page_id){
				$url = new KBUrl();
				$board_url = $url->set('kboard_content_redirect', '')->set('kboard_redirect', '')->toStringWithPath(get_permalink($page_id));
			}
			else{
				$board_url = home_url("?kboard_id={$board_id}");
			}
			return $board_url;
		}
		return '';
	}
	
	/**
	 * 오류 화면을 출력한다.
	 */
	private function error(){
		if(!wp_get_referer()) $next = '<a href="'.home_url().'">'.__('Go home', 'kboard').'</a>';
		else $next = '<a href="javascript:history.go(-1);">'.__('Go back', 'kboard').'</a>';
		wp_die(__('It is an invalid access.', 'kboard').'<br>'.$next);
	}
}
?>
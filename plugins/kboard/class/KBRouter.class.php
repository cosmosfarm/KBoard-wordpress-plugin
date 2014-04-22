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
		$content_uid = intval($_GET['kboard_content_redirect']);
		$board_id = intval($_GET['kboard_redirect']);
		
		if($content_uid){
			$this->contentRedirect($content_uid);
		}
		elseif($board_id){
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
	private function contentRedirect($content_uid){
		global $wpdb;
		$content = $wpdb->get_row("SELECT * FROM `".KBOARD_DB_PREFIX."kboard_board_content` WHERE `uid`='$content_uid'");
		if($content->board_id){
			$meta = new KBoardMeta($content->board_id);
			
			if($meta->auto_page) $page_id = $meta->auto_page;
			else {
				$page_id = $wpdb->get_var("SELECT `ID` FROM `".KBOARD_DB_PREFIX."posts` WHERE `post_content` LIKE '%[kboard id={$content->board_id}]%' AND `post_type`='page'");
			}
				
			if($page_id){
				$url = new KBUrl();
				$board_url = $url->set('kboard_content_redirect', '')->set('kboard_redirect', '')->set('uid', $content->uid)->set('mod', 'document')->toStringWithPath( get_permalink($page_id) );
			}
			else{
				$board_url = plugins_url("board.php?board_id={$content->board_id}&mod=document&uid={$content->uid}", __FILE__);
			}
				
			header("Location:{$board_url}");
			exit;
		}
		
		$this->error();
	}
	
	/**
	 * 게시판 목록 페이지로 이동한다.
	 * @param int $board_id
	 */
	private function boardRedirect($board_id){
		global $wpdb;
		$board = new KBoard($board_id);
		if($board->uid){
			$meta = new KBoardMeta($board_id);
			
			if($meta->auto_page) $page_id = $meta->auto_page;
			else {
				$page_id = $wpdb->get_var("SELECT `ID` FROM `".KBOARD_DB_PREFIX."posts` WHERE `post_content` LIKE '%[kboard id={$board_id}]%' AND `post_type`='page'");
			}
			
			if($page_id){
				$url = new KBUrl();
				$board_url = $url->set('kboard_content_redirect', '')->set('kboard_redirect', '')->toStringWithPath( get_permalink($page_id) );
			}
			else{
				$board_url = plugins_url("board.php?board_id={$board_id}", __FILE__);
			}
			
			header("Location:{$board_url}");
			exit;
		}
		
		$this->error();
	}
	
	/**
	 * 오류 화면을 출력한다.
	 */
	private function error(){
		if(!stristr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) $next = '<a href="'.site_url().'">'.__('Go home', 'kboard').'</a>';
		else $next = '<a href="javascript:history.go(-1);">'.__('Go back', 'kboard').'</a>';
		wp_die(__('It is an invalid access.', 'kboard').'<br>'.$next);
		exit;
	}
}
?>
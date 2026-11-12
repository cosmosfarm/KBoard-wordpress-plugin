<?php
/**
 * KBoard Router
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBRouter {
	
	/**
	 * 라우터 시작
	 */
	public function process(){
		$content_uid = isset($_GET['kboard_content_redirect']) ? intval($_GET['kboard_content_redirect']) : '';
		$board_id = isset($_GET['kboard_redirect']) ? intval($_GET['kboard_redirect']) : '';
		
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
		add_filter('posts_clauses', array($this, 'protectSearchPosts'), 99, 2);
		add_filter('posts_pre_query', array($this, 'protectUnfilteredSearchPosts'), 99, 2);
	}

	/** 워드프레스 복제 글은 사용자와 무관하게 공개로 확인되는 내용만 노출한다. */
	public function protectSearchPosts($clauses, $query){
		global $wpdb;
		$post_type = $query->get('post_type');
		if($post_type && $post_type !== 'any' && !in_array('kboard', (array)$post_type, true)) return $clauses;
		if(!$post_type && !$query->is_search()) return $clauses;
		// 이전 검색 결과 캐시가 변경된 공개 설정을 우회하지 못하게 한다.
		$query->set('cache_results', false);
		$public = '0';
		// 동적 권한/값 필터가 있으면 공개라고 확정하지 않는다.
		if(!has_filter('kboard_is_reader') && !has_filter('kboard_content_value')){
			$point = function_exists('mycred_add') ? " OR m.`key`='document_read_down_point'" : '';
			$public = "EXISTS (SELECT 1 FROM `{$wpdb->prefix}kboard_board_content` c
				INNER JOIN `{$wpdb->prefix}kboard_board_setting` b ON b.uid=c.board_id
				WHERE c.uid=CAST({$wpdb->posts}.post_name AS UNSIGNED)
				AND CAST({$wpdb->posts}.post_name AS BINARY)=CAST(c.uid AS BINARY)
				AND BINARY b.permission_read=BINARY 'all'
				AND NOT EXISTS (SELECT 1 FROM `{$wpdb->prefix}kboard_board_meta` m WHERE m.board_id=b.uid
					AND (m.`key`='permission_list'{$point}) AND OCTET_LENGTH(m.`value`)>0
					AND NOT (OCTET_LENGTH(m.`value`)=1 AND m.`value`='0'))
				AND c.search IN ('1','2') AND COALESCE(c.status,'') NOT IN ('pending_approval','trash')
				AND (c.parent_uid=0 OR EXISTS (SELECT 1 FROM `{$wpdb->prefix}kboard_board_content` parent_content
					WHERE parent_content.uid=c.parent_uid AND parent_content.parent_uid=0 AND parent_content.board_id=c.board_id
					AND COALESCE(parent_content.status,'') NOT IN ('pending_approval','trash')))
				AND ((CAST(COALESCE(c.secret,'') AS BINARY) IN ('','0') AND c.search='1')
					OR (OCTET_LENGTH({$wpdb->posts}.post_content)=0 AND OCTET_LENGTH({$wpdb->posts}.post_excerpt)=0)))";
		}
		$clauses['where'] .= " AND ({$wpdb->posts}.post_type<>'kboard' OR {$public})";
		return $clauses;
	}

	/** suppress_filters를 변경하지 않고 필터를 생략한 WordPress 기본 조회에도 적용한다. */
	public function protectUnfilteredSearchPosts($posts, $query){
		if(!$query->get('suppress_filters')) return $posts;
		$clauses = $this->protectSearchPosts(array('where'=>''), $query);
		if(!$clauses['where']) return $posts;
		$position = strpos($query->request, 'WHERE 1=1');
		if($position === false) return array();
		$query->request = substr_replace($query->request, 'WHERE 1=1'.$clauses['where'], $position, 9);
		return $posts;
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
		
		$content_url = '';
		
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
			else{
				$page_id = $wpdb->get_var("SELECT `ID` FROM `{$wpdb->prefix}posts` WHERE `post_content` LIKE '%[kboard id={$board_id}]%' AND `post_type`='page'");
			}
			
			if($page_id){
				$url = new KBUrl();
				$content_url = $url->set('kboard_content_redirect', '')->set('kboard_redirect', '')->set('uid', $content->uid)->set('mod', 'document')->toStringWithPath(get_permalink($page_id));
			}
			else{
				$content_url = home_url("?kboard_id={$board_id}&mod=document&uid={$content->uid}");
			}
		}
		
		return apply_filters('kboard_router_content_url', $content_url, $page_id, $board_id, $content_uid);
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
		
		$board_url = '';
		
		$board_id = intval($board_id);
		$board = new KBoard($board_id);
		
		if($board->id){
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
		}
		return apply_filters('kboard_router_board_url', $board_url, $page_id, $board_id);
	}
	
	/**
	 * 오류 화면을 출력한다.
	 */
	private function error(){
		if(!wp_get_referer()) $next = '<a href="'.home_url().'">« '.__('Go home', 'kboard').'</a>';
		else $next = '<a href="javascript:history.go(-1);">« '.__('Go back', 'kboard').'</a>';
		wp_die(__('It is an invalid access.', 'kboard').'<br>'.$next, '', array('response'=>410));
	}
}

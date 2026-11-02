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
		add_action('pre_get_posts', array($this, 'protectSearchPosts'), 99);
	}

	/** 워드프레스 검색용 복제 글에도 현재 게시판의 읽기 권한을 적용한다. */
	public function protectSearchPosts($query){
		global $wpdb;

		$post_type = $query->get('post_type');
		if($post_type && $post_type !== 'any' && !in_array('kboard', (array)$post_type, true)) return;
		if(!$post_type && !$query->is_search() && !$query->is_singular()) return;

		$has_reader_filter = has_filter('kboard_is_reader');
		$extra = $has_reader_filter ? ' OR 1=1' : '';
		$fields = has_filter('kboard_content_value') ? 'c.*' : 'c.uid, c.board_id, c.parent_uid, c.member_uid, c.search, c.secret, c.status, c.notice, c.password';
		$list_check = "EXISTS (SELECT 1 FROM `{$wpdb->prefix}kboard_board_meta` list_meta WHERE list_meta.board_id=c.board_id AND list_meta.`key`='permission_list' AND list_meta.`value` NOT IN ('', '0'))";
		$point_check = function_exists('mycred_add') ? "EXISTS (SELECT 1 FROM `{$wpdb->prefix}kboard_board_meta` point_meta WHERE point_meta.board_id=c.board_id AND point_meta.`key`='document_read_down_point' AND point_meta.`value` NOT IN ('', '0'))" : '0';
		$rows = $wpdb->get_results("SELECT p.ID, (p.post_content<>'' OR p.post_excerpt<>'') AS has_public_text,
			{$fields},
			b.permission_read, {$list_check} AS private_list, {$point_check} AS point_required FROM `{$wpdb->posts}` p
			LEFT JOIN `{$wpdb->prefix}kboard_board_content` c ON p.post_name=CAST(c.uid AS CHAR)
			LEFT JOIN `{$wpdb->prefix}kboard_board_setting` b ON b.uid=c.board_id
			WHERE p.post_type='kboard' AND (c.uid IS NULL OR b.uid IS NULL OR b.permission_read<>'all' OR c.parent_uid<>0
			OR c.status IN ('pending_approval', 'trash') OR c.search='3'
			OR ((c.secret<>'' OR c.search='2') AND (p.post_content<>'' OR p.post_excerpt<>''))
			OR {$list_check} OR {$point_check}{$extra})");
		if($wpdb->last_error){
			// 권한을 확인할 수 없는 경우 기존 복제 글을 공개하지 않는다.
			$ids = $wpdb->get_col("SELECT ID FROM `{$wpdb->posts}` WHERE post_type='kboard'");
			if($wpdb->last_error){
				$query->set('p', 0);
				$query->set('post__in', array(0));
				return;
			}
			$rows = array();
			foreach($ids as $id) $rows[] = (object)array('ID'=>$id, 'uid'=>0);
		}
		if(!$rows) return;

		$excluded = array();
		$boards = array();
		$guest_without_override = !is_user_logged_in() && !$has_reader_filter && !(get_option('kboard_allow_search_engines_always_read') && kboard_is_bot());
		foreach($rows as $row){
			if(!$row->uid || $row->search == '3'){
				$excluded[] = (int)$row->ID;
				continue;
			}
			if(($row->secret || $row->search == '2') && $row->has_public_text){
				// 예전 복원본의 노출된 본문만 정리하고 원본 글과 제목 검색은 유지한다.
				$updated = $wpdb->update($wpdb->posts, array('post_content'=>'', 'post_excerpt'=>''), array('ID'=>(int)$row->ID));
				if($updated === false){
					$excluded[] = (int)$row->ID;
					continue;
				}
				clean_post_cache((int)$row->ID);
			}
			if($guest_without_override && ($row->permission_read != 'all' || $row->private_list || $row->point_required || $row->status == 'pending_approval' || $row->status == 'trash')){
				$excluded[] = (int)$row->ID;
				continue;
			}
			$board_id = (int)$row->board_id;
			if(!isset($boards[$board_id])) $boards[$board_id] = new KBoard($board_id);
			$content = new KBContent();
			$content->row = $row;
			$content->board_id = $board_id;
			$content->board = $boards[$board_id];
			$board = $content->board;
			if(!$board->id){
				$excluded[] = (int)$row->ID;
				continue;
			}
			// 공개 게시판의 비밀글은 원래 설정된 제목만 검색 기능을 유지한다.
			$title_only = $content->secret && !$board->isPrivate() && $board->permission_read == 'all'
				&& !$content->isTrash() && ($content->status != 'pending_approval' || $content->isEditor())
				&& (!$board->meta->document_read_down_point || !function_exists('mycred_add') || ($board->meta->point_applied_to && $board->isAdmin()));
			if($title_only && $content->parent_uid){
				$top = $content->getTopContent();
				if(!$top->uid || (intval($top->board_id) !== $board_id && !$top->isDirectReader())) $title_only = false;
			}
			if(!$title_only && !$content->isDirectReader()) $excluded[] = (int)$row->ID;
		}
		if(!$excluded) return;

		// WP_Query는 p, post__in, post__not_in을 동시에 적용하지 않는다.
		$post_id = (int)$query->get('p');
		if($post_id && in_array($post_id, $excluded, true)){
			$query->set('p', 0);
			$query->set('post__in', array(0));
		}
		else if($query->get('post__in')){
			$allowed = array_diff(array_map('intval', (array)$query->get('post__in')), $excluded);
			$query->set('post__in', $allowed ? array_values($allowed) : array(0));
		}
		else{
			$query->set('post__not_in', array_values(array_unique(array_merge((array)$query->get('post__not_in'), $excluded))));
		}
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

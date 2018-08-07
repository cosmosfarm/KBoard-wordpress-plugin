<?php
/**
 * KBoard 워드프레스 게시판 생성
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBoardBuilder {
	
	var $mod;
	var $board;
	var $board_id;
	var $meta;
	var $uid;
	var $skin;
	var $skin_name;
	var $category1;
	var $category2;
	var $rpp;
	var $sort;
	var $url;
	var $within_days;
	var $view_iframe;
	
	public function __construct($board_id='', $is_latest=false){
		$this->category1 = kboard_category1();
		$this->category2 = kboard_category2();
		$this->uid = kboard_uid();
		$this->sort = 'newest';
		
		$this->setSkin('default');
		
		if($board_id) $this->setBoardID($board_id, $is_latest);
	}
	
	/**
	 * 게시판 뷰(View)를 설정한다. (List/Document/Editor/Remove/Order/Complete/History/Sales)
	 * @param string $mod
	 */
	public function setMOD($mod){
		$this->mod = $mod;
	}
	
	/**
	 * 게시판 스킨을 설정한다.
	 * @param string $skin
	 */
	public function setSkin($skin){
		$this->skin = KBoardSkin::getInstance();
		$this->skin_name = $skin;
	}
	
	/**
	 * 게시판 ID를 설정한다.
	 * @param int $board_id
	 */
	public function setBoardID($board_id, $is_latest=false){
		static $check_kboard_comments_plugin_once;
		
		$this->board_id = $board_id;
		$this->meta = new KBoardMeta($this->board_id);
		
		// 코스모스팜 소셜댓글 스크립트 추가
		if(!$check_kboard_comments_plugin_once){
			if($this->meta->comments_plugin_id && $this->meta->use_comments_plugin){
				wp_localize_script('kboard-script', 'cosmosfarm_comments_plugin_id', $this->meta->comments_plugin_id);
				wp_enqueue_script('cosmosfarm-comments-plugin', 'https://plugin.cosmosfarm.com/comments.js', array(), '1.0', true);
				wp_enqueue_script('kboard-comments-plugin', KBOARD_URL_PATH . '/template/js/comments_plugin.js', array(), KBOARD_VERSION, true);
				$check_kboard_comments_plugin_once = true;
			}
		}
		
		if(!$is_latest){
			$default_build_mod = $this->meta->default_build_mod;
			if(!$default_build_mod) $default_build_mod = 'list';
			$this->mod = kboard_mod(apply_filters('kboard_default_build_mod', $default_build_mod, $this->board_id));
			
			// 외부 요청을 금지하기 위해서 사용될 게시판 id는 세션에 저장한다.
			$_SESSION['kboard_board_id'] = $this->board_id;
			
			$tree_category = unserialize($this->meta->tree_category);
			wp_localize_script('kboard-script', 'kboard_current', array('board_id'=>$this->board_id, 'content_uid'=>$this->uid, 'use_tree_category'=>$this->meta->use_tree_category, 'tree_category'=>$tree_category));
			
			// KBoard 미디어 추가
			add_action('media_buttons_context', 'kboard_editor_button');
			add_filter('mce_buttons', 'kboard_register_media_button');
			add_filter('mce_external_plugins', 'kboard_add_media_button');
			
			// font-awesome 출력
			if(!get_option('kboard_fontawesome')){
				global $wp_styles;
				wp_enqueue_style('font-awesome', KBOARD_URL_PATH . '/assets/font-awesome/css/font-awesome.min.css', array(), KBOARD_VERSION);
				wp_enqueue_style('font-awesome-ie7', KBOARD_URL_PATH . '/assets/font-awesome/css/font-awesome-ie7.min.css', array(), KBOARD_VERSION);
				$wp_styles->add_data('font-awesome-ie7', 'conditional', 'lte IE 7');
			}
		}
	}
	
	/**
	 * 페이지당 게시글 개수를 설정한다.
	 * @param int $rpp
	 */
	public function setRpp($rpp){
		$this->rpp = intval($rpp);
	}
	
	/**
	 * 게시글 정렬 순서를 설정한다.
	 * @param string $sort
	 */
	public function setSorting($sort){
		$this->sort = $sort;
	}
	
	/**
	 * 게시판 실제 주소를 설정한다.
	 * @param string $url
	 */
	public function setURL($url){
		$this->url = $url;
	}
	
	/**
	 * 최신글 숏코드 기간을 설정한다.
	 * @param int $within_days
	 */
	public function setWithinDays($within_days){
		$this->within_days = intval($within_days);
	}
	
	/**
	 * 게시판 리스트를 반환한다.
	 * @return KBContentList
	 */
	public function getList(){
		$list = new KBContentList($this->board_id);
		$list->category1($this->category1);
		$list->category2($this->category2);
		
		if($this->board->isPrivate()){
			if(is_user_logged_in()){
				$list->memberUID(get_current_user_id());
			}
			else{
				$list->stop = true;
			}
		}
		
		$list->rpp($this->rpp);
		$list->page(kboard_pageid());
		$list->setCompare(kboard_compare());
		$list->setDateRange(kboard_start_date(), kboard_end_date());
		$list->setSearchOption(kboard_search_option());
		$list->getList(kboard_keyword(), kboard_target(), kboard_with_notice());
		return $list;
	}
	
	/**
	 * 게시판 리스트를 배열로 반환한다.
	 */
	public function getListArray(){
		// KBoardBuilder 클래스에서 실행된 게시판의 mod 값을 설정한다.
		kboard_builder_mod('list');
		
		$list = $this->getList();
		$data = array();
		while($content = $list->hasNext()){
			$url = new KBUrl(wp_get_referer());
			$_data['uid'] = $content->uid;
			$_data['member_uid'] = $content->member_uid;
			$_data['member_display'] = $content->member_display;
			$_data['title'] = $content->title;
			$_data['content'] = $content->secret!='true'?$content->content:'';
			$_data['date'] = $content->date;
			$_data['view'] = $content->view;
			$_data['comment'] = $content->comment;
			$_data['like'] = $content->like;
			$_data['unlike'] = $content->unlike;
			$_data['vote'] = $content->vote;
			$_data['thumbnail_file'] = $content->thumbnail_file;
			$_data['thumbnail_name'] = $content->thumbnail_name;
			$_data['category1'] = $content->category1;
			$_data['category2'] = $content->category2;
			$_data['secret'] = $content->secret;
			$_data['search'] = $content->search;
			$_data['attach'] = $content->attach;
			$_data['option'] = $content->option->toArray();
			if($this->view_iframe){
				$_data['urls']['document'] = $url->set('uid', $content->uid)->set('mod', 'document')->set('kboard_id', $content->board_id)->set('view_iframe', '1')->toString();
				$_data['urls']['editor'] = $url->set('uid', $content->uid)->set('mod', 'editor')->set('kboard_id', $content->board_id)->set('view_iframe', '1')->toString();
				$_data['urls']['remove'] = $url->set('uid', $content->uid)->set('mod', 'remove')->set('kboard_id', $content->board_id)->set('view_iframe', '1')->toString();
			}
			else{
				$_data['urls']['document'] = $url->getDocumentURLWithUID($content->uid);
				$_data['urls']['editor'] = $url->getContentEditor($content->uid);
				$_data['urls']['remove'] = $url->getContentRemove($content->uid);
			}
			$data[] = $_data;
		}
		return $data;
	}
	
	/**
	 * 게시판 페이지를 생성하고 반환한다.
	 * @return string
	 */
	public function create(){
		if($this->meta->permission_list && $this->meta->permission_access && !is_user_logged_in()){
			echo '<script>alert("'.__('Please Log in to continue.', 'kboard').'");</script>';
			echo '<script>top.window.location.href="' . wp_login_url($_SERVER['REQUEST_URI']) . '";</script>';
		}
		else{
			if(($this->meta->view_iframe || is_admin()) && !kboard_id()){
				$url = new KBUrl();
				return '<iframe id="kboard-iframe-' . $this->board_id . '" src="' . $url->set('kboard_id', $this->board_id)->set('uid', kboard_uid())->set('mod', kboard_mod())->set('category1', kboard_category1())->set('category2', kboard_category2())->set('keyword', kboard_keyword())->set('target', kboard_target())->set('view_iframe', '1')->toString() . '" style="width:100%" scrolling="no" frameborder="0"></iframe>';
			}
			
			// KBoardBuilder 클래스에서 실행된 게시판의 mod 값을 설정한다.
			kboard_builder_mod($this->mod);
			
			if($this->meta->pass_autop == 'enable'){
				do_action('kboard_skin_header', $this);
				call_user_func(array($this, 'builder'.ucfirst($this->mod)));
				do_action('kboard_skin_footer', $this);
				return '';
			}
			else{
				ob_start();
				do_action('kboard_skin_header', $this);
				call_user_func(array($this, 'builder'.ucfirst($this->mod)));
				do_action('kboard_skin_footer', $this);
				return ob_get_clean();
			}
		}
	}
	
	/**
	 * 게시판 리스트 페이지를 생성한다.
	 */
	public function builderList(){
		$order = new KBOrder();
		$order->board = $this->board;
		$order->board_id = $this->board_id;
		
		$url = new KBUrl();
		$url->setBoard($this->board);
		
		$vars = array(
			'list' => $this->getList(),
			'order' => $order,
			'url' => $url,
			'skin' => $this->skin,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $this->board,
			'boardBuilder' => $this,
		);
		
		echo $this->skin->load($this->skin_name, 'list.php', $vars);
	}
	
	/**
	 * 답글 리스트를 생성한다.
	 * @param int $parent_uid
	 */
	public function builderReply($parent_uid, $depth=0){
		$list = new KBContentList();
		$list->getReplyList($parent_uid);
		
		$order = new KBOrder();
		$order->board = $this->board;
		$order->board_id = $this->board_id;
		
		$url = new KBUrl();
		$url->setBoard($this->board);
		
		$vars = array(
			'list' => $list,
			'depth' => $depth,
			'order' => $order,
			'url' => $url,
			'skin' => $this->skin,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $this->board,
			'boardBuilder' => $this,
		);
		
		echo $this->skin->load($this->skin_name, 'reply-template.php', $vars);
	}
	
	/**
	 * 게시판 본문 페이지를 생성한다.
	 */
	public function builderDocument(){
		$url = new KBUrl();
		$url->setBoard($this->board);
		
		$content = new KBContent($this->board_id);
		$content->initWithUID($this->uid);
		
		if(!$content->uid){
			echo '<script>window.location.href="' . $url->set('mod', 'list')->toString() . '";</script>';
			exit;
		}
		
		if($content->isTrash()){
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			exit;
		}
		
		if($this->board->isPrivate()){
			if(is_user_logged_in()){
				if(!$content->notice && $content->member_uid != get_current_user_id() && $content->getTopContent()->member_uid != get_current_user_id()){
					echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
					exit;
				}
			}
			else{
				echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
				exit;
			}
		}
		
		$board = $this->board;
		$content->board = $board;
		$board->content = $content;
		
		$order = new KBOrder();
		$order->board = $this->board;
		$order->board_id = $this->board_id;
		
		$vars = array(
			'content' => $content,
			'order' => $order,
			'url' => $url,
			'skin' => $this->skin,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $board,
			'boardBuilder' => $this,
		);
		
		$allow_document = false;
		if(!$content->isReader()){
			if($this->board->permission_read != 'all' && !is_user_logged_in()){
				if($this->meta->view_iframe){
					do_action('kboard_cannot_read_document', 'go_login', wp_login_url($url->getDocumentRedirect($content->uid)), $content, $board, $this);
				}
				else{
					do_action('kboard_cannot_read_document', 'go_login', wp_login_url($_SERVER['REQUEST_URI']), $content, $board, $this);
				}
			}
			else if($content->secret){
				if(!$content->isConfirm()){
					if($content->parent_uid){
						$parent = new KBContent();
						$parent->initWithUID($content->getTopContentUID());
						if($this->board->isReader($parent->member_uid, $content->secret) || $parent->isConfirm()){
							$allow_document = true;
						}
						else{
							echo $this->skin->load($this->skin_name, 'confirm.php', $vars);
						}
					}
					else{
						echo $this->skin->load($this->skin_name, 'confirm.php', $vars);
					}
				}
				else{
					$allow_document = true;
				}
			}
			else{
				do_action('kboard_cannot_read_document', 'go_back', $url->set('mod', 'list')->toString(), $content, $board, $this);
			}
		}
		else{
			$allow_document = true;
		}
		
		// 글읽기 감소 포인트
		if($allow_document && $board->meta->document_read_down_point){
			if(function_exists('mycred_add')){
				if(!is_user_logged_in()){
					if($this->meta->view_iframe){
						do_action('kboard_cannot_read_document', 'go_login', wp_login_url($url->getDocumentRedirect($content->uid)), $content, $board, $this);
					}
					else{
						do_action('kboard_cannot_read_document', 'go_login', wp_login_url($_SERVER['REQUEST_URI']), $content, $board, $this);
					}
					$allow_document = false;
				}
				else if($content->member_uid != get_current_user_id()){
					$log_args['user_id'] = get_current_user_id();
					$log_args['ref'] = 'document_read_down_point';
					$log_args['ref_id'] = $content->uid;
					$log = new myCRED_Query_Log($log_args);
					
					if(!$log->have_entries()){
						$balance = mycred_get_users_balance(get_current_user_id());
						if($board->meta->document_read_down_point > $balance){
							do_action('kboard_cannot_read_document', 'not_enough_points', $url->set('mod', 'list')->toString(), $content, $board, $this);
							$allow_document = false;
						}
						else{
							$point = intval(get_user_meta(get_current_user_id(), 'kboard_document_mycred_point', true));
							update_user_meta(get_current_user_id(), 'kboard_document_mycred_point', $point + ($board->meta->document_read_down_point*-1));
							
							mycred_add('document_read_down_point', get_current_user_id(), ($board->meta->document_read_down_point*-1), __('Reading decrease points', 'kboard'), $content->uid);
						}
					}
				}
			}
		}
		
		if($allow_document){
			$content->increaseView();
			
			// 에디터를 사용하지 않고, autolink가 활성화면 자동으로 link를 생성한다.
			if(!$board->use_editor && $this->meta->autolink){
				include_once KBOARD_DIR_PATH . '/helper/Autolink.helper.php';
				$content->content = apply_filters('kboard_content_paragraph_breaks', kboard_autolink($content->getContent()), $this);
			}
			else{
				$content->content = apply_filters('kboard_content_paragraph_breaks', $content->getContent(), $this);
			}
			
			// kboard_content 필터 실행
			$content->content = apply_filters('kboard_content', $content->getContent(), $content->uid, $this->board_id);
			
			// 게시글 숏코드(Shortcode) 실행
			if($this->meta->shortcode_execute == 1){
				$content->content = do_shortcode($content->getContent());
			}
			else{
				$content->content = str_replace('[', '&#91;', $content->getContent());
				$content->content = str_replace(']', '&#93;', $content->getContent());
			}
			
			echo $this->skin->load($this->skin_name, 'document.php', $vars);
			
			if(apply_filters('kboard_always_view_list', $board->meta->always_view_list, $this)){
				do_action('kboard_skin_always_view_list', $this);
				$this->builderList();
			}
		}
	}
	
	/**
	 * 게시판 에디터 페이지를 생성한다.
	 */
	public function builderEditor(){
		$url = new KBUrl();
		$url->setBoard($this->board);
		
		if($this->board->isWriter() && $this->board->permission_write=='all' && isset($_POST['title']) && $_POST['title']){
			$next_url = $url->set('uid', $this->uid)->set('mod', 'editor')->toString();
			if(!is_user_logged_in() && (!isset($_POST['password']) || !$_POST['password'])){
				echo '<script>alert("'.__('Please enter your password.', 'kboard').'");</script>';
				echo '<script>window.location.href="' . $next_url . '";</script>';
				exit;
			}
		}
		
		$content = new KBContent();
		$content->initWithUID($this->uid);
		$content->setBoardID($this->board_id);
		
		if($content->isTrash()){
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			exit;
		}
		
		$board = $this->board;
		$content->board = $board;
		$board->content = $content;
		
		$order = new KBOrder();
		$order->board = $this->board;
		$order->board_id = $this->board_id;
		
		$vars = array(
			'content' => $content,
			'order' => $order,
			'url' => $url,
			'skin' => $this->skin,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $board,
			'boardBuilder' => $this,
		);
		
		$confirm_view = false;
		if(!$content->uid && !$this->board->isWriter()){
			if(is_user_logged_in()){
				echo '<script>alert("'.__('You do not have permission.', 'kboard').'");</script>';
				echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			}
			else{
				$login_url = wp_login_url($_SERVER['REQUEST_URI']);
				echo '<script>alert("'.__('You do not have permission.', 'kboard').'");</script>';
				echo "<script>top.window.location.href='{$login_url}';</script>";
			}
			exit;
		}
		else if($content->uid && !$content->isEditor()){
			if($this->board->permission_write=='all' && !$content->member_uid){
				if(!$content->isConfirm()){
					$confirm_view = true;
				}
			}
			else{
				if(is_user_logged_in()){
					echo '<script>alert("'.__('You do not have permission.', 'kboard').'");</script>';
					echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
				}
				else{
					$login_url = wp_login_url($_SERVER['REQUEST_URI']);
					echo '<script>alert("'.__('You do not have permission.', 'kboard').'");</script>';
					echo "<script>top.window.location.href='{$login_url}';</script>";
				}
				exit;
			}
		}
		
		if($confirm_view){
			echo $this->skin->load($this->skin_name, 'confirm.php', $vars);
		}
		else{
			// 글쓰기 감소 포인트 체크
			if($content->execute_action == 'insert' && $board->meta->document_insert_down_point){
				if(function_exists('mycred_add')){
					if(!is_user_logged_in()){
						$login_url = wp_login_url($_SERVER['REQUEST_URI']);
						echo '<script>alert("'.__('You do not have permission.', 'kboard').'");</script>';
						echo "<script>top.window.location.href='{$login_url}';</script>";
						exit;
					}
					else{
						$balance = mycred_get_users_balance(get_current_user_id());
						if($board->meta->document_insert_down_point > $balance){
							echo '<script>alert("'.__('You have not enough points.', 'kboard').'");</script>';
							echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
							exit;
						}
					}
				}
			}
			
			// 임시저장된 데이터로 초기화 한다.
			if($content->execute_action == 'insert'){
				$content->initWithTemporary();
			}
			
			// 내용이 없으면 등록된 기본 양식을 가져온다.
			if(!$content->uid && !$content->content){
				$content->content = $this->meta->default_content;
			}
			// 새로운 글 작성 시 기본적으로 비밀글로 설정한다.
			if(!$content->uid && $this->meta->secret_checked_default){
				$content->secret = 'true';
			}
			
			// 새로운 답글 쓰기에서만 실행한다.
			if(kboard_parent_uid() && !$content->uid && !$content->parent_uid){
				$parent = new KBContent();
				$parent->initWithUID(kboard_parent_uid());
				
				// 부모 고유번호가 있으면 답글로 등록하기 위해서 부모 고유번호를 등록한다.
				$content->parent_uid = $parent->uid;
				
				// 부모의 제목을 가져온다.
				$content->title = 'Re:' . $parent->title;
				
				// 답글 기본 내용을 설정한다.
				if($this->meta->reply_copy_content=='1'){
					$content->content = $parent->getContent();
				}
				else if($this->meta->reply_copy_content=='2'){
					$content->content = $this->meta->default_content;
				}
				else{
					$content->content = '';
				}
			}
			
			// 숏코드(Shortcode)를 실행하지 못하게 변경한다.
			$content->content = str_replace('[', '&#91;', $content->getContent());
			$content->content = str_replace(']', '&#93;', $content->getContent());
			
			$vars['parent'] = isset($parent) ? $parent : new KBContent();
			
			echo $this->skin->load($this->skin_name, 'editor.php', $vars);
		}
	}
	
	/**
	 * 게시글 삭제 페이지를 생성한다. (완료 후 바로 리다이렉션)
	 */
	public function builderRemove(){
		$url = new KBUrl();
		$url->setBoard($this->board);
		
		if(!isset($_GET['kboard-content-remove-nonce']) || !wp_verify_nonce($_GET['kboard-content-remove-nonce'], 'kboard-content-remove')){
			if(!wp_get_referer()){
				echo '<script>alert("'.__('This page is restricted from external access.', 'kboard').'");</script>';
				echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
				exit;
			}
		}
		
		$content = new KBContent($this->board_id);
		$content->initWithUID($this->uid);
		
		$confirm_view = false;
		if(!$content->isEditor()){
			if($this->board->permission_write=='all' && !$content->member_uid){
				if(!$content->isConfirm(true)){
					$confirm_view = true;
				}
			}
			else{
				if(wp_get_referer()){
					echo '<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>';
				}
				else{
					echo '<script>alert("'.__('You do not have permission.', 'kboard').'");</script>';
					echo "<script>window.location.href='{$url->set('mod', 'document')->set('uid', $content->uid)->toString()}';</script>";
				}
				exit;
			}
		}
		
		if($confirm_view){
			$board = $this->board;
			$content->board = $board;
			$board->content = $content;
			
			$order = new KBOrder();
			$order->board = $board;
			$order->board_id = $board->id;
			
			$vars = array(
				'content' => $content,
				'order' => $order,
				'url' => $url,
				'skin' => $this->skin,
				'skin_path' => $this->skin->url($this->skin_name),
				'skin_dir' => $this->skin->dir($this->skin_name),
				'board' => $board,
				'boardBuilder' => $this,
			);
			
			echo $this->skin->load($this->skin_name, 'confirm.php', $vars);
		}
		else{
			$delete_immediately = get_option('kboard_content_delete_immediately');
			
			if($delete_immediately){
				$content->remove();
			}
			else{
				$content->status = 'trash';
				$content->updateContent();
			}
			
			// 삭제뒤 게시판 리스트로 이동한다.
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			exit;
		}
	}
	
	/**
	 * 주문 작성 페이지를 생성한다.
	 */
	public function builderOrder(){
		$url = new KBUrl();
		$url->setBoard($this->board);
		
		$content = new KBContent($this->board_id);
		$content->initWithUID($this->uid);
		
		if(!$content->uid){
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			exit;
		}
		
		if($content->isTrash()){
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			exit;
		}
		
		if($this->board->isPrivate()){
			if(is_user_logged_in()){
				if(!$content->notice && $content->member_uid != get_current_user_id() && $content->getTopContent()->member_uid != get_current_user_id()){
					echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
					exit;
				}
			}
			else{
				echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
				exit;
			}
		}
		
		$board = $this->board;
		$content->board = $board;
		$board->content = $content;
		
		$order = new KBOrder();
		$order->board = $this->board;
		$order->board_id = $this->board_id;
		$order->initOrder();
		$order->initOrderItems();
		
		$vars = array(
			'content' => $content,
			'order' => $order,
			'url' => $url,
			'skin' => $this->skin,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $board,
			'boardBuilder' => $this,
		);
		
		$allow_document = false;
		if(!$this->board->isReader($content->member_uid, $content->secret)){
			if(!is_user_logged_in() && $this->board->permission_read!='all'){
				if($this->meta->view_iframe){
					do_action('kboard_cannot_read_document', 'go_login', wp_login_url($url->getDocumentRedirect($content->uid)), $content, $board, $this);
				}
				else{
					do_action('kboard_cannot_read_document', 'go_login', wp_login_url($_SERVER['REQUEST_URI']), $content, $board, $this);
				}
			}
			else if($content->secret){
				if(!$this->board->isConfirm($content->password, $content->uid)){
					if($content->parent_uid){
						$parent = new KBContent();
						$parent->initWithUID($content->getTopContentUID());
						if($this->board->isReader($parent->member_uid, $content->secret)){
							$allow_document = true;
						}
						else{
							if(!$this->board->isConfirm($parent->password, $parent->uid)){
								echo $this->skin->load($this->skin_name, 'confirm.php', $vars);
							}
							else{
								$allow_document = true;
							}
						}
					}
					else{
						echo $this->skin->load($this->skin_name, 'confirm.php', $vars);
					}
				}
				else{
					$allow_document = true;
				}
			}
			else{
				do_action('kboard_cannot_read_document', 'go_back', $url->set('uid', $content->uid)->set('mod', 'document')->toString(), $content, $board, $this);
			}
		}
		else{
			$allow_document = true;
		}
		
		if($allow_document){
			if(!$this->board->isOrder()){
				if(is_user_logged_in()){
					do_action('kboard_cannot_read_document', 'go_back', $url->set('uid', $content->uid)->set('mod', 'document')->toString(), $content, $board, $this);
				}
				else{
					if($this->meta->view_iframe){
						do_action('kboard_cannot_read_document', 'go_login', wp_login_url($url->getDocumentRedirect($content->uid)), $content, $board, $this);
					}
					else{
						do_action('kboard_cannot_read_document', 'go_login', wp_login_url($_SERVER['REQUEST_URI']), $content, $board, $this);
					}
				}
			}
			else{
				echo $this->skin->load($this->skin_name, 'order.php', $vars);
			}
		}
	}
	
	/**
	 * 주문 완료 페이지를 생성한다.
	 */
	public function builderComplete(){
		$url = new KBUrl();
		$url->setBoard($this->board);
		
		$content = new KBContent($this->board_id);
		$content->initWithUID($this->uid);
		
		if(!$content->uid){
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			exit;
		}
		
		if($content->isTrash()){
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			exit;
		}
		
		if($this->board->isPrivate()){
			if(is_user_logged_in()){
				if(!$content->notice && $content->member_uid != get_current_user_id() && $content->getTopContent()->member_uid != get_current_user_id()){
					echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
					exit;
				}
			}
			else{
				echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
				exit;
			}
		}
		
		$board = $this->board;
		$content->board = $board;
		$board->content = $content;
		
		$order = new KBOrder();
		$order->board = $this->board;
		$order->board_id = $this->board_id;
		
		$vars = array(
			'content' => $content,
			'order' => $order,
			'url' => $url,
			'skin' => $this->skin,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $board,
			'boardBuilder' => $this,
		);
		
		echo $this->skin->load($this->skin_name, 'complete.php', $vars);
	}
	
	/**
	 * 주문 내역 페이지를 생성한다.
	 */
	public function builderHistory(){
		$list = new KBOrderHistory();
		$list->board = $this->board;
		$list->board_id = $this->board_id;
		$list->rpp = $this->rpp;
		$list->page = kboard_pageid();
		$list->setSearchOption(kboard_search_option());
		
		if(is_user_logged_in()){
			$list->initOrder(get_current_user_id());
		}
		else{
			$nonmember_key = '';
			
			if(isset($_SESSION['nonmember_key'][$this->board_id]) && $_SESSION['nonmember_key'][$this->board_id]){
				$nonmember_key = sanitize_text_field($_SESSION['nonmember_key'][$this->board_id]);
			}
			
			$buyer_name = isset($_POST['buyer_name'])?sanitize_text_field($_POST['buyer_name']):'';
			$buyer_eamil = isset($_POST['buyer_eamil'])?sanitize_email($_POST['buyer_eamil']):'';
			$buyer_password = isset($_POST['buyer_password'])?sanitize_text_field($_POST['buyer_password']):'';
			
			if($buyer_name && $buyer_eamil && $buyer_password){
				$nonmember_key = kboard_hash($buyer_eamil, $buyer_name . $buyer_password);
			}
			
			if($nonmember_key){
				$_SESSION['nonmember_key'][$this->board_id] = $nonmember_key;
				$list->initOrderWithKey($nonmember_key);
			}
		}
		
		$url = new KBUrl();
		$url->setBoard($this->board);
		
		$vars = array(
			'list' => $list,
			'url' => $url,
			'skin' => $this->skin,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $this->board,
			'boardBuilder' => $this,
		);
		
		echo $this->skin->load($this->skin_name, 'history.php', $vars);
	}
	
	/**
	 * 판매 내역 페이지를 생성한다.
	 */
	public function builderSales(){
		$url = new KBUrl();
		$url->setBoard($this->board);
		
		if($this->board->isWriter()){
			$list = new KBOrderSales();
			$list->board = $this->board;
			$list->board_id = $this->board_id;
			$list->rpp = $this->rpp;
			$list->page = kboard_pageid();
			if(kboard_start_date() && kboard_end_date()){
				$list->setDateRange(kboard_start_date(), kboard_end_date());
			}
			else{
				$today = date('Ymd', current_time('timestamp'));
				$last_month = date('Ymd', strtotime("{$today} -1 month"));
				$list->setDateRange($last_month, $today);
			}
			$list->setSearchOption(kboard_search_option());
			$list->init(get_current_user_id());
			
			$order = new KBOrder();
			$order->board = $this->board;
			$order->board_id = $this->board_id;
			
			$vars = array(
				'list' => $list,
				'order' => $order,
				'url' => $url,
				'skin' => $this->skin,
				'skin_path' => $this->skin->url($this->skin_name),
				'skin_dir' => $this->skin->dir($this->skin_name),
				'board' => $this->board,
				'boardBuilder' => $this,
			);
			
			echo $this->skin->load($this->skin_name, 'sales.php', $vars);
		}
		else if(is_user_logged_in()){
			echo '<script>alert("'.__('You do not have permission.', 'kboard').'");</script>';
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
		}
		else{
			$login_url = wp_login_url($_SERVER['REQUEST_URI']);
			echo '<script>alert("'.__('You do not have permission.', 'kboard').'");</script>';
			echo "<script>top.window.location.href='{$login_url}';</script>";
		}
	}
	
	/**
	 * 최신 게시물 리스트를 생성한다.
	 * @param boolean $with_notice
	 * @param array $args
	 * @return string
	 */
	public function createLatest($with_notice=true, $args=array()){
		ob_start();
		
		$list = new KBContentList($this->board_id);
		
		if(!is_array($this->board_id) && $this->board->isPrivate()){
			if(is_user_logged_in()){
				$list->memberUID(get_current_user_id());
			}
			else{
				$list->stop = true;
			}
		}
		
		$list->is_latest = true;
		$list->latest = $args;
		$list->category1($this->category1);
		$list->category2($this->category2);
		$list->setSorting($this->sort);
		$list->rpp($this->rpp);
		$list->setWithinDays($this->within_days);
		$list->getList('', '', $with_notice);
		
		$url = new KBUrl();
		$url->setBoard($this->board);
		
		$vars = array(
			'latest' => $args,
			'board_url' => $this->url,
			'list' => $list,
			'url' => $url,
			'skin' => $this->skin,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $this->board,
			'boardBuilder' => $this,
		);
		
		echo $this->skin->load($this->skin_name, 'latest.php', $vars);
		
		return ob_get_clean();
	}
}
?>
<?php
/**
 * KBoard 워드프레스 게시판 생성
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBoardBuilder {

	var $mod;
	var $board_id;
	var $uid;
	var $skin;
	var $skin_name;
	var $category1;
	var $category2;
	var $rpp;
	var $sort;
	var $url;
	var $board;
	var $meta;

	public function __construct($board_id='', $is_latest=false){
		$this->category1 = kboard_category1();
		$this->category2 = kboard_category2();
		$this->uid = kboard_uid();
		$this->sort = 'newest';

		$this->setSkin('default');

		if($board_id) $this->setBoardID($board_id, $is_latest);
	}

	/**
	 * 게시판 뷰(View)를 설정한다. (List/Document/Editor/Remove)
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
			
			wp_localize_script('kboard-script', 'kbaord_current', array('board_id'=>$this->board_id, 'content_uid'=>$this->uid));
			
			// KBoard 미디어 추가
			add_action('media_buttons_context',  'kboard_editor_button');
			add_filter('mce_buttons', 'kboard_register_media_button');
			add_filter('mce_external_plugins', 'kboard_add_media_button');
			
			// font-awesome 출력
			if(!get_option('kboard_fontawesome')){
				global $wp_styles;
				wp_enqueue_style('font-awesome', KBOARD_URL_PATH . '/assets/font-awesome/css/font-awesome.min.css', array(), KBOARD_VERSION);
				wp_enqueue_style('font-awesome-ie7', KBOARD_URL_PATH . '/assets/font-awesome/css/font-awesome-ie7.min.css', array(), KBOARD_VERSION);
				$wp_styles->add_data('font-awesome-ie7', 'conditional', 'lte IE 7');
			}
			
			// Tags Input 등록
			wp_register_style('tagsinput', KBOARD_URL_PATH . '/assets/tagsinput/jquery.tagsinput.css', array(), '1.3.3');
			wp_register_script('tagsinput', KBOARD_URL_PATH . '/assets/tagsinput/jquery.tagsinput.js', array('jquery'), '1.3.3');
			
			// Moment.js 등록
			wp_register_script('moment', KBOARD_URL_PATH . '/assets/moment/moment.js', array('jquery'), '2.17.1');
			
			// jQuery Date Range Picker Plugin 등록
			wp_register_style('daterangepicker', KBOARD_URL_PATH . '/assets/daterangepicker/daterangepicker.css', array(), '0.0.8');
			wp_register_script('daterangepicker', KBOARD_URL_PATH . '/assets/daterangepicker/jquery.daterangepicker.js', array('jquery', 'moment'), '0.0.8');
			
			// jQuery lightSlider 등록
			wp_register_style('lightslider', KBOARD_URL_PATH . '/assets/lightslider/lightslider.css', array(), '1.1.6');
			wp_register_script('lightslider', KBOARD_URL_PATH . '/assets/lightslider/lightslider.js', array('jquery'), '1.1.6');
			
			// 구글 리캡차 등록
			if(kboard_use_recaptcha()){
				wp_register_script('recaptcha', 'https://www.google.com/recaptcha/api.js');
			}
		}
	}

	/**
	 * 페이지당 게시글 개수를 설정한다.
	 * @param int $rpp
	 */
	public function setRpp($rpp){
		$this->rpp = $rpp;
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
		
		$list->rpp($this->rpp)->page(kboard_pageid())->getList(kboard_keyword(), kboard_target());
		return $list;
	}

	/**
	 * 게시판 데이터를 JSON 형식으로 반환한다.
	 */
	public function getJsonList(){
		$list = $this->getList();
		while($content = $list->hasNext()){
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
			$_data['option'] = $content->option;
			$data[] = $_data;
		}
		return kboard_json_encode($data);
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
				return '<iframe id="kboard-iframe-' . $this->board_id . '" src="' . $url->set('kboard_id', $this->board_id)->set('uid', kboard_uid())->set('mod', kboard_mod())->set('category1', kboard_category1())->set('category2', kboard_category2())->set('keyword', kboard_keyword())->set('target', kboard_target())->toString() . '" style="width:100%" scrolling="no" frameborder="0"></iframe>';
			}
				
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
		$vars = array(
				'list' => $this->getList(),
				'url' => new KBUrl(),
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

		$vars = array(
				'list' => $list,
				'depth' => $depth,
				'url' => new KBUrl(),
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
		$content = new KBContent($this->board_id);
		$content->initWithUID($this->uid);

		if(!$content->uid){
			echo '<script>window.location.href="' . $url->set('mod', 'list')->toString() . '";</script>';
			exit;
		}

		if($this->board->isPrivate()){
			if(is_user_logged_in()){
				if($content->member_uid != get_current_user_id() && $content->getTopContent()->member_uid != get_current_user_id()){
					echo '<script>window.location.href="' . $url->set('mod', 'list')->toString() . '";</script>';
					exit;
				}
			}
			else{
				echo '<script>window.location.href="' . $url->set('mod', 'list')->toString() . '";</script>';
				exit;
			}
		}

		$board = $this->board;
		$content->board = $board;
		$board->content = $content;

		$vars = array(
				'content' => $content,
				'url' => $url,
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
				do_action('kboard_cannot_read_document', 'go_back', $url->set('mod', 'list')->toString(), $content, $board, $this);
			}
		}
		else{
			$allow_document = true;
		}

		if($allow_document){
			$content->increaseView();

			// 에디터를 사용하지 않고, autolink가 활성화면 자동으로 link를 생성한다.
			if(!$board->use_editor && $this->meta->autolink){
				include_once KBOARD_DIR_PATH . '/helper/Autolink.helper.php';
				$content->content = nl2br(kboard_autolink($content->getContent()));
				$content->content = preg_replace("/(<(|\/)(table|thead|tbody|tfoot|th|tr|td).*>)(<br \/>)/","\$1", $content->getContent());
			}
			else{
				$content->content = nl2br($content->getContent());
				$content->content = preg_replace("/(<(|\/)(table|thead|tbody|tfoot|th|tr|td).*>)(<br \/>)/","\$1", $content->getContent());
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

			if($board->meta->always_view_list){
				$this->builderList();
			}
		}
	}

	/**
	 * 게시판 에디터 페이지를 생성한다.
	 */
	public function builderEditor(){
		$url = new KBUrl();
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

		$board = $this->board;
		$content->board = $board;
		$board->content = $content;

		$vars = array(
				'content' => $content,
				'url' => $url,
				'skin_path' => $this->skin->url($this->skin_name),
				'skin_dir' => $this->skin->dir($this->skin_name),
				'board' => $board,
				'boardBuilder' => $this,
		);

		$confirm_view = false;
		if(!$this->uid && !$this->board->isWriter()){
			if(wp_get_referer()){
				echo '<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>';
			}
			else{
				echo '<script>alert("'.__('You do not have permission.', 'kboard').'");</script>';
				echo "<script>window.location.href='{$url->set('mod', 'document')->set('uid', $content->uid)->toString()}';</script>";
			}
			exit;
		}
		else if($this->uid && !$this->board->isEditor($content->member_uid)){
			if($this->board->permission_write=='all' && !$content->member_uid){
				if(!$this->board->isConfirm($content->password, $content->uid)){
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
			echo $this->skin->load($this->skin_name, 'confirm.php', $vars);
		}
		else{
			if(!$this->uid){
				// 빈 글이라면 임시저장된 데이터로 초기화 한다.
				$content->initWithTemporary();
			}
				
			// 내용이 없으면 등록된 기본 양식을 가져온다.
			if(!$content->content){
				$content->content = $this->meta->default_content;
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
	 * 게시물 삭제 페이지를 생성한다. (완료 후 바로 리다이렉션)
	 */
	public function builderRemove(){
		$url = new KBUrl();

		if(!wp_get_referer()){
			echo '<script>alert("'.__('This page is restricted from external access.', 'kboard').'");</script>';
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			exit;
		}

		$content = new KBContent($this->board_id);
		$content->initWithUID($this->uid);

		$confirm_view = false;
		if(!$this->board->isEditor($content->member_uid)){
			if($this->board->permission_write=='all' && !$content->member_uid){
				if(!$this->board->isConfirm($content->password, $content->uid, true)){
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
				
			$vars = array(
					'content' => $content,
					'url' => $url,
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
	 * 최신 게시물 리스트를 생성한다.
	 * @return string
	 */
	public function createLatest(){
		ob_start();

		$list = new KBContentList($this->board_id);
		$list->category1($this->category1);
		$list->category2($this->category2);
		$list->setSorting($this->sort);
		$list->rpp($this->rpp)->getList('', '', true);

		$vars = array(
				'board_url' => $this->url,
				'list' => $list,
				'url' => new KBUrl(),
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
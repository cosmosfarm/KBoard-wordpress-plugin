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
	var $category1;
	var $category2;
	var $rpp;
	var $url;
	var $board;
	var $meta;
	
	public function __construct($board_id='', $is_latest=false){
		$_GET['uid'] = isset($_GET['uid'])?intval($_GET['uid']):'';
		$_GET['parent_uid'] = isset($_GET['parent_uid'])?intval($_GET['parent_uid']):'';
		$_GET['pageid'] = isset($_GET['pageid'])?intval($_GET['pageid']):'';
		$_GET['mod'] = isset($_GET['mod'])?addslashes(kboard_xssfilter(kboard_htmlclear($_GET['mod']))):'';
		$_GET['category1'] = isset($_GET['category1'])?addslashes(kboard_xssfilter(kboard_htmlclear($_GET['category1']))):'';
		$_GET['category2'] = isset($_GET['category2'])?addslashes(kboard_xssfilter(kboard_htmlclear($_GET['category2']))):'';
		$_GET['keyword'] = isset($_GET['keyword'])?addslashes(str_replace(array('/', '\\', '"', '\'', ':', '+', '-', '=', '`', '[', ']', '{', '}', '(', ')', '<', '>'), '', kboard_xssfilter(kboard_htmlclear($_GET['keyword'])))):'';
		$_GET['target'] = isset($_GET['target'])?addslashes(kboard_xssfilter(kboard_htmlclear($_GET['target']))):'';
		$_GET['kboard_id'] = isset($_GET['kboard_id'])?intval($_GET['kboard_id']):'';
		
		$_POST['uid'] = isset($_POST['uid'])?intval($_POST['uid']):'';
		$_POST['mod'] = isset($_POST['mod'])?addslashes(kboard_xssfilter(kboard_htmlclear($_POST['mod']))):'';
		
		$uid = $_GET['uid']?$_GET['uid']:$_POST['uid'];
		$mod = $_GET['mod']?$_GET['mod']:$_POST['mod'];
		
		$this->mod = in_array($mod, array('list', 'document', 'editor', 'remove'))?$mod:apply_filters('kboard_default_build_mod', 'list', $board_id);
		$this->category1 = $_GET['category1'];
		$this->category2 = $_GET['category2'];
		$this->uid = $uid;
		$this->skin = 'default';
		
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
		$this->skin = $skin;
	}
	
	/**
	 * 게시판 ID를 설정한다.
	 * @param int $board_id
	 */
	public function setBoardID($board_id, $is_latest=false){
		global $check_kboard_comments_plugin_once;
		$this->meta = new KBoardMeta($board_id);
		$this->board_id = $board_id;
		
		// 소셜댓글 플러그인 생성
		if(!$check_kboard_comments_plugin_once){
			if($this->meta->comments_plugin_id && $this->meta->use_comments_plugin && !is_admin()){
				wp_localize_script('kboard-script', 'cosmosfarm_comments_plugin_id', $this->meta->comments_plugin_id);
				wp_enqueue_script('cosmosfarm-comments-plugin', 'https://plugin.cosmosfarm.com/comments.js', array(), '1.0', true);
				wp_enqueue_script('kboard-comments-plugin', KBOARD_URL_PATH . '/template/js/comments_plugin.js', array(), KBOARD_VERSION, true);
			}
			$check_kboard_comments_plugin_once = true;
		}
		
		if(!$is_latest){
			// 외부 요청을 금지하기 위해서 사용될 게시판 id는 세션에 저장한다.
			$_SESSION['kboard_board_id'] = $this->board_id;
			
			wp_localize_script('kboard-script', 'kbaord_current', array('board_id'=>$this->board_id, 'content_uid'=>$this->uid));
			
			// KBoard 미디어 추가
			add_action('media_buttons_context',  'kboard_editor_button');
			add_filter('mce_buttons', 'kboard_register_media_button');
			add_filter('mce_external_plugins', 'kboard_add_media_button');
		}
	}
	
	/**
	 * 페이지당 게시물 숫자를 설정한다.
	 * @param int $rpp
	 */
	public function setRpp($rpp){
		$this->rpp = $rpp;
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
		$list->rpp($this->rpp)->page($_GET['pageid'])->getList($_GET['keyword'], $_GET['target']);
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
		if($this->meta->view_iframe && !intval($_GET['kboard_id'])){
			$url = new KBUrl();
			return '<iframe id="kboard-iframe-' . $this->board_id . '" src="' . $url->set('kboard_id', $this->board_id)->set('uid', $_GET['uid'])->set('mod', $_GET['mod'])->set('mod', $_GET['mod'])->set('category1', $_GET['category1'])->set('category2', $_GET['category2'])->set('keyword', $_GET['keyword'])->set('target', $_GET['target'])->toString() . '" style="width:100%" scrolling="no" frameborder="0"></iframe>';
		}
		
		if($this->meta->pass_autop == 'enable'){
			call_user_func(array($this, 'builder'.ucfirst($this->mod)));
			return '';
		}
		else{
			ob_start();
			call_user_func(array($this, 'builder'.ucfirst($this->mod)));
			return ob_get_clean();
		}
	}
	
	/**
	 * 게시판 리스트 페이지를 생성한다.
	 */
	public function builderList(){
		global $user_ID;
		
		$url = new KBUrl();
		$list = $this->getList();
		$skin_path = KBOARD_URL_PATH . "/skin/{$this->skin}";
		$board = $this->board;
		$boardBuilder = $this;
		
		include KBOARD_DIR_PATH . "/skin/{$this->skin}/list.php";
	}
	
	/**
	 * 답글 리스트를 생성한다.
	 * @param int $parent_uid
	 */
	public function builderReply($parent_uid, $depth=0){
		$url = new KBUrl();
		$list = new KBContentList();
		$list->getReplyList($parent_uid);
		$skin_path = KBOARD_URL_PATH . "/skin/{$this->skin}";
		$board = $this->board;
		$boardBuilder = $this;
		
		include KBOARD_DIR_PATH . "/skin/{$this->skin}/reply-template.php";
	}
	
	/**
	 * 게시판 본문 페이지를 생성한다.
	 */
	public function builderDocument(){
		global $user_ID;
		
		$url = new KBUrl();
		$content = new KBContent($this->board_id);
		$content->initWithUID($this->uid);
		
		$skin_path = KBOARD_URL_PATH . "/skin/{$this->skin}";
		$board = $this->board;
		$boardBuilder = $this;
		$content->board = $board;
		
		$allow_document = false;
		if(!$this->board->isReader($content->member_uid, $content->secret)){
			if(!$user_ID && $this->board->permission_read!='all'){
				echo '<script>alert("'.__('Please Log in to continue.', 'kboard').'");location.href="' . wp_login_url($_SERVER['REQUEST_URI']) . '";</script>';
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
								include KBOARD_DIR_PATH . "/skin/{$this->skin}/confirm.php";
							}
							else{
								$allow_document = true;
							}
						}
					}
					else{
						include KBOARD_DIR_PATH . "/skin/{$this->skin}/confirm.php";
					}
				}
				else{
					$allow_document = true;
				}
			}
			else{
				echo '<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>';
			}
		}
		else{
			$allow_document = true;
		}
		
		if($allow_document){
			$content->increaseView();
			$content->initWithUID($this->uid);
			
			// 에디터를 사용하지 않고, autolink가 활성화면 자동으로 link를 생성한다.
			if(!$board->use_editor && $this->meta->autolink){
				include_once KBOARD_DIR_PATH . '/helper/Autolink.helper.php';
				$content->content = nl2br(kboard_autolink($content->content));
				$content->content = preg_replace("/(<(|\/)(table|th|tr|td).*>)(<br \/>)/","\$1", $content->content);
			}
			else{
				$content->content = nl2br($content->content);
				$content->content = preg_replace("/(<(|\/)(table|th|tr|td).*>)(<br \/>)/","\$1", $content->content);
			}
			
			// 게시글 숏코드(Shortcode) 실행
			if($this->meta->shortcode_execute == 1){
				$content->content = do_shortcode($content->content);
			}
			
			// kboard_content 필터 실행
			$content->content = apply_filters('kboard_content', $content->content, $content->uid, $this->board_id);
			
			include KBOARD_DIR_PATH . "/skin/{$this->skin}/document.php";
			
			if($board->meta->always_view_list){
				$this->builderList();
			}
		}
	}
	
	/**
	 * 게시판 에디터 페이지를 생성한다.
	 */
	public function builderEditor(){
		global $user_ID;
		
		$url = new KBUrl();
		if($this->board->isWriter() && $this->board->permission_write=='all' && isset($_POST['title']) && $_POST['title']){
			$next_url = $url->set('uid', $this->uid)->set('mod', 'editor')->toString();
			if(!$user_ID && (!isset($_POST['password']) || !$_POST['password'])) die('<script>alert("'.__('Please enter your password.', 'kboard').'");location.href="' . $next_url . '";</script>');
		}
		
		$content = new KBContent();
		$content->initWithUID($this->uid);
		$content->setBoardID($this->board_id);
		
		$skin_path = KBOARD_URL_PATH . "/skin/{$this->skin}";
		$board = $this->board;
		$boardBuilder = $this;
		$content->board = $board;
		
		$confirm_view = false;
		if(!$this->uid && !$this->board->isWriter()){
			die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
		}
		else if($this->uid && !$this->board->isEditor($content->member_uid)){
			if($this->board->permission_write=='all'){
				if(!$this->board->isConfirm($content->password, $content->uid)){
					$confirm_view = true;
				}
			}
			else{
				die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
			}
		}
		
		if($confirm_view){
			include KBOARD_DIR_PATH . "/skin/{$this->skin}/confirm.php";
		}
		else{
			$execute_uid = $content->execute();
			if($execute_uid){
				// 비밀번호가 입력되면 즉시 인증과정을 거친다.
				if($content->password) $this->board->isConfirm($content->password, $execute_uid);
				
				$next_page_url = $url->set('uid', $execute_uid)->set('mod', 'document')->toString();
				$next_page_url = apply_filters('kboard_after_executing_url', $next_page_url, $execute_uid, $this->board_id);
				
				if($content->execute_action == 'insert'){
					if($this->board->meta->conversion_tracking_code){
						echo $meta->conversion_tracking_code;
					}
				}
				echo "<script>location.href='{$next_page_url}';</script>";
				exit;
			}
			else{
				// execute후 POST 데이터를 지우고 다시 초기화 한다.
				$content->initWithUID($this->uid);
					
				// 내용이 없으면 등록된 기본 양식을 가져온다.
				if(!$content->content){
					$content->content = $this->meta->default_content;
				}
					
				// 새로운 답글 쓰기에서만 실행한다.
				if(isset($_GET['parent_uid']) && $_GET['parent_uid'] && !$content->uid && !$content->parent_uid){
					$parent = new KBContent();
					$parent->initWithUID($_GET['parent_uid']);
					
					// 부모 고유번호가 있으면 답글로 등록하기 위해서 부모 고유번호를 등록한다.
					$content->parent_uid = $parent->uid;
					
					// 부모의 제목을 가져온다.
					$content->title = 'Re:' . $parent->title;
					
					// 답글 기본 내용을 설정한다.
					if($this->meta->reply_copy_content=='1'){
						$content->content = $parent->content;
					}
					else if($this->meta->reply_copy_content=='2'){
						$content->content = $this->meta->default_content;
					}
					else{
						$content->content = '';
					}
				}
					
				include KBOARD_DIR_PATH . "/skin/{$this->skin}/editor.php";
			}
		}
	}
	
	/**
	 * 게시물 삭제 페이지를 생성한다. (완료 후 바로 리다이렉션)
	 */
	public function builderRemove(){
		$url = new KBUrl();
		
		if(strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false){
			echo '<script>alert("'.__('This page is restricted from external access.', 'kboard').'");</script>';
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			exit;
		}
		
		$content = new KBContent($this->board_id);
		$content->initWithUID($this->uid);
		
		$confirm_view = false;
		if(!$this->board->isEditor($content->member_uid)){
			if($this->board->permission_write=='all'){
				if(!$this->board->isConfirm($content->password, $content->uid, true)){
					$confirm_view = true;
				}
			}
			else{
				echo '<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>';
			}
		}
		
		if($confirm_view){
			$skin_path = KBOARD_URL_PATH . "/skin/{$this->skin}";
			$board = $this->board;
			include KBOARD_DIR_PATH . "/skin/{$this->skin}/confirm.php";
		}
		else{
			$content->remove();
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
		
		$url = new KBUrl();
		$list = new KBContentList($this->board_id);
		$list->category1($this->category1);
		$list->category2($this->category2);
		$list->setSorting('newest');
		$list->rpp($this->rpp)->getList('', '', true);
		
		$skin_path = KBOARD_URL_PATH . "/skin/{$this->skin}";
		$board = $this->board;
		$board_url = $this->url;
		$boardBuilder = $this;
		
		include KBOARD_DIR_PATH . "/skin/{$this->skin}/latest.php";
		return ob_get_clean();
	}
}
?>
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
	
	private $meta;
	private $skin_path;
	
	public function __construct($board_id=''){
		$_GET['uid'] = intval($_GET['uid']);
		$_GET['pageid'] = intval($_GET['pageid']);
		$_GET['mod'] = kboard_xssfilter(kboard_htmlclear($_GET['mod']));
		$_GET['category1'] = kboard_xssfilter(kboard_htmlclear($_GET['category1']));
		$_GET['category2'] = kboard_xssfilter(kboard_htmlclear($_GET['category2']));
		$_GET['keyword'] = kboard_xssfilter(kboard_htmlclear($_GET['keyword']));
		$_GET['target'] = kboard_xssfilter(kboard_htmlclear($_GET['target']));
		
		$_POST['uid'] = intval($_POST['uid']);
		$_POST['mod'] = kboard_xssfilter(kboard_htmlclear($_POST['mod']));
		
		$uid = $_GET['uid']?$_GET['uid']:$_POST['uid'];
		$mod = $_GET['mod']?$_GET['mod']:$_POST['mod'];
		
		$this->mod = in_array($mod, array('list', 'document', 'editor', 'remove'))?$mod:'list';
		$this->category1 = $_GET['category1'];
		$this->category2 = $_GET['category2'];
		$this->uid = $uid;
		$this->skin = 'default';
		
		if($board_id) $this->setBoardID($board_id);
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
	public function setBoardID($board_id){
		$this->meta = new KBoardMeta($board_id);
		$this->board_id = $board_id;
		
		// ajax 요청에서 사용될 게시판 id는 세션에 저장한다. 외부 요청 불허
		$_SESSION['kboard_board_id'] = $this->board_id;
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
		$data = array();
		while($content = $list->hasNext()){
			$_data['uid'] = $content->uid;
			$_data['member_uid'] = $content->member_uid;
			$_data['member_display'] = $content->member_display;
			$_data['title'] = $content->title;
			$_data['content'] = $content->secret!='true'?$content->content:'';
			$_data['date'] = $content->date;
			$_data['view'] = $content->view;
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
		$userdata = get_userdata($user_ID);
		$url = new KBUrl();
		$list = $this->getList();
		$skin_path = KBOARD_URL_PATH . "/skin/$this->skin";
		$board = $this->board;
		
		include KBOARD_DIR_PATH . "/skin/$this->skin/list.php";
	}
	
	/**
	 * 게시판 본문 페이지를 생성한다.
	 */
	public function builderDocument(){
		global $user_ID;
		$userdata = get_userdata($user_ID);
		$url = new KBUrl();
		
		$content = new KBContent($this->board_id);
		$content->initWithUID($this->uid);
		
		$skin_path = KBOARD_URL_PATH . "/skin/$this->skin";
		$board = $this->board;
		
		if(!$this->board->isReader($content->member_uid, $content->secret) && $content->notice != 'true'){
			if($this->board->permission_write=='all' && ($this->board->permission_read=='all' || $this->board->permission_read=='author')){
				if(!$this->board->isConfirm($content->password, $content->uid)){
					include KBOARD_DIR_PATH . "/skin/$this->skin/confirm.php";
				}
				else{
					$allow_document = true;
				}
			}
			else if(!$user_ID){
				die('<script>alert("'.__('Please Log in to continue.', 'kboard').'");location.href="'.wp_login_url().'";</script>');
			}
			else{
				die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
			}
		}
		else{
			$allow_document = true;
		}
		
		if($allow_document == true){
			$content->increaseView();
			$content->initWithUID($this->uid);
			
			// 에디터를 사용하지 않고, autolink가 활성화면 자동으로 link를 생성한다.
			if(!$board->use_editor && $this->meta->autolink){
				include KBOARD_DIR_PATH . '/helper/Autolink.helper.php';
				$content->content = nl2br(Kboard_autolink($content->content));
				$content->content = preg_replace("/(<(|\/)(table|th|tr|td).*>)(<br \/>)/","\$1", $content->content);
			}
			else{
				$content->content = nl2br($content->content);
				$content->content = preg_replace("/(<(|\/)(table|th|tr|td).*>)(<br \/>)/","\$1", $content->content);
			}
			
			// 게시글 숏코드(Shortcode) 실행
			if($this->meta->shortcode_execute==1){
				$content->content = do_shortcode($content->content);
			}
			
			// kboard_content 필터 실행
			$content->content = apply_filters('kboard_content', $content->content);
			
			include KBOARD_DIR_PATH . "/skin/$this->skin/document.php";
		}
	}
	
	/**
	 * 게시판 에디터 페이지를 생성한다.
	 */
	public function builderEditor(){
		global $user_ID;
		$userdata = get_userdata($user_ID);
		$url = new KBUrl();
		
		if($this->board->isWriter() && $this->board->permission_write=='all' && $_POST['title']){
			$next_url = $url->set('uid', $this->uid)->set('mod', 'editor')->toString();
			if(!$user_ID && !$_POST['password']) die('<script>alert("'.__('Please enter your password.', 'kboard').'");location.href="'.$next_url.'";</script>');
		}
		
		$content = new KBContent($this->board_id);
		$content->initWithUID($this->uid);
		
		$skin_path = KBOARD_URL_PATH . "/skin/$this->skin";
		$board = $this->board;
		
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
			include KBOARD_DIR_PATH . "/skin/$this->skin/confirm.php";
		}
		else{
			$execute_uid = $content->execute();
			if($execute_uid){
				// 비밀번호가 입력되면 즉시 인증과정을 거친다.
				if($content->password) $this->board->isConfirm($content->password, $execute_uid);
				
				$next_url = $url->set('uid', $execute_uid)->set('mod', 'document')->toString();
				die("<script>location.href='$next_url';</script>");
			}
			
			// execute후 POST 데이터를 지우고 다시 초기화 한다.
			$content->initWithUID($this->uid);
			
			// 내용이 없으면 등록된 기본 양식을 가져온다.
			if(!$content->content){
				$content->content = $this->meta->default_content;
			}
			
			include KBOARD_DIR_PATH . "/skin/$this->skin/editor.php";
		}
	}
	
	/**
	 * 게시물 삭제 페이지를 생성한다. (완료 후 바로 리다이렉션)
	 */
	public function builderRemove(){
		if(!stristr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])){
			echo '<script>alert("KBoard : '.__('This page is restricted from external access.', 'kboard').'");</script>';
			return;
		}
		
		$url = new KBUrl();
		$content = new KBContent($this->board_id);
		$content->initWithUID($this->uid);
		
		if(!$this->board->isEditor($content->member_uid)){
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
			$skin_path = KBOARD_URL_PATH . "/skin/$this->skin";
			$board = $this->board;
			include KBOARD_DIR_PATH . "/skin/$this->skin/confirm.php";
		}
		else{
			$content->remove();
			// 삭제뒤 게시판 리스트로 이동한다.
			$next = $url->set('mod', 'list')->toString();
			die("<script>location.href='$next';</script>");
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
		$list->rpp($this->rpp)->getList();
		
		$skin_path = KBOARD_URL_PATH . "/skin/$this->skin";
		$board = $this->board;
		$board_url = $this->url;
		
		include KBOARD_DIR_PATH . "/skin/$this->skin/latest.php";
		return ob_get_clean();
	}
}
?>
<?php
/**
 * KBoard 워드프레스 게시판 생성
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardBuilder {
	
	var $mod;
	var $board_id;
	var $uid;
	var $skin;
	var $category1;
	var $category2;
	var $rpp;
	
	var $url;
	
	var $board;
	
	private $skin_path;
	
	public function __construct($board_id=''){
		$_REQUEST['uid'] = intval($_REQUEST['uid']);
		$_REQUEST['pageid'] = intval($_REQUEST['pageid']);
		$_REQUEST['mod'] = kboard_xssfilter($_REQUEST['mod']);
		$_REQUEST['category1'] = kboard_xssfilter($_REQUEST['category1']);
		$_REQUEST['category2'] = kboard_xssfilter($_REQUEST['category2']);
		$_REQUEST['keyword'] = kboard_xssfilter($_REQUEST['keyword']);
		$_REQUEST['search'] = kboard_xssfilter($_REQUEST['search']);
		
		$this->mod = $_REQUEST['mod']?$_REQUEST['mod']:'list';
		$this->category1 = $_REQUEST['category1'];
		$this->category2 = $_REQUEST['category2'];
		$this->uid = $_REQUEST['uid'];
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
		$this->board_id = $board_id;
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
	 * 게시판 페이지를 생성하고 반환한다.
	 * @return string
	 */
	public function create(){
		$meta = new KBoardMeta($this->board_id);
		if($meta->pass_autop == 'enable'){
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
		$url = new Url();
		
		$list = new ContentList($this->board_id);
		$list->category1($this->category1);
		$list->category2($this->category2);
		
		$list->rpp($this->rpp)->page($_REQUEST['pageid'])->getList($_REQUEST['keyword'], $_REQUEST['search']);
		
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
		$url = new Url();
		
		$content = new Content($this->board_id);
		$content->initWithUID($_REQUEST['uid']);
		
		$skin_path = KBOARD_URL_PATH . "/skin/$this->skin";
		$board = $this->board;
		
		if(!$this->board->isReader($content->member_uid, $content->secret) && $content->notice != 'true'){
			if($this->board->permission_write=='all'){
				if(!$this->board->isConfirm($content->password, $content->uid)){
					include KBOARD_DIR_PATH . "/skin/$this->skin/confirm.php";
				}
				else{
					$allow_document = true;
				}
			}
			else if(!$user_ID){
				die('<script>alert("로그인 하셔야 사용할 수 있습니다.");location.href="'.site_url('/wp-login.php').'";</script>');
			}
			else{
				die('<script>alert("권한이 없습니다.");history.go(-1);</script>');
			}
		}
		else{
			$allow_document = true;
		}
		
		if($allow_document == true){
			$content->increaseView();
			$content->initWithUID($_REQUEST['uid']);
			include KBOARD_DIR_PATH . "/skin/$this->skin/document.php";
		}
	}
	
	/**
	 * 게시판 에디터 페이지를 생성한다.
	 */
	public function builderEditor(){
		global $user_ID;
		$userdata = get_userdata($user_ID);
		$url = new Url();
		
		if($this->board->isWriter() && $this->board->permission_write=='all' && $_POST['title']){
			$next_url = $url->set('uid', $_GET['uid'])->set('mod', 'editor')->toString();
			if(!$user_ID && !$_POST['password']) die('<script>alert("비밀번호를 입력해주세요.");location.href="'.$next_url.'";</script>');
		}
		
		$content = new Content($this->board_id);
		$content->initWithUID($_REQUEST['uid']);
		
		$skin_path = KBOARD_URL_PATH . "/skin/$this->skin";
		$board = $this->board;
		
		if(!$_REQUEST['uid'] && !$this->board->isWriter()){
			die('<script>alert("권한이 없습니다.");history.go(-1);</script>');
		}
		else if($_REQUEST['uid'] && !$this->board->isEditor($content->member_uid)){
			if($this->board->permission_write=='all'){
				if(!$this->board->isConfirm($content->password, $content->uid)){
					$confirm_view = true;
				}
			}
			else{
				die('<script>alert("권한이 없습니다.");history.go(-1);</script>');
			}
		}
		
		if($confirm_view){
			include KBOARD_DIR_PATH . "/skin/$this->skin/confirm.php";
		}
		else{
			$content->execute();
			$content->initWithUID($_REQUEST['uid']);
			
			if($_GET['uid']){
				$next_url = $url->set('uid', $_GET['uid'])->set('mod', 'document')->toString();
			}
			else{
				$next_url = $url->set('pageid', '')->toString();
			}
			
			if(!$content->content){
				$meta = new KBoardMeta($content->board_id);
				$content->content = $meta->default_content;
			}
			
			include KBOARD_DIR_PATH . "/skin/$this->skin/editor.php";
		}
	}
	
	/**
	 * 게시물 삭제 페이지를 생성한다. (완료 후 바로 리다이렉션)
	 */
	public function builderRemove(){
		$url = new Url();
		
		$content = new Content($this->board_id);
		$content->initWithUID($_REQUEST['uid']);
		
		if(!$this->board->isEditor($content->member_uid)){
			if($this->board->permission_write=='all'){
				if(!$this->board->isConfirm($content->password, $content->uid)){
					$confirm_view = true;
				}
			}
			else{
				die('<script>alert("권한이 없습니다.");history.go(-1);</script>');
			}
		}
		
		if($confirm_view){
			$skin_path = KBOARD_URL_PATH . "/skin/$this->skin";
			$board = $this->board;
				
			include KBOARD_DIR_PATH . "/skin/$this->skin/confirm.php";
		}
		else{
			$content->remove($url->set('mod', 'list')->toString());
		}
	}
	
	/**
	 * 최신 게시물 리스트를 생성한다.
	 * @return string
	 */
	public function createLatest(){
		ob_start();
		
		$url = new Url();
		$list = new ContentList($this->board_id);
		$list->rpp($this->rpp)->getList();
		
		$skin_path = KBOARD_URL_PATH . "/skin/$this->skin";
		$board = $this->board;
		$board_url = $this->url;
		
		include KBOARD_DIR_PATH . "/skin/$this->skin/latest.php";
		
		return ob_get_clean();
	}
}
?>
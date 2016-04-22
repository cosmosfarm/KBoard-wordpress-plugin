<?php
/**
 * KBoard 워드프레스 게시판 설정
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBoard {
	
	var $id;
	var $row;
	var $category;
	var $category_row;
	var $userdata;
	var $meta;
	
	public function __construct($id=''){
		global $user_ID;
		$this->row = new stdClass();
		$this->meta = new KBoardMeta();
		$this->userdata = $user_ID?get_userdata($user_ID):new stdClass();
		if(!isset($this->userdata->roles)) $this->userdata->roles = array();
		if(!isset($this->userdata->ID)) $this->userdata->ID = '';
		if(!isset($this->userdata->user_login)) $this->userdata->user_login = '';
		if($id) $this->setID($id);
	}
	
	public function __get($name){
		if(isset($this->row->{$name}) && $this->row->{$name}){
			return $this->row->{$name};
		}
		else{
			return '';
		}
	}
	
	/**
	 * 게시판 아이디값을 입력받는다.
	 * @param int $id
	 * @return KBoard
	 */
	public function setID($id){
		global $wpdb;
		$id = intval($id);
		$this->row = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_board_setting` WHERE `uid`='$id'");
		if(isset($this->row->uid) && $this->row->uid){
			$this->id = $this->row->uid;
			$this->meta = new KBoardMeta($this->row->uid);
		}
		else{
			$this->id = 0;
			$this->meta = new KBoardMeta();
		}
		return $this;
	}
	
	/**
	 * 게시판 아이디값을 반환한다.
	 * @return int
	 */
	public function getID(){
		return $this->id;
	}
	
	/**
	 * 게시판 정보를 입력받는다.
	 * @param object $row
	 * @return KBoard
	 */
	public function initWithRow($row){
		$this->row = $row;
		if(isset($this->row->uid) && $this->row->uid){
			$this->id = $this->row->uid;
			$this->meta = new KBoardMeta($this->row->uid);
		}
		else{
			$this->id = 0;
			$this->meta = new KBoardMeta();
		}
		return $this;
	}
	
	/**
	 * 카테고리 정보를 초기화 한다.
	 */
	public function initCategory1(){
		$this->category = explode(',', $this->category1_list);
		return $this->category1_list;
	}
	
	/**
	 * 두번째 카테코리 정보를 초기화 한다.
	 */
	public function initCategory2(){
		$this->category = explode(',', $this->category2_list);
		return $this->category2_list;
	}
	
	/**
	 * 다음 카테고리 정보를 반환한다.
	 * @return object
	 */
	public function hasNextCategory(){
		if(!$this->category) $this->initCategory1();
		$this->category_row = current($this->category);
		
		if(!$this->category_row) unset($this->category);
		else next($this->category);
		
		return $this->category_row;
	}
	
	/**
	 * 카테고리 정보를 반환한다.
	 */
	public function currentCategory(){
		return $this->category_row;
	}
	
	/**
	 * 게시물의 댓글 폼과 리스트를 생성한다.
	 * @param int $content_uid
	 * @return string
	 */
	public function buildComment($content_uid){
		if($this->id && $content_uid && $this->isComment()){
			if($this->meta->comments_plugin_id && $this->meta->use_comments_plugin){
				$template = new KBTemplate();
				return $template->comments_plugin($this->meta);
			}
			else{
				$args['board'] = $this;
				$args['board_id'] = $this->id;
				$args['content_uid'] = $content_uid;
				$args['skin'] = $this->meta->comment_skin;
				$args['permission_comment_write'] = $this->meta->permission_comment_write;
				return kboard_comments_builder($args);
			}
		}
		return '';
	}
	
	/**
	 * 글 읽기 권한이 있는 사용자인지 확인한다.
	 * @param int $writer_uid
	 * @param string $secret
	 * @return boolean
	 */
	public function isReader($writer_uid, $secret=''){
		$admin_user = array_map(create_function('$string', 'return trim($string);'), explode(',', $this->admin_user));
		
		if($this->permission_read == 'all' && !$secret){
			return true;
		}
		else if($this->userdata->ID){
			if($writer_uid == $this->userdata->ID){
				// 본인인 경우
				return true;
			}
			else if(@in_array('administrator', $this->userdata->roles)){
				// 최고관리자 허용
				return true;
			}
			else if(in_array($this->permission_read, array('all', 'author', 'editor')) && @in_array($this->userdata->user_login, $admin_user)){
				// 선택된 관리자 권한일때, 사용자명과 선택된관리자와 비교후, 일치하면 허용
				return true;
			}
			else if($this->permission_read == 'author' && !$secret){
				// 로그인 사용자 권한일때, role대신 ID값이 있으면, 모든 사용자 허용
				return true;
			}
			else if($this->permission_read == 'roles' && !$secret){
				// 직접선택 권한일때, 선택된 역할의 사용자 허용
				if(array_intersect($this->getReadRoles(), $this->userdata->roles)){
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * 글 쓰기 권한이 있는 사용자인지 확인한다.
	 * @return boolean
	 */
	public function isWriter(){
		$admin_user = array_map(create_function('$string', 'return trim($string);'), explode(',', $this->admin_user));
		
		if($this->permission_write == 'all'){
			return true;
		}
		else if($this->userdata->ID){
			if(@in_array('administrator', $this->userdata->roles)){
				// 최고관리자 허용
				return true;
			}
			else if($this->permission_write == 'editor' && @in_array($this->userdata->user_login, $admin_user)){
				// 선택된 관리자 권한일때, 사용자명과 선택된관리자와 비교후, 일치하면 허용
				return true;
			}
			else if($this->permission_write == 'author'){
				// 로그인 사용자 권한일때, role대신 ID값이 있으면, 모든 사용자 허용
				return true;
			}
			else if($this->permission_write == 'roles'){
				// 직접선택 권한일때, 선택된 역할의 사용자 허용
				if(array_intersect($this->getWriteRoles(), $this->userdata->roles)){
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * 글 수정 권한이 있는 사용자인지 확인한다.
	 * @param int $writer_uid
	 * @return boolean
	 */
	public function isEditor($writer_uid){
		if($this->userdata->ID){
			if($writer_uid == $this->userdata->ID){
				// 본인인 경우
				return true;
			}
			else if($this->isAdmin()){
				// 게시판 관리자 허용
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 게시글 비밀번호와 일치하는지 확인한다.
	 * @param string $password
	 * @param int $content_uid
	 * @param boolean $reauth
	 * @return boolean
	 */
	public function isConfirm($password, $content_uid, $reauth=false){
		if(!$password || !$content_uid) return false;
		
		$submitted_password = isset($_POST['password'])?kboard_htmlclear(trim($_POST['password'])):'';
		
		if($reauth){
			if($submitted_password == $password){
				$_SESSION['kboard_confirm'][$content_uid] = $password;
				return true;
			}
		}
		else if(isset($_SESSION['kboard_confirm']) && isset($_SESSION['kboard_confirm'][$content_uid]) && $_SESSION['kboard_confirm'][$content_uid] == $password){
			return true;
		}
		else if($submitted_password == $password){
			$_SESSION['kboard_confirm'][$content_uid] = $password;
			return true;
		}
		return false;
	}
	
	/**
	 * 관리자인지 확인한다.
	 * @return boolean
	 */
	public function isAdmin(){
		if($this->userdata->ID){
			$admin_user = array_map(create_function('$string', 'return trim($string);'), explode(',', $this->admin_user));
			
			if(@in_array('administrator', $this->userdata->roles)){
				// 최고관리자 허용
				return true;
			}
			else if(@in_array($this->userdata->user_login, $admin_user)){
				// 선택된 관리자 권한일때, 사용자명과 선택된관리자와 비교후, 일치하면 허용
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 댓글 플러그인이 있고, 해당 게시판에서 댓글을 사용하는지 확인한다. 
	 * @return boolean
	 */
	public function isComment(){
		if(defined('KBOARD_COMMNETS_VERSION') && $this->use_comment) return true;
		if($this->meta->comments_plugin_id && $this->meta->use_comments_plugin) return true;
		return false;
	}
	
	/**
	 * 읽기권한의 role을 반환한다.
	 * @return array
	 */
	public function getReadRoles(){
		if($this->meta->permission_read_roles){
			return unserialize($this->meta->permission_read_roles);
		}
		else{
			return array();
		}
	}
	
	/**
	 * 쓰기권한의 role을 반환한다.
	 * @return array
	 */
	public function getWriteRoles(){
		if($this->meta->permission_write_roles){
			return unserialize($this->meta->permission_write_roles);
		}
		else{
			return array();
		}
	}
	
	/**
	 * 댓글쓰기권한의 role을 반환한다.
	 * @return array
	 */
	public function getCommentRoles(){
		if($this->meta->permission_comment_write_roles){
			return unserialize($this->meta->permission_comment_write_roles);
		}
		else{
			return array();
		}
	}
	
	/**
	 * 게시판을 삭제한다.
	 * @param int $board_id
	 */
	public function delete($board_id=''){
		$board_id = intval($board_id);
		if($board_id){
			$this->remove($board_id);
		}
		else if($this->id){
			$this->remove($this->id);
		}
	}
	
	/**
	 * 게시판을 삭제한다.
	 * @param int $board_id
	 */
	public function remove($board_id){
		global $wpdb;
		$board_id = intval($board_id);
		$list = new KBContentList($board_id);
		$list->getAllList();
		while($content = $list->hasNext()){
			$content->remove();
		}
		$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_setting` WHERE `uid`='$board_id'");
		$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_meta` WHERE `board_id`='$board_id'");
	}
	
	/**
	 * 게시판에서 CAPTCHA 사용 여부를 확인한다.
	 */
	public function useCAPTCHA(){
		if(is_user_logged_in() || get_option('kboard_captcha_stop')){
			return false;
		}
		return true;
	}
}
?>
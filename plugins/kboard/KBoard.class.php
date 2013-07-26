<?php
/**
 * KBoard 워드프레스 게시판 설정
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBoard {
	
	var $id;
	
	var $resource;
	var $total;
	var $row;
	
	var $category;
	var $category_row;
	
	var $userdata;
	
	public function __construct($id=''){
		global $user_ID;
		$this->row = new stdClass();
		$this->userdata = get_userdata($user_ID);
		if($id) $this->setID($id);
	}
	
	public function __get($name){
		return $this->row->{$name};
	}
	
	/**
	 * 게시판 아이디값을 입력받는다.
	 * @param int $id
	 * @return KBoard
	 */
	public function setID($id){
		$this->id = $id;
		$resource = kboard_query("SELECT * FROM ".WP_PREFIX."Kboard_board_setting WHERE uid=$id");
		$this->row = mysql_fetch_object($resource);
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
	 * 생성된 게시판들의 리스트를 초기화한다.
	 * @return resource
	 */
	public function getList(){
		$this->resource = kboard_query("SELECT * FROM ".WP_PREFIX."Kboard_board_setting WHERE 1 ORDER BY uid DESC");
		$this->total = reset(mysql_fetch_row(kboard_query("SELECT COUNT(*) FROM ".WP_PREFIX."Kboard_board_setting WHERE 1")));
		return $this->resource;
	}
	
	/**
	 * 생성된 게시판 숫자를 반환한다.
	 * @return int
	 */
	public function getCount(){
		return $this->total;
	}
	
	/**
	 * 다음 게시판 정보를 불러온다.
	 * @return object
	 */
	public function hasNext(){
		if(!$this->resource) $this->getList();
		$this->row = mysql_fetch_object($this->resource);
		return $this->row;
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
	 * 글 읽기 권한이 있는 사용자 인지 확인한다.
	 * @param int $writer_uid
	 * @param string $secret
	 * @return boolean
	 */
	public function isReader($writer_uid, $secret=''){
		$admin_user = array_map(create_function('$string', 'return trim($string);'), explode(',', $this->admin_user));
		
		if($this->permission_read == 'all' && !$secret){
			return true;
		}
		else if($writer_uid == $this->userdata->data->ID && $this->userdata->data->ID){
			// 본인일경우 허용
			return true;
		}
		else if(@in_array('administrator', $this->userdata->roles) || @in_array('editor', $this->userdata->roles)){
			// 최고관리자 허용
			return true;
		}
		else if($this->permission_read == 'editor' && @in_array($this->userdata->data->user_login, $admin_user)){
			// 선택된 관리자 권한일때, 사용자명과 선택된관리자와 비교후, 일치하면 허용
			return true;
		}
		else if($this->permission_read == 'author' && $this->userdata->data->ID && !$secret){
			// 로그인 사용자 권한일때, role대신 ID값이 있으면, 모든 사용자 허용
			return true;
		}
		else{
			return false;
		}
	}
	
	/**
	 * 글 쓰기 권한이 있는 사용자 인지 확인한다.
	 * @return boolean
	 */
	public function isWriter(){
		$admin_user = array_map(create_function('$string', 'return trim($string);'), explode(',', $this->admin_user));
		
		if($this->permission_write == 'all'){
			return true;
		}
		else if(@in_array('administrator', $this->userdata->roles) || @in_array('editor', $this->userdata->roles)){
			// 최고관리자 허용
			return true;
		}
		else if($this->permission_write == 'editor' && @in_array($this->userdata->data->user_login, $admin_user)){
			// 선택된 관리자 권한일때, 사용자명과 선택된관리자와 비교후, 일치하면 허용
			return true;
		}
		else if($this->permission_write == 'author' && $this->userdata->data->ID){
			// 로그인 사용자 권한일때, role대신 ID값이 있으면, 모든 사용자 허용
			return true;
		}
		else{
			return false;
		}
	}
	
	/**
	 * 글 수정 권한이 있는 사용자 인지 확인한다.
	 * @param int $writer_uid
	 * @return boolean
	 */
	public function isEditor($writer_uid){
		$admin_user = array_map(create_function('$string', 'return trim($string);'), explode(',', $this->admin_user));
		
		if($writer_uid == $this->userdata->data->ID && $this->userdata->data->ID){
			// 본인일경우 허용
			return true;
		}
		else if(@in_array('administrator' , $this->userdata->roles) || @in_array('editor', $this->userdata->roles)){
			// 최고관리자 허용
			return true;
		}
		else if(@in_array($this->userdata->data->user_login, $admin_user) && $this->userdata->data->user_login){
			// 선택된 관리자 권한일때, 사용자명과 선택된관리자와 비교후, 일치하면 허용
			return true;
		}
		else{
			return false;
		}
	}
	
	/**
	 * 게시글 비밀번호와 일치하는지 확인한다.
	 * @param string $password
	 * @param int $content_uid
	 * @return boolean
	 */
	public function isConfirm($password, $content_uid){
		if(!$password || $this->permission_write != 'all'){
			return false;
		}
		
		if($_POST['password'] == $password || $_SESSION['kboard_confirm'][$content_uid] == $password){
			$_SESSION['kboard_confirm'][$content_uid] = $password;
			return true;
		}
		else{
			return false;
		}
	}
	
	/**
	 * 관리자인지 확인한다.
	 * @return boolean
	 */
	public function isAdmin(){
		$admin_user = array_map(create_function('$string', 'return trim($string);'), explode(',', $this->admin_user));
		
		if(@in_array('administrator', $this->userdata->roles) || @in_array('editor', $this->userdata->roles)){
			// 최고관리자 허용
			return true;
		}
		else if(@in_array($this->userdata->data->user_login, $admin_user) && $this->userdata->data->user_login){
			// 선택된 관리자 권한일때, 사용자명과 선택된관리자와 비교후, 일치하면 허용
			return true;
		}
		else{
			return false;
		}
	}
	
	/**
	 * 댓글 플러그인이 있고, 해당 게시판에서 댓글을 사용하는지 확인한다. 
	 * @return boolean
	 */
	public function isComment(){
		if(defined('KBOARD_COMMNETS_VERSION') && $this->use_comment) return true;
		else return false;
	}
	
	/**
	 * 게시판을 삭제한다.
	 * @param int $uid
	 */
	public function remove($uid){
		$list = new ContentList($uid);
		$list->getAllList();
		while($content = $list->hasNext()){
			$content->remove();
		}
		kboard_query("DELETE FROM ".WP_PREFIX."Kboard_board_setting WHERE uid=$uid");
		kboard_query("DELETE FROM ".WP_PREFIX."Kboard_board_meta WHERE board_id=$uid");
	}
}
?>
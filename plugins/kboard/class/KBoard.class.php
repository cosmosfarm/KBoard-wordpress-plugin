<?php
/**
 * KBoard 워드프레스 게시판 설정
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBoard {
	
	private $fields;
	
	var $id;
	var $row;
	var $content;
	var $category;
	var $category_row;
	var $tree_category;
	var $current_user;
	var $meta;
	
	public function __construct($id=''){
		$this->row = new stdClass();
		$this->meta = new KBoardMeta();
		$this->fields = null;
		$this->tree_category = new KBoardTreeCategory();
		$this->current_user = wp_get_current_user();
		$this->setID($id);
	}
	
	public function __get($name){
		if(isset($this->row->{$name})){
			return $this->row->{$name};
		}
		return '';
	}
	
	/**
	 * 게시판 아이디값을 입력받는다.
	 * @param int $id
	 * @return KBoard
	 */
	public function setID($id){
		global $wpdb;
		$id = intval($id);
		if($id){
			$this->row = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_board_setting` WHERE `uid`='$id'");
			if(isset($this->row->uid) && $this->row->uid){
				$this->id = $this->row->uid;
				$this->meta = new KBoardMeta($this->row->uid);
				$this->fields = new KBoardFields($this);
				$this->tree_category = new KBoardTreeCategory($this->meta->tree_category);
				return $this;
			}
		}
		$this->id = 0;
		$this->meta = new KBoardMeta();
		$this->fields = null;
		$this->tree_category = new KBoardTreeCategory();
		$wpdb->flush();
		return $this;
	}
	
	/**
	 * 게시판 아이디값을 반환한다.
	 * @return int
	 */
	public function getID(){
		return intval($this->id);
	}
	
	/**
	 * 게시판 아이디값을 반환한다.
	 * @return int
	 */
	public function ID(){
		return intval($this->id);
	}
	
	/**
	 * 게시판 이름을 반환한다.
	 * @return string
	 */
	public function getTitle(){
		return $this->board_name;
	}
	
	/**
	 * 게시판 정보를 입력받는다.
	 * @param object $row
	 * @return KBoard
	 */
	public function initWithRow($row){
		global $wpdb;
		$this->row = $row;
		if(isset($this->row->uid) && $this->row->uid){
			$this->id = $this->row->uid;
			$this->meta = new KBoardMeta($this->row->uid);
			$this->fields = new KBoardFields($this);
			$this->tree_category = new KBoardTreeCategory($this->meta->tree_category);
		}
		else{
			$this->id = 0;
			$this->meta = new KBoardMeta();
			$this->fields = null;
			$this->tree_category = new KBoardTreeCategory();
		}
		$wpdb->flush();
		return $this;
	}
	
	/**
	 * 게시글이 등록된 게시판 정보를 초기화한다.
	 * @param int $content_uid
	 * @return KBoard
	 */
	public function initWithContentUID($content_uid){
		global $wpdb;
		$content_uid = intval($content_uid);
		if($content_uid){
			$this->row = $wpdb->get_row("SELECT `board`.* FROM `{$wpdb->prefix}kboard_board_content` AS `content` LEFT JOIN `{$wpdb->prefix}kboard_board_setting` AS `board` ON `content`.`board_id`=`board`.`uid` WHERE `content`.`uid`='{$content_uid}'");
			if(isset($this->row->uid) && $this->row->uid){
				$this->id = $this->row->uid;
				$this->meta = new KBoardMeta($this->row->uid);
				$this->fields = new KBoardFields($this);
				$this->tree_category = new KBoardTreeCategory($this->meta->tree_category);
				$wpdb->flush();
				return $this;
			}
		}
		$this->id = 0;
		$this->meta = new KBoardMeta();
		$this->fields = null;
		$this->tree_category = new KBoardTreeCategory();
		$wpdb->flush();
		return $this;
	}
	
	/**
	 * 최신글 목록에 추가할 정보를 반환한다.
	 * @return array
	 */
	public function getLatestListColumns(){
		if($this->meta->latest_list_columns){
			$value = trim(stripslashes($this->meta->latest_list_columns));
			$data = @unserialize($value);
			if(is_array($data)){
				return $data;
			}
		}
		return array();
	}
	
	/**
	 * 카테고리1 정보를 초기화 한다.
	 * @return string
	 */
	public function initCategory1(){
		$category1_list = apply_filters('kboard_init_category1_list', $this->category1_list, $this);
		$this->category = explode(',', $category1_list);
		return $category1_list;
	}
	
	/**
	 * 카테코리2 정보를 초기화 한다.
	 * @return string
	 */
	public function initCategory2(){
		$category2_list = apply_filters('kboard_init_category2_list', $this->category2_list, $this);
		$this->category = explode(',', $category2_list);
		return $category2_list;
	}
	
	/**
	 * 카테코리3 정보를 초기화 한다.
	 * @return string
	 */
	public function initCategory3(){
		$category3_list = apply_filters('kboard_init_category2_list', $this->category3_list, $this);
		$this->category = explode(',', $category3_list);
		return $category3_list;
	}
	
	/**
	 * 카테코리4 정보를 초기화 한다.
	 * @return string
	 */
	public function initCategory4(){
		$category4_list = apply_filters('kboard_init_category2_list', $this->category4_list, $this);
		$this->category = explode(',', $category4_list);
		return $category4_list;
	}
	
	/**
	 * 카테코리5 정보를 초기화 한다.
	 * @return string
	 */
	public function initCategory5(){
		$category5_list = apply_filters('kboard_init_category2_list', $this->category5_list, $this);
		$this->category = explode(',', $category5_list);
		return $category5_list;
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
	 * @return string
	 */
	public function currentCategory(){
		return sanitize_text_field($this->category_row);
	}
	
	/**
	 * 게시글의 댓글 폼과 리스트를 생성한다.
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
	 * @param int $user_id
	 * @param string $secret
	 * @return boolean
	 */
	public function isReader($user_id, $secret=''){
		$is_reader = false;
		
		if($this->permission_read == 'all' && !$secret){
			$is_reader = true;
		}
		else if(is_user_logged_in()){
			if($user_id == get_current_user_id()){
				// 본인 허용
				$is_reader = true;
			}
			else if($this->isAdmin()){
				// 게시판 관리자 허용
				$is_reader = true;
			}
			else if($this->permission_read == 'author' && !$secret){
				// 로그인 사용자 허용
				$is_reader = true;
			}
			else if($this->permission_read == 'roles' && !$secret){
				// 선택된 역할의 사용자 허용
				if(array_intersect($this->getReadRoles(), kboard_current_user_roles())){
					$is_reader = true;
				}
			}
		}
		else if(get_option('kboard_allow_search_engines_always_read') && kboard_is_bot()){
			// 검색엔진 검색로봇 허용
			$is_reader = true;
		}
		return apply_filters('kboard_is_reader', $is_reader, $user_id, $secret, $this);
	}
	
	/**
	 * 글 쓰기 권한이 있는 사용자인지 확인한다.
	 * @return boolean
	 */
	public function isWriter(){
		$is_writer = false;
		
		if($this->permission_write == 'all'){
			$is_writer = true;
		}
		else if(is_user_logged_in()){
			if($this->isAdmin()){
				// 게시판 관리자 허용
				$is_writer = true;
			}
			else if($this->permission_write == 'author'){
				// 로그인 사용자 허용
				$is_writer = true;
			}
			else if($this->permission_write == 'roles'){
				// 선택된 역할의 사용자 허용
				if(array_intersect($this->getWriteRoles(), kboard_current_user_roles())){
					$is_writer = true;
				}
			}
		}
		return apply_filters('kboard_is_writer', $is_writer, $this);
	}
	
	/**
	 * 글 수정 권한이 있는 사용자인지 확인한다.
	 * @param int $user_id
	 * @return boolean
	 */
	public function isEditor($user_id){
		$is_editor = false;
		$use_prevent_modify_delete = $this->meta->use_prevent_modify_delete;
		
		if(is_user_logged_in()){
			if(!$use_prevent_modify_delete && $user_id == get_current_user_id()){
				// 본인 허용
				$is_editor = true;
			}
			else if($this->isAdmin()){
				// 게시판 관리자 허용
				$is_editor = true;
			}
		}
		return apply_filters('kboard_is_editor', $is_editor, $user_id, $use_prevent_modify_delete, $this);
	}
	
	/**
	 * 주문하기 권한이 있는 사용자인지 확인한다.
	 * @return boolean
	 */
	public function isOrder(){
		$is_order = false;
		
		if(!$this->meta->permission_order){
			$is_order = true;
		}
		else if(is_user_logged_in()){
			if($this->isAdmin()){
				// 게시판 관리자 허용
				$is_order = true;
			}
			else if($this->meta->permission_order == 'roles'){
				// 선택된 역할의 사용자 허용
				if(array_intersect($this->getOrderRoles(), kboard_current_user_roles())){
					$is_order = true;
				}
			}
			else{
				// 로그인 사용자 허용
				$is_order = true;
			}
		}
		return apply_filters('kboard_is_order', $is_order, $this);
	}
	
	/**
	 * 답글쓰기 권한이 있는 사용자인지 확인한다.
	 * @return boolean
	 */
	public function isReply(){
		$is_reply = false;
		
		if(!$this->meta->permission_reply){
			$is_reply = true;
		}
		else if(is_user_logged_in()){
			if($this->isAdmin()){
				// 게시판 관리자 허용
				$is_reply = true;
			}
			else if($this->meta->permission_reply == 'roles'){
				// 선택된 역할의 사용자 허용
				if(array_intersect($this->getReplyRoles(), kboard_current_user_roles())){
					$is_reply = true;
				}
			}
			else{
				// 로그인 사용자 허용
				$is_reply = true;
			}
		}
		return apply_filters('kboard_is_reply', $is_reply, $this);
	}
	
	public function isBuyer($user_id){
		$is_buyer = false;
		
		if(is_user_logged_in()){
			if($user_id == get_current_user_id()){
				// 본인 허용
				$is_buyer = true;
			}
			else if($this->isAdmin()){
				// 게시판 관리자 허용
				$is_buyer = true;
			}
		}
		return apply_filters('kboard_is_buyer', $is_buyer, $this);
	}
	
	/**
	 * 첨부파일 다운로드 권한이 있는 사용자인지 확인한다.
	 * @return boolean
	 */
	public function isAttachmentDownload(){
		$is_attachment_download = false;
		
		if(!$this->meta->permission_attachment_download){
			$is_attachment_download = true;
		}
		else if(is_user_logged_in()){
			if($this->isAdmin()){
				// 게시판 관리자 허용
				$is_attachment_download = true;
			}
			else if($this->meta->permission_attachment_download == 'roles'){
				// 선택된 역할의 사용자 허용
				if(array_intersect($this->getAttachmentDownloadRoles(), kboard_current_user_roles())){
					$is_attachment_download = true;
				}
			}
			else{
				// 로그인 사용자 허용
				$is_attachment_download = true;
			}
		}
		return apply_filters('kboard_is_attachment_download', $is_attachment_download, $this);
	}
	
	/**
	 * 추천권한이 있는 사용자인지 확인한다.
	 * @return boolean
	 */
	public function isVote(){
		$is_vote = false;
		
		if(!$this->meta->permission_vote){
			$is_vote = true;
		}
		else if(is_user_logged_in()){
			if($this->isAdmin()){
				// 게시판 관리자 허용
				$is_vote = true;
			}
			else if($this->meta->permission_vote == 'roles'){
				// 선택된 역할의 사용자 허용
				if(array_intersect($this->getVoteRoles(), kboard_current_user_roles())){
					$is_vote = true;
				}
			}
			else{
				// 로그인 사용자 허용
				$is_vote = true;
			}
		}
		return apply_filters('kboard_is_vote', $is_vote, $this);
	}
	
	/**
	 * 게시글 비밀번호와 일치하는지 확인한다.
	 * @param string $content_password
	 * @param int $content_uid
	 * @param boolean $reauth
	 * @return boolean
	 */
	public function isConfirm($content_password, $content_uid, $reauth=false){
		$confirm = false;
		$input_password = '';
		
		$reauth = apply_filters('kboard_password_confirm_reauth', $reauth, $this);
		
		if($content_password && $content_uid){
			$input_password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';
			
			if($reauth){
				if($input_password == $content_password){
					$_SESSION['kboard_confirm'][$content_uid] = $content_password;
					$confirm = true;
				}
			}
			else if(isset($_SESSION['kboard_confirm']) && isset($_SESSION['kboard_confirm'][$content_uid]) && $_SESSION['kboard_confirm'][$content_uid] == $content_password){
				$confirm = true;
			}
			else if($input_password == $content_password){
				$_SESSION['kboard_confirm'][$content_uid] = $content_password;
				$confirm = true;
			}
		}
		
		return apply_filters('kboard_password_confirm', $confirm, $input_password, $content_password, $content_uid, $reauth, $this);
	}
	
	/**
	 * 비밀번호 확인에 실패했는지 확인한다.
	 * @return boolean
	 */
	public function isConfirmFailed(){
		$submitted_password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';
		if($submitted_password){
			return true;
		}
		return false;
	}
	
	/**
	 * 관리자인지 확인한다.
	 * @param int $user_id
	 * @return boolean
	 */
	public function isAdmin($user_id=''){
		if($this->id && (is_user_logged_in() || $user_id)){
			$admin_user = explode(',', $this->admin_user);
			$admin_user = array_map('sanitize_text_field', $admin_user);
			
			if($user_id){
				$user = get_userdata($user_id);
			}
			else{
				$user = $this->current_user;
			}
			
			if(in_array('administrator', kboard_current_user_roles($user_id))){
				// 최고관리자 허용
				return true;
			}
			else if(is_array($admin_user) && in_array($user->user_login, $admin_user)){
				// 선택된 관리자 허용
				return true;
			}
			else if(array_intersect($this->getAdminRoles(), kboard_current_user_roles($user_id))){
				// 선택된 역할의 사용자 허용
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
	 * 주문시 포인트를 사용할 수 있는지 확인한다.
	 * @return boolean
	 */
	public function isUsePointOrder(){
		if(class_exists('myCRED_Core')){
			return true;
		}
		return false;
	}
	
	public function isTreeCategoryActive(){
		if($this->use_category && $this->meta->use_tree_category){
			return true;
		}
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
		return array();
	}
	
	/**
	 * 쓰기권한의 role을 반환한다.
	 * @return array
	 */
	public function getWriteRoles(){
		if($this->meta->permission_write_roles){
			return unserialize($this->meta->permission_write_roles);
		}
		return array();
	}
	
	/**
	 * 답글쓰기권한의 role을 반환한다.
	 * @return array
	 */
	public function getReplyRoles(){
		if($this->meta->permission_reply_roles){
			return unserialize($this->meta->permission_reply_roles);
		}
		return array();
	}
	
	/**
	 * 댓글쓰기권한의 role을 반환한다.
	 * @return array
	 */
	public function getCommentRoles(){
		if($this->meta->permission_comment_write_roles){
			return unserialize($this->meta->permission_comment_write_roles);
		}
		return array();
	}
	
	/**
	 * 주문하기권한의 role을 반환한다.
	 * @return array
	 */
	public function getOrderRoles(){
		if($this->meta->permission_order_roles){
			return unserialize($this->meta->permission_order_roles);
		}
		return array();
	}
	
	/**
	 * 관리자권한의 role을 반환한다.
	 * @return array
	 */
	public function getAdminRoles(){
		if($this->meta->permission_admin_roles){
			return unserialize($this->meta->permission_admin_roles);
		}
		return array();
	}
	
	/**
	 * 첨부파일 다운로드 권한의 role을 반환한다.
	 * @return array
	 */
	public function getAttachmentDownloadRoles(){
		if($this->meta->permission_attachment_download_roles){
			return unserialize($this->meta->permission_attachment_download_roles);
		}
		return array();
	}
	
	/**
	 * 추천권한의 role을 반환한다.
	 * @return array
	 */
	public function getVoteRoles(){
		if($this->meta->permission_vote_roles){
			return unserialize($this->meta->permission_vote_roles);
		}
		return array();
	}

	/**
	 * 복사 방지 스크립트를 사용하는지 반환한다.
	 * @return int
	 */
	public function isUsePreventCopy(){
		if($this->meta->prevent_copy){
			return $this->meta->prevent_copy;
		}
		else{
			return get_option('kboard_prevent_copy', '');
		}
		return '';
	}
	
	/**
	 * 게시글 표시 수 제외 옵션을 확인한다.
	 * @return int
	 */
	public function getExceptCountList(){
		global $wpdb;
		$results = '';
		if($this->meta->except_count_type == '1'){//답변 제외
			$results = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE `board_id`='$this->id' AND `parent_uid` = 0 AND `status`!='trash'");
			return intval($results);
		}
		else if($this->meta->except_count_type == '2'){//공지사항 제외
			$results = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE `board_id`='$this->id' AND `notice` != 'true' AND `status`!='trash'");
			return intval($results);
		}
		else if($this->meta->except_count_type == '3'){//답변, 공지사항 제외
			$results = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE `board_id`='$this->id' AND `parent_uid` = 0 AND `notice` != 'true' AND `status`!='trash'");
			return intval($results);
		}
		else if($this->meta->except_count_type == '4'){//글 제목 키워드 설정 제외
			if($this->meta->except_count_type_keyword){
				$results = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE `board_id`='$this->id' AND `title`  NOT LIKE '%{$this->meta->except_count_type_keyword}%' AND `status`!='trash'");
				return intval($results);
			}
			else{
				$results = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE `board_id`='$this->id' AND `status`!='trash'");
				return intval($results);
			}
		}
		return $results;
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
		if($board_id){
			$list = new KBContentList($board_id);
			$list->rpp(1000);
			$list->initFirstList();
			
			while($list->hasNextList()){
				while($content = $list->hasNext()){
					$content->delete(false);
				}
				$list->initFirstList();
			}
			
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_setting` WHERE `uid`='$board_id'");
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_meta` WHERE `board_id`='$board_id'");
			$wpdb->flush();
		}
	}
	
	/**
	 * 모든 게시글을 삭제한다.
	 */
	public function truncate(){
		if($this->id){
			$list = new KBContentList($this->id);
			$list->rpp(1000);
			$list->initFirstList();
			
			while($list->hasNextList()){
				while($content = $list->hasNext()){
					$content->delete(false);
				}
				$list->initFirstList();
			}
			
			$this->resetTotal();
		}
	}
	
	/**
	 * 게시판에서 CAPTCHA 사용 여부를 확인한다.
	 * @return boolean
	 */
	public function useCAPTCHA(){
		if(is_user_logged_in() || get_option('kboard_captcha_stop')){
			return apply_filters('kboard_use_captcha', false, $this);
		}
		return apply_filters('kboard_use_captcha', true, $this);
	}
	
	/**
	 * 게시판에서 비로그인 작성자 입력 필드 보여줄지 확인한다.
	 * @return boolean
	 */
	public function viewUsernameField(){
		if(!is_user_logged_in() || ($this->content->uid && !$this->content->member_uid)){
			return true;
		}
		return false;
	}
	
	/**
	 * 게시판에 등록된 전체 게시글 숫자를 반환한다.
	 * @return int
	 */
	public function getTotal(){
		global $wpdb;
		if(!$this->id){
			return 0;
		}
		if(!$this->meta->total || $this->meta->total<=0){
			$this->meta->total = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE `board_id`='$this->id'");
		}
		return intval($this->meta->total);
	}
	
	/**
	 * 게시판 리스트에 표시되는 게시글 숫자를 반환한다.
	 * @return int
	 */
	public function getListTotal(){
		global $wpdb;
		if(!$this->id){
			return 0;
		}
		
		if($this->meta->except_count_type){
			return $this->getExceptCountList();
		}
		
		if(!$this->meta->list_total || $this->meta->list_total<=0){
			$this->meta->list_total = $this->getTotal();
			
			$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE `board_id`='$this->id' AND `status`='trash'");
			$wpdb->flush();
			
			foreach($results as $row){
				$content = new KBContent();
				$content->initWithRow($row);
				$content->board = $this;
				$content->moveReplyToTrash($content->uid);
			}
		}
		// return intval(apply_filters('kboard_get_list_total', $this->meta->list_total, $this));
		return intval($this->meta->list_total);
	}
	
	/**
	 * 게시글 숫자를 초기화한다.
	 */
	public function resetTotal(){
		if($this->id){
			$this->meta->total = 0;
			$this->meta->list_total = 0;
		}
	}
	
	/**
	 * 본인의 글만 보기로 설정한다.
	 */
	public function setPrivate(){
		$this->row->private = true;
	}
	
	/**
	 * 본인의 글만 보기인지 확인한다.
	 */
	public function isPrivate(){
		if(($this->meta->permission_list || (isset($this->row->private) && $this->row->private == true)) && !$this->isAdmin()){
			return true;
		}
		return false;
	}
	
	/**
	 * 입력된 숫자를 통화 형식으로 반환한다.
	 * @param int $value
	 * @param string $format
	 * @return string
	 */
	public function currency($value, $format='%s원'){
		return sprintf(apply_filters('kboard_currency_format', $format, $this), number_format($value));
	}
	
	/**
	 * 해당 카테고리에 등록된 게시글 숫자를 반환한다.
	 * @param array|string $category
	 * @return int
	 */
	public function getCategoryCount($category){
		global $wpdb;
		if($this->id && $category){
			$where = array();
			$where[] = "`board_id`='{$this->id}'";
			
			if(is_array($category)){
				if(isset($category['category1']) && $category['category1']){
					$category1 = esc_sql($category['category1']);
					$where[] = "`category1`='{$category1}'";
				}
				
				if(isset($category['category2']) && $category['category2']){
					$category2 = esc_sql($category['category2']);
					$where[] = "`category2`='{$category2}'";
				}
			}
			else{
				$category = esc_sql($category);
				$where[] = "(`category1`='{$category}' OR `category2`='{$category}')";
			}
			
			// 휴지통에 없는 게시글만 불러온다.
			$get_list_status_query = kboard_get_list_status_query($this->id);
			if($get_list_status_query){
				$where[] = $get_list_status_query;
			}
			
			$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE " . implode(' AND ', $where));
			$wpdb->flush();
			
			return intval($count);
		}
		return 0;
	}
	
	/**
	 * 사용자가 작성한 개시글 숫자를 반환한다.
	 * @param int $user_id
	 * @return int
	 */
	public function getUserCount($user_id){
		global $wpdb;
		$user_id = intval($user_id);
		if($this->id && $user_id){
			$where = array();
			$where[] = "`board_id`='{$this->id}'";
			$where[] = "`member_uid`='{$user_id}'";
			
			// 휴지통에 없는 게시글만 불러온다.
			$get_list_status_query = kboard_get_list_status_query($this->id);
			if($get_list_status_query){
				$where[] = $get_list_status_query;
			}
			
			$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE " . implode(' AND ', $where));
			$wpdb->flush();
			
			return intval($count);
		}
		return 0;
	}
	
	/**
	 * 사용자가 작성한 마지막 글을 반환한다.
	 * @param int $user_id
	 * @return KBContent
	 */
	public function getLastContentByUser($user_id){
		global $wpdb;
		$content = new KBContent();
		$user_id = intval($user_id);
		
		if($this->id && $user_id){
			$where = array();
			$where[] = "`board_id`='{$this->id}'";
			$where[] = "`member_uid`='{$user_id}'";
			
			// 휴지통에 없는 게시글만 불러온다.
			$get_list_status_query = kboard_get_list_status_query($this->id);
			if($get_list_status_query){
				$where[] = $get_list_status_query;
			}
			
			$uid = $wpdb->get_var("SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` WHERE " . implode(' AND ', $where) . " ORDER BY `date` DESC LIMIT 1");
			$wpdb->flush();
			
			if($uid){
				$content->initWithUID($uid);
			}
		}
		return $content;
	}
	
	/**
	 * 동일한 아이피로 작성된 마지막 글을 반환한다.
	 * @param string $ip
	 * @return KBContent
	 */
	public function getLastContentByIP($ip){
		global $wpdb;
		$content = new KBContent();
		$ip = esc_sql(sanitize_text_field($ip));
		
		if($this->id && $ip){
			$where = array();
			$where[] = "`content`.`board_id`='{$this->id}'";
			$where[] = "`option`.`option_key`='ip'";
			$where[] = "`option`.`option_value`='{$ip}'";
			
			// 휴지통에 없는 게시글만 불러온다.
			$get_list_status_query = kboard_get_list_status_query($this->id, 'content');
			if($get_list_status_query){
				$where[] = $get_list_status_query;
			}
			
			$uid = $wpdb->get_var("SELECT `content`.`uid` FROM `{$wpdb->prefix}kboard_board_content` AS `content` LEFT JOIN `{$wpdb->prefix}kboard_board_option` AS `option` ON `content`.`uid`=`option`.`content_uid` WHERE " . implode(' AND ', $where) . " ORDER BY `content`.`date` DESC LIMIT 1");
			$wpdb->flush();
			
			if($uid){
				$content->initWithUID($uid);
			}
		}
		return $content;
	}
	
	/**
	 * 인기글 이름 정보를 반환한다.
	 * @return string
	 */
	public function getPopularName(){
		$popular_name = '';
		if(!$this->meta->popular_name){
			$popular_name = '인기글';
		}
		else{
			$popular_name = $this->meta->popular_name;
		}
		return $popular_name;
	}
	
	/**
	 * KBoard 커뮤니티에 기여합니다.
	 * @return boolean
	 */
	public function contribution(){
		$contribution = true;
		return apply_filters('kboard_contribution', $contribution, $this);
	}
	
	/**
	 * 필드 클래스를 반환한다.
	 * @return KBoardFields
	 */
	public function fields(){
		if(!$this->fields){
			$this->fields = new KBoardFields($this);
		}
		return $this->fields;
	}
	
	/**
	 * 사이드톡 API로 질문을 전송하고 응답을 가져옵니다.
	 * Cloudflare Worker 환경에 맞춤
	 *
	 * @param {string} question 사용자 질문
	 * @param {string} apiKey Bearer API 키
	 */
	public function sidetalk_request_ai_reply($question, $api_key) {
		if (!$question || !$api_key) {
			return new WP_Error('missing_params', '질문 또는 API 키가 누락되었습니다.');
		}
		
		$url = 'https://workers.sidetalk.ai/v1/chat/completions';
		
		$args = array(
			'timeout' => 30,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $api_key,
			),
			'body' => json_encode(array(
				'message' => $question,
				'stream' => false,
			)),
		);
		
		$response = wp_remote_post($url, $args);

		if (is_wp_error($response)) {
			return $response;
		}
		
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);
		if (!isset($data['success']) || !$data['success'] || !isset($data['message']['content'])) {
			$error_message = isset($data['errors'][0]['message']) ? $data['errors'][0]['message'] : 'AI 응답이 유효하지 않습니다.';
			error_log('[Sidetalk] API 실패 응답: ' . $error_message);
			return '⚠️ 적절한 답변을 찾지 못했습니다. 다시 질문해 주세요.';
		}
		
		$content = trim($data['message']['content']);

		// 응답이 비정상적인 경우 필터링
		if ($content === '' || mb_strlen($content, 'utf-8') < 5 || preg_match('/^[★☆]+$/u', $content)) {
			error_log('[Sidetalk] 응답 내용이 무의미하거나 짧음: ' . $content);
			return '⚠️ AI가 정확한 답변을 생성하지 못했습니다. 다시 질문해 주세요.';
		}
	
		return $data['message']['content'];
	}
	
	/**
	 * 게시글에 AI 자동 답변을 생성합니다.
	 *
	 * @param KBContent $content 게시글 객체
	 * @return void
	 */
	public function sidetalk_generate_post_ai($content) {
		if (!$content || $content->execute_action !== 'insert' || !$this->meta->sidetalk_ai_enable || !$this->meta->sidetalk_api_key || !in_array($this->meta->sidetalk_ai_target, array('post', 'all')) || !intval($this->meta->sidetalk_ai_reply_user_id)
		) { return; }

		$question = $content->title . "\n\n" . strip_tags($content->content);
		$ai_reply = $this->sidetalk_request_ai_reply($question, $this->meta->sidetalk_api_key);
		
		$ai_reply_user_id = intval($this->meta->sidetalk_ai_reply_user_id);
		$author_name = '사이드톡 AI';

		if (!empty($this->meta->sidetalk_ai_reply_author)) {
			$author_name = $this->meta->sidetalk_ai_reply_author;
		} elseif ($ai_reply_user_id > 0) {
			$user = get_userdata($ai_reply_user_id);
			if ($user && !empty($user->display_name)) {
				$author_name = $user->display_name;
			}
		}

		if (!is_wp_error($ai_reply) && $ai_reply) {
			$reply_mode = $this->meta->sidetalk_ai_post_reply_mode;

			if ($reply_mode === 'comment') {
				$comment_list = new KBCommentList($content->uid);
				$comment_list->board = $this;
				$comment_list->add(
					0,
					$ai_reply_user_id,
					$author_name,
					$ai_reply,
					'',
					''
				);
			} else {
				$reply = new KBContent();
				$reply->setBoardID($this->id);
				$reply->parent_uid = $content->uid;
				$reply->member_uid = 1;
				$reply->member_display = $author_name;
				$reply->title = $this->meta->sidetalk_ai_reply_title ?: 'AI 자동 답변';
				$reply->content = $ai_reply;
				$reply->secret = $content->secret === 'yes' ? 'yes' : 'no';
				$reply->execute_action = 'insert';
				$reply->execute();
			}
		}
	}
	
	/**
	 * 댓글에 AI 자동 답변을 생성합니다.
	 *
	 * @param int $parent_comment_uid 부모 댓글 UID
	 * @param string $comment_content 원본 댓글 내용
	 * @return void
	 */
	public function sidetalk_generate_comment_ai($content_uid, $parent_comment_uid, $comment_content) {
		if (!$this->meta->sidetalk_ai_enable || !$this->meta->sidetalk_api_key || !in_array($this->meta->sidetalk_ai_target, array('comment', 'all')) || !intval($this->meta->sidetalk_ai_reply_user_id)
		) { return; }

		$question = strip_tags($comment_content);
		$ai_reply = $this->sidetalk_request_ai_reply($question, $this->meta->sidetalk_api_key);
		$ai_reply_user_id = intval($this->meta->sidetalk_ai_reply_user_id);
		$author_name = '사이드톡 AI';

		if (!empty($this->meta->sidetalk_ai_reply_author)) {
			$author_name = $this->meta->sidetalk_ai_reply_author;
		} elseif ($ai_reply_user_id > 0) {
			$user = get_userdata($ai_reply_user_id);
			if ($user && !empty($user->display_name)) {
				$author_name = $user->display_name;
			}
		}
		
		if (!is_wp_error($ai_reply) && $ai_reply) {
			$comment_list = new KBCommentList($content_uid);
			$comment_list->board = $this;

			$comment_list->add(
				$parent_comment_uid,
				$ai_reply_user_id,
				$author_name,
				$ai_reply,
				'',
				''
			);
		}
	}
}
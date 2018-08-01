<?php
/**
 * KBoard 워드프레스 게시판 설정
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
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
		return $this->id;
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
		return sanitize_text_field($this->category_row);
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
	 * @param int $user_id
	 * @param string $secret
	 * @return boolean
	 */
	public function isReader($user_id, $secret=''){
		if($this->permission_read == 'all' && !$secret){
			return true;
		}
		else if(is_user_logged_in()){
			if($user_id == get_current_user_id()){
				// 본인 허용
				return true;
			}
			else if($this->isAdmin()){
				// 게시판 관리자 허용
				return true;
			}
			else if($this->permission_read == 'author' && !$secret){
				// 로그인 사용자 허용
				return true;
			}
			else if($this->permission_read == 'roles' && !$secret){
				// 선택된 역할의 사용자 허용
				if(array_intersect($this->getReadRoles(), kboard_current_user_roles())){
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
		if($this->permission_write == 'all'){
			return true;
		}
		else if(is_user_logged_in()){
			if($this->isAdmin()){
				// 게시판 관리자 허용
				return true;
			}
			else if($this->permission_write == 'author'){
				// 로그인 사용자 허용
				return true;
			}
			else if($this->permission_write == 'roles'){
				// 선택된 역할의 사용자 허용
				if(array_intersect($this->getWriteRoles(), kboard_current_user_roles())){
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * 글 수정 권한이 있는 사용자인지 확인한다.
	 * @param int $user_id
	 * @return boolean
	 */
	public function isEditor($user_id){
		if(is_user_logged_in()){
			if($user_id == get_current_user_id()){
				// 본인 허용
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
	 * 주문하기 권한이 있는 사용자인지 확인한다.
	 * @return boolean
	 */
	public function isOrder(){
		if(!$this->meta->permission_order){
			return true;
		}
		else if(is_user_logged_in()){
			if($this->isAdmin()){
				// 게시판 관리자 허용
				return true;
			}
			else if($this->meta->permission_order == 'roles'){
				// 선택된 역할의 사용자 허용
				if(array_intersect($this->getOrderRoles(), kboard_current_user_roles())){
					return true;
				}
			}
			else{
				// 로그인 사용자 허용
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 답글쓰기 권한이 있는 사용자인지 확인한다.
	 * @return boolean
	 */
	public function isReply(){
		if(!$this->meta->permission_reply){
			return true;
		}
		else if(is_user_logged_in()){
			if($this->isAdmin()){
				// 게시판 관리자 허용
				return true;
			}
			else if($this->meta->permission_reply == 'roles'){
				// 선택된 역할의 사용자 허용
				if(array_intersect($this->getReplyRoles(), kboard_current_user_roles())){
					return true;
				}
			}
			else{
				// 로그인 사용자 허용
				return true;
			}
		}
		return false;
	}
	
	public function isBuyer($user_id){
		if(is_user_logged_in()){
			if($user_id == get_current_user_id()){
				// 본인 허용
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
	 * 첨부파일 다운로드 권한이 있는 사용자인지 확인한다.
	 * @return boolean
	 */
	public function isAttachmentDownload(){
		if(!$this->meta->permission_attachment_download){
			return true;
		}
		else if(is_user_logged_in()){
			if($this->isAdmin()){
				// 게시판 관리자 허용
				return true;
			}
			else if($this->meta->permission_attachment_download == 'roles'){
				// 선택된 역할의 사용자 허용
				if(array_intersect($this->getAttachmentDownloadRoles(), kboard_current_user_roles())){
					return true;
				}
			}
			else{
				// 로그인 사용자 허용
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 추천권한이 있는 사용자인지 확인한다.
	 * @return boolean
	 */
	public function isVote(){
		if(!$this->meta->permission_vote){
			return true;
		}
		else if(is_user_logged_in()){
			if($this->isAdmin()){
				// 게시판 관리자 허용
				return true;
			}
			else if($this->meta->permission_vote == 'roles'){
				// 선택된 역할의 사용자 허용
				if(array_intersect($this->getVoteRoles(), kboard_current_user_roles())){
					return true;
				}
			}
			else{
				// 로그인 사용자 허용
				return true;
			}
		}
		return false;
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
	 * @return boolean
	 */
	public function isAdmin(){
		if($this->id && is_user_logged_in()){
			$admin_user = explode(',', $this->admin_user);
			$admin_user = array_map('sanitize_text_field', $admin_user);
			
			if(in_array('administrator', kboard_current_user_roles())){
				// 최고관리자 허용
				return true;
			}
			else if(is_array($admin_user) && in_array($this->current_user->user_login, $admin_user)){
				// 선택된 관리자 허용
				return true;
			}
			else if(array_intersect($this->getAdminRoles(), kboard_current_user_roles())){
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
		if(is_user_logged_in() && class_exists('myCRED_Core')){
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
	 * 본인의 글만 보기인지 확인한다.
	 */
	public function isPrivate(){
		if($this->meta->permission_list && !$this->isAdmin()){
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
			
			$where[] = "(`status`='' OR `status` IS NULL OR `status`='pending_approval')";
			
			$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE " . implode(' AND ', $where));
			$wpdb->flush();
			
			return intval($count);
		}
		return 0;
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
}
?>
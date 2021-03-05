<?php
/**
 * KBoard 워드프레스 게시판 댓글
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBComment {
	
	private $abspath;
	
	var $board;
	var $row;
	var $option;
	var $attach;
	var $login_is_required_for_reading;
	var $you_do_not_have_permission_for_reading;
	var $remaining_time_for_reading;
	
	public function __construct(){
		$this->abspath = untrailingslashit(ABSPATH);
		$this->board = new KBoard();
		$this->row = new stdClass();
		$this->option = new KBCommentOption();
	}
	
	public function __get($name){
		$value = '';
		if(isset($this->row->{$name})){
			if($name == 'content'){
				$content = $this->row->{$name};
				$content = apply_filters('kboard_comments_content', $content, $this->row->uid, $this->row->content_uid);
				$content = str_replace('[', '&#91;', $content);
				$content = str_replace(']', '&#93;', $content);
				$value = $content;
			}
			else{
				$value = $this->row->{$name};
			}
		}
		return apply_filters('kboard_comments_value', $value, $name, $this);
	}
	
	public function __set($name, $value){
		$this->row->{$name} = $value;
	}
	
	/**
	 * 댓글 고유번호를 입력받아 정보를 초기화한다.
	 * @param int $uid
	 * @return KBComment
	 */
	public function initWithUID($uid){
		global $wpdb;
		$uid = intval($uid);
		$this->row = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_comments` WHERE `uid`='{$uid}'");
		$this->option = new KBCommentOption($this->uid);
		$this->initAttachedFiles();
		$wpdb->flush();
		return $this;
	}
	
	/**
	 * 댓글 정보를 입력받아 초기화한다.
	 * @param object $comment
	 * @return KBComment
	 */
	public function initWithRow($comment){
		global $wpdb;
		$this->row = $comment;
		$this->option = new KBCommentOption($this->uid);
		$this->initAttachedFiles();
		$wpdb->flush();
		return $this;
	}
	
	/**
	 * 댓글 첨부파일 정보를 초기화 한다.
	 * @return array
	 */
	public function initAttachedFiles(){
		global $wpdb;
		$this->attach = new stdClass();
		if($this->uid){
			$url = new KBCommentUrl($this->uid);
			$url->setBoard($this->getBoard());
			$result = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_attached` WHERE `comment_uid`='{$this->uid}'");
			foreach($result as $row){
				$this->attach->{$row->file_key} = array($row->file_path, $row->file_name, $url->getDownloadURLWithAttach($row->file_key), intval($row->file_size), intval($row->download_count), $row->metadata);
			}
		}
		return $this->attach;
	}
	
	/**
	 * 게시판 정보를 반환한다.
	 * @return KBoard
	 */
	public function getBoard(){
		if(isset($this->board->id) && $this->board->id){
			return $this->board;
		}
		else if($this->content_uid){
			$this->board = new KBoard();
			$this->board->initWithContentUID($this->content_uid);
			return $this->board;
		}
		return new KBoard();
	}
	
	/**
	 * 관리 권한이 있는지 확인한다.
	 * @return boolean
	 */
	public function isEditor(){
		if($this->uid && is_user_logged_in()){
			$board = $this->getBoard();
			if($board->isAdmin()){
				// 게시판 관리자 허용
				return true;
			}
			
			if($this->getUserID() == get_current_user_id()){
				// 본인인 경우 허용
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 보기 권한이 있는지 확인한다.
	 * @return boolean
	 */
	public function isReader(){
		if($this->uid){
			$board = $this->getBoard();
			if($board->isAdmin()){
				// 게시판 관리자 허용
				return true;
			}
			
			if($board->meta->permission_comment_read == 'author'){
				if(is_user_logged_in()){
					return true;
				}
				$this->login_is_required_for_reading = true;
			}
			else if($board->meta->permission_comment_read == 'comment_owner'){
				if(is_user_logged_in()){
					if($this->getUserID() == get_current_user_id()){
						// 본인인 경우 허용
						return true;
					}
					
					$content = new KBContent();
					$content->initWithUID($this->content_uid);
					if($content->isEditor()){
						// 게시글 작성자 허용
						return true;
					}
					$this->you_do_not_have_permission_for_reading = true;
				}
				else{
					$this->login_is_required_for_reading = true;
				}
			}
			else{
				if(!is_user_logged_in() && $board->meta->permission_comment_read_minute){
					$this->remaining_time_for_reading = ($board->meta->permission_comment_read_minute * 60) - (current_time('timestamp') - strtotime($this->created));
					if($this->remaining_time_for_reading <= 0){
						return true;
					}
				}
				else{
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * 댓글 정보를 업데이트한다.
	 */
	public function update(){
		global $wpdb;
		if($this->uid){
			foreach($this->row as $key=>$value){
				if($key == 'uid') continue;
				else if($key == 'user_display' || $key == 'password'){
					$value = sanitize_text_field($value);
				}
				else if($key == 'content'){
					$value = kboard_safeiframe(kboard_xssfilter($value));
				}
				$key = esc_sql(sanitize_key($key));
				$value = esc_sql($value);
				$update[] = "`$key`='$value'";
			}
			
			$wpdb->query("UPDATE `{$wpdb->prefix}kboard_comments` SET ".implode(',', $update)." WHERE `uid`='{$this->uid}'");
			
			// 댓글 수정 액션 훅 실행
			do_action('kboard_comments_update', $this->uid, $this->content_uid, $this->getBoard());
		}
	}
	
	/**
	 * 댓글을 삭제한다.
	 * @param boolean $delete_action
	 */
	public function delete($delete_action=true){
		global $wpdb;
		if($this->uid){
			$board = $this->getBoard();
			
			if($delete_action){
				// 댓글 삭제 액션 훅 실행
				do_action('kboard_comments_delete', $this->uid, $this->content_uid, $board);
					
				// 댓글삭제 증가 포인트
				if($board->meta->comment_delete_up_point && (!$board->meta->point_applied_to || !$board->isAdmin())){
					if($this->user_uid){
						if(function_exists('mycred_add')){
							$point = intval(get_user_meta($this->user_uid, 'kboard_comments_mycred_point', true));
							update_user_meta($this->user_uid, 'kboard_comments_mycred_point', $point + $board->meta->comment_delete_up_point);
				
							mycred_add('comment_delete_up_point', $this->user_uid, $board->meta->comment_delete_up_point, __('Deleted comment increment points', 'kboard-comments'));
						}
					}
				}
					
				// 댓글삭제 감소 포인트
				if($board->meta->comment_delete_down_point && (!$board->meta->point_applied_to || !$board->isAdmin())){
					if($this->user_uid){
						if(function_exists('mycred_add')){
							$point = intval(get_user_meta($this->user_uid, 'kboard_comments_mycred_point', true));
							update_user_meta($this->user_uid, 'kboard_comments_mycred_point', $point + ($board->meta->comment_delete_down_point*-1));
				
							mycred_add('comment_delete_down_point', $this->user_uid, ($board->meta->comment_delete_down_point*-1), __('Deleted comment decrease points', 'kboard-comments'));
						}
					}
				}
			}
			
			// 댓글 정보 삭제
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_comments` WHERE `uid`='{$this->uid}'");
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_comments_option` WHERE `comment_uid`='{$this->uid}'");
			
			// 추천 정보 삭제
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_vote` WHERE `target_uid`='{$this->uid}' AND `target_type`='commemt'");
			
			// 게시글의 댓글 숫자를 변경한다.
			$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `comment`=`comment`-1 WHERE `uid`='{$this->content_uid}'");
			
			$this->deleteAllAttached();
			
			// 미디어 파일을 삭제한다.
			$media = new KBCommentMedia();
			$media->deleteWithCommentUID($this->uid);
			
			// 자식 댓글을 삭제한다.
			$this->deleteChildren();
			
			$wpdb->flush();
		}
	}
	
	/**
	 * 내용을 반환한다.
	 * @return string
	 */
	public function getContent(){
		if(isset($this->row->content)){
			return $this->row->content;
		}
		return '';
	}
	
	/**
	 * 자식 댓글을 삭제한다.
	 * @param int $parent_uid
	 */
	public function deleteChildren($parent_uid=''){
		global $wpdb;
		if($this->uid){
			if($parent_uid){
				$parent_uid = intval($parent_uid);
			}
			else{
				$parent_uid = $this->uid;
			}
			
			$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_comments` WHERE `parent_uid`='{$parent_uid}'");
			foreach($results as $key=>$child){
				$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_comments` WHERE `uid`='{$child->uid}'");
				$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_comments_option` WHERE `comment_uid`='{$child->uid}'");
				
				// 게시글의 댓글 숫자를 변경한다.
				$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `comment`=`comment`-1 WHERE `uid`='{$child->content_uid}'");
				
				$this->deleteAllAttached($child->uid);
				
				// 미디어 파일을 삭제한다.
				$media = new KBCommentMedia();
				$media->deleteWithCommentUID($child->uid);
				
				// 자식 댓글을 삭제한다.
				$this->deleteChildren($child->uid);
			}
		}
	}
	
	/**
	 * 작성자 ID를 반환한다.
	 * @return int
	 */
	public function getUserID(){
		if($this->uid && $this->user_uid){
			return intval($this->user_uid);
		}
		return 0;
	}
	
	/**
	 * 작성자 이름을 반환한다.
	 * @return string
	 */
	public function getUserName(){
		if($this->uid && $this->user_display){
			// 작성자명 마스킹
			$board = $this->getBoard();
			if($board->meta->display_name_masking){
				return kboard_text_masking($this->user_display);
			}
			
			return $this->user_display;
		}
		return '';
	}
	
	/**
	 * 작성자 이름을 반환한다.
	 * @param string $user_display
	 * @return string
	 */
	public function getUserDisplay($user_display=''){
		global $kboard_comment_builder;
		
		if($this->uid){
			if(!$user_display){
				$user_display = sprintf('%s %s', get_avatar($this->getUserID(), 24, '', $this->getUserName()), $this->getUserName());
			}
			
			$user_id = $this->getUserID();
			$user_name = $this->getUserName();
			$type = 'kboard-comments';
			$builder = $kboard_comment_builder;
			
			$user_display = apply_filters('kboard_user_display', $user_display, $user_id, $user_name, $type, $builder);
		}
		return $user_display;
	}
	
	/**
	 * 작성자 이름을 읽을 수 없도록 만든다.
	 * @param string $replace
	 * @return string
	 */
	public function getObfuscateName($replace='*'){
		if($this->uid && $this->user_display){
			$strlen = mb_strlen($this->user_display, 'utf-8');
			
			if($strlen > 3){
				$showlen = 2;
			}
			else{
				$showlen = 1;
			}
			
			$obfuscate_name = mb_substr($this->user_display, 0, $showlen, 'utf-8') . str_repeat($replace, $strlen-$showlen);
			return apply_filters('kboard_obfuscate_name', $obfuscate_name, $this->user_display, $this->getBoard());
		}
		return apply_filters('kboard_obfuscate_name', '', '', $this->getBoard());
	}
	
	/**
	 * 댓글의 모든 첨부파일을 삭제한다.
	 */
	public function deleteAllAttached($comment_uid=''){
		global $wpdb;
		$comment_uid = $comment_uid ? intval($comment_uid) : $this->uid;
		if($comment_uid){
			$result = $wpdb->get_results("SELECT `file_path` FROM `{$wpdb->prefix}kboard_board_attached` WHERE `comment_uid`='$comment_uid'");
			foreach($result as $file){
				kbaord_delete_resize($this->abspath . $file->file_path);
				@unlink($this->abspath . $file->file_path);
			}
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_attached` WHERE `comment_uid`='$comment_uid'");
		}
	}
	
	/**
	 * 첨부파일을 삭제한다.
	 * @param string $key
	 */
	public function deleteAttached($key){
		global $wpdb;
		if($this->uid){
			$key = sanitize_key($key);
			$key = esc_sql($key);
			$file = $wpdb->get_var("SELECT `file_path` FROM `{$wpdb->prefix}kboard_board_attached` WHERE `comment_uid`='$this->uid' AND `file_key`='$key'");
			if($file){
				kbaord_delete_resize($this->abspath . $file);
				@unlink($this->abspath . $file);
				$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_attached` WHERE `comment_uid`='$this->uid' AND `file_key`='$key'");
			}
		}
	}
}
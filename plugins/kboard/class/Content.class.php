<?php
include_once 'KBFileHandler.class.php';

/**
 * KBoard 워드프레스 게시판 게시물
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class Content {
	
	var $next;
	var $board_id;
	
	var $option;
	var $attach;
	
	// 스킨에서 사용 할 첨부파일 input[type=file] 이름의 prefix를 정의한다.
	var $skin_attach_prefix = 'kboard_attach_';
	// 스킨에서 사용 할 사용자 정의 옵션 input, textarea, select 이름의 prefix를 정의한다.
	var $skin_option_prefix = 'kboard_option_';
	
	var $attach_store_path;
	var $thumbnail_store_path;
	
	private $row;
	
	public function __construct($board_id=''){
		if($board_id) $this->setBoardID($board_id);
		$this->row = new stdClass();
		$this->next = $_POST['next'];
	}
	
	public function __get($name){
		return stripslashes($this->row->{$name});
	}
	
	public function __set($name, $value){
		$this->row->{$name} = $value;
	}
	
	/**
	 * 게시판 ID를 입력받는다.
	 * @param int $board_id
	 */
	public function setBoardID($board_id){
		$this->board_id = $board_id;
		
		// 첨부파일 업로드 경로를 만든다.
		$upload_dir = wp_upload_dir();
		$this->attach_store_path = str_replace(KBOARD_WORDPRESS_ROOT, '', $upload_dir['basedir']) . "/kboard_attached/$board_id/" . date("Ym", current_time('timestamp')) . '/';
		$this->thumbnail_store_path = str_replace(KBOARD_WORDPRESS_ROOT, '', $upload_dir['basedir']) . "/kboard_thumbnails/$board_id/" . date("Ym", current_time('timestamp')) . '/';
	}
	
	/**
	 * 게시글 고유번호를 입력받아 정보를 초기화한다.
	 * @param int $uid
	 * @return Content
	 */
	public function initWithUID($uid){
		if($uid){
			$this->row = mysql_fetch_object(kboard_query("SELECT * FROM ".KBOARD_DB_PREFIX."kboard_board_content WHERE uid='$uid' LIMIT 1"));
			$this->board_id = $this->row->board_id;
			$this->initOptions();
			$this->initAttachedFiles();
		}
		return $this;
	}
	
	/**
	 * 게시글 정보를 입력받는다.
	 * @param object $row
	 * @return Content
	 */
	public function initWithRow($row){
		if($row){
			$this->row = $row;
			$this->board_id = $this->row->board_id;
			$this->initOptions();
			$this->initAttachedFiles();
		}
		return $this;
	}
	
	/**
	 * 게시글을 등록/수정한다.
	 */
	public function execute(){
		$this->member_uid = intval($_POST['member_uid']);
		$this->member_display = kboard_xssfilter(kboard_htmlclear(trim($_POST['member_display'])));
		$this->title = kboard_xssfilter(kboard_htmlclear(trim($_POST['title'])));
		$this->content = kboard_xssfilter(trim($_POST['kboard_content']));
		$this->date = $_POST['date'];
		$this->category1 = $_POST['category1'];
		$this->category2 = $_POST['category2'];
		$this->secret = $_POST['secret'];
		$this->notice = $_POST['notice'];
		$this->search = ($this->secret=='true' && $_POST['search']==1)?'2':$_POST['search'];
		$this->password = kboard_xssfilter(kboard_htmlclear(trim($_POST['password'])));
		
		if($this->uid && $this->date){
			// 기존게시물 업데이트
			$this->_updateContent();
			$this->setThumbnail($this->uid);
			$this->update_options($this->uid);
			$this->update_attach($this->uid);
		}
		else if(!$this->uid && $this->title){
			// captcha 코드 확인
			include_once 'KBCaptcha.class.php';
			$captcha = new KBCaptcha();
			if(!$captcha->textCheck($_POST['captcha'])){
				die("<script>alert('보안코드가 올바르지 않습니다. 보안 코드를 입력하세요.');history.go(-1);</script>");
			}
			
			// 신규게시물 등록
			$uid = $this->_insertContent();
			if($uid){
				$this->setThumbnail($uid);
				$this->update_options($uid);
				$this->update_attach($uid);
				
				// 게시판 설정에 알림 이메일이 설정되어 있으면 메일을 보낸다.
				$meta = new KBoardMeta($this->board_id);
				if($meta->latest_alerts){
					include 'KBMail.class.php';
					$mail = new KBMail();
					$mail->to = explode(',', $meta->latest_alerts);
					$mail->title = $data['title'];
					$mail->content = $data['content'];
					$mail->send();
				}
			}
		}
		
		if($this->next) die("<script>location.href='$this->next';</script>");
	}
	
	/**
	 * 게시글을 등록한다.
	 * @return int
	 */
	private function _insertContent(){
		global $user_ID;
		$userdata = get_userdata($user_ID);
		
		$data['board_id'] = $this->board_id;
		$data['member_uid'] = intval($userdata->data->ID);
		$data['member_display'] = $this->member_display?$this->member_display:$userdata->data->display_name;
		$data['title'] = $this->title;
		$data['content'] = $this->content;
		$data['date'] = date("YmdHis", current_time('timestamp'));
		$data['view'] = 0;
		$data['category1'] = $this->category1;
		$data['category2'] = $this->category2;
		$data['secret'] = $this->secret;
		$data['notice'] = $this->notice;
		$data['search'] = $this->search;
		$data['thumbnail_file'] = '';
		$data['thumbnail_name'] = '';
		$data['password'] = $this->password?$this->password:'';
		
		foreach($data AS $key => $value){
			$value = addslashes($value);
			$insert_key[] = "`$key`";
			$insert_data[] = "'$value'";
		}
		
		kboard_query("INSERT INTO ".KBOARD_DB_PREFIX."kboard_board_content (".implode(',', $insert_key).") VALUE (".implode(',', $insert_data).")");
		
		$insert_id = mysql_insert_id();
		if(!$insert_id) list($insert_id) = mysql_fetch_row(kboard_query("SELECT LAST_INSERT_ID()"));
		
		$this->insertPost($content_uid, $data['member_uid']);
		
		return $insert_id;
	}
	
	/**
	 * 게시글 정보를 수정한다.
	 */
	private function _updateContent(){
		if($this->uid){
			$data['board_id'] = $this->board_id;
			$data['member_uid'] = $this->member_uid;
			$data['member_display'] = $this->member_display;
			$data['title'] = $this->title;
			$data['content'] = $this->content;
			$data['date'] = $this->date;
			$data['category1'] = $this->category1;
			$data['category2'] = $this->category2;
			$data['secret'] = $this->secret;
			$data['notice'] = $this->notice;
			$data['search'] = $this->search;
			if($this->password) $data['password'] = $this->password;
			
			foreach($data AS $key => $value){
				$value = addslashes($value);
				$update[] = "`$key`='$value'";
			}
			kboard_query("UPDATE ".KBOARD_DB_PREFIX."kboard_board_content SET ".implode(',', $update)." WHERE uid='$this->uid'");
			
			$post_id = $this->getPostID();
			if($post_id){
				if($this->search==3){
					$this->deletePost($post_id);
				}
				else{
					$this->updatePost($post_id, $data['member_uid']);
				}
			}
			else{
				$this->insertPost($this->uid, $data['member_uid']);
			}
		}
	}
	
	/**
	 * posts 테이블에 내용을 입력한다.
	 * @param int $content_uid
	 * @param int $member_uid
	 */
	public function insertPost($content_uid, $member_uid){
		if($content_uid && $this->search<3){
			$kboard_post = array(
				'post_author'   => $member_uid,
				'post_title'    => $this->title,
				'post_content'  => ($this->secret=='true' || $this->search==2)?'':$this->content,
				'post_status'   => 'publish',
				'comment_status'=> 'closed',
				'ping_status'   => 'closed',
				'post_name'     => $content_uid,
				'post_parent'   => $this->board_id,
				'post_type'     => 'kboard'
			);
			wp_insert_post($kboard_post);
		}
	}
	
	/**
	 * posts 테이블에 내용을 수정한다.
	 * @param int $post_id
	 * @param int $member_uid
	 */
	public function updatePost($post_id, $member_uid){
		if($post_id && $this->search<3){
			$kboard_post = array(
				'ID'            => $post_id,
				'post_author'   => $member_uid,
				'post_title'    => $this->title,
				'post_content'  => ($this->secret=='true' || $this->search==2)?'':$this->content,
				'post_parent'   => $this->board_id
			);
			wp_update_post($kboard_post);
		}
	}
	
	/**
	 * posts 테이블에 내용을 삭제한다.
	 * @param int $post_id
	 */
	public function deletePost($post_id){
		wp_delete_post($post_id);
	}
	
	/**
	 * 게시물의 조회수를 증가한다.
	 */
	public function increaseView(){
		if($this->uid && !@in_array($this->uid, $_SESSION['increased_document_uid'])){
			$_SESSION['increased_document_uid'][] = $this->uid;
			kboard_query("UPDATE ".KBOARD_DB_PREFIX."kboard_board_content SET view=view+1 WHERE uid='$this->uid'");
		}
	}
	
	/**
	 * 게시글 옵션 정보를 초기화 한다.
	 * @return string
	 */
	public function initOptions(){
		if(!$this->uid) return '';
		$option = array();
		$result = kboard_query("SELECT * FROM ".KBOARD_DB_PREFIX."kboard_board_option WHERE content_uid='$this->uid'");
		while($row = mysql_fetch_array($result)){
			$option[$row['option_key']] = stripslashes($row['option_value']);
		}
		$this->option = (object)$option;
		return $option;
	}
	
	/**
	 * 게시글 첨부파일 정보를 초기화 한다.
	 * @return array
	 */
	public function initAttachedFiles(){
		if(!$this->uid) return '';
		$file = array();
		$result = kboard_query("SELECT * FROM ".KBOARD_DB_PREFIX."kboard_board_attached WHERE content_uid='$this->uid'");
		while($row = mysql_fetch_array($result)){
			$file[$row['file_key']] = array($row['file_path'], $row['file_name']);
		}
		$this->attach = (object)$file;
		return $file;
	}
	
	/**
	 * 게시글의 첨부파일을 업데이트한다. (입력/수정)
	 * @param int $uid
	 */
	public function update_attach($uid){
		if(!$this->attach_store_path) die('업로드 경로가 없습니다. 게시판 ID를 입력하고 초기화 해주세요.');
		
		$file = new KBFileHandler();
		$file->setPath($this->attach_store_path);
		
		foreach($_FILES AS $key => $value){
			$key = str_replace($this->skin_attach_prefix, '', $key);
			
			$upload = $file->upload($this->skin_attach_prefix . $key);
			$original_name = $upload['original_name'];
			$file_path = $upload['path'] . $upload['stored_name'];
			
			if($original_name){
				$resource = kboard_query("SELECT file_path FROM ".KBOARD_DB_PREFIX."kboard_board_attached WHERE file_key LIKE '$key' AND content_uid='$uid'");
				$row = mysql_fetch_row($resource);
				$present_file = @reset($row);
				if($present_file){
					unlink(KBOARD_WORDPRESS_ROOT . stripslashes($present_file));
					$this->_update_attach($uid, $key, $file_path, $original_name);
				}
				else{
					$this->_insert_attach($uid, $key, $file_path, $original_name);
				}
			}
		}
	}
	
	/**
	 * 첨부파일 정보를 수정한다.
	 * @param int $uid
	 * @param string $key
	 * @param string $file_path
	 * @param string $file_name
	 */
	private function _update_attach($uid, $key, $file_path, $file_name){
		$key = addslashes($key);
		$file_path = addslashes($file_path);
		$file_name = addslashes($file_name);
		kboard_query("UPDATE ".KBOARD_DB_PREFIX."kboard_board_attached SET file_path='$file_path', file_name='$file_name' WHERE file_key LIKE '$key' AND content_uid='$uid'");
	}
	
	/**
	 * 첨부파일 정보를 등록한다.
	 * @param int $uid
	 * @param string $key
	 * @param string $file_path
	 * @param string $file_name
	 */
	private function _insert_attach($uid, $key, $file_path, $file_name){
		$date = date("YmdHis", current_time('timestamp'));
		$key = addslashes($key);
		$file_path = addslashes($file_path);
		$file_name = addslashes($file_name);
		kboard_query("INSERT INTO ".KBOARD_DB_PREFIX."kboard_board_attached (`content_uid`, `file_key`, `date`, `file_path`, `file_name`) VALUE ('$uid', '$key', '$date', '$file_path', '$file_name')");
	}
	
	/**
	 * 게시글의 모든 첨부파일을 삭제한다.
	 * @param int $uid
	 */
	private function _remove_all_attached($uid){
		$result = kboard_query("SELECT file_path FROM ".KBOARD_DB_PREFIX."kboard_board_attached WHERE content_uid='$uid'");
		while($file = mysql_fetch_row($result)){
			unlink(KBOARD_WORDPRESS_ROOT . stripslashes($file[0]));
		}
		kboard_query("DELETE FROM ".KBOARD_DB_PREFIX."kboard_board_attached WHERE content_uid='$uid'");
	}
	
	/**
	 * 첨부파일을 삭제한다.
	 * @param string $key
	 */
	public function removeAttached($key){
		if($this->uid){
			$key = addslashes($key);
			$file = reset(mysql_fetch_row(kboard_query("SELECT file_path FROM ".KBOARD_DB_PREFIX."kboard_board_attached WHERE file_key LIKE '$key' AND content_uid='$this->uid'")));
			if($file){
				@unlink(KBOARD_WORDPRESS_ROOT . $file);
				$this->_update_attach($this->uid, $key, '', '');
			}
			kboard_query("DELETE FROM ".KBOARD_DB_PREFIX."kboard_board_attached WHERE content_uid='$this->uid' AND file_key LIKE '$key'");
		}
	}
	
	/**
	 * 게시글의 옵션을 업데이트한다. (입력/수정/삭제)
	 * @param int $uid
	 */
	function update_options($uid){
		foreach($_REQUEST AS $key => $value){
			if(strstr($key, $this->skin_option_prefix) && trim($value)){
				
				$key = kboard_htmlclear(str_replace($this->skin_option_prefix, '', addslashes($key)));
				$value = kboard_xssfilter(trim(addslashes($value)));
				
				$present_value = @reset(mysql_fetch_row(kboard_query("SELECT option_value FROM ".KBOARD_DB_PREFIX."kboard_board_option WHERE option_key LIKE '$key' AND content_uid='$uid'")));
				if($present_value){
					$this->_update_option($uid, $key, $value);
				}
				else{
					$this->_insert_option($uid, $key, $value);
				}
			}
		}
		$this->_remove_empty_option();
	}
	
	/**
	 * 옵션 값을 변경한다.
	 * @param int $uid
	 * @param string $key
	 * @param string $value
	 */
	private function _update_option($uid, $key, $value){
		kboard_query("UPDATE ".KBOARD_DB_PREFIX."kboard_board_option SET option_value='$value' WHERE option_key LIKE '$key' AND content_uid='$uid'");
	}
	
	/**
	 * 옵션을 등록한다.
	 * @param int $uid
	 * @param string $key
	 * @param string $value
	 */
	private function _insert_option($uid, $key, $value){
		kboard_query("INSERT INTO ".KBOARD_DB_PREFIX."kboard_board_option (content_uid, option_key, option_value) VALUE ('$uid', '$key', '$value')");
	}
	
	/**
	 * 빈 옵션들을 삭제한다.
	 */
	private function _remove_empty_option(){
		kboard_query("DELETE FROM ".KBOARD_DB_PREFIX."kboard_board_option WHERE option_value=''");
	}
	
	/**
	 * 옵션을 삭제한다.
	 * @param int $uid
	 */
	private function _remove_option($uid){
		kboard_query("DELETE FROM ".KBOARD_DB_PREFIX."kboard_board_option WHERE content_uid='$uid'");
	}
	
	/**
	 * 썸네일을 등록한다.
	 * @param int $uid
	 */
	public function setThumbnail($uid){
		if(!$this->thumbnail_store_path) die('업로드 경로가 없습니다. 게시판 ID를 입력하고 초기화 해주세요.');
		
		$file = new KBFileHandler();
		$file->setPath($this->thumbnail_store_path);
		$upload = $file->upload('thumbnail');
		
		$original_name = $upload['original_name'];
		$file = $upload['path'] . $upload['stored_name'];
		
		if($upload['original_name']){
			$this->removeThumbnail($uid);
			kboard_query("UPDATE ".KBOARD_DB_PREFIX."kboard_board_content SET thumbnail_file='$file', thumbnail_name='$original_name' WHERE uid='$uid'");
		}
	}
	
	/**
	 * 썸네일 파일을 삭제한다.
	 */
	public function removeThumbnail(){
		if($this->uid){
			$result = kboard_query("SELECT * FROM ".KBOARD_DB_PREFIX."kboard_board_content WHERE uid='$this->uid' LIMIT 1");
			$row = mysql_fetch_array($result);
			if($row['thumbnail_file']){
				@unlink(KBOARD_WORDPRESS_ROOT . $row['thumbnail_file']);
				kboard_query("UPDATE ".KBOARD_DB_PREFIX."kboard_board_content SET thumbnail_file='', thumbnail_name='' WHERE uid='$this->uid'");
			}
		}
	}
	
	/**
	 * 게시글을 삭제한다.
	 * @param string $next
	 */
	public function remove($next=''){
		if($this->uid){
			$this->_remove_option($this->uid);
			$this->_remove_all_attached($this->uid);
			$this->removeThumbnail();
			kboard_query("DELETE FROM ".KBOARD_DB_PREFIX."kboard_board_content WHERE uid='$this->uid'");
			$this->deletePost($this->getPostID());
			if(defined('KBOARD_COMMNETS_VERSION')) kboard_query("DELETE FROM ".KBOARD_DB_PREFIX."kboard_comments WHERE content_uid='$this->uid'");
			if($next) die("<script>location.href='$next';</script>");
		}
	}
	
	/**
	 * 게시글의 댓글 개수를 반환한다.
	 * @param string $prefix
	 * @param string $endfix
	 * @return string
	 */
	public function getCommentsCount($prefix='(', $endfix=')'){
		if($this->uid && defined('KBOARD_COMMNETS_VERSION')){
			$commentList = new CommentList($this->uid);
			$commentsCount = $commentList->getCount();
			if($commentsCount) echo "$prefix$commentsCount$endfix";
		}
		return '';
	}
	
	/**
	 * posts 테이블에 등록된 게시글의 ID 값을 가져온다.
	 */
	public function getPostID(){
		if($this->uid){
			$resource = kboard_query("SELECT `ID` FROM `".KBOARD_DB_PREFIX."posts` WHERE post_name='$this->uid' AND post_type='kboard'");
			list($post_id) = mysql_fetch_row($resource);
		}
		return intval($post_id);
	}
	
	/**
	 * 다음 게시물의 UID를 반환한다.
	 */
	public function getNextUID(){
		if($this->uid){
			$resource = kboard_query("SELECT uid FROM ".KBOARD_DB_PREFIX."kboard_board_content WHERE board_id='$this->board_id' AND uid>'$this->uid' ORDER BY uid ASC LIMIT 1");
			list($uid) = mysql_fetch_row($resource);
		}
		return intval($uid);
	}
	
	/**
	 * 이전 게시물의 UID를 반환한다.
	 */
	public function getPrevUID(){
		if($this->uid){
			$resource = kboard_query("SELECT uid FROM ".KBOARD_DB_PREFIX."kboard_board_content WHERE board_id='$this->board_id' AND uid<'$this->uid' ORDER BY uid DESC LIMIT 1");
			list($uid) = mysql_fetch_row($resource);
		}
		return intval($uid);
	}
	
	/**
	 * 본문에 인터넷 주소가 있을때 자동으로 링크를 생성한다.
	 */
	static function autolink($contents){
		// http://yongji.tistory.com/28
		$pattern = "/(http|https|ftp|mms):\/\/[0-9a-z-]+(\.[_0-9a-z-]+)+(:[0-9]{2,4})?\/?"; //domain+port
		$pattern .= "([\.~_0-9a-z-]+\/?)*";// sub roots
		$pattern .= "(\S+\.[_0-9a-z]+)?";// file & extension string
		$pattern .= "(\?[_0-9a-z#%&=\-\+]+)*/i";// parameters
		$replacement = "<a href=\"\\0\" target=\"window.opne(this.href); return false;\">\\0</a>";
		return preg_replace($pattern, $replacement, $contents, -1);
	}
}
?>
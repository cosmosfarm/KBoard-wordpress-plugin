<?php
/**
 * KBoard 게시글
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBContent {
	
	// 스킨에서 사용 할 첨부파일 input[type=file] 이름의 prefix를 정의한다.
	var $skin_attach_prefix = 'kboard_attach_';
	// 스킨에서 사용 할 사용자 정의 옵션 input, textarea, select 이름의 prefix를 정의한다.
	var $skin_option_prefix = 'kboard_option_';
	
	var $board;
	var $board_id;
	var $option;
	var $attach;
	var $attach_store_path;
	var $thumbnail_store_path;
	var $row;
	var $execute_action;
	var $thumbnail;
	
	private $upload_attach_files;
	
	public function __construct($board_id=''){
		$this->row = new stdClass();
		if($board_id) $this->setBoardID($board_id);
	}
	
	public function __get($name){
		if(isset($this->row->{$name}) && $this->row->{$name}){
			return stripslashes($this->row->{$name});
		}
		return '';
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
		$this->attach_store_path = str_replace(KBOARD_WORDPRESS_ROOT, '', $upload_dir['basedir']) . "/kboard_attached/{$board_id}/" . date('Ym', current_time('timestamp')) . '/';
		$this->thumbnail_store_path = str_replace(KBOARD_WORDPRESS_ROOT, '', $upload_dir['basedir']) . "/kboard_thumbnails/{$board_id}/" . date('Ym', current_time('timestamp')) . '/';
	}
	
	/**
	 * 게시글 고유번호를 입력받아 정보를 초기화한다.
	 * @param int $uid
	 * @return KBContent
	 */
	public function initWithUID($uid){
		global $wpdb;
		$uid = intval($uid);
		if($uid){
			$this->row = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE `uid`='$uid' LIMIT 1");
			if($this->row){
				$this->setBoardID($this->row->board_id);
			}
			else{
				$this->row = new stdClass();
			}
		}
		else{
			$this->row = new stdClass();
		}
		$this->initOptions();
		$this->initAttachedFiles();
		$this->execute_action = '';
		return $this;
	}
	
	/**
	 * 게시글 정보를 입력받는다.
	 * @param object $row
	 * @return KBContent
	 */
	public function initWithRow($row){
		if($row){
			$this->row = $row;
			$this->setBoardID($this->row->board_id);
		}
		else{
			$this->row = new stdClass();
		}
		$this->initOptions();
		$this->initAttachedFiles();
		$this->execute_action = '';
		return $this;
	}
	
	/**
	 * 게시글을 등록/수정한다.
	 */
	public function execute(){
		$this->parent_uid = isset($_POST['parent_uid'])?intval($_POST['parent_uid']):0;
		$this->member_uid = isset($_POST['member_uid'])?intval($_POST['member_uid']):0;
		$this->member_display = isset($_POST['member_display'])?kboard_htmlclear(trim($_POST['member_display'])):'';
		$this->title = isset($_POST['title'])?kboard_safeiframe(kboard_xssfilter(trim($_POST['title']))):'';
		$this->content = isset($_POST['kboard_content'])?kboard_safeiframe(kboard_xssfilter(trim($_POST['kboard_content']))):'';
		$this->date = isset($_POST['date'])?kboard_htmlclear(trim($_POST['date'])):'';
		if(isset($_POST['view'])) $this->view = intval($_POST['view']);
		if(isset($_POST['comment'])) $this->comment = intval($_POST['comment']);
		if(isset($_POST['like'])) $this->like = intval($_POST['like']);
		if(isset($_POST['unlike'])) $this->unlike = intval($_POST['unlike']);
		if(isset($_POST['vote'])) $this->vote = intval($_POST['vote']);
		$this->category1 = isset($_POST['category1'])?kboard_htmlclear(trim($_POST['category1'])):'';
		$this->category2 = isset($_POST['category2'])?kboard_htmlclear(trim($_POST['category2'])):'';
		$this->secret = isset($_POST['secret'])?kboard_htmlclear(trim($_POST['secret'])):'';
		$this->notice = isset($_POST['notice'])?kboard_htmlclear(trim($_POST['notice'])):'';
		$this->search = isset($_POST['wordpress_search'])?intval(($this->secret && $_POST['wordpress_search']==1)?'2':$_POST['wordpress_search']):'3';
		$this->password = isset($_POST['password'])?kboard_htmlclear(trim($_POST['password'])):'';
		
		if($this->uid && $this->date){
			// 기존게시물 업데이트
			$this->initUploadAttachFiles();
			$this->updateContent();
			$this->setThumbnail();
			$this->updateOptions();
			$this->updateAttach();
			$this->addMediaRelationships();
			
			// 게시글 수정 액션 훅 실행
			do_action('kboard_document_update', $this->uid, $this->board_id);
			
			$this->execute_action = 'update';
			
			return $this->uid;
		}
		else if(!$this->uid && $this->title){
			$board = $this->getBoard();
			
			if($board->useCAPTCHA()){
				// captcha 코드 확인
				include_once 'KBCaptcha.class.php';
				$captcha = new KBCaptcha();
				$captcha_text = isset($_POST['captcha'])?$_POST['captcha']:'';
				if(!$captcha->textCheck($captcha_text)){
					die("<script>alert('".__('The CAPTCHA code is not valid. Please enter the CAPTCHA code.', 'kboard')."');history.go(-1);</script>");
				}
			}
			
			// 신규게시물 등록
			$this->initUploadAttachFiles();
			if($this->insertContent()){
				$this->setThumbnail();
				$this->updateOptions();
				$this->updateAttach();
				$this->addMediaRelationships();
				
				// 게시판 설정에 알림 이메일이 설정되어 있으면 메일을 보낸다.
				if($board->meta->latest_alerts){
					include_once 'KBMail.class.php';
					/*
					 * http://www.cosmosfarm.com/threads/document/3025
					 * 메일 제목에 게시글이 등록된 게시판 이름 추가해서 보낸다.
					 */
					$url = new KBUrl();
					$mail = new KBMail();
					$mail->to = explode(',', $board->meta->latest_alerts);
					$mail->title = '['.__('KBoard new document', 'kboard').'] '.$board->board_name.' - '.$this->title;
					$mail->content = $this->content;
					$mail->url = $url->getDocumentRedirect($this->uid);
					$mail->send();
				}
				
				// 게시글 입력 액션 훅 실행
				do_action('kboard_document_insert', $this->uid, $this->board_id);
			}
			
			$this->execute_action = 'insert';
			
			return $this->uid;
		}
		return '';
	}
	
	/**
	 * 게시글을 등록한다.
	 * @return int
	 */
	public function insertContent(){
		global $user_ID, $wpdb;
		
		if($user_ID){
			$userdata = get_userdata($user_ID);
			$member_uid = $userdata->data->ID;
			$member_display = $this->member_display?$this->member_display:$userdata->data->display_name;
		}
		else{
			$member_uid = 0;
			$member_display = $this->member_display;
		}
		
		$data['board_id'] = $this->board_id;
		$data['parent_uid'] = $this->parent_uid?$this->parent_uid:0;
		$data['member_uid'] = $member_uid;
		$data['member_display'] = $member_display;
		$data['title'] = $this->title;
		$data['content'] = $this->content;
		$data['date'] = date('YmdHis', current_time('timestamp'));
		$data['update'] = date('YmdHis', current_time('timestamp'));
		$data['view'] = 0;
		$data['comment'] = 0;
		$data['like'] = 0;
		$data['unlike'] = 0;
		$data['vote'] = 0;
		$data['category1'] = $this->category1;
		$data['category2'] = $this->category2;
		$data['secret'] = $this->secret;
		$data['notice'] = $this->notice;
		$data['search'] = $this->search;
		$data['thumbnail_file'] = '';
		$data['thumbnail_name'] = '';
		$data['password'] = $this->password?$this->password:'';
		
		/*
		 * 입력할 데이터 필터
		 */
		$data = apply_filters('kboard_insert_data', $data, $this->board_id);
		
		foreach($data as $key=>$value){
			$value = esc_sql($value);
			$insert_key[] = "`$key`";
			$insert_data[] = "'$value'";
		}
		
		$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_board_content` (".implode(',', $insert_key).") VALUE (".implode(',', $insert_data).")");
		$this->uid = $wpdb->insert_id;
		
		$this->insertPost($this->uid, $data['member_uid']);
		
		return $this->uid;
	}
	
	/**
	 * 게시글 정보를 수정한다.
	 */
	public function updateContent(){
		global $wpdb;
		if($this->uid){
			$data['board_id'] = $this->board_id;
			$data['parent_uid'] = $this->parent_uid?$this->parent_uid:0;
			$data['member_uid'] = $this->member_uid;
			$data['member_display'] = $this->member_display;
			$data['title'] = $this->title;
			$data['content'] = $this->content;
			$data['date'] = $this->date;
			$data['update'] = date('YmdHis', current_time('timestamp'));
			$data['view'] = $this->view;
			$data['comment'] = $this->comment;
			$data['like'] = $this->like;
			$data['unlike'] = $this->unlike;
			$data['vote'] = $this->vote;
			$data['category1'] = $this->category1;
			$data['category2'] = $this->category2;
			$data['secret'] = $this->secret;
			$data['notice'] = $this->notice;
			$data['search'] = $this->search;
			if($this->password) $data['password'] = $this->password;
			
			/*
			 * 수정할 데이터 필터
			 */
			$data = apply_filters('kboard_update_data', $data, $this->board_id);
			
			foreach($data as $key=>$value){
				$value = esc_sql($value);
				$update[] = "`$key`='$value'";
			}
			$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET ".implode(',', $update)." WHERE `uid`='$this->uid'");
			
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
		if($content_uid && $this->search>0 && $this->search<3){
			$kboard_post = array(
				'post_author'   => $member_uid,
				'post_title'    => $this->title,
				'post_content'  => ($this->secret || $this->search==2)?'':$this->content,
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
		if($post_id && $this->search>0 && $this->search<3){
			$kboard_post = array(
				'ID'            => $post_id,
				'post_author'   => $member_uid,
				'post_title'    => $this->title,
				'post_content'  => ($this->secret || $this->search==2)?'':$this->content,
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
		global $wpdb;
		if($this->uid && !@in_array($this->uid, $_SESSION['increased_document_uid'])){
			$_SESSION['increased_document_uid'][] = $this->uid;
			$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `view`=`view`+1 WHERE `uid`='$this->uid'");
		}
	}
	
	/**
	 * 게시글 옵션 정보를 초기화 한다.
	 * @return string
	 */
	public function initOptions(){
		global $wpdb;
		$this->option = new KBContentOption($this->uid);
	}
	
	/**
	 * 게시글 첨부파일 정보를 초기화 한다.
	 * @return array
	 */
	public function initAttachedFiles(){
		global $wpdb;
		if(!$this->uid) return '';
		$file = array();
		$result = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_uid`='$this->uid'", ARRAY_A);
		foreach($result as $row){
			$file[$row['file_key']] = array($row['file_path'], $row['file_name']);
		}
		$this->attach = (object)$file;
		return $file;
	}
	
	/**
	 * 첨부파일을 초기화한다.
	 */
	public function initUploadAttachFiles(){
		global $wpdb;
		if(!$this->attach_store_path) die(__('No upload path. Please enter board ID and initialize.', 'kboard'));
		
		// 업로드된 파일이 있는지 확인한다. (없으면 중단)
		$upload_checker = false;
		foreach($_FILES as $key=>$value){
			if(strpos($key, $this->skin_attach_prefix) === false) continue;
			if($_FILES[$key]['tmp_name']){
				$upload_checker = true;
				break;
			}
		}
		
		if($upload_checker){
			$file = new KBFileHandler();
			$file->setPath($this->attach_store_path);
			
			foreach($_FILES as $key=>$value){
				if(strpos($key, $this->skin_attach_prefix) === false) continue;
				$key = str_replace($this->skin_attach_prefix, '', $key);
			
				$upload = $file->upload($this->skin_attach_prefix . $key);
				$original_name = $upload['original_name'];
				$file_path = $upload['path'] . $upload['stored_name'];
			
				if($original_name){
					$attach_file = new stdClass();
					$attach_file->key = $key;
					$attach_file->path = $file_path;
					$attach_file->name = $original_name;
					$this->upload_attach_files[] = $attach_file;
				}
			}
		}
	}
	
	/**
	 * 게시글의 첨부파일을 업데이트한다. (입력/수정)
	 */
	public function updateAttach(){
		global $wpdb;
		if(!$this->attach_store_path) die(__('No upload path. Please enter board ID and initialize.', 'kboard'));
		
		if($this->uid && $this->upload_attach_files && is_array($this->upload_attach_files)){
			foreach($this->upload_attach_files as $attach_file){
				$key = esc_sql($attach_file->key);
				$file_path = esc_sql($attach_file->path);
				$file_name = esc_sql($attach_file->name);
				
				$present_file = $wpdb->get_var("SELECT `file_path` FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_uid`='$this->uid' AND `file_key`='$key'");
				if($present_file){
					@unlink(KBOARD_WORDPRESS_ROOT . stripslashes($present_file));
					$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_attached` SET `file_path`='$file_path', `file_name`='$file_name' WHERE `content_uid`='$this->uid' AND `file_key`='$key'");
				}
				else{
					$date = date('YmdHis', current_time('timestamp'));
					$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_board_attached` (`content_uid`, `file_key`, `date`, `file_path`, `file_name`) VALUE ('$this->uid', '$key', '$date', '$file_path', '$file_name')");
				}
			}
		}
	}
	
	/**
	 * 게시글의 모든 첨부파일을 삭제한다.
	 */
	private function _deleteAllAttached(){
		global $wpdb;
		if($this->uid){
			$result = $wpdb->get_results("SELECT `file_path` FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_uid`='$this->uid'");
			foreach($result as $file){
				kbaord_delete_resize(KBOARD_WORDPRESS_ROOT . stripslashes($file->file_path));
				@unlink(KBOARD_WORDPRESS_ROOT . stripslashes($file->file_path));
			}
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_uid`='$this->uid'");
		}
	}
	
	/**
	 * 첨부파일을 삭제한다.
	 * @param string $key
	 */
	public function removeAttached($key){
		global $wpdb;
		if($this->uid){
			$key = esc_sql($key);
			$file = $wpdb->get_var("SELECT `file_path` FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_uid`='$this->uid' AND `file_key`='$key'");
			if($file){
				kbaord_delete_resize(KBOARD_WORDPRESS_ROOT . stripslashes($file));
				@unlink(KBOARD_WORDPRESS_ROOT . stripslashes($file));
				$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_uid`='$this->uid' AND `file_key`='$key'");
			}
		}
	}
	
	/**
	 * 게시글의 옵션을 업데이트한다. (입력/수정/삭제)
	 */
	function updateOptions(){
		global $wpdb;
		if($this->uid){
			$this->option = new KBContentOption($this->uid);
			foreach($_POST as $key=>$value){
				$key = trim($key);
				$value = trim($value);
				if(strpos($key, $this->skin_option_prefix) !== false){
					$key = kboard_htmlclear(str_replace($this->skin_option_prefix, '', $key));
					$value = kboard_safeiframe(kboard_xssfilter($value));
					$this->option->{$key} = $value;
				}
			}
		}
	}
	
	/**
	 * 옵션을 삭제한다.
	 */
	private function _deleteAllOptions(){
		global $wpdb;
		if($this->uid){
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_option` WHERE `content_uid`='$this->uid'");
		}
	}
	
	/**
	 * 썸네일을 등록한다.
	 */
	public function setThumbnail(){
		global $wpdb;
		if(!$this->thumbnail_store_path) die(__('No upload path. Please enter board ID and initialize.', 'kboard'));
		
		if($this->uid && $_FILES['thumbnail']['tmp_name']){
			$file = new KBFileHandler();
			$file->setPath($this->thumbnail_store_path);
			$upload = $file->upload('thumbnail');
			$original_name = esc_sql($upload['original_name']);
			$file = esc_sql($upload['path'] . $upload['stored_name']);
			
			if($original_name){
				
				// 업로드된 원본 이미지 크기를 줄인다.
				$upload_dir = wp_upload_dir();
				$file_path = explode('/wp-content/uploads', $upload['path'] . $upload['stored_name']);
				$file_path = strtolower($upload_dir['basedir'] . end($file_path));
				$image_editor = wp_get_image_editor($file_path);
				if(!is_wp_error($image_editor)){
					$thumbnail_size = apply_filters('kboard_thumbnail_size', array(1200, 1200));
					$image_editor->resize($thumbnail_size[0], $thumbnail_size[0]);
					$image_editor->save($file_path);
				}
				
				$this->removeThumbnail(false);
				$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `thumbnail_file`='$file', `thumbnail_name`='$original_name' WHERE `uid`='$this->uid'");
			}
		}
	}
	
	/**
	 * 썸네일 주소를 반환한다.
	 * @param int $width
	 * @param int $height
	 * @return string
	 */
	public function getThumbnail($width='', $height=''){
		if(isset($this->thumbnail["{$width}x{$height}"]) && $this->thumbnail["{$width}x{$height}"]){
			return $this->thumbnail["{$width}x{$height}"];
		}
		else if($this->thumbnail_file){
			if($width && $height){
				$this->thumbnail["{$width}x{$height}"] = kboard_resize($this->thumbnail_file, $width, $height);
			}
			else{
				$this->thumbnail["{$width}x{$height}"] = site_url($this->thumbnail_file);
			}
			return $this->thumbnail["{$width}x{$height}"];
		}
		else if($this->uid){
			$media = new KBContentMedia();
			$media->content_uid = $this->uid;
			foreach($media->getList() as $media_item){
				if(isset($media_item->file_path) && $media_item->file_path){
					if($width && $height){
						$this->thumbnail["{$width}x{$height}"] = kboard_resize($media_item->file_path, $width, $height);
					}
					else{
						$this->thumbnail["{$width}x{$height}"] = site_url($media_item->file_path);
					}
					return $this->thumbnail["{$width}x{$height}"];
				}
			}
			foreach($this->attach as $attach){
				$extension = strtolower(pathinfo($attach[0], PATHINFO_EXTENSION));
				if(in_array($extension, array('gif','jpg','jpeg','png'))){
					if($width && $height){
						$this->thumbnail["{$width}x{$height}"] = kboard_resize($attach[0], $width, $height);
					}
					else{
						$this->thumbnail["{$width}x{$height}"] = site_url($attach[0]);
					}
					return $this->thumbnail["{$width}x{$height}"];
				}
			}
		}
		return '';
	}
	
	/**
	 * 게시글을 삭제한다.
	 */
	public function remove(){
		global $wpdb;
		if($this->uid){
			// 게시글 삭제 액션 실행
			do_action('kboard_document_delete', $this->uid, $this->board_id);
			
			$this->_deleteAllOptions();
			$this->_deleteAllAttached();
			$this->removeThumbnail(false);
			$this->deletePost($this->getPostID());
			$this->deleteReply($this->uid);
			if(defined('KBOARD_COMMNETS_VERSION')){
				$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_comments` WHERE `content_uid`='$this->uid'");
			}
			
			// 미디어 파일을 삭제한다.
			$media = new KBContentMedia();
			$media->deleteWithContentUID($this->uid);
			
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_content` WHERE `uid`='$this->uid'");
		}
	}
	
	/**
	 * 썸네일 파일을 삭제한다.
	 * @param boolean $update
	 */
	public function removeThumbnail($update=true){
		global $wpdb;
		if($this->uid && $this->thumbnail_file){
			kbaord_delete_resize(KBOARD_WORDPRESS_ROOT . stripslashes($this->thumbnail_file));
			@unlink(KBOARD_WORDPRESS_ROOT . stripslashes($this->thumbnail_file));
			
			if($update){
				$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `thumbnail_file`='', `thumbnail_name`='' WHERE `uid`='$this->uid'");
			}
		}
	}
	
	/**
	 * 답글을 삭제한다.
	 * @param int $parent_uid
	 */
	public function deleteReply($parent_uid){
		$list = new KBContentList();
		$list->getReplyList($parent_uid);
		while($content = $list->hasNextReply()){
			$content->remove();
			$this->deleteReply($content->uid);
		}
	}
	
	/**
	 * 게시글의 댓글 개수를 반환한다.
	 * @param string $prefix
	 * @param string $endfix
	 * @param string $default
	 * @return string
	 */
	public function getCommentsCount($prefix='(', $endfix=')', $default=''){
		if($this->uid){
			$board = $this->getBoard();
			if($board->meta->comments_plugin_id && $board->meta->use_comments_plugin){
				$url = new KBUrl();
				return '<span class="cosmosfarm-comments-plugin-count" data-url="'.$url->getCommentsPluginURLWithUID($this->uid).'" data-prefix="'.$prefix.'" data-endfix="'.$endfix.'" data-default="'.$default.'"></span>';
			}
			else if($this->comment || $default){
				$count = $this->comment?$this->comment:$default;
				return "{$prefix}{$count}{$endfix}";
			}
		}
		return '';
	}
	
	/**
	 * 게시글의 댓글 개수를 반환한다.
	 * @param string $prefix
	 * @param string $endfix
	 * @return string
	 */
	public function getCommentsCountOld($prefix='(', $endfix=')'){
		if($this->uid && defined('KBOARD_COMMNETS_VERSION')){
			$commentList = new KBCommentList($this->uid);
			$commentsCount = $commentList->getCount();
			if($commentsCount) return "{$prefix}{$commentsCount}{$endfix}";
		}
		return '';
	}
	
	/**
	 * posts 테이블에 등록된 게시글의 ID 값을 가져온다.
	 */
	public function getPostID(){
		global $wpdb;
		if($this->uid){
			$post_id = $wpdb->get_var("SELECT `ID` FROM `{$wpdb->prefix}posts` WHERE `post_name`='$this->uid' AND `post_type`='kboard'");
		}
		else{
			$post_id = 0;
		}
		return intval($post_id);
	}
	
	/**
	 * 다음 게시물의 UID를 반환한다.
	 */
	public function getNextUID(){
		global $wpdb;
		if($this->uid){
			$uid = $wpdb->get_var("SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` WHERE `board_id`='$this->board_id' AND `uid`>'$this->uid' ORDER BY `uid` ASC LIMIT 1");
		}
		else{
			$uid = 0;
		}
		return intval($uid);
	}
	
	/**
	 * 이전 게시물의 UID를 반환한다.
	 */
	public function getPrevUID(){
		global $wpdb;
		if($this->uid){
			$uid = $wpdb->get_var("SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` WHERE `board_id`='$this->board_id' AND `uid`<'$this->uid' ORDER BY `uid` DESC LIMIT 1");
		}
		else{
			$uid = 0;
		}
		return intval($uid);
	}
	
	/**
	 * 최상위 부모 UID를 반환한다.
	 * @return int
	 */
	public function getTopContentUID(){
		if($this->parent_uid){
			$content = new KBContent();
			$content->initWithUID($this->parent_uid);
			return $content->getTopContentUID();
		}
		return $this->uid;
	}
	
	/**
	 * 게시글과 미디어의 관계를 입력한다.
	 */
	public function addMediaRelationships(){
		if($this->uid){
			$media = new KBContentMedia();
			$media->content_uid = $this->uid;
			$media->media_group = kboard_htmlclear(isset($_POST['media_group'])?$_POST['media_group']:'');
			$media->createRelationships();
		}
	}
	
	/**
	 * 게시글에서 댓글을 보여줄지 확인한다.
	 */
	public function visibleComments(){
		if(isset($this->board->id) && $this->board->id){
			$visible = $this->board->isComment();
		}
		else if($this->board_id){
			$this->board = new KBoard($this->board_id);
			$visible = $this->board->isComment();
		}
		else{
			$visible = false;
		}
		return apply_filters('kboard_visible_comments', $visible, $this);
	}
	
	/**
	 * 새글인지 확인한다.
	 */
	public function isNew(){
		if($this->uid){
			if((current_time('timestamp')-strtotime($this->date)) <= kboard_new_document_notify_time()){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 게시판 정보를 반환한다.
	 * @return KBoard
	 */
	public function getBoard(){
		if(isset($this->board->id) && $this->board->id){
			return $this->board;
		}
		else if($this->board_id){
			$this->board = new KBoard($this->board_id);
			return $this->board;
		}
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
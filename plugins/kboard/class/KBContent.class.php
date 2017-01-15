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
	var $previous_status;

	private $upload_attach_files;

	public function __construct($board_id=''){
		$this->row = new stdClass();
		if($board_id) $this->setBoardID($board_id);
	}

	public function __get($name){
		if(isset($this->row->{$name})){
			if(in_array($name, array('title', 'content'))){
				if(isset($this->row->status) && $this->row->status == 'pending_approval' && in_array(kboard_mod(), array('list', 'document'))){
					if($this->isEditor()){
						switch($name){
							case 'title': return sprintf(__('&#91;Pending&#93; %s', 'kboard'), $this->row->title); break;
							case 'content': return sprintf(__('<p>&#91;Waiting for administrator Approval.&#93;</p>%s', 'kboard'), $this->row->content); break;
						}
					}
					else{
						switch($name){
							case 'title': return __('&#91;Pending&#93; Waiting for administrator Approval.', 'kboard'); break;
							case 'content': return __('&#91;Waiting for administrator Approval.&#93;', 'kboard'); break;
						}
					}
				}
			}
			return $this->row->{$name};
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
		$this->board_id = intval($board_id);

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
		$this->previous_status = $this->status;
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
		$this->previous_status = $this->status;
		return $this;
	}

	/**
	 * 게시글을 등록/수정한다.
	 * @return int
	 */
	public function execute(){
		$board = $this->getBoard();
		
		if($this->uid && $this->date){
			/*
			 * 기존 게시글 업데이트
			 */
			
			$this->initUploadAttachFiles();
			$this->updateContent();
			$this->setThumbnail();
			$this->updateOptions();
			$this->updateAttach();
			$this->addMediaRelationships();

			// 게시글 수정 액션 훅 실행
			do_action('kboard_document_update', $this->uid, $this->board_id);

			$this->execute_action = 'update';
			
			// 임시저장 데이터 삭제
			$this->cleanTemporary();
			
			return $this->uid;
		}
		else if(!$this->uid && $this->title){
			/*
			 * 신규 게시글 등록
			 */
			
			// Captcha 검증
			if($board->useCAPTCHA()){
				if(!class_exists('KBCaptcha')){
					include_once 'KBCaptcha.class.php';
				}
				$captcha = new KBCaptcha();
				
				if(!$captcha->validate()){
					die("<script>alert('".__('CAPTCHA is invalid.', 'kboard')."');history.go(-1);</script>");
				}
			}
			
			if($board->meta->permit){
				// 게시글 승인 대기
				$this->status = 'pending_approval';
			}
			
			// 글쓴이의 id값 등록
			$this->member_uid = get_current_user_id();
			
			$this->initUploadAttachFiles();
			if($this->insertContent()){
				$this->setThumbnail();
				$this->updateOptions();
				$this->updateAttach();
				$this->addMediaRelationships();

				// 게시판 설정에 알림 이메일이 설정되어 있으면 메일을 보낸다.
				if($board->meta->latest_alerts){
					if(!class_exists('KBMail')){
						include_once 'KBMail.class.php';
					}
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
			
			// 임시저장 데이터 삭제
			$this->cleanTemporary();
			
			return $this->uid;
		}
		return 0;
	}

	/**
	 * 게시글을 등록한다.
	 * @return int
	 */
	public function insertContent($data = array()){
		global $wpdb;

		if(!$data){
			$data['board_id'] = $this->board_id;
			$data['parent_uid'] = $this->parent_uid?$this->parent_uid:0;
			$data['member_uid'] = $this->member_uid;
			$data['member_display'] = $this->member_display;
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
			$data['status'] = $this->status;
			$data['password'] = $this->password?$this->password:'';
		}

		// 입력할 데이터 필터
		$data = apply_filters('kboard_insert_data', $data, $this->board_id);
		
		if($data['board_id'] && $data['title']){
			foreach($data as $key=>$value){
				$value = esc_sql($value);
				$insert_key[] = "`$key`";
				$insert_data[] = "'$value'";
			}
			
			$board = $this->getBoard();
			$board_total = $board->getTotal();
			$board_list_total = $board->getListTotal();
			
			if($this->status != 'trash'){
				$board->meta->total = $board_total + 1;
				$board->meta->list_total = $board_list_total + 1;
			}
			else{
				$board->meta->total = $board_total + 1;
			}
			
			$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_board_content` (".implode(',', $insert_key).") VALUE (".implode(',', $insert_data).")");
			
			$this->uid = $wpdb->insert_id;
			
			$this->insertPost($this->uid, $data['member_uid']);
			
			return $this->uid;
		}
		return 0;
	}

	/**
	 * 게시글 정보를 수정한다.
	 */
	public function updateContent($data = array()){
		global $wpdb;
		
		if($this->uid){
			if(!$data){
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
				$data['status'] = $this->status;
				if($this->member_uid || $this->password) $data['password'] = $this->password;
			}

			// 수정할 데이터 필터
			$data = apply_filters('kboard_update_data', $data, $this->board_id);
			
			if($data['board_id'] && $data['title']){
				foreach($data as $key=>$value){
					$value = esc_sql($value);
					$update[] = "`$key`='$value'";
				}
				
				if($this->previous_status != $this->status){
					if($this->status == 'trash'){
						$this->moveReplyToTrash($this->uid);
					}
					else if($this->previous_status == 'trash'){
						$this->restoreReplyFromTrash($this->uid);
					}
				}
				
				$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET ".implode(',', $update)." WHERE `uid`='{$this->uid}'");
				
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
			$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `view`=`view`+1 WHERE `uid`='{$this->uid}'");
			$this->view = $this->view + 1;
		}
	}

	/**
	 * 게시글 옵션 정보를 초기화 한다.
	 * @return string
	 */
	public function initOptions(){
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
				$key = sanitize_key(str_replace($this->skin_attach_prefix, '', $key));
					
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
				$file_key = esc_sql($attach_file->key);
				$file_path = esc_sql($attach_file->path);
				$file_name = esc_sql($attach_file->name);

				$present_file = $wpdb->get_var("SELECT `file_path` FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_uid`='$this->uid' AND `file_key`='$file_key'");
				if($present_file){
					@unlink(KBOARD_WORDPRESS_ROOT . $present_file);
					$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_attached` SET `file_path`='$file_path', `file_name`='$file_name' WHERE `content_uid`='$this->uid' AND `file_key`='$file_key'");
				}
				else{
					$date = date('YmdHis', current_time('timestamp'));
					$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_board_attached` (`content_uid`, `file_key`, `date`, `file_path`, `file_name`) VALUE ('$this->uid', '$file_key', '$date', '$file_path', '$file_name')");
				}
			}
		}
		else if($this->upload_attach_files && is_array($this->upload_attach_files)){
			foreach($this->upload_attach_files as $attach_file){
				kbaord_delete_resize(KBOARD_WORDPRESS_ROOT . $attach_file->path);
				@unlink(KBOARD_WORDPRESS_ROOT . $attach_file->path);
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
				kbaord_delete_resize(KBOARD_WORDPRESS_ROOT . $file->file_path);
				@unlink(KBOARD_WORDPRESS_ROOT . $file->file_path);
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
			$key = esc_sql(sanitize_key($key));
			$file = $wpdb->get_var("SELECT `file_path` FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_uid`='$this->uid' AND `file_key`='$key'");
			if($file){
				kbaord_delete_resize(KBOARD_WORDPRESS_ROOT . $file);
				@unlink(KBOARD_WORDPRESS_ROOT . $file);
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
				if(strpos($key, $this->skin_option_prefix) !== false){
					$key = sanitize_key(str_replace($this->skin_option_prefix, '', $key));
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
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_option` WHERE `content_uid`='{$this->uid}'");
		}
	}

	/**
	 * 썸네일을 등록한다.
	 */
	public function setThumbnail(){
		global $wpdb;
		if(!$this->thumbnail_store_path) die(__('No upload path. Please enter board ID and initialize.', 'kboard'));

		if($this->uid && isset($_FILES['thumbnail']) && $_FILES['thumbnail']['tmp_name']){
			$file = new KBFileHandler();
			$file->setPath($this->thumbnail_store_path);
			$upload = $file->upload('thumbnail');
			
			$thumbnail_name = esc_sql($upload['original_name']);
			$thumbnail_file = esc_sql($upload['path'] . $upload['stored_name']);

			if($thumbnail_name){

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
				$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `thumbnail_file`='{$thumbnail_file}', `thumbnail_name`='{$thumbnail_name}' WHERE `uid`='{$this->uid}'");
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
	 * @param boolean $delete_action
	 */
	public function delete($delete_action=true){
		$this->remove($delete_action);
	}
	
	/**
	 * 게시글을 삭제한다.
	 * @param boolean $delete_action
	 */
	public function remove($delete_action=true){
		global $wpdb;
		if($this->uid){
			
			if($delete_action){
				// 게시글 삭제 액션 실행
				do_action('kboard_document_delete', $this->uid, $this->board_id);
			}

			$this->_deleteAllOptions();
			$this->_deleteAllAttached();
			$this->removeThumbnail(false);
			$this->deletePost($this->getPostID());
			$this->deleteReply($this->uid);
			if(defined('KBOARD_COMMNETS_VERSION')){
				$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_comments` WHERE `content_uid`='{$this->uid}'");
			}

			// 미디어 파일을 삭제한다.
			$media = new KBContentMedia();
			$media->deleteWithContentUID($this->uid);

			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_content` WHERE `uid`='{$this->uid}'");
			
			$board = $this->getBoard();
			$board->meta->total = $board->getTotal() - 1;
		}
	}

	/**
	 * 썸네일 파일을 삭제한다.
	 * @param boolean $update
	 */
	public function removeThumbnail($update=true){
		global $wpdb;
		if($this->uid && $this->thumbnail_file){
			kbaord_delete_resize(KBOARD_WORDPRESS_ROOT . $this->thumbnail_file);
			@unlink(KBOARD_WORDPRESS_ROOT . $this->thumbnail_file);

			if($update){
				$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `thumbnail_file`='', `thumbnail_name`='' WHERE `uid`='{$this->uid}'");
			}
		}
	}

	/**
	 * 답글을 삭제한다.
	 * @param int $parent_uid
	 */
	public function deleteReply($parent_uid){
		global $wpdb;
		
		$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE `parent_uid`='$parent_uid'");
		foreach($results as $row){
			$content = new KBContent();
			$content->initWithRow($row);
			$content->board = $this->getBoard();
			$content->remove(false);
			$this->deleteReply($content->uid);
		}
	}
	
	/**
	 * 휴지통으로 이동할 때 실행한다.
	 * @param string $content_uid
	 */
	public function moveReplyToTrash($parent_uid){
		global $wpdb;
		
		$board = $this->getBoard();
		$board->meta->list_total = $board->getListTotal() - 1;
		
		$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE `parent_uid`='$parent_uid'");
		foreach($results as $row){
			if($row->status != 'trash'){
				$this->moveReplyToTrash($row->uid);
			}
		}
	}
	
	/**
	 * 휴지통에서 복구할 때 실행한다.
	 * @param string $content_uid
	 */
	public function restoreReplyFromTrash($parent_uid){
		global $wpdb;
		
		$board = $this->getBoard();
		$board->meta->list_total = $board->getListTotal() + 1;
		
		$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE `parent_uid`='$parent_uid'");
		foreach($results as $row){
			if($row->status != 'trash'){
				$this->restoreReplyFromTrash($row->uid);
			}
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
			$category1 = kboard_category1();
			$category2 = kboard_category2();

			$where[] = "`board_id`='{$this->board_id}'";
			$where[] = "`uid`>'{$this->uid}'";
			
			// 휴지통에 없는 게시글만 불러온다.
			$where[] = "(`status`='' OR `status` IS NULL OR `status`='pending_approval')";

			if($category1){
				$category1 = esc_sql($category1);
				$where[] = "`category1`='{$category1}'";
			}
			if($category2){
				$category2 = esc_sql($category2);
				$where[] = "`category2`='{$category2}'";
			}

			$where = implode(' AND ', $where);
			$uid = $wpdb->get_var("SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` WHERE {$where} ORDER BY `uid` ASC LIMIT 1");
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
			$category1 = kboard_category1();
			$category2 = kboard_category2();

			$where[] = "`board_id`='{$this->board_id}'";
			$where[] = "`uid`<'{$this->uid}'";
			
			// 휴지통에 없는 게시글만 불러온다.
			$where[] = "(`status`='' OR `status` IS NULL OR `status`='pending_approval')";

			if($category1){
				$category1 = esc_sql($category1);
				$where[] = "`category1`='{$category1}'";
			}
			if($category2){
				$category2 = esc_sql($category2);
				$where[] = "`category2`='{$category2}'";
			}

			$where = implode(' AND ', $where);
			$uid = $wpdb->get_var("SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` WHERE {$where} ORDER BY `uid` DESC LIMIT 1");
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
	 * 최상위 부모 object를 반환한다.
	 * @return KBContent
	 */
	public function getTopContent(){
		if($this->parent_uid){
			$content = new KBContent();
			$content->initWithUID($this->parent_uid);
			return $content->getTopContent();
		}
		return $this;
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
	 * @return boolean
	 */
	public function isNew(){
		if($this->uid){
			$notify_time = kboard_new_document_notify_time();
			if((current_time('timestamp')-strtotime($this->date)) <= $notify_time && $notify_time != '1'){
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
		return new KBoard();
	}

	/**
	 * 첨부파일이 있는지 확인한다.
	 * @return boolean
	 */
	public function isAttached(){
		if($this->uid){
			if(count((array)$this->attach) > 0){
				return true;
			}
		}
		return false;
	}

	/**
	 * 날짜를 반환한다.
	 * @return string
	 */
	public function getDate(){
		if(isset($this->row->date)){
			if(date('Ymd', current_time('timestamp')) == date('Ymd', strtotime($this->row->date))){
				return date('H:i', strtotime($this->row->date));
			}
			return date('Y.m.d', strtotime($this->row->date));
		}
		return '';
	}

	/**
	 * 제목을 반환한다.
	 * @return string
	 */
	public function getTitle(){
		if(isset($this->row->title)){
			return $this->row->title;
		}
		return '';
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
	 * 게시글 정보를 쿠키에 저장한다.
	 */
	public function saveTemporary(){
		$this->parent_uid = isset($_POST['parent_uid'])?intval($_POST['parent_uid']):0;
		$this->member_uid = isset($_POST['member_uid'])?intval($_POST['member_uid']):0;
		$this->member_display = isset($_POST['member_display'])?sanitize_text_field($_POST['member_display']):'';
		$this->title = isset($_POST['title'])?kboard_safeiframe(kboard_xssfilter($_POST['title'])):'';
		$this->content = isset($_POST['kboard_content'])?kboard_safeiframe(kboard_xssfilter($_POST['kboard_content'])):'';
		$this->date = isset($_POST['date'])?kboard_htmlclear($_POST['date']):'';
		if(isset($_POST['view'])) $this->view = intval($_POST['view']);
		if(isset($_POST['comment'])) $this->comment = intval($_POST['comment']);
		if(isset($_POST['like'])) $this->like = intval($_POST['like']);
		if(isset($_POST['unlike'])) $this->unlike = intval($_POST['unlike']);
		if(isset($_POST['vote'])) $this->vote = intval($_POST['vote']);
		$this->category1 = isset($_POST['category1'])?sanitize_text_field($_POST['category1']):'';
		$this->category2 = isset($_POST['category2'])?sanitize_text_field($_POST['category2']):'';
		$this->secret = isset($_POST['secret'])?kboard_htmlclear($_POST['secret']):'';
		$this->notice = isset($_POST['notice'])?kboard_htmlclear($_POST['notice']):'';
		$this->search = isset($_POST['wordpress_search'])?intval(($this->secret && $_POST['wordpress_search']==1)?'2':$_POST['wordpress_search']):'3';
		if(isset($_POST['status'])) $this->status = kboard_htmlclear($_POST['status']);
		$password = isset($_POST['password'])?sanitize_text_field($_POST['password']):'';
		$this->password = '';

		if(is_user_logged_in() && !$this->member_display){
			$current_user = wp_get_current_user();
			$this->member_display = $current_user->display_name;
		}
		
		$option = new stdClass();
		foreach($_POST as $key=>$value){
			if(strpos($key, $this->skin_option_prefix) !== false){
				$key = sanitize_key(str_replace($this->skin_option_prefix, '', $key));
				$value = kboard_safeiframe(kboard_xssfilter($value));
				$option->{$key} = $value;
			}
		}
		
		$temporary = $this->row;
		$temporary->option = $option;
		setcookie('kboard_temporary_content', base64_encode(serialize($temporary)), 0, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
		
		$this->password = $password;
	}

	/**
	 * 쿠키에 저장된 게시글 정보로 초기화 한다.
	 */
	public function initWithTemporary(){
		if(isset($_COOKIE['kboard_temporary_content']) && $_COOKIE['kboard_temporary_content']){
			$temporary = unserialize(base64_decode($_COOKIE['kboard_temporary_content']));
			$this->row = $temporary;
		}
		else{
			$this->row = new stdClass();
		}
		
		if(!isset($temporary->option) || !(array)$temporary->option){
			$this->option = new KBContentOption();
		}
		else{
			$this->option = $temporary->option;
		}
	}

	/**
	 * 쿠키에 저장된 게시글 정보를 비운다.
	 */
	public function cleanTemporary(){
		setcookie('kboard_temporary_content', '', time()-(60*60), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
	}
	
	/**
	 * 글 수정 권한이 있는 사용자인지 확인한다.
	 * @return boolean
	 */
	public function isEditor(){
		if($this->uid){
			$board = $this->getBoard();
			if($board->isEditor($this->member_uid)){
				return true;
			}
			else if(isset($_SESSION['kboard_confirm']) && isset($_SESSION['kboard_confirm'][$this->uid]) && $_SESSION['kboard_confirm'][$this->uid] == $this->password){
				return true;
			}
		}
		return false;
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
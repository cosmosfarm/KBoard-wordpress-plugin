<?php
/**
 * KBoard Controller
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBController {
	
	public function __construct(){
		$action = isset($_POST['action'])?$_POST['action']:'';
		if($action == 'kboard_editor_execute'){
			add_action('template_redirect', array($this, 'editorExecute'), 0);
		}
		else if($action == 'kboard_media_upload'){
			add_action('template_redirect', array($this, 'mediaUpload'), 0);
		}
		else if($action == 'kboard_media_delete'){
			add_action('template_redirect', array($this, 'mediaDelete'), 0);
		}
		
		$action = isset($_GET['action'])?$_GET['action']:'';
		if($action == 'kboard_file_delete'){
			add_action('template_redirect', array($this, 'fileDelete'), 0);
		}
		else if($action == 'kboard_file_download'){
			add_action('template_redirect', array($this, 'fileDownload'), 0);
		}
		
		add_action('wp_ajax_kboard_document_like', array($this, 'documentLike'));
		add_action('wp_ajax_nopriv_kboard_document_like', array($this, 'documentLike'));
		
		add_action('wp_ajax_kboard_document_unlike', array($this, 'documentUnlike'));
		add_action('wp_ajax_nopriv_kboard_document_unlike', array($this, 'documentUnlike'));
	}
	
	/**
	 * 게시글 등록 및 수정
	 */
	public function editorExecute(){
		if(isset($_POST['kboard-editor-execute-nonce']) && wp_verify_nonce($_POST['kboard-editor-execute-nonce'], 'kboard-editor-execute')){
			header('Content-Type: text/html; charset=UTF-8');
			
			$uid = intval(isset($_POST['uid'])?$_POST['uid']:'');
			$board_id = intval(isset($_POST['board_id'])?$_POST['board_id']:'');
			
			$board = new KBoard($board_id);
			if(!$board->id){
				die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
			}
			
			if($board->isWriter() && $board->permission_write=='all' && $_POST['title']){
				if(!is_user_logged_in() && !$_POST['password']){
					die('<script>alert("'.__('Please enter your password.', 'kboard').'");history.go(-1);";</script>');
				}
			}
			
			$content = new KBContent();
			$content->initWithUID($uid);
			$content->setBoardID($board_id);
			$content->board = $board;
			
			if(!$uid && !$board->isWriter()){
				die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
			}
			else if($uid && !$board->isEditor($content->member_uid)){
				if($board->permission_write=='all'){
					if(!$board->isConfirm($content->password, $content->uid)){
						die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
					}
				}
				else{
					die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
				}
			}
			
			$execute_uid = $content->execute();
			// 비밀번호가 입력되면 즉시 인증과정을 거친다.
			if($content->password) $board->isConfirm($content->password, $execute_uid);
			
			$url = new KBUrl();
			$next_page_url = $url->set('uid', $execute_uid)->set('mod', 'document')->toString();
			$next_page_url = apply_filters('kboard_after_executing_url', $next_page_url, $execute_uid, $board_id);
			
			if($content->execute_action == 'insert'){
				if($board->meta->conversion_tracking_code){
					echo $board->meta->conversion_tracking_code;
					echo "<script>window.location.href='{$next_page_url}';</script>";
					exit;
				}
			}
			wp_redirect($next_page_url);
		}
		else{
			wp_redirect(site_url());
		}
		exit;
	}
	
	/**
	 * 미디어 파일 업로드
	 */
	public function mediaUpload(){
		if(isset($_POST['kboard-media-upload-nonce']) && wp_verify_nonce($_POST['kboard-media-upload-nonce'], 'kboard-media-upload')){
			header('Content-Type: text/html; charset=UTF-8');
			
			$media = new KBContentMedia();
			$media->board_id = intval(isset($_POST['board_id'])?$_POST['board_id']:'');
			$media->media_group = kboard_htmlclear(isset($_POST['media_group'])?$_POST['media_group']:'');
			$media->content_uid = intval(isset($_POST['content_uid'])?$_POST['content_uid']:'');
			$media->upload();
		}
	}
	
	/**
	 * 미디어 파일 삭제
	 */
	public function mediaDelete(){
		if(isset($_POST['kboard-media-upload-nonce']) && wp_verify_nonce($_POST['kboard-media-upload-nonce'], 'kboard-media-upload')){
			header("Content-Type: text/html; charset=UTF-8");
			
			$media_uid = intval(isset($_POST['media_uid'])?$_POST['media_uid']:'');
			$media = new KBContentMedia();
			$media->deleteWithMediaUID($media_uid);
		}
	}
	
	/**
	 * 첨부파일 삭제
	 */
	public function fileDelete(){
		header('Content-Type: text/html; charset=UTF-8');
		
		$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
		$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
		if($referer){
			$url = parse_url($referer);
			$referer_host = $url['host'] . (isset($url['port'])&&$url['port']?':'.$url['port']:'');
		}
		else{
			wp_die(__('This page is restricted from external access.', 'kboard'));
		}
		if(!in_array($referer_host, array($host))) wp_die(__('This page is restricted from external access.', 'kboard'));
		
		$uid = intval($_GET['uid']);
		if(isset($_GET['file'])){
			$file = trim($_GET['file']);
			$file = kboard_htmlclear($file);
			$file = kboard_xssfilter($file);
			$file = esc_sql($file);
		}
		else{
			$file = '';
		}
		
		if(!$uid || !$file){
			die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
		}
		
		$content = new KBContent();
		$content->initWithUID($uid);
		
		if($content->parent_uid){
			$parent = new KBContent();
			$parent->initWithUID($content->getTopContentUID());
			$board = new KBoard($parent->board_id);
		}
		else{
			$board = new KBoard($content->board_id);
		}
		
		if(!$board->isEditor($content->member_uid)){
			if($board->permission_write=='all'){
				if(!$board->isConfirm($content->password, $content->uid)){
					$url = new KBUrl();
					$skin_path = KBOARD_URL_PATH . "/skin/$board->skin";
					include KBOARD_DIR_PATH . "/skin/$board->skin/confirm.php";
					exit;
				}
			}
			else{
				die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
			}
		}
		
		if($file == 'thumbnail') $content->removeThumbnail();
		else $content->removeAttached($file);
		
		header("Location: {$referer}");
		exit;
	}
	
	/**
	 * 첨부파일 다운로드
	 */
	public function fileDownload(){
		global $wpdb;
		
		header('X-Robots-Tag: noindex', true); // 검색엔진 수집 금지
		header('Content-Type: text/html; charset=UTF-8');
		
		$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
		$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
		if($referer){
			$url = parse_url($referer);
			$referer_host = $url['host'] . (isset($url['port'])&&$url['port']?':'.$url['port']:'');
		}
		else{
			wp_die(__('This page is restricted from external access.', 'kboard'));
		}
		if(!in_array($referer_host, array($host))) wp_die(__('This page is restricted from external access.', 'kboard'));
		
		$uid = isset($_GET['uid'])?intval($_GET['uid']):'';
		if(isset($_GET['file'])){
			$file = trim($_GET['file']);
			$file = kboard_htmlclear($file);
			$file = kboard_xssfilter($file);
			$file = esc_sql($file);
		}
		else{
			$file = '';
		}
		
		if(!$uid || !$file){
			die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
		}
		
		$content = new KBContent();
		$content->initWithUID($uid);
		
		if($content->parent_uid){
			$parent = new KBContent();
			$parent->initWithUID($content->getTopContentUID());
			$board = new KBoard($parent->board_id);
		}
		else{
			$board = new KBoard($content->board_id);
		}
		
		if(!$board->isReader($content->member_uid, $content->secret)){
			if(!$user_ID && $board->permission_read == 'author'){
				die('<script>alert("'.__('Please Log in to continue.', 'kboard').'");location.href="' . wp_login_url($referer) . '";</script>');
			}
			else if($content->secret && in_array($board->permission_write, array('all', 'author')) && in_array($board->permission_read, array('all', 'author'))){
				if(!$board->isConfirm($content->password, $content->uid)){
					if($content->parent_uid){
						$parent = new KBContent();
						$parent->initWithUID($content->getTopContentUID());
						if(!$board->isReader($parent->member_uid, $content->secret)){
							if(!$board->isConfirm($parent->password, $parent->uid)){
								die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
							}
						}
					}
					else{
						die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
					}
				}
			}
			else{
				die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
			}
		}
		
		$file_info = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_uid`='$uid' AND `file_key`='$file'");
		
		list($path) = explode(DIRECTORY_SEPARATOR . 'wp-content', dirname(__FILE__));
		$file_info->full_path = $path . str_replace('/', DIRECTORY_SEPARATOR, $file_info->file_path);
		$file_info->file_name = str_replace(' ' ,'-', $file_info->file_name);
		
		$file_info = apply_filters('kboard_download_file', $file_info, $content->uid, $board->id);
		
		if(!$file_info->file_path || !file_exists($file_info->full_path)){
			die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
		}
		
		if(get_option('kboard_attached_copy_download')){
			$unique_dir = uniqid();
			$upload_dir = wp_upload_dir();
			$temp_path = $upload_dir['basedir'] . '/kboard_temp';
		
			$kboard_file_handler = new KBFileHandler();
			$kboard_file_handler->deleteWithOvertime($temp_path, 60);
			$kboard_file_handler->mkPath("{$temp_path}/{$unique_dir}");
		
			copy($file_info->full_path, "{$temp_path}/{$unique_dir}/{$file_info->file_name}");
			header('Location: ' . $upload_dir['baseurl'] . "/kboard_temp/{$unique_dir}/{$file_info->file_name}");
		}
		else{
			$ie = isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false);
			if($ie) $file_info->file_name = iconv('UTF-8', 'EUC-KR//IGNORE', $file_info->file_name);
		
			header('Content-type: '.kboard_mime_type($file_info->full_path));
			header('Content-Disposition: attachment; filename="'.$file_info->file_name.'"');
			header('Content-Transfer-Encoding: binary');
			header('Content-length: '.sprintf('%d', filesize($file_info->full_path)));
			header('Expires: 0');
		
			if($ie){
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
			}
			else{
				header('Pragma: no-cache');
			}
		
			$fp = fopen($file_info->full_path, 'rb');
			fpassthru($fp);
			fclose($fp);
		}
		exit;
	}
	
	/**
	 * 게시글 좋아요
	 */
	public function documentLike(){
		if(isset($_POST['document_uid']) && intval($_POST['document_uid'])){
			if(!@in_array($_POST['document_uid'], $_SESSION['document_vote'])){
				$_SESSION['document_vote'][] = $_POST['document_uid'];
				
				$content = new KBContent();
				$content->initWithUID($_POST['document_uid']);
				
				if($content->uid){
					$content->like+=1;
					$content->vote = $content->like - $content->unlike;
					$content->updateContent();
					echo intval($content->like);
					exit;
				}
			}
		}
		exit;
	}
	
	/**
	 * 게시글 싫어요
	 */
	function documentUnlike(){
		if(isset($_POST['document_uid']) && intval($_POST['document_uid'])){
			if(!@in_array($_POST['document_uid'], $_SESSION['document_vote'])){
				$_SESSION['document_vote'][] = $_POST['document_uid'];
				
				$content = new KBContent();
				$content->initWithUID($_POST['document_uid']);
				
				if($content->uid){
					$content->unlike+=1;
					$content->vote = $content->like - $content->unlike;
					$content->updateContent();
					echo intval($content->unlike);
					exit;
				}
			}
		}
		exit;
	}
}
?>
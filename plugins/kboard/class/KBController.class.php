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
			add_action('template_redirect', array($this, 'editorExecute'));
		}
		else if($action == 'kboard_media_upload'){
			add_action('template_redirect', array($this, 'mediaUpload'));
		}
		else if($action == 'kboard_media_delete'){
			add_action('template_redirect', array($this, 'mediaDelete'));
		}
		
		$action = isset($_GET['action'])?$_GET['action']:'';
		if($action == 'kboard_file_delete'){
			add_action('template_redirect', array($this, 'fileDelete'));
		}
		else if($action == 'kboard_file_download'){
			add_action('template_redirect', array($this, 'fileDownload'));
		}
		else if($action == 'kboard_backup'){
			add_action('template_redirect', array($this, 'backup'));
		}
	}
	
	/**
	 * 게시글 등록 및 수정
	 */
	public function editorExecute(){
		global $user_ID;
		
		if(isset($_POST['kboard-editor-execute-nonce']) && wp_verify_nonce($_POST['kboard-editor-execute-nonce'], 'kboard-editor-execute')){
			header("Content-Type: text/html; charset=UTF-8");
			
			$uid = intval(isset($_POST['uid'])?$_POST['uid']:'');
			$board_id = intval(isset($_POST['board_id'])?$_POST['board_id']:'');
			
			$board = new KBoard($board_id);
			if(!$board->uid){
				die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
			}
			
			if($board->isWriter() && $board->permission_write=='all' && $_POST['title']){
				if(!$user_ID && !$_POST['password']){
					die('<script>alert("'.__('Please enter your password.', 'kboard').'");history.go(-1);";</script>');
				}
			}
			
			$content = new KBContent();
			$content->initWithUID($uid);
			$content->setBoardID($board_id);
			
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
			header("Content-Type: text/html; charset=UTF-8");
			
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
			$referer_host = $url['host'];
		}
		else{
			wp_die('KBoard : '.__('This page is restricted from external access.', 'kboard'));
		}
		if(!in_array($referer_host, array($host))) wp_die('KBoard : '.__('This page is restricted from external access.', 'kboard'));
		
		$uid = intval($_GET['uid']);
		if(isset($_GET['file'])){
			$file = trim($_GET['file']);
			$file = kboard_htmlclear($file);
			$file = kboard_xssfilter($file);
			$file = addslashes($file);
		}
		else{
			$file = '';
		}
		
		if(!$uid || !$file){
			die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
		}
		
		if(!strstr($referer, basename(__file__))) $_SESSION['redirect_uri'] = $referer;
		
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
		
		header("Location:{$_SESSION['redirect_uri']}");
		exit;
	}
	
	/**
	 * 첨부파일 다운로드
	 */
	public function fileDownload(){
		global $wpdb;
		header('Content-Type: text/html; charset=UTF-8');
		$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
		$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
		if($referer){
			$url = parse_url($referer);
			$referer_host = $url['host'];
		}
		else{
			wp_die('KBoard : '.__('This page is restricted from external access.', 'kboard'));
		}
		if(!in_array($referer_host, array($host))) wp_die('KBoard : '.__('This page is restricted from external access.', 'kboard'));
		
		$uid = isset($_GET['uid'])?intval($_GET['uid']):'';
		if(isset($_GET['file'])){
			$file = trim($_GET['file']);
			$file = kboard_htmlclear($file);
			$file = kboard_xssfilter($file);
			$file = addslashes($file);
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
		
		list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
		$path = $path.str_replace('/', DIRECTORY_SEPARATOR, $file_info->file_path);
		$filename = str_replace(' ' ,'-', $file_info->file_name);
		
		if(!$file_info->file_path || !file_exists($path)){
			die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
		}
		
		if(get_option('kboard_attached_copy_download')){
			$unique_dir = uniqid();
			$upload_dir = wp_upload_dir();
			$temp_path = $upload_dir['basedir'] . '/kboard_temp';
		
			$kboard_file_handler = new KBFileHandler();
			$kboard_file_handler->deleteWithOvertime($temp_path, 60);
			$kboard_file_handler->mkPath("{$temp_path}/{$unique_dir}");
		
			copy($path, "{$temp_path}/{$unique_dir}/{$filename}");
			header('Location:' . $upload_dir['baseurl'] . "/kboard_temp/{$unique_dir}/{$filename}");
		}
		else{
			$ie = isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false);
			if($ie) $filename = iconv('UTF-8', 'EUC-KR//IGNORE', $filename);
		
			header('Content-type: '.kboard_mime_type($path));
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header('Content-Transfer-Encoding: binary');
			header('Content-length: '.sprintf('%d', filesize($path)));
			header('Expires: 0');
		
			if($ie){
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
			}
			else{
				header('Pragma: no-cache');
			}
		
			$fp = fopen($path, 'rb');
			fpassthru($fp);
			fclose($fp);
		}
		exit;
	}
	
	/**
	 * 백업파일 다운로드
	 */
	public function backup(){
		header('Content-Type: text/html; charset=UTF-8');
		$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
		$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
		if($referer){
			$url = parse_url($referer);
			$referer_host = $url['host'];
		}
		else{
			wp_die('KBoard : '.__('This page is restricted from external access.', 'kboard'));
		}
		if(!in_array($referer_host, array($host))) wp_die('KBoard : '.__('This page is restricted from external access.', 'kboard'));
		if(!current_user_can('activate_plugins')) wp_die('KBoard : '.__('No backup privilege.', 'kboard'));
		
		include KBOARD_DIR_PATH.'/class/KBBackup.class.php';
		$backup = new KBBackup();
		
		$tables = $backup->getTables();
		$data = '';
		foreach($tables as $key => $value){
			$data .= $backup->getXml($value);
		}
		
		$backup->download($data, 'xml');
		exit;
	}
}
?>
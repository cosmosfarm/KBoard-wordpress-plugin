<?php
/**
 * KBoard Admin Controller
* @link www.cosmosfarm.com
* @copyright Copyright 2013 Cosmosfarm. All rights reserved.
* @license http://www.gnu.org/licenses/gpl.html
*/
class KBAdminController {
	
	public function __construct(){
		add_action('wp_ajax_kboard_content_list_update', array($this, 'content_list_update'));
		add_action('admin_post_kboard_backup_download', array($this, 'backup'));
		add_action('admin_post_kboard_restore_execute', array($this, 'restore'));
		add_action('admin_post_kboard_latestview_action', array($this, 'latestview_update'));
	}
	
	/**
	 * 백업
	 */
	public function backup(){
		if(!current_user_can('activate_plugins')) wp_die(__('관리 권한이 없습니다.', 'kboard'));
		if(isset($_POST['kboard-backup-download-nonce']) && wp_verify_nonce($_POST['kboard-backup-download-nonce'], 'kboard-backup-download')){
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
				
			include_once KBOARD_DIR_PATH . '/class/KBBackup.class.php';
			$backup = new KBBackup();
			$tables = $backup->getTables();
			$data = '';
			foreach($tables as $key=>$value){
				$data .= $backup->getXml($value);
			}
				
			$backup->download($data, 'xml');
			exit;
		}
		$redirect_url = admin_url('admin.php?page=kboard_backup');
		echo "<script>window.location.href='{$redirect_url}';</script>";
		exit;
	}
	
	/**
	 * 복원
	 */
	public function restore(){
		if(!current_user_can('activate_plugins')) wp_die(__('관리 권한이 없습니다.', 'kboard'));
		if(isset($_POST['kboard-restore-execute-nonce']) && wp_verify_nonce($_POST['kboard-restore-execute-nonce'], 'kboard-restore-execute')){
			header('Content-Type: text/html; charset=UTF-8');
				
			$xmlfile = $_FILES['kboard_backup_xml_file']['tmp_name'];
			$xmlfile_name = basename($_FILES['kboard_backup_xml_file']['name']);
				
			if(is_uploaded_file($xmlfile)){
				$file_extension = explode('.', $xmlfile_name);
				if(end($file_extension) == 'xml'){
					include_once KBOARD_DIR_PATH . '/class/KBBackup.class.php';
					$backup = new KBBackup();
					$backup->importXml($xmlfile);
					echo '<script>alert("'.__('복원파일의 데이터로 복구 되었습니다.', 'kboard').'");</script>';
				}
				else{
					echo '<script>alert("'.__('올바른 복원파일이 아닙니다.', 'kboard').'");</script>';
				}
				unlink($xmlfile);
			}
			else{
				echo '<script>alert("'.__('파일 업로드에 실패 했습니다.', 'kboard').'");</script>';
			}
		}
		$redirect_url = admin_url('admin.php?page=kboard_backup');
		echo "<script>window.location.href='{$redirect_url}';</script>";
		exit;
	}
	
	/**
	 * 전체 게시글 정보 업데이트
	 */
	public function content_list_update(){
		if(current_user_can('activate_plugins')){
			$content = new KBContent();
			foreach($_POST['board_id'] as $uid=>$value){
				$content->initWithUID($uid);
				$content->board_id = $_POST['board_id'][$uid];
				$content->status = $_POST['status'][$uid];
				$content->updateContent();
			}
		}
		exit;
	}
	
	/**
	 * 최신글 뷰 업데이트
	 */
	function latestview_update(){
		if(!current_user_can('activate_plugins')) wp_die(__('You do not have permission.', 'kboard'));
	
		$latestview_uid = $_POST['latestview_uid'];
		$latestview_link = $_POST['latestview_link'];
		$latestview_unlink = $_POST['latestview_unlink'];
		$name = $_POST['name'];
		$skin = $_POST['skin'];
		$rpp = $_POST['rpp'];
		$sort = $_POST['sort'];
	
		$latestview = new KBLatestview();
		if($latestview_uid) $latestview->initWithUID($latestview_uid);
		else $latestview->create();
	
		$latestview->name = $name;
		$latestview->skin = $skin;
		$latestview->rpp = $rpp;
		$latestview->sort = $sort;
		$latestview->update();
	
		$latestview_link = explode(',', $latestview_link);
		if(is_array($latestview_link)){
			foreach($latestview_link as $key=>$value){
				$value = intval($value);
				if($value) $latestview->pushBoard($value);
			}
		}
	
		$latestview_unlink = explode(',', $latestview_unlink);
		if(is_array($latestview_unlink)){
			foreach($latestview_unlink as $key=>$value){
				$value = intval($value);
				if($value) $latestview->popBoard($value);
			}
		}
	
		echo '<script>window.location.href="' . admin_url("admin.php?page=kboard_latestview&latestview_uid={$latestview->uid}") . '"</script>';
		exit;
	}
}
?>
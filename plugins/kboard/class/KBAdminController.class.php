<?php
/**
 * KBoard Admin Controller
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBAdminController {
	
	public function __construct(){
		add_action('admin_post_kboard_backup_download', array($this, 'backup'));
		add_action('admin_post_kboard_restore_execute', array($this, 'restore'));
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
		echo "<script>location.href='{$redirect_url}';</script>";
		exit;
	}
	
	/**
	 * 복원
	 */
	public function restore(){
		if(!current_user_can('activate_plugins')) wp_die(__('관리 권한이 없습니다.', 'kboard'));
		if(isset($_POST['kboard-restore-execute-nonce']) && wp_verify_nonce($_POST['kboard-restore-execute-nonce'], 'kboard-restore-execute')){
			header('Content-Type: text/html; charset=UTF-8');
			
			$xmlfile = WP_CONTENT_DIR . '/uploads/' . basename($_FILES['kboard_backup_xml_file']['name']);
			if(move_uploaded_file($_FILES['kboard_backup_xml_file']['tmp_name'], $xmlfile)){
				$file_extension = explode('.', $xmlfile);
				if(end($file_extension) == 'xml'){
					include_once KBOARD_DIR_PATH . '/class/KBBackup.class.php';
					$backup = new KBBackup();
					$backup->importXml($xmlfile);
					echo '<script>alert("'.__('복원파일의 데이터로 복구 되었습니다.', 'kboard').'");</script>';
				}
				else{
					echo '<script>alert("'.__('복원에 실패 했습니다. 올바른 복원파일이 아닙니다.', 'kboard').'");</script>';
				}
				unlink($xmlfile);
			}
			else{
				echo '<script>alert("'.__('파일의 업로드를 실패 했습니다.', 'kboard').'");</script>';
			}
		}
		$redirect_url = admin_url('admin.php?page=kboard_backup');
		echo "<script>location.href='{$redirect_url}';</script>";
		exit;
	}
}
?>
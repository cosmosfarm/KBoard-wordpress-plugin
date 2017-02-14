<?php
/**
 * KBoard Admin Controller
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBAdminController {
	
	public function __construct(){
		add_action('admin_post_kboard_update_action', array($this, 'update'));
		add_action('admin_post_kboard_backup_download', array($this, 'backup'));
		add_action('admin_post_kboard_restore_execute', array($this, 'restore'));
		add_action('admin_post_kboard_latestview_action', array($this, 'latestview_update'));
		add_action('wp_ajax_kboard_content_list_update', array($this, 'content_list_update'));
		add_action('wp_ajax_kboard_system_option_update', array($this, 'system_option_update'));
	}
	
	/**
	 * 게시판 정보 수정
	 */
	public function update(){
		global $wpdb;
		if(!defined('KBOARD_COMMNETS_VERSION')) die('<script>alert("게시판 생성 실패!\nKBoard 댓글 플러그인을 설치해주세요.\nhttp://www.cosmosfarm.com/ 에서 다운로드 가능합니다.");history.go(-1);</script>');
		if(!current_user_can('activate_plugins')) wp_die(__('You do not have permission.', 'kboard'));
		if(isset($_POST['kboard-setting-execute-nonce']) && wp_verify_nonce($_POST['kboard-setting-execute-nonce'], 'kboard-setting-execute')){
			
			$_POST = stripslashes_deep($_POST);
			
			$board_id = isset($_POST['board_id'])?intval($_POST['board_id']):'';
			$board_name = isset($_POST['board_name'])?esc_sql($_POST['board_name']):'';
			$skin = isset($_POST['skin'])?$_POST['skin']:'';
			$page_rpp = isset($_POST['page_rpp'])?$_POST['page_rpp']:'';
			$use_comment = isset($_POST['use_comment'])?$_POST['use_comment']:'';
			$use_editor = isset($_POST['use_editor'])?$_POST['use_editor']:'';
			$permission_read = isset($_POST['permission_read'])?$_POST['permission_read']:'';
			$permission_write = isset($_POST['permission_write'])?$_POST['permission_write']:'';
			$admin_user = isset($_POST['admin_user'])?implode(',', array_map('esc_sql', array_map('trim', explode(',', $_POST['admin_user'])))):'';
			$use_category = isset($_POST['use_category'])?$_POST['use_category']:'';
			$category1_list = isset($_POST['category1_list'])?implode(',', array_map('esc_sql', array_map('trim', explode(',', $_POST['category1_list'])))):'';
			$category2_list = isset($_POST['category2_list'])?implode(',', array_map('esc_sql', array_map('trim', explode(',', $_POST['category2_list'])))):'';
			$create = date('YmdHis', current_time('timestamp'));
			
			$auto_page = isset($_POST['auto_page'])?$_POST['auto_page']:'';
			if($auto_page){
				$auto_page_board_id = $wpdb->get_var("SELECT `board_id` FROM `{$wpdb->prefix}kboard_board_meta` WHERE `key`='auto_page' AND `value`='$auto_page'");
				if($auto_page_board_id && $auto_page_board_id != $board_id){
					$meta->auto_page = '';
					echo '<script>alert("게시판 자동 설치 페이지에 이미 연결된 게시판이 존재합니다. 페이지당 하나의 게시판만 설치 가능합니다.");history.go(-1);</script>';
					exit;
				}
			}
			
			if(!$board_id){
				$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_board_setting` (`board_name`, `skin`, `page_rpp`, `use_comment`, `use_editor`, `permission_read`, `permission_write`, `admin_user`, `use_category`, `category1_list`, `category2_list`, `created`) VALUE ('$board_name', '$skin', '$page_rpp', '$use_comment', '$use_editor', '$permission_read', '$permission_write', '$admin_user', '$use_category', '$category1_list', '$category2_list', '$create')");
				$board_id = $wpdb->insert_id;
			}
			else{
				$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_setting` SET `board_name`='$board_name', `skin`='$skin', `page_rpp`='$page_rpp', `use_comment`='$use_comment', `use_editor`='$use_editor', `permission_read`='$permission_read', `permission_write`='$permission_write', `use_category`='$use_category', `category1_list`='$category1_list', `category2_list`='$category2_list', `admin_user`='$admin_user' WHERE `uid`='$board_id'");
			}
			
			$meta = new KBoardMeta($board_id);
			$meta->auto_page = $auto_page;
			$meta->use_direct_url = isset($_POST['use_direct_url'])?$_POST['use_direct_url']:'';
			$meta->latest_alerts = isset($_POST['latest_alerts'])?implode(',', array_map('esc_sql', array_map('trim', explode(',', $_POST['latest_alerts'])))):'';
			$meta->comment_skin = ($use_comment && isset($_POST['comment_skin']))?$_POST['comment_skin']:'';
			$meta->default_content = isset($_POST['default_content'])?$_POST['default_content']:'';
			$meta->pass_autop = isset($_POST['pass_autop'])?$_POST['pass_autop']:'';
			$meta->shortcode_execute = isset($_POST['shortcode_execute'])?$_POST['shortcode_execute']:'';
			$meta->autolink = isset($_POST['autolink'])?$_POST['autolink']:'';
			$meta->reply_copy_content = isset($_POST['reply_copy_content'])?$_POST['reply_copy_content']:'';
			$meta->view_iframe = isset($_POST['view_iframe'])?$_POST['view_iframe']:'';
			$meta->permission_comment_write = isset($_POST['permission_comment_write'])?$_POST['permission_comment_write']:'';
			$meta->comments_plugin_id = isset($_POST['comments_plugin_id'])?$_POST['comments_plugin_id']:'';
			$meta->use_comments_plugin = isset($_POST['use_comments_plugin'])?$_POST['use_comments_plugin']:'';
			$meta->comments_plugin_row = isset($_POST['comments_plugin_row'])?$_POST['comments_plugin_row']:'';
			$meta->conversion_tracking_code = isset($_POST['conversion_tracking_code'])?$_POST['conversion_tracking_code']:'';
			$meta->always_view_list = isset($_POST['always_view_list'])?$_POST['always_view_list']:'';
			$meta->max_attached_count = isset($_POST['max_attached_count'])?$_POST['max_attached_count']:'';
			$meta->permit = isset($_POST['permit'])?$_POST['permit']:'';
			$meta->default_build_mod = isset($_POST['default_build_mod'])?$_POST['default_build_mod']:'';
			$meta->after_executing_mod = isset($_POST['after_executing_mod'])?$_POST['after_executing_mod']:'';
			$meta->add_menu_page = isset($_POST['add_menu_page'])?$_POST['add_menu_page']:'';
			$meta->permission_list = isset($_POST['permission_list'])?$_POST['permission_list']:'';
			$meta->permission_access = isset($_POST['permission_access'])?$_POST['permission_access']:'';
			
			if(isset($_POST['permission_read_roles'])){
				$meta->permission_read_roles = serialize($_POST['permission_read_roles']);
			}
			if(isset($_POST['permission_write_roles'])){
				$meta->permission_write_roles = serialize($_POST['permission_write_roles']);
			}
			if(isset($_POST['permission_comment_write_roles'])){
				$meta->permission_comment_write_roles = serialize($_POST['permission_comment_write_roles']);
			}
			
			// kboard_extends_setting_update 액션 실행
			do_action('kboard_extends_setting_update', $meta, $board_id);
	
			$tab_kboard_setting = isset($_POST['tab_kboard_setting'])?'#tab-kboard-setting-'.intval($_POST['tab_kboard_setting']):'';
			wp_redirect(admin_url('admin.php?page=kboard_list&board_id=' . $board_id . $tab_kboard_setting));
		}
		else{
			wp_redirect(admin_url('admin.php?page=kboard_dashboard'));
		}
		exit;
	}
	
	/**
	 * 백업
	 */
	public function backup(){
		if(!current_user_can('activate_plugins')) wp_die(__('You do not have permission.', 'kboard'));
		if(isset($_POST['kboard-backup-download-nonce']) && wp_verify_nonce($_POST['kboard-backup-download-nonce'], 'kboard-backup-download')){
			header('Content-Type: text/html; charset=UTF-8');
				
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
		if(!current_user_can('activate_plugins')) wp_die(__('You do not have permission.', 'kboard'));
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
	
		echo '<script>window.location.href="'.admin_url("admin.php?page=kboard_latestview&latestview_uid={$latestview->uid}").'"</script>';
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
				$content->date = date('YmdHis', strtotime($_POST['date'][$uid] . ' ' . $_POST['time'][$uid]));
				$content->updateContent();
			}
		}
		exit;
	}
	
	/**
	 * 시스템 설정 업데이트
	 */
	public function system_option_update(){
		if(current_user_can('activate_plugins')){
			$option_name = isset($_POST['option'])?$_POST['option']:'';
			$new_value = isset($_POST['value'])?$_POST['value']:'';
			if(!$new_value){
				delete_option($option_name);
			}
			else if(get_option($option_name) !== false){
				update_option($option_name, $new_value, 'yes');
			}
			else{
				add_option($option_name, $new_value, '', 'yes');
			}
		}
		exit;
	}
}
?>
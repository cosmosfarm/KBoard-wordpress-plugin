<?php
/**
 * KBoard Admin Controller
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBAdminController {
	
	public function __construct(){
		add_action('admin_post_kboard_update_execute', array($this, 'update'));
		add_action('admin_post_kboard_backup_download', array($this, 'backup'));
		add_action('admin_post_kboard_restore_execute', array($this, 'restore'));
		add_action('admin_post_kboard_latestview_action', array($this, 'latestview_update'));
		add_action('admin_post_kboard_csv_download_execute', array($this, 'csv_download'));
		add_action('admin_post_kboard_csv_upload_execute', array($this, 'csv_upload'));
		add_action('wp_ajax_kboard_content_list_update', array($this, 'content_list_update'));
		add_action('wp_ajax_kboard_system_option_update', array($this, 'system_option_update'));
		add_action('wp_ajax_kboard_tree_category_update', array($this, 'tree_category_update'));
		add_action('wp_ajax_kboard_tree_category_sortable', array($this, 'tree_category_sortable'));
	}
	
	/**
	 * 게시판 정보 수정
	 */
	public function update(){
		global $wpdb;
		if(!defined('KBOARD_COMMNETS_VERSION')) die('<script>alert("게시판 생성 실패!\nKBoard 댓글 플러그인을 설치해주세요.\nhttp://www.cosmosfarm.com/ 에서 다운로드 가능합니다.");history.go(-1);</script>');
		if(!current_user_can('manage_options')) wp_die(__('You do not have permission.', 'kboard'));
		if(isset($_POST['kboard-setting-execute-nonce']) && wp_verify_nonce($_POST['kboard-setting-execute-nonce'], 'kboard-setting-execute')){
			
			header('Content-Type: text/html; charset=UTF-8');
			
			$_POST = stripslashes_deep($_POST);
			
			$board_id         = isset($_POST['board_id'])         ? intval($_POST['board_id'])                               : '';
			$board_name       = isset($_POST['board_name'])       ? esc_sql(sanitize_text_field($_POST['board_name']))       : '';
			$skin             = isset($_POST['skin'])             ? esc_sql(sanitize_text_field($_POST['skin']))             : '';
			$page_rpp         = isset($_POST['page_rpp'])         ? esc_sql(sanitize_text_field($_POST['page_rpp']))         : '';
			$use_comment      = isset($_POST['use_comment'])      ? esc_sql(sanitize_text_field($_POST['use_comment']))      : '';
			$use_editor       = isset($_POST['use_editor'])       ? esc_sql(sanitize_text_field($_POST['use_editor']))       : '';
			$permission_read  = isset($_POST['permission_read'])  ? esc_sql(sanitize_text_field($_POST['permission_read']))  : '';
			$permission_write = isset($_POST['permission_write']) ? esc_sql(sanitize_text_field($_POST['permission_write'])) : '';
			$admin_user       = isset($_POST['admin_user'])       ? implode(',', array_map('esc_sql', array_map('sanitize_text_field', explode(',', $_POST['admin_user']))))     : '';
			$use_category     = isset($_POST['use_category'])     ? esc_sql(sanitize_text_field($_POST['use_category']))     : '';
			$category1_list   = isset($_POST['category1_list'])   ? implode(',', array_map('esc_sql', array_map('sanitize_text_field', explode(',', $_POST['category1_list'])))) : '';
			$category2_list   = isset($_POST['category2_list'])   ? implode(',', array_map('esc_sql', array_map('sanitize_text_field', explode(',', $_POST['category2_list'])))) : '';
			
			$auto_page = isset($_POST['auto_page']) ? intval($_POST['auto_page']) : '';
			if($auto_page){
				$auto_page_board_id = $wpdb->get_var("SELECT `board_id` FROM `{$wpdb->prefix}kboard_board_meta` WHERE `key`='auto_page' AND `value`='$auto_page'");
				if($auto_page_board_id && $auto_page_board_id != $board_id){
					$auto_page = '';
					echo '<script>alert("게시판 자동 설치 페이지에 이미 연결된 게시판이 존재합니다. 페이지당 하나의 게시판만 설치 가능합니다.");window.history.go(-1);</script>';
					exit;
				}
			}
			
			if(!$board_id){
				$wpdb->insert(
					"{$wpdb->prefix}kboard_board_setting",
					array(
						'board_name'       => $board_name,
						'skin'             => $skin,
						'page_rpp'         => $page_rpp,
						'use_comment'      => $use_comment,
						'use_editor'       => $use_editor,
						'permission_read'  => $permission_read,
						'permission_write' => $permission_write,
						'admin_user'       => $admin_user,
						'use_category'     => $use_category,
						'category1_list'   => $category1_list,
						'category2_list'   => $category2_list,
						'created'          => date('YmdHis', current_time('timestamp'))
					),
					array('%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
				);
				$board_id = $wpdb->insert_id;
			}
			else{
				$wpdb->update(
					"{$wpdb->prefix}kboard_board_setting",
					array(
						'board_name'       => $board_name,
						'skin'             => $skin,
						'page_rpp'         => $page_rpp,
						'use_comment'      => $use_comment,
						'use_editor'       => $use_editor,
						'permission_read'  => $permission_read,
						'permission_write' => $permission_write,
						'use_category'     => $use_category,
						'category1_list'   => $category1_list,
						'category2_list'   => $category2_list,
						'admin_user'       => $admin_user
					),
					array('uid' => $board_id),
					array('%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
					array('%d')
				);
			}
			
			$board = new KBoard($board_id);
			
			if($board->id){
				$board->meta->auto_page = $auto_page;
				$board->meta->latest_target_page             = isset($_POST['latest_target_page'])             ? $_POST['latest_target_page']              : '';
				$board->meta->use_direct_url                 = isset($_POST['use_direct_url'])                 ? $_POST['use_direct_url']                  : '';
				$board->meta->latest_alerts                  = isset($_POST['latest_alerts'])                  ? implode(',', array_map('sanitize_text_field', explode(',', $_POST['latest_alerts']))) : '';
				$board->meta->latest_alerts_attachments_size      = isset($_POST['latest_alerts_attachments_size'])      ? $_POST['latest_alerts_attachments_size']       : '';
				$board->meta->comment_skin                   = ($use_comment && isset($_POST['comment_skin'])) ? $_POST['comment_skin']                    : '';
				$board->meta->use_tree_category              = isset($_POST['use_tree_category'])              ? $_POST['use_tree_category']               : '';
				$board->meta->default_content                = isset($_POST['default_content'])                ? $_POST['default_content']                 : '';
				$board->meta->pass_autop                     = isset($_POST['pass_autop'])                     ? $_POST['pass_autop']                      : '';
				$board->meta->shortcode_execute              = isset($_POST['shortcode_execute'])              ? $_POST['shortcode_execute']               : '';
				$board->meta->autolink                       = isset($_POST['autolink'])                       ? $_POST['autolink']                        : '';
				$board->meta->reply_copy_content             = isset($_POST['reply_copy_content'])             ? $_POST['reply_copy_content']              : '';
				$board->meta->view_iframe                    = isset($_POST['view_iframe'])                    ? $_POST['view_iframe']                     : '';
				$board->meta->editor_view_iframe             = isset($_POST['editor_view_iframe'])             ? $_POST['editor_view_iframe']              : '';
				$board->meta->permission_list                = isset($_POST['permission_list'])                ? $_POST['permission_list']                 : '';
				$board->meta->permission_access              = isset($_POST['permission_access'])              ? $_POST['permission_access']               : '';
				$board->meta->permission_reply               = isset($_POST['permission_reply'])               ? $_POST['permission_reply']                : '';
				$board->meta->permission_comment_write       = isset($_POST['permission_comment_write'])       ? $_POST['permission_comment_write']        : '';
				$board->meta->permission_comment_read        = isset($_POST['permission_comment_read'])        ? $_POST['permission_comment_read']         : '';
				$board->meta->permission_comment_read_minute = isset($_POST['permission_comment_read_minute']) ? $_POST['permission_comment_read_minute']  : '';
				$board->meta->permission_order               = isset($_POST['permission_order'])               ? $_POST['permission_order']                : '';
				$board->meta->permission_attachment_download = isset($_POST['permission_attachment_download']) ? $_POST['permission_attachment_download']  : '';
				$board->meta->permission_vote                = isset($_POST['permission_vote'])                ? $_POST['permission_vote']                 : '';
				$board->meta->comments_plugin_id             = isset($_POST['comments_plugin_id'])             ? $_POST['comments_plugin_id']              : '';
				$board->meta->use_comments_plugin            = isset($_POST['use_comments_plugin'])            ? $_POST['use_comments_plugin']             : '';
				$board->meta->comments_plugin_row            = isset($_POST['comments_plugin_row'])            ? $_POST['comments_plugin_row']             : '';
				$board->meta->conversion_tracking_code       = isset($_POST['conversion_tracking_code'])       ? $_POST['conversion_tracking_code']        : '';
				$board->meta->always_view_list               = isset($_POST['always_view_list'])               ? $_POST['always_view_list']                : '';
				$board->meta->max_attached_count             = isset($_POST['max_attached_count'])             ? $_POST['max_attached_count']              : '';
				$board->meta->list_sort_numbers              = isset($_POST['list_sort_numbers'])              ? $_POST['list_sort_numbers']               : '';
				$board->meta->permit                         = isset($_POST['permit'])                         ? $_POST['permit']                          : '';
				$board->meta->secret_checked_default         = isset($_POST['secret_checked_default'])         ? $_POST['secret_checked_default']          : '';
				$board->meta->default_build_mod              = isset($_POST['default_build_mod'])              ? $_POST['default_build_mod']               : '';
				$board->meta->after_executing_mod            = isset($_POST['after_executing_mod'])            ? $_POST['after_executing_mod']             : '';
				$board->meta->add_menu_page                  = isset($_POST['add_menu_page'])                  ? $_POST['add_menu_page']                   : '';
				
				if(isset($_POST['permission_read_roles'])){
					$board->meta->permission_read_roles = serialize($_POST['permission_read_roles']);
				}
				if(isset($_POST['permission_write_roles'])){
					$board->meta->permission_write_roles = serialize($_POST['permission_write_roles']);
				}
				if(isset($_POST['permission_reply_roles'])){
					$board->meta->permission_reply_roles = serialize($_POST['permission_reply_roles']);
				}
				if(isset($_POST['permission_comment_write_roles'])){
					$board->meta->permission_comment_write_roles = serialize($_POST['permission_comment_write_roles']);
				}
				if(isset($_POST['permission_order_roles'])){
					$board->meta->permission_order_roles = serialize($_POST['permission_order_roles']);
				}
				if(isset($_POST['permission_admin_roles'])){
					$board->meta->permission_admin_roles = serialize($_POST['permission_admin_roles']);
				}
				if(isset($_POST['permission_vote_roles'])){
					$board->meta->permission_vote_roles = serialize($_POST['permission_vote_roles']);
				}
				if(isset($_POST['permission_attachment_download_roles'])){
					$board->meta->permission_attachment_download_roles = serialize($_POST['permission_attachment_download_roles']);
				}
				
				$board->meta->skin_fields                    = isset($_POST['fields'])                         ? serialize($_POST['fields'])                   : '';
				
				$board->meta->document_insert_up_point       = isset($_POST['document_insert_up_point'])       ? abs($_POST['document_insert_up_point'])       : '';
				$board->meta->document_insert_down_point     = isset($_POST['document_insert_down_point'])     ? abs($_POST['document_insert_down_point'])     : '';
				$board->meta->document_delete_up_point       = isset($_POST['document_delete_up_point'])       ? abs($_POST['document_delete_up_point'])       : '';
				$board->meta->document_delete_down_point     = isset($_POST['document_delete_down_point'])     ? abs($_POST['document_delete_down_point'])     : '';
				$board->meta->document_read_down_point       = isset($_POST['document_read_down_point'])       ? abs($_POST['document_read_down_point'])       : '';
				$board->meta->attachment_download_down_point = isset($_POST['attachment_download_down_point']) ? abs($_POST['attachment_download_down_point']) : '';
				$board->meta->comment_insert_up_point        = isset($_POST['comment_insert_up_point'])        ? abs($_POST['comment_insert_up_point'])        : '';
				$board->meta->comment_insert_down_point      = isset($_POST['comment_insert_down_point'])      ? abs($_POST['comment_insert_down_point'])      : '';
				$board->meta->comment_delete_up_point        = isset($_POST['comment_delete_up_point'])        ? abs($_POST['comment_delete_up_point'])        : '';
				$board->meta->comment_delete_down_point      = isset($_POST['comment_delete_down_point'])      ? abs($_POST['comment_delete_down_point'])      : '';
				
				// kboard_extends_setting_update 액션 실행
				do_action('kboard_extends_setting_update', $board->meta, $board_id);
				do_action("kboard_{$board->skin}_extends_setting_update", $board->meta, $board_id);
				
				$tab_kboard_setting = isset($_POST['tab_kboard_setting'])?'#tab-kboard-setting-'.intval($_POST['tab_kboard_setting']):'';
				wp_redirect(admin_url('admin.php?page=kboard_list&board_id=' . $board_id . $tab_kboard_setting));
				exit;
			}
		}
		wp_redirect(admin_url('admin.php?page=kboard_dashboard'));
		exit;
	}
	
	/**
	 * 백업
	 */
	public function backup(){
		if(!current_user_can('manage_options')) wp_die(__('You do not have permission.', 'kboard'));
		if(isset($_POST['kboard-backup-download-nonce']) && wp_verify_nonce($_POST['kboard-backup-download-nonce'], 'kboard-backup-download')){
			set_time_limit(0);
			ini_set('memory_limit', '-1');
			
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
		if(!current_user_can('manage_options')) wp_die(__('You do not have permission.', 'kboard'));
		if(isset($_POST['kboard-restore-execute-nonce']) && wp_verify_nonce($_POST['kboard-restore-execute-nonce'], 'kboard-restore-execute')){
			set_time_limit(0);
			ini_set('memory_limit', '-1');
			
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
			}
			else{
				echo '<script>alert("'.__('파일 업로드에 실패 했습니다.', 'kboard').'");</script>';
			}
			
			if($xmlfile) unlink($xmlfile);
		}
		$redirect_url = admin_url('admin.php?page=kboard_backup');
		echo "<script>window.location.href='{$redirect_url}';</script>";
		exit;
	}
	
	/**
	 * 최신글 뷰 업데이트
	 */
	function latestview_update(){
		if(!current_user_can('manage_options')) wp_die(__('You do not have permission.', 'kboard'));
		
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
	
	public function csv_download(){
		global $wpdb;
		if(!current_user_can('manage_options')) wp_die(__('You do not have permission.', 'kboard'));
		if(isset($_GET['kboard-csv-download-execute-nonce']) && wp_verify_nonce($_GET['kboard-csv-download-execute-nonce'], 'kboard-csv-download-execute')){
			set_time_limit(0);
			ini_set('memory_limit', '-1');
			
			header('Content-Type: text/html; charset=UTF-8');
			
			$board_id = isset($_GET['board_id'])?$_GET['board_id']:'';
			$board = new KBoard($board_id);
			
			if($board->id){
				$date = date('YmdHis', current_time('timestamp'));
				$filename = "KBoard-{$board->id}-{$date}.csv";
				
				$columns = $wpdb->get_col("DESCRIBE `{$wpdb->prefix}kboard_board_content`");
				$option = $wpdb->get_col("SELECT DISTINCT(`option_key`) FROM `{$wpdb->prefix}kboard_board_option` AS `option` LEFT JOIN `{$wpdb->prefix}kboard_board_content` AS `content` ON `option`.`content_uid`=`content`.`uid` WHERE `content`.`board_id`='{$board->id}'");
				
				foreach($option as $option_key){
					$columns[] = KBContent::$SKIN_OPTION_PREFIX . $option_key;
				}
				
				header('Content-type: application/csv');
				header('Content-Disposition: attachment; filename="'.$filename.'"');
				header('Pragma: no-cache');
				header('Expires: 0');
				
				@ob_clean();
				@flush();
				
				$csv = fopen('php://output', 'w');
				
				fprintf($csv, chr(0xEF).chr(0xBB).chr(0xBF));
				fputcsv($csv, $columns);
				
				$list = new KBContentList($board_id);
				$list->rpp(1000);
				$list->orderASC('uid');
				$list->initFirstList();
				
				while($list->hasNextList()){
					while($content = $list->hasNext()){
						$row_data = $content->toArray();
						
						$row_data['date'] = date('Y-m-d H:i:s', strtotime($row_data['date']));
						$row_data['update'] = date('Y-m-d H:i:s', strtotime($row_data['update']));
						
						foreach($option as $option_key){
							$option_value = $content->option->{$option_key};
							if(is_array($option_value)){
								$row_data[] = json_encode($option_value, JSON_UNESCAPED_UNICODE);
							}
							else{
								$row_data[] = $option_value;
							}
						}
						
						fputcsv($csv, $row_data);
					}
					@ob_flush();
					@flush();
				}
				
				fclose($csv);
				exit;
			}
		}
		wp_redirect(admin_url('admin.php?page=kboard_dashboard'));
		exit;
	}
	
	public function csv_upload(){
		global $wpdb;
		if(!current_user_can('manage_options')) wp_die(__('You do not have permission.', 'kboard'));
		if(isset($_POST['kboard-setting-execute-nonce']) && wp_verify_nonce($_POST['kboard-setting-execute-nonce'], 'kboard-setting-execute')){
			set_time_limit(0);
			ini_set('memory_limit', '-1');
			
			header('Content-Type: text/html; charset=UTF-8');
			
			$board_id = isset($_POST['board_id'])?$_POST['board_id']:'';
			$board = new KBoard($board_id);
			
			$option = isset($_POST['kboard_csv_upload_option'])?$_POST['kboard_csv_upload_option']:'';
			if(!in_array($option, array('keep', 'update', 'delete'))) $option = 'keep';
			
			$file = $_FILES['kboard_csv_upload_file']['tmp_name'];
			$file_name = basename($_FILES['kboard_csv_upload_file']['name']);
			
			if(is_uploaded_file($file) && $board->id){
				$file_extension = explode('.', $file_name);
				
				if(end($file_extension) == 'csv' || end($file_extension) == 'CSV'){
					if(($handle = fopen($file, 'r')) !== false){
						$length = 0;
						
						while(($data = fgetcsv($handle, 0, ',')) !== false){
							$total = count($data);
							
							for($index=0; $index<$total; $index++){
								$value = $data[$index];
								
								// 인코딩 변환
								if(function_exists('mb_detect_encoding')){
									$encoding = mb_detect_encoding($value, 'auto');
									if($encoding != 'UTF-8'){
										$value = @iconv($encoding, 'UTF-8//TRANSLIT', $value);
									}
								}
								
								if($length){
									// 데이터
									if($columns[$index] == 'date' || $columns[$index] == 'update'){
										$value = date('YmdHis', strtotime($value));
									}
									
									$decode_value = json_decode($value);
									if(is_array($decode_value)){
										$row_data[$columns[$index]] = $decode_value;
									}
									else{
										$row_data[$columns[$index]] = $value;
									}
								}
								else{
									// 컬럼
									$columns[] = $value;
								}
							}
							
							if(isset($row_data)) $rows[] = $row_data;
							$length++;
						}
						
						if($length >= 2){
							$content = new KBContent();
							$content->setBoardID($board->id);
							
							if($option == 'keep'){ // insert
								foreach($rows as $key=>$row){
									if(!isset($row['parent_uid']) || !intval($row['parent_uid'])){
										$row['board_id'] = $board->id;
										$insert_id = $content->insertContent($row);
										if(isset($row['﻿uid']) ) $new_uids[] = $insert_id;
										$content->updateOptions($row);
										unset($rows[$key]);
										@ob_flush();
										@flush();
									}
								}
								foreach($rows as $key=>$row){
									$row['board_id'] = $board->id;
									$row['parent_uid'] = $new_uids[$row['parent_uid']];
									$content->insertContent($row);
									$content->updateOptions($row);
									unset($rows[$key]);
									@ob_flush();
									@flush();
								}
							}
							else if($option == 'update'){ // update
								foreach($rows as $key=>$row){
									if(isset($row['﻿uid'])){
										$row['board_id'] = $board->id;
										$content->initWithUID($row['﻿uid']);
										$content->updateContent($row);
										$content->updateOptions($row);
										unset($rows[$key]);
										@ob_flush();
										@flush();
									}
								}
							}
							else if($option == 'delete'){ // delete
								$board->truncate();
								foreach($rows as $key=>$row){
									if(!isset($row['parent_uid']) || !intval($row['parent_uid'])){
										$row['board_id'] = $board->id;
										$insert_id = $content->insertContent($row);
										if(isset($row['﻿uid']) ) $new_uids[] = $insert_id;
										$content->updateOptions($row);
										unset($rows[$key]);
										@ob_flush();
										@flush();
									}
								}
								foreach($rows as $key=>$row){
									$row['board_id'] = $board->id;
									$row['parent_uid'] = $new_uids[$row['parent_uid']];
									$content->insertContent($row);
									$content->updateOptions($row);
									unset($rows[$key]);
									@ob_flush();
									@flush();
								}
							}
							
							$board->resetTotal();
						}
						
						fclose($handle);
					}
					echo '<script>alert("CSV 파일을 업로드했습니다.");</script>';
				}
				else{
					echo '<script>alert("CSV 파일만 업로드 가능합니다.");</script>';
				}
			}
			else{
				echo '<script>alert("파일 업로드에 실패했습니다");</script>';
			}
			
			if($file) unlink($file);
			
			$tab_kboard_setting = isset($_POST['tab_kboard_setting'])?'#tab-kboard-setting-'.intval($_POST['tab_kboard_setting']):'';
			$redirect_url = admin_url('admin.php?page=kboard_list&board_id=' . $board_id . $tab_kboard_setting);
			echo "<script>window.location.href='{$redirect_url}'</script>";
			exit;
		}
		wp_redirect(admin_url('admin.php?page=kboard_dashboard'));
		exit;
	}
	
	/**
	 * 전체 게시글 정보 업데이트
	 */
	public function content_list_update(){
		if(current_user_can('manage_options')){
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
		if(current_user_can('manage_options')){
			
			$_POST = stripslashes_deep($_POST);
			
			$option = isset($_POST['option'])?$_POST['option']:array();
			
			foreach($option as $name=>$value){
				$value = sanitize_textarea_field($value);
				
				if(!$value){
					delete_option($name);
				}
				else if(get_option($name) !== false){
					update_option($name, $value, 'yes');
				}
				else{
					add_option($name, $value, '', 'yes');
				}
			}
		}
		exit;
	}
	
	/**
	 * 계층형 카테고리 업데이트
	 */
	public function tree_category_update(){
		if(!current_user_can('manage_options')) wp_die(__('You do not have permission.', 'kboard'));
		
		$_POST = stripslashes_deep($_POST);
		
		$board_id = isset($_POST['board_id'])?$_POST['board_id']:'';
		
		$tree_category = array();
		if(isset($_POST['tree_category'])){
			parse_str($_POST['tree_category'], $tree_category);
		}
		
		$board = new KBoard($board_id);
		$category = new KBoardTreeCategory();
		$category->setBoardID($board_id);
		$board->meta->tree_category = serialize($tree_category['tree_category']);
		$category->setTreeCategory($tree_category['tree_category']);
		$build_tree_category = $category->buildAdminTreeCategory();
		
		$table_body = $category->buildAdminTreeCategorySortableRow($build_tree_category);
		
		wp_send_json(array('table_body'=>$table_body));
	}
	
	/**
	 * 계층형 카테고리 순서 변경
	 */
	public function tree_category_sortable(){
		if(!current_user_can('manage_options')) wp_die(__('You do not have permission.', 'kboard'));
		
		$_POST = stripslashes_deep($_POST);
		
		$tree_category_serialize = isset($_POST['tree_category_serialize'])?json_decode($_POST['tree_category_serialize']):'';
		$board_id = isset($_POST['board_id'])?$_POST['board_id']:'';
		
		$board = new KBoard($board_id);
		$category = new KBoardTreeCategory();
		$category->setBoardID($board_id);
		
		$sortable_category = array();
		
		foreach($tree_category_serialize as $item){
			if(isset($item->id) && $item->id){
				foreach($category->tree_category as $key=>$value){
					if($item->id == $value['id']){
						$value['parent_id'] = $item->parent_id;
						$sortable_category[] = $value;
					}
				}
			}
		}
		
		$board->meta->tree_category = serialize($sortable_category);
		$category->setTreeCategory($sortable_category);
		$build_tree_category = $category->buildAdminTreeCategory();
		
		$table_body = $category->buildAdminTreeCategorySortableRow($build_tree_category);
		
		wp_send_json(array('table_body'=>$table_body));
	}
}
?>
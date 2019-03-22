<?php
/**
 * KBoard Controller
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBController {
	
	public function __construct(){
		$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
		switch($action){
			case 'kboard_editor_execute': add_action('wp_loaded', array($this, 'editorExecute'), 0); break;
			case 'kboard_media_upload': add_action('wp_loaded', array($this, 'mediaUpload'), 0); break;
			case 'kboard_media_delete': add_action('wp_loaded', array($this, 'mediaDelete'), 0); break;
			case 'kboard_file_delete': add_action('wp_loaded', array($this, 'fileDelete'), 0); break;
			case 'kboard_file_download': add_action('wp_loaded', array($this, 'fileDownload'), 0); break;
			case 'kboard_iamport_endpoint': add_action('wp_loaded', array($this, 'iamportEndpoint'), 0); break;
			case 'kboard_iamport_notification': add_action('wp_loaded', array($this, 'iamportNotification'), 0); break;
			case 'kboard_order_execute': add_action('wp_loaded', array($this, 'orderExecute'), 0); break;
		}
		
		add_action('wp_ajax_kboard_document_like', array($this, 'documentLike'));
		add_action('wp_ajax_nopriv_kboard_document_like', array($this, 'documentLike'));
		add_action('wp_ajax_kboard_document_unlike', array($this, 'documentUnlike'));
		add_action('wp_ajax_nopriv_kboard_document_unlike', array($this, 'documentUnlike'));
		add_action('wp_ajax_kboard_order_item_update', array($this, 'orderItemUpdate'));
		add_action('wp_ajax_kboard_content_update', array($this, 'contentUpdate'));
		add_action('wp_ajax_nopriv_kboard_content_update', array($this, 'contentUpdate'));
	}
	
	/**
	 * 게시글 등록 및 수정
	 */
	public function editorExecute(){
		if(isset($_POST['kboard-editor-execute-nonce']) && wp_verify_nonce($_POST['kboard-editor-execute-nonce'], 'kboard-editor-execute')){
			kboard_switch_to_blog();
			
			header('Content-Type: text/html; charset=UTF-8');
			
			$_POST = stripslashes_deep($_POST);
			
			$uid = isset($_POST['uid'])?intval($_POST['uid']):0;
			$board_id = isset($_POST['board_id'])?intval($_POST['board_id']):0;
			
			$content = new KBContent();
			$content->initWithUID($uid);
			$content->setBoardID($board_id);
			$content->saveTemporary();
			$board = $content->getBoard();
			
			if(!$content->uid && !$board->isWriter()){
				die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
			}
			else if($content->uid && !$content->isEditor()){
				if($board->permission_write=='all' && !$content->member_uid){
					if(!$content->isConfirm()){
						die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
					}
				}
				else{
					die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
				}
			}
			
			$content->new_password = isset($_POST['password'])?sanitize_text_field($_POST['password']):$content->password;
			
			if(!$board->id){
				die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
			}
			else if(!$content->title){
				die("<script>alert('".__('Please enter the title.', 'kboard')."');history.go(-1);</script>");
			}
			else if(!is_user_logged_in() && !$content->new_password){
				die("<script>alert('".__('Please enter the password.', 'kboard')."');history.go(-1);</script>");
			}
			
			if($content->execute_action == 'update'){
				if(isset($_POST['kboard-editor-content-nonce'])){
					if(!wp_verify_nonce($_POST['kboard-editor-content-nonce'], "kboard-editor-content-{$content->uid}")){
						die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
					}
				}
			}
			
			// 금지단어 체크
			if(!$board->isAdmin()){
				$replace = array(' ', '「', '」', '『', '』', '-', '_', '.', '(', ')', '［', '］', ',', '~', '＊', '+', '^', '♥', '★', '!', '#', '=', '­', '[', ']', '/', '▶', '▷', '<', '>', '%', ':', 'ღ', '$', '*', '♣', '♧', '☞');
				
				// 작성자 금지단어 체크
				$name_filter = kboard_name_filter(true);
				if($name_filter){
					$subject = urldecode($content->member_display);
					$subject = strtolower($subject);
					$subject = str_replace($replace, '', $subject);
					
					$name_filter_message = get_option('kboard_name_filter_message', '');
					
					foreach($name_filter as $filter){
						if($filter && strpos($subject, $filter) !== false){
							if(!$name_filter_message){
								$name_filter_message = sprintf(__('%s is not available.', 'kboard'), $filter);
							}
							$name_filter_message = apply_filters('kboard_name_filter_message', $name_filter_message, $filter, $subject, $board);
							die("<script>alert('".$name_filter_message."');history.go(-1);</script>");
						}
					}
				}
				
				// 본문/제목/댓글 금지단어 체크
				$content_filter = kboard_content_filter(true);
				if($content_filter){
					$subject = urldecode($content->content);
					$subject = strtolower($subject);
					$subject = str_replace($replace, '', $subject);
					
					$content_filter_message = get_option('kboard_content_filter_message', '');
					
					foreach($content_filter as $filter){
						if($filter && strpos($subject, $filter) !== false){
							if(!$content_filter_message){
								$content_filter_message = sprintf(__('%s is not available.', 'kboard'), $filter);
							}
							$content_filter_message = apply_filters('kboard_content_filter_message', $content_filter_message, $filter, $subject, $board);
							die("<script>alert('".$content_filter_message."');history.go(-1);</script>");
						}
					}
					
					$subject = urldecode($content->title);
					$subject = strtolower($subject);
					$subject = str_replace($replace, '', $subject);
					
					$content_filter_message = get_option('kboard_content_filter_message', '');
					
					foreach($content_filter as $filter){
						if($filter && strpos($subject, $filter) !== false){
							if(!$content_filter_message){
								$content_filter_message = sprintf(__('%s is not available.', 'kboard'), $filter);
							}
							$content_filter_message = apply_filters('kboard_content_filter_message', $content_filter_message, $filter, $subject, $board);
							die("<script>alert('".$content_filter_message."');history.go(-1);</script>");
						}
					}
				}
			}
			
			do_action('kboard_pre_content_execute', $content, $board);
			
			// 글쓰기 감소 포인트
			if($content->execute_action == 'insert' && $board->meta->document_insert_down_point){
				if(function_exists('mycred_add')){
					if(!is_user_logged_in()){
						die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
					}
					else{
						$balance = mycred_get_users_balance(get_current_user_id());
						if($board->meta->document_insert_down_point > $balance){
							die('<script>alert("'.__('You have not enough points.', 'kboard').'");history.go(-1);</script>');
						}
						else{
							$point = intval(get_user_meta($content->member_uid, 'kboard_document_mycred_point', true));
							update_user_meta($content->member_uid, 'kboard_document_mycred_point', $point + ($board->meta->document_insert_down_point*-1));
							
							mycred_add('document_insert_down_point', get_current_user_id(), ($board->meta->document_insert_down_point*-1), __('Writing decrease points', 'kboard'));
						}
					}
				}
			}
			
			// 실행
			$execute_uid = $content->execute();
			
			do_action('kboard_content_execute', $content, $board);
			
			// 글쓰기 증가 포인트
			if($content->execute_action == 'insert' && $board->meta->document_insert_up_point){
				if(function_exists('mycred_add')){
					if(is_user_logged_in()){
						$point = intval(get_user_meta($content->member_uid, 'kboard_document_mycred_point', true));
						update_user_meta($content->member_uid, 'kboard_document_mycred_point', $point + $board->meta->document_insert_up_point);
						
						mycred_add('document_insert_up_point', get_current_user_id(), $board->meta->document_insert_up_point, __('Writing increase points', 'kboard'));
					}
				}
			}
			
			// 비밀번호가 입력되면 즉시 인증과정을 거친다.
			if($content->password) $board->isConfirm($content->password, $execute_uid);
			
			$url = new KBUrl();
			
			if($content->execute_action == 'insert'){
				if(!$board->meta->after_executing_mod){
					$next_page_url = $url->set('execute_uid', $execute_uid)->set('uid', $execute_uid)->set('mod', 'document')->toString();
				}
				else{
					$next_page_url = $url->set('execute_uid', $execute_uid)->set('mod', $board->meta->after_executing_mod)->toString();
				}
			}
			else{
				$next_page_url = $url->set('uid', $execute_uid)->set('mod', 'document')->toString();
			}
			
			$next_page_url = apply_filters('kboard_after_executing_url', $next_page_url, $execute_uid, $board_id);
			
			do_action('kboard_content_execute_pre_redirect', $next_page_url, $content, $board);
			
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
			wp_redirect(home_url());
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
		
		$uid = isset($_GET['uid'])?intval($_GET['uid']):'';
		$file = isset($_GET['file'])?sanitize_key($_GET['file']):'';
		
		$content = new KBContent();
		$content->initWithUID($uid);
		$board = $content->getBoard();
		
		if(!isset($_GET['kboard-file-delete-nonce']) || !wp_verify_nonce($_GET['kboard-file-delete-nonce'], 'kboard-file-delete')){
			if(!wp_get_referer()){
				wp_die(__('This page is restricted from external access.', 'kboard'));
			}
		}
		
		if(!$content->uid || !$file){
			wp_die(__('You do not have permission.', 'kboard'));
		}
		
		if(!$content->isEditor()){
			if($board->permission_write=='all' && !$content->member_uid){
				if(!$content->isConfirm()){
					wp_die(__('You do not have permission.', 'kboard'));
				}
			}
			else{
				wp_die(__('You do not have permission.', 'kboard'));
			}
		}
		
		if($file == 'thumbnail'){
			$content->removeThumbnail();
		}
		else{
			$content->removeAttached($file);
		}

		wp_redirect(wp_get_referer());
		exit;
	}

	/**
	 * 첨부파일 다운로드
	 */
	public function fileDownload(){
		global $wpdb;
		
		header('X-Robots-Tag: noindex, nofollow'); // 검색엔진 수집 금지
		header('Content-Type: text/html; charset=UTF-8');
		
		$uid = isset($_GET['uid'])?intval($_GET['uid']):'';
		$comment_uid = isset($_GET['comment_uid'])?intval($_GET['comment_uid']):'';
		$file = isset($_GET['file'])?sanitize_key($_GET['file']):'';
		
		$content = new KBContent();
		$comment = new KBComment();
		
		if($comment_uid){
			$comment->initWithUID($comment_uid);
			$board = $content->getBoard();
			
			if(!$comment->uid){
				do_action('kboard_cannot_download_file', 'go_back', wp_get_referer(), $content, $board, $comment);
				exit;
			}
			
			$uid = $comment->content_uid;
		}
		
		$content->initWithUID($uid);
		$board = $content->getBoard();
		
		if(!isset($_GET['kboard-file-download-nonce']) || !wp_verify_nonce($_GET['kboard-file-download-nonce'], 'kboard-file-download')){
			if(!wp_get_referer()){
				wp_die(__('This page is restricted from external access.', 'kboard'));
			}
		}
		
		if(!$file){
			do_action('kboard_cannot_download_file', 'go_back', wp_get_referer(), $content, $board, $comment);
			exit;
		}
		
		if(!$content->uid){
			do_action('kboard_cannot_download_file', 'go_back', wp_get_referer(), $content, $board, $comment);
			exit;
		}
		
		if(!$content->isReader()){
			if($board->permission_read != 'all' && !is_user_logged_in()){
				do_action('kboard_cannot_download_file', 'go_login', wp_login_url(wp_get_referer()), $content, $board, $comment);
				exit;
			}
			else if($content->secret){
				if(!$content->isConfirm()){
					if($content->parent_uid){
						$parent = new KBContent();
						$parent->initWithUID($content->getTopContentUID());
						if(!$board->isReader($parent->member_uid, $content->secret) && !$parent->isConfirm()){
							do_action('kboard_cannot_download_file', 'go_back', wp_get_referer(), $content, $board, $comment);
							exit;
						}
					}
					else{
						do_action('kboard_cannot_download_file', 'go_back', wp_get_referer(), $content, $board, $comment);
						exit;
					}
				}
			}
			else{
				do_action('kboard_cannot_download_file', 'go_back', wp_get_referer(), $content, $board, $comment);
				exit;
			}
		}
		
		if(!$content->isAttachmentDownload()){
			if($board->meta->permission_attachment_download == '1' && !is_user_logged_in()){
				do_action('kboard_cannot_download_file', 'go_login', wp_login_url(wp_get_referer()), $content, $board, $comment);
				exit;
			}
			else{
				do_action('kboard_cannot_download_file', 'go_back', wp_get_referer(), $content, $board, $comment);
				exit;
			}
		}
		
		$file = esc_sql($file);
		
		if($comment->uid){
			$file_info = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_board_attached` WHERE `comment_uid`='{$comment->uid}' AND `file_key`='{$file}'");
		}
		else{
			$file_info = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_uid`='{$content->uid}' AND `file_key`='{$file}'");
		}
		
		$file_info = apply_filters('kboard_pre_download_file', $file_info, $content->uid, $board->id, $comment->uid);
		
		do_action('kboard_pre_file_download', $file_info, $content, $board, $comment);
		do_action("kboard_{$board->skin}_pre_file_download", $file_info, $content, $board, $comment);
		
		$ds = DIRECTORY_SEPARATOR;
		
		$content_dir_name = basename(WP_CONTENT_DIR);
		list($path) = explode("{$ds}{$content_dir_name}", dirname(__FILE__));
		$file_info->full_path = $path . str_replace('/', $ds, $file_info->file_path);
		
		if(!$file_info->file_path || !file_exists($file_info->full_path)){
			echo '<script>alert("'.__('File does not exist.', 'kboard').'");</script>';
			echo '<script>window.location.href="' . wp_get_referer() . '";</script>';
			exit;
		}
		
		$file_info->file_name = str_replace(' ' ,'-', $file_info->file_name);
		$file_info->mime_type = kboard_mime_type($file_info->full_path);
		$file_info->size = sprintf('%d', filesize($file_info->full_path));
		
		$file_info = apply_filters('kboard_download_file', $file_info, $content->uid, $board->id, $comment->uid);
		
		if(!$file_info->file_path || !file_exists($file_info->full_path)){
			echo '<script>alert("'.__('File does not exist.', 'kboard').'");</script>';
			echo '<script>window.location.href="' . wp_get_referer() . '";</script>';
			exit;
		}
		
		do_action('kboard_file_download', $file_info, $content, $board, $comment);
		do_action("kboard_{$board->skin}_file_download", $file_info, $content, $board, $comment);
		
		// 첨부파일 다운로드 감소 포인트
		if($board->meta->attachment_download_down_point){
			if(function_exists('mycred_add')){
				if(!is_user_logged_in()){
					do_action('kboard_cannot_download_file', 'go_back', wp_get_referer(), $content, $board, $comment);
					exit;
				}
				else if($content->member_uid != get_current_user_id()){
					$log_args['user_id'] = get_current_user_id();
					$log_args['ref'] = 'attachment_download_down_point';
					$log_args['ref_id'] = $content->uid;
					$log = new myCRED_Query_Log($log_args);
					
					if(!$log->have_entries()){
						$balance = mycred_get_users_balance(get_current_user_id());
						if($board->meta->attachment_download_down_point > $balance){
							do_action('kboard_cannot_download_file', 'not_enough_points', wp_get_referer(), $content, $board, $comment);
							exit;
						}
						else{
							$point = intval(get_user_meta(get_current_user_id(), 'kboard_document_mycred_point', true));
							update_user_meta(get_current_user_id(), 'kboard_document_mycred_point', $point + ($board->meta->attachment_download_down_point*-1));
							
							mycred_add('attachment_download_down_point', get_current_user_id(), ($board->meta->attachment_download_down_point*-1), __('Attachment download decrease points', 'kboard'), $content->uid);
						}
					}
				}
			}
		}
		
		// download_count 증가
		$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_attached` SET `download_count`=`download_count`+1 WHERE `uid`='{$file_info->uid}'");
		
		if(get_option('kboard_attached_copy_download')){
			$unique_dir = uniqid();
			$upload_dir = wp_upload_dir();
			$temp_path = $upload_dir['basedir'] . "{$ds}kboard_temp";
			
			$file_handler = new KBFileHandler();
			$file_handler->deleteWithOvertime($temp_path, 60);
			$file_handler->mkPath("{$temp_path}{$ds}{$unique_dir}");
			
			copy($file_info->full_path, "{$temp_path}{$ds}{$unique_dir}{$ds}{$file_info->file_name}");
			header('Location: ' . $upload_dir['baseurl'] . "{$ds}kboard_temp{$ds}{$unique_dir}{$ds}{$file_info->file_name}");
		}
		else{
			$ie = isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false);
			if($ie){
				$file_info->file_name = iconv('UTF-8', 'EUC-KR//IGNORE', $file_info->file_name);
				
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
			}
			else{
				header('Pragma: no-cache');
			}
			
			header('Content-type: ' . $file_info->mime_type);
			header('Content-Disposition: attachment; filename="' . $file_info->file_name . '"');
			header('Content-Transfer-Encoding: binary');
			header('Content-length: ' . $file_info->size);
			header('Expires: 0');
			
			@ob_clean();
			@flush();
			
			readfile($file_info->full_path);
		}
		exit;
	}
	
	/**
	 * 아임포트 결제후 데이터 검증 및 저장
	 */
	public function iamportEndpoint(){
		kboard_switch_to_blog();
		
		$display = isset($_REQUEST['display'])?$_REQUEST['display']:'pc';
		$imp_uid = isset($_REQUEST['imp_uid'])?$_REQUEST['imp_uid']:'';
		$imp_success = isset($_REQUEST['imp_success'])?sanitize_text_field($_REQUEST['imp_success']):'';
		$error_msg = isset($_REQUEST['error_msg'])?sanitize_text_field($_REQUEST['error_msg']):'';
		
		if($imp_uid){
			header('Content-Type: text/html; charset=UTF-8');
			
			$iamport = kboard_iamport();
			
			if(!$iamport->imp_key || !$iamport->imp_secret){
				if($display == 'mobile'){
					die('<script>alert("iamport error");window.location.href="'.home_url().'";</script>');
				}
				else{
					wp_send_json(array('result'=>'error', 'message'=>'iamport error'));
				}
			}
			
			$payment = $iamport->payments($imp_uid);
			parse_str($payment->data->custom_data, $_POST);
			
			$_POST['kboard_order']['imp_uid'] = $payment->data->imp_uid;
			$_POST['kboard_order']['merchant_uid'] = $payment->data->merchant_uid;
			$_POST['kboard_order']['receipt_url'] = $payment->data->receipt_url;
			
			$next_page_url = isset($_POST['next_page_url']) ? esc_url_raw($_POST['next_page_url']) : home_url();
			
			if(!isset($_GET['kboard-iamport-endpoint-nonce']) || !wp_verify_nonce($_GET['kboard-iamport-endpoint-nonce'], "kboard-iamport-endpoint-{$payment->data->merchant_uid}")){
				if($display == 'mobile'){
					die('<script>alert("'.__('You do not have permission.', 'kboard').'");window.location.href="'.$next_page_url.'";</script>');
				}
				else{
					wp_send_json(array('result'=>'error', 'message'=>__('You do not have permission.', 'kboard')));
				}
			}
			if(!$payment->success){
				if($display == 'mobile'){
					die('<script>alert("'.$payment->message.'");window.location.href="'.$next_page_url.'";</script>');
				}
				else{
					wp_send_json(array('result'=>'error', 'message'=>$payment->message));
				}
			}
			if($imp_success == 'false'){
				if($error_msg == 'User cancelled payment process.'){
					$error_msg = __('Payment has been cancelled.', 'kboard');
				}
				
				$board_id = (isset($_POST['board_id'])&&$_POST['board_id']) ? intval($_POST['board_id']) : 0;
				if(isset($_POST['kboard_order_item'][$board_id])){
					$item = reset($_POST['kboard_order_item'][$board_id]);
					
					$url = new KBUrl($next_page_url);
					$url->clear()->set('uid', $item['uid'])->set('mod', 'document');
					$next_page_url = $url->toString();
				}
				
				$error_msg = apply_filters('kboard_iamport_endpoint_error_msg', $error_msg, $payment);
				
				if($display == 'mobile'){
					die('<script>alert("'.esc_js($error_msg).'");window.location.href="'.$next_page_url.'";</script>');
				}
				else{
					wp_send_json(array('result'=>'error', 'message'=>$error_msg));
				}
			}
			if($payment->data->status != 'paid'){
				if($display == 'mobile'){
					die('<script>alert("'.$payment->data->fail_reason.'");window.location.href="'.$next_page_url.'";</script>');
				}
				else{
					wp_send_json(array('result'=>'error', 'message'=>$payment->data->fail_reason));
				}
			}
			
			// 동일한 결제건이 이미 저장되어 있는지 확인
			$orders = get_posts(array(
				'post_type' => 'kboard_order',
				'meta_query' => array(array('key'=>'imp_uid', 'value'=>$payment->data->imp_uid))
			));
			if($orders){
				if($display == 'mobile'){
					die('<script>alert("iamport error");window.location.href="'.$next_page_url.'";</script>');
				}
				else{
					wp_send_json(array('result'=>'error', 'message'=>'iamport error'));
				}
			}
			
			$board_id = isset($_POST['board_id'])?intval($_POST['board_id']):'';
			$board = new KBoard($board_id);
			if(!$board->id){
				if($display == 'mobile'){
					die('<script>alert("'.__('You do not have permission.', 'kboard').'");window.location.href="'.$next_page_url.'";</script>');
				}
				else{
					wp_send_json(array('result'=>'error', 'message'=>__('You do not have permission.', 'kboard')));
				}
			}
			
			/* 결제 데이터 저장 시작 */
			$order = new KBOrder();
			$order->board = $board;
			$order->board_id = $board->id;
			$order->initOrder();
			$order->initOrderItems();
			
			// 결제된 가격이 정확한지 체크
			if($order->getAmount() != $payment->data->amount){
				if($display == 'mobile'){
					die('<script>alert("'.__('You do not have permission.', 'kboard').'");window.location.href="'.$next_page_url.'";</script>');
				}
				else{
					wp_send_json(array('result'=>'error', 'message'=>__('You do not have permission.', 'kboard')));
				}
			}
			
			// 포인트 결제 적용
			if($board->isUsePointOrder() && is_user_logged_in() && $order->use_points){
				$balance = mycred_get_users_balance(get_current_user_id());
				if($balance >= $order->use_points){
					mycred_add('kboard_order', get_current_user_id(), ($order->use_points*-1), __('Point payment', 'kboard'));
				}
				else{
					if($display == 'mobile'){
						die('<script>alert("'.__('Your point is not enough.', 'kboard').'");window.location.href="'.$next_page_url.'";</script>');
					}
					else{
						wp_send_json(array('result'=>'error', 'message'=>__('Your point is not enough.', 'kboard')));
					}
				}
			}
			
			$order->create();
			$order->createItems(array(
				'order_status' => 'paid'
			));
			
			foreach($order->items as $item){
				$item->addUserRewardPoint();
			}
			
			do_action('kboard_order_execute', $order, $board);
			do_action("kboard_{$board->skin}_order_execute", $order, $board);
			/* 결제 데이터 저장 끝 */
			
			$url = new KBUrl();
			$next_page_url = $url->clear()->set('order_id', $order->order_id)->toStringWithPath($next_page_url);
			$next_page_url = apply_filters('kboard_after_order_url', $next_page_url, $order->order_id, $board_id);
			
			if($display == 'mobile'){
				wp_redirect($next_page_url);
			}
			else{
				wp_send_json(array('result'=>'success', 'next_page_url'=>$next_page_url));
			}
		}
		exit;
	}
	
	/**
	 * 아임포트 Notification 실행
	 */
	public function iamportNotification(){
		kboard_switch_to_blog();
		
		$iamport = kboard_iamport();
		
		if(!$iamport->imp_id || !$iamport->imp_key || !$iamport->imp_secret){
			exit;
		}
		
		$security = hash('sha512', $iamport->imp_id . $iamport->imp_key . $iamport->imp_secret);
		$security = hash('sha256', $security);
		$security = hash('md5', $security);
		
		if(!isset($_GET['security']) || $_GET['security'] != $security){
			exit;
		}
		
		$data = file_get_contents('php://input');
		$data = json_decode($data);
		
		$imp_uid = isset($data->imp_uid)?$data->imp_uid:'';
		$merchant_uid = isset($data->merchant_uid)?$data->merchant_uid:'';
		$status = isset($data->status)?$data->status:'';
		
		if($imp_uid && $merchant_uid && $status == 'paid'){
			header('Content-Type: text/html; charset=UTF-8');
			
			$payment = $iamport->payments($imp_uid);
			parse_str($payment->data->custom_data, $_POST);
			
			if(!$payment->success){
				exit;
			}
			if($payment->data->status != 'paid'){
				exit;
			}
			
			// 동일한 결제건이 이미 저장되어 있는지 확인
			$orders = get_posts(array(
				'post_type' => 'kboard_order',
				'meta_query' => array(array('key'=>'imp_uid', 'value'=>$payment->data->imp_uid))
			));
			if($orders){
				exit;
			}
			
			$board_id = isset($_POST['board_id'])?intval($_POST['board_id']):'';
			$board = new KBoard($board_id);
			if(!$board->id){
				exit;
			}
			
			/* 결제 데이터 저장 시작 */
			$order = new KBOrder();
			$order->board = $board;
			$order->board_id = $board->id;
			$order->initWithMerchantUID($merchant_uid);
			$order->initOrderItems();
			
			// 결제된 가격이 정확한지 체크
			if($order->getAmount() != $payment->data->amount){
				exit;
			}
			
			// 포인트 결제 적용
			if($board->isUsePointOrder() && $order->user_id && $order->use_points){
				$balance = mycred_get_users_balance($order->user_id);
				if($balance >= $order->use_points){
					mycred_add('kboard_order', $order->user_id, ($order->use_points*-1), __('Point payment', 'kboard'));
				}
				else{
					exit;
				}
			}
			
			$order->update(array(
				'imp_uid' => $payment->data->imp_uid,
				'receipt_url' => $payment->data->receipt_url,
			));
			
			foreach($order->items as $item){
				$item->update(array(
					'order_status' => 'paid'
				));
				
				$item->addUserRewardPoint();
			}
			
			do_action('kboard_order_execute', $order, $board);
			do_action("kboard_{$board->skin}_order_execute", $order, $board);
			/* 결제 데이터 저장 끝 */
		}
		exit;
	}
	
	/**
	 * 무통장입금, 무료 상품 정보 저장
	 */
	public function orderExecute(){
		if(isset($_POST['kboard-order-execute-nonce']) && wp_verify_nonce($_POST['kboard-order-execute-nonce'], 'kboard-order-execute')){
			kboard_switch_to_blog();
			
			header('Content-Type: text/html; charset=UTF-8');
			
			$_POST = stripslashes_deep($_POST);
			
			$board_id = isset($_POST['board_id'])?intval($_POST['board_id']):'';
			$board = new KBoard($board_id);
			if(!$board->id){
				die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
			}
			
			/* 결제 데이터 저장 시작 */
			$order = new KBOrder();
			$order->board = $board;
			$order->board_id = $board->id;
			$order->initOrder();
			$order->initOrderItems();
			
			if($order->getAmount() > 0){
				$items_data = array('order_status' => 'pay_waiting');
				
				// 가상계좌 정보 저장
				if($order->payment_method == 'vbank'){
					$iamport = kboard_iamport();
					
					if($iamport->imp_key && $iamport->imp_secret){
						$imp_uid = isset($_REQUEST['imp_uid'])?$_REQUEST['imp_uid']:'';
						$payment = $iamport->payments($imp_uid);
						// 아임포트에서 보내주는 timestamp는 한국시간 기준으로 생성됐기 때문에 timezone을 변경해준다.
						date_default_timezone_set('Asia/Seoul');
						
						$order->vbank_date = date('Y-m-d H:i:s', $payment->data->vbank_date);
						$order->vbank_holder = $payment->data->vbank_holder;
						$order->vbank_name = $payment->data->vbank_name;
						$order->vbank_num = $payment->data->vbank_num;
						
						// WordPress calculates offsets from UTC.
						date_default_timezone_set('UTC');
					}
					else{
						die('<script>alert("iamport error");history.go(-1);</script>');
					}
				}
			}
			else{
				$items_data = array('order_status' => 'paid');
				
				// 포인트 결제 적용
				if($board->isUsePointOrder() && is_user_logged_in() && $order->use_points){
					$balance = mycred_get_users_balance(get_current_user_id());
					if($balance >= $order->use_points){
						mycred_add('kboard_order', get_current_user_id(), ($order->use_points*-1), __('Point payment', 'kboard'));
					}
					else{
						die('<script>alert("'.__('Your point is not enough.', 'kboard').'");history.go(-1);</script>');
					}
				}
			}
			
			$order->create();
			$order->createItems($items_data);
			
			if($order->getAmount() <= 0){
				foreach($order->items as $item){
					$item->addUserRewardPoint();
				}
			}
			
			do_action('kboard_order_execute', $order, $board);
			do_action("kboard_{$board->skin}_order_execute", $order, $board);
			/* 결제 데이터 저장 끝 */
			
			$url = new KBUrl();
			$next_page_url = $url->set('pageid', '1')->set('order_id', $order->order_id)->set('mod', 'history')->toString();
			$next_page_url = apply_filters('kboard_after_order_url', $next_page_url, $order->order_id, $board_id);
			
			wp_redirect($next_page_url);
		}
		else{
			wp_redirect(home_url());
		}
		exit;
	}
	
	/**
	 * 무통장입금확인, 결제취소 등 결제정보 변경
	 */
	public function orderItemUpdate(){
		check_ajax_referer('kboard_ajax_security', 'security');
		
		$result = array('result'=>'error', 'message'=>__('You do not have permission.', 'kboard'));
		$board_id = isset($_POST['board_id'])?intval($_POST['board_id']):'';
		$board = new KBoard($board_id);
		
		if($board->id){
			$order_item_id = isset($_POST['order_item_id'])?intval($_POST['order_item_id']):'';
			$item = new KBOrderItem();
			$item->board = $board;
			$item->board_id = $board->id;
			$item->initWithID($order_item_id);
			
			if($item->order_item_id && $item->content->isEditor()){
				$order_status = isset($_POST['order_status'])?sanitize_text_field($_POST['order_status']):'';
				
				if($order_status == 'paid' && $item->order_status != 'paid'){
					$is_success = true;
					
					// 포인트 결제 적용
					if($board->isUsePointOrder() && $item->order->user_id && $item->order->use_points){
						$balance = mycred_get_users_balance($item->order->user_id);
						if($balance >= $item->order->use_points){
							mycred_add('kboard_order', $item->order->user_id, ($item->order->use_points*-1), __('Point payment', 'kboard'));
						}
						else{
							$is_success = false;
							$result = array('result'=>'error', 'message'=>__('Not enough points.', 'kboard'));
						}
					}
					
					if($is_success){
						$item->update(array(
							'order_status' => $order_status,
							'datetime' => date('YmdHis', current_time('timestamp'))
						));
						
						$item->addUserRewardPoint();
						
						$result = array('result'=>'success', 'message'=>__('Order information has been changed.', 'kboard'));
					}
				}
				else if($order_status == 'cancel' && $item->order_status != 'cancel'){
					if($item->order->getAmount() <= 0){
						// 포인트 결제 취소
						if($board->isUsePointOrder() && $item->order->user_id && $item->order->use_points){
							mycred_add('kboard_order', $item->order->user_id, $item->order->use_points, __('Cancel point payment', 'kboard'));
						}
						
						$item->update(array(
							'order_status' => $order_status,
							'datetime' => date('YmdHis', current_time('timestamp'))
						));
						
						$item->cancelUserRewardPoint();
						
						$result = array('result'=>'success', 'message'=>__('Your order has been cancelled.', 'kboard'));
					}
					else if($item->order->imp_uid){
						$iamport = kboard_iamport();
						
						if(!$iamport->imp_key || !$iamport->imp_secret){
							$result = array('result'=>'error', 'message'=>'iamport error');
						}
						else{
							if($item->order->payment_method == 'vbank'){
								$payment = $iamport->cancel($item->order->imp_uid, array(
									'refund_bank' => apply_filters('kboard_order_vbank_refund_bank_code', $item->order->refund_bank, $item, $board),
									'refund_account' => $item->order->refund_account,
									'refund_holder' => $item->order->refund_holder,
								));
							}
							else{
								$payment = $iamport->cancel($item->order->imp_uid);
							}
							
							if(!$payment->success){
								$result = array('result'=>'error', 'message'=>$payment->message);
							}
							else if($payment->data->status == 'cancelled'){
								// 포인트 결제 취소
								if($board->isUsePointOrder() && $item->order->user_id && $item->order->use_points){
									mycred_add('kboard_order', $item->order->user_id, $item->order->use_points, __('Cancel point payment', 'kboard'));
								}
								
								$item->update(array(
									'order_status' => $order_status,
									'datetime' => date('YmdHis', current_time('timestamp'))
								));
								
								$item->cancelUserRewardPoint();
								
								$result = array('result'=>'success', 'message'=>__('Your order has been cancelled.', 'kboard'));
							}
							else{
								$result = array('result'=>'error', 'message'=>'iamport error');
							}
						}
					}
					else if($item->order->payment_method == 'cash'){
						// 포인트 결제 취소
						if($board->isUsePointOrder() && $item->order->user_id && $item->order->use_points){
							mycred_add('kboard_order', $item->order->user_id, $item->order->use_points, __('Cancel point payment', 'kboard'));
						}
						
						$item->update(array(
							'order_status' => $order_status,
							'datetime' => date('YmdHis', current_time('timestamp'))
						));
						
						$item->cancelUserRewardPoint();
						
						$result = array('result'=>'success', 'message'=>__('Your order has been cancelled.', 'kboard'));
					}
				}
				
				$result = apply_filters('kboard_order_item_update_action', $result, $item, $board);
				$result = apply_filters("kboard_{$board->skin}_order_item_update_action", $result, $item, $board);
			}
		}
		
		wp_send_json($result);
	}
	
	/**
	 * 게시글 좋아요
	 */
	public function documentLike(){
		check_ajax_referer('kboard_ajax_security', 'security');
		if(isset($_POST['document_uid']) && intval($_POST['document_uid'])){
			$content = new KBContent();
			$content->initWithUID($_POST['document_uid']);
			if($content->uid){
				$board = $content->getBoard();
				if($board->isVote()){
					$args['target_uid'] = $content->uid;
					$args['target_type'] = KBVote::$TYPE_DOCUMENT;
					$args['target_vote'] = KBVote::$VOTE_LIKE;
					$vote = new KBVote();
					if($vote->isExists($args) === 0){
						if($vote->insert($args)){
							$content->like += 1;
							$content->vote = $content->like - $content->unlike;
							$content->updateContent();
							
							do_action('kboard_content_like', $content, $board);
							
							wp_send_json(array('result'=>'success', 'data'=>array('vote'=>intval($content->vote), 'like'=>intval($content->vote), 'unlike'=>intval($content->unlike))));
						}
					}
					else{
						wp_send_json(array('result'=>'error', 'message'=>__('You have already voted.', 'kboard')));
					}
				}
				else if(!is_user_logged_in()){
					wp_send_json(array('result'=>'error', 'message'=>__('Please Log in to continue.', 'kboard')));
				}
			}
		}
		wp_send_json(array('result'=>'error', 'message'=>__('You do not have permission.', 'kboard')));
	}
	
	/**
	 * 게시글 싫어요
	 */
	function documentUnlike(){
		check_ajax_referer('kboard_ajax_security', 'security');
		if(isset($_POST['document_uid']) && intval($_POST['document_uid'])){
			$content = new KBContent();
			$content->initWithUID($_POST['document_uid']);
			if($content->uid){
				$board = $content->getBoard();
				if($board->isVote()){
					$args['target_uid'] = $content->uid;
					$args['target_type'] = KBVote::$TYPE_DOCUMENT;
					$args['target_vote'] = KBVote::$VOTE_UNLIKE;
					$vote = new KBVote();
					if($vote->isExists($args) === 0){
						if($vote->insert($args)){
							$content->unlike += 1;
							$content->vote = $content->like - $content->unlike;
							$content->updateContent();
							
							do_action('kboard_content_unlike', $content, $board);
							
							wp_send_json(array('result'=>'success', 'data'=>array('vote'=>intval($content->vote), 'like'=>intval($content->vote), 'unlike'=>intval($content->unlike))));
						}
					}
					else{
						wp_send_json(array('result'=>'error', 'message'=>__('You have already voted.', 'kboard')));
					}
				}
				else if(!is_user_logged_in()){
					wp_send_json(array('result'=>'error', 'message'=>__('Please Log in to continue.', 'kboard')));
				}
			}
		}
		wp_send_json(array('result'=>'error', 'message'=>__('You do not have permission.', 'kboard')));
	}
	
	/**
	 * 게시글 정보 업데이트
	 */
	public function contentUpdate(){
		check_ajax_referer('kboard_ajax_security', 'security');
		if(isset($_POST['content_uid']) && intval($_POST['content_uid'])){
			$content = new KBContent();
			$content->initWithUID($_POST['content_uid']);
			if($content->isEditor() || $content->isConfirm()){
				$content->updateContent($_POST['data']);
				$content->updateOptions($_POST['data']);
				
				// 게시글 수정 액션 훅 실행
				$content->initWithUID($_POST['content_uid']);
				do_action('kboard_document_update', $content->uid, $content->board_id, $content, $content->getBoard());
				
				wp_send_json(array('result'=>'success', 'data'=>$_POST['data']));
			}
		}
		wp_send_json(array('result'=>'error', 'message'=>__('You do not have permission.', 'kboard')));
	}
}
?>
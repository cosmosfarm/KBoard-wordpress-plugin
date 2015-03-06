<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';

$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';

header("Content-Type: text/html; charset=UTF-8");
if(!stristr($referer, $host)) wp_die('KBoard : '.__('This page is restricted from external access.', 'kboard'));

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
				if(!$board->isConfirm($parent->password, $parent->uid)){
					die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
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
?>
<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';

header("Content-Type: text/html; charset=UTF-8");
if(!stristr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) wp_die('KBoard : '.__('This page is restricted from external access.', 'kboard'));

$uid = intval($_GET['uid']);
$file = addslashes(kboard_xssfilter(kboard_htmlclear(trim($_GET['file']))));

if(!$uid || !$file){
	die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
}

$content = new KBContent();
$content->initWithUID($uid);
$board = new KBoard($content->board_id);

if(!$board->isReader($content->member_uid, $content->secret)){
	if(!$user_ID) die('<script>alert("'.__('Please Log in to continue.', 'kboard').'");location.href="'.wp_login_url().'";</script>');
	else die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
}

$file_info = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_uid`='$uid' AND `file_key`='$file'");

list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
$path = $path.str_replace('/', DIRECTORY_SEPARATOR, $file_info->file_path);
$filename = str_replace(' ' ,'-', $file_info->file_name);

if(!$file_info->file_path || !file_exists($path)){
	die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
}

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
?>
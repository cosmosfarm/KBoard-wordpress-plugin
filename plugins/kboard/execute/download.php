<?php
$path = explode(DIRECTORY_SEPARATOR . 'wp-content', dirname(__FILE__) . DIRECTORY_SEPARATOR);
include reset($path) . DIRECTORY_SEPARATOR . 'wp-load.php';

header("Content-Type: text/html; charset=UTF-8");
if(!stristr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) wp_die('KBoard : 이 페이지는 외부에서의 접근을 제한하고 있습니다.');

$uid = intval($_GET['uid']);
$file = kboard_htmlclear($_GET['file']);

if(!$uid || !$file){
	die('<script>alert("권한이 없습니다.");history.go(-1);</script>');
}

$content = new Content();
$content->initWithUID($uid);
$board = new KBoard($content->board_id);
if(!$board->isReader($content->member_uid, $content->secret)){
	if(!$user_ID) die('<script>alert("로그인 하셔야 사용할 수 있습니다.");location.href="'.site_url('/wp-login.php').'";</script>');
	else die('<script>alert("권한이 없습니다.");history.go(-1);</script>');
}

$result = kboard_query("SELECT * FROM ".KBOARD_DB_PREFIX."kboard_board_attached WHERE content_uid=$uid AND file_key LIKE '$file'");
$file_info = mysql_fetch_array($result);

$path = reset($path) . str_replace('/', DIRECTORY_SEPARATOR, $file_info['file_path']);
$name = $file_info['file_name'];

if(eregi("(MSIE 5.0|MSIE 5.1|MSIE 5.5|MSIE 6.0)", $_SERVER["HTTP_USER_AGENT"]) && !eregi("(Opera|Netscape)", $_SERVER["HTTP_USER_AGENT"])){
	Header("Content-type: application/octet-stream");
	Header("Content-Length: ".filesize($path));
	Header("Content-Disposition: attachment; filename=" . iconv('UTF-8','CP949',$name));
	Header("Content-Transfer-Encoding: binary");
	Header("Pragma: no-cache");
	Header("Expires: 0");
}
else{
	Header("Content-type: file/unknown");
	Header("Content-Length: ".filesize($path));
	Header("Content-Disposition: attachment; filename=" . iconv('UTF-8','CP949',$name));
	Header("Pragma: no-cache");
	Header("Expires: 0");
}
$fp = fopen($path, "rb");
if(!fpassthru($fp)) fclose($fp);
?>
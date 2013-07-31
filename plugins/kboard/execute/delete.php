<?php
$path = explode(DIRECTORY_SEPARATOR . 'wp-content', dirname(__FILE__) . DIRECTORY_SEPARATOR);
include reset($path) . DIRECTORY_SEPARATOR . 'wp-load.php';

header("Content-Type: text/html; charset=UTF-8");
if(!stristr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) die("<script>alert('외부접근불가');</script>");

$uid = intval($_GET['uid']);
$file = kboard_htmlclear($_GET['file']);

if(!$uid || !$file){
	die('<script>alert("권한이 없습니다.");history.go(-1);</script>');
}

if(!strstr($_SERVER['HTTP_REFERER'], basename(__file__))) $_SESSION['redirect_uri'] = $_SERVER['HTTP_REFERER'];

$content = new Content();
$content->initWithUID($uid);
$board = new KBoard($content->board_id);
if(!$board->isEditor($content->member_uid)){
	if($board->permission_write=='all'){
		if(!$board->isConfirm($content->password, $content->uid)){
			$url = new Url();
			$skin_path = KBOARD_URL_PATH . "/skin/$board->skin";
			include_once KBOARD_DIR_PATH . "/skin/$board->skin/confirm.php";
			exit;
		}
		else{
			
		}
	}
	elseif(!$user_ID) die('<script>alert("로그인 하셔야 사용할 수 있습니다."); location.href="' . site_url('/wp-login.php') . '";</script>');
	else die('<script>alert("권한이 없습니다."); history.go(-1);</script>');
}
if($file == 'thumbnail') $content->removeThumbnail();
else $content->removeAttached($file);
header("Location:" . $_SESSION['redirect_uri']);
?>
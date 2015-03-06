<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';

$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';

header("Content-Type: text/html; charset=UTF-8");
if(!stristr($referer, $host)) wp_die('KBoard : '.__('This page is restricted from external access.', 'kboard'));

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

header("Location:".$_SESSION['redirect_uri']);
?>
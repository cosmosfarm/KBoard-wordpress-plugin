<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';

header("Content-Type: text/html; charset=UTF-8");
if(!stristr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) wp_die('KBoard : 이 페이지는 외부에서의 접근을 제한하고 있습니다.');

$uid = intval($_GET['uid']);
$file = addslashes(kboard_xssfilter(kboard_htmlclear(trim($_GET['file']))));

if(!$uid || !$file){
	die('<script>alert("권한이 없습니다.");history.go(-1);</script>');
}

if(!strstr($_SERVER['HTTP_REFERER'], basename(__file__))) $_SESSION['redirect_uri'] = $_SERVER['HTTP_REFERER'];

$content = new KBContent();
$content->initWithUID($uid);
$board = new KBoard($content->board_id);

if(!$board->isEditor($content->member_uid)){
	if($board->permission_write=='all'){
		if(!$board->isConfirm($content->password, $content->uid)){
			$url = new KBUrl();
			$skin_path = KBOARD_URL_PATH . "/skin/$board->skin";
			include KBOARD_DIR_PATH . "/skin/$board->skin/confirm.php";
			exit;
		}
	}
	elseif(!$user_ID){
		die('<script>alert("로그인 하셔야 사용할 수 있습니다.");location.href="'.wp_login_url().'";</script>');
	}
	else{
		die('<script>alert("권한이 없습니다.");history.go(-1);</script>');
	}
}

if($file == 'thumbnail') $content->removeThumbnail();
else $content->removeAttached($file);

header("Location:" . $_SESSION['redirect_uri']);
?>
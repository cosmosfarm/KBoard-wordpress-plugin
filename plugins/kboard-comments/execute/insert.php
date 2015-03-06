<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';
include KBOARD_DIR_PATH.'/class/KBCaptcha.class.php';

$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';

header("Content-Type: text/html; charset=UTF-8");
if(!stristr($referer, $host)) wp_die('KBoard : '.__('This page is restricted from external access.', 'kboard-comments'));

$content = isset($_POST['content'])?$_POST['content']:'';
$comment_content = isset($_POST['comment_content'])?$_POST['comment_content']:'';
$member_display = isset($_POST['member_display'])?$_POST['member_display']:'';
$password = isset($_POST['password'])?$_POST['password']:'';
$captcha_text = isset($_POST['captcha'])?$_POST['captcha']:'';

$captcha = new KBCaptcha();
$content = $content?$content:$comment_content;
$content_uid = isset($_POST['content_uid'])?intval($_POST['content_uid']):'';
$parent_uid = isset($_POST['parent_uid'])?intval($_POST['parent_uid']):'';
$member_uid = isset($_POST['member_uid'])?intval($_POST['member_uid']):'';

if(!is_user_logged_in() && !$member_display){
	die("<script>alert('".__('Please enter a author.', 'kboard-comments')."');history.go(-1);</script>");
}
else if(!is_user_logged_in() && !$password){
	die("<script>alert('".__('Please enter a password.', 'kboard-comments')."');history.go(-1);</script>");
}
else if(!$captcha->textCheck($captcha_text)){
	die("<script>alert('".__('The CAPTCHA code is not valid. Please enter the CAPTCHA code.', 'kboard-comments')."');history.go(-1);</script>");
}
else if(!$content_uid){
	die("<script>alert('".__('No document UID.', 'kboard-comments')."');history.go(-1);</script>");
}
else if(!$content){
	die("<script>alert('".__('Type the content of the comment.', 'kboard-comments')."');history.go(-1);</script>");
}

$document = new KBContent();
$document->initWithUID($content_uid);
$setting = new KBoardMeta($document->board_id);

if(!is_user_logged_in() && $setting->permission_comment_write=='1'){
	die('<script>alert("'.__('You do not have permission.', 'kboard-comments').'");history.go(-1);</script>');
}

$commentList = new KBCommentList($content_uid);
$commentList->add($parent_uid, $member_uid, $member_display, $content, $password);

// 댓글 입력 완료 후 이전 페이지로 이동
header("Location:".$referer);
?>
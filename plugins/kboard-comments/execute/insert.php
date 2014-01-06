<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';
include KBOARD_DIR_PATH.'/class/KBCaptcha.class.php';

header("Content-Type: text/html; charset=UTF-8");
if(!stristr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) wp_die('KBoard : '.__('This page is restricted from external access.', 'kboard-comments'));

$userdata = get_userdata($user_ID);
$captcha = new KBCaptcha();

if(!$userdata->id && !$_POST['member_display']){
	die("<script>alert('".__('Please enter a author.', 'kboard-comments')."');history.go(-1);</script>");
}
else if(!$userdata->id && !$_POST['password']){
	die("<script>alert('".__('Please enter a password.', 'kboard-comments')."');history.go(-1);</script>");
}
else if(!$captcha->textCheck($_POST['captcha'])){
	die("<script>alert('".__('The CAPTCHA code is not valid. Please enter the CAPTCHA code.', 'kboard-comments')."');history.go(-1);</script>");
}
else if(!$_POST['content_uid'] && !$_POST['parent_uid']){
	die("<script>alert('".__('No document UID.', 'kboard-comments')."');history.go(-1);</script>");
}
else if(!$_POST['content']){
	die("<script>alert('".__('Type the content of the comment.', 'kboard-comments')."');history.go(-1);</script>");
}

$commentList = new KBCommentList($_POST['content_uid']);
$commentList->add($_POST['parent_uid'], $_POST['member_uid'], $_POST['member_display'], $_POST['content'], $_POST['password']);

// 댓글 입력 완료 후 이전 페이지로 이동
header("Location:".$_SERVER['HTTP_REFERER']);
?>
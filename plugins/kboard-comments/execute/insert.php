<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';
include KBOARD_DIR_PATH.'/class/KBCaptcha.class.php';

header("Content-Type: text/html; charset=UTF-8");
if(!stristr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) wp_die('KBoard : 이 페이지는 외부에서의 접근을 제한하고 있습니다.');

$userdata = get_userdata($user_ID);
$captcha = new KBCaptcha();

if(!$userdata->id && !$_POST['member_display']){
	die("<script>alert('작성자명을 입력하세요.');history.go(-1);</script>");
}
else if(!$userdata->id && !$_POST['password']){
	die("<script>alert('비밀번호를 입력하세요.');history.go(-1);</script>");
}
else if(!$captcha->textCheck($_POST['captcha'])){
	die("<script>alert('보안코드가 올바르지 않습니다. 보안 코드를 입력하세요.');history.go(-1);</script>");
}
else if(!$_POST['content_uid'] && !$_POST['parent_uid']){
	die("<script>alert('게시물 고유번호가 없습니다.');history.go(-1);</script>");
}
else if(!$_POST['content']){
	die("<script>alert('댓글 내용을 입력하세요.');history.go(-1);</script>");
}

$commentList = new KBCommentList($_POST['content_uid']);
$commentList->add($_POST['parent_uid'], $_POST['member_uid'], $_POST['member_display'], $_POST['content'], $_POST['password']);
header("Location:".$_SERVER['HTTP_REFERER']);
?>
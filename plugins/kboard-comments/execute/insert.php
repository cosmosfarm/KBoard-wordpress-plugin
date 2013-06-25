<?php
include reset(explode('/wp-content', dirname(__FILE__) . '/')) . '/wp-load.php';

header("Content-Type: text/html; charset=UTF-8");
if(!stristr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) die("<script>alert('외부접근불가');</script>");

$userdata = get_userdata($user_ID);

if(!$userdata->id && !$_POST['member_display']){
	die("<script>alert('작성자명을 입력하세요.'); history.go(-1);</script>");
}
else if(!$userdata->id && !$_POST['password']){
	die("<script>alert('비밀번호를 입력하세요.'); history.go(-1);</script>");
}
else if(!$_POST['content_uid']){
	die("<script>alert('게시물 고유번호가 없습니다.'); history.go(-1);</script>");
}
else if(!$_POST['content']){
	die("<script>alert('댓글 내용을 입력하세요.'); history.go(-1);</script>");
}

$commentList = new CommentList($_POST['content_uid']);
$commentList->add($_POST['member_uid'], $_POST['member_display'], $_POST['content'], $_POST['password']);
header("Location:" . $_SERVER['HTTP_REFERER']);
?>
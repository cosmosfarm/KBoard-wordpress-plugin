<?php
include reset(explode('/wp-content', dirname(__FILE__) . '/')) . '/wp-load.php';

header("Content-Type: text/html; charset=UTF-8");
if(!stristr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) die("<script>alert('외부접근불가');</script>");

$userdata = get_userdata($user_ID);

if(!$_GET['uid']){
	die("<script>alert('댓글 고유번호가 없습니다.'); history.go(-1);</script>");
}
else if(!$userdata->id && !$_POST['password']){
	die("<script>alert('로그인해야 합니다.'); history.go(-1);</script>");
}

$commentList = new CommentList();
$comment = $commentList->getComment($_GET['uid']);
if(!$comment->isEditor() && $comment->password != $_POST['password']){
	die("<script>alert('권한이 없습니다.'); history.go(-1);</script>");
}
$commentList->delete($_GET['uid']);
if($comment->password && $comment->password == $_POST['password']){
	echo '<script>';
	echo 'opener.window.location.reload();';
	echo 'window.close();';
	echo '</script>';
}
else{
	header("Location:" . $_SERVER['HTTP_REFERER']);
}
?>
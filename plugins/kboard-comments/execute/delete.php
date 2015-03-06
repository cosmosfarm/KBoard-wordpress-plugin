<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';

$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';

header("Content-Type: text/html; charset=UTF-8");
if(!stristr($referer, $host)) wp_die('KBoard : '.__('This page is restricted from external access.', 'kboard-comments'));

$userdata = get_userdata($user_ID);
$uid = isset($_GET['uid'])?intval($_GET['uid']):'';
$password = isset($_POST['password'])?$_POST['password']:'';

if(!$uid){
	die("<script>alert('".__('No UID of comments.', 'kboard-comments')."');history.go(-1);</script>");
}
else if((!isset($userdata->ID) || !$userdata->ID) && !$password){
	die("<script>alert('".__('Please Log in to continue.', 'kboard-comments')."');history.go(-1);</script>");
}

$commentList = new KBCommentList();
$comment = $commentList->getComment($uid);
if(!$comment->isEditor() && $comment->password != $password){
	die("<script>alert('".__('You do not have permission.', 'kboard-comments')."');history.go(-1);</script>");
}
$commentList->setContentUID($comment->content_uid);
$commentList->delete($uid);

if($comment->password && $comment->password == $password){
	// 팝업창으로 비밀번호 확인 후 opener 윈도우를 새로고침 한다.
	echo '<script>';
	echo 'opener.window.location.reload();';
	echo 'window.close();';
	echo '</script>';
}
else{
	// 삭제권한이 있는 사용자일 경우 팝업창은 없기 때문에 페이지 이동한다.
	header("Location:".$referer);
}
?>
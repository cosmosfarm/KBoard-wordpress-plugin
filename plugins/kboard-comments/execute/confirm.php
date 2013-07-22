<?php
include reset(explode(DIRECTORY_SEPARATOR . 'wp-content', dirname(__FILE__) . DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR . 'wp-load.php';

header("Content-Type: text/html; charset=UTF-8");
if(!stristr($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'])) die("<script>alert('외부접근불가');</script>");

$uid = intval($_GET['uid']);

if(!$uid){
	die("<script>alert('댓글 고유번호가 없습니다.');window.close();</script>");
}

$commentList = new CommentList();
$comment = $commentList->getComment($uid);

if(!$comment->uid){
	die("<script>alert('존재하지 않는 댓글 입니다.');window.close();</script>");
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<meta name="author" content="http://www.cosmosfarm.com/">
	<title>KBoard - 비밀번호 확인</title>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	<style>
		body { background-color: #f9f9f9; }
		label { font-size: 12px; }
		input { margin: 0; }
	</style>
	<script>
		function password_checker(form){
			if(!$('input[name=password]').val()){
				alert('비밀번호를 입력하세요.');
				$('input[name=password]').focus();
				return false;
			}
			return true;
		}
	</script>
</head>

<body>
	<form method="post" action="<?=plugins_url().'/kboard-comments/execute/delete.php?uid='.$comment->uid?>" onsubmit="return password_checker(this);">
		<p><label for="input_password">비밀번호 확인</label></p>
		<p><input type="password" name="password" id="input_password"></p>
		<p><input type="submit" value="댓글 삭제하기"></p>
	</form>
</body>
</html>
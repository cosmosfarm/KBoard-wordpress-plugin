<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';

$uid = isset($_GET['uid'])?intval($_GET['uid']):'';

if(!$uid){
	die("<script>alert('".__('No UID of comments.', 'kboard-comments')."');window.close();</script>");
}

$commentList = new KBCommentList();
$comment = $commentList->getComment($uid);

if(!$comment->uid){
	die("<script>alert('".__('It is a comment does not exist.', 'kboard-comments')."');window.close();</script>");
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<meta name="author" content="http://www.cosmosfarm.com/">
	<title>KBoard - <?php echo __('Password confirmation', 'kboard-comments')?></title>
	<script src="<?php echo includes_url('/js/jquery/jquery.js')?>"></script>
	<script src="<?php echo includes_url('/js/jquery/jquery-migrate.min.js')?>"></script>
	<style>
		body { background-color: #f9f9f9; }
		label { font-size: 12px; }
		input { margin: 0; }
	</style>
	<script>
		function password_checker(form){
			if(!jQuery('input[name=password]').val()){
				alert('<?php echo __('Please enter a password.', 'kboard-comments')?>');
				jQuery('input[name=password]').focus();
				return false;
			}
			return true;
		}
	</script>
</head>

<body>
	<form method="post" action="<?php echo plugins_url().'/kboard-comments/execute/delete.php?uid='.$comment->uid?>" onsubmit="return password_checker(this);">
		<p><label for="input_password"><?php echo __('Password confirmation', 'kboard-comments')?></label></p>
		<p><input type="password" name="password" id="input_password"></p>
		<p><input type="submit" value="<?php echo __('Submit', 'kboard-comments')?>"></p>
	</form>
</body>
</html>
<?php
if(!defined('ABSPATH')) exit;

$uid = isset($_GET['uid'])?intval($_GET['uid']):'';
if(!$uid){
	die("<script>alert('".__('No UID of comments.', 'kboard-comments')."');window.close();</script>");
}

$commentList = new KBCommentList();
$comment = $commentList->getComment($uid);

if(!$comment->uid){
	die("<script>alert('".__('It is a comment does not exist.', 'kboard-comments')."');window.close();</script>");
}

$commentURL = new KBCommentUrl();
$commentURL->setCommentUID($comment->uid);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<meta name="author" content="http://www.cosmosfarm.com/">
	<title>KBoard - <?php echo __('Password confirmation', 'kboard-comments')?></title>
	<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
	<script type="text/javascript" src="https://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/flick/jquery-ui.css">
	<style>
		fieldset { border: none; }
		input { margin: 0; }
	</style>
</head>
<body>
<form id="kbaord-comments-confirm-form" method="post" action="<?php echo $commentURL->getDeleteURL()?>" onsubmit="return password_checker(this)">
	<fieldset>
		<legend><?php echo __('Password confirmation', 'kboard-comments')?></legend>
		<input type="password" name="password" id="input_password">
		<button type="submit" class="ui-button ui-state-default ui-button-text-only" role="button"><span class="ui-button-text"><?php echo __('Submit', 'kboard-comments')?></span></button>
	</fieldset>
</form>
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
</body>
</html>
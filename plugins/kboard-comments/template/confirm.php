<?php if(!defined('ABSPATH')) exit;?>
<!DOCTYPE html>
<html <?php language_attributes()?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta name="robots" content="noindex,follow">
	<title>KBoard - <?php echo __('Password confirmation', 'kboard-comments')?></title>
	<script type="text/javascript" src="//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
	<style>
	* { font-family: Apple SD Gothic Neo,Malgun Gothic,arial,sans-serif,arial,sans-serif; }
	html, body { margin: 0; padding: 0; }
	.kboard-popup-title { margin: 0; padding: 0 20px; font-size: 20px; font-weight: normal; line-height: 50px; border-bottom: 1px solid #e7e7e7; text-align: center; color: gray; }
	.kboard-popup-form { text-align: center; }
	.kboard-popup-row { padding: 20px 0; }
	.kboard-popup-row input { padding: 10px; border: 0; border-bottom: 1px solid #e7e7e7; font-size: 20px; }
	.row-button button { padding: 10px; border: 0; background-color: transparent; color: #f28178; font-size: 20px; font-weight: bold; }
	</style>
</head>
<body>
<h1 class="kboard-popup-title"><?php echo __('Password confirmation', 'kboard-comments')?></h1>
<form id="kbaord-comments-confirm-form" method="post" action="<?php echo $submit_action_url?>" onsubmit="return password_checker(this)">
	<div class="kboard-popup-form">
		<div class="kboard-popup-row"><input type="password" name="password" placeholder="<?php echo __('Password', 'kboard-comments')?>"></div>
		<div class="kboard-popup-row row-button"><button type="submit"><?php echo __('Submit', 'kboard-comments')?></button></div>
	</div>
</form>
<script>
window.onload = function(){
	jQuery('input[name=password]').focus();
}
function password_checker(form){
	if(!jQuery('input[name=password]').val()){
		alert('<?php echo __('Please enter the password.', 'kboard-comments')?>');
		jQuery('input[name=password]').focus();
		return false;
	}
	return true;
}
</script>
</body>
</html>
<?php if(!defined('ABSPATH')) exit;?>
<!DOCTYPE html>
<html <?php language_attributes()?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta name="robots" content="noindex,follow">
	<title><?php echo $content->title?></title>
</head>
<body>
<h1><?php echo $content->title?></h1>
<p>작성자:<?php echo $content->member_display?> / 날짜:<?php echo date('Y-m-d H:i:s', strtotime($content->date))?> / 조회수:<?php echo $content->view?></p>
<?php echo $content->getDocumentOptionsHTML()?>
<?php echo nl2br($content->content)?>

<script>
window.onload = function(){
	window.print();
};
</script>
</body>
</html>
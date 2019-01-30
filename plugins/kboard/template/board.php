<?php if(!defined('ABSPATH')) exit;?>
<!DOCTYPE html>
<html <?php language_attributes()?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<title><?php wp_title('')?></title>
	
	<style>
	html, body { margin: 0; padding: 0; width: 1px; min-width: 100%; *width: 100%; }
	a { color: #545861; }
	img { border: 0; }
	</style>
	
	<?php
	// SEO 정보 출력
	$seo->head();
	
	// 고유주소 또는 아이프레임으로 접근시 실행
	do_action('kboard_iframe_head');
	?>
</head>
<body class="kboard board-<?php echo $board_id?>">
	<div id="kboard" style="float:left;width:100%;min-height:250px">
		<?php echo kboard_builder(array('id'=>$board_id))?>
	</div>
	
	<?php if(kboard_iframe_id()):?>
	<script>
	function kboard_iframe_resize(){
		var kboard = document.getElementById('kboard');
		if(kboard.offsetHeight != 0 && parent.document.getElementById("kboard-iframe-<?php echo kboard_iframe_id()?>")){
			parent.document.getElementById("kboard-iframe-<?php echo kboard_iframe_id()?>").style.height = kboard.offsetHeight + "px";
		}
	}
	var kboard_iframe_resize_interval = setInterval(function(){
		kboard_iframe_resize();
	}, 100);
	</script>
	<?php endif?>
	
	<!--[if lt IE 9]><script src="<?php echo KBOARD_URL_PATH?>/template/js/html5.js"></script><![endif]-->
	<!--[if lt IE 9]><script src="<?php echo KBOARD_URL_PATH?>/template/js/respond.js"></script><![endif]-->
	
	<?php
	if(is_admin()) do_action('admin_print_footer_scripts');
	
	wp_footer();
	?>
</body>
</html>
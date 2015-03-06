<?php if(!defined('ABSPATH')) exit;?>
<!DOCTYPE html>
<html <?php language_attributes()?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width">
	<title><?php echo __('WordPress')?> KBoard <?php echo KBOARD_VERSION?></title>
	<link rel="stylesheet" id="font-awesome-ie7-css"  href="<?php echo KBOARD_URL_PATH?>/font-awesome/css/font-awesome.min.css?ver=<?php echo KBOARD_VERSION?>" type="text/css" media="all">
	<!--[if lte IE 7]><link rel="stylesheet" id=""  href="<?php echo KBOARD_URL_PATH?>/font-awesome/css/font-awesome-ie7.min.css?ver=<?php echo KBOARD_VERSION?>" type="text/css" media="all"><![endif]-->
	
	<?php
	// 게시판 스킨 스타일 파일 추가
	$skin = KBoardSkin::getInstance();
	foreach($skin->getActiveList() AS $key=>$value): if(!empty($value)):
	?>
	<link rel="stylesheet" id="kboard-skin-<?php echo $value?>-css"  href='<?php echo KBOARD_URL_PATH?>/skin/<?php echo $value?>/style.css?ver=<?php echo KBOARD_VERSION?>' type="text/css" media="all">
	<?php endif; endforeach;?>
	
	<?php
	// 댓글 스킨 스타일 파일 추가
	$result = $wpdb->get_results("SELECT DISTINCT `value` FROM `".KBOARD_DB_PREFIX."kboard_board_meta` WHERE `key`='comment_skin'");
	foreach($result as $row): if(!empty($row->value)):
	?>
	<link rel="stylesheet" id="kboard-comments-skin-<?php echo $row->value?>-css"  href='<?php echo KBOARD_COMMENTS_URL_PATH?>/skin/<?php echo $row->value?>/style.css?ver=<?php echo KBOARD_COMMNETS_VERSION?>' type="text/css" media="all">
	<?php endif; endforeach;?>
	
	<script src="<?php echo includes_url('/js/jquery/jquery.js')?>"></script>
	<script src="<?php echo includes_url('/js/jquery/jquery-migrate.min.js')?>"></script>
	<!--[if lt IE 9]><script src="<?php echo KBOARD_URL_PATH?>/template/js/html5.js"></script><![endif]-->
	<!--[if lt IE 9]><script src="<?php echo KBOARD_URL_PATH?>/template/js/respond.js"></script><![endif]-->
	<style>#wpadminbar {display: none;}</style>
</head>
<body>
	<div id="kboard" style="float: left; width: 100%; min-height: 250px;">
		<?php echo kboard_builder(array('id'=>$board_id))?>
	</div>
	<script>
	function kboard_iframe_resize(){
		var kboard = document.getElementById('kboard');
		if(kboard.offsetHeight != 0 && parent.document.getElementById("kboard-iframe-<?php echo $board_id?>")){
			parent.document.getElementById("kboard-iframe-<?php echo $board_id?>").style.height = kboard.offsetHeight + "px";
		}
	}
	setInterval(function(){
		kboard_iframe_resize();
	}, 100);
	</script>
	<?php wp_footer()?>
</body>
</html>
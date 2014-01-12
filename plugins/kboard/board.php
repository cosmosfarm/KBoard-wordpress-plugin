<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';

/**
 * KBoard 게시판 고유주소로 요청하기
 * 입력코드(Shortcode) 없이 직접 게시판을 요청합니다.
 * @author http://www.cosmosfarm.com/
 */

$board_id = intval($_GET['board_id']);
if(!$board_id) wp_die(__('Board ID does not exist.', 'kboard'));

$meta = new KBoardMeta($board_id);
if(!$meta->use_direct_url) exit;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width">
	<title><?php echo __('WordPress')?> KBoard <?php echo KBOARD_VERSION?></title>
	<!--[if lt IE 9]><script src="https://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	<!--[if lt IE 9]><script src="https://raw.github.com/scottjehl/Respond/master/respond.src.js"></script><![endif]-->
	<?php wp_head()?>
</head>
<body>
	<!-- 
		KBoard
		@author http://www.cosmosfarm.com/
	-->
	<div id="kboard" style="float: left; width: 100%; min-height: 250px;">
		<?php echo do_shortcode('[kboard id='.$board_id.']');?>
	</div>
	<script>
	/*
	 * iframe태그로 이 'board.php' 페이지를 요청할 수 있습니다.
	 * 자바스크립트 resize() 함수는 이 iframe으로 페이지를 불러올때 iframe의 height값을 자동으로 조정합니다.
	 * 단, iframe의 id값을 'kboardframe[게시판 아이디값]'으로 설정해야 합니다.
	 * ex: <iframe id="kboardframe1" src="kboard/board.php?board_id=1"></iframe>
	 * @author http://www.cosmosfarm.com/
	 *
		function resize(){
			var kboard = document.getElementById('kboard');
			if(kboard.offsetHeight != 0){
				parent.document.getElementById("kboardframe<?php echo $board_id?>").style.height = kboard.offsetHeight + "px";
			}
		}
		window.onload = function(){
			resize();
		}
	*/
	</script>
	<?php wp_footer()?>
</body>
</html>
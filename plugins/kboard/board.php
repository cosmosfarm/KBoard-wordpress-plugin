<?php
include reset(explode(DIRECTORY_SEPARATOR . 'wp-content', dirname(__FILE__) . DIRECTORY_SEPARATOR)) . DIRECTORY_SEPARATOR . 'wp-load.php';
$board_id = $_GET['board_id'];

if($board_id):
/**
 * KBoard 게시판 고유주소로 요청하기
 * 페이지를 생성하고, 입력코드(Shortcode) 삽입 없이 직접 게시판을 요청합니다.
 * 이 페이지는 Twenty_Eleven 테마에서 테스트 되었습니다.
 * @author www.cosmosfarm.com
 */
get_header(); ?>

<div id="primary" class="site-content">
	<div id="content" role="kboard">
		<!--
			KBoard 시작
			@author www.cosmosfarm.com
		-->
		<div id="kboard" style="float: left; width: 100%; min-height: 250px;">
			<?=do_shortcode('[kboard id='.$board_id.']');?>
		</div>
		<!--
			KBoard 완료
		-->
	</div><!-- #content -->
</div><!-- #primary -->

<script>
/*
 * 워드프레스 get_header(), get_sidebar(), get_footer() 함수를 제거하고 iframe태그로 이 'board.php' 페이지를 요청할 수 있습니다.
 * 자바스크립트 resize() 함수는 이 iframe으로 페이지를 불러올때 iframe의 height값을 자동으로 조정합니다.
 * 단, iframe의 id값을 'kboardframe[요청한 게시판의 아이디값]'으로 설정해야 합니다. (ex: <iframe id="kboardframe1" src="kboard/board.php?board_id=1"></iframe>)
 * @author www.cosmosfarm.com
 *
	function resize(){
		var kboard = document.getElementById('kboard');
		if(kboard.offsetHeight != 0){
			parent.document.getElementById("kboardframe<?=$board_id?>").style.height = kboard.offsetHeight + "px";
		}
	}
	window.onload = function(){
		resize();
	}
*/
</script>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
<?php endif;?>
<?php
/**
 * KBoard 템플릿 페이지 설정
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBTemplate {

	/**
	 * 템플릿 페이지를 표시한다.
	 */
	public function route(){
		$action = isset($_GET['action'])?$_GET['action']:'';
		switch($action){
			case 'kboard_media': add_action('wp_loaded', array($this, 'media')); break;
			case 'kboard_document_print': add_action('wp_loaded', array($this, 'documentPrint')); break;
		}
		
		add_action('wp_loaded', array($this, 'board'));
	}

	/**
	 * 게시판 화면을 출력한다.
	 * @param int $board_id
	 */
	public function board(){
		$board_id = isset($_GET['kboard_id'])?intval($_GET['kboard_id']):'';
		if($board_id){
			$meta = new KBoardMeta($board_id);
			if($meta->use_direct_url || isset($_SESSION['kboard_board_id'])){
				
				// SEO 정보
				include_once KBOARD_DIR_PATH . '/class/KBSeo.class.php';
				$seo = new KBSeo();
				
				// 어드민바 제거
				add_filter('show_admin_bar', '__return_false');
				
				// 스타일과 스크립트 등록
				kboard_style();
				kboard_scripts();
				if(defined('KBOARD_COMMNETS_VERSION')){
					kboard_comments_style();
					kboard_comments_scripts();
				}
				
				include_once KBOARD_DIR_PATH . '/template/board.php';
				exit;
			}
		}
	}

	/**
	 * 코스모스팜 소셜댓글 출력한다.
	 * @param KBoardMeta $meta
	 * @return string
	 */
	public function comments_plugin($meta){
		ob_start();
		$url = new KBUrl();
		$template = $this;
		include KBOARD_DIR_PATH . '/template/comments_plugin.php';
		return ob_get_clean();
	}

	/**
	 * 코스모스팜 소셜댓글의 회원연동 API 토큰을 반환한다.
	 * @return string
	 */
	public function get_comments_access_token(){
		if(defined('COSMOSFARM_COMMENTS_VERSION')){
			$comments = new Cosmosfarm_Comments_Core();
			return $comments->get_access_token();
		}
		else{
			return '';
		}
	}

	/**
	 * 이미지 추가하기 팝업창 화면을 출력한다.
	 */
	public function media(){
		$media = new KBContentMedia();
		$media->truncate();
		$media->board_id = intval(isset($_GET['board_id'])?$_GET['board_id']:'');
		$media->content_uid = intval(isset($_GET['content_uid'])?$_GET['content_uid']:'');
		$media->media_group = kboard_htmlclear(isset($_GET['media_group'])?$_GET['media_group']:'');

		include_once KBOARD_DIR_PATH . '/template/media.php';
		exit;
	}

	/**
	 * 이미지 추가하기 팝업창 화면을 출력한다.
	 */
	public function documentPrint(){
		$uid = isset($_GET['uid'])?intval($_GET['uid']):'';

		$content = new KBContent();
		$content->initWithUID($uid);

		if(!$content->uid){
			wp_die(__('You do not have permission.', 'kboard'));
		}

		$board = new KBoard($content->board_id);

		if(!$board->isReader($content->member_uid, $content->secret) && !$board->isConfirm($content->password, $content->uid)){
			wp_die(__('You do not have permission.', 'kboard'));
		}

		include_once KBOARD_DIR_PATH . '/template/document_print.php';
		exit;
	}
}
?>
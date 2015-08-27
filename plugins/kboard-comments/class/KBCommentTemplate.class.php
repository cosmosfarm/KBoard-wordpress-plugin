<?php
/**
 * KBoard 댓글 템플릿 페이지 설정
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentTemplate {
	
	public function __construct(){
		$action = isset($_GET['action'])?$_GET['action']:'';
		if($action == 'kboard_comment_confirm'){
			add_action('template_redirect', array($this, 'confirm'));
		}
	}
	
	/**
	 * 댓글의 비밀번호 입력창을 출력한다.
	 */
	public function confirm(){
		include_once KBOARD_COMMENTS_DIR_PATH . '/template/confirm.php';
		exit;
	}
}
?>
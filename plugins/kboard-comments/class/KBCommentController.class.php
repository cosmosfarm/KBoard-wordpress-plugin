<?php
/**
 * KBoard Comments Controller
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentController {
	
	public function __construct(){
		$action = isset($_GET['action'])?$_GET['action']:'';
		if($action == 'kboard_comment_insert'){
			add_action('template_redirect', array($this, 'insert'));
		}
		else if($action == 'kboard_comment_delete'){
			add_action('template_redirect', array($this, 'delete'));
		}
	}
	
	/**
	 * 댓글 입력
	 */
	public function insert(){
		$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
		$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
		
		header("Content-Type: text/html; charset=UTF-8");
		if(!stristr($referer, $host)) wp_die('KBoard : '.__('This page is restricted from external access.', 'kboard-comments'));
		
		$content = isset($_POST['content'])?$_POST['content']:'';
		$comment_content = isset($_POST['comment_content'])?$_POST['comment_content']:'';
		$member_display = isset($_POST['member_display'])?$_POST['member_display']:'';
		$password = isset($_POST['password'])?$_POST['password']:'';
		$captcha_text = isset($_POST['captcha'])?$_POST['captcha']:'';
		
		if(!class_exists('KBCaptcha')){
			include_once KBOARD_DIR_PATH.'/class/KBCaptcha.class.php';
		}
		
		$captcha = new KBCaptcha();
		$content = $content?$content:$comment_content;
		$content_uid = isset($_POST['content_uid'])?intval($_POST['content_uid']):'';
		$parent_uid = isset($_POST['parent_uid'])?intval($_POST['parent_uid']):'';
		$member_uid = isset($_POST['member_uid'])?intval($_POST['member_uid']):'';
		
		if(!is_user_logged_in() && !$member_display){
			die("<script>alert('".__('Please enter a author.', 'kboard-comments')."');history.go(-1);</script>");
		}
		else if(!is_user_logged_in() && !$password){
			die("<script>alert('".__('Please enter a password.', 'kboard-comments')."');history.go(-1);</script>");
		}
		else if(!$captcha->textCheck($captcha_text)){
			die("<script>alert('".__('The CAPTCHA code is not valid. Please enter the CAPTCHA code.', 'kboard-comments')."');history.go(-1);</script>");
		}
		else if(!$content_uid){
			die("<script>alert('".__('No document UID.', 'kboard-comments')."');history.go(-1);</script>");
		}
		else if(!$content){
			die("<script>alert('".__('Type the content of the comment.', 'kboard-comments')."');history.go(-1);</script>");
		}
		
		$document = new KBContent();
		$document->initWithUID($content_uid);
		$setting = new KBoardMeta($document->board_id);
		
		if(!is_user_logged_in() && $setting->permission_comment_write=='1'){
			die('<script>alert("'.__('You do not have permission.', 'kboard-comments').'");history.go(-1);</script>');
		}
		
		$commentList = new KBCommentList($content_uid);
		$commentList->add($parent_uid, $member_uid, $member_display, $content, $password);
		
		header("Location:{$referer}");
		exit;
	}
	
	/**
	 * 댓글 삭제
	 */
	public function delete(){
		global $user_ID;
		$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
		$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
		
		header("Content-Type: text/html; charset=UTF-8");
		if(!stristr($referer, $host)) wp_die('KBoard : '.__('This page is restricted from external access.', 'kboard-comments'));
		
		$userdata = $user_ID?get_userdata($user_ID):new stdClass();
		$uid = isset($_GET['uid'])?intval($_GET['uid']):'';
		$password = isset($_POST['password'])?$_POST['password']:'';
		
		if(!$uid){
			die("<script>alert('".__('No UID of comments.', 'kboard-comments')."');history.go(-1);</script>");
		}
		else if((!isset($userdata->ID) || !$userdata->ID) && !$password){
			die("<script>alert('".__('Please Log in to continue.', 'kboard-comments')."');history.go(-1);</script>");
		}
		
		$commentList = new KBCommentList();
		$comment = $commentList->getComment($uid);
		if(!$comment->isEditor() && $comment->password != $password){
			die("<script>alert('".__('You do not have permission.', 'kboard-comments')."');history.go(-1);</script>");
		}
		$commentList->setContentUID($comment->content_uid);
		$commentList->delete($uid);
		
		if($comment->password && $comment->password == $password){
			// 팝업창으로 비밀번호 확인 후 opener 윈도우를 새로고침 한다.
			echo '<script>';
			echo 'opener.window.location.reload();';
			echo 'window.close();';
			echo '</script>';
		}
		else{
			// 삭제권한이 있는 사용자일 경우 팝업창은 없기 때문에 페이지 이동한다.
			header("Location:{$referer}");
		}
		exit;
	}
}
?>
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
		else if($action == 'kboard_comment_update'){
			add_action('template_redirect', array($this, 'update'));
		}
		
		add_action('wp_ajax_kboard_comment_like', array($this, 'commentLike'));
		add_action('wp_ajax_nopriv_kboard_comment_like', array($this, 'commentLike'));
		
		add_action('wp_ajax_kboard_comment_unlike', array($this, 'commentUnlike'));
		add_action('wp_ajax_nopriv_kboard_comment_unlike', array($this, 'commentUnlike'));
	}
	
	/**
	 * 댓글 입력
	 */
	public function insert(){
		header("Content-Type: text/html; charset=UTF-8");
		
		$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
		$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
		if($referer){
			$url = parse_url($referer);
			$referer_host = $url['host'] . (isset($url['port'])&&$url['port']?':'.$url['port']:'');
		}
		else{
			wp_die(__('This page is restricted from external access.', 'kboard-comments'));
		}
		if(!in_array($referer_host, array($host))) wp_die(__('This page is restricted from external access.', 'kboard-comments'));
		
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
		
		$document = new KBContent();
		$document->initWithUID($content_uid);
		$board = new KBoard($document->board_id);
		
		if(!$board->id){
			die("<script>alert('".__('Board does not exist.', 'kboard-comments')."');history.go(-1);</script>");
		}
		else if(!$document->uid){
			die("<script>alert('".__('Document does not exist.', 'kboard-comments')."');history.go(-1);</script>");
		}
		else if(!is_user_logged_in() && $board->meta->permission_comment_write){
			die('<script>alert("'.__('You do not have permission.', 'kboard-comments').'");history.go(-1);</script>');
		}
		else if(!is_user_logged_in() && !$member_display){
			die("<script>alert('".__('Please enter the author.', 'kboard-comments')."');history.go(-1);</script>");
		}
		else if(!is_user_logged_in() && !$password){
			die("<script>alert('".__('Please enter the password.', 'kboard-comments')."');history.go(-1);</script>");
		}
		else if($board->useCAPTCHA() && !$captcha->textCheck($captcha_text)){
			die("<script>alert('".__('The CAPTCHA is invalid. Please enter the CAPTCHA.', 'kboard-comments')."');history.go(-1);</script>");
		}
		else if(!$content){
			die("<script>alert('".__('Please enter the content.', 'kboard-comments')."');history.go(-1);</script>");
		}
		else if(!$content_uid){
			die("<script>alert('".__('content_uid is required.', 'kboard-comments')."');history.go(-1);</script>");
		}
		
		$commentList = new KBCommentList($content_uid);
		$commentList->add($parent_uid, $member_uid, $member_display, $content, $password);
		
		header("Location: {$referer}#kboard-comments");
		exit;
	}
	
	/**
	 * 댓글 삭제
	 */
	public function delete(){
		header("Content-Type: text/html; charset=UTF-8");
		
		$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
		$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
		if($referer){
			$url = parse_url($referer);
			$referer_host = $url['host'] . (isset($url['port'])&&$url['port']?':'.$url['port']:'');
		}
		else{
			wp_die(__('This page is restricted from external access.', 'kboard-comments'));
		}
		if(!in_array($referer_host, array($host))) wp_die(__('This page is restricted from external access.', 'kboard-comments'));
		
		$uid = isset($_GET['uid'])?intval($_GET['uid']):'';
		$password = isset($_POST['password'])?$_POST['password']:'';
		
		if(!$uid){
			die("<script>alert('".__('uid is required.', 'kboard-comments')."');history.go(-1);</script>");
		}
		else if(!is_user_logged_in() && !$password){
			die("<script>alert('".__('Please log in to continue.', 'kboard-comments')."');history.go(-1);</script>");
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
			header("Location: {$referer}");
		}
		exit;
	}
	
	/**
	 * 댓글 수정
	 */
	public function update(){
		header("Content-Type: text/html; charset=UTF-8");
		
		$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
		$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
		if($referer){
			$url = parse_url($referer);
			$referer_host = $url['host'] . (isset($url['port'])&&$url['port']?':'.$url['port']:'');
		}
		else{
			wp_die(__('This page is restricted from external access.', 'kboard-comments'));
		}
		if(!in_array($referer_host, array($host))) wp_die(__('This page is restricted from external access.', 'kboard-comments'));
		
		$uid = isset($_GET['uid'])?intval($_GET['uid']):'';
		$password = isset($_POST['password'])?$_POST['password']:'';
		$content = isset($_POST['content'])?$_POST['content']:'';
		
		if(!$uid){
			die("<script>alert('".__('uid is required.', 'kboard-comments')."');history.go(-1);</script>");
		}
		else if(!$content){
			die("<script>alert('".__('Please enter the content.', 'kboard-comments')."');history.go(-1);</script>");
		}
		else if(!is_user_logged_in() && !$password){
			die("<script>alert('".__('Please log in to continue.', 'kboard-comments')."');history.go(-1);</script>");
		}
		
		$comment = new KBComment();
		$comment->initWithUID($uid);
		
		if(!$comment->isEditor() && $comment->password != $password){
			die("<script>alert('".__('You do not have permission.', 'kboard-comments')."');history.go(-1);</script>");
		}
		
		$comment->content = $content;
		$comment->update();
		
		echo '<script>';
		echo 'opener.window.location.reload();';
		echo 'window.close();';
		echo '</script>';
		exit;
	}
	
	/**
	 * 댓글 좋아요
	 */
	public function commentLike(){
		if(isset($_POST['comment_uid']) && intval($_POST['comment_uid'])){
			if(!@in_array($_POST['comment_uid'], $_SESSION['comment_vote'])){
				$_SESSION['comment_vote'][] = $_POST['comment_uid'];
				
				$comment = new KBComment();
				$comment->initWithUID($_POST['comment_uid']);
				
				if($comment->uid){
					$comment->like+=1;
					$comment->vote = $comment->like - $comment->unlike;
					$comment->update();
					echo intval($comment->like);
					exit;
				}
			}
		}
		exit;
	}
	
	/**
	 * 댓글 싫어요
	 */
	public function commentUnlike(){
		if(isset($_POST['comment_uid']) && intval($_POST['comment_uid'])){
			if(!@in_array($_POST['comment_uid'], $_SESSION['comment_vote'])){
				$_SESSION['comment_vote'][] = $_POST['comment_uid'];
				
				$comment = new KBComment();
				$comment->initWithUID($_POST['comment_uid']);
				
				if($comment->uid){
					$comment->unlike+=1;
					$comment->vote = $comment->like - $comment->unlike;
					$comment->update();
					echo intval($comment->unlike);
					exit;
				}
			}
		}
		exit;
	}
}
?>
<?php
/**
 * KBoard 댓글 템플릿 페이지 설정
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentTemplate {
	
	public function __construct(){
		$action = isset($_GET['action'])?$_GET['action']:'';
		switch($action){
			case 'kboard_comment_confirm': add_action('template_redirect', array($this, 'confirm')); break;
			case 'kboard_comment_edit': add_action('template_redirect', array($this, 'edit')); break;
		}
	}
	
	/**
	 * 댓글의 비밀번호 입력창을 출력한다.
	 */
	public function confirm(){
		$uid = isset($_GET['uid'])?intval($_GET['uid']):'';
		if(!$uid){
			die("<script>alert('".__('uid is required.', 'kboard-comments')."');window.close();</script>");
		}
		
		$comment = new KBComment();
		$comment->initWithUID($uid);
		
		if(!$comment->uid){
			die("<script>alert('".__('Comment does not exist.', 'kboard-comments')."');window.close();</script>");
		}
		if(!$comment->password){
			die("<script>alert('".__('You do not have permission.', 'kboard-comments')."');window.close();</script>");
		}
		
		$commentURL = new KBCommentUrl();
		$commentURL->setCommentUID($comment->uid);
		
		$password = isset($_POST['password'])?$_POST['password']:'';
		
		if($password){
			if($comment->password && $comment->password == $password){
				$delete_url = $commentURL->getDeleteURL();
				
				// 비밀번호 nonce 추가
				$delete_url = add_query_arg('kboard-comments-delete-nonce', wp_create_nonce("kboard-comments-delete-{$comment->password}"), $delete_url);
				?>
				<!DOCTYPE html>
				<html <?php language_attributes()?>>
				<head>
					<meta charset="UTF-8">
					<meta name="robots" content="noindex,nofollow">
				</head>
				<body onload="document.kboard_comments_delete.submit()">
					<form method="post" action="<?php echo esc_attr($delete_url)?>" name="kboard_comments_delete">
						<input type="hidden" name="password" value="<?php echo esc_attr($password)?>">
					</form>
				</body>
				</html>
				<?php
				exit;
			}
		}
		
		$submit_action_url = $commentURL->getConfirmURL();
		
		include_once KBOARD_COMMENTS_DIR_PATH . '/template/confirm.php';
		exit;
	}
	
	/**
	 * 댓글의 편집창을 출력한다.
	 */
	public function edit(){
		$uid = isset($_GET['uid'])?intval($_GET['uid']):'';
		if(!$uid){
			die("<script>alert('".__('uid is required.', 'kboard-comments')."');window.close();</script>");
		}
		
		$comment = new KBComment();
		$comment->initWithUID($uid);
		
		if(!$comment->uid){
			die("<script>alert('".__('Comment does not exist.', 'kboard-comments')."');window.close();</script>");
		}
		if(!$comment->password && !is_user_logged_in()){
			die("<script>alert('".__('You do not have permission.', 'kboard-comments')."');window.close();</script>");
		}
		
		$password = isset($_POST['password'])?$_POST['password']:'';
		
		if($comment->isEditor() || ($comment->password && $comment->password == $password)){
			$commentURL = new KBCommentUrl();
			$commentURL->setCommentUID($comment->uid);
			$submit_action_url = $commentURL->getUpdateURL();
			
			if($comment->password){
				// 비밀번호 nonce 추가
				$submit_action_url = add_query_arg('kboard-comments-update-nonce', wp_create_nonce("kboard-comments-update-{$comment->password}"), $submit_action_url);
			}
			
			include_once KBOARD_COMMENTS_DIR_PATH . '/template/edit.php';
		}
		else{
			if($password && $comment->password != $password){
				die("<script>alert('".__('You do not have permission.', 'kboard-comments')."');history.go(-1);</script>");
			}
			$commentURL = new KBCommentUrl();
			$commentURL->setCommentUID($comment->uid);
			$submit_action_url = $commentURL->getEditURL();
			include_once KBOARD_COMMENTS_DIR_PATH . '/template/confirm.php';
		}
		exit;
	}
}
?>
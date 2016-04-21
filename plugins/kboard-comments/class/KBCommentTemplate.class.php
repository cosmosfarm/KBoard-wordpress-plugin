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
		else if($action == 'kboard_comment_edit'){
			add_action('template_redirect', array($this, 'edit'));
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
		
		$commentURL = new KBCommentUrl();
		$commentURL->setCommentUID($comment->uid);
		$submit_action_url = $commentURL->getDeleteURL();
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
		
		$password = isset($_POST['password'])?$_POST['password']:'';
		
		if($comment->isEditor() || ($comment->password && $comment->password == $password)){
			$commentURL = new KBCommentUrl();
			$commentURL->setCommentUID($comment->uid);
			$submit_action_url = $commentURL->getUpdateURL();
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
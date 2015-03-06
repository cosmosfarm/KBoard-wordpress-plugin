<?php
/**
 * KBoard Controller
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBController {
	
	public function __construct(){
		header("Content-Type: text/html; charset=UTF-8");
	}
	
	public function init(){
		$action = isset($_POST['action'])?$_POST['action']:'';
		if($action == 'kboard_editor_execute'){
			add_action('template_redirect', array($this, 'editorExecute'));
		}
	}
	
	public function editorExecute(){
		global $user_ID;
		
		if(isset($_POST['kboard-editor-execute-nonce']) && wp_verify_nonce($_POST['kboard-editor-execute-nonce'], 'kboard-editor-execute')){
			$uid = intval($_POST['uid']);
			$board_id = intval($_POST['board_id']);
			
			$board = new KBoard($board_id);
			if(!$board->uid){
				die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
			}
			
			if($board->isWriter() && $board->permission_write=='all' && $_POST['title']){
				if(!$user_ID && !$_POST['password']){
					die('<script>alert("'.__('Please enter your password.', 'kboard').'");history.go(-1);";</script>');
				}
			}
			
			$content = new KBContent();
			$content->initWithUID($uid);
			$content->setBoardID($board_id);
			
			if(!$uid && !$board->isWriter()){
				die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
			}
			else if($uid && !$board->isEditor($content->member_uid)){
				if($board->permission_write=='all'){
					if(!$board->isConfirm($content->password, $content->uid)){
						die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
					}
				}
				else{
					die('<script>alert("'.__('You do not have permission.', 'kboard').'");history.go(-1);</script>');
				}
			}
			
			$execute_uid = $content->execute();
			// 비밀번호가 입력되면 즉시 인증과정을 거친다.
			if($content->password) $board->isConfirm($content->password, $execute_uid);
			
			$url = new KBUrl();
			$next_page_url = $url->set('uid', $execute_uid)->set('mod', 'document')->toString();
			$next_page_url = apply_filters('kboard_after_executing_url', $next_page_url, $execute_uid, $board_id);
			wp_redirect($next_page_url);
		}
		else{
			wp_redirect(site_url());
		}
		exit;
	}
}
?>
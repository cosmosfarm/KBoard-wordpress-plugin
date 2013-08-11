<?php
/*
Plugin Name: KBoard : 댓글
Plugin URI: http://www.cosmosfarm.com/products/kboard
Description: 워드프레스 KBoard 댓글 플러그인 입니다.
Version: 2.2
Author: Cosmosfarm
Author URI: http://www.cosmosfarm.com/
*/

define('KBOARD_COMMNETS_VERSION', '2.2');
define('KBOARD_WORDPRESS_ROOT', substr(ABSPATH, 0, -1));

include_once 'Comment.class.php';
include_once 'CommentList.class.php';
include_once 'CommentsBuilder.class.php';
include_once 'KBCommentSkin.class.php';

define('KBOARD_COMMENTS_PAGE_TITLE', 'KBoard : 댓글');
define('KBOARD_COMMENTS_DIR_PATH', str_replace(DIRECTORY_SEPARATOR . 'index.php', '', __FILE__));
define('KBOARD_COMMENTS_URL_PATH', plugins_url('kboard-comments'));
define('KBOARD_COMMENTS_LIST_PAGE', admin_url('/admin.php?page=kboard_comments_list'));

/*
 * 관리자메뉴에 추가
 */
add_action('admin_menu', 'kboard_comments_settings_menu');
function kboard_comments_settings_menu(){
	
}

/*
 * 댓글 목록 페이지
 */
function kboard_comments_list(){
	$commentList = new CommentList();
	
	$action = $_POST['action'];
	$action2 = $_POST['action2'];
	if(($action=='remove' || $action2=='remove') && $_POST['comment_uid']){
		foreach($_POST['comment_uid'] AS $key => $value){
			$commentList->delete($value);
		}
	}
	
	$commentList->order = 'DESC';
	include_once 'pages/comments_list.php';
}

/*
 * 페이지 표시 단축코드
 */
add_shortcode('kboard_comments', 'kboard_comments_builder');
function kboard_comments_builder($atts){
	$comments_builder = new CommentsBuilder();
	$comments_builder->content_uid = $atts['content_uid'];
	if($atts['skin']) $comments_builder->setSkin($atts['skin']);
	return $comments_builder->create();
}

/*
 * 활성화
 */
register_activation_hook(__FILE__, 'kboard_comments_activation');
function kboard_comments_activation(){
	if(!defined('KBOARD_VERSION')){
		echo 'KBoard 댓글 알림 :: 먼저 KBoard 플러그인을 설치하세요. http://www.cosmosfarm.com/ 에서 다운로드 가능합니다.';
		exit;
	}
	
	$kboard_comments = "CREATE TABLE IF NOT EXISTS `".KBOARD_DB_PREFIX."kboard_comments` (
	  `uid` bigint(20) unsigned NOT NULL auto_increment,
	  `content_uid` bigint(20) unsigned NOT NULL,
	  `user_uid` bigint(20) unsigned NOT NULL,
	  `user_display` varchar(127) NOT NULL,
	  `content` text NOT NULL,
	  `created` char(14) NOT NULL,
	  `password` varchar(127) NOT NULL,
	  PRIMARY KEY  (`uid`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	kboard_query($kboard_comments);
}

/*
 * 비활성화
 */
register_deactivation_hook(__FILE__, 'kboard_comments_deactivation');
function kboard_comments_deactivation(){
	
}

/*
 * 언인스톨
 */
register_uninstall_hook(__FILE__, 'kboard_comments_uninstall');
function kboard_comments_uninstall(){
	$drop_table = "DROP TABLE `".KBOARD_DB_PREFIX."kboard_comments`";
	kboard_query($drop_table);
}
?>
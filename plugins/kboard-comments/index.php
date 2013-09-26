<?php
/*
Plugin Name: KBoard : 댓글
Plugin URI: http://www.cosmosfarm.com/products/kboard
Description: 워드프레스 KBoard 댓글 플러그인 입니다.
Version: 3.2
Author: Cosmosfarm
Author URI: http://www.cosmosfarm.com/
*/

if(!defined('ABSPATH')) exit;

define('KBOARD_COMMNETS_VERSION', '3.2');
define('KBOARD_COMMENTS_PAGE_TITLE', 'KBoard : 댓글');
define('KBOARD_COMMENTS_DIR_PATH', str_replace(DIRECTORY_SEPARATOR . 'index.php', '', __FILE__));
define('KBOARD_COMMENTS_URL_PATH', plugins_url('kboard-comments'));
define('KBOARD_COMMENTS_LIST_PAGE', admin_url('/admin.php?page=kboard_comments_list'));

include_once 'class/KBComment.class.php';
include_once 'class/KBCommentList.class.php';
include_once 'class/KBCommentsBuilder.class.php';
include_once 'class/KBCommentSkin.class.php';

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
	kboard_comments_system_update();
	$commentList = new KBCommentList();
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
	$comments_builder = new KBCommentsBuilder();
	$comments_builder->content_uid = $atts['content_uid'];
	if($atts['skin']) $comments_builder->setSkin($atts['skin']);
	return $comments_builder->create();
}

/*
 * 활성화
 */
register_activation_hook(__FILE__, 'kboard_comments_activation');
function kboard_comments_activation(){
	global $wpdb;
	
	if(!defined('KBOARD_VERSION')){
		die('KBoard 댓글 알림 :: 먼저 KBoard 플러그인을 설치하세요. http://www.cosmosfarm.com/ 에서 다운로드 가능합니다.');
	}
	
	$kboard_comments = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."kboard_comments` (
	  `uid` bigint(20) unsigned NOT NULL auto_increment,
	  `content_uid` bigint(20) unsigned NOT NULL,
	  `parent_uid` bigint(20) unsigned NOT NULL,
	  `user_uid` bigint(20) unsigned NOT NULL,
	  `user_display` varchar(127) NOT NULL,
	  `content` text NOT NULL,
	  `created` char(14) NOT NULL,
	  `password` varchar(127) NOT NULL,
	  PRIMARY KEY  (`uid`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	kboard_query($kboard_comments);
	
	/*
	 * KBoard 댓글 3.2
	 * kboard_comments `parent_uid` 컬럼 생성 확인
	 */
	$resource = kboard_query("DESCRIBE `".$wpdb->prefix."kboard_comments` `parent_uid`");
	list($name) = mysql_fetch_row($resource);
	if(!$name){
		kboard_query("ALTER TABLE `".$wpdb->prefix."kboard_comments` ADD `parent_uid` BIGINT UNSIGNED NOT NULL AFTER `content_uid`");
	}
	unset($resource, $name);
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
	global $wpdb;
	$drop_table = "DROP TABLE `".$wpdb->prefix."kboard_comments`";
	mysql_query($drop_table);
}

/*
 * 시스템 업데이트
 */
function kboard_comments_system_update(){
	/*
	 * KBoard 댓글 2.8
	 * 파일 제거
	 */
	@unlink(KBOARD_COMMENTS_DIR_PATH . '/Comment.class.php');
	@unlink(KBOARD_COMMENTS_DIR_PATH . '/CommentList.class.php');
	@unlink(KBOARD_COMMENTS_DIR_PATH . '/CommentsBuilder.class.php');
	@unlink(KBOARD_COMMENTS_DIR_PATH . '/KBCommentSkin.class.php');
	@unlink(KBOARD_COMMENTS_DIR_PATH . '/KBCommentUrl.class.php');
	
	/*
	 * KBoard 댓글 3.2
	 * kboard_comments `parent_uid` 컬럼 생성 확인
	 */
	$resource = kboard_query("DESCRIBE `".KBOARD_DB_PREFIX."kboard_comments` `parent_uid`");
	list($name) = mysql_fetch_row($resource);
	if(!$name){
		kboard_comments_activation();
		return;
	}
	unset($resource, $name);
}
?>
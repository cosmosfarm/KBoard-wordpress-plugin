<?php
/*
Plugin Name: KBoard : 댓글
Plugin URI: http://www.cosmosfarm.com/products/kboard
Description: 워드프레스 KBoard 댓글 플러그인 입니다.
Version: 4.0
Author: Cosmosfarm
Author URI: http://www.cosmosfarm.com/
*/

if(!defined('ABSPATH')) exit;
if(in_array('kboard/index.php', get_option('active_plugins')) || in_array('kboard/index.php', array_keys(get_site_option('active_sitewide_plugins')))){

define('KBOARD_COMMNETS_VERSION', '4.0');
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
function kboard_comments_settings_menu(){
	add_submenu_page('kboard_dashboard', KBOARD_COMMENTS_PAGE_TITLE, '전체 댓글', 'administrator', 'kboard_comments_list', 'kboard_comments_list');
}

/*
 * 댓글 목록 페이지
 */
function kboard_comments_list(){
	kboard_comments_system_update();
	$commentList = new KBCommentList();
	$action = isset($_POST['action'])?$_POST['action']:'';
	$action2 = isset($_POST['action2'])?$_POST['action2']:'';
	if(($action=='remove' || $action2=='remove') && isset($_POST['comment_uid']) && $_POST['comment_uid']){
		foreach($_POST['comment_uid'] AS $key => $value){
			$commentList->delete($value);
		}
	}
	
	$commentList->order = 'DESC';
	$commentList->page = isset($_GET['pageid'])?intval($_GET['pageid']):1;
	$commentList->init();
	include_once 'pages/comments_list.php';
}

/*
 * 페이지 표시 단축코드
 */
add_shortcode('kboard_comments', 'kboard_comments_builder');
function kboard_comments_builder($atts){
	$commentBuilder = new KBCommentsBuilder();
	$commentBuilder->board_id = $atts['board_id'];
	$commentBuilder->content_uid = $atts['content_uid'];
	$commentBuilder->permission_comment_write = $atts['permission_comment_write'];
	$commentBuilder->setSkin($atts['skin']);
	return $commentBuilder->create();
}

/*
 * 댓글 스크립트 추가
 */
add_action('wp_enqueue_scripts', 'kboard_comments_script');
function kboard_comments_script(){
	$mod = isset($_GET['mod'])?kboard_htmlclear($_GET['mod']):'';
	$uid = isset($_GET['uid'])?intval($_GET['uid']):'';
	if($mod == 'document' && $uid){
		wp_enqueue_script('kboard-comments', 'http://contents.cosmosfarm.com/wordpress/kboard-comments.js', array(), KBOARD_COMMNETS_VERSION);
	}
}

/*
 * 언어 파일 추가
 */
add_action('plugins_loaded', 'kboard_comments_languages');
function kboard_comments_languages(){
	load_plugin_textdomain('kboard-comments', false, dirname(plugin_basename(__FILE__)).'/languages/');
}

/*
 * 관리자 알림 출력
 */
add_action('admin_notices', 'kboard_comments_admin_notices');
function kboard_comments_admin_notices(){
	$upgrader = KBUpgrader::getInstance();
	if(KBOARD_COMMNETS_VERSION < $upgrader->getLatestVersion()->comments){
		echo '<div class="updated"><p>KBoard 댓글 : '.$upgrader->getLatestVersion()->comments.' 버전으로 업그레이드가 가능합니다. - <a href="'.admin_url('/admin.php?page=kboard_dashboard').'">대시보드로 이동</a> 또는 <a href="http://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href); return false;">홈페이지 열기</a></p></div>';
	}
}

/*
 * 스타일 파일을 출력한다.
 */
add_action('wp_enqueue_scripts', 'kboard_comments_style', 999);
function kboard_comments_style(){
	global $wpdb;
	$result = $wpdb->get_results("SELECT DISTINCT `value` FROM `{$wpdb->prefix}kboard_board_meta` WHERE `key`='comment_skin'");
	foreach($result as $row){
		if(!empty($row->value)){
			wp_enqueue_style("kboard-comments-skin-{$row->value}", KBOARD_COMMENTS_URL_PATH.'/skin/'.$row->value.'/style.css', array(), KBOARD_COMMNETS_VERSION);
		}
	}
}

/*
 * 스킨의 functions.php 파일을 실행한다.
 */
add_action('init', 'kboard_comments_skin_functions');
function kboard_comments_skin_functions(){
	global $wpdb;
	$result = $wpdb->get_results("SELECT DISTINCT `value` FROM `{$wpdb->prefix}kboard_board_meta` WHERE `key`='comment_skin'");
	foreach($result as $row){
		if(file_exists(KBOARD_COMMENTS_DIR_PATH.'/skin/'.$row->value.'/functions.php')) include_once KBOARD_COMMENTS_DIR_PATH.'/skin/'.$row->value.'/functions.php';
	}
}

/*
 * 시스템 업데이트
 */
add_action('admin_init', 'kboard_comments_system_update');
function kboard_comments_system_update(){
	global $wpdb;
	
	// 시스템 업데이트를 이미 진행 했다면 중단한다.
	if(KBOARD_COMMNETS_VERSION <= get_option('kboard_comments_version')) return;
	
	// 시스템 업데이트를 확인하기 위해서 버전 등록
	if(get_option('kboard_comments_version') !== false) update_option('kboard_comments_version', KBOARD_COMMNETS_VERSION);
	else add_option('kboard_comments_version', KBOARD_COMMNETS_VERSION, null, 'no');
	
	// 관리자 알림
	add_action('admin_notices', create_function('', "echo '<div class=\"updated\"><p>KBoard 댓글 : '.KBOARD_COMMNETS_VERSION.' 버전으로 업그레이드 되었습니다. - <a href=\"http://www.cosmosfarm.com/products/kboard\" onclick=\"window.open(this.href); return false;\">홈페이지 열기</a></p></div>';"));
	
	$networkwide = is_plugin_active_for_network(__FILE__);
	
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
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_comments` `parent_uid`", ARRAY_N);
	if(!$name){
		kboard_comments_activation($networkwide);
		return;
	}
	unset($name);
}
} // KBoard 게시판 플러그인이 활성화 돼 있어야 동작하는 구간 완료

/*
 * 활성화
 */
register_activation_hook(__FILE__, 'kboard_comments_activation');
function kboard_comments_activation($networkwide){
	global $wpdb;
	
	if(!defined('KBOARD_VERSION')){
		die('KBoard 댓글 알림 :: 먼저 KBoard 게시판 플러그인을 설치하고 활성화 해주세요. http://www.cosmosfarm.com/ 에서 다운로드 가능합니다.');
	}
	
	if(function_exists('is_multisite') && is_multisite()){
		if($networkwide){
			$old_blog = $wpdb->blogid;
			$blogids = $wpdb->get_col("SELECT `blog_id` FROM $wpdb->blogs");
			foreach($blogids as $blog_id){
				switch_to_blog($blog_id);
				kboard_comments_activation_execute();
			}
			switch_to_blog($old_blog);
			return;
		}
	}
	kboard_comments_activation_execute();
}

/*
 * 활성화 실행
 */
function kboard_comments_activation_execute(){
	global $wpdb;
	
	$wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}kboard_comments` (
		`uid` bigint(20) unsigned NOT NULL auto_increment,
		`content_uid` bigint(20) unsigned NOT NULL,
		`parent_uid` bigint(20) unsigned default NULL,
		`user_uid` bigint(20) unsigned default NULL,
		`user_display` varchar(127) default NULL,
		`content` longtext NOT NULL,
		`created` char(14) NOT NULL,
		`password` varchar(127) default NULL,
		PRIMARY KEY (`uid`)
	) DEFAULT CHARSET=utf8");
	
	/*
	 * KBoard 댓글 3.2
	 * kboard_comments `parent_uid` 컬럼 생성 확인
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_comments` `parent_uid`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_comments` ADD `parent_uid` bigint(20) unsigned default NULL AFTER `content_uid`");
	}
	unset($name);
}

/*
 * 비활성화
 */
register_deactivation_hook(__FILE__, 'kboard_comments_deactivation');
function kboard_comments_deactivation($networkwide){
	
}

/*
 * 언인스톨
 */
register_uninstall_hook(__FILE__, 'kboard_comments_uninstall');
function kboard_comments_uninstall(){
	global $wpdb;
	
	if(function_exists('is_multisite') && is_multisite()){
		$old_blog = $wpdb->blogid;
		$blogids = $wpdb->get_col("SELECT `blog_id` FROM $wpdb->blogs");
		foreach($blogids as $blog_id){
			switch_to_blog($blog_id);
			kboard_comments_uninstall_exeucte();
		}
		switch_to_blog($old_blog);
		return;
	}
	kboard_comments_uninstall_exeucte();
}

/*
 * 언인스톨 실행
 */
function kboard_comments_uninstall_exeucte(){
	global $wpdb;
	$wpdb->query("DROP TABLE `{$wpdb->prefix}kboard_comments`");
}
?>

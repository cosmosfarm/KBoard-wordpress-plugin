<?php
/*
Plugin Name: KBoard : 댓글
Plugin URI: http://www.cosmosfarm.com/products/kboard
Description: 워드프레스 KBoard 댓글 플러그인 입니다.
Version: 4.4.2
Author: 코스모스팜 - Cosmosfarm
Author URI: http://www.cosmosfarm.com/
*/

if(!defined('ABSPATH')) exit;
if(!function_exists('is_plugin_active') || !function_exists('is_plugin_active_for_network')) require_once(ABSPATH . '/wp-admin/includes/plugin.php');
if(is_plugin_active('kboard/index.php') || is_plugin_active_for_network('kboard/index.php')){

define('KBOARD_COMMNETS_VERSION', '4.4.2');
define('KBOARD_COMMENTS_PAGE_TITLE', __('KBoard : 댓글', 'kboard-comments'));
define('KBOARD_COMMENTS_DIR_PATH', dirname(__FILE__));
define('KBOARD_COMMENTS_URL_PATH', plugins_url('', __FILE__));
define('KBOARD_COMMENTS_LIST_PAGE', admin_url('admin.php?page=kboard_comments_list'));

include_once 'class/KBComment.class.php';
include_once 'class/KBCommentController.class.php';
include_once 'class/KBCommentList.class.php';
include_once 'class/KBCommentOption.class.php';
include_once 'class/KBCommentsBuilder.class.php';
include_once 'class/KBCommentSkin.class.php';
include_once 'class/KBCommentTemplate.class.php';
include_once 'class/KBCommentUrl.class.php';

/*
 * KBoard 댓글 시작
 */
add_action('init', 'kboard_comments_init', 0);
function kboard_comments_init(){
	
	// 컨트롤러 시작
	$comment_controller = new KBCommentController();
	
	// 템플릿 시작
	$comment_template = new KBCommentTemplate();
	
	$kboard_comments_sort = isset($_GET['kboard_comments_sort'])?$_GET['kboard_comments_sort']:'';
	if($kboard_comments_sort){
		$_COOKIE['kboard_comments_sort'] = $kboard_comments_sort;
		setcookie('kboard_comments_sort', $kboard_comments_sort, strtotime('+1 year'), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
	}
	
	// 스킨의 functions.php 파일을 실행한다.
	$skin = KBCommentSkin::getInstance();
	foreach($skin->getActiveList() as $skin_name){
		$skin->loadFunctions($skin_name);
	}
}

/*
 * 관리자메뉴에 추가
 */
function kboard_comments_settings_menu(){
	add_submenu_page('kboard_dashboard', KBOARD_COMMENTS_PAGE_TITLE, __('전체 댓글', 'kboard-comments'), 'administrator', 'kboard_comments_list', 'kboard_comments_list');
}

/*
 * 댓글 임시저장 데이터를 반환한다.
 */
function kboard_comments_get_temporary(){
	static $temporary;
	if($temporary === null){
		if(isset($_SESSION['kboard_temporary_comments']) && $_SESSION['kboard_temporary_comments']){
			$temporary = $_SESSION['kboard_temporary_comments'];
		}
		else{
			$temporary = new stdClass();
			$temporary->member_display = '';
			$temporary->content = '';
		}
		if(!isset($temporary->option) || !(array)$temporary->option){
			$temporary->option = new KBCommentOption();
		}
	}
	return $temporary;
}

/*
 * 댓글 목록 페이지
 */
function kboard_comments_list(){
	include_once 'class/KBCommentListTable.class.php';
	$table = new KBCommentListTable();
	if(isset($_POST['comment_uid']) && $table->current_action() == 'delete'){
		$comment = new KBComment();
		foreach($_POST['comment_uid'] as $key=>$comment_uid){
			$comment->initWithUID($comment_uid);
			$comment->delete();
		}
	}
	$table->prepare_items();
	include_once 'pages/comments_list.php';
}

/*
 * 댓글 숏코드
 */
add_shortcode('kboard_comments', 'kboard_comments_builder');
function kboard_comments_builder($atts){
	$comment_builder = new KBCommentsBuilder();
	$comment_builder->board = $atts['board'];
	$comment_builder->board_id = $atts['board_id'];
	$comment_builder->content_uid = $atts['content_uid'];
	$comment_builder->permission_comment_write = $atts['permission_comment_write'];
	$comment_builder->setSkin($atts['skin']);
	return $comment_builder->create();
}

/*
 * 댓글 스크립트 추가
 */
add_action('wp_enqueue_scripts', 'kboard_comments_scripts', 9999);
function kboard_comments_scripts(){
	
	// 번역 등록
	$localize = array(
			'reply' => __('Reply', 'kboard-comments'),
			'cancel' => __('Cancel', 'kboard-comments'),
			'please_enter_the_author' => __('Please enter the author.', 'kboard-comments'),
			'please_enter_the_password' => __('Please enter the password.', 'kboard-comments'),
			'please_enter_the_CAPTCHA' => __('Please enter the CAPTCHA.', 'kboard-comments'),
			'please_enter_the_content' => __('Please enter the content.', 'kboard-comments'),
			'are_you_sure_you_want_to_delete' => __('Are you sure you want to delete?', 'kboard-comments'),
			'please_wait' => __('Please wait.', 'kboard-comments'),
			'email' => __('Email', 'kboard-comments'),
			'address' => __('Address', 'kboard-comments'),
			'postcode' => __('Postcode', 'kboard-comments'),
			'phone_number' => __('Phone number', 'kboard-comments'),
			'find' => __('Find', 'kboard-comments'),
			'rate' => __('Rate', 'kboard-comments'),
			'ratings' => __('Ratings', 'kboard-comments'),
			'waiting' => __('Waiting', 'kboard-comments'),
			'complete' => __('Complete', 'kboard-comments'),
			'question' => __('Question', 'kboard-comments'),
			'answer' => __('Answer', 'kboard-comments'),
			'notify_me_of_new_comments_via_email' => __('Notify me of new comments via email', 'kboard-comments'),
	);
	wp_localize_script('kboard-script', 'kboard_comments_localize_strings', $localize);
}

/*
 * 댓글 스킨에서 로그인 메시지 출력
 */
add_action('kboard_comments_login_content', 'kboard_comments_login_content', 10, 3);
function kboard_comments_login_content($board, $content_uid, $comment_builder){
	echo sprintf(__('You must be <a href="%s">logged in</a> to post a comment.', 'kboard-comments'), wp_login_url($_SERVER['REQUEST_URI']));
}

/*
 * 댓글 스킨에서 입력 필드 출력
 */
add_action('kboard_comments_field', 'kboard_comments_field', 10, 4);
function kboard_comments_field($field_html, $board, $content_uid, $comment_builder){
	echo $field_html;
}

/*
 * 언어 파일 추가
 */
add_action('init', 'kboard_comments_languages');
function kboard_comments_languages(){
	load_plugin_textdomain('kboard-comments', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

/*
 * 관리자 알림 출력
 */
add_action('admin_notices', 'kboard_comments_admin_notices');
function kboard_comments_admin_notices(){
	if(current_user_can('activate_plugins')){
		$upgrader = KBUpgrader::getInstance();
		if(version_compare(KBOARD_COMMNETS_VERSION, $upgrader->getLatestVersion()->comments, '<')){
			echo '<div class="notice notice-info is-dismissible"><p>KBoard 댓글 : ' . $upgrader->getLatestVersion()->comments . ' 버전으로 업그레이드가 가능합니다. - <a href="'.admin_url('/admin.php?page=kboard_dashboard').'">대시보드로 이동</a> 또는 <a href="http://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href);return false;">홈페이지 열기</a></p></div>';
		}
	}
}

/*
 * 스타일 파일을 출력한다.
 */
add_action('wp_enqueue_scripts', 'kboard_comments_style', 999);
add_action('kboard_switch_to_blog', 'kboard_comments_style');
function kboard_comments_style(){
	$skin = KBCommentSkin::getInstance();
	foreach($skin->getActiveList() as $skin_name){
		wp_enqueue_style("kboard-comments-skin-{$skin_name}", $skin->url($skin_name, 'style.css'), array(), KBOARD_COMMNETS_VERSION);
	}
}

/*
 * 시스템 업데이트
 */
add_action('plugins_loaded', 'kboard_comments_update_check');
function kboard_comments_update_check(){
	global $wpdb;
	
	// 시스템 업데이트를 이미 진행 했다면 중단한다.
	if(version_compare(KBOARD_COMMNETS_VERSION, get_option('kboard_comments_version'), '<=')) return;
	
	// 시스템 업데이트를 확인하기 위해서 버전 등록
	if(get_option('kboard_comments_version') !== false){
		update_option('kboard_comments_version', KBOARD_COMMNETS_VERSION);
		
		// 관리자 알림
		add_action('admin_notices', create_function('', "echo '<div class=\"updated\"><p>KBoard 댓글 : '.KBOARD_COMMNETS_VERSION.' 버전으로 업데이트 되었습니다. - <a href=\"http://www.cosmosfarm.com/products/kboard\" onclick=\"window.open(this.href);return false;\">홈페이지 열기</a></p></div>';"));
	}
	else{
		add_option('kboard_comments_version', KBOARD_COMMNETS_VERSION, null, 'no');
	}
	
	kboard_comments_activation_execute();
}
} // KBoard 게시판 플러그인이 활성화 돼 있어야 동작하는 구간 완료

/*
 * 활성화
 */
register_activation_hook(__FILE__, 'kboard_comments_activation');
function kboard_comments_activation($networkwide){
	global $wpdb;
	if(function_exists('is_multisite') && is_multisite()){
		if($networkwide){
			$old_blog = $wpdb->blogid;
			$blogids = $wpdb->get_col("SELECT `blog_id` FROM {$wpdb->blogs}");
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
	
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset_collate = $wpdb->get_charset_collate();
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_comments` (
	`uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`content_uid` bigint(20) unsigned NOT NULL,
	`parent_uid` bigint(20) unsigned DEFAULT NULL,
	`user_uid` bigint(20) unsigned DEFAULT NULL,
	`user_display` varchar(127) DEFAULT NULL,
	`content` longtext NOT NULL,
	`like` int(10) unsigned DEFAULT NULL,
	`unlike` int(10) unsigned DEFAULT NULL,
	`vote` int(11) DEFAULT NULL,
	`created` char(14) NOT NULL,
	`status` varchar(20) DEFAULT NULL,
	`password` varchar(127) DEFAULT NULL,
	PRIMARY KEY (`uid`),
	KEY `content_uid` (`content_uid`),
	KEY `parent_uid` (`parent_uid`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_comments_option` (
	`uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`comment_uid` bigint(20) unsigned NOT NULL,
	`option_key` varchar(127) NOT NULL,
	`option_value` text NOT NULL,
	PRIMARY KEY (`uid`),
	UNIQUE KEY `comment_uid` (`comment_uid`,`option_key`),
	KEY `option_key` (`option_key`)
	) {$charset_collate};");
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
		$blogids = $wpdb->get_col("SELECT `blog_id` FROM {$wpdb->blogs}");
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
	$wpdb->query("DROP TABLE
	`{$wpdb->prefix}kboard_comments`,
	`{$wpdb->prefix}kboard_comments_option`
	");
}
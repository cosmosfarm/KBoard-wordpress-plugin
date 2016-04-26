<?php
/*
Plugin Name: KBoard : 댓글
Plugin URI: http://www.cosmosfarm.com/products/kboard
Description: 워드프레스 KBoard 댓글 플러그인 입니다.
Version: 4.3
Author: 코스모스팜 - Cosmosfarm
Author URI: http://www.cosmosfarm.com/
*/

if(!defined('ABSPATH')) exit;
if(!function_exists('is_plugin_active') || !function_exists('is_plugin_active_for_network')) require_once(ABSPATH . '/wp-admin/includes/plugin.php');
if(is_plugin_active('kboard/index.php') || is_plugin_active_for_network('kboard/index.php')){

define('KBOARD_COMMNETS_VERSION', '4.3');
define('KBOARD_COMMENTS_PAGE_TITLE', __('KBoard : 댓글', 'kboard-comments'));
define('KBOARD_COMMENTS_DIR_PATH', dirname(__FILE__));
define('KBOARD_COMMENTS_URL_PATH', plugins_url('', __FILE__));
define('KBOARD_COMMENTS_LIST_PAGE', admin_url('admin.php?page=kboard_comments_list'));

include_once 'class/KBComment.class.php';
include_once 'class/KBCommentController.class.php';
include_once 'class/KBCommentList.class.php';
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
}

/*
 * 관리자메뉴에 추가
 */
function kboard_comments_settings_menu(){
	add_submenu_page('kboard_dashboard', KBOARD_COMMENTS_PAGE_TITLE, __('전체 댓글', 'kboard-comments'), 'administrator', 'kboard_comments_list', 'kboard_comments_list');
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
		foreach($_POST['comment_uid'] as $key=>$value){
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
	$commentBuilder->board = $atts['board'];
	$commentBuilder->board_id = $atts['board_id'];
	$commentBuilder->content_uid = $atts['content_uid'];
	$commentBuilder->permission_comment_write = $atts['permission_comment_write'];
	$commentBuilder->setSkin($atts['skin']);
	return $commentBuilder->create();
}

/*
 * 댓글 스크립트 추가
 */
add_action('wp_enqueue_scripts', 'kboard_comments_scripts', 999);
function kboard_comments_scripts(){
	wp_enqueue_script('jquery');
	
	// 번역 등록
	$localize = array(
			'reply' => __('Reply', 'kboard-comments'),
			'cancel' => __('Cancel', 'kboard-comments'),
			'please_enter_the_author' => __('Please enter the author.', 'kboard-comments'),
			'please_enter_the_password' => __('Please enter the password.', 'kboard-comments'),
			'please_enter_the_CAPTCHA' => __('Please enter the CAPTCHA.', 'kboard-comments'),
			'please_enter_the_content' => __('Please enter the content.', 'kboard-comments'),
			'are_you_sure_you_want_to_delete' => __('Are you sure you want to delete?', 'kboard-comments'),
	);
	wp_localize_script('jquery', 'kboard_comments_localize_strings', $localize);
}

/*
 * 언어 파일 추가
 */
add_action('plugins_loaded', 'kboard_comments_languages');
function kboard_comments_languages(){
	load_plugin_textdomain('kboard-comments', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

/*
 * 관리자 알림 출력
 */
add_action('admin_notices', 'kboard_comments_admin_notices');
function kboard_comments_admin_notices(){
	if(current_user_can('activate_plugins')){
		$upgrader = KBUpgrader::getInstance();
		if(KBOARD_COMMNETS_VERSION < $upgrader->getLatestVersion()->comments){
			echo '<div class="updated"><p>KBoard 댓글 : ' . $upgrader->getLatestVersion()->comments . ' 버전으로 업그레이드가 가능합니다. - <a href="'.admin_url('/admin.php?page=kboard_dashboard').'">대시보드로 이동</a> 또는 <a href="http://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href);return false;">홈페이지 열기</a></p></div>';
		}
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
			wp_enqueue_style("kboard-comments-skin-{$row->value}", KBOARD_COMMENTS_URL_PATH . "/skin/{$row->value}/style.css", array(), KBOARD_COMMNETS_VERSION);
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
		if(file_exists(KBOARD_COMMENTS_DIR_PATH . "/skin/{$row->value}/functions.php")) include_once KBOARD_COMMENTS_DIR_PATH . "/skin/{$row->value}/functions.php";
	}
}

/*
 * 시스템 업데이트
 */
add_action('admin_init', 'kboard_comments_system_update');
function kboard_comments_system_update(){
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
	 * kboard_comments 테이블에 parent_uid 컬럼 생성 확인
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_comments` `parent_uid`", ARRAY_N);
	if(!$name){
		kboard_comments_activation($networkwide);
		return;
	}
	unset($name);
	
	/*
	 * KBoard 댓글 4.2
	 * kboard_comments 테이블에 like 컬럼 생성 확인
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_comments` `like`", ARRAY_N);
	if(!$name){
		kboard_comments_activation($networkwide);
		return;
	}
	unset($name);
	
	/*
	 * KBoard 댓글 4.2
	 * kboard_comments 테이블에 unlike 컬럼 생성 확인
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_comments` `unlike`", ARRAY_N);
	if(!$name){
		kboard_comments_activation($networkwide);
		return;
	}
	unset($name);
	
	/*
	 * KBoard 댓글 4.2
	 * kboard_comments 테이블에 vote 컬럼 생성 확인
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_comments` `vote`", ARRAY_N);
	if(!$name){
		kboard_comments_activation($networkwide);
		return;
	}
	unset($name);
	
	/*
	 * KBoard 댓글 4.2
	 * kboard_comments 테이블의 인덱스 생성 확인
	 */
	$content_uid_index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='content_uid'");
	$parent_uid_index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='parent_uid'");
	if(!count($content_uid_index) || !count($parent_uid_index)){
		kboard_activation($networkwide);
		return;
	}
	unset($index);
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
	
	$wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}kboard_comments` (
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
		`password` varchar(127) default NULL,
		PRIMARY KEY (`uid`),
		KEY `content_uid` (`content_uid`),
		KEY `parent_uid` (`parent_uid`)
	) DEFAULT CHARSET=utf8");
	
	/*
	 * KBoard 댓글 3.2
	 * kboard_comments 테이블에 parent_uid 컬럼 추가
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_comments` `parent_uid`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_comments` ADD `parent_uid` bigint(20) unsigned DEFAULT NULL AFTER `content_uid`");
	}
	unset($name);
	
	/*
	 * KBoard 댓글 4.2
	 * kboard_comments 테이블에 like 컬럼 추가
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_comments` `like`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_comments` ADD `like` int(10) UNSIGNED NULL AFTER `content`");
	}
	unset($name);
	
	/*
	 * KBoard 댓글 4.2
	 * kboard_comments 테이블에 unlike 컬럼 추가
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_comments` `unlike`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_comments` ADD `unlike` int(10) UNSIGNED NULL AFTER `like`");
	}
	unset($name);
	
	/*
	 * KBoard 댓글 4.2
	 * kboard_comments 테이블에 vote 컬럼 추가
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_comments` `vote`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_comments` ADD `vote` int(11) NULL AFTER `unlike`");
	}
	unset($name);

	/*
	 * KBoard 댓글 4.2
	 * kboard_comments 테이블에 content_uid 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_comments` WHERE `Key_name`='content_uid'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_comments` ADD INDEX (`content_uid`)");
	}
	unset($index);

	/*
	 * KBoard 댓글 4.2
	 * kboard_comments 테이블에 parent_uid 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_comments` WHERE `Key_name`='parent_uid'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_comments` ADD INDEX (`parent_uid`)");
	}
	unset($index);
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
	$wpdb->query("DROP TABLE `{$wpdb->prefix}kboard_comments`");
}
?>
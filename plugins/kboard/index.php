<?php
/*
Plugin Name: KBoard : 워드프레스 게시판
Plugin URI: http://www.cosmosfarm.com/
Description: 워드프레스 게시판 플러그인
Version: 2.0
Author: Cosmosfarm
Author URI: http://www.cosmosfarm.com/
*/

define('KBOARD_VERSION', '2.0');
define('KBOARD_WORDPRESS_ROOT', substr(ABSPATH, 0, -1));

if(!session_id()) session_start();

include_once 'KBoard.class.php';
include_once 'Content.class.php';
include_once 'ContentList.class.php';
include_once 'Url.class.php';
include_once 'KBoardSkin.class.php';
include_once 'KBoardMeta.class.php';
include_once 'KBMail.class.php';
include_once 'BoardBuilder.class.php';
include_once 'Pagination.helper.php';
include_once 'Security.helper.php';

define('KBOARD_PAGE_TITLE', 'KBoard : 워드프레스 게시판');
define('KBOARD_DIR_PATH', str_replace(DIRECTORY_SEPARATOR . 'index.php', '', __FILE__));
define('KBOARD_URL_PATH', plugins_url('kboard'));
define('KBOARD_LIST_PAGE', admin_url('/admin.php?page=kboard_list'));
define('KBOARD_NEW_PAGE', admin_url('/admin.php?page=kboard_new'));
define('KBOARD_SETTING_PAGE', admin_url('/admin.php?page=kboard_setting'));
define('KBOARD_UPDATE_ACTION', admin_url('/admin.php?page=kboard_update'));

/*
 * jQuery를 추가한다.
 */
wp_enqueue_script('jquery');

/*
 * 플러그인 페이지 링크
 */
add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), 'kboard_settings_link');
function kboard_settings_link($links){
	return array_merge($links, array('settings' => '<a href="'.KBOARD_NEW_PAGE.'">게시판 생성</a>'));
}

/*
 * 관리자메뉴에 추가
 */
add_action('admin_menu', 'kboard_settings_menu');
function kboard_settings_menu(){
	add_menu_page(KBOARD_PAGE_TITLE, 'KBoard', 'administrator', 'kboard_list', 'kboard_list', '', '55.1');
	
	add_submenu_page('kboard_list', KBOARD_PAGE_TITLE, '게시판 생성', 'administrator', 'kboard_new', 'kboard_new');
	if($_GET['board_id']) add_submenu_page('kboard_list', KBOARD_PAGE_TITLE, '게시판 정보 수정', 'administrator', 'kboard_setting', 'kboard_setting');
	add_submenu_page('kboard_new', KBOARD_PAGE_TITLE, '게시판 업데이트', 'administrator', 'kboard_update', 'kboard_update');
	
	// 댓글 플러그인 활성화면 댓글 리스트 페이지를 보여준다.
	if(defined('KBOARD_COMMNETS_VERSION') && KBOARD_COMMNETS_VERSION >= '1.3') add_submenu_page('kboard_list', KBOARD_COMMENTS_PAGE_TITLE, '전체 댓글', 'administrator', 'kboard_comments_list', 'kboard_comments_list');
}

/*
 * 게시판 목록 페이지
 */
function kboard_list(){
	kboard_system_update();
	
	$action = $_POST['action'];
	$action2 = $_POST['action2'];
	if(($action=='remove' || $action2=='remove') && $_POST['board_id']){
		$board = new KBoard();
		foreach($_POST['board_id'] AS $key => $value){
			$board->remove($value);
		}
	}
	
	$board = new KBoard();
	$board->getList();
	include_once 'pages/kboard_list.php';
}

/*
 * 새로운 게시판 생성
 */
function kboard_new(){
	kboard_system_update();
	
	$skin = KBoardSkin::getInstance();
	include_once 'pages/kboard_setting.php';
}

/*
 * 게시판 목록 페이지
 */
function kboard_setting(){
	kboard_system_update();
	
	if(!$_GET['board_id']){
		echo '<script>location.href="' . KBOARD_LIST_PAGE . '"</script>';
		exit;
	}
	
	$board = new KBoard();
	$board->setID($_GET['board_id']);
	$skin = KBoardSkin::getInstance();
	$meta = new KBoardMeta($board->uid);
	
	include_once 'pages/kboard_setting.php';
}

/*
 * 게시판 정보 업데이트
 */
function kboard_update(){
	global $wpdb;
	
	if(!defined('KBOARD_COMMNETS_VERSION')){
		echo '<script>alert("게시판 생성 실패!\nKBoard 댓글 플러그인을 설치해주세요.\nhttp://www.cosmosfarm.com/ 에서 다운로드 가능합니다.");history.go(-1);</script>';
		exit;
	}
	
	$board_id = $_POST['board_id'];
	
	$board_name = addslashes($_POST['board_name']);
	$skin = $_POST['skin'];
	$page_rpp = $_POST['page_rpp'];
	$use_comment = $_POST['use_comment'];
	$use_editor = $_POST['use_editor'];
	$permission_read = $_POST['permission_read'];
	$permission_write = $_POST['permission_write'];
	$admin_user = addslashes($_POST['admin_user']);
	$use_category = $_POST['use_category'];
	$category1_list = addslashes($_POST['category1_list']);
	$category2_list = addslashes($_POST['category2_list']);
	$create = date("YmdHis", current_time('timestamp'));
	
	if(!$board_id){
		$wpdb->query("INSERT INTO kboard_board_setting (board_name, skin, page_rpp, use_comment, use_editor, permission_read, permission_write, admin_user, use_category, category1_list, category2_list, created) VALUE ('$board_name', '$skin', '$page_rpp', '$use_comment', '$use_editor', '$permission_read', '$permission_write', '$admin_user', '$use_category', '$category1_list', '$category2_list', '$create')");
		$board_id = mysql_insert_id();
	}
	else{
		$wpdb->query("UPDATE kboard_board_setting SET board_name='$board_name', skin='$skin', page_rpp='$page_rpp', use_comment='$use_comment', use_editor='$use_editor', permission_read='$permission_read', permission_write='$permission_write', use_category='$use_category', category1_list='$category1_list', category2_list='$category2_list', admin_user='$admin_user' WHERE uid=$board_id");
	}
	
	if($board_id){
		$meta = new KBoardMeta($board_id);
		$meta->latest_alerts = $_POST['latest_alerts'];
	}
	
	echo '<script>location.href="' . KBOARD_SETTING_PAGE . '&board_id=' . $board_id . '"</script>';
}

/*
 * 최신 게시물 추출 단축코드
 */
add_shortcode('kboard_latest', 'kboard_latest');
function kboard_latest($args){
	if(!$args['id']) return 'KBoard 알림 :: id=null, 아이디값은 필수 입니다.';
	else if(!$args['url']) return 'KBoard 알림 :: url=null, 게시판 페이지 주소는 필수 입니다.';
	
	if(!$args['rpp']) $args['rpp'] = 5;
	
	$board = new KBoard();
	$board->setID($args['id']);
	
	if($board->uid){
		$board_builder = new BoardBuilder();
		$board_builder->setBoardID($board->uid);
		$board_builder->setSkin($board->skin);
		$board_builder->setRpp($args['rpp']);
		$board_builder->setURL($args['url']);
		$board_builder->board = $board;
		$kboard_latest = $board_builder->createLatest();
		return $kboard_latest;
	}
	else{
		return 'KBoard 알림 :: id='.$args['id'].', 생성되지 않은 게시판입니다.';
	}
}

/*
 * 페이지 표시 단축코드
 */
add_shortcode('kboard', 'kboard_builder');
function kboard_builder($args){
	if(!$args['id']) return 'KBoard 알림 :: id=null, 아이디값은 필수 입니다.';
	
	$board = new KBoard();
	$board->setID($args['id']);
	
	if($board->uid){
		$board_builder = new BoardBuilder();
		$board_builder->setBoardID($board->uid);
		$board_builder->setSkin($board->skin);
		$board_builder->setRpp($board->page_rpp);
		$board_builder->board = $board;
		$kboard = $board_builder->create();
		return $kboard;
	}
	else{
		return 'KBoard 알림 :: id='.$args['id'].', 생성되지 않은 게시판입니다.';
	}
}

/*
 * KBoard RSS 피드 
 */
add_action('wp_head', 'kboard_rss');
function kboard_rss(){
	$name = get_bloginfo('name');
	echo '<link rel="alternate" href="'.plugins_url().'/kboard/rss.php" type="application/rss+xml" title="'.$name.' &raquo; KBoard 통합 피드">';
}

/*
 * 활성화
 */
register_activation_hook(__FILE__, 'kboard_activation');
function kboard_activation(){
	global $wpdb;
	
	$kboard_board_setting = "CREATE TABLE IF NOT EXISTS `kboard_board_setting` (
	  `uid` bigint(20) unsigned NOT NULL auto_increment,
	  `board_name` varchar(127) NOT NULL,
	  `skin` varchar(127) NOT NULL,
	  `use_comment` varchar(5) NOT NULL,
	  `use_editor` varchar(5) NOT NULL,
	  `permission_read` varchar(127) NOT NULL,
	  `permission_write` varchar(127) NOT NULL,
	  `admin_user` varchar(127) NOT NULL,
	  `use_category` varchar(5) NOT NULL,
	  `category1_list` varchar(127) NOT NULL,
	  `category2_list` varchar(127) NOT NULL,
	  `page_rpp` int(10) unsigned NOT NULL,
	  `created` char(14) NOT NULL,
	  PRIMARY KEY  (`uid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	$wpdb->query($kboard_board_setting);
	
	$kboard_board_attached = "CREATE TABLE IF NOT EXISTS `kboard_board_attached` (
	  `uid` bigint(20) unsigned NOT NULL auto_increment,
	  `content_uid` bigint(20) unsigned NOT NULL,
	  `file_key` varchar(127) NOT NULL,
	  `date` char(14) NOT NULL,
	  `file_path` varchar(127) NOT NULL,
	  `file_name` varchar(127) NOT NULL,
	  PRIMARY KEY  (`uid`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	$wpdb->query($kboard_board_attached);
	
	$kboard_board_content = "CREATE TABLE IF NOT EXISTS `kboard_board_content` (
	  `uid` bigint(20) unsigned NOT NULL auto_increment,
	  `board_id` bigint(20) unsigned NOT NULL,
	  `member_uid` bigint(20) unsigned NOT NULL,
	  `member_display` varchar(127) NOT NULL,
	  `title` varchar(127) NOT NULL,
	  `content` text NOT NULL,
	  `date` char(14) NOT NULL,
	  `view` int(10) unsigned NOT NULL,
	  `thumbnail_file` varchar(127) NOT NULL,
	  `thumbnail_name` varchar(127) NOT NULL,
	  `category1` varchar(127) NOT NULL,
	  `category2` varchar(127) NOT NULL,
	  `secret` varchar(5) NOT NULL,
	  `notice` varchar(5) NOT NULL,
	  `password` varchar(127) NOT NULL,
	  PRIMARY KEY  (`uid`),
	  KEY `board_id` (`board_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	$wpdb->query($kboard_board_content);
	
	$kboard_board_option = "CREATE TABLE IF NOT EXISTS `kboard_board_option` (
	  `uid` bigint(20) unsigned NOT NULL auto_increment,
	  `content_uid` bigint(20) unsigned NOT NULL,
	  `option_key` varchar(127) NOT NULL,
	  `option_value` text NOT NULL,
	  PRIMARY KEY  (`uid`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	$wpdb->query($kboard_board_option);
	
	$kboard_board_meta = "CREATE TABLE IF NOT EXISTS `kboard_board_meta` (
	  `board_id` bigint(20) unsigned NOT NULL,
	  `key` varchar(127) NOT NULL,
	  `value` varchar(127) NOT NULL,
	  UNIQUE KEY `meta_index` (`board_id`,`key`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8";
	$wpdb->query($kboard_board_meta);
}

/*
 * 비활성화
 */
register_deactivation_hook(__FILE__, 'kboard_deactivation');
function kboard_deactivation(){
	
}

/*
 * 언인스톨
 */
register_uninstall_hook(__FILE__, 'kboard_uninstall');
function kboard_uninstall(){
	global $wpdb;
	
	$drop_table = "DROP TABLE  `kboard_board_attached` ,
		`kboard_board_content` ,
		`kboard_board_option` ,
		`kboard_board_setting` ,
		`kboard_board_meta`";
	$wpdb->query($drop_table);
}

function kboard_permission($permission){
	if($permission == 'all'){
		return '제한없음';
	}
	else if($permission == 'author'){
		return '로그인 사용자';
	}
	else if($permission == 'editor'){
		return '선택된 관리자';
	}
	else if($permission == 'administrator'){
		return '최고관리자';
	}
	else{
		return $permission;
	}
}

function kboard_system_update(){
	// KBoard 2.0에서 테이블 추가 생성 확인
	if(!mysql_query("SELECT 1 FROM `kboard_board_meta`")) kboard_activation();
}
?>
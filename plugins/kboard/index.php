<?php
/*
Plugin Name: KBoard : 게시판
Plugin URI: http://www.cosmosfarm.com/products/kboard
Description: 워드프레스 KBoard 게시판 플러그인 입니다.
Version: 3.5
Author: Cosmosfarm
Author URI: http://www.cosmosfarm.com/
*/

if(!defined('ABSPATH')) exit;
if(!session_id()) session_start();

define('KBOARD_VERSION', '3.5');

include_once 'KBoard.class.php';
include_once 'Content.class.php';
include_once 'ContentList.class.php';
include_once 'Url.class.php';
include_once 'KBoardMeta.class.php';
include_once 'KBRouter.class.php';
include_once 'KBSeo.class.php';
include_once 'KBUpgrader.class.php';
include_once 'BoardBuilder.class.php';
include_once 'Pagination.helper.php';
include_once 'Security.helper.php';

define('KBOARD_PAGE_TITLE', 'KBoard : 게시판');
define('KBOARD_WORDPRESS_ROOT', substr(ABSPATH, 0, -1));
define('KBOARD_WORDPRESS_APP_ID', '083d136637c09572c3039778d8667b27');
define('KBOARD_DB_PREFIX', $wpdb->prefix);
define('KBOARD_DIR_PATH', str_replace(DIRECTORY_SEPARATOR . 'index.php', '', __FILE__));
define('KBOARD_URL_PATH', plugins_url('kboard'));
define('KBOARD_DASHBOARD_PAGE', admin_url('/admin.php?page=kboard_dashboard'));
define('KBOARD_LIST_PAGE', admin_url('/admin.php?page=kboard_list'));
define('KBOARD_NEW_PAGE', admin_url('/admin.php?page=kboard_new'));
define('KBOARD_SETTING_PAGE', admin_url('/admin.php?page=kboard_list'));
define('KBOARD_BACKUP_PAGE', admin_url('/admin.php?page=kboard_backup'));
define('KBOARD_BACKUP_ACTION', plugins_url('/execute/backup.php', __FILE__));
define('KBOARD_UPDATE_ACTION', admin_url('/admin.php?page=kboard_update'));
define('KBOARD_UPGRADE_ACTION', admin_url('/admin.php?page=kboard_upgrade'));

/*
 * jQuery를 추가한다.
 */
wp_enqueue_script('jquery');

/*
 * KBoard Router
 */
add_action('init', 'kboard_router');
function kboard_router(){
	$router = new KBRouter();
	$router->process();
}

/*
 * 워드프레스 검색용 필터 입력
 */
add_filter('pre_get_posts', 'kboard_search_filter');
function kboard_search_filter($query){
	$post_type = get_query_var('post_type');
	if($query->is_search){
		$query->set('post_type', array('post', 'page', 'attachment', 'kboard'));
	};
	return $query;
}

/*
 * 플러그인 페이지 링크
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'kboard_settings_link');
function kboard_settings_link($links){
	return array_merge($links, array('settings' => '<a href="'.KBOARD_NEW_PAGE.'">게시판 생성</a>'));
}

/*
 * 워드프레스 관리자 웰컴 패널에 KBoard 패널을 추가한다.
 */
add_action('welcome_panel', 'kboard_welcome_panel');
function kboard_welcome_panel(){
	echo '<script>jQuery(document).ready(function($){$("div.welcome-panel-content").eq(0).hide();});</script>';
	$upgrader = KBUpgrader::getInstance();
	include_once 'pages/welcome.php';
}

/*
 * 관리자메뉴에 추가
 */
add_action('admin_menu', 'kboard_settings_menu');
function kboard_settings_menu(){
	$position = 50.5;
	while($GLOBALS['menu'][$position]) $position++;
	
	add_menu_page(KBOARD_PAGE_TITLE, 'KBoard', 'administrator', 'kboard_dashboard', 'kboard_dashboard', plugins_url('kboard/images/icon.png'), $position);
	add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, '대시보드', 'administrator', 'kboard_dashboard');
	add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, '게시판 목록', 'administrator', 'kboard_list', 'kboard_list');
	add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, '게시판 생성', 'administrator', 'kboard_new', 'kboard_new');
	add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, '백업 및 복구', 'administrator', 'kboard_backup', 'kboard_backup');
	
	// 표시되지 않는 페이지
	add_submenu_page('kboard_new', KBOARD_PAGE_TITLE, '게시판 수정', 'administrator', 'kboard_update', 'kboard_update');
	add_submenu_page('kboard_new', KBOARD_PAGE_TITLE, '게시판 업그레이드', 'administrator', 'kboard_upgrade', 'kboard_upgrade');
	
	// 댓글 플러그인 활성화면 댓글 리스트 페이지를 보여준다.
	if(defined('KBOARD_COMMNETS_VERSION') && KBOARD_COMMNETS_VERSION >= '1.3') add_submenu_page('kboard_dashboard', KBOARD_COMMENTS_PAGE_TITLE, '전체 댓글', 'administrator', 'kboard_comments_list', 'kboard_comments_list');
}

/*
 * 게시판 대시보드 페이지
 */
function kboard_dashboard(){
	kboard_system_update();
	if($_GET['access_token']){
		$_SESSION['cosmosfarm_access_token'] = $_GET['access_token'];
	}
	$upgrader = KBUpgrader::getInstance();
	include_once 'pages/kboard_dashboard.php';
}

/*
 * 게시판 목록 페이지
 */
function kboard_list(){
	kboard_system_update();
	if($_GET['board_id']){
		kboard_setting();
	}
	else{
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
		$meta = new KBoardMeta();
		
		include_once 'pages/kboard_list.php';
	}
}

/*
 * 새로운 게시판 생성
 */
function kboard_new(){
	kboard_system_update();
	include 'KBoardSkin.class.php';
	$skin = KBoardSkin::getInstance();
	include_once 'pages/kboard_setting.php';
}

/*
 * 게시판 목록 페이지
 */
function kboard_setting(){
	include 'KBoardSkin.class.php';
	include_once WP_CONTENT_DIR . '/plugins/kboard-comments/KBCommentSkin.class.php';
	$board = new KBoard();
	$board->setID($_GET['board_id']);
	$skin = KBoardSkin::getInstance();
	$meta = new KBoardMeta($board->uid);
	$comment_skin = KBCommentSkin::getInstance();
	include_once 'pages/kboard_setting.php';
}

/*
 * 게시판 백업 및 복구 페이지
 */
function kboard_backup(){
	include 'KBBackup.class.php';
	$backup = new KBBackup();
	
	if($_GET['action'] == 'upload'){
		$xmlfile = WP_CONTENT_DIR . '/uploads/' . basename($_FILES['kboard_backup_xml_file']['name']);
		if(move_uploaded_file($_FILES['kboard_backup_xml_file']['tmp_name'], $xmlfile)){
			$file_extension = explode('.', $xmlfile);
			if(end($file_extension) == 'xml'){
				$backup->importXml($xmlfile);
				echo '<script>alert("복원파일의 데이터로 복구 되었습니다.");</script>';
			}
			else{
				echo '<script>alert("복원에 실패 했습니다. 올바른 복원파일이 아닙니다.");</script>';
			}
			unlink($xmlfile);
		}
		else{
			echo '<script>alert("파일의 업로드를 실패 했습니다.");</script>';
		}
	}
	
	include_once 'pages/kboard_backup.php';
}

/*
 * 게시판 정보 수정
 */
function kboard_update(){
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
		kboard_query("INSERT INTO ".KBOARD_DB_PREFIX."kboard_board_setting (board_name, skin, page_rpp, use_comment, use_editor, permission_read, permission_write, admin_user, use_category, category1_list, category2_list, created) VALUE ('$board_name', '$skin', '$page_rpp', '$use_comment', '$use_editor', '$permission_read', '$permission_write', '$admin_user', '$use_category', '$category1_list', '$category2_list', '$create')");
		
		$insert_id = mysql_insert_id();
		if(!$insert_id) list($insert_id) = mysql_fetch_row(kboard_query("SELECT LAST_INSERT_ID()"));
		
		$board_id = $insert_id;
	}
	else{
		kboard_query("UPDATE ".KBOARD_DB_PREFIX."kboard_board_setting SET board_name='$board_name', skin='$skin', page_rpp='$page_rpp', use_comment='$use_comment', use_editor='$use_editor', permission_read='$permission_read', permission_write='$permission_write', use_category='$use_category', category1_list='$category1_list', category2_list='$category2_list', admin_user='$admin_user' WHERE uid=$board_id");
	}
	
	if($board_id){
		$meta = new KBoardMeta($board_id);
		$meta->latest_alerts = $_POST['latest_alerts'];
		$meta->comment_skin = $_POST['comment_skin'];
		$meta->default_content = kboard_xssfilter($_POST['default_content']);
		$meta->pass_autop = $_POST['pass_autop'];
		
		$auto_page = $_POST['auto_page'];
		if($auto_page){
			$row = mysql_fetch_row(kboard_query("SELECT board_id FROM ".KBOARD_DB_PREFIX."kboard_board_meta WHERE `key`='auto_page' AND `value`='$auto_page'"));
			$auto_page_board_id = @reset($row);
			if($auto_page_board_id && $auto_page_board_id != $board_id) echo '<script>alert("선택하신 페이지에 이미 연결된 게시판이 존재합니다.")</script>';
			else $meta->auto_page = $auto_page;
		}
		else{
			$meta->auto_page = '';
		}
	}
	
	echo '<script>location.href="' . KBOARD_SETTING_PAGE . '&board_id=' . $board_id . '"</script>';
}

/*
 * 게시판 업그레이드
 */
function kboard_upgrade(){
	if(!current_user_can('activate_plugins')) wp_die('KBoard : 업그레이드 권한이 없습니다.');
	$upgrader = KBUpgrader::getInstance();
	
	if($_GET['action'] == 'kboard'){
		if($upgrader->getLatestVersion()->kboard <= KBOARD_VERSION){
			echo '<script>alert("최신버전 입니다.");location.href="' . KBOARD_DASHBOARD_PAGE . '"</script>';
			exit;
		}
		$download_file = $upgrader->download(KBUpgrader::$CONNECT_KBOARD, $upgrader->getLatestVersion()->kboard, $_SESSION['cosmosfarm_access_token']);
		$working_dir = $upgrader->install($download_file, KBUpgrader::$TYPE_PLUGINS);
	}
	else if($_GET['action'] == 'comments'){
		if(defined('KBOARD_COMMNETS_VERSION')){
			if($upgrader->getLatestVersion()->comments <= KBOARD_COMMNETS_VERSION){
				echo '<script>alert("최신버전 입니다.");location.href="' . KBOARD_DASHBOARD_PAGE . '"</script>';
				exit;
			}
		}
		$download_file = $upgrader->download(KBUpgrader::$CONNECT_COMMENTS, $upgrader->getLatestVersion()->comments, $_SESSION['cosmosfarm_access_token']);
		$working_dir = $upgrader->install($download_file, KBUpgrader::$TYPE_PLUGINS);
	}
	else{
		echo '<script>alert("업그레이드에 실패 했습니다.");location.href="' . KBOARD_DASHBOARD_PAGE . '"</script>';
		exit;
	}
	
	echo '<script>alert("업그레이드 되었습니다.");location.href="' . KBOARD_DASHBOARD_PAGE . '"</script>';
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
 * 선택된 페이지에 자동으로 게시판 생성
 */
add_filter('the_content', 'kboard_auto_builder');
function kboard_auto_builder($content){
	global $post;
	if(is_page($post->ID)){
		$board_id = @reset(mysql_fetch_row(kboard_query("SELECT board_id FROM ".KBOARD_DB_PREFIX."kboard_board_meta WHERE `key`='auto_page' AND `value`='$post->ID'")));
		if($board_id) return $content . kboard_builder(array('id'=>$board_id));
	}
	return $content;
}

/*
 * KBoard SEO를 적용한다.
 */
add_action('plugins_loaded', 'kboard_seo', 1);
function kboard_seo(){
	if(!is_admin()){
		$seo = new KBSeo();
	}
}

/*
 * 활성화
 */
register_activation_hook(__FILE__, 'kboard_activation');
function kboard_activation(){
	global $wpdb;
	
	/*
	 * KBoard 2.5
	 * table 이름에 prefix 추가
	 */
	$resource = kboard_query('SHOW TABLES');
	while(list($table) = mysql_fetch_row($resource)){
		$prefix = substr($table, 0, 7);
		if($prefix == 'kboard_'){
			kboard_query("RENAME TABLE `$table` TO `".$wpdb->prefix.$table."`");
		}
	}
	unset($resource, $table, $prefix);
	
	$kboard_board_setting = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."kboard_board_setting` (
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
	kboard_query($kboard_board_setting);
	
	$kboard_board_attached = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."kboard_board_attached` (
	  `uid` bigint(20) unsigned NOT NULL auto_increment,
	  `content_uid` bigint(20) unsigned NOT NULL,
	  `file_key` varchar(127) NOT NULL,
	  `date` char(14) NOT NULL,
	  `file_path` varchar(127) NOT NULL,
	  `file_name` varchar(127) NOT NULL,
	  PRIMARY KEY  (`uid`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	kboard_query($kboard_board_attached);
	
	$kboard_board_content = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."kboard_board_content` (
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
	  `search` char(1) NOT NULL,
	  `password` varchar(127) NOT NULL,
	  PRIMARY KEY  (`uid`),
	  KEY `board_id` (`board_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	kboard_query($kboard_board_content);
	
	$kboard_board_option = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."kboard_board_option` (
	  `uid` bigint(20) unsigned NOT NULL auto_increment,
	  `content_uid` bigint(20) unsigned NOT NULL,
	  `option_key` varchar(127) NOT NULL,
	  `option_value` text NOT NULL,
	  PRIMARY KEY  (`uid`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	kboard_query($kboard_board_option);
	
	$kboard_board_meta = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."kboard_board_meta` (
	  `board_id` bigint(20) unsigned NOT NULL,
	  `key` varchar(127) NOT NULL,
	  `value` text NOT NULL,
	  UNIQUE KEY `meta_index` (`board_id`,`key`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8";
	kboard_query($kboard_board_meta);
	
	/*
	 * KBoard 2.9
	 * kboard_board_meta `value` 데이터형 text로 변경
	 */
	$resource = kboard_query("DESCRIBE `".$wpdb->prefix."kboard_board_meta` `value`");
	list($name, $type) = mysql_fetch_row($resource);
	if(stristr($type, 'varchar')){
		kboard_query("ALTER TABLE `".$wpdb->prefix."kboard_board_meta` CHANGE `value` `value` text NOT NULL");
	}
	unset($resource, $name, $type);
	
	/*
	 * KBoard 3.5
	 * kboard_board_content `search` 컬럼 생성 확인
	 */
	$resource = kboard_query("DESCRIBE `".$wpdb->prefix."kboard_board_content` `search`");
	list($name) = mysql_fetch_row($resource);
	if(!$name){
		kboard_query("ALTER TABLE `".$wpdb->prefix."kboard_board_content` ADD `search` CHAR(1) NOT NULL AFTER `notice`");
	}
	unset($resource, $name);
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
	$drop_table = "DROP TABLE  `".$wpdb->prefix."kboard_board_attached` ,
		`".$wpdb->prefix."kboard_board_content` ,
		`".$wpdb->prefix."kboard_board_option` ,
		`".$wpdb->prefix."kboard_board_setting` ,
		`".$wpdb->prefix."kboard_board_meta`";
	mysql_query($drop_table);
}

/*
 * 쿼리
 */
function kboard_query($query){
	$resource = mysql_query($query);
	if(mysql_errno()){
		$error = 'MySQL 메시지 ' . mysql_errno() . ":<br>\n<b>" . mysql_error() . "</b><br>\n SQL 질의:<br>\n<b>" . $query . "</b><br>\n" . '이 오류 내용을 코스모스팜 스레드(<a href="http://www.cosmosfarm.com/threads" onclick="window.open(this.href); return false;">http://www.cosmosfarm.com/threads</a>)에 알려주세요. 개인정보는 지워주세요.';
		die($error);
	}
	return $resource;
}

/*
 * 권한 한글 출력
 */
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

/*
 * Captcha 이미지
 */
function kboard_captcha(){
	include_once 'KBCaptcha.class.php';
	$captcha = new KBCaptcha();
	return $captcha->createImage();
}

/*
 * 업데이트
 */
function kboard_system_update(){
	/*
	 * KBoard 2.0
	 * kboard_board_meta 테이블 추가 생성
	 */
	if(!mysql_query("SELECT 1 FROM `".KBOARD_DB_PREFIX."kboard_board_meta`")){
		kboard_activation();
		return;
	}
	
	/*
	 * KBoard 2.5
	 * table 이름에 prefix 추가
	 */
	$resource = kboard_query('SHOW TABLES');
	while(list($table) = mysql_fetch_row($resource)){
		$prefix = substr($table, 0, 7);
		if($prefix == 'kboard_'){
			kboard_activation();
			return;
		}
	}
	unset($resource, $table, $prefix);
	
	/*
	 * KBoard 2.9
	 * kboard_board_meta `value` 데이터형 text로 변경
	 */
	$resource = kboard_query("DESCRIBE `".KBOARD_DB_PREFIX."kboard_board_meta` `value`");
	list($name, $type) = mysql_fetch_row($resource);
	if(stristr($type, 'varchar')){
		kboard_activation();
		return;
	}
	unset($resource, $name, $type);
	
	/*
	 * KBoard 3.2
	 * captcha.php 파일 제거
	 */
	@unlink(KBOARD_DIR_PATH . '/execute/captcha.php');
	
	/*
	 * KBoard 3.5
	 * kboard_board_content `search` 컬럼 생성 확인
	 */
	$resource = kboard_query("DESCRIBE `".KBOARD_DB_PREFIX."kboard_board_content` `search`");
	list($name) = mysql_fetch_row($resource);
	if(!$name){
		kboard_activation();
		return;
	}
	unset($resource, $name);
}
?>
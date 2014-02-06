<?php
/*
Plugin Name: KBoard : 게시판
Plugin URI: http://www.cosmosfarm.com/products/kboard
Description: 워드프레스 KBoard 게시판 플러그인 입니다.
Version: 4.3
Author: Cosmosfarm
Author URI: http://www.cosmosfarm.com/
*/

if(!defined('ABSPATH')) exit;
if(!session_id()) session_start();

define('KBOARD_VERSION', '4.3');
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
define('KBOARD_LATESTVIEW_PAGE', admin_url('/admin.php?page=kboard_latestview'));
define('KBOARD_LATESTVIEW_NEW_PAGE', admin_url('/admin.php?page=kboard_latestview_new'));
define('KBOARD_BACKUP_PAGE', admin_url('/admin.php?page=kboard_backup'));
define('KBOARD_BACKUP_ACTION', plugins_url('/execute/backup.php', __FILE__));
define('KBOARD_UPDATE_ACTION', admin_url('/admin.php?page=kboard_update'));
define('KBOARD_UPGRADE_ACTION', admin_url('/admin.php?page=kboard_upgrade'));
define('KBOARD_LATESTVIEW_ACTION', admin_url('/admin.php?page=kboard_latestview_update'));

include_once 'class/KBoardBuilder.class.php';
include_once 'class/KBContent.class.php';
include_once 'class/KBContentList.class.php';
include_once 'class/KBoard.class.php';
include_once 'class/KBoardMeta.class.php';
include_once 'class/KBoardSkin.class.php';
include_once 'class/KBSeo.class.php';
include_once 'class/KBStore.class.php';
include_once 'class/KBUrl.class.php';
include_once 'class/KBUpgrader.class.php';
include_once 'class/KBRouter.class.php';
include_once 'class/KBLatestview.class.php';
include_once 'class/KBLatestviewList.class.php';
include_once 'class/KBFileHandler.class.php';
include_once 'helper/Pagination.helper.php';
include_once 'helper/Security.helper.php';
include_once 'helper/Functions.helper.php';

/*
 * jQuery 추가
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
	
	// KBoard 메뉴 등록
	add_menu_page(KBOARD_PAGE_TITLE, 'KBoard', 'administrator', 'kboard_dashboard', 'kboard_dashboard', plugins_url('kboard/images/icon.png'), $position);
	add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, '대시보드', 'administrator', 'kboard_dashboard');
	add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, '게시판 목록', 'administrator', 'kboard_list', 'kboard_list');
	add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, '게시판 생성', 'administrator', 'kboard_new', 'kboard_new');
	add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, '최신글 뷰 목록', 'administrator', 'kboard_latestview', 'kboard_latestview');
	add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, '최신글 뷰 생성', 'administrator', 'kboard_latestview_new', 'kboard_latestview_new');
	add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, '백업 및 복구', 'administrator', 'kboard_backup', 'kboard_backup');
	
	// 표시되지 않는 페이지
	add_submenu_page('kboard_new', KBOARD_PAGE_TITLE, '게시판 수정', 'administrator', 'kboard_update', 'kboard_update');
	add_submenu_page('kboard_new', KBOARD_PAGE_TITLE, '최신글 뷰 수정', 'administrator', 'kboard_latestview_update', 'kboard_latestview_update');
	add_submenu_page('kboard_new', KBOARD_PAGE_TITLE, '게시판 업그레이드', 'administrator', 'kboard_upgrade', 'kboard_upgrade');
	
	// 스토어 메뉴 등록
	while($GLOBALS['menu'][$position]) $position++;
	add_menu_page('스토어', '스토어', 'administrator', 'kboard_store', 'kboard_store', plugins_url('kboard/images/icon.png'), $position);
	add_submenu_page('kboard_store', '스토어', '스토어', 'administrator', 'kboard_store');
	
	// 댓글 플러그인 활성화면 댓글 리스트 페이지를 보여준다.
	if(defined('KBOARD_COMMNETS_VERSION') && KBOARD_COMMNETS_VERSION >= '1.3' && KBOARD_COMMNETS_VERSION < '3.3') add_submenu_page('kboard_dashboard', KBOARD_COMMENTS_PAGE_TITLE, '전체 댓글', 'administrator', 'kboard_comments_list', 'kboard_comments_list');
	else if(defined('KBOARD_COMMNETS_VERSION') && KBOARD_COMMNETS_VERSION >= '3.3') kboard_comments_settings_menu();
}

/*
 * 스토어 상품 리스트 페이지
 */
function kboard_store(){
	if($_GET['access_token']) $_SESSION['cosmosfarm_access_token'] = $_GET['access_token'];
	KBStore::productsList();
}

/*
 * 게시판 대시보드 페이지
 */
function kboard_dashboard(){
	if($_GET['access_token']) $_SESSION['cosmosfarm_access_token'] = $_GET['access_token'];
	$upgrader = KBUpgrader::getInstance();
	include_once 'pages/kboard_dashboard.php';
}

/*
 * 게시판 목록 페이지
 */
function kboard_list(){
	if($_GET['board_id']){
		kboard_setting();
	}
	else{
		$board = new KBoard();
		$action = $_POST['action'];
		$action2 = $_POST['action2'];
		if(($action=='remove' || $action2=='remove') && $_POST['board_id']){			
			foreach($_POST['board_id'] AS $key => $value){
				$board->remove($value);
			}
		}
		$board->getList();
		$meta = new KBoardMeta();
		include_once 'pages/kboard_list.php';
	}
}

/*
 * 새로운 게시판 생성
 */
function kboard_new(){
	$skin = KBoardSkin::getInstance();
	include_once 'pages/kboard_setting.php';
}

/*
 * 게시판 목록 페이지
 */
function kboard_setting(){
	include_once WP_CONTENT_DIR.'/plugins/kboard-comments/class/KBCommentSkin.class.php';
	$board = new KBoard();
	$board->setID($_GET['board_id']);
	$skin = KBoardSkin::getInstance();
	$meta = new KBoardMeta($board->uid);
	$comment_skin = KBCommentSkin::getInstance();
	include_once 'pages/kboard_setting.php';
}

/*
 * 게시판 정보 수정
 */
function kboard_update(){
	if(!defined('KBOARD_COMMNETS_VERSION')){
		die('<script>alert("게시판 생성 실패!\nKBoard 댓글 플러그인을 설치해주세요.\nhttp://www.cosmosfarm.com/ 에서 다운로드 가능합니다.");history.go(-1);</script>');
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
		$meta->use_direct_url = $_POST['use_direct_url'];
		$meta->latest_alerts = $_POST['latest_alerts'];
		$meta->comment_skin = $_POST['comment_skin'];
		$meta->default_content = $_POST['default_content'];
		$meta->pass_autop = $_POST['pass_autop'];
		$meta->shortcode_execute = $_POST['shortcode_execute'];
		$meta->autolink = $_POST['autolink'];
		
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
	
	die('<script>location.href="' . KBOARD_SETTING_PAGE . '&board_id=' . $board_id . '"</script>');
}

/*
 * 최신글 뷰
 */
function kboard_latestview(){
	if($_GET['latestview_uid']){
		$skin = KBoardSkin::getInstance();
		$latestview = new KBLatestview();
		$latestview->initWithUID($_GET['latestview_uid']);
		$linkedBoard = $latestview->getLinkedBoard();
		$board = new KBoard();
		include_once 'pages/kboard_latestview_setting.php';
	}
	else{
		$action = $_POST['action'];
		$action2 = $_POST['action2'];
		if(($action=='remove' || $action2=='remove') && $_POST['latestview_uid']){
			$latestview = new KBLatestview();
			foreach($_POST['latestview_uid'] AS $key => $uid){
				$latestview->initWithUID($uid);
				$latestview->delete();
			}
		}
		
		$latestviewList = new KBLatestviewList();
		$latestviewList->init();
		include_once 'pages/kboard_latestview.php';
	}
}

/*
 * 최신글 뷰 생성
 */
function kboard_latestview_new(){
	$skin = KBoardSkin::getInstance();
	include_once 'pages/kboard_latestview_setting.php';
}

/*
 * 최신글 뷰 수정
 */
function kboard_latestview_update(){
	if(!defined('KBOARD_COMMNETS_VERSION')){
		die('<script>alert("게시판 생성 실패!\nKBoard 댓글 플러그인을 설치해주세요.\nhttp://www.cosmosfarm.com/ 에서 다운로드 가능합니다.");history.go(-1);</script>');
	}
	
	$latestview_uid = $_POST['latestview_uid'];
	$latestview_link = $_POST['latestview_link'];
	$latestview_unlink = $_POST['latestview_unlink'];
	$name = $_POST['name'];
	$skin = $_POST['skin'];
	$rpp = $_POST['rpp'];
	
	$latestview = new KBLatestview();
	if($latestview_uid) $latestview->initWithUID($latestview_uid);
	else $latestview->create();
	
	$latestview->name = $name;
	$latestview->skin = $skin;
	$latestview->rpp = $rpp;
	$latestview->update();
	
	$latestview_link = explode(',', $latestview_link);
	if(is_array($latestview_link)){
		foreach($latestview_link AS $key => $value){
			$value = intval($value);
			if($value) $latestview->pushBoard($value);
		}
	}
	
	$latestview_unlink = explode(',', $latestview_unlink);
	if(is_array($latestview_unlink)){
		foreach($latestview_unlink AS $key => $value){
			$value = intval($value);
			if($value) $latestview->popBoard($value);
		}
	}
	
	die('<script>location.href="' . KBOARD_LATESTVIEW_PAGE . '&latestview_uid=' . $latestview_uid . '"</script>');
}

/*
 * 게시판 백업 및 복구 페이지
 */
function kboard_backup(){
	include 'class/KBBackup.class.php';
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
 * 게시판 업그레이드
 */
function kboard_upgrade(){
	if(!current_user_can('activate_plugins')) wp_die('KBoard : 설치 권한이 없습니다.');
	
	$action = kboard_htmlclear($_GET['action']);
	$download_url = kboard_htmlclear($_GET['download_url']);
	$download_version = kboard_htmlclear($_GET['download_version']);
	$form_url = wp_nonce_url(admin_url("/admin.php?page=kboard_upgrade&action=$action" . ($download_url?"&download_url=$download_url":'') . ($download_version?"&download_version=$download_version":'')), 'kboard_upgrade');
	$upgrader = KBUpgrader::getInstance();
	
	if($action == 'kboard'){
		if($upgrader->getLatestVersion()->kboard <= KBOARD_VERSION){
			die('<script>alert("최신버전 입니다.");location.href="' . KBOARD_DASHBOARD_PAGE . '"</script>');
		}
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_PLUGINS)) exit;
		$download_file = $upgrader->download(KBUpgrader::$CONNECT_KBOARD, $upgrader->getLatestVersion()->kboard, $_SESSION['cosmosfarm_access_token']);
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_PLUGINS);
		die('<script>alert("완료 되었습니다.");location.href="' . KBOARD_DASHBOARD_PAGE . '"</script>');
	}
	else if($action == 'comments'){
		if(defined('KBOARD_COMMNETS_VERSION')){
			if($upgrader->getLatestVersion()->comments <= KBOARD_COMMNETS_VERSION){
				die('<script>alert("최신버전 입니다.");location.href="' . KBOARD_DASHBOARD_PAGE . '"</script>');
			}
		}
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_PLUGINS)) exit;
		$download_file = $upgrader->download(KBUpgrader::$CONNECT_COMMENTS, $upgrader->getLatestVersion()->comments, $_SESSION['cosmosfarm_access_token']);
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_PLUGINS);
		die('<script>alert("완료 되었습니다.");location.href="' . KBOARD_DASHBOARD_PAGE . '"</script>');
	}
	else if($action == 'plugin'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_PLUGINS)) exit;
		$download_file = $upgrader->download($download_url, $download_version, $_SESSION['cosmosfarm_access_token']);
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_PLUGINS);
		die('<script>alert("완료 되었습니다.");location.href="' . admin_url('/plugins.php') . '"</script>');
	}
	else if($action == 'theme'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_THEMES)) exit;
		$download_file = $upgrader->download($download_url, $download_version, $_SESSION['cosmosfarm_access_token']);
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_THEMES);
		die('<script>alert("완료 되었습니다.");location.href="' . admin_url('/themes.php') . '"</script>');
	}
	else if($action == 'kboard-skin'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_KBOARD_SKIN)) exit;
		$download_file = $upgrader->download($download_url, $download_version, $_SESSION['cosmosfarm_access_token']);
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_KBOARD_SKIN);
		die('<script>alert("완료 되었습니다.");location.href="' . admin_url('/admin.php?page=kboard_store') . '"</script>');
	}
	else if($action == 'comments-skin'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_COMMENTS_SKIN)) exit;
		$download_file = $upgrader->download($download_url, $download_version, $_SESSION['cosmosfarm_access_token']);
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_COMMENTS_SKIN);
		die('<script>alert("완료 되었습니다.");location.href="' . admin_url('/admin.php?page=kboard_store') . '"</script>');
	}
	else{
		die('<script>alert("설치에 실패 했습니다.");location.href="' . KBOARD_DASHBOARD_PAGE . '"</script>');
	}
}

/*
 * 게시판 생성 숏코드
 */
add_shortcode('kboard', 'kboard_builder');
function kboard_builder($args){
	if(!$args['id']) return 'KBoard 알림 :: id=null, 아이디값은 필수 입니다.';
	
	$board = new KBoard();
	$board->setID($args['id']);
	
	if($board->uid){
		$board_builder = new KBoardBuilder();
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
		$resource = kboard_query("SELECT board_id FROM ".KBOARD_DB_PREFIX."kboard_board_meta WHERE `key`='auto_page' AND `value`='$post->ID'");
		list($board_id) = mysql_fetch_row($resource);
		if($board_id) return $content . kboard_builder(array('id'=>$board_id));
	}
	return $content;
}

/*
 * 최신글 생성 숏코드
 */
add_shortcode('kboard_latest', 'kboard_latest_shortcode');
function kboard_latest_shortcode($args){
	if(!$args['id']) return 'KBoard 알림 :: id=null, 아이디값은 필수 입니다.';
	else if(!$args['url']) return 'KBoard 알림 :: url=null, 페이지 주소는 필수 입니다.';
	if(!$args['rpp']) $args['rpp'] = 5;
	
	$board = new KBoard();
	$board->setID($args['id']);
	
	if($board->uid){
		$board_builder = new KBoardBuilder();
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
 * 최신글 뷰 생성 숏코드
 */
add_shortcode('kboard_latestview', 'kboard_latestview_shortcode');
function kboard_latestview_shortcode($args){
	if(!$args['id']) return 'KBoard 알림 :: id=null, 아이디값은 필수 입니다.';
	
	$latestview = new KBLatestview($args['id']);
	if($latestview->uid){
		$board_builder = new KBoardBuilder();
		$board_builder->setBoardID($latestview->getLinkedBoard());
		$board_builder->setSkin($latestview->skin);
		$board_builder->setRpp($latestview->rpp);
		$kboard_latest = $board_builder->createLatest();
		return $kboard_latest;
	}
	else{
		return 'KBoard 알림 :: id='.$args['id'].', 생성되지 않은 최신글 뷰 입니다.';
	}
}

/*
 * KBoard SEO를 적용한다.
 */
add_action('plugins_loaded', 'kboard_seo', 1);
function kboard_seo(){
	if(!is_admin()) $seo = new KBSeo();
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
	include_once 'class/KBCaptcha.class.php';
	$captcha = new KBCaptcha();
	return $captcha->createImage();
}

/*
 * 언어 파일 추가
 */
add_action('plugins_loaded', 'kboard_languages');
function kboard_languages(){
	$domain = 'kboard';
	$locale = apply_filters('plugin_locale', get_locale(), $domain);
	load_plugin_textdomain($domain, false, dirname(plugin_basename(__FILE__)).'/languages/');
}

/*
 * 비동기로 게시판 데이터를 요청한다.
 */
add_action('init', 'kboard_ajax');
function kboard_ajax(){
	add_action('wp_ajax_kboard_ajax_builder', 'kboard_ajax_builder');
	add_action('wp_ajax_nopriv_kboard_ajax_builder', 'kboard_ajax_builder');
}

/*
 * 비동기 게시판 빌더
 */
function kboard_ajax_builder(){
	if(!$_SESSION['kboard_board_id']) die('KBoard 알림 :: id=null, 아이디값은 필수 입니다.');
	
	$board = new KBoard();
	$board->setID($_SESSION['kboard_board_id']);
	
	if($board->uid){
		$board_builder = new KBoardBuilder();
		$board_builder->setBoardID($board->uid);
		$board_builder->setSkin($board->skin);
		$board_builder->setRpp($board->page_rpp);
		$board_builder->board = $board;
		die($board_builder->getJsonList());
	}
	else{
		die('KBoard 알림 :: id='.$_SESSION['kboard_board_id'].', 생성되지 않은 게시판입니다.');
	}
}

/*
 * 관리자 알림 출력
 */
add_action('admin_notices', 'kboard_admin_notices');
function kboard_admin_notices(){
	if(!is_writable(WP_CONTENT_DIR.'/uploads')){
		echo '<div class="error"><p>KBoard 게시판 : 디렉토리 '.WP_CONTENT_DIR.'/uploads'.'에 파일을 쓸 수 없습니다. 디렉토리가 존재하지 않거나 쓰기 권한이 있는지 확인해주세요. - <a href="http://www.cosmosfarm.com/threads" onclick="window.open(this.href); return false;">이 알림에 대해서 질문하기</a></p></div>';
	}
	
	$upgrader = KBUpgrader::getInstance();
	if(KBOARD_VERSION < $upgrader->getLatestVersion()->kboard){
		echo '<div class="updated"><p>KBoard 게시판 : '.$upgrader->getLatestVersion()->kboard.' 버전으로 업그레이드가 가능합니다. - <a href="'.admin_url('/admin.php?page=kboard_dashboard').'">대시보드로 이동</a> 또는 <a href="http://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href); return false;">홈페이지 열기</a></p></div>';
	}
}

/*
 * 스타일 파일을 출력한다.
 */
add_action('init', 'kboard_style');
function kboard_style(){
	global $wp_styles;
	wp_enqueue_style("font-awesome", KBOARD_URL_PATH.'/font-awesome/css/font-awesome.min.css');
	wp_enqueue_style("font-awesome-ie7", KBOARD_URL_PATH.'/font-awesome/css/font-awesome-ie7.min.css');
	$wp_styles->add_data('font-awesome-ie7', 'conditional', 'lte IE 7');
	
	$skin = KBoardSkin::getInstance();
	foreach($skin->list AS $key => $value){
		wp_enqueue_style("kboard-skin-{$value}", KBOARD_URL_PATH.'/skin/'.$value.'/style.css');
	}
	
	if(is_admin()){
		wp_enqueue_style("kboard-store", KBOARD_URL_PATH.'/pages/kboard-store.css');
	}
}

/*
 * 활성화
 */
register_activation_hook(__FILE__, 'kboard_activation');
function kboard_activation($networkwide){
	global $wpdb;
	
	if(function_exists('is_multisite') && is_multisite()){
		if($networkwide){
			$old_blog = $wpdb->blogid;
			$blogids = $wpdb->get_col("SELECT `blog_id` FROM $wpdb->blogs");
			foreach($blogids as $blog_id){
				switch_to_blog($blog_id);
				kboard_activation_execute();
			}
			switch_to_blog($old_blog);
			return;
		}
	}
	kboard_activation_execute();
}

/*
 * 활성화 실행
 */
function kboard_activation_execute(){
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
	) CHARSET=utf8";
	kboard_query($kboard_board_setting);
	
	$kboard_board_attached = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."kboard_board_attached` (
		`uid` bigint(20) unsigned NOT NULL auto_increment,
		`content_uid` bigint(20) unsigned NOT NULL,
		`file_key` varchar(127) NOT NULL,
		`date` char(14) NOT NULL,
		`file_path` varchar(127) NOT NULL,
		`file_name` varchar(127) NOT NULL,
		PRIMARY KEY  (`uid`)
	) CHARSET=utf8";
	kboard_query($kboard_board_attached);
	
	$kboard_board_content = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."kboard_board_content` (
		`uid` bigint(20) unsigned NOT NULL auto_increment,
		`board_id` bigint(20) unsigned NOT NULL,
		`parent_uid` bigint(20) unsigned NOT NULL,
		`member_uid` bigint(20) unsigned NOT NULL,
		`member_display` varchar(127) NOT NULL,
		`title` varchar(127) NOT NULL,
		`content` text NOT NULL,
		`date` char(14) NOT NULL,
		`view` int(10) unsigned NOT NULL,
		`comment` int(10) unsigned NOT NULL,
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
	) CHARSET=utf8";
	kboard_query($kboard_board_content);
	
	$kboard_board_option = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."kboard_board_option` (
		`uid` bigint(20) unsigned NOT NULL auto_increment,
		`content_uid` bigint(20) unsigned NOT NULL,
		`option_key` varchar(127) NOT NULL,
		`option_value` text NOT NULL,
		PRIMARY KEY  (`uid`)
	) CHARSET=utf8";
	kboard_query($kboard_board_option);
	
	$kboard_board_meta = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."kboard_board_meta` (
		`board_id` bigint(20) unsigned NOT NULL,
		`key` varchar(127) NOT NULL,
		`value` text NOT NULL,
		UNIQUE KEY `meta_index` (`board_id`,`key`)
	) CHARSET=utf8";
	kboard_query($kboard_board_meta);
	
	$kboard_board_latestview = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."kboard_board_latestview` (
		`uid` bigint(20) unsigned NOT NULL auto_increment,
		`name` varchar(127) NOT NULL,
		`skin` varchar(127) NOT NULL,
		`rpp` int(10) unsigned NOT NULL,
		`created` char(14) NOT NULL,
		PRIMARY KEY  (`uid`)
	) CHARSET=utf8";
	kboard_query($kboard_board_latestview);
	
	$kboard_board_latestview = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."kboard_board_latestview_link` (
		`latestview_uid` bigint(20) unsigned NOT NULL,
		`board_id` bigint(20) unsigned NOT NULL,
		UNIQUE KEY `latestview_uid` (`latestview_uid`,`board_id`)
	) CHARSET=utf8";
	kboard_query($kboard_board_latestview);
	
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
	
	/*
	 * KBoard 4.1
	 * kboard_board_content `comment` 컬럼 생성 확인
	 */
	$resource = kboard_query("DESCRIBE `".$wpdb->prefix."kboard_board_content` `comment`");
	list($name) = mysql_fetch_row($resource);
	if(!$name){
		kboard_query("ALTER TABLE `".$wpdb->prefix."kboard_board_content` ADD `comment` INT UNSIGNED NOT NULL AFTER `view`");
	}
	if(defined('KBOARD_COMMNETS_VERSION')){
		// comment 컬럼에 댓글 입력 숫자를 등록한다.
		$resource = kboard_query("SELECT `uid` FROM `".$wpdb->prefix."kboard_board_content` WHERE 1");
		while($row = mysql_fetch_row($resource)){
			list($count) = mysql_fetch_row(kboard_query("SELECT COUNT(*) FROM `".$wpdb->prefix."kboard_comments` WHERE `content_uid`='".intval($row[0])."'"));
			kboard_query("UPDATE `".$wpdb->prefix."kboard_board_content` SET `comment`='$count' WHERE `uid`='".intval($row[0])."'");
		}
	}
	unset($resource, $name, $count);
	
	/*
	 * KBoard 4.2
	 * kboard_board_content `parent_uid` 컬럼 생성 확인
	 */
	$resource = kboard_query("DESCRIBE `".$wpdb->prefix."kboard_board_content` `parent_uid`");
	list($name) = mysql_fetch_row($resource);
	if(!$name){
		kboard_query("ALTER TABLE `".$wpdb->prefix."kboard_board_content` ADD `parent_uid` BIGINT UNSIGNED NOT NULL AFTER `board_id`");
	}
	unset($resource, $name);
}

/*
 * 비활성화
 */
register_deactivation_hook(__FILE__, 'kboard_deactivation');
function kboard_deactivation($networkwide){
	
}

/*
 * 언인스톨
 */
register_uninstall_hook(__FILE__, 'kboard_uninstall');
function kboard_uninstall(){
	global $wpdb;
	
	if(function_exists('is_multisite') && is_multisite()){
		$old_blog = $wpdb->blogid;
		$blogids = $wpdb->get_col("SELECT `blog_id` FROM $wpdb->blogs");
		foreach($blogids as $blog_id){
			switch_to_blog($blog_id);
			kboard_uninstall_execute();
		}
		switch_to_blog($old_blog);
		return;
	}
	kboard_uninstall_execute();
}

/*
 * 언인스톨 실행
 */
function kboard_uninstall_execute(){
	global $wpdb;
	
	$drop_table = "DROP TABLE 
		`".$wpdb->prefix."kboard_board_attached`,
		`".$wpdb->prefix."kboard_board_content`,
		`".$wpdb->prefix."kboard_board_option`,
		`".$wpdb->prefix."kboard_board_setting`,
		`".$wpdb->prefix."kboard_board_meta`,
		`".$wpdb->prefix."kboard_board_latestview`,
		`".$wpdb->prefix."kboard_board_latestview_link`";
	mysql_query($drop_table);
}

/*
 * 시스템 업데이트
 */
add_action('admin_init', 'kboard_system_update');
function kboard_system_update(){
	// 시스템 업데이트를 이미 진행 했다면 중단한다.
	if(KBOARD_VERSION <= get_option('kboard_version')) return;
	
	// 시스템 업데이트를 확인하기 위해서 버전 등록
	if(get_option('kboard_version') !== false) update_option('kboard_version', KBOARD_VERSION);
	else add_option('kboard_version', KBOARD_VERSION, null, 'no');
	
	// 관리자 알림
	add_action('admin_notices', create_function('', "echo '<div class=\"updated\"><p>KBoard 게시판 : '.KBOARD_VERSION.' 버전으로 업그레이드 되었습니다. - <a href=\"http://www.cosmosfarm.com/products/kboard\" onclick=\"window.open(this.href); return false;\">홈페이지 열기</a></p></div>';"));
	
	$networkwide = is_plugin_active_for_network(__FILE__);
	
	/*
	 * KBoard 2.0
	 * kboard_board_meta 테이블 추가 생성
	 */
	if(!mysql_query("SELECT 1 FROM `".KBOARD_DB_PREFIX."kboard_board_meta`")){
		kboard_activation($networkwide);
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
			kboard_activation($networkwide);
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
		kboard_activation($networkwide);
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
	 * kboard_board_latestview, kboard_board_latestview_link 테이블 추가 생성
	 */
	$resource = kboard_query("DESCRIBE `".KBOARD_DB_PREFIX."kboard_board_content` `search`");
	list($name) = mysql_fetch_row($resource);
	if(!$name){
		kboard_activation($networkwide);
		return;
	}
	if(!mysql_query("SELECT 1 FROM `".KBOARD_DB_PREFIX."kboard_board_latestview`")){
		kboard_activation($networkwide);
		return;
	}
	unset($resource, $name);
	
	/*
	 * KBoard 3.5
	 * 파일 제거
	 */
	@unlink(KBOARD_DIR_PATH . '/BoardBuilder.class.php');
	@unlink(KBOARD_DIR_PATH . '/Content.class.php');
	@unlink(KBOARD_DIR_PATH . '/ContentList.class.php');
	@unlink(KBOARD_DIR_PATH . '/KBBackup.class.php');
	@unlink(KBOARD_DIR_PATH . '/KBCaptcha.class.php');
	@unlink(KBOARD_DIR_PATH . '/KBFileHandler.class.php');
	@unlink(KBOARD_DIR_PATH . '/KBMail.class.php');
	@unlink(KBOARD_DIR_PATH . '/KBoard.class.php');
	@unlink(KBOARD_DIR_PATH . '/KBoardMeta.class.php');
	@unlink(KBOARD_DIR_PATH . '/KBoardSkin.class.php');
	@unlink(KBOARD_DIR_PATH . '/KBSeo.class.php');
	@unlink(KBOARD_DIR_PATH . '/KBUpgrader.class.php');
	@unlink(KBOARD_DIR_PATH . '/Pagination.helper.php');
	@unlink(KBOARD_DIR_PATH . '/Security.helper.php');
	@unlink(KBOARD_DIR_PATH . '/Url.class.php');
	@unlink(KBOARD_DIR_PATH . '/XML2Array.class.php');
	
	/*
	 * KBoard 4.1
	 * kboard_board_content `comment` 컬럼 생성 확인
	 */
	$resource = kboard_query("DESCRIBE `".KBOARD_DB_PREFIX."kboard_board_content` `comment`");
	list($name) = mysql_fetch_row($resource);
	if(!$name){
		kboard_activation($networkwide);
		return;
	}
	unset($resource, $name);
	
	/*
	 * KBoard 4.2
	 * kboard_board_content `parent_uid` 컬럼 생성 확인
	 */
	$resource = kboard_query("DESCRIBE `".KBOARD_DB_PREFIX."kboard_board_content` `parent_uid`");
	list($name) = mysql_fetch_row($resource);
	if(!$name){
		kboard_activation($networkwide);
		return;
	}
	unset($resource, $name);
}
?>
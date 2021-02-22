<?php
/*
Plugin Name: KBoard : 게시판
Plugin URI: https://www.cosmosfarm.com/products/kboard
Description: 워드프레스 KBoard 게시판 플러그인 입니다.
Version: 6.0
Author: 코스모스팜 - Cosmosfarm
Author URI: https://www.cosmosfarm.com/
*/

if(!defined('ABSPATH')) exit;

define('KBOARD_VERSION', '6.0');
define('KBOARD_PAGE_TITLE', __('KBoard : 게시판', 'kboard'));
define('KBOARD_WORDPRESS_ROOT', substr(ABSPATH, 0, -1));
define('KBOARD_WORDPRESS_APP_ID', '083d136637c09572c3039778d8667b27');
define('KBOARD_DIR_PATH', dirname(__FILE__));
define('KBOARD_URL_PATH', plugins_url('', __FILE__));
define('KBOARD_DASHBOARD_PAGE', admin_url('admin.php?page=kboard_dashboard'));
define('KBOARD_LIST_PAGE', admin_url('admin.php?page=kboard_list'));
define('KBOARD_NEW_PAGE', admin_url('admin.php?page=kboard_new'));
define('KBOARD_SETTING_PAGE', admin_url('admin.php?page=kboard_list'));
define('KBOARD_LATESTVIEW_PAGE', admin_url('admin.php?page=kboard_latestview'));
define('KBOARD_LATESTVIEW_NEW_PAGE', admin_url('admin.php?page=kboard_latestview_new'));
define('KBOARD_BACKUP_PAGE', admin_url('admin.php?page=kboard_backup'));
define('KBOARD_CONTENT_LIST_PAGE', admin_url('admin.php?page=kboard_content_list'));

if(!defined('KBOARD_STORE_AUTH')){
	define('KBOARD_STORE_AUTH', true);
}

if(!defined('KBOARD_CONNECT_COSMOSFARM')){
	define('KBOARD_CONNECT_COSMOSFARM', true);
}

include_once 'class/KBContent.class.php';
include_once 'class/KBContentList.class.php';
include_once 'class/KBContentMedia.class.php';
include_once 'class/KBCommentMedia.class.php';
include_once 'class/KBContentOption.class.php';
include_once 'class/KBController.class.php';
include_once 'class/KBoard.class.php';
include_once 'class/KBoardBuilder.class.php';
include_once 'class/KBoardFields.class.php';
include_once 'class/KBoardList.class.php';
include_once 'class/KBoardMeta.class.php';
include_once 'class/KBoardSkin.class.php';
include_once 'class/KBoardTreeCategory.class.php';
include_once 'class/KBOrder.class.php';
include_once 'class/KBOrderHistory.class.php';
include_once 'class/KBOrderItem.class.php';
include_once 'class/KBOrderSales.class.php';
include_once 'class/KBStore.class.php';
include_once 'class/KBTemplate.class.php';
include_once 'class/KBUrl.class.php';
include_once 'class/KBUpgrader.class.php';
include_once 'class/KBRouter.class.php';
include_once 'class/KBLatestview.class.php';
include_once 'class/KBLatestviewList.class.php';
include_once 'class/KBFileHandler.class.php';
include_once 'class/KBVote.class.php';
include_once 'helper/Pagination.helper.php';
include_once 'helper/Security.helper.php';
include_once 'helper/Functions.helper.php';

/*
 * 애드온 파일 로딩
 */
foreach(glob(KBOARD_DIR_PATH . '/addons/*.php') as $filename){
	include_once $filename;
}

add_action('plugins_loaded', 'kboard_plugins_loaded');
function kboard_plugins_loaded(){
	if(!session_id() && (!is_admin() || kboard_id()) && !wp_is_json_request()){
		session_start();
	}
	
	// 언어 파일 추가
	load_plugin_textdomain('kboard', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

/*
 * KBoard 게시판 시작
 */
add_action('init', 'kboard_init', 5);
function kboard_init(){
	
	// 스킨의 functions.php 파일을 실행한다.
	$skin = KBoardSkin::getInstance();
	foreach($skin->getActiveList() as $skin_name){
		$skin->loadFunctions($skin_name);
	}
	
	// 게시판 페이지 이동
	$router = new KBRouter();
	$router->process();
	
	// 컨트롤러 시작
	$controller = new KBController();
	
	// 템플릿 시작
	$template = new KBTemplate();
	$template->route();
	
	$kboard_list_sort = isset($_GET['kboard_list_sort']) ? sanitize_text_field($_GET['kboard_list_sort']) : '';
	$kboard_list_sort_remember = isset($_GET['kboard_list_sort_remember']) ? intval($_GET['kboard_list_sort_remember']) : '';
	if($kboard_list_sort && $kboard_list_sort_remember){
		if(!in_array($kboard_list_sort, kboard_list_sorting_types())){
			$kboard_list_sort = '';
		}
		
		if($kboard_list_sort){
			$_COOKIE["kboard_list_sort_{$kboard_list_sort_remember}"] = $kboard_list_sort;
			setcookie("kboard_list_sort_{$kboard_list_sort_remember}", $kboard_list_sort, strtotime('+1 year'), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
		}
		else{
			$_COOKIE["kboard_list_sort_{$kboard_list_sort_remember}"] = '';
			setcookie("kboard_list_sort_{$kboard_list_sort_remember}", '', strtotime('-1 year'), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
		}
	}
}

/*
 * 테마 헤더에 정보 출력
 */
add_action('get_header', 'kboard_get_header', 999);
function kboard_get_header(){
	
	// SEO 시작
	include_once 'class/KBSeo.class.php';
	$seo = new KBSeo();
}

/*
 * KBoard 관리자 페이지 기능 실행
 */
add_action('admin_init', 'kboard_admin_init');
function kboard_admin_init(){
	$page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
	
	// 게시판 관리
	if($page == 'kboard_list' && (!isset($_GET['board_id']) || !$_GET['board_id'])){
		$wp_list_table = _get_list_table('WP_Posts_List_Table');
		if(isset($_POST['board_id']) && $wp_list_table->current_action() == 'reset_total'){
			set_time_limit(0);
			ini_set('memory_limit', '-1');
			foreach($_POST['board_id'] as $key=>$board_id){
				$board = new KBoard($board_id);
				$board->resetTotal();
			}
		}
		else if(isset($_POST['board_id']) && $wp_list_table->current_action() == 'truncate'){
			set_time_limit(0);
			ini_set('memory_limit', '-1');
			foreach($_POST['board_id'] as $key=>$board_id){
				$board = new KBoard($board_id);
				$board->truncate();
			}
		}
		else if(isset($_POST['board_id']) && $wp_list_table->current_action() == 'delete'){
			set_time_limit(0);
			ini_set('memory_limit', '-1');
			foreach($_POST['board_id'] as $key=>$board_id){
				$board = new KBoard($board_id);
				$board->delete();
			}
		}
	}
	
	// 관리자 컨트롤러 시작
	include_once KBOARD_DIR_PATH . '/class/KBAdminController.class.php';
	new KBAdminController();
	
	// 사용자 프로필 필드 추가
	include_once KBOARD_DIR_PATH . '/class/KBUserProfileFields.class.php';
	new KBUserProfileFields();
	
	// 스토어 연동 액세스 토큰 설정
	if($page == 'kboard_store' && isset($_GET['access_token']) && $_GET['access_token']){
		$_COOKIE['kboard_access_token'] = sanitize_text_field($_GET['access_token']);
		setcookie('kboard_access_token', $_COOKIE['kboard_access_token'], 0, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
	}
}

/*
 * 비서(Bswer) 웹사이트로 이동
 */
//add_action('admin_init', 'kboard_admin_init_redirect');
function kboard_admin_init_redirect(){
	global $pagenow;
	if($pagenow == 'index.php'){
		if(isset($_GET['goto']) && $_GET['goto'] == 'bswer-home'){
			wp_redirect('https://www.bswer.com/?ref=wp_admin_to_bswer_home&utm_campaign=wp_admin_to_bswer_home&utm_source=wordpress&utm_medium=referral');
			exit;
		}
	}
	
	add_submenu_page('themes.php', __('Find theme', 'kboard'), __('Find theme', 'kboard'), 'manage_options', 'index.php?goto=bswer-home');
	add_submenu_page('plugins.php', __('Find plugin', 'kboard'), __('Find plugin', 'kboard'), 'manage_options', 'index.php?goto=bswer-home');
}

/*
 * 글쓰기 에디터에 미디어 추가하기 버튼을 추가한다.
 */
function kboard_editor_button($editor_id){
	echo ' <button type="button" class="button" onclick="kboard_editor_open_media()">'.__('KBoard Add Media', 'kboard').'</button> ';
}

/*
 * 글쓰기 에디터에 미디어 버튼을 등록한다.
 */
function kboard_register_media_button($buttons){
	array_push($buttons, 'kboard_media');
	return $buttons;
}

/*
 * 글쓰기 에디터에 미디어 버튼을 추가한다.
 */
function kboard_add_media_button($plugin_array){
	if(!is_admin() || kboard_view_iframe()){
		$plugin_array['kboard_media_button_script'] = plugins_url('/template/js/editor_media_button.js', __FILE__);
	}
	return $plugin_array;
}

/*
 * 플러그인 페이지 링크
 */
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'kboard_settings_link');
function kboard_settings_link($links){
	return array_merge($links, array('kboard-new'=>'<a href="'.admin_url('admin.php?page=kboard_new').'">'.__('New Forum', 'kboard').'</a>'));
}

/*
 * 워드프레스 관리자 웰컴 패널에 KBoard 패널을 추가한다.
 */
add_action('welcome_panel', 'kboard_welcome_panel');
function kboard_welcome_panel(){
	echo '<script>jQuery(document).ready(function(){jQuery("div.welcome-panel-content").eq(0).hide();});</script>';
	include_once 'pages/welcome.php';
}

/*
 * 관리자메뉴에 추가
 */
add_action('admin_menu', 'kboard_settings_menu');
function kboard_settings_menu(){
	global $wpdb, $_wp_last_object_menu;
	
	// KBoard 메뉴 등록
	$_wp_last_object_menu++;
	add_menu_page(KBOARD_PAGE_TITLE, 'KBoard', 'manage_options', 'kboard_dashboard', 'kboard_dashboard', plugins_url('kboard/images/icon.png'), $_wp_last_object_menu);
	add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, __('대시보드', 'kboard'), 'manage_options', 'kboard_dashboard');
	add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, __('게시판 목록 및 관리', 'kboard'), 'manage_options', 'kboard_list', 'kboard_list');
	add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, __('게시판 생성', 'kboard'), 'manage_options', 'kboard_new', 'kboard_new');
	add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, __('최신글 모아보기', 'kboard'), 'manage_options', 'kboard_latestview', 'kboard_latestview');
	add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, __('최신글 모아보기 생성', 'kboard'), 'manage_options', 'kboard_latestview_new', 'kboard_latestview_new');
	add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, __('백업 및 복구', 'kboard'), 'manage_options', 'kboard_backup', 'kboard_backup');
	
	if(KBOARD_CONNECT_COSMOSFARM){
		add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, __('업데이트', 'kboard'), 'manage_options', 'kboard_updates', 'kboard_updates');
		
		// 스토어 메뉴 등록
		$_wp_last_object_menu++;
		add_menu_page(__('스토어', 'kboard'), __('스토어', 'kboard'), 'manage_options', 'kboard_store', 'kboard_store', plugins_url('kboard/images/icon.png'), $_wp_last_object_menu);
		add_submenu_page('kboard_store', __('스토어', 'kboard'), __('스토어', 'kboard'), 'manage_options', 'kboard_store');
		add_submenu_page('kboard_store', __('Partners', 'kboard'), __('Partners', 'kboard'), 'manage_options', 'kboard_store_partners', 'kboard_store_partners');
	}
	
	add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, __('전체 게시글', 'kboard'), 'manage_options', 'kboard_content_list', 'kboard_content_list');
	
	// 댓글 플러그인 활성화면 댓글 리스트 페이지를 보여준다.
	if(defined('KBOARD_COMMNETS_VERSION') && KBOARD_COMMNETS_VERSION >= '1.3' && KBOARD_COMMNETS_VERSION < '3.3'){
		add_submenu_page('kboard_dashboard', KBOARD_COMMENTS_PAGE_TITLE, __('전체 댓글', 'kboard'), 'administrator', 'kboard_comments_list', 'kboard_comments_list');
	}
	else if(defined('KBOARD_COMMNETS_VERSION') && KBOARD_COMMNETS_VERSION >= '3.3'){
		kboard_comments_settings_menu();
	}
	
	// 메뉴 액션 실행
	do_action('kboard_admin_menu');
	
	$result = $wpdb->get_results("SELECT `meta`.`board_id`, `setting`.`board_name` FROM `{$wpdb->prefix}kboard_board_meta` AS `meta` LEFT JOIN `{$wpdb->prefix}kboard_board_setting` AS `setting` ON `meta`.`board_id`=`setting`.`uid` WHERE `meta`.`key`='add_menu_page'");
	foreach($result as $row){
		add_submenu_page('kboard_dashboard', KBOARD_PAGE_TITLE, $row->board_name, 'manage_options', "kboard_admin_view_{$row->board_id}", 'kboard_admin_view');
	}
}

/*
 * 스토어 상품 리스트 페이지
 */
function kboard_store(){
	KBStore::productsList();
}

/*
 * 파트너 페이지
 */
function kboard_store_partners(){
	$store_partners_list = get_transient('kboard_store_partners_list');
	
	if(!$store_partners_list){
		$response = wp_remote_get('http://updates.wp-kboard.com/v1/AUTH_3529e134-c9d7-4172-8338-f64309faa5e5/kboard/partners.json');
		
		if(!is_wp_error($response) && isset($response['body']) && $response['body']){
			$store_partners_list = json_decode($response['body']);
		}
		else{
			$store_partners_list = array();
		}
		
		set_transient('kboard_store_partners_list', $store_partners_list, 60*60);
	}
	
	include_once 'pages/kboard_store_partners.php';
}

/*
 * 게시판 대시보드 페이지
 */
function kboard_dashboard(){
	include_once 'pages/kboard_dashboard.php';
}

/*
 * 게시판 목록 페이지
 */
function kboard_list(){
	if(isset($_GET['board_id']) && $_GET['board_id']){
		kboard_setting();
	}
	else{
		include_once 'class/KBoardListTable.class.php';
		$table = new KBoardListTable();
		/*
		 if(isset($_POST['board_id']) && $table->current_action() == 'delete'){
		 foreach($_POST['board_id'] as $key=>$value){
		 $table->board->delete($value);
		 }
		 }
		 */
		$table->prepare_items();
		include_once 'pages/kboard_list.php';
	}
}

/*
 * 새로운 게시판 생성
 */
function kboard_new(){
	$board = new KBoard();
	$meta = $board->meta;
	$skin = KBoardSkin::getInstance();
	if(defined('KBOARD_COMMNETS_VERSION')){
		include_once WP_CONTENT_DIR.'/plugins/kboard-comments/class/KBCommentSkin.class.php';
		$comment_skin = KBCommentSkin::getInstance();
	}
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('jquery-timepicker', KBOARD_URL_PATH . '/template/js/jquery.timepicker.js', array(), KBOARD_VERSION);
	wp_enqueue_style('jquery-flick-style', KBOARD_URL_PATH.'/template/css/jquery-ui.css', array(), '1.12.1');
	wp_enqueue_style('jquery-timepicker', KBOARD_URL_PATH.'/template/css/jquery.timepicker.css', array(), KBOARD_VERSION);
	wp_enqueue_script('nested-sortable', KBOARD_URL_PATH . '/assets/nested-sortable/jquery.mjs.nestedSortable.js', array('jquery', 'jquery-ui-sortable'), '2.1a');
	include_once 'pages/kboard_setting.php';
}

/*
 * 게시판 목록 페이지
 */
function kboard_setting(){
	$board_id = isset($_GET['board_id'])?$_GET['board_id']:'';
	$board = new KBoard($board_id);
	$meta = $board->meta;
	$skin = KBoardSkin::getInstance();
	if(defined('KBOARD_COMMNETS_VERSION')){
		include_once WP_CONTENT_DIR.'/plugins/kboard-comments/class/KBCommentSkin.class.php';
		$comment_skin = KBCommentSkin::getInstance();
	}
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('jquery-timepicker', KBOARD_URL_PATH . '/template/js/jquery.timepicker.js', array(), KBOARD_VERSION);
	wp_enqueue_style('jquery-flick-style', KBOARD_URL_PATH.'/template/css/jquery-ui.css', array(), '1.12.1');
	wp_enqueue_style('jquery-timepicker', KBOARD_URL_PATH.'/template/css/jquery.timepicker.css', array(), KBOARD_VERSION);
	wp_enqueue_script('nested-sortable', KBOARD_URL_PATH . '/assets/nested-sortable/jquery.mjs.nestedSortable.js', array('jquery', 'jquery-ui-sortable'), '2.1a');
	include_once 'pages/kboard_setting.php';
}

/*
 * 최신글 뷰
 */
function kboard_latestview(){
	if(isset($_GET['latestview_uid']) && $_GET['latestview_uid']){
		$skin = KBoardSkin::getInstance();
		$latestview = new KBLatestview();
		$latestview->initWithUID($_GET['latestview_uid']);
		$linked_board = $latestview->getLinkedBoard();
		$board_list = new KBoardList();
		include_once 'pages/kboard_latestview_setting.php';
	}
	else{
		include_once 'class/KBLatestviewListTable.class.php';
		$table = new KBLatestviewListTable();
		if(isset($_POST['latestview_uid']) && $table->current_action() == 'delete'){
			$latestview = new KBLatestview();
			foreach($_POST['latestview_uid'] as $key=>$latestview_uid){
				$latestview->initWithUID($latestview_uid);
				$latestview->delete();
			}
		}
		$table->prepare_items();
		include_once 'pages/kboard_latestview.php';
	}
}

/*
 * 최신글 뷰 생성
 */
function kboard_latestview_new(){
	$skin = KBoardSkin::getInstance();
	$latestview = new KBLatestview();
	$linked_board = $latestview->getLinkedBoard();
	$board_list = new KBoardList();
	include_once 'pages/kboard_latestview_setting.php';
}

/*
 * 게시판 백업 및 복구 페이지
 */
function kboard_backup(){
	include_once 'pages/kboard_backup.php';
}

/*
 * 플러그인 업데이트 페이지
 */
function kboard_updates(){
	$action = isset($_GET['action'])?kboard_htmlclear($_GET['action']):'';
	$download_url = isset($_GET['download_url'])?kboard_htmlclear($_GET['download_url']):'';
	$download_version = isset($_GET['download_version'])?kboard_htmlclear($_GET['download_version']):'';
	$form_url = wp_nonce_url(admin_url("admin.php?page=kboard_updates&action={$action}" . ($download_url?"&download_url=$download_url":'') . ($download_version?"&download_version=$download_version":'')), 'kboard_updates');
	
	$upgrader = KBUpgrader::getInstance();
	
	if($action == 'kboard'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_PLUGINS)) exit;
		
		echo "<h1>KBoard 게시판 플러그인 설치</h1>";
		@ob_flush();
		@flush();
		
		echo "<p>다운로드 중입니다. 기다려주세요.</p>";
		@ob_flush();
		@flush();
		
		$download_file = $upgrader->getKBoard();
		echo "<p>다운로드를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_PLUGINS);
		echo "<p>설치를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p>KBoard 게시판 플러그인 설치가 완료 되었습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p><a href=\"" . admin_url('admin.php?page=kboard_updates') . "\" class=\"button\">업데이트 메뉴로 이동</a></p>";
		@ob_flush();
		@flush();
		
		exit;
	}
	if($action == 'kboard-noskins'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_PLUGINS)) exit;
		
		echo "<h1>KBoard 게시판 플러그인 설치</h1>";
		@ob_flush();
		@flush();
		
		echo "<p>다운로드 중입니다. 기다려주세요.</p>";
		@ob_flush();
		@flush();
		
		$download_file = $upgrader->getKBoardNoSkins();
		echo "<p>다운로드를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_PLUGINS);
		echo "<p>설치를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p>KBoard 게시판 플러그인 설치가 완료 되었습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p><a href=\"" . admin_url('admin.php?page=kboard_updates') . "\" class=\"button\">업데이트 메뉴로 이동</a></p>";
		@ob_flush();
		@flush();
		
		exit;
	}
	else if($action == 'comments'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_PLUGINS)) exit;
		
		echo "<h1>KBoard 댓글 플러그인 설치</h1>";
		@ob_flush();
		@flush();
		
		echo "<p>다운로드 중입니다. 기다려주세요.</p>";
		@ob_flush();
		@flush();
		
		$download_file = $upgrader->getComments();
		echo "<p>다운로드를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_PLUGINS);
		echo "<p>설치를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p>KBoard 댓글 플러그인 설치가 완료 되었습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p><a href=\"" . admin_url('admin.php?page=kboard_updates') . "\" class=\"button\">업데이트 메뉴로 이동</a></p>";
		@ob_flush();
		@flush();
		
		exit;
	}
	else if($action == 'comments-noskins'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_PLUGINS)) exit;
		
		echo "<h1>KBoard 댓글 플러그인 설치</h1>";
		@ob_flush();
		@flush();
		
		echo "<p>다운로드 중입니다. 기다려주세요.</p>";
		@ob_flush();
		@flush();
		
		$download_file = $upgrader->getCommentsNoSkins();
		echo "<p>다운로드를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_PLUGINS);
		echo "<p>설치를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p>KBoard 댓글 플러그인 설치가 완료 되었습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p><a href=\"" . admin_url('admin.php?page=kboard_updates') . "\" class=\"button\">업데이트 메뉴로 이동</a></p>";
		@ob_flush();
		@flush();
		
		exit;
	}
	else if($action == 'plugin'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_PLUGINS)) exit;
		
		echo "<h1>플러그인 설치</h1>";
		@ob_flush();
		@flush();
		
		echo "<p>다운로드 중입니다. 기다려주세요.</p>";
		@ob_flush();
		@flush();
		
		$download_file = $upgrader->download($download_url, $download_version, KBStore::getAccessToken());
		echo "<p>다운로드를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_PLUGINS);
		echo "<p>설치를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p>플러그인 설치가 완료 되었습니다. 플러그인을 활성화해주세요.</p>";
		@ob_flush();
		@flush();
		
		echo "<p><a href=\"" . admin_url('plugins.php') . "\" class=\"button\">플러그인 메뉴로 이동</a></p>";
		@ob_flush();
		@flush();
		
		exit;
	}
	else if($action == 'theme'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_THEMES)) exit;
		
		echo "<h1>테마 설치</h1>";
		@ob_flush();
		@flush();
		
		echo "<p>다운로드 중입니다. 기다려주세요.</p>";
		@ob_flush();
		@flush();
		
		$download_file = $upgrader->download($download_url, $download_version, KBStore::getAccessToken());
		echo "<p>다운로드를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_THEMES);
		echo "<p>설치를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p>테마 설치가 완료 되었습니다. 테마를 선택해주세요.</p>";
		@ob_flush();
		@flush();
		
		echo "<p><a href=\"" . admin_url('themes.php') . "\" class=\"button\">테마 메뉴로 이동</a></p>";
		@ob_flush();
		@flush();
		
		exit;
	}
	else if($action == 'kboard-skin'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_KBOARD_SKIN)) exit;
		
		echo "<h1>KBoard 게시판 스킨 설치</h1>";
		@ob_flush();
		@flush();
		
		echo "<p>다운로드 중입니다. 기다려주세요.</p>";
		@ob_flush();
		@flush();
		
		$download_file = $upgrader->download($download_url, $download_version, KBStore::getAccessToken());
		echo "<p>다운로드를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_KBOARD_SKIN);
		echo "<p>설치를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p>게시판 스킨 설치가 완료 되었습니다. <code>KBoard -&gt; 게시판 목록 -&gt; 게시판 선택 -&gt; 게시판 관리</code> 페이지에서 스킨을 선택해주세요.</p>";
		@ob_flush();
		@flush();
		
		echo "<p><a href=\"" . admin_url('admin.php?page=kboard_store') . "\" class=\"button\">스토어 메뉴로 이동</a></p>";
		@ob_flush();
		@flush();
		
		exit;
	}
	else if($action == 'comments-skin'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_COMMENTS_SKIN)) exit;
		
		echo "<h1>KBoard 댓글 스킨 설치</h1>";
		@ob_flush();
		@flush();
		
		echo "<p>다운로드 중입니다. 기다려주세요.</p>";
		@ob_flush();
		@flush();
		
		$download_file = $upgrader->download($download_url, $download_version, KBStore::getAccessToken());
		echo "<p>다운로드를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_COMMENTS_SKIN);
		echo "<p>설치를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p>댓글 스킨 설치가 완료 되었습니다. <code>KBoard -&gt; 게시판 목록 -&gt; 게시판 선택 -&gt; 게시판 관리</code> 페이지에서 스킨을 선택해주세요.</p>";
		@ob_flush();
		@flush();
		
		echo "<p><a href=\"" . admin_url('admin.php?page=kboard_store') . "\" class=\"button\">스토어 메뉴로 이동</a></p>";
		@ob_flush();
		@flush();
		
		exit;
	}
	
	$upgrader->flush();
	$version = $upgrader->getLatestVersion();
	
	include_once 'pages/kboard_updates.php';
}

/*
 * 전체 게시글 리스트
 */
function kboard_content_list(){
	include_once 'class/KBContentListTable.class.php';
	$table = new KBContentListTable();
	if(isset($_POST['uid'])){
		$action = $table->current_action();
		$content = new KBContent();
		if($action == 'delete'){
			foreach($_POST['uid'] as $key=>$value){
				$content->initWithUID($value);
				$content->remove();
			}
		}
		else if($action == 'trash'){
			foreach($_POST['uid'] as $key=>$value){
				$content->initWithUID($value);
				$content->status = 'trash';
				$content->updateContent();
			}
		}
		else if($action == 'published'){
			foreach($_POST['uid'] as $key=>$value){
				$content->initWithUID($value);
				$content->status = '';
				$content->updateContent();
			}
		}
	}
	$table->prepare_items();
	wp_enqueue_style('jquery-flick-style', KBOARD_URL_PATH.'/template/css/jquery-ui.css', array(), '1.12.1');
	wp_enqueue_style('jquery-timepicker', KBOARD_URL_PATH.'/template/css/jquery.timepicker.css', array(), KBOARD_VERSION);
	include_once 'pages/kboard_content_list.php';
}

/*
 * 관리자 페이지에서 게시판 보기
 */
function kboard_admin_view(){
	$screen = get_current_screen();
	$board_id = intval(str_replace('kboard_page_kboard_admin_view_', '', $screen->id));
	$board = new KBoard($board_id);
	include_once 'pages/kboard_admin_view.php';
}

/*
 * 게시판 디비 변경
 */
add_action('kboard_switch_to_blog', 'kboard_switch_to_blog', 0, 1);
function kboard_switch_to_blog($args=array()){
	if(isset($args['blog']) && $args['blog']){
		switch_to_blog($args['blog']);
	}
	else if(isset($_SESSION['kboard_switch_to_blog']) && $_SESSION['kboard_switch_to_blog']){
		switch_to_blog($_SESSION['kboard_switch_to_blog']);
	}
}

/*
 * 게시판 디비 복구
 */
add_action('kboard_restore_current_blog', 'kboard_restore_current_blog', 9999, 1);
function kboard_restore_current_blog($args=array()){
	if(isset($args['blog']) && $args['blog']){
		restore_current_blog();
	}
	else if(isset($_SESSION['kboard_switch_to_blog']) && $_SESSION['kboard_switch_to_blog']){
		restore_current_blog();
	}
}

/*
 * 게시판 생성 숏코드
 */
add_shortcode('kboard', 'kboard_builder');
function kboard_builder($args){
	if(!isset($args['id']) || !$args['id']) return 'KBoard 알림 :: id=null, 아이디값은 필수입니다.';
	
	if(isset($args['blog']) && $args['blog']){
		$_SESSION['kboard_switch_to_blog'] = $args['blog'];
		do_action('kboard_switch_to_blog', $args);
	}
	else{
		$_SESSION['kboard_switch_to_blog'] = '';
	}
	
	$board = new KBoard();
	$board->setID($args['id']);
	
	if($board->id){
		$builder = new KBoardBuilder($board->id);
		$builder->board = $board;
		$builder->setSkin($board->skin);
		$builder->setRpp($board->page_rpp);
		
		if(isset($args['mod']) && $args['mod']){
			if(!kboard_mod()){
				$builder->setMOD($args['mod']);
			}
		}
		
		if(isset($args['category1']) && $args['category1']){
			$builder->category1 = $args['category1'];
		}
		if(isset($args['category2']) && $args['category2']){
			$builder->category2 = $args['category2'];
		}
		
		$kboard = $builder->create();
		
		if(isset($args['blog']) && $args['blog']){
			do_action('kboard_restore_current_blog', $args);
		}
		
		return $kboard;
	}
	else{
		if(isset($args['blog']) && $args['blog']){
			do_action('kboard_restore_current_blog', $args);
		}
		
		return 'KBoard 알림 :: id='.$args['id'].', 생성되지 않은 게시판입니다.';
	}
}

/*
 * 선택된 페이지에 자동으로 게시판 생성
 */
add_filter('the_content', 'kboard_auto_builder');
function kboard_auto_builder($content){
	global $post, $wpdb;
	if(isset($post->ID) && is_page($post->ID) && !post_password_required()){
		$board_id = $wpdb->get_var("SELECT `board_id` FROM `{$wpdb->prefix}kboard_board_meta` WHERE `key`='auto_page' AND `value`='$post->ID'");
		if($board_id) return $content . kboard_builder(array('id'=>$board_id));
	}
	return $content;
}

/*
 * 최신글 생성 숏코드
 */
add_shortcode('kboard_latest', 'kboard_latest_shortcode');
function kboard_latest_shortcode($args){
	if(!isset($args['id']) || !$args['id']) return 'KBoard 알림 :: id=null, 아이디값은 필수입니다.';
	if(!isset($args['url']) || !$args['url']) return 'KBoard 알림 :: url=null, 페이지 주소는 필수입니다.';
	if(!isset($args['rpp']) || !$args['rpp']) $args['rpp'] = 5;
	
	if(isset($args['blog']) && $args['blog']){
		do_action('kboard_switch_to_blog', $args);
	}
	
	$board = new KBoard();
	$board->setID($args['id']);
	
	if($board->id){
		$builder = new KBoardBuilder($board->id, true);
		$builder->board = $board;
		$builder->setSkin($board->skin);
		$builder->setRpp($args['rpp']);
		$builder->setURL($args['url']);
		
		if(isset($args['sort']) && $args['sort']){
			if(strpos($args['sort'], '|') !== false){
				$sort = explode('|', $args['sort']);
				$args['sort'] = reset($sort);
			}
			$builder->setSorting($args['sort']);
		}
		
		if(isset($args['category1']) && $args['category1']){
			$builder->category1 = $args['category1'];
		}
		else{
			$builder->category1 = '';
		}
		
		if(isset($args['category2']) && $args['category2']){
			$builder->category2 = $args['category2'];
		}
		else{
			$builder->category2 = '';
		}
		
		$with_notice = true;
		if(isset($args['with_notice']) && $args['with_notice'] == 'false'){
			$with_notice = false;
		}
		
		if(isset($args['dayofweek']) && $args['dayofweek']){
			if(strpos($args['dayofweek'], '|') !== false){
				$dayofweek = explode('|', $args['dayofweek']);
				$args['dayofweek'] = reset($dayofweek);
			}
			$builder->setDayOfWeek($args['dayofweek']);
		}
		
		if(isset($args['within_days']) && $args['within_days']){
			$within_days = intval($args['within_days']);
			$builder->setWithinDays($within_days);
		}
		
		if(isset($args['random']) && $args['random'] == 'true'){
			$builder->setRandom(true);
		}
		
		$args['type'] = 'latest';
		$latest = $builder->createLatest($with_notice, $args);
		
		if(isset($args['blog']) && $args['blog']){
			do_action('kboard_restore_current_blog', $args);
		}
		
		return $latest;
	}
	else{
		if(isset($args['blog']) && $args['blog']){
			do_action('kboard_restore_current_blog', $args);
		}
		
		return 'KBoard 알림 :: id='.$args['id'].', 생성되지 않은 게시판입니다.';
	}
}

/*
 * 최신글 모아보기 생성 숏코드
 */
add_shortcode('kboard_latestview', 'kboard_latestview_shortcode');
function kboard_latestview_shortcode($args){
	if(!isset($args['id']) || !$args['id']) return 'KBoard 알림 :: id=null, 아이디값은 필수입니다.';
	
	if(isset($args['blog']) && $args['blog']){
		do_action('kboard_switch_to_blog', $args);
	}
	
	$latestview = new KBLatestview($args['id']);
	if($latestview->uid){
		$builder = new KBoardBuilder($latestview->getLinkedBoard(), true);
		$builder->board = new KBoard();
		$builder->setSkin($latestview->skin);
		$builder->setRpp($latestview->rpp);
		$builder->setSorting($latestview->sort);
		
		if(isset($args['category1']) && $args['category1']){
			$builder->category1 = $args['category1'];
		}
		else{
			$builder->category1 = '';
		}
		
		if(isset($args['category2']) && $args['category2']){
			$builder->category2 = $args['category2'];
		}
		else{
			$builder->category2 = '';
		}
		
		$with_notice = true;
		if(isset($args['with_notice']) && $args['with_notice'] == 'false'){
			$with_notice = false;
		}
		
		if(isset($args['dayofweek']) && $args['dayofweek']){
			if(strpos($args['dayofweek'], '|') !== false){
				$dayofweek = explode('|', $args['dayofweek']);
				$args['dayofweek'] = reset($dayofweek);
			}
			$builder->setDayOfWeek($args['dayofweek']);
		}
		
		if(isset($args['within_days']) && $args['within_days']){
			$within_days = intval($args['within_days']);
			$builder->setWithinDays($within_days);
		}
		
		if(isset($args['random']) && $args['random'] == 'true'){
			$builder->setRandom(true);
		}
		
		$args['type'] = 'latestview';
		$latest = $builder->createLatest($with_notice, $args);
		
		if(isset($args['blog']) && $args['blog']){
			do_action('kboard_restore_current_blog', $args);
		}
		
		return $latest;
	}
	else{
		if(isset($args['blog']) && $args['blog']){
			do_action('kboard_restore_current_blog', $args);
		}
		
		return 'KBoard 알림 :: id='.$args['id'].', 생성되지 않은 최신글 뷰 입니다.';
	}
}

/*
 * 비동기 게시판 빌더
 */
add_action('wp_ajax_kboard_ajax_builder', 'kboard_ajax_builder');
add_action('wp_ajax_nopriv_kboard_ajax_builder', 'kboard_ajax_builder');
function kboard_ajax_builder(){
	check_ajax_referer('kboard_ajax_security', 'security');
	
	if(isset($_SESSION['kboard_board_id']) && $_SESSION['kboard_board_id']){
		$board_id = intval($_SESSION['kboard_board_id']);
	}
	
	if(isset($_REQUEST['board_id']) && $_REQUEST['board_id']){
		$board_id = intval($_REQUEST['board_id']);
	}
	
	$board = new KBoard();
	$board->setID($board_id);
	
	if($board->id){
		$builder = new KBoardBuilder($board->id);
		$builder->mod = 'list';
		$builder->board = $board;
		$builder->is_ajax = true;
		$builder->view_iframe = $board->meta->view_iframe;
		
		if(isset($_REQUEST['view_iframe'])){
			$builder->view_iframe = $_REQUEST['view_iframe'] ? '1' : '';
		}
		
		if(isset($_REQUEST['rpp']) && $_REQUEST['rpp']){
			$builder->setRpp($_REQUEST['rpp']);
		}
		else{
			$builder->setRpp($board->page_rpp);
		}
		
		if(isset($_REQUEST['sort']) && $_REQUEST['sort']){
			$sort = sanitize_key($_REQUEST['sort']);
			$builder->setSorting($sort);
		}
		
		if(isset($_REQUEST['base_url']) && $_REQUEST['base_url']){
			$builder->setURL($_REQUEST['base_url']);
		}
		else{
			$builder->setURL(wp_get_referer());
		}
		
		if(isset($_REQUEST['skin']) && $_REQUEST['skin']){
			$skin = sanitize_key($_REQUEST['skin']);
			$builder->setSkin($skin);
		}
		else{
			$builder->setSkin($board->skin);
		}
		
		$ajax_builder_type = 'array';
		if(isset($_REQUEST['ajax_builder_type']) && in_array($_REQUEST['ajax_builder_type'], array('array', 'html'))){
			$ajax_builder_type = $_REQUEST['ajax_builder_type'];
		}
		
		if($ajax_builder_type == 'array'){
			wp_send_json(array('result'=>'success', 'data'=>$builder->getListArray()));
		}
		else if($ajax_builder_type == 'html'){
			wp_send_json(array('result'=>'success', 'data'=>$builder->getListHTML()));
		}
	}
	
	wp_send_json(array('result'=>'error', 'message'=>__('You do not have permission.', 'kboard')));
}

/*
 * 관리자 알림 출력
 */
add_action('admin_notices', 'kboard_admin_notices');
function kboard_admin_notices(){
	if(KBOARD_CONNECT_COSMOSFARM && current_user_can('manage_options')){
		
		// 관리자 알림 시작
		include_once KBOARD_DIR_PATH . '/class/KBAdminNotices.class.php';
		
		if(!is_writable(WP_CONTENT_DIR . '/uploads')){
			echo KBAdminNotices::get_upload_folder_not_writable_message();
		}
		
		if(!get_option('kboard_updates_notify_disabled')){
			$upgrader = KBUpgrader::getInstance();
			$vsersion = $upgrader->getLatestVersion()->kboard;
			
			if(version_compare(KBOARD_VERSION, $vsersion, '<')){
				echo KBAdminNotices::get_kboard_update_notice_message_message($vsersion);
			}
		}
	}
}

/*
 * 스크립트와 스타일 파일 등록
 */
add_action('wp_enqueue_scripts', 'kboard_style', 999);
add_action('kboard_switch_to_blog', 'kboard_style');
function kboard_style(){
	// KBoard 미디어 추가 스타일 속성 등록
	wp_enqueue_style('kboard-editor-media', KBOARD_URL_PATH . '/template/css/editor_media.css', array(), KBOARD_VERSION);
	
	// 활성화된 스킨의 style.css 등록
	$skin = KBoardSkin::getInstance();
	foreach($skin->getActiveList() as $skin_name){
		wp_enqueue_style("kboard-skin-{$skin_name}", $skin->url($skin_name, 'style.css'), array(), KBOARD_VERSION);
	}
	
	wp_register_style('kboard-jquery-flick-style', KBOARD_URL_PATH . '/template/css/jquery-ui.css', array(), '1.12.1');
}

/*
 * 스크립트와 스타일 파일 등록
 */
add_action('wp_enqueue_scripts', 'kboard_scripts', 999);
add_action('kboard_switch_to_blog', 'kboard_scripts');
function kboard_scripts(){
	wp_enqueue_script('jquery');
	wp_enqueue_script('kboard-script', KBOARD_URL_PATH . '/template/js/script.js', array(), KBOARD_VERSION, true);
	
	// Tags Input 등록
	wp_register_style('tagsinput', KBOARD_URL_PATH . '/assets/tagsinput/jquery.tagsinput.css', array(), '1.3.3');
	wp_register_script('tagsinput', KBOARD_URL_PATH . '/assets/tagsinput/jquery.tagsinput.js', array('jquery'), '1.3.3');
	
	// Moment.js 등록
	wp_register_script('moment', KBOARD_URL_PATH . '/assets/moment/moment.js', array('jquery'), '2.17.1');
	
	// jQuery Date Range Picker Plugin 등록
	wp_register_style('daterangepicker', KBOARD_URL_PATH . '/assets/daterangepicker/daterangepicker.css', array(), '0.0.8');
	wp_register_script('daterangepicker', KBOARD_URL_PATH . '/assets/daterangepicker/jquery.daterangepicker.js', array('jquery', 'moment'), '0.0.8');
	
	// jQuery lightSlider 등록
	wp_register_style('lightslider', KBOARD_URL_PATH . '/assets/lightslider/css/lightslider.css', array(), '1.1.6');
	wp_register_script('lightslider', KBOARD_URL_PATH . '/assets/lightslider/js/lightslider.js', array('jquery'), '1.1.6');
	
	// 아임포트 등록
	wp_register_script('iamport-payment', 'https://cdn.iamport.kr/js/iamport.payment-1.1.7.js', array('jquery'), '1.1.7');
	
	// 구글 리캡차 등록
	wp_register_script('recaptcha', 'https://www.google.com/recaptcha/api.js');
	
	// Summernot 등록
	wp_register_style('summernote', KBOARD_URL_PATH . '/assets/summernote/summernote-lite.css', array(), '0.8.18');
	wp_register_script('summernote', KBOARD_URL_PATH . '/assets/summernote/summernote-lite.js', array('jquery'), '0.8.18');
	wp_register_script('summernote-ko-KR', KBOARD_URL_PATH . '/assets/summernote/lang/summernote-ko-KR.js', array('summernote'), '0.8.18');
	wp_register_script('summernote-ja-JP', KBOARD_URL_PATH . '/assets/summernote/lang/summernote-ja-JP.js', array('summernote'), '0.8.18');
	
	// jQuery Timepicker 등록
	wp_register_style('jquery-timepicker', KBOARD_URL_PATH . '/template/css/jquery.timepicker.css', array(), '1.3.5');
	wp_register_script('jquery-timepicker', KBOARD_URL_PATH . '/template/js/jquery.timepicker.js', array('jquery'), '1.3.5');
	
	// PG 등록
	wp_register_script('kboard-builtin-pg', KBOARD_URL_PATH . '/template/js/pg.js', array(), KBOARD_VERSION, true);
	
	// 필드 관련 스크립트 등록
	wp_register_script('kboard-field-date', KBOARD_URL_PATH . '/template/js/field-date.js', array('jquery'), KBOARD_VERSION, true);
	wp_register_script('kboard-field-time', KBOARD_URL_PATH . '/template/js/field-time.js', array('jquery'), KBOARD_VERSION, true);
	
	// 설정 등록
	$localize = array(
		'version' => KBOARD_VERSION,
		'home_url' => home_url('/', 'relative'),
		'site_url' => site_url('/', 'relative'),
		'post_url' => admin_url('admin-post.php'),
		'ajax_url' => admin_url('admin-ajax.php'),
		'plugin_url' => KBOARD_URL_PATH,
		'media_group' => kboard_media_group(),
		'view_iframe' => kboard_view_iframe(),
		'locale' => get_locale(),
		'ajax_security' => wp_create_nonce('kboard_ajax_security'),
	);
	$kboard_iamport_id = get_option('kboard_iamport_id');
	if($kboard_iamport_id){
		$localize['iamport_id'] = $kboard_iamport_id;
	}
	wp_localize_script('kboard-script', 'kboard_settings', apply_filters('kboard_settings', $localize));
	
	// 번역 등록
	$localize = array(
		'kboard_add_media' => __('KBoard Add Media', 'kboard'),
		'next' => __('Next', 'kboard'),
		'prev' => __('Prev', 'kboard'),
		'required' => __('%s is required.', 'kboard'),
		'please_enter_the_title' => __('Please enter the title.', 'kboard'),
		'please_enter_the_author' => __('Please enter the author.', 'kboard'),
		'please_enter_the_password' => __('Please enter the password.', 'kboard'),
		'please_enter_the_CAPTCHA' => __('Please enter the CAPTCHA.', 'kboard'),
		'please_enter_the_name' => __('Please enter the name.', 'kboard'),
		'please_enter_the_email' => __('Please enter the email.', 'kboard'),
		'you_have_already_voted' => __('You have already voted.', 'kboard'),
		'please_wait' => __('Please wait.', 'kboard'),
		'newest' => __('Newest', 'kboard'),
		'best' => __('Best', 'kboard'),
		'updated' => __('Updated', 'kboard'),
		'viewed' => __('Viewed', 'kboard'),
		'yes' => __('Yes', 'kboard'),
		'no' => __('No', 'kboard'),
		'did_it_help' => __('Did it help?', 'kboard'),
		'hashtag' => __('Hashtag', 'kboard'),
		'tag' => __('Tag', 'kboard'),
		'add_a_tag' => __('Add a Tag', 'kboard'),
		'removing_tag' => __('Removing tag', 'kboard'),
		'changes_you_made_may_not_be_saved' => __('Changes you made may not be saved.', 'kboard'),
		'name' => __('Name', 'kboard'),
		'email' => __('Email', 'kboard'),
		'address' => __('Address', 'kboard'),
		'postcode' => __('Postcode', 'kboard'),
		'phone_number' => __('Phone number', 'kboard'),
		'mobile_phone' => __('Mobile phone', 'kboard'),
		'phone' => __('Phone', 'kboard'),
		'company_name' => __('Company name', 'kboard'),
		'vat_number' => __('VAT number', 'kboard'),
		'bank_account' => __('Bank account', 'kboard'),
		'name_of_deposit' => __('Name of deposit', 'kboard'),
		'find' => __('Find', 'kboard'),
		'rate' => __('Rate', 'kboard'),
		'ratings' => __('Ratings', 'kboard'),
		'waiting' => __('Waiting', 'kboard'),
		'complete' => __('Complete', 'kboard'),
		'question' => __('Question', 'kboard'),
		'answer' => __('Answer', 'kboard'),
		'notify_me_of_new_comments_via_email' => __('Notify me of new comments via email', 'kboard'),
		'ask_question' => __('Ask Question', 'kboard'),
		'categories' => __('Categories', 'kboard'),
		'pages' => __('Pages', 'kboard'),
		'all_products' => __('All Products', 'kboard'),
		'your_orders' => __('Your Orders', 'kboard'),
		'your_sales' => __('Your Sales', 'kboard'),
		'my_orders' => __('My Orders', 'kboard'),
		'my_sales' => __('My Sales', 'kboard'),
		'new_product' => __('New Product', 'kboard'),
		'edit_product' => __('Edit Product', 'kboard'),
		'delete_product' => __('Delete Product', 'kboard'),
		'seller' => __('Seller', 'kboard'),
		'period' => __('Period', 'kboard'),
		'period_of_use' => __('Period of use', 'kboard'),
		'last_updated' => __('Last updated', 'kboard'),
		'list_price' => __('List price', 'kboard'),
		'price' => __('Price', 'kboard'),
		'total_price' => __('Total price', 'kboard'),
		'amount' => __('Amount', 'kboard'),
		'quantity' => __('Quantity', 'kboard'),
		'use_points' => __('Use points', 'kboard'),
		'my_points' => __('My points', 'kboard'),
		'available_points' => __('Available points', 'kboard'),
		'apply_points' => __('Apply points', 'kboard'),
		'buy_it_now' => __('Buy It Now', 'kboard'),
		'sold_out' => __('Sold Out', 'kboard'),
		'for_free' => __('For free', 'kboard'),
		'pay_s' => __('Pay %s', 'kboard'),
		'payment_method' => __('Payment method', 'kboard'),
		'credit_card' => __('Credit card', 'kboard'),
		'make_a_deposit' => __('Make a deposit', 'kboard'),
		'reward_point' => __('Reward point', 'kboard'),
		'download_expiry' => __('Download expiry', 'kboard'),
		'checkout' => __('Checkout', 'kboard'),
		'buyer_information' => __('Buyer information', 'kboard'),
		'applying_cash_receipts' => __('Applying cash receipts', 'kboard'),
		'applying_cash_receipt' => __('Applying cash receipt', 'kboard'),
		'cash_receipt' => __('Cash receipt', 'kboard'),
		'privacy_policy' => __('Privacy policy', 'kboard'),
		'i_agree_to_the_privacy_policy' => __('I agree to the privacy policy.', 'kboard'),
		'i_confirm_the_terms_of_the_transaction_and_agree_to_the_payment_process' => __('I confirm the terms of the transaction and agree to the payment process.', 'kboard'),
		'today' => __('Today', 'kboard'),
		'yesterday' => __('Yesterday', 'kboard'),
		'this_month' => __('This month', 'kboard'),
		'last_month' => __('Last month', 'kboard'),
		'last_30_days' => __('Last 30 days', 'kboard'),
		'agree' => __('Agree', 'kboard'),
		'disagree' => __('Disagree', 'kboard'),
		'opinion' => __('Opinion', 'kboard'),
		'comment' => __('Comment', 'kboard'),
		'comments' => __('Comments', 'kboard'),
		'your_order_has_been_cancelled' => __('Your order has been cancelled.', 'kboard'),
		'order_information_has_been_changed' => __('Order information has been changed.', 'kboard'),
		'order_date' => __('Order date', 'kboard'),
		'point_payment' => __('Point payment', 'kboard'),
		'cancel_point_payment' => __('Cancel point payment', 'kboard'),
		'paypal' => __('PayPal', 'kboard'),
		'point' => __('Point', 'kboard'),
		'zipcode' => __('Zip Code', 'kboard'),
		'this_year' => __('This year', 'kboard'),
		'last_year' => __('Last year', 'kboard'),
		'period_total' => __('Period total', 'kboard'),
		'total_revenue' => __('Total revenue', 'kboard'),
		'terms_of_service' => __('Terms of service', 'kboard'),
		'i_agree_to_the_terms_of_service' => __('I agree to the terms of service.', 'kboard'),
		'your_shopping_cart_is_empty' => __('Your Shopping Cart Is Empty!', 'kboard'),
		'category' => __('Category', 'kboard'),
		'select' => __('Select', 'kboard'),
		'category_select' => __('Category select', 'kboard'),
		'information' => __('Information', 'kboard'),
		'telephone' => __('Telephone', 'kboard'),
		'items' => __('Items', 'kboard'),
		'total_amount' => __('Total amount', 'kboard'),
		'total_quantity' => __('Total quantity', 'kboard'),
		'make_payment' => __('Make Payment', 'kboard'),
		'add' => __('Add', 'kboard'),
		'close' => __('Close', 'kboard'),
	);
	wp_localize_script('kboard-script', 'kboard_localize_strings', apply_filters('kboard_localize_strings', $localize));
}

/*
 * 관리자 페이지 스타일 파일을 출력한다.
 */
add_action('admin_enqueue_scripts', 'kboard_admin_style', 999, 1);
function kboard_admin_style($hook_suffix){
	if($hook_suffix == 'kboard_page_kboard_content_list'){
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('jquery-timepicker', KBOARD_URL_PATH . '/template/js/jquery.timepicker.js', array(), KBOARD_VERSION);
	}
	wp_enqueue_script('kboard-cosmosfarm-apis', KBOARD_URL_PATH . '/pages/cosmosfarm-apis.js', array(), KBOARD_VERSION);
	wp_enqueue_style('kboard-admin', KBOARD_URL_PATH . '/pages/kboard-admin.css', array(), KBOARD_VERSION);
}

/*
 * 읽기권한이 없어서 로그인 페이지로 이동한다.
 */
add_action('kboard_cannot_read_document', 'kboard_cannot_read_document_go_login', 10, 5);
function kboard_cannot_read_document_go_login($action, $url, $content, $board, $builder){
	if($action == 'go_login'){
		echo '<script>alert("'.__('Please Log in to continue.', 'kboard').'");</script>';
		echo '<script>top.window.location.href="' . esc_url_raw($url) . '";</script>';
	}
}

/*
 * 읽기권한이 없어서 게시판 리스트로 돌아간다.
 */
add_action('kboard_cannot_read_document', 'kboard_cannot_read_document_go_back', 10, 5);
function kboard_cannot_read_document_go_back($action, $url, $content, $board, $builder){
	if($action == 'go_back'){
		echo '<script>alert("'.__('You do not have permission.', 'kboard').'");</script>';
		echo '<script>window.location.href="' . esc_url_raw($url) . '";</script>';
	}
}

/*
 * 포인트 부족으로 게시판 리스트로 돌아간다.
 */
add_action('kboard_cannot_read_document', 'kboard_not_enough_points_read_document_go_back', 10, 5);
function kboard_not_enough_points_read_document_go_back($action, $url, $content, $board, $builder){
	if($action == 'not_enough_points'){
		echo '<script>alert("'.__('You have not enough points.', 'kboard').'");</script>';
		echo '<script>window.location.href="' . esc_url_raw($url) . '";</script>';
	}
}

/*
 * 첨부파일 다운로드 권한이 없어서 로그인 페이지로 이동한다.
 */
add_action('kboard_cannot_download_file', 'kboard_cannot_download_file_go_login', 10, 5);
function kboard_cannot_download_file_go_login($action, $url, $content, $board, $comment){
	if($action == 'go_login'){
		echo '<script>alert("'.__('Please Log in to continue.', 'kboard').'");</script>';
		echo '<script>top.window.location.href="' . esc_url_raw($url) . '";</script>';
	}
}

/*
 * 첨부파일 다운로드 권한이 없어서 이전 페이지로 돌아간다.
 */
add_action('kboard_cannot_download_file', 'kboard_cannot_download_file_go_back', 10, 5);
function kboard_cannot_download_file_go_back($action, $url, $content, $board, $comment){
	if($action == 'go_back'){
		echo '<script>alert("'.__('You do not have permission.', 'kboard').'");</script>';
		echo '<script>history.go(-1);</script>';
	}
}

/*
 * 첨부파일 다운로드 포인트 부족으로 이전 페이지로 돌아간다.
 */
add_action('kboard_cannot_download_file', 'kboard_not_enough_points_download_file_go_back', 10, 5);
function kboard_not_enough_points_download_file_go_back($action, $url, $content, $board, $comment){
	if($action == 'not_enough_points'){
		echo '<script>alert("'.__('You have not enough points.', 'kboard').'");</script>';
		echo '<script>history.go(-1);</script>';
	}
}

/*
 * 툴바에 게시판 설정페이지 링크를 추가한다.
 */
add_action('admin_bar_menu', 'kboard_add_toolbar_link', 999);
function kboard_add_toolbar_link($wp_admin_bar){
	global $post, $wpdb;
	if(!is_admin() && current_user_can('manage_options') && $post && $post->ID){
		$board_id = $wpdb->get_var("SELECT `board_id` FROM `{$wpdb->prefix}kboard_board_meta` WHERE `key`='auto_page' AND `value`='{$post->ID}'");
		
		if(!$board_id){
			$board_id = $wpdb->get_var("SELECT `board_id` FROM `{$wpdb->prefix}kboard_board_meta` WHERE `key`='latest_target_page' AND `value`='{$post->ID}'");
		}
		
		if($board_id){
			$args = array(
				'id'    => 'kboard-setting-page',
				'title' => __('KBoard Forum Settings', 'kboard'),
				'href'  => admin_url("admin.php?page=kboard_list&board_id={$board_id}"),
				'meta'  => array('class' => 'kboard-setting-page')
			);
			$wp_admin_bar->add_node($args);
		}
	}
}

/*
 * head 태그 사이에 내용을 출력한다.
 */
add_action('wp_head', 'kboard_head', 999);
add_action('kboard_iframe_head', 'kboard_head');
function kboard_head(){
	wp_print_head_scripts();
	print_late_styles();
	
	$custom_css = get_option('kboard_custom_css');
	if($custom_css){
		echo "<style type=\"text/css\">{$custom_css}</style>";
	}
}

/*
 * 게시글 내용의 문단을 나눈다.
 */
add_filter('kboard_content_paragraph_breaks', 'kboard_content_paragraph_breaks', 10, 2);
function kboard_content_paragraph_breaks($content, $builder=''){
	$content = nl2br($content);
	$content = preg_replace("/(<(|\/)(table|thead|tfoot|tbody|th|tr|td|ul|ol|li|h1|h2|h3|h4|h5|h6|hr|p).*>)(<br \/>)/", "\$1", $content);
	return $content;
}

/*
 * 우커머스 상품 탭에 표시
 */
add_filter('woocommerce_product_tabs', 'kboard_woocommerce_product_tabs_add');
function kboard_woocommerce_product_tabs_add($tabs){
	$board_list = new KBoardList();
	foreach($board_list->getWoocommerceProductTabsAdd() as $list){
		$tabs['kboard_woocommerce_product_tabs_' . $list->board_id]['title'] = $list->board_name;
		$tabs['kboard_woocommerce_product_tabs_' . $list->board_id]['priority'] = $list->priority;
		$tabs['kboard_woocommerce_product_tabs_' . $list->board_id]['callback'] = function() use ($list){
			global $product;
			
			echo '<h2>' . esc_html($list->board_name) . '</h2>';
			
			$board_id = $list->board_id;
			$iframe_id = uniqid();
			$product_id = $product->get_id();
			
			$url = new KBUrl();
			$_SESSION['kboard_board_id'] = $board_id;
			
			echo '<iframe id="kboard-iframe-' . $iframe_id . '" class="kboard-iframe kboard-iframe-' . $board_id . '" src="' . $url->set('kboard_id', $board_id)->set('category1', $product_id)->set('iframe_id', $iframe_id)->set('woocommerce_product_tabs_inside', $product_id)->toString() . '" style="width:100%" scrolling="no" frameborder="0"></iframe>';
		};
	}
	return $tabs;
}

/*
add_filter('kboard_content', 'kboard_woocommerce_product_tabs_add_content', 10, 3);
function kboard_woocommerce_product_tabs_add_content($content, $content_uid, $board_id){
	return $content;
}
*/

/*
 * 시스템 업데이트
 */
add_action('plugins_loaded', 'kboard_update_check');
function kboard_update_check(){
	// 시스템 업데이트를 이미 진행 했다면 중단한다.
	if(version_compare(KBOARD_VERSION, get_option('kboard_version'), '<=')) return;
	
	// 시스템 업데이트를 확인하기 위해서 버전 등록
	if(get_option('kboard_version') !== false){
		update_option('kboard_version', KBOARD_VERSION);
		
		// 관리자 알림 시작
		include_once KBOARD_DIR_PATH . '/class/KBAdminNotices.class.php';
		KBAdminNotices::kboard_updated_notice();
	}
	else{
		add_option('kboard_version', KBOARD_VERSION, null, 'no');
	}
	
	kboard_activation_execute();
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
			$blogids = $wpdb->get_col("SELECT `blog_id` FROM {$wpdb->blogs}");
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
	$tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
	foreach($tables as $table){
		$table = $table[0];
		$prefix = substr($table, 0, 7);
		if($prefix == 'kboard_') $wpdb->query("RENAME TABLE `{$table}` TO `{$wpdb->prefix}{$table}`");
	}
	unset($tables, $table, $prefix);
	
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset_collate = $wpdb->get_charset_collate();
	
	$wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}kboard_board_setting` (
	`uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`board_name` varchar(127) NOT NULL,
	`skin` varchar(127) NOT NULL,
	`use_comment` varchar(5) NOT NULL,
	`use_editor` varchar(5) NOT NULL,
	`permission_read` varchar(127) NOT NULL,
	`permission_write` varchar(127) NOT NULL,
	`admin_user` text NOT NULL,
	`use_category` varchar(5) NOT NULL,
	`category1_list` text NOT NULL,
	`category2_list` text NOT NULL,
	`page_rpp` int(10) unsigned NOT NULL,
	`created` char(14) NOT NULL,
	PRIMARY KEY (`uid`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_board_attached` (
	`uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`content_uid` bigint(20) unsigned NOT NULL,
	`comment_uid` bigint(20) unsigned NOT NULL,
	`file_key` varchar(127) NOT NULL,
	`date` char(14) NOT NULL,
	`file_path` varchar(255) NOT NULL,
	`file_name` varchar(127) NOT NULL,
	`file_size` bigint(20) unsigned NOT NULL,
	`download_count` int(10) unsigned NOT NULL,
	`metadata` longtext NOT NULL,
	PRIMARY KEY (`uid`),
	KEY `content_uid` (`content_uid`),
	KEY `comment_uid` (`comment_uid`)
	) {$charset_collate};");
	
	$wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}kboard_board_content` (
	`uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`board_id` bigint(20) unsigned NOT NULL,
	`parent_uid` bigint(20) unsigned NOT NULL,
	`member_uid` bigint(20) unsigned NOT NULL,
	`member_display` varchar(127) NOT NULL,
	`title` varchar(127) NOT NULL,
	`content` longtext NOT NULL,
	`date` char(14) NOT NULL,
	`update` char(14) NOT NULL,
	`view` int(10) unsigned NOT NULL,
	`comment` int(10) unsigned NOT NULL,
	`like` int(10) unsigned NOT NULL,
	`unlike` int(10) unsigned NOT NULL,
	`vote` int(11) NOT NULL,
	`thumbnail_file` varchar(127) NOT NULL,
	`thumbnail_name` varchar(127) NOT NULL,
	`category1` varchar(127) NOT NULL,
	`category2` varchar(127) NOT NULL,
	`secret` varchar(5) NOT NULL,
	`notice` varchar(5) NOT NULL,
	`search` char(1) NOT NULL,
	`status` varchar(20) NOT NULL,
	`password` varchar(127) NOT NULL,
	PRIMARY KEY (`uid`),
	KEY `board_id` (`board_id`),
	KEY `parent_uid` (`parent_uid`),
	KEY `member_uid` (`member_uid`),
	KEY `date` (`date`),
	KEY `update` (`update`),
	KEY `view` (`view`),
	KEY `vote` (`vote`),
	KEY `category1` (`category1`),
	KEY `category2` (`category2`),
	KEY `notice` (`notice`),
	KEY `status` (`status`)
	) {$charset_collate};");
	
	$wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}kboard_board_option` (
	`uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`content_uid` bigint(20) unsigned NOT NULL,
	`option_key` varchar(127) NOT NULL,
	`option_value` longtext NOT NULL,
	PRIMARY KEY (`uid`),
	KEY `content_uid` (`content_uid`),
	KEY `option_key` (`option_key`)
	) {$charset_collate};");
	
	$wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}kboard_board_meta` (
	`board_id` bigint(20) unsigned NOT NULL,
	`key` varchar(127) NOT NULL,
	`value` longtext NOT NULL,
	UNIQUE KEY `meta_index` (`board_id`,`key`)
	) {$charset_collate};");
	
	$wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}kboard_board_latestview` (
	`uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(127) NOT NULL,
	`skin` varchar(127) NOT NULL,
	`rpp` int(10) unsigned NOT NULL,
	`sort` varchar(20) NOT NULL,
	`created` char(14) NOT NULL,
	PRIMARY KEY (`uid`)
	) {$charset_collate};");
	
	$wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}kboard_board_latestview_link` (
	`latestview_uid` bigint(20) unsigned NOT NULL,
	`board_id` bigint(20) unsigned NOT NULL,
	UNIQUE KEY `latestview_uid` (`latestview_uid`,`board_id`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_meida` (
	`uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`media_group` varchar(127) DEFAULT NULL,
	`date` char(14) DEFAULT NULL,
	`file_path` varchar(127) DEFAULT NULL,
	`file_name` varchar(127) DEFAULT NULL,
	`file_size` bigint(20) unsigned NOT NULL,
	`download_count` int(10) unsigned NOT NULL,
	`metadata` longtext NOT NULL,
	PRIMARY KEY (`uid`),
	KEY `media_group` (`media_group`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_meida_relationships` (
	`content_uid` bigint(20) unsigned NOT NULL,
	`comment_uid` bigint(20) unsigned NOT NULL,
	`media_uid` bigint(20) unsigned NOT NULL,
	KEY `content_uid_2` (`content_uid`),
	KEY `comment_uid` (`comment_uid`),
	KEY `media_uid` (`media_uid`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_order_item` (
	`order_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`order_id` bigint(20) unsigned NOT NULL,
	PRIMARY KEY (`order_item_id`),
	KEY `order_id` (`order_id`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_order_item_meta` (
	`order_item_meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`order_item_id` bigint(20) unsigned NOT NULL,
	`meta_key` varchar(127) DEFAULT NULL,
	`meta_value` longtext,
	PRIMARY KEY (`order_item_meta_id`),
	UNIQUE KEY `order_item_id` (`order_item_id`,`meta_key`),
	KEY `meta_key` (`meta_key`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_vote` (
	`vote_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`target_uid` bigint(20) unsigned NOT NULL,
	`target_type` varchar(20) NOT NULL,
	`target_vote` varchar(20) NOT NULL,
	`user_id` bigint(20) unsigned NOT NULL,
	`ip_address` varchar(127) NOT NULL,
	`created` char(14) NOT NULL,
	PRIMARY KEY (`vote_id`),
	KEY `target_uid` (`target_uid`),
	KEY `user_id` (`user_id`)
	) {$charset_collate};");
	
	/*
	 * KBoard 2.9
	 * kboard_board_meta 테이블의 value 컬럼 데이터형 text로 변경
	 */
	list($name, $type) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_meta` `value`", ARRAY_N);
	if(stristr($type, 'varchar')){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_meta` CHANGE `value` `value` TEXT NOT NULL");
	}
	unset($name, $type);
	
	/*
	 * KBoard 3.5
	 * kboard_board_content 테이블에 search 컬럼 생성 확인
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `search`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD `search` char(1) DEFAULT NULL AFTER `notice`");
	}
	unset($name);
	
	/*
	 * KBoard 4.1
	 * kboard_board_content 테이블에 comment 컬럼 생성 확인
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `comment`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD `comment` int(10) unsigned DEFAULT NULL AFTER `view`");
	}
	if(defined('KBOARD_COMMNETS_VERSION')){
		// comment 컬럼에 댓글 입력 숫자를 등록한다.
		$contents = $wpdb->get_results("SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` WHERE 1");
		foreach($contents as $content){
			$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_comments` WHERE `content_uid`='".intval($content->uid)."'");
			$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `comment`='$count' WHERE `uid`='".intval($content->uid)."'");
		}
	}
	unset($name, $count);
	
	/*
	 * KBoard 4.2
	 * kboard_board_content 테이블에 parent_uid 컬럼 생성 확인
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `parent_uid`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD `parent_uid` BIGINT UNSIGNED NOT NULL AFTER `board_id`");
	}
	unset($name);
	
	/*
	 * KBoard 4.5
	 * kboard_board_meta 테이블의 content 컬럼 데이터형 longtext로 변경
	 */
	list($name, $type) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `content`", ARRAY_N);
	if(stristr($type, 'text')){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` CHANGE `content` `content` LONGTEXT NOT NULL");
	}
	unset($name, $type);
	
	/*
	 * KBoard 4.5
	 * kboard_board_content 테이블에 like 컬럼 추가
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `like`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD `like` int(10) unsigned DEFAULT NULL AFTER `comment`");
	}
	unset($name);
	
	/*
	 * KBoard 5.1
	 * kboard_board_option 테이블에 content_uid 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_option` WHERE `Key_name`='content_uid'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_option` ADD UNIQUE (`content_uid`, `option_key`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.1
	 * kboard_board_content 테이블에 unlike 컬럼 추가
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `unlike`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD `unlike` int(10) UNSIGNED NULL AFTER `like`");
	}
	unset($name);
	
	/*
	 * KBoard 5.1
	 * kboard_board_content 테이블에 vote 컬럼 추가
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `vote`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD `vote` int(11) NULL AFTER `unlike`");
	}
	unset($name);
	
	/*
	 * KBoard 5.1
	 * kboard_board_content 테이블에 parent_uid 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='parent_uid'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`parent_uid`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.1
	 * kboard_board_attached 테이블에 content_uid 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_attached` WHERE `Key_name`='content_uid'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_attached` ADD INDEX (`content_uid`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.2
	 * kboard_board_content 테이블에 update 컬럼 추가
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `update`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD `update` char(14) NULL AFTER `date`");
		$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `update`=`date` WHERE 1");
	}
	unset($name);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 status 컬럼 추가
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `status`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD `status` varchar(20) DEFAULT NULL AFTER `search`");
	}
	unset($name);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 member_uid 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='member_uid'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`member_uid`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 date 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='date'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`date`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 update 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='update'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`update`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 view 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='view'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`view`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 vote 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='vote'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`vote`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 category1 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='category1'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`category1`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 category2 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='category2'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`category2`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 notice 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='notice'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`notice`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 status 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='status'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`status`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.3
	 * kboard_board_latestview 테이블에 sort 컬럼 추가
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_latestview` `sort`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_latestview` ADD `sort` varchar(20) NOT NULL AFTER `rpp`");
	}
	unset($name);
	
	/*
	 * KBoard 5.3
	 * kboard_board_meta 테이블에서 게시판의 total, list_total 데이터를 지움으로써 초기화한다.
	 */
	$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_meta` WHERE `key`='total' OR `key`='list_total'");
	
	/*
	 * KBoard 5.3.11
	 * kboard_meida_relationships 테이블에 content_uid 인덱스 삭제
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_meida_relationships` WHERE `Key_name`='content_uid'");
	if(count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_meida_relationships` DROP INDEX `content_uid`");
	}
	unset($index);
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
		$blogids = $wpdb->get_col("SELECT `blog_id` FROM {$wpdb->blogs}");
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
	$wpdb->query("DROP TABLE
	`{$wpdb->prefix}kboard_board_attached`,
	`{$wpdb->prefix}kboard_board_content`,
	`{$wpdb->prefix}kboard_board_option`,
	`{$wpdb->prefix}kboard_board_setting`,
	`{$wpdb->prefix}kboard_board_meta`,
	`{$wpdb->prefix}kboard_board_latestview`,
	`{$wpdb->prefix}kboard_board_latestview_link`,
	`{$wpdb->prefix}kboard_meida`,
	`{$wpdb->prefix}kboard_meida_relationships`,
	`{$wpdb->prefix}kboard_order_item`,
	`{$wpdb->prefix}kboard_order_item_meta`,
	`{$wpdb->prefix}kboard_vote`
	");
}
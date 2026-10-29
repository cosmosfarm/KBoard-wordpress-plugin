<?php
/**
 * Offline regressions for the real KBoard PHP classes and HTTP controllers.
 * Run: php tests/security-regression.php
 * WordPress, database, network and file-upload boundaries are test doubles.
 * No WordPress database, web server or external CAPTCHA service is contacted.
 */
$cases = array(
	'captcha-valid', 'captcha-wrong', 'captcha-missing', 'captcha-no-session', 'captcha-array',
	'captcha-single-use', 'captcha-error-answer', 'captcha-generation-failure', 'captcha-login-exempt',
	'recaptcha-valid', 'recaptcha-missing', 'recaptcha-array', 'recaptcha-no-fallback',
	'recaptcha-network-error', 'recaptcha-v3-low-score', 'recaptcha-v3-wrong-action', 'recaptcha-v3-valid',
	'captcha-missing-skin', 'captcha-missing-skin-valid', 'captcha-disabled', 'captcha-field-fallback', 'captcha-field-filter', 'fields-unbound', 'fields-integer',
	'ip-spoof', 'ip-ipv6', 'ip-trusted-filter', 'ip-option-omitted', 'ip-option-forged', 'ip-update-preserved',
	'delay-member-20', 'delay-member-50', 'delay-guest-20', 'delay-guest-50',
	'delay-edit-exempt', 'delay-admin-exempt', 'delay-other-account', 'delay-other-board', 'delay-shared-ip-member',
	'filter-title', 'filter-html', 'filter-case', 'filter-space', 'filter-entity', 'filter-url', 'filter-domain',
	'filter-zero-width', 'filter-clean', 'filter-admin-exempt', 'filter-message-hook', 'filter-empty-message', 'filter-settings-preserved', 'filter-link-domain', 'filter-image-domain',
	'writer-roles-denied', 'writer-roles-admin', 'writer-designated-admin', 'writer-member-allowed',
	'editor-owner-spoof', 'editor-board-spoof', 'editor-empty-date', 'editor-missing-uid', 'editor-guest-password',
	'editor-guest-wrong-password', 'editor-no-member-reassignment', 'editor-moderation-preserved', 'editor-notice-author', 'editor-notice-role', 'editor-notice-denied',
	'ajax-management-fields', 'ajax-category-option', 'ajax-banned-content', 'ajax-forced-secret', 'ajax-other-owner', 'ajax-admin-fields', 'ajax-notice-role',
	'comment-role-denied', 'comment-role-allowed', 'comment-admin', 'comment-guest-denied', 'comment-guest-allowed',
	'comment-filter-denied', 'comment-filter-context', 'comment-global-preserved', 'comment-edit-owner', 'comment-edit-other',
	'comment-delete-owner', 'comment-delete-other', 'comment-edit-password', 'comment-update-filter',
	'permission-settings-roundtrip', 'permission-settings-display'
);
if(!isset($argv[1])){
	$failed = 0;
	foreach($cases as $case){
		$command = array(PHP_BINARY, '-d', 'display_errors=1', __FILE__, $case);
		$process = proc_open($command, array(1=>array('pipe', 'w'), 2=>array('pipe', 'w')), $pipes);
		$output = stream_get_contents($pipes[1]); fclose($pipes[1]);
		$error = stream_get_contents($pipes[2]); fclose($pipes[2]);
		$status = proc_close($process);
		$pass = $status === 0 && strpos($output, 'RESULT:PASS') !== false && $error === '';
		echo ($pass ? 'PASS ' : 'FAIL ').$case."\n";
		if(!$pass){ $failed++; echo $output.$error."\n"; }
	}
	echo sprintf("TOTAL %d | PASS %d | FAIL %d\n", count($cases), count($cases)-$failed, $failed);
	exit($failed ? 1 : 0);
}

error_reporting(E_ALL & ~E_DEPRECATED);
date_default_timezone_set('UTC');
$source = getenv('KBOARD_TEST_SOURCE') ?: dirname(__DIR__);
define('ABSPATH', __DIR__.'/');
define('KBOARD_DIR_PATH', $source.'/kboard');
define('KBOARD_URL_PATH', 'https://example.test/kboard');
define('WP_CONTENT_DIR', sys_get_temp_dir().'/kboard-offline-no-files-'.getmypid());
$_GET = $_POST = $_REQUEST = $_FILES = $_SESSION = array();
$_SERVER['REMOTE_ADDR'] = '192.0.2.10';
$_SERVER['HTTP_REFERER'] = 'https://example.test/board';
$GLOBALS['test_user'] = (object)array('ID'=>10, 'roles'=>array('subscriber'), 'user_login'=>'member', 'display_name'=>'Member');
$GLOBALS['test_options'] = array('kboard_captcha_stop'=>1, 'kboard_xssfilter'=>1);
$GLOBALS['test_hooks'] = array();
$GLOBALS['test_filters'] = array();
$GLOBALS['test_now'] = strtotime('2026-10-29 12:00:00 UTC');
$GLOBALS['test_result'] = false;
$GLOBALS['test_shutdown'] = null;
ob_start();
register_shutdown_function(function(){
	$body = ob_get_clean();
	if($GLOBALS['test_shutdown']){
		try{ call_user_func($GLOBALS['test_shutdown'], $body); $GLOBALS['test_result'] = true; }
		catch(Throwable $e){ $body .= $e->getMessage(); }
	}
	echo ($GLOBALS['test_result'] ? 'RESULT:PASS' : 'RESULT:FAIL')."\n";
	if(!$GLOBALS['test_result']) echo $body;
});
function check($value, $message='Assertion failed'){ if(!$value) throw new RuntimeException($message); }
class TestBoundary extends RuntimeException { public $data; public function __construct($name, $data=array()){ parent::__construct($name); $this->data=$data; } }
function __($text, $domain=''){ return $text; }
function apply_filters($tag, $value, ...$args){ foreach($GLOBALS['test_filters'][$tag] ?? array() as $callback) $value=$callback($value, ...$args); return $value; }
function add_action($tag, $callback, $priority=10, $accepted=1){}
function do_action($tag, ...$args){
	$GLOBALS['test_hooks'][]=$tag;
	if($tag==='kboard_pre_document_update' && !empty($GLOBALS['test_allow_update'])) return;
	if(in_array($tag, array('kboard_pre_document_insert','kboard_pre_document_update','kboard_comments_pre_insert','kboard_comments_pre_update','kboard_comments_pre_delete'), true)) throw new TestBoundary($tag, $args);
}
function get_option($key, $default=false){ return $GLOBALS['test_options'][$key] ?? $default; }
function is_user_logged_in(){ return get_current_user_id()>0; }
function get_current_user_id(){ return $GLOBALS['test_user']->ID; }
function wp_get_current_user(){ return $GLOBALS['test_user']; }
function get_userdata($id){ return $GLOBALS['test_user']; }
function get_current_blog_id(){ return 1; }
function is_admin(){ return false; }
function is_multisite(){ return false; }
function kboard_switch_to_blog(){}
function current_time($type){ return $GLOBALS['test_now']; }
function current_user_can($capability){ return in_array('administrator', $GLOBALS['test_user']->roles, true); }
function sanitize_key($value){ return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string)$value)); }
function sanitize_text_field($value){ return is_scalar($value) ? trim(strip_tags((string)$value)) : ''; }
function wp_strip_all_tags($value){ return strip_tags(preg_replace('@<(script|style)[^>]*?>.*?</\1>@si', '', $value)); }
function esc_sql($value){ return is_array($value) ? array_map('esc_sql',$value) : addslashes((string)$value); }
function wp_unslash($value){ return is_array($value) ? array_map('wp_unslash',$value) : stripslashes((string)$value); }
function stripslashes_deep($value){ return wp_unslash($value); }
function wp_json_encode($value){ return json_encode($value); }
function wp_verify_nonce($nonce, $action){ return is_string($nonce) && $nonce==='valid:'.$action; }
function check_ajax_referer($action, $key){ check(wp_verify_nonce($_POST[$key] ?? '', $action), 'Invalid AJAX nonce'); }
function wp_send_json($data){ throw new TestBoundary('json', $data); }
function wp_die($message){ throw new TestBoundary('wp_die', $message); }
function wp_upload_dir(){ return array('basedir'=>'C:/offline/wp-content/uploads'); }
function untrailingslashit($value){ return rtrim($value, '/\\'); }
function wp_get_raw_referer(){ return $_SERVER['HTTP_REFERER']; }
function wp_get_referer(){ return $_SERVER['HTTP_REFERER']; }
function wp_check_password($password, $hash){ return $password==='guest-pass' && $hash==='stored-hash'; }
function wp_hash_password($password){ return 'hash:'.$password; }
function wp_insert_post($data){ return 1; }
function wp_update_post($data){ return 1; }
function wp_mkdir_p($directory){ return false; }
function content_url($path){ return 'https://example.test'.$path; }
// GD is optional here. These doubles exercise the image-write failure branch.
if(!function_exists('imagecreate')){
	function imagecreate($width,$height){ return true; }
	function imagecolorallocate($image,$red,$green,$blue){ return 0; }
	function imagestring(...$args){}
	function imageline(...$args){}
	function imagepng($image,$path){ return false; }
	function imagedestroy($image){}
}
class KBFileHandler { public function getDirlist($path){return array();} }
function is_wp_error($value){ return $value instanceof TestNetworkError; }
class TestNetworkError {}
function add_query_arg($args, $url){ return $url.'?'.http_build_query($args); }
function wp_remote_get($url){ return $GLOBALS['test_verify_response'] ?? array('body'=>'{"success":true}'); }
class KBUrl { public function __construct(...$args){} }
class KBCommentUrl { public function __construct(...$args){} public function setBoard($board){} }
class KBCommentSkin { public static function getInstance(){ return new self(); } }

// Models only the SQL shapes exercised here; never connects to a database.
class TestDatabase {
	public $prefix='wp_', $charset='utf8mb4', $insert_id=0, $queries=array(), $rows=array(), $boards=array(), $meta=array(), $options=array(), $comments=array();
	public function flush(){}
	private function id($sql, $key){ preg_match('/`'.preg_quote($key,'/').'`=\x27([^\x27]*)\x27/', $sql, $match); return $match[1] ?? null; }
	public function get_row($sql){
		$this->queries[]=$sql;
		$id=$this->id($sql,'uid');
		if(strpos($sql,'kboard_board_setting')!==false) $row=$this->boards[$id] ?? null;
		else if(strpos($sql,'kboard_comments')!==false) $row=$this->comments[$id] ?? null;
		else $row=$this->rows[$id] ?? null;
		return $row ? clone (object)$row : null;
	}
	public function get_results($sql){
		$this->queries[]=$sql;
		$result=array();
		if(strpos($sql,'kboard_board_meta')!==false){
			foreach($this->meta[$this->id($sql,'board_id')] ?? array() as $key=>$value) $result[]=(object)array('key'=>$key,'value'=>$value);
		}
		else if(strpos($sql,'kboard_board_option')!==false){
			foreach($this->options[$this->id($sql,'content_uid')] ?? array() as $key=>$value) $result[]=(object)array('option_key'=>$key,'option_value'=>$value);
		}
		return $result;
	}
	public function get_var($sql){
		$this->queries[]=$sql;
		if(strpos($sql,'SELECT `uid`')!==false || strpos($sql,'SELECT `content`.`uid`')!==false){
			$matches=array();
			foreach($this->rows as $id=>$row){
				if((string)$row['board_id']!==(string)$this->id($sql,'board_id')) continue;
				$user=$this->id($sql,'member_uid');
				if($user!==null && (string)$row['member_uid']!==(string)$user) continue;
				$ip=$this->id($sql,'option_value');
				if($ip!==null && ($this->options[$id]['ip'] ?? null)!==$ip) continue;
				if(($row['status'] ?? '')==='trash') continue;
				$matches[$id]=$row['date'];
			}
			arsort($matches); return key($matches);
		}
		if(strpos($sql,'kboard_board_option')!==false) return isset($this->options[$this->id($sql,'content_uid')][$this->id($sql,'option_key')]) ? 1 : 0;
		return 0;
	}
	public function query($sql){
		$this->queries[]=$sql;
		if(preg_match('/INSERT INTO `wp_kboard_board_meta`.*VALUES \(\x27(\d+)\x27, \x27([^\x27]+)\x27, \x27((?:\\\\.|[^\x27])*)\x27\)/', $sql, $m)) $this->meta[$m[1]][$m[2]]=stripslashes($m[3]);
		if(preg_match('/^UPDATE `wp_kboard_board_content` SET (.+) WHERE `uid`=\x27(\d+)\x27/s', $sql, $match)){
			preg_match_all('/`([^`]+)`=\x27((?:\\\\.|[^\x27])*)\x27/', $match[1], $values, PREG_SET_ORDER);
			foreach($values as $pair) $this->rows[$match[2]][$pair[1]]=stripslashes($pair[2]);
		}
		if(preg_match('/INSERT INTO `wp_kboard_board_option`.*VALUES \(\x27(\d+)\x27, \x27([^\x27]+)\x27, \x27([^\x27]*)\x27\)/', $sql, $m)) $this->options[$m[1]][$m[2]]=stripslashes($m[3]);
		if(preg_match('/UPDATE `wp_kboard_board_option` SET `option_value`=\x27([^\x27]*)\x27 WHERE `content_uid`=\x27(\d+)\x27 AND `option_key`=\x27([^\x27]+)\x27/', $sql, $m)) $this->options[$m[2]][$m[3]]=stripslashes($m[1]);
		return 1;
	}
}
$wpdb = new TestDatabase();
$wpdb->boards[1]=array('uid'=>1,'permission_write'=>'author','permission_read'=>'all','admin_user'=>'manager');
$wpdb->boards[2]=array('uid'=>2,'permission_write'=>'roles','permission_read'=>'all','admin_user'=>'manager');
$wpdb->meta[1]=array('new_document_delay'=>50, 'permission_write_roles'=>serialize(array('administrator')), 'permission_comment_write'=>'roles', 'permission_comment_write_roles'=>serialize(array('editor')), 'skin_fields'=>serialize(array('title'=>array('field_type'=>'title','meta_key'=>'title'))));
$wpdb->meta[2]=$wpdb->meta[1];
$wpdb->rows[100]=array('uid'=>100,'board_id'=>1,'member_uid'=>10,'member_display'=>'Member','title'=>'Normal','content'=>'Normal body','date'=>gmdate('YmdHis',$GLOBALS['test_now']-1200),'status'=>'','secret'=>'','notice'=>'','password'=>'$kboard_password$v1$stored-hash','search'=>3,'parent_uid'=>0);
$wpdb->rows[200]=$wpdb->rows[100]; $wpdb->rows[200]['uid']=200; $wpdb->rows[200]['member_uid']=20;
$wpdb->comments[300]=array('uid'=>300,'content_uid'=>100,'user_uid'=>10,'user_display'=>'Member','content'=>'Normal','password'=>'$kboard_password$v1$stored-hash','created'=>'20261029110000');
require KBOARD_DIR_PATH.'/helper/Functions.helper.php';
require KBOARD_DIR_PATH.'/helper/Security.helper.php';
foreach(array('KBoardMeta','KBoardTreeCategory','KBoardFields','KBoard','KBContentOption','KBContent','KBCaptcha','KBController') as $class) require KBOARD_DIR_PATH.'/class/'.$class.'.class.php';
foreach(array('KBCommentOption','KBComment','KBCommentsBuilder','KBCommentController') as $class) require $source.'/kboard-comments/class/'.$class.'.class.php';
function guest(){ $GLOBALS['test_user']->ID=0; $GLOBALS['test_user']->roles=array(); }
function admin(){ $GLOBALS['test_user']->ID=1; $GLOBALS['test_user']->roles=array('administrator'); }
function deny($callback, $needle='You do not have permission.'){
	$GLOBALS['test_shutdown']=function($body) use($needle){ check(strpos($body,$needle)!==false, 'Expected denial: '.$needle.'; got '.$body); };
	$callback(); throw new RuntimeException('Expected request to stop');
}
function boundary($callback, $name){
	try{ $callback(); } catch(TestBoundary $e){ check($e->getMessage()===$name, 'Expected '.$name.', got '.$e->getMessage()); return $e->data; }
	throw new RuntimeException('Boundary not reached: '.$name);
}
function editor(){ $_POST+=array('kboard-editor-execute-nonce'=>'valid:kboard-editor-execute','board_id'=>1,'title'=>'Normal','kboard_content'=>'Normal body','password'=>'guest-pass','member_uid'=>10,'date'=>'20261029114000'); (new KBController())->editorExecute(); }
function ajax($data){ $_POST=array('content_uid'=>100,'security'=>'valid:kboard_ajax_security','data'=>$data); return boundary(function(){(new KBController())->contentUpdate();},'json'); }
function commentInsert(){ $_POST+=array('content_uid'=>100,'kboard-comments-execute-nonce'=>'valid:kboard-comments-execute-100','content'=>'Normal','member_display'=>'Visitor','password'=>'guest-pass'); (new KBCommentController())->insert(); }
function commentChange($action){ $_GET['uid']=300; $_POST+=array('content'=>'Edited','kboard-comments-'.$action.'-nonce'=>'valid:kboard-comments-'.$action.'-300'); $_REQUEST=$_POST; (new KBCommentController())->$action(); }

try{
	$case=$argv[1];
	if(strpos($case,'recaptcha-')===0){
		guest(); $GLOBALS['test_options']+=array('kboard_recaptcha_type'=>'v2','kboard_recaptcha_site_key'=>'test-site','kboard_recaptcha_secret_key'=>'test-secret');
		$_POST['g-recaptcha-response']='test-token';
		if($case==='recaptcha-missing') unset($_POST['g-recaptcha-response']);
		if($case==='recaptcha-array') $_POST['g-recaptcha-response']=array('token');
		if($case==='recaptcha-no-fallback'){ $GLOBALS['test_verify_response']=array('body'=>'{"success":false}'); $_SESSION['kboard_captcha']=array('ABCDE'); $_POST['captcha']='ABCDE'; }
		if($case==='recaptcha-network-error') $GLOBALS['test_verify_response']=new TestNetworkError();
		if(strpos($case,'recaptcha-v3-')===0){
			$GLOBALS['test_options']['kboard_recaptcha_type']='v3';
			$GLOBALS['test_verify_response']=array('body'=>json_encode(array('success'=>true,'action'=>$case==='recaptcha-v3-wrong-action'?'other':'kboard_submit','score'=>$case==='recaptcha-v3-low-score'?0.1:0.8)));
		}
		check((new KBCaptcha())->validate()===in_array($case,array('recaptcha-valid','recaptcha-v3-valid'),true));
	}
	else if($case==='captcha-generation-failure'){
		guest(); (new KBCaptcha())->createImage(); $_POST['captcha']='ERROR'; check(!(new KBCaptcha())->validate());
	}
	else if(in_array($case,array('captcha-valid','captcha-wrong','captcha-missing','captcha-no-session','captcha-array','captcha-single-use','captcha-error-answer','captcha-login-exempt'),true)){
		if($case!=='captcha-login-exempt') guest();
		$_SESSION['kboard_captcha']=array('ABCDE'); $_POST['captcha']='abcde';
		if($case==='captcha-wrong') $_POST['captcha']='wrong';
		if($case==='captcha-missing' || $case==='captcha-login-exempt') unset($_POST['captcha']);
		if($case==='captcha-no-session') unset($_SESSION['kboard_captcha']);
		if($case==='captcha-array') $_POST['captcha']=array('ABCDE');
		if($case==='captcha-error-answer'){$_SESSION['kboard_captcha']=array('ERROR');$_POST['captcha']='ERROR';}
		$captcha=new KBCaptcha(); $valid=$captcha->validate();
		check($valid===in_array($case,array('captcha-valid','captcha-single-use','captcha-login-exempt'),true));
		if($case==='captcha-single-use') check(!$captcha->validate());
	}
	else if($case==='fields-unbound' || $case==='fields-integer'){
		check(isset((new KBoardFields($case==='fields-integer'?1:false))->getSkinFields()['title']));
	}
	else if(in_array($case,array('captcha-missing-skin','captcha-missing-skin-valid','captcha-disabled','captcha-field-fallback','captcha-field-filter'),true)){
		guest(); $wpdb->boards[1]['permission_write']='all'; $wpdb->meta[1]['new_document_delay']='';
		$GLOBALS['test_options']['kboard_captcha_stop']=$case==='captcha-disabled'?1:0;
		if($case==='captcha-field-fallback' || $case==='captcha-field-filter'){
			if($case==='captcha-field-filter') $GLOBALS['test_filters']['kboard_skin_fields'][]=function($fields){unset($fields['captcha']); return $fields;};
			check(isset((new KBoard(1))->fields()->getSkinFields()['captcha']));
		}
		else if($case==='captcha-missing-skin') deny('editor','CAPTCHA is invalid.');
		else{ if($case==='captcha-missing-skin-valid'){$_SESSION['kboard_captcha']=array('ABCDE');$_POST['captcha']='ABCDE';} boundary('editor','kboard_pre_document_insert'); }
	}
	else if(strpos($case,'ip-')===0){
		if($case==='ip-spoof'){$_SERVER['HTTP_CLIENT_IP']='198.51.100.8';$_SERVER['HTTP_X_FORWARDED_FOR']='203.0.113.8';check(kboard_user_ip()==='192.0.2.10');}
		else if($case==='ip-ipv6'){$_SERVER['REMOTE_ADDR']='2001:db8::1';check(kboard_user_ip()==='2001:db8::1');}
		else if($case==='ip-trusted-filter'){$GLOBALS['test_filters']['kboard_user_ip'][]=function($ip){return '198.51.100.5';};check(kboard_user_ip()==='198.51.100.5');}
		else{
			$content=new KBContent();$content->uid=100;$content->execute_action=$case==='ip-update-preserved'?'update':'insert';
			if($case==='ip-update-preserved') $wpdb->options[100]['ip']='198.51.100.4';
			$_POST=$case==='ip-option-omitted'?array():array('kboard_option_ip'=>'203.0.113.9');$content->updateOptions();
			check(($wpdb->options[100]['ip']??null)===($case==='ip-update-preserved'?'198.51.100.4':'192.0.2.10'));
		}
	}
	else if(strpos($case,'delay-')===0){
		unset($wpdb->rows[200]);
		if(strpos($case,'guest')!==false || $case==='delay-shared-ip-member'){guest();$wpdb->boards[1]['permission_write']='all';$wpdb->options[100]['ip']='192.0.2.10';if($case!=='delay-shared-ip-member')$wpdb->rows[100]['member_uid']=0;}
		if(strpos($case,'-50')!==false) $wpdb->rows[100]['date']=gmdate('YmdHis',$GLOBALS['test_now']-3000);
		if($case==='delay-admin-exempt') admin();
		if($case==='delay-other-account') $wpdb->rows[100]['member_uid']=20;
		if($case==='delay-other-board') $wpdb->rows[100]['board_id']=2;
		if($case==='delay-edit-exempt') $_POST['uid']=100;
		if(in_array($case,array('delay-member-20','delay-guest-20'),true)) deny('editor','You can create a new post after');
		else boundary('editor',$case==='delay-edit-exempt'?'kboard_pre_document_update':'kboard_pre_document_insert');
	}
	else if(strpos($case,'filter-')===0){
		$GLOBALS['test_options']['kboard_content_filter']='SPAM, casino.com, 카지노';
		$values=array('filter-title'=>'SPAM','filter-html'=>'카<strong>지</strong>노','filter-case'=>'SpAm','filter-space'=>"S\tP A\nM",'filter-entity'=>'&#83;PAM','filter-url'=>'%53PAM','filter-domain'=>'CASINO.COM','filter-zero-width'=>"S\u{200B}PAM",'filter-clean'=>'A normal sentence','filter-admin-exempt'=>'SPAM','filter-message-hook'=>'SPAM','filter-empty-message'=>'SPAM','filter-link-domain'=>'<a href="https://casino.com">visit</a>','filter-image-domain'=>'<img src="https://casino.com/banner.png">');
		if($case==='filter-settings-preserved') check(kboard_content_filter()==='SPAM, casino.com, 카지노');
		else{
			$wpdb->meta[1]['new_document_delay']='';
			if($case==='filter-admin-exempt') admin();
			if($case==='filter-message-hook') $GLOBALS['test_filters']['kboard_content_filter_message'][]=function($message,$word,$subject,$board){check($board instanceof KBoard);return 'custom-block';};
			if($case==='filter-empty-message') $GLOBALS['test_filters']['kboard_content_filter_message'][]=function($message){return '';};
			if($case==='filter-title') $_POST['title']=$values[$case]; else $_POST['kboard_content']=$values[$case];
			if(in_array($case,array('filter-clean','filter-admin-exempt'),true)) boundary('editor','kboard_pre_document_insert');
			else deny('editor',$case==='filter-message-hook'?'custom-block':($case==='filter-empty-message'?'<script>alert(':'is not available.'));
		}
	}
	else if(strpos($case,'writer-')===0){
		$wpdb->meta[1]['new_document_delay']=$wpdb->meta[2]['new_document_delay']=''; $_POST['board_id']=2;
		if($case==='writer-roles-admin') admin();
		if($case==='writer-designated-admin') $GLOBALS['test_user']->user_login='manager';
		if($case==='writer-member-allowed') $_POST['board_id']=1;
		if($case==='writer-roles-denied') deny('editor'); else boundary('editor','kboard_pre_document_insert');
	}
	else if(strpos($case,'editor-')===0){
		$_POST['uid']=100;
		if($case==='editor-owner-spoof'){$_POST['uid']=200;$_POST['member_uid']=10;deny('editor');}
		else if($case==='editor-board-spoof'){$_POST['board_id']=2;deny('editor');}
		else if($case==='editor-missing-uid'){$_POST['uid']=999;deny('editor');}
		else{
			if(strpos($case,'editor-notice-')===0){
				$permission=$case==='editor-notice-author'?'author':'roles';
				$roles=$case==='editor-notice-denied'?array('editor'):array('subscriber');
				$wpdb->meta[1]['skin_fields']=serialize(array('option'=>array('notice_permission'=>$permission,'notice'=>$roles)));
				$_POST['notice']='true';
			}
			if($case==='editor-empty-date') $_POST['date']='';
			if($case==='editor-no-member-reassignment') $_POST['member_uid']=999;
			if($case==='editor-moderation-preserved'){ $wpdb->rows[100]['status']='pending_approval'; $_POST['status']=''; $_POST['notice']='true'; }
			if(strpos($case,'editor-guest-')===0){guest();$wpdb->boards[1]['permission_write']='all';$wpdb->rows[100]['member_uid']=0;$_POST['password']=$case==='editor-guest-password'?'guest-pass':'wrong';}
			if($case==='editor-guest-wrong-password') deny('editor');
			else{ $args=boundary('editor','kboard_pre_document_update'); check($args[2]->member_uid===$wpdb->rows[100]['member_uid']);check($args[2]->date===$wpdb->rows[100]['date']); if($case==='editor-moderation-preserved'){check($args[2]->status==='pending_approval');check(!$args[2]->notice);} if(strpos($case,'editor-notice-')===0)check((bool)$args[2]->notice===($case!=='editor-notice-denied')); }
		}
	}
	else if(strpos($case,'ajax-')===0){
		if($case==='ajax-other-owner'){$GLOBALS['test_user']->ID=30;$reply=ajax(array('title'=>'Other'));check($reply['result']==='error');}
		else if($case==='ajax-banned-content'){$GLOBALS['test_options']['kboard_content_filter']='SPAM';$reply=ajax(array('content'=>'spam'));check($reply['result']==='error');}
		else{
			if($case==='ajax-admin-fields') admin();
			if($case==='ajax-notice-role') $wpdb->meta[1]['skin_fields']=serialize(array('option'=>array('notice_permission'=>'roles','notice'=>array('subscriber'))));
			$data=$case==='ajax-management-fields'?array('board_id'=>2,'member_uid'=>20,'date'=>'20000101000000','status'=>'','notice'=>'true'):array('category2'=>'done','kboard_option_custom'=>'saved');
			if($case==='ajax-admin-fields') $data=array('member_uid'=>20,'date'=>'20000101000000','notice'=>'true');
			if($case==='ajax-notice-role') $data=array('notice'=>'true');
			if($case==='ajax-management-fields') $wpdb->rows[100]['status']='pending_approval';
			if($case==='ajax-forced-secret'){ $wpdb->meta[1]['secret_checked_forced']=1; $data=array('secret'=>''); }
			// The controller's pre-update hook is observable; let storage continue for this API.
			$GLOBALS['test_allow_update']=true;
			$reply=ajax($data); check($reply['result']==='success');
			if($case==='ajax-management-fields'){check($wpdb->rows[100]['board_id']==1);check($wpdb->rows[100]['member_uid']==10);check($wpdb->rows[100]['date']==='20261029114000');check($wpdb->rows[100]['status']==='pending_approval');check(!$wpdb->rows[100]['notice']);}
			if($case==='ajax-category-option'){check($wpdb->rows[100]['category2']==='done');check($wpdb->options[100]['custom']==='saved');}
			if($case==='ajax-forced-secret') check($wpdb->rows[100]['secret']==='true');
			if($case==='ajax-admin-fields'){check($wpdb->rows[100]['member_uid']==20);check($wpdb->rows[100]['date']==='20000101000000');check($wpdb->rows[100]['notice']==='true');}
			if($case==='ajax-notice-role') check($wpdb->rows[100]['notice']==='true');
		}
	}
	else if(strpos($case,'comment-')===0){
		if(in_array($case,array('comment-edit-owner','comment-edit-other','comment-delete-owner','comment-delete-other','comment-edit-password','comment-update-filter'),true)){
			$action=strpos($case,'delete')!==false?'delete':'update';
			if(strpos($case,'other')!==false) $GLOBALS['test_user']->ID=20;
			if($case==='comment-edit-password'){ guest();$_POST['password']='guest-pass';$_POST['kboard-comments-update-nonce']='valid:kboard-comments-update-$kboard_password$v1$stored-hash'; }
			if($case==='comment-update-filter'){ $GLOBALS['test_options']['kboard_content_filter']='SPAM';$_POST['content']='spam'; }
			if(strpos($case,'other')!==false || $case==='comment-update-filter') deny(function() use($action){commentChange($action);},$case==='comment-update-filter'?'is not available.':'You do not have permission.');
			else boundary(function() use($action){commentChange($action);},'kboard_comments_pre_'.$action);
		}
		else{
			if($case==='comment-role-allowed' || $case==='comment-filter-denied' || $case==='comment-filter-context' || $case==='comment-global-preserved') $GLOBALS['test_user']->roles=array('editor');
			if($case==='comment-admin') admin();
			if(strpos($case,'comment-guest-')===0) guest();
			if($case==='comment-guest-allowed') $wpdb->meta[1]['permission_comment_write']='';
			if($case==='comment-filter-denied') $GLOBALS['test_filters']['kboard_comments_is_writer'][]=function($allowed,$builder){return false;};
			if($case==='comment-filter-context') $GLOBALS['test_filters']['kboard_comments_is_writer'][]=function($allowed,$builder){check($builder instanceof KBCommentsBuilder && $builder->board_id==1 && $builder->content_uid==100);return $allowed;};
			$original=(object)array('sentinel'=>true);$GLOBALS['kboard_comment_builder']=$original;
			if(in_array($case,array('comment-role-denied','comment-guest-denied','comment-filter-denied'),true)) deny('commentInsert');
			else {boundary('commentInsert','kboard_comments_pre_insert');if($case==='comment-global-preserved')check($GLOBALS['kboard_comment_builder']===$original);}
		}
	}
	else if($case==='permission-settings-roundtrip'){
		$meta=new KBoardMeta(2);$meta->permission_write_roles=serialize(array('editor'));
		// Exercise the real setter and loader against persisted role serialization.
		check((new KBoard(2))->getWriteRoles()===array('editor'));
		check(!(new KBoard(2))->isWriter()); admin();check((new KBoard(2))->isWriter());
	}
	else if($case==='permission-settings-display'){
		$html=file_get_contents(KBOARD_DIR_PATH.'/pages/kboard_setting.php');
		preg_match('/<select name="permission_write".*?<\/select>/s',$html,$match);
		check(strpos($match[0],"\$board->permission_write == 'all'")!==false);
		check(strpos($match[0],'permission_read')===false);
	}
	else throw new RuntimeException('Unknown test '.$case);
	$GLOBALS['test_result']=true;
}
catch(Throwable $e){ echo get_class($e).': '.$e->getMessage()."\n".$e->getTraceAsString(); }

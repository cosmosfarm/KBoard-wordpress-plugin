<?php
/**
 * KBoard 업그레이더
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
final class KBUpgrader {
	
	static private $instance;
	static private $latest_version;
	static private $sever_host = 'www.cosmosfarm.com';
	
	static $CONNECT_LOGIN_STATUS = 'http://www.cosmosfarm.com/accounts/loginstatus';
	static $CONNECT_VERSION = 'http://www.cosmosfarm.com/wpstore/kboard/version';
	static $CONNECT_KBOARD = 'http://www.cosmosfarm.com/wpstore/kboard/getkboard';
	static $CONNECT_COMMENTS = 'http://www.cosmosfarm.com/wpstore/kboard/getcomments';
	
	static $TYPE_PLUGINS = '/plugins';
	static $TYPE_THEMES = '/themes';
	static $TYPE_KBOARD_SKIN = '/plugins/kboard/skin';
	static $TYPE_COMMENTS_SKIN = '/plugins/kboard-comments/skin';
	
	private function __construct(){
		
	}
	
	/**
	 * 인스턴스를 반환한다.
	 * @return KBUpgrader
	 */
	static public function getInstance(){
		if(!self::$instance) self::$instance = new KBUpgrader();
		return self::$instance;
	}
	
	/**
	 * 서버에 접속한다.
	 * @param string $url
	 * @return object
	 */
	static public function connect($url){
		$response = wp_remote_get($url, array('headers'=>array('Referer'=>$_SERVER['HTTP_HOST']), 'sslverify'=>false));
		
		if(is_wp_error($response) || !isset($response['body']) || !$response['body']){
			$data = new stdClass();
			$data->kboard = 0;
			$data->comments = 0;
			$data->error = $response->get_error_message();
		}
		else{
			$data = json_decode($response['body']);
			$data->error = '';
		}
		
		return $data;
	}
	
	/**
	 * 서버에서 최신버전을 가져온다.
	 * @return string
	 */
	static public function getLatestVersion(){
		if(self::$latest_version){
			return self::$latest_version;
		}
		else if(isset($_SESSION['kboard_latest_version']) && $_SESSION['kboard_latest_version']){
			self::$latest_version = $_SESSION['kboard_latest_version'];
		}
		else if(!self::$latest_version){
			self::$latest_version = self::connect(self::$CONNECT_VERSION.'?version='.KBOARD_VERSION);
		}
		$_SESSION['kboard_latest_version'] = self::$latest_version;
		return self::$latest_version;
	}
	
	/**
	 * 패키지 파일을 다운받는다.
	 * @param string $package
	 * @param string $version
	 * @param string $access_token
	 * @return string
	 */
	public function download($package, $version, $access_token){
		// 로컬에 있는 파일인지 확인한다.
		if(!preg_match('!^(http|https|ftp)://!i', $package) && file_exists($package)){
			return $package;
		}
		
		$download_file = download_url($package.'?host='.$_SERVER['HTTP_HOST'].'&version='.$version.'&app_id='.KBOARD_WORDPRESS_APP_ID.'&access_token='.$access_token);
		
		if(is_wp_error($download_file)){
			die('<script>alert("'.__('Unable to connect to the update server, Cosmosfarm account please connect again.', 'kboard').'");history.go(-1);</script>');
		}
		
		return $download_file;
	}
	
	/**
	 * 패키지 파일의 압축을 풀고 설치한다.
	 * @param string $package
	 * @param string $content_type
	 * @param string $delete_package
	 * @return string
	 */
	public function install($package, $content_type, $delete_package=true){
		WP_Filesystem();
		
		$destination_path= trailingslashit(WP_CONTENT_DIR . $content_type);
		$unzipfile = unzip_file($package, $destination_path);
		
		if($delete_package) unlink($package);
		
		if(!$unzipfile){
			die('<script>alert("'.__('There was an error unzipping the file.', 'kboard').'");history.go(-1);</script>');
		}
		
		return '';
	}
	
	/**
	 * 워드프레스 Filesystem을 초기화 한다.
	 * @param string $form_url
	 * @param string $path
	 * @param string $method
	 * @param string $fields
	 * @return boolean
	 */
	function credentials($form_url, $path, $method='', $fields=null){
		global $wp_filesystem;
		
		if(is_writable($path)){
			return true;
		}
		if(false === ($creds = request_filesystem_credentials($form_url, $method, false, $path, $fields))){
			return false;
		}
		if(!WP_Filesystem($creds)){
			request_filesystem_credentials($form_url, $method, true, $path);
			return false;
		}
		return true;
	}
}
?>
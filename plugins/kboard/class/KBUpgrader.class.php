<?php
/**
 * KBoard 업그레이더
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
final class KBUpgrader {
	
	private static $instance;
	private static $latest_version;
	private static $latest_news;
	
	static $CONNECT_VERSION = 'http://updates.wp-kboard.com/v1/AUTH_3529e134-c9d7-4172-8338-f64309faa5e5/kboard/version.json';
	static $CONNECT_NEWS = 'http://updates.wp-kboard.com/v1/AUTH_3529e134-c9d7-4172-8338-f64309faa5e5/kboard/news.json';
	static $CONNECT_KBOARD = 'http://updates.wp-kboard.com/v1/AUTH_3529e134-c9d7-4172-8338-f64309faa5e5/kboard/kboard-latest.zip';
	static $CONNECT_COMMENTS = 'http://updates.wp-kboard.com/v1/AUTH_3529e134-c9d7-4172-8338-f64309faa5e5/kboard/kboard-comments-latest.zip';
	static $CONNECT_KBOARD_NOSKINS = 'http://updates.wp-kboard.com/v1/AUTH_3529e134-c9d7-4172-8338-f64309faa5e5/kboard/kboard-latest-noskins.zip';
	static $CONNECT_COMMENTS_NOSKINS = 'http://updates.wp-kboard.com/v1/AUTH_3529e134-c9d7-4172-8338-f64309faa5e5/kboard/kboard-comments-latest-noskins.zip';
	
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
	public static function getInstance(){
		if(!self::$instance) self::$instance = new KBUpgrader();
		return self::$instance;
	}
	
	/**
	 * 캐시를 비운다.
	 */
	public static function flush(){
		unset($_SESSION['kboard_latest_version']);
		unset($_SESSION['kboard_latest_news']);
		
		self::$latest_version = '';
		self::$latest_news = '';
	}
	
	/**
	 * 서버에 접속한다.
	 * @param string $url
	 * @return object
	 */
	public static function connect($url){
		$response = wp_remote_get($url);
		
		if(is_wp_error($response) || !isset($response['body']) || !$response['body']){
			echo $response->get_error_message();
			
			return '';
		}
		else{
			return json_decode($response['body']);
		}
	}
	
	/**
	 * 서버에서 최신버전 정보를 가져온다.
	 * @return object
	 */
	public static function getLatestVersion(){
		$version = self::connect(self::$CONNECT_VERSION);
		if(!$version){
			$version = new stdClass();
			$version->kboard = '';
			$version->comments = '';
		}
		self::$latest_version = $version;
		
		if(self::$latest_version){
			return self::$latest_version;
		}
		else if(isset($_SESSION['kboard_latest_version']) && is_object($_SESSION['kboard_latest_version']) && $_SESSION['kboard_latest_version']){
			self::$latest_version = $_SESSION['kboard_latest_version'];
		}
		else if(!self::$latest_version){
			$version = self::connect(self::$CONNECT_VERSION);
			if(!$version){
				$version = new stdClass();
				$version->kboard = '';
				$version->comments = '';
			}
			self::$latest_version = $version;
		}
		$_SESSION['kboard_latest_version'] = self::$latest_version;
		return self::$latest_version;
	}
	
	/**
	 * 서버에서 이벤트 및 뉴스 정보를 가져온다.
	 * @return object
	 */
	public static function getLatestNews(){
		if(self::$latest_news){
			return self::$latest_news;
		}
		else if(isset($_SESSION['kboard_latest_news']) && is_array($_SESSION['kboard_latest_news']) && $_SESSION['kboard_latest_news']){
			self::$latest_news = $_SESSION['kboard_latest_news'];
		}
		else if(!self::$latest_news){
			$news = self::connect(self::$CONNECT_NEWS);
			if(!$news){
				$news = array();
			}
			self::$latest_news = $news;
		}
		$_SESSION['kboard_latest_news'] = self::$latest_news;
		return self::$latest_news;
	}
	
	public function getKBoard(){
		$download_file = download_url(self::$CONNECT_KBOARD);
		if(is_wp_error($download_file)){
			die('<script>alert("업데이트 파일 다운로드에 실패했습니다. 잠시 후 다시 시도해주세요.");history.go(-1);</script>');
		}
		return $download_file;
	}
	
	public function getKBoardNoSkins(){
		$download_file = download_url(self::$CONNECT_KBOARD_NOSKINS);
		if(is_wp_error($download_file)){
			die('<script>alert("업데이트 파일 다운로드에 실패했습니다. 잠시 후 다시 시도해주세요.");history.go(-1);</script>');
		}
		return $download_file;
	}
	
	public function getComments(){
		$download_file = download_url(self::$CONNECT_COMMENTS);
		if(is_wp_error($download_file)){
			die('<script>alert("업데이트 파일 다운로드에 실패했습니다. 잠시 후 다시 시도해주세요.");history.go(-1);</script>');
		}
		return $download_file;
	}
	
	public function getCommentsNoSkins(){
		$download_file = download_url(self::$CONNECT_COMMENTS_NOSKINS);
		if(is_wp_error($download_file)){
			die('<script>alert("업데이트 파일 다운로드에 실패했습니다. 잠시 후 다시 시도해주세요.");history.go(-1);</script>');
		}
		return $download_file;
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
		
		$destination_path = trailingslashit(WP_CONTENT_DIR . $content_type);
		$unzipfile = unzip_file($package, $destination_path);
		
		if($delete_package) unlink($package);
		
		if(is_wp_error($unzipfile)){
			foreach($unzipfile->errors as $code=>$message){
				if(is_array($message)){
					$message = implode(', ', $message);
					echo "<p>{$message} ({$unzipfile->error_data[$code]})</p>";
				}
				else{
					echo "<p>{$message} ({$unzipfile->error_data[$code]})</p>";
				}
			}
			echo '<script>alert("'.$unzipfile->get_error_message().'");</script>';
			exit;
		}
		
		return $unzipfile;
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
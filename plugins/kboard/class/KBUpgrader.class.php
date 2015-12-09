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
	 * @return data
	 */
	static public function connect($url){
		$host = self::$sever_host;
		$fp = @fsockopen($host, 80, $errno, $errstr, 3);
		if($fp){
			$output = '';
			fputs($fp, "GET ".$url." HTTP/1.0\r\n"."Host: $host\r\n"."Referer: ".$_SERVER['HTTP_HOST']."\r\n"."\r\n");
			while(!feof($fp)){
				$output .= fgets($fp, 1024);
			}
			fclose($fp);
			$data = @explode("\r\n\r\n", $output);
			$data = @end($data);
			return json_decode($data);
		}
		else{
			$data->error = __('Unable to connect to Cosmosfarm server.', 'kboard');
			return $data;
		}
	}
	
	/**
	 * 서버에서 최신버전을 가져온다.
	 * @return string
	 */
	static public function getLatestVersion(){
		if(isset($_SESSION['kboard_latest_version']) && $_SESSION['kboard_latest_version']){
			self::$latest_version = $_SESSION['kboard_latest_version'];
		}
		else if(!self::$latest_version){
			$data = self::connect(self::$CONNECT_VERSION.'?version='.KBOARD_VERSION);
			if(isset($data->error) && $data->error){
				echo 'null';
			}
			else{
				self::$latest_version = $data;
			}
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
	public function install($package, $content_type, $delete_package = true){
		// See #15789 - PclZip uses string functions on binary data, If it's overloaded with Multibyte safe functions the results are incorrect.
		if(ini_get('mbstring.func_overload') && function_exists('mb_internal_encoding')){
			$previous_encoding = mb_internal_encoding();
			mb_internal_encoding('ISO-8859-1');
		}
		require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
		
		$archive = new PclZip($package);
		$archive_files = $archive->extract(PCLZIP_OPT_EXTRACT_AS_STRING);
		
		if($delete_package) unlink($package);
		
		if(!$archive_files){
			die('<script>alert("'.__('Download file is decompression failed, please check directory and file permissions.', 'kboard').'");history.go(-1);</script>');
		}
		else{
			$install_result = true;
			
			if(is_writable(WP_CONTENT_DIR . $content_type)){
				$file_handler = new KBFileHandler();
				$target_dir = trailingslashit(WP_CONTENT_DIR . $content_type);
				foreach($archive_files AS $file){
					if('__MACOSX/' === substr($file['filename'], 0, 9)) continue;
					if($file['folder']){
						$install_result = $file_handler->mkPath($target_dir . $file['filename']);
					}
					else{
						$install_result = $file_handler->putContents($target_dir . $file['filename'], $file['content']);
					}
					if(!$install_result) break;
				}
			}
			else{
				global $wp_filesystem;
				$target_dir = trailingslashit($wp_filesystem->find_folder(WP_CONTENT_DIR . $content_type));
				foreach($archive_files as $file){
					if('__MACOSX/' === substr($file['filename'], 0, 9)) continue;
					if($file['folder']){
						if($wp_filesystem->is_dir($target_dir . $file['filename'])) continue;
						else $install_result = $wp_filesystem->mkdir($target_dir . $file['filename'], FS_CHMOD_DIR);
					}
					else{
						$install_result = $wp_filesystem->put_contents($target_dir . $file['filename'], $file['content'], FS_CHMOD_FILE);
					}
					if(!$install_result) break;
				}
			}
			if(!$install_result){
				die('<script>alert("'.__('File copy failed, directory requires write permission.', 'kboard').' (/wp-content'.$content_type.')");history.go(-1);</script>');
			}
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
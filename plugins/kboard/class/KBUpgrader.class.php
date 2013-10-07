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
	static private $sever_host = 'cosmosfarm.com';
	
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
		if($_SESSION['kboard_latest_version']){
			self::$latest_version = $_SESSION['kboard_latest_version'];
		}
		else if(!self::$latest_version){
			$data = self::connect(self::$CONNECT_VERSION.'?version='.KBOARD_VERSION);
			if($data->error){
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
		//로컬에 있는 파일인지 확인한다.
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
		$file_handler = new KBFileHandler();
		$upgrade_folder = WP_CONTENT_DIR . '/upgrade/';
		$upgrade_files = $file_handler->getDirlist($upgrade_folder);
		$working_dir = $upgrade_folder . basename($package, '.zip');
		
		foreach($upgrade_files as $file){
			$file_handler->delete($upgrade_folder . $file);
		}
		
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
			$file_handler->delete($working_dir);
			die('<script>alert("'.__('Download file is decompression failed, please check directory and file permissions.', 'kboard').'");history.go(-1);</script>');
		}
		else{
			$extract_result = true;
			foreach($archive_files AS $file){
				if($file['folder']){
					$extract_result = $file_handler->mkPath($working_dir . '/' . $file['filename']);
				}
				else{
					$extract_result = $file_handler->putContents($working_dir . '/' . $file['filename'], $file['content']);
				}
			}
			
			if(!$extract_result){
				$file_handler->delete($working_dir);
				die('<script>alert("'.__('File copy failed, directory requires write permission.', 'kboard').' (/wp-content/upgrade)");history.go(-1);</script>');
			}
			
			$copy_result = $file_handler->copy($working_dir, WP_CONTENT_DIR . $content_type);
			
			if(!$copy_result){
				$file_handler->delete($working_dir);
				die('<script>alert("'.__('File copy failed, directory requires write permission.', 'kboard').' (/wp-content'.$content_type.')");history.go(-1);</script>');
			}
		}
		
		return $working_dir;
	}
}
?>
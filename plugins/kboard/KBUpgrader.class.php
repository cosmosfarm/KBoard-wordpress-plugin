<?php
/**
 * 업그레이드
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
final class KBUpgrader {
	
	private static $LATEST_VERSIOIN;
	static $KBOARD_SERVER_URL = 'http://www.cosmosfarm.com/data/latest/kboard.zip';
	static $KBOARD_COMMENTS_SERVER_URL = 'http://www.cosmosfarm.com/data/latest/kboard.zip';
	
	/**
	 * 서버에서 최신버전을 가져온다.
	 * @return string
	 */
	static function getLatestVersion(){
		if(!self::$LATEST_VERSIOIN){
			
			$host = 'cosmosfarm.com';
			$url = 'http://www.cosmosfarm.com/wpstore/kboard/version';
				
			$fp=fsockopen($host, 80, &$errno, &$errstr, 30);
			fputs($fp, "GET ".$url." HTTP/1.0\r\n"."Host: $host\r\n"."User-Agent: Web 0.1\r\n"."\r\n");
			while(!feof($fp)){
				$output .= fgets($fp, 1024);
			}
			fclose($fp);
			
			$data = @explode("\r\n\r\n", $output);
			$data = @end($data);
			if($output) self::$LATEST_VERSIOIN = json_decode($data);
		}
		return self::$LATEST_VERSIOIN;
	}
	
	/**
	 * 패키지 파일을 다운받는다.
	 * @param string $package
	 * @return string
	 */
	public function download($package){
		//Local file or remote?
		if (!preg_match('!^(http|https|ftp)://!i', $package) && file_exists($package)){
			return $package; //must be a local file..
		}
		
		$download_file = download_url($package);
		
		if(is_wp_error($download_file)){
			die('<script>alert("자동업데이트 실패 : 서버 접속 실패, 잠시 후 다시 시도해 주세요.");history.go(-1);</script>');
		}
		
		return $download_file;
	}
	
	/**
	 * 패키지 파일의 압축을 풀고 설치한다.
	 * @param string $package
	 * @param string $delete_package
	 * @return string
	 */
	public function install($package, $delete_package = true){
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
			die('<script>alert("자동업데이트 실패 : 압축 해제 실패, 디렉토리 권한을 확인하세요.");history.go(-1);</script>');
		}
		else{
			foreach($archive_files AS $file){
				if($file['folder']){
					$file_handler->mkPath($working_dir . '/' . $file['filename']);
				}
				else{
					$file_handler->putContents($working_dir . '/' . $file['filename'], $file['content']);
				}
			}
		}
		$file_handler->copy($working_dir, WP_CONTENT_DIR . '/plugins/test/');
		
		return $working_dir;
	}
}
?>
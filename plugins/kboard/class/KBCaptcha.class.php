<?php
/**
 * KBoard Captcha
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCaptcha {
	
	/**
	 * Captcha 이미지를 생성한다.
	 */
	public function createImage(){
		if(!isset($_SESSION['kboard_captcha'])) $_SESSION['kboard_captcha'] = array();
		
		$captcha_folder = WP_CONTENT_DIR.'/uploads/kboard_captcha/';
		$captcha_name = uniqid('captcha_').'.png';
		
		// 디렉토리 생성
		wp_mkdir_p($captcha_folder);
		
		// 1시간이 지난 이미지는 삭제한다.
		$file_handler = new KBFileHandler();
		$captcha_files = $file_handler->getDirlist($captcha_folder);
		foreach($captcha_files as $file){
			$filetime = @filemtime($captcha_folder . $file);
			$created = (time() - $filetime) / 60 / 60;
			if($created > 1) $file_handler->delete($captcha_folder . $file);
		}
		
		$text = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		shuffle($text);
		$text = substr(implode('', $text), 0, 5);
		
		$image = imagecreate(50, 20);
		$background_color = imagecolorallocate($image, 255, 255, 255);
		$font_color = imagecolorallocate($image, 194, 51, 21);
		
		imagestring($image, 5, 2, 2, $text, $font_color);
		imageline($image, 0, 0, 50, 20, $font_color);
		@imagepng($image, $captcha_folder . $captcha_name);
		imagedestroy($image);
		
		if(file_exists($captcha_folder . $captcha_name)){
			$_SESSION['kboard_captcha'][] = $text;
			$src = content_url('/uploads/kboard_captcha/' . $captcha_name);
		}
		else{
			$_SESSION['kboard_captcha'][] = 'ERROR';
			$src = KBOARD_URL_PATH . '/images/captcha-error.png';
		}
		
		return $src;
	}
	
	/**
	 * 저장된 Captcha 문자와 비교한다.
	 * @param string $text
	 * @return boolean
	 */
	public function textCheck($text){
		global $user_ID;
		$userdata = get_userdata($user_ID);
		
		if($userdata->data->ID){
			return true;
		}
		else if(!isset($_SESSION['kboard_captcha'])){
			return true;
		}
		else if(in_array(strtoupper($text), $_SESSION['kboard_captcha'])){
			unset($_SESSION['kboard_captcha']);
			return true;
		}
		return false;
	}
}
?>
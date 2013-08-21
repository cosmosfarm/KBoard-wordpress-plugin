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
		$font = KBOARD_DIR_PATH . '/font/NanumGothic.ttf';
		$size = 12;
		$text = array('가', '나', '다', '라', '마', '바', '사', '아', '자', '차', '카', '타', '파', '하');
		shuffle($text);
		$text = substr(implode($text), 0, 15);
		
		$image = imagecreate(80 , 20);
		$background_color = imagecolorallocate($image, 255, 255, 255);
		$font_color = imagecolorallocate($image, 138, 138, 138);
		
		imagettftext($image, $size, 0, 2, 14, $font_color, $font, $text);
		header('content-type: image/png');
		imagepng($image);
		imagedestroy($image);
		
		$_SESSION['kboard_captcha'] = $text;
	}
	
	/**
	 * 저장된 Captcha 문자와 비교한다.
	 * @param string $text
	 * @return boolean
	 */
	public function textCheck($text){
		global $user_ID;
		
		if($user_ID){
			return true;
		}
		else if(!$_SESSION['kboard_captcha']){
			return true;
		}
		else if($_SESSION['kboard_captcha'] == $text){
			return true;
		}
		return false;
	}
}
?>
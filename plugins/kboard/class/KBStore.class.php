<?php
/**
 * KBoard 스토어
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBStore {
	
	/**
	 * 상품 리스트 페이지
	 */
	public static function productsList(){
		if(isset($_GET['access_token']) && $_GET['access_token']){
			$_SESSION['kboard_access_token'] = sanitize_text_field($_GET['access_token']);
		}
		
		$category = isset($_GET['kbstore_category']) ? sanitize_text_field($_GET['kbstore_category']) : ''; 
		
		include KBOARD_DIR_PATH . '/pages/kboard_store.php';
	}
	
	/**
	 * 액세스 토큰을 반환한다.
	 * @return string
	 */
	public static function getAccessToken(){
		if(isset($_SESSION['kboard_access_token'])){
			return $_SESSION['kboard_access_token'];
		}
		return '';
	}
}
?>
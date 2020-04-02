<?php
/**
 * KBoard 스토어
 * @link www.cosmosfarm.com
 * @copyright Copyright 2020 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBStore {
	
	/**
	 * 상품 리스트 페이지
	 */
	public static function productsList(){
		$category = isset($_GET['kbstore_category']) ? sanitize_text_field($_GET['kbstore_category']) : ''; 
		include KBOARD_DIR_PATH . '/pages/kboard_store.php';
	}
	
	/**
	 * 액세스 토큰을 반환한다.
	 * @return string
	 */
	public static function getAccessToken(){
		if(isset($_COOKIE['kboard_access_token'])){
			return sanitize_text_field($_COOKIE['kboard_access_token']);
		}
		return '';
	}
}
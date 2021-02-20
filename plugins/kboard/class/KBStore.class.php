<?php
/**
 * KBoard 스토어
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
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
	
	/**
	 * 로그인된 회원 정보를 반환한다.
	 * @return object
	 */
	public static function getMyProfile(){
		$profile = (object) array('uid'=>'', 'created'=>'', 'email'=>'', 'username'=>'');
		$access_token = self::getAccessToken();
		
		if($access_token){
			$args = array();
			$args['method'] = 'GET';
			$args['timeout'] = '15';
			$args['body'] = array(
				'app_id' => KBOARD_WORDPRESS_APP_ID,
				'access_token' => $access_token,
				'ip' => kboard_user_ip(),
			);
			
			$response = wp_remote_request('https://www.cosmosfarm.com/apis/v2_me', $args);
			
			if(is_wp_error($response) || !isset($response['body']) || !$response['body']){
				echo $response->get_error_message();
			}
			else{
				$profile = json_decode($response['body'])->profile;
			}
		}
		return $profile;
	}
}
<?php
/**
 * KBoard Captcha
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCaptcha {

	/** ALTCHA v3 challenge, using the KBoard session for single-use verification. */
	public function createAltchaChallenge(){
		if(!session_id()) return false;
		$cost = intval(get_option('kboard_altcha_cost', 5000));
		if($cost === 2000) $cost = 5000;
		if(!in_array($cost, array(500, 5000, 10000), true)) $cost = 5000;
		$minutes = intval(get_option('kboard_altcha_expiry', 30));
		if(!in_array($minutes, array(10, 20, 30, 60), true)) $minutes = 30;
		try{
			if($cost === 500) $counter = random_int(500, 750);
			else if($cost === 10000) $counter = random_int(4000, 5000);
			else $counter = random_int(2000, 2500);
			$params = array(
				'algorithm'=>'PBKDF2/SHA-256',
				'cost'=>5000,
				'expiresAt'=>time()+$minutes*60,
				'keyLength'=>32,
				'nonce'=>bin2hex(random_bytes(16)),
				'salt'=>bin2hex(random_bytes(16)),
			);
			$key = hash_pbkdf2('sha256', hex2bin($params['nonce']).pack('N', $counter), hex2bin($params['salt']), $params['cost'], $params['keyLength'], true);
			$params['keyPrefix'] = bin2hex(substr($key, 0, 16));
			$params['keySignature'] = hash_hmac('sha256', $key, wp_salt('auth'));
			ksort($params);
		}
		catch(Exception $e){ return false; }
		$signature = hash_hmac('sha256', wp_json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), wp_salt('nonce'));
		if(empty($_SESSION['kboard_altcha']) || !is_array($_SESSION['kboard_altcha'])) $_SESSION['kboard_altcha'] = array();
		foreach($_SESSION['kboard_altcha'] as $key=>$expires){
			if($expires < time()) unset($_SESSION['kboard_altcha'][$key]);
		}
		while(count($_SESSION['kboard_altcha']) >= 50) array_shift($_SESSION['kboard_altcha']);
		$_SESSION['kboard_altcha'][$signature] = $params['expiresAt'];
		return array('parameters'=>$params, 'signature'=>$signature);
	}

	/** Verify the signed ALTCHA solution and consume it only once. */
	public function validateAltcha($payload){
		if(!session_id() || !is_string($payload) || strlen($payload)>8192) return false;
		$decoded = base64_decode($payload, true);
		$data = $decoded !== false ? json_decode($decoded, true) : null;
		if(!is_array($data) || !isset($data['challenge']['parameters'], $data['challenge']['signature'], $data['solution']['counter'], $data['solution']['derivedKey'])) return false;
		$params = $data['challenge']['parameters'];
		$signature = $data['challenge']['signature'];
		$solution = $data['solution'];
		if(!is_array($params) || !is_string($signature) || !preg_match('/^[a-f0-9]{64}$/D', $signature)) return false;
		if(!isset($_SESSION['kboard_altcha'][$signature]) || $_SESSION['kboard_altcha'][$signature] < time()) return false;
		$deterministic = isset($params['keySignature']);
		if(count($params)!==($deterministic ? 8 : 7) || !isset($params['algorithm'], $params['cost'], $params['expiresAt'], $params['keyLength'], $params['keyPrefix'], $params['nonce'], $params['salt'])) return false;
		$allowed_costs = $deterministic ? array(5000) : array(500, 2000, 5000, 10000);
		if($params['algorithm']!=='PBKDF2/SHA-256' || !in_array($params['cost'], $allowed_costs, true) || $params['keyLength']!==32 || !is_int($params['expiresAt']) || $params['expiresAt'] < time() || $params['expiresAt'] !== $_SESSION['kboard_altcha'][$signature]) return false;
		$prefix_pattern = $deterministic ? '/^[a-f0-9]{32}$/D' : '/^[a-f0-9]{2}$/D';
		if(!is_string($params['keyPrefix']) || !preg_match($prefix_pattern, $params['keyPrefix']) || !is_string($params['nonce']) || !preg_match('/^[a-f0-9]{32}$/D', $params['nonce']) || !is_string($params['salt']) || !preg_match('/^[a-f0-9]{32}$/D', $params['salt'])) return false;
		if($deterministic && (!is_string($params['keySignature']) || !preg_match('/^[a-f0-9]{64}$/D', $params['keySignature']))) return false;
		if(!is_int($solution['counter']) || $solution['counter'] < 0 || $solution['counter'] > 2147483647 || !is_string($solution['derivedKey']) || !preg_match('/^[a-f0-9]{64}$/D', $solution['derivedKey'])) return false;
		ksort($params);
		$expected = hash_hmac('sha256', wp_json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), wp_salt('nonce'));
		if(!hash_equals($expected, $signature)) return false;
		if($deterministic){
			$key = hex2bin($solution['derivedKey']);
			if(!hash_equals($params['keySignature'], hash_hmac('sha256', $key, wp_salt('auth'))) || strpos($solution['derivedKey'], $params['keyPrefix']) !== 0) return false;
		}
		else{
			// Accept unexpired challenges issued before the deterministic-mode update.
			$key = hash_pbkdf2('sha256', hex2bin($params['nonce']).pack('N', $solution['counter']), hex2bin($params['salt']), $params['cost'], $params['keyLength'], true);
			$key_hex = bin2hex($key);
			if(!hash_equals($key_hex, $solution['derivedKey']) || strpos($key_hex, $params['keyPrefix']) !== 0) return false;
		}
		unset($_SESSION['kboard_altcha'][$signature]);
		return true;
	}
	
	/**
	 * Captcha 이미지를 생성한다.
	 */
	public function createImage(){
		if(kboard_use_altcha()){
			wp_enqueue_script('kboard-altcha', KBOARD_URL_PATH.'/assets/altcha/altcha.min.js', array(), '3.5.0', true);
			wp_enqueue_script('kboard-altcha-form', KBOARD_URL_PATH.'/assets/altcha/form.js', array('kboard-altcha'), KBOARD_VERSION, true);
			wp_localize_script('kboard-altcha-form', 'kboard_altcha_settings', array(
				'challenge'=>add_query_arg('action', 'kboard_altcha_challenge', home_url('/')),
				'language'=>substr(get_locale(), 0, 2),
			));
			return 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=#kboard-altcha';
		}
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
			unset($_SESSION['kboard_captcha']);
			$src = KBOARD_URL_PATH . '/images/captcha-error.png';
		}
		
		return $src;
	}
	
	/**
	 * Captcha 검증
	 * @return boolean
	 */
	public function validate(){
		if(is_user_logged_in()){
			return true;
		}
		if(kboard_use_recaptcha()){
			return $this->recaptcha();
		}
		if(isset($_POST['altcha']) && is_string($_POST['altcha'])){
			return $this->validateAltcha(wp_unslash($_POST['altcha']));
		}
		if(isset($_POST['captcha']) && is_string($_POST['captcha']) && !empty($_SESSION['kboard_captcha']) && is_array($_SESSION['kboard_captcha'])){
			$answer = strtoupper($_POST['captcha']);
			if($answer !== 'ERROR' && in_array($answer, $_SESSION['kboard_captcha'], true)){
				unset($_SESSION['kboard_captcha']);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 구글 reCAPTCHA 검증
	 * @return boolean
	 */
	function recaptcha(){
		if(!kboard_use_recaptcha()){
			return false;
		}
		if(empty($_POST['g-recaptcha-response']) || !is_string($_POST['g-recaptcha-response'])){
			return false;
		}

		$siteverify_url = add_query_arg(array(
				'secret'   => kboard_recaptcha_secret_key(),
				'response' => isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '',
				'remoteip' => kboard_user_ip()
		), 'https://www.google.com/recaptcha/api/siteverify');
		
		$response = wp_remote_get($siteverify_url);
		
		if(is_wp_error($response) || empty($response['body']) || !($json = json_decode($response['body'])) || !$json->success){
			return false;
		}
		
		if(kboard_recaptcha_type() == 'v3'){
			$response_action = isset($json->action) ? sanitize_key($json->action) : '';
			$response_score = isset($json->score) ? floatval($json->score) : 0;
			
			if($response_action !== kboard_recaptcha_v3_action()){
				return false;
			}
			if($response_score < 0.5){
				return false;
			}
		}
		return true;
	}
}

<?php
if(!defined('ABSPATH')) exit;
/**
 * KBoard 워드프레스 게시판 보안 함수
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */

// 시스템 설정을 가져온다.
$kboard_xssfilter_active = get_option('kboard_xssfilter')?false:true;
if($kboard_xssfilter_active){
	// HTML Purifier 클래스를 불러온다.
	if(!class_exists('HTMLPurifier')){
		include_once KBOARD_DIR_PATH.'/htmlpurifier/HTMLPurifier.standalone.php';
	}
	// HTML Purifier 캐시 저장을 위한 디렉토리 생성
	wp_mkdir_p(WP_CONTENT_DIR.'/uploads/kboard_htmlpurifier');
}

/**
 * Cross-site scripting (XSS) 공격을 방어하기 위해서 위험한 문자열을 제거한다.
 * @param string $data
 */
function kboard_xssfilter($data){
	global $kboard_xssfilter_active;
	if(is_array($data)) return array_map('kboard_xssfilter', $data);
	if($kboard_xssfilter_active){
		if(!isset($GLOBALS['KBOARD']) || !isset($GLOBALS['KBOARD']['HTMLPurifier']) || !$GLOBALS['KBOARD']['HTMLPurifier'] || !isset($GLOBALS['KBOARD']['HTMLPurifier_Config']) || !$GLOBALS['KBOARD']['HTMLPurifier_Config']){
			$HTMLPurifier_Config = HTMLPurifier_Config::createDefault();
			$HTMLPurifier_Config->set('URI.AllowedSchemes', array('http'=>true,'https'=>true,'mailto'=>true,'tel'=>true));
			$HTMLPurifier_Config->set('URI.SafeIframeRegexp', '(.*)');
			$HTMLPurifier_Config->set('HTML.SafeIframe', true);
			$HTMLPurifier_Config->set('HTML.SafeObject', true);
			$HTMLPurifier_Config->set('HTML.SafeEmbed', true);
			$HTMLPurifier_Config->set('HTML.TidyLevel', 'light');
			$HTMLPurifier_Config->set('HTML.FlashAllowFullScreen', true);
			$HTMLPurifier_Config->set('HTML.AllowedElements','img,div,a,strong,font,span,em,del,ins,br,p,u,i,b,sup,sub,small,table,thead,tbody,tfoot,tr,td,th,caption,pre,code,ul,ol,li,big,code,blockquote,center,hr,h1,h2,h3,h4,h5,h6,iframe,dl,dt,dd');
			$HTMLPurifier_Config->set('HTML.AllowedAttributes', 'a.href,a.target,img.src,iframe.src,iframe.frameborder,*.id,*.alt,*.style,*.class,*.title,*.width,*.height,*.border,*.colspan,*.rowspan');
			$HTMLPurifier_Config->set('HTML.TargetNoreferrer', false);
			$HTMLPurifier_Config->set('Attr.AllowedFrameTargets', array('_blank'));
			$HTMLPurifier_Config->set('Attr.EnableID', true);
			$HTMLPurifier_Config->set('Output.FlashCompat', true);
			$HTMLPurifier_Config->set('Core.RemoveInvalidImg', true);
			$HTMLPurifier_Config->set('Core.LexerImpl', 'DirectLex');
			$HTMLPurifier_Config->set('Cache.SerializerPath', WP_CONTENT_DIR.'/uploads/kboard_htmlpurifier');
			$GLOBALS['KBOARD']['HTMLPurifier_Config'] = $HTMLPurifier_Config;
			$GLOBALS['KBOARD']['HTMLPurifier'] = HTMLPurifier::getInstance();
			unset($HTMLPurifier_Config);
		}
		$data = $GLOBALS['KBOARD']['HTMLPurifier']->purify($data, $GLOBALS['KBOARD']['HTMLPurifier_Config']);
	}
	return trim($data);
}

/**
 * 허용된 도메인의 아이프레임만 남기고 모두 제거한다.
 * @param string $data
 * @return string
 */
function kboard_safeiframe($data){
	if(is_array($data)) return array_map('kboard_safeiframe', $data);
	
	/*
	 * 허가된 도메인 호스트 (화이트리스트)
	 */
	$whitelist = kboard_iframe_whitelist(true);
	
	// kboard_iframe_whitelist 필터
	$whitelist = apply_filters('kboard_iframe_whitelist', $whitelist);
	
	preg_match_all('/<iframe.+?src="(.+?)".+?[^>]*+>/is', $data, $matches);
	
	$iframe = $matches[0];
	$domain = $matches[1];
	
	foreach($domain as $key=>$value){
		$value = 'http://' . preg_replace('/^(http:\/\/|https:\/\/|\/\/)/i', '', $value);
		$url = parse_url($value);
		if(!in_array($url['host'], $whitelist)){
			$data = str_replace($iframe[$key].'</iframe>', '', $data);
			$data = str_replace($iframe[$key], '', $data);
		}
	}
	
	// 풀스크린 허용
	$data = preg_replace('/<iframe(.*?)>/is', '<iframe$1 allowfullscreen>', $data);
	
	return $data;
}

/**
 * 모든 html을 제거한다.
 * @param object $data
 */
function kboard_htmlclear($data){
	if(is_array($data)) return array_map('kboard_htmlclear', $data);
	$data = sanitize_text_field($data);
	return htmlspecialchars($data);
}

/**
 * 아이프레임 화이트리스트를 반환한다.
 * @param boolean $to_array
 */
function kboard_iframe_whitelist($to_array=false){
	/*
	 * 허가된 도메인 호스트 (화이트리스트)
	 */
	$whitelist = 'google.com' . PHP_EOL;
	$whitelist .= 'www.google.com' . PHP_EOL;
	$whitelist .= 'youtube.com' . PHP_EOL;
	$whitelist .= 'www.youtube.com' . PHP_EOL;
	$whitelist .= 'maps.google.com' . PHP_EOL;
	$whitelist .= 'maps.google.co.kr' . PHP_EOL;
	$whitelist .= 'docs.google.com' . PHP_EOL;
	$whitelist .= 'serviceapi.nmv.naver.com' . PHP_EOL;
	$whitelist .= 'serviceapi.rmcnmv.naver.com' . PHP_EOL;
	$whitelist .= 'videofarm.daum.net' . PHP_EOL;
	$whitelist .= 'tv.kakao.com' . PHP_EOL;
	$whitelist .= 'player.vimeo.com' . PHP_EOL;
	$whitelist .= 'w.soundcloud.com' . PHP_EOL;
	$whitelist .= 'slideshare.net' . PHP_EOL;
	$whitelist .= 'www.slideshare.net' . PHP_EOL;
	$whitelist .= 'channel.pandora.tv' . PHP_EOL;
	$whitelist .= 'mgoon.com' . PHP_EOL;
	$whitelist .= 'www.mgoon.com' . PHP_EOL;
	$whitelist .= 'tudou.com' . PHP_EOL;
	$whitelist .= 'www.tudou.com' . PHP_EOL;
	$whitelist .= 'player.youku.com' . PHP_EOL;
	$whitelist .= 'videomega.tv' . PHP_EOL;
	$whitelist .= 'mtab.clickmon.co.kr' . PHP_EOL;
	$whitelist .= 'tab2.clickmon.co.kr';
	
	$iframe_whitelist_data = get_option('kboard_iframe_whitelist');
	$iframe_whitelist_data = trim($iframe_whitelist_data);
	
	if(!$iframe_whitelist_data){
		$iframe_whitelist_data = $whitelist;
	}
	
	if($to_array){
		$iframe_whitelist_data = explode(PHP_EOL, $iframe_whitelist_data);
		return array_map('trim', $iframe_whitelist_data);
	}
	return $iframe_whitelist_data;
}

if(!function_exists('hash_pbkdf2')){
	/**
	 * hash_pbkdf2
	 * @link http://php.net/manual/en/function.hash-pbkdf2.php#118301
	 * @param string $algo
	 * @param string $password
	 * @param string $salt
	 * @param int $count
	 * @param int $length
	 * @param string $raw_output
	 * @return string
	 */
	function hash_pbkdf2($algo, $password, $salt, $count, $length = 0, $raw_output = false){
		if(!in_array(strtolower($algo), hash_algos())) trigger_error(__FUNCTION__ . '(): Unknown hashing algorithm: ' . $algo, E_USER_WARNING);
		if(!is_numeric($count)) trigger_error(__FUNCTION__ . '(): expects parameter 4 to be long, ' . gettype($count) . ' given', E_USER_WARNING);
		if(!is_numeric($length)) trigger_error(__FUNCTION__ . '(): expects parameter 5 to be long, ' . gettype($length) . ' given', E_USER_WARNING);
		if($count <= 0) trigger_error(__FUNCTION__ . '(): Iterations must be a positive integer: ' . $count, E_USER_WARNING);
		if($length < 0) trigger_error(__FUNCTION__ . '(): Length must be greater than or equal to 0: ' . $length, E_USER_WARNING);
		$output = '';
		$block_count = $length ? ceil($length / strlen(hash($algo, '', $raw_output))) : 1;
		for($i=1; $i<=$block_count; $i++){
			$last = $xorsum = hash_hmac($algo, $salt . pack('N', $i), $password, true);
			for($j=1; $j<$count; $j++){
				$xorsum ^= ($last = hash_hmac($algo, $last, $password, true));
			}
			$output .= $xorsum;
		}
		if(!$raw_output) $output = bin2hex($output);
		return $length ? substr($output, 0, $length) : $output;
	}
}

/**
 * 텍스트의 해시값을 반환한다.
 * @param string $text
 * @param string $salt
 * @param number $length
 * @return string
 */
function kboard_hash($text, $salt, $length=0){
	switch(strlen($text)%8){
		case 0 : $salt = AUTH_KEY . md5($salt); break;
		case 1 : $salt = SECURE_AUTH_KEY . md5($salt); break;
		case 2 : $salt = LOGGED_IN_KEY . md5($salt); break;
		case 3 : $salt = NONCE_KEY . md5($salt); break;
		case 4 : $salt = AUTH_SALT . md5($salt); break;
		case 5 : $salt = SECURE_AUTH_SALT . md5($salt); break;
		case 6 : $salt = LOGGED_IN_SALT . md5($salt); break;
		case 7 : $salt = NONCE_SALT . md5($salt); break;
	}
	return '$kboard$v1$' . hash_pbkdf2('sha256', $text, $salt, 100000, $length);
}
?>
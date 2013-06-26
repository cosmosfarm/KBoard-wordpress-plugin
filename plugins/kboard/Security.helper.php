<?php
/**
 * KBoard 워드프레스 게시판 보안 함수
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */

/**
 * XSS filter
 *
 * This was built from numerous sources
 * (thanks all, sorry I didn't track to credit you)
 *
 * It was tested against *most* exploits here: http://ha.ckers.org/xss.html
 * WARNING: Some weren't tested!!!
 * Those include the Actionscript and SSI samples, or any newer than Jan 2011
 *
 *
 * TO-DO: compare to SymphonyCMS filter:
 * https://github.com/symphonycms/xssfilter/blob/master/extension.driver.php
 * (Symphony's is probably faster than my hack)
 */
function kboard_xssfilter($data){
	if(is_array($data)){
		return array_map('xssfilter', $data);
	}
	
	// Fix &entity\n;
	$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
	$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
	$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
	$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

	// Remove any attribute starting with "on" or xmlns
	$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

	// Remove javascript: and vbscript: protocols
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

	// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

	// Remove namespaced elements (we do not need them)
	$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

	do{
		// Remove really unwanted tags
		$old_data = $data;
		$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|ilayer|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
	}
	while ($old_data !== $data);
	
	$data = kboard_safeiframe($data);
	return $data;
}

/**
 * 허용된 도메인의 아이프레임만 남기고 모두 제거한다.
 * @param string $data
 * @return string
 */
function kboard_safeiframe($data){
	/*
	 * 허가된 도메인 호스트
	 */
	$whilelist[] = 'youtube.com';
	$whilelist[] = 'www.youtube.com';
	$whilelist[] = 'maps.google.com';
	$whilelist[] = 'maps.google.co.kr';
	$whilelist[] = 'serviceapi.nmv.naver.com';
	$whilelist[] = 'videofarm.daum.net';
	
	$re = preg_match_all('/<iframe.+?src="(.+?)".+?[^>]*+>/is', $data, $matches);
	$iframe = $matches[0];
	$domain = $matches[1];
	
	foreach($domain AS $key => $value){
		$url = parse_url($value);
		if(!in_array($url['host'], $whilelist)){
			$data = str_replace($iframe[$key].'</iframe>', '', $data);
			$data = str_replace($iframe[$key], '', $data);
		}
	}
	return $data;
}

/**
 * 모든 html을 제거한다.
 * @param object $data
 */
function kboard_htmlclear($data){
	if(is_array($data)){
		return array_map('htmlclear', $data);
	}
	return htmlspecialchars(strip_tags($data));
}
?>
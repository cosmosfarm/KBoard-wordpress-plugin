<?php
if(!defined('ABSPATH')) exit;
/**
 * KBoard 워드프레스 게시판 보안 함수
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
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
			$HTMLPurifier_Config->set('URI.AllowedSchemes', array('http'=>true,'https'=>true,'mailto'=>true,'tel'=>true,'data'=>true));
			$HTMLPurifier_Config->set('URI.SafeIframeRegexp', '(.*)');
			$HTMLPurifier_Config->set('HTML.SafeIframe', true);
			$HTMLPurifier_Config->set('HTML.SafeObject', true);
			$HTMLPurifier_Config->set('HTML.SafeEmbed', true);
			$HTMLPurifier_Config->set('HTML.TidyLevel', 'light');
			$HTMLPurifier_Config->set('HTML.FlashAllowFullScreen', true);
			$HTMLPurifier_Config->set('HTML.AllowedElements','img,div,a,strong,font,span,em,del,ins,br,p,u,i,b,sup,sub,small,table,thead,tbody,tfoot,tr,td,th,caption,pre,code,ul,ol,li,big,code,blockquote,center,hr,h1,h2,h3,h4,h5,h6,iframe,dl,dt,dd,strike');
			$HTMLPurifier_Config->set('HTML.AllowedAttributes', 'a.rel,a.href,a.target,img.src,iframe.src,iframe.frameborder,font.color,*.id,*.alt,*.style,*.class,*.title,*.width,*.height,*.border,*.colspan,*.rowspan');
			$HTMLPurifier_Config->set('HTML.TargetNoreferrer', false);
			$HTMLPurifier_Config->set('Attr.AllowedFrameTargets', array('_blank'));
			$HTMLPurifier_Config->set('Attr.EnableID', true);
			$HTMLPurifier_Config->set('Attr.AllowedRel', 'nofollow,noopener,noreferrer');
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
 * SVG 파일에서 실행 가능한 마크업을 제거한다.
 * @param string $file
 * @return boolean
 */
function kboard_sanitize_svg_file($file){
	if(strtolower(pathinfo($file, PATHINFO_EXTENSION)) != 'svg'){
		return true;
	}
	if(!file_exists($file) || !is_readable($file) || !is_writable($file)){
		return false;
	}
	$svg = file_get_contents($file);
	if($svg === false){
		return false;
	}
	$svg = kboard_sanitize_svg_markup($svg);
	if($svg === false || !trim($svg)){
		return false;
	}
	return file_put_contents($file, $svg) !== false;
}

/**
 * SVG 문자열을 정제한다.
 * @param string $svg
 * @return string|boolean
 */
function kboard_sanitize_svg_markup($svg){
	if(!is_string($svg) || stripos($svg, '<svg') === false){
		return false;
	}
	if(preg_match('/<!DOCTYPE|<!ENTITY/i', $svg)){
		return false;
	}
	if(!class_exists('DOMDocument')){
		return false;
	}
	
	$internal_errors = libxml_use_internal_errors(true);
	$dom = new DOMDocument('1.0', 'UTF-8');
	$options = 0;
	foreach(array('LIBXML_NONET', 'LIBXML_NOERROR', 'LIBXML_NOWARNING', 'LIBXML_NOBLANKS', 'LIBXML_COMPACT') as $constant){
		if(defined($constant)){
			$options |= constant($constant);
		}
	}
	$loaded = $dom->loadXML($svg, $options);
	libxml_clear_errors();
	libxml_use_internal_errors($internal_errors);
	
	if(!$loaded || !$dom->documentElement || strtolower($dom->documentElement->localName) != 'svg'){
		return false;
	}
	
	$allowed_tags = array(
		'svg' => array('xmlns', 'xmlns:xlink', 'version', 'width', 'height', 'viewbox', 'preserveaspectratio'),
		'g' => array('transform', 'opacity', 'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-opacity', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'clip-path', 'mask'),
		'defs' => array(),
		'symbol' => array('viewbox', 'preserveaspectratio'),
		'use' => array('href', 'xlink:href', 'x', 'y', 'width', 'height', 'transform'),
		'title' => array(),
		'desc' => array(),
		'path' => array('d', 'pathlength', 'transform', 'opacity', 'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-opacity', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'clip-path', 'mask'),
		'rect' => array('x', 'y', 'width', 'height', 'rx', 'ry', 'transform', 'opacity', 'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-opacity', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'clip-path', 'mask'),
		'circle' => array('cx', 'cy', 'r', 'transform', 'opacity', 'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-opacity', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'clip-path', 'mask'),
		'ellipse' => array('cx', 'cy', 'rx', 'ry', 'transform', 'opacity', 'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-opacity', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'clip-path', 'mask'),
		'line' => array('x1', 'y1', 'x2', 'y2', 'transform', 'opacity', 'stroke', 'stroke-opacity', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'clip-path', 'mask'),
		'polyline' => array('points', 'transform', 'opacity', 'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-opacity', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'clip-path', 'mask'),
		'polygon' => array('points', 'transform', 'opacity', 'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-opacity', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'clip-path', 'mask'),
		'lineargradient' => array('id', 'x1', 'y1', 'x2', 'y2', 'gradientunits', 'gradienttransform'),
		'radialgradient' => array('id', 'cx', 'cy', 'r', 'fx', 'fy', 'gradientunits', 'gradienttransform'),
		'stop' => array('offset', 'stop-color', 'stop-opacity'),
		'clippath' => array('id', 'clippathunits', 'transform'),
		'mask' => array('id', 'maskunits', 'maskcontentunits', 'x', 'y', 'width', 'height'),
		'pattern' => array('id', 'patternunits', 'patterncontentunits', 'patterntransform', 'x', 'y', 'width', 'height'),
	);
	$common_attributes = array('id', 'class', 'style');
	
	kboard_sanitize_svg_node($dom->documentElement, $allowed_tags, $common_attributes);
	$dom->documentElement->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
	
	return $dom->saveXML($dom->documentElement);
}

/**
 * SVG 노드를 순회하며 허용되지 않은 요소와 속성을 제거한다.
 * @param DOMNode $node
 * @param array $allowed_tags
 * @param array $common_attributes
 */
function kboard_sanitize_svg_node($node, $allowed_tags, $common_attributes){
	for($child=$node->firstChild; $child; $child=$next_sibling){
		$next_sibling = $child->nextSibling;
		
		if($child->nodeType == XML_ELEMENT_NODE){
			$tag_name = strtolower($child->localName);
			if(!isset($allowed_tags[$tag_name])){
				$node->removeChild($child);
				continue;
			}
			
			$allowed_attributes = array_merge($common_attributes, $allowed_tags[$tag_name]);
			$remove_attributes = array();
			foreach($child->attributes as $attribute){
				$attribute_name = strtolower($attribute->nodeName);
				if(strpos($attribute_name, 'on') === 0 || !in_array($attribute_name, $allowed_attributes)){
					$remove_attributes[] = $attribute->nodeName;
					continue;
				}
				$sanitized_value = kboard_sanitize_svg_attribute_value($attribute_name, $attribute->nodeValue);
				if($sanitized_value === false || $sanitized_value === ''){
					$remove_attributes[] = $attribute->nodeName;
					continue;
				}
				$child->setAttribute($attribute->nodeName, $sanitized_value);
			}
			foreach($remove_attributes as $attribute_name){
				$child->removeAttribute($attribute_name);
			}
			
			kboard_sanitize_svg_node($child, $allowed_tags, $common_attributes);
		}
		else if(in_array($child->nodeType, array(XML_COMMENT_NODE, XML_PI_NODE, XML_DOCUMENT_TYPE_NODE, XML_ENTITY_REF_NODE, XML_CDATA_SECTION_NODE))){
			$node->removeChild($child);
		}
	}
}

/**
 * SVG 속성 값을 정제한다.
 * @param string $attribute_name
 * @param string $value
 * @return string|boolean
 */
function kboard_sanitize_svg_attribute_value($attribute_name, $value){
	$value = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $value));
	if($value === ''){
		return '';
	}
	if(preg_match('/(?:javascript:|vbscript:|data:|expression\s*\(|@import|-moz-binding|behavior\s*:)/i', $value)){
		return false;
	}
	if(preg_match('/url\s*\(\s*[\'"]?\s*(?!#)/i', $value)){
		return false;
	}
	if(strpos($value, '<') !== false || strpos($value, '>') !== false){
		return false;
	}
	
	if(in_array($attribute_name, array('href', 'xlink:href'))){
		return preg_match('/^#[-A-Za-z0-9_:.]+$/', $value) ? $value : false;
	}
	if($attribute_name == 'style'){
		return kboard_sanitize_svg_style($value);
	}
	if(in_array($attribute_name, array('fill', 'stroke', 'clip-path', 'mask'))){
		if(preg_match('/^url\s*\(\s*#[-A-Za-z0-9_:.]+\s*\)$/i', $value)){
			return $value;
		}
	}
	
	return $value;
}

/**
 * SVG style 속성을 정제한다.
 * @param string $style
 * @return string|boolean
 */
function kboard_sanitize_svg_style($style){
	$declarations = explode(';', $style);
	$sanitized = array();
	
	foreach($declarations as $declaration){
		$declaration = trim($declaration);
		if(!$declaration || strpos($declaration, ':') === false){
			continue;
		}
		list($property, $value) = array_map('trim', explode(':', $declaration, 2));
		if(!$property || !$value){
			continue;
		}
		if(preg_match('/[^a-z-]/i', $property)){
			continue;
		}
		if(preg_match('/(?:javascript:|vbscript:|data:|expression\s*\(|@import|-moz-binding|behavior\s*:)/i', $value)){
			continue;
		}
		if(preg_match('/url\s*\(\s*[\'"]?\s*(?!#)/i', $value)){
			continue;
		}
		if(strpos($value, '<') !== false || strpos($value, '>') !== false){
			continue;
		}
		$sanitized[] = "{$property}: {$value}";
	}
	
	if(!$sanitized){
		return false;
	}
	return implode('; ', $sanitized);
}

/**
 * 업로드 경로의 기존 SVG 파일을 일괄 정리한다.
 * @return array
 */
function kboard_svg_batch_scan_uploads(){
	$upload_dir = wp_upload_dir();
	$result = array(
		'version' => defined('KBOARD_VERSION') ? KBOARD_VERSION : '',
		'started_at' => current_time('mysql'),
		'completed_at' => '',
		'checked' => 0,
		'sanitized' => 0,
		'unchanged' => 0,
		'quarantined' => 0,
		'errors' => 0,
		'quarantined_files' => array(),
	);
	$targets = array(
		untrailingslashit($upload_dir['basedir']) . '/kboard_attached',
		untrailingslashit($upload_dir['basedir']) . '/kboard_thumbnails',
	);
	
	foreach($targets as $target){
		kboard_svg_batch_scan_path($target, $result);
	}
	
	$result['completed_at'] = current_time('mysql');
	return $result;
}

/**
 * 지정한 경로의 SVG 파일을 순회하며 정리한다.
 * @param string $path
 * @param array $result
 */
function kboard_svg_batch_scan_path($path, &$result){
	if(!is_dir($path)){
		return;
	}
	$flags = RecursiveDirectoryIterator::SKIP_DOTS;
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, $flags));
	
	foreach($iterator as $file_info){
		if(!$file_info->isFile()){
			continue;
		}
		if(strtolower($file_info->getExtension()) != 'svg'){
			continue;
		}
		
		$file = $file_info->getPathname();
		$result['checked']++;
		$svg = file_get_contents($file);
		if($svg === false){
			$result['errors']++;
			continue;
		}
		
		$sanitized = kboard_sanitize_svg_markup($svg);
		if($sanitized === false || !trim($sanitized)){
			if(kboard_quarantine_svg_file($file)){
				$result['quarantined']++;
				if(count($result['quarantined_files']) < 5){
					$result['quarantined_files'][] = str_replace(ABSPATH, '', $file);
				}
			}
			else{
				$result['errors']++;
			}
			continue;
		}
		
		if($sanitized !== $svg){
			if(file_put_contents($file, $sanitized) !== false){
				$result['sanitized']++;
			}
			else{
				$result['errors']++;
			}
		}
		else{
			$result['unchanged']++;
		}
	}
}

/**
 * SVG 파일을 직접 접근할 수 없도록 격리한다.
 * @param string $file
 * @return boolean
 */
function kboard_quarantine_svg_file($file){
	$quarantined_file = $file . '.blocked';
	$index = 1;
	while(file_exists($quarantined_file)){
		$quarantined_file = $file . '.blocked' . $index;
		$index++;
	}
	return @rename($file, $quarantined_file);
}

/**
 * 허용된 iframe 도메인 화이트리스트를 반환한다.
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
	$whitelist .= 'tv.naver.com' . PHP_EOL;
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

/**
 * KBoard 비밀번호 해시 버전 prefix를 반환한다.
 * @return string
 */
function kboard_password_prefix(){
	return '$kboard_password$v1$';
}

/**
 * 에디터에서 기존 해시 비밀번호를 가리기 위한 마스크 문자열을 반환한다.
 * @return string
 */
function kboard_password_mask(){
	return '__KBOARD_PASSWORD_KEEP__';
}

/**
 * 마스크 문자열인지 확인한다.
 * @param string $password
 * @return boolean
 */
function kboard_password_is_mask($password){
	return $password === kboard_password_mask();
}

/**
 * KBoard 비밀번호가 해시화되어 저장되었는지 확인한다.
 * @param string $password
 * @return boolean
 */
function kboard_password_is_hashed($password){
	if(!$password){
		return false;
	}
	return strpos($password, kboard_password_prefix()) === 0;
}

/**
 * KBoard 비밀번호 해시를 생성한다.
 * @param string $password
 * @return string
 */
function kboard_password_hash($password){
	$password = sanitize_text_field($password);
	if(!$password){
		return '';
	}
	if(kboard_password_is_hashed($password)){
		return $password;
	}
	return kboard_password_prefix() . wp_hash_password($password);
}

/**
 * 저장 가능한 비밀번호 값으로 변환한다.
 * @param string $password
 * @return string
 */
function kboard_password_prepare($password){
	$password = sanitize_text_field($password);
	if(!$password){
		return '';
	}
	return kboard_password_hash($password);
}

/**
 * 레거시 평문 비밀번호인지 확인한다.
 * @param string $password
 * @return boolean
 */
function kboard_password_needs_migration($password){
	return $password && !kboard_password_is_hashed($password);
}

/**
 * 입력된 비밀번호와 저장된 비밀번호를 비교한다.
 * 저장된 해시 문자열 자체가 전달된 경우도 허용한다.
 * @param string $input_password
 * @param string $stored_password
 * @return boolean
 */
function kboard_password_verify($input_password, $stored_password){
	$input_password = sanitize_text_field($input_password);
	$stored_password = sanitize_text_field($stored_password);
	
	if(!$input_password || !$stored_password){
		return false;
	}
	if($input_password === $stored_password){
		return true;
	}
	if(kboard_password_is_hashed($stored_password)){
		$password_hash = substr($stored_password, strlen(kboard_password_prefix()));
		return wp_check_password($input_password, $password_hash);
	}
	return $input_password === $stored_password;
}

/**
 * Sanitizes a string for safe usage in a CSV file.
 *
 * This function specifically addresses CSV injection vulnerabilities by:
 * 1. Prefixing cells starting with =, +, -, @, and Tab characters with a single quote (').
 *    This prevents the CSV parser from interpreting them as formulas.
 * 2. Enclosing the string in double quotes if it contains commas or newlines.
 *    This ensures proper cell separation and formatting.
 *
 * @param string $str The string to sanitize.
 * @return string The sanitized string.
 */
function kboard_sanitize_csv_field($str){
	// Escape characters that could be interpreted as formulas (=, +, -, @, Tab, Carriage Return)
	if (preg_match('/^[\=\+\-\@\t\r]/', $str)){
		$str = "'" . $str;
	}
	
	// Escape double quotes by doubling them
	//$str = str_replace('"', '""', $str);
	
	// Escape newlines to preserve data integrity
	//$str = str_replace(array("\r\n", "\r", "\n"), " ", $str);
	
	// Enclose in double quotes if it contains commas or newlines (already handled above)
	// if (strpos($str, ',') !== false || strpos($str, "\n") !== false || strpos($str, '"') !== false) {
	//     $str = '"' . $str . '"';
	// }
	
	return $str;
}

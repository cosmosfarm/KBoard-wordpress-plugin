<?php
/**
 * KBoard 워드프레스 게시판 보안 함수
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */

/*
 * HTMLPurifier 클래스를 불러온다.
 */
if(!class_exists('HTMLPurifier')){
	include_once KBOARD_DIR_PATH.'/htmlpurifier/HTMLPurifier.standalone.php';
}

/*
 * HTMLPurifier 설정 캐시 경로
 */
$kboard_file_handler = new KBFileHandler();
$kboard_file_handler->mkPath(WP_CONTENT_DIR.'/uploads/kboard_htmlpurifier');
unset($kboard_file_handler);

/**
 * Cross-site scripting (XSS) 공격을 방어하기 위해서 위험 문자열을 제거한다.
 * @param string $data
 */
function kboard_xssfilter($data){
	if(is_array($data)) return array_map('kboard_xssfilter', $data);
	if(!$GLOBALS['KBOARD']['HTMLPurifier'] || !$GLOBALS['KBOARD']['HTMLPurifier_Config']){
		$HTMLPurifier_Config = HTMLPurifier_Config::createDefault();
		$HTMLPurifier_Config->set('HTML.SafeIframe', true);
		$HTMLPurifier_Config->set('URI.SafeIframeRegexp', '(.*)');
		$HTMLPurifier_Config->set('HTML.TidyLevel', 'light');
		$HTMLPurifier_Config->set('HTML.SafeObject', true);
		$HTMLPurifier_Config->set('HTML.SafeEmbed', true);
		$HTMLPurifier_Config->set('HTML.AllowedAttributes', "*.style,*.alt,*.title,*.id,*.class,*.name,*.classid,*.usemap,*.type,a.href,a.target,src,height,width,table.align,table.border,table.cellpadding,table.cellspacing,frameborder");
		$HTMLPurifier_Config->set('HTML.AllowedElements','h1,h2,h3,h4,h5,h6,a,p,ol,li,ul,dl,dt,dd,b,br,span,img,pre,blockquote,div,table,thead,tbody,tfoot,th,tr,td,iframe,object,embed');
		$HTMLPurifier_Config->set('Attr.AllowedFrameTargets', array('_blank'));
		$HTMLPurifier_Config->set('Output.FlashCompat', true);
		$HTMLPurifier_Config->set('Cache.SerializerPath', WP_CONTENT_DIR.'/uploads/kboard_htmlpurifier');
		$GLOBALS['KBOARD']['HTMLPurifier_Config'] = $HTMLPurifier_Config;
		$GLOBALS['KBOARD']['HTMLPurifier'] = HTMLPurifier::getInstance();
		unset($HTMLPurifier_Config);
	}
	$data = $GLOBALS['KBOARD']['HTMLPurifier']->purify(stripslashes($data), $GLOBALS['KBOARD']['HTMLPurifier_Config']);
	return kboard_safeiframe($data);
}

/**
 * 허용된 도메인의 아이프레임만 남기고 모두 제거한다.
 * @param string $data
 * @return string
 */
function kboard_safeiframe($data){
	/*
	 * 허가된 도메인 호스트 (화이트 리스트)
	 */
	$whilelist[] = 'youtube.com';
	$whilelist[] = 'www.youtube.com';
	$whilelist[] = 'maps.google.com';
	$whilelist[] = 'maps.google.co.kr';
	$whilelist[] = 'serviceapi.nmv.naver.com';
	$whilelist[] = 'videofarm.daum.net';
	$whilelist[] = 'player.vimeo.com';
	$whilelist[] = 'w.soundcloud.com';
	
	$re = preg_match_all('/<iframe.+?src="(.+?)".+?[^>]*+>/is', $data, $matches);
	$iframe = $matches[0];
	$domain = $matches[1];
	
	foreach($domain AS $key => $value){
		$value = 'http://' . preg_replace('/^(http:\/\/|https:\/\/|\/\/)/i', '', $value);
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
	if(is_array($data)) return array_map('kboard_htmlclear', $data);
	return htmlspecialchars(strip_tags($data));
}
?>
<?php
if(!defined('ABSPATH')) exit;
/**
 * KBoard 게시글 본문 자동링크
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
function kboard_autolink($text){
	/*
	* Mark Goldsmith
	* http://css-tricks.com/snippets/php/find-urls-in-text-make-links/
	*/
	// <iframe> 태그가 포함된 경우 변환하지 않고 원래 내용 유지
	if (preg_match('/<iframe.*?>.*?<\/iframe>/is', $text)) {
		return $text;
	}

	// 이미 <a> 태그가 있는 경우 변환 방지
	if (preg_match('/<a\s+href=["\']?(http|https):\/\/[^"\']+["\']?\s*target=["\']?_blank["\']?>/i', $text)) {
		return $text;
	}
	
	// 유튜브 & 비메오 URL이 포함된 경우 자동 링크 변환 방지
	if (preg_match('#(youtube\.com|youtu\.be|vimeo\.com)#', $text)) {
		return $text;
	}
	
	// <iframe> 내부의 src 속성 URL은 변환하지 않도록 정규식 예외 처리
	return preg_replace_callback(
		'#(?i)(?<!src=")(http|https)?(://)?(([-\w^@]+\.)+(kr|co.kr|go.kr|net|org|edu|gov|me|com|xyz|or.kr|pe.kr|re.kr|ne.kr|biz|us|so|asia|tv|co+)(?:/[^,\s]*|))#',
		'kboard_autolink_prependHTTP',
		$text
	);
}

function kboard_autolink_prependHTTP($m){
	/*
	 * Mark Goldsmith
	 * http://css-tricks.com/snippets/php/find-urls-in-text-make-links/
	 */
	$mStr = $m[1].$m[2].$m[3];
	if(preg_match('#([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#', $mStr)){
		return "<a href=\"mailto:".$m[2].$m[3]."\" target=\"_blank\">".$m[1].$m[2].$m[3]."</a>";
	}
	else{
		$http = (!preg_match("#(https://)#", $mStr)) ? 'http://' : 'https://';
		return "<a href=\"".$http.$m[3]."\" target=\"_blank\">".$m[1].$m[2].$m[3]."</a>";
	}
}
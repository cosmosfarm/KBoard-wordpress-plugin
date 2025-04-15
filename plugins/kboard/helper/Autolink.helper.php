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
	
	$protected_tags = array();
	$text = preg_replace_callback('/<[^>]+>/', function($matches) use (&$protected_tags) {
		$hash = '__PROTECTED_TAG_' . count($protected_tags) . '__';
		$protected_tags[$hash] = $matches[0];
		return $hash;
	}, $text);
	
	$text = preg_replace_callback(
		'#(?<![="\'])(https?:\/\/(?:[-\w]+\.)+(com|net|org|kr|co\.kr|go\.kr|edu|gov|me|xyz|biz|tv|us|asia|store|shop|io|ai|re\.kr|pe\.kr|ne\.kr|or\.kr)(\/[^\s<]*)?)#i',
		'kboard_autolink_prependHTTP',
		$text
	);
	
	foreach($protected_tags as $hash => $original){
		$text = str_replace($hash, $original, $text);
	}

	return $text;
}

function kboard_autolink_prependHTTP($m){
	/*
	 * Mark Goldsmith
	 * http://css-tricks.com/snippets/php/find-urls-in-text-make-links/
	 */
	$mStr = $m[1];
	if(preg_match('#([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#', $mStr)){
		return "<a href=\"mailto:{$m[2]}{$m[3]}\" target=\"_blank\">{$m[1]}{$m[2]}{$m[3]}</a>";
	}
	else{
		$http = (!preg_match("#^https://#i", $mStr)) ? 'http://' : '';
		return "<a href=\"".$http.$mStr."\" target=\"_blank\">".$mStr."</a>";
	}
}
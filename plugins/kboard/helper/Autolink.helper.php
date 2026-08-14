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
	$protected_tags = array();
	$text = preg_replace_callback('/<(iframe|a)\b[^>]*>.*?<\/\1>/is', function($matches) use (&$protected_tags) {
		$hash = '__PROTECTED_TAG_' . count($protected_tags) . '__';
		$protected_tags[$hash] = $matches[0];
		return $hash;
	}, $text);
	
	$text = preg_replace_callback('#(?<![="\'])(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?[^\\s<"\']+|shorts\/[a-zA-Z0-9\-_]+[^\\s<"\']*)|youtu\.be\/[a-zA-Z0-9\-_]+[^\\s<"\']*|vimeo\.com\/\d+[^\\s<"\']*)#i', function($matches) use (&$protected_tags) {
		$hash = '__PROTECTED_TAG_' . count($protected_tags) . '__';
		$protected_tags[$hash] = $matches[0];
		return $hash;
	}, $text);
	
	$text = preg_replace_callback('/<[^>]+>/', function($matches) use (&$protected_tags) {
		$hash = '__PROTECTED_TAG_' . count($protected_tags) . '__';
		$protected_tags[$hash] = $matches[0];
		return $hash;
	}, $text);
	
	$text = preg_replace_callback(
		'#(?<![="\'])(https?:\/\/(?:[-\w]+\.)+(com|net|org|kr|co\.kr|go\.kr|edu|gov|me|xyz|biz|tv|us|asia|store|shop|io|ai|re\.kr|pe\.kr|ne\.kr|or\.kr)(\/[^\s<>"]*)?)#i',
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
		$http = (!preg_match("#^https?://#i", $mStr)) ? 'http://' : '';
		$url = esc_url($http . $mStr);
		$text = esc_html($mStr);
		return '<a href="'.$url.'" target="_blank">'.$text.'</a>';
	}
}

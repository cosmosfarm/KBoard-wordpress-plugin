<?php
if(!defined('ABSPATH')) exit;
/**
 * KBoard 게시글 본문 자동링크
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
function kboard_autolink($text){
	/*
	 * Mark Goldsmith
	 * http://css-tricks.com/snippets/php/find-urls-in-text-make-links/
	 */
	return preg_replace_callback('#(?i)(http|https)?(://)?(([-\w^@]+\.)+(kr|co.kr|go.kr|net|org|edu|gov|me|com|co+)(?:/[^,\s]*|))#', 'kboard_autolink_prependHTTP', $text);
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
?>
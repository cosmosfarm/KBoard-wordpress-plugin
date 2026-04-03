<?php
if(!defined('ABSPATH')) exit;

/**
 * 검색 인덱스 사용 여부를 반환한다.
 * @return boolean
 */
function kboard_use_search_index(){
	return get_option('kboard_use_search_index') === '1';
}

/**
 * 검색 가능한 텍스트로 정규화한다.
 * @param string $text
 * @return string
 */
function kboard_search_normalize_text($text){
	$text = html_entity_decode((string) $text, ENT_QUOTES, 'UTF-8');
	$text = preg_replace('/\[(\/?)(\\w+)([^\\]]*)\]/u', ' ', $text);
	$text = preg_replace('/https?:\/\/[^\s]+/iu', ' ', $text);
	$text = preg_replace('/<iframe\b[^>]*>[\s\S]*?<\/iframe>/iu', ' ', $text);
	$text = preg_replace('/<script\b[^>]*>[\s\S]*?<\/script>/iu', ' ', $text);
	$text = preg_replace('/<style\b[^>]*>[\s\S]*?<\/style>/iu', ' ', $text);
	$text = wp_strip_all_tags($text, true);
	
	if(function_exists('mb_strtolower')){
		$text = mb_strtolower($text, 'UTF-8');
	}
	else{
		$text = strtolower($text);
	}
	
	$text = preg_replace('/[^\p{L}\p{N}\s\-\_\.\#\+\@\/]+/u', ' ', $text);
	$text = preg_replace('/\s+/u', ' ', trim($text));
	return $text;
}

/**
 * MySQL ngram 파서 지원 여부를 확인한다.
 * @return boolean
 */
function kboard_search_supports_ngram(){
	static $supports = null;
	if($supports !== null) return $supports;
	global $wpdb;
	$result = $wpdb->get_var("SELECT @@ngram_token_size");
	$supports = ($result !== null && $result !== false);
	return $supports;
}

/**
 * FULLTEXT BOOLEAN MODE 검색어를 이스케이프한다.
 * 특수 연산자(+, -, >, <, ~, *, (, ), @, ")를 제거한다.
 * @param string $keyword
 * @return string
 */
function kboard_search_escape_boolean($keyword){
	$keyword = trim((string) $keyword);
	$keyword = preg_replace('/[+\-><~*()@"]+/', ' ', $keyword);
	$keyword = preg_replace('/\s+/', ' ', trim($keyword));
	return $keyword;
}

/**
 * search_document 테이블 존재 여부를 확인한다.
 * @return boolean
 */
function kboard_search_document_table_exists(){
	static $exists = null;
	if($exists !== null) return $exists;
	global $wpdb;
	$table = $wpdb->prefix . 'kboard_search_document';
	$result = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
	$exists = ($result === $table);
	return $exists;
}

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
	$text = preg_replace('/\[(\/?)(\w+)([^\]]*)\]/u', ' ', $text);
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
	
	$text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);
	$text = preg_replace('/\s+/u', ' ', trim($text));
	return $text;
}

/**
 * 2-gram 중심 토큰을 생성한다.
 * @param string $text
 * @param int $gram
 * @param int $max_tokens
 * @return array
 */
function kboard_search_tokenize($text, $gram=2, $max_tokens=128){
	$gram = max(2, intval($gram));
	$max_tokens = max(1, intval($max_tokens));
	$text = kboard_search_normalize_text($text);
	if(!$text){
		return array();
	}
	
	$tokens = array();
	$words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
	foreach($words as $word){
		if(!$word){
			continue;
		}
		
		$is_ascii_word = preg_match('/^[a-z0-9]+$/', $word);
		if($is_ascii_word){
			$tokens[] = $word;
		}
		
		if(function_exists('mb_strlen')){
			$length = mb_strlen($word, 'UTF-8');
		}
		else{
			$length = strlen($word);
		}
		
		if($length < $gram){
			if(!$is_ascii_word && $length > 1){
				$tokens[] = $word;
			}
			continue;
		}
		
		$chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
		if(!$chars){
			continue;
		}
		
		$char_count = count($chars);
		for($i=0; $i<=$char_count-$gram; $i++){
			$token = '';
			for($j=0; $j<$gram; $j++){
				$token .= $chars[$i+$j];
			}
			if($token){
				$tokens[] = $token;
			}
		}
	}
	
	if(count($tokens) > $max_tokens){
		$tokens = array_slice($tokens, 0, $max_tokens);
	}
	return $tokens;
}

/**
 * 토큰 빈도수를 반환한다.
 * @param array $tokens
 * @return array
 */
function kboard_search_token_frequencies($tokens){
	$frequencies = array();
	foreach((array) $tokens as $token){
		$token = trim((string) $token);
		if(!$token){
			continue;
		}
		if(isset($frequencies[$token])){
			$frequencies[$token]++;
		}
		else{
			$frequencies[$token] = 1;
		}
	}
	return $frequencies;
}

/**
 * 검색어를 OR/AND 그룹으로 파싱한다.
 * @param string $keyword
 * @return array
 */
function kboard_search_parse_keyword_groups($keyword){
	$keyword = html_entity_decode((string) $keyword, ENT_QUOTES, 'UTF-8');
	$keyword = wp_strip_all_tags($keyword, true);
	if(function_exists('mb_strtolower')){
		$keyword = mb_strtolower($keyword, 'UTF-8');
	}
	else{
		$keyword = strtolower($keyword);
	}
	$keyword = preg_replace('/[^\p{L}\p{N}\s\|&]+/u', ' ', $keyword);
	$keyword = preg_replace('/\s+/u', ' ', trim($keyword));
	if(!$keyword){
		return array();
	}
	
	$groups = array();
	$or_groups = explode('|', $keyword);
	foreach($or_groups as $group_text){
		$group_text = trim($group_text);
		if(!$group_text){
			continue;
		}
		$and_terms = preg_split('/\s*&\s*|\s+/u', $group_text, -1, PREG_SPLIT_NO_EMPTY);
		$group_tokens = array();
		foreach($and_terms as $term){
			$group_tokens = array_merge($group_tokens, kboard_search_tokenize($term, 2, 32));
		}
		$group_tokens = array_values(array_unique($group_tokens));
		if($group_tokens){
			$groups[] = $group_tokens;
		}
	}
	return $groups;
}

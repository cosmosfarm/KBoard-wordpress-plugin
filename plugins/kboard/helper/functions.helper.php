<?php
/**
 * KBoard 워드프레스 게시판 사용자 함수
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */

/**
 * JSON 인코더
 * @param array $val
 * @return string
 */
function kboard_json_encode($val){
	if(function_exists('json_encode')){
		return json_encode($val);
	}
	
	/*
	 * http://kr1.php.net/json_encode#113219
	 */
	
	if(is_string($val)) return '"'.addslashes($val).'"';
	if(is_numeric($val)) return $val;
	if($val === null) return 'null';
	if($val === true) return 'true';
	if($val === false) return 'false';

	$assoc = false;
	$i = 0;
	foreach($val as $k=>$v){
		if($k !== $i++){
			$assoc = true;
			break;
		}
	}
	$res = array();
	foreach($val as $k=>$v){
		$v = kboard_json_encode($v);
		if($assoc){
			$k = '"'.addslashes($k).'"';
			$v = $k.':'.$v;
		}
		$res[] = $v;
	}
	$res = implode(',', $res);
	
	return ($assoc)? '{'.$res.'}' : '['.$res.']';
}
?>
<?php
if(!defined('ABSPATH')) exit;
/**
 * KBoard 워드프레스 게시판 페이지 출력 함수
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
function kboard_pagination($current_page, $total, $limit){
	foreach($_GET as $key=>$value){
		if(is_array($value)){
			$query_strings[] = http_build_query(array(sanitize_key($key)=>$value));
		}
		else if($key == 'mod'){
			if(!in_array($value, array('list', 'history', 'sales'))){
				$value = 'list';
			}
			$query_strings[] = "mod={$value}";
		}
		else if($key != 'pageid' && $value){
			$query_strings[] = sanitize_key($key).'='.urlencode(kboard_htmlclear($value));
		}
	}
	if(isset($query_strings) && $query_strings) $query_strings = '&' . implode('&', $query_strings);
	else $query_strings = '';
	
	$sliding_size = 10;
	$total_page = ceil($total/$limit);
	$paging = '';
	$i = 0;
	
	if($current_page > $sliding_size){
		$i = ($current_page-1) - (($current_page-1) % $sliding_size);
	}
	
	// offset은 윈도의 마지막 페이지 번호다.
	$offset = $i + $sliding_size;
	
	// 윈도의 시작 $i 부터, 윈도우 마지막 까지 출력한다.
	for($i; $i<$offset && $i<$total_page; $i++){
		$page_name = $i + 1;
		// 링크는 적당히 수정
		if($current_page != $page_name){
			$paging .= "<li><a href=\"?pageid={$page_name}{$query_strings}\">{$page_name}</a></li>";
		}
		else{
			$paging .= "<li class=\"active\"><a href=\"?pageid={$page_name}{$query_strings}\" onclick=\"return false\">{$page_name}</a></li>";
		}
	}
	
	// 좌우 이동 화살표 «, »를 출력한다.
	// 처음과 마지막 페이지가 아니라면 링크를 걸어주면 된다.
	if($current_page != 1){
		$prev_page = $current_page - 1;
		$paging = "<li class=\"first-page\"><a href=\"?pageid=1{$query_strings}\">".__('First', 'kboard')."</a></li>" . "<li class=\"prev-page\"><a href=\"?pageid={$prev_page}{$query_strings}\">«</a></li>{$paging}";
	}
	if($current_page != $total_page){
		$next_page = $current_page + 1;
		$paging = "{$paging}<li class=\"next-page\"><a href=\"?pageid={$next_page}{$query_strings}\">»</a></li>" . "<li class=\"last-page\"><a href=\"?pageid={$total_page}{$query_strings}\">".__('Last', 'kboard')."</a></li>";
	}
	
	return $total?$paging:'<li class="active"><a href="#" onclick="return false">1</a></li>';
}
?>
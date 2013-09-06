<?php
/**
 * KBoard 워드프레스 게시판 페이지 출력 함수
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
function kboard_pagination($current_page, $total, $limit){
	foreach($_GET AS $key => $value){
		if($key != 'pageid'){
			$query_strings[] = $key . '=' . kboard_htmlclear($value);
		}
	}
	if($query_strings) $query_strings = '&' . implode('&', $query_strings);
	
	$total_page = ceil($total/$limit);
	$paging;
	$i = 0;
	
	// 움직일 윈도 사이즈
	$sliding_size = 10;
	
	// 만약 윈도 범위가 첫 페이지를 벗어나면 1 ...을 출력한다.
	if($current_page > $sliding_size){
		//$paging .= '<a href=?pageid=$current_page>1</a>... ';
		$i = $current_page - ($current_page % $sliding_size);
	}
	
	// offset은 윈도의 마지막 페이지 번호다.
	$offset = $i + $sliding_size;
	
	// 윈도의 시작 $i 부터, 윈도우 마지막 까지 출력한다.
	for($i; $i < $offset && $i < $total_page; $i++){
		$page_name = $i+ 1;
		// 링크는 적당히 수정
		if($current_page != $page_name){
			$paging .= "<li><a href=\"?pageid={$page_name}{$query_strings}\">{$page_name}</a></li>";
		}
		else{
			$paging .= "<li class=\"active\"><a href=\"?pageid={$page_name}{$query_strings}\">{$page_name}</a></li>";
		}
	}
	
	/*
	// 만약 윈도 범위가 마지막 페이지를 포함하지 못하면 ... N 을 출력한다.
	if(($i + 10 ) < $total_page){
		$paging .= " ... <a href=url?pageid=$total_page>$total_page</a>";
	}
	*/
	
	// 좌우 이동 화살표 <, >를 출력한다.
	// 처음과 마지막 페이지가 아니라면 링크를 걸어주면 된다.
	if($current_page != 1){
		$prev_page = $current_page - 1;
		$paging = "<li><a href=\"?pageid={$prev_page}{$query_strings}\">«</a></li>{$paging}";
	}
	if($current_page != $total_page){
		$next_page = $current_page + 1;
		$paging = "$paging<li><a href=\"?pageid={$next_page}{$query_strings}\">»</a></li>";
	}
	
	return $total?$paging:'<li><a>1</a></li>';
}
?>
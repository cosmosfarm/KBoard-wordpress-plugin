<?php
/**
 * KBoard 판매조회 목록
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBOrderSales {
	
	private $multiple_condition_keys;
	
	var $board;
	var $board_id;
	var $total;
	var $search_condition = array();
	var $start_date;
	var $end_date;
	var $rpp = 10;
	var $page = 1;
	var $resource;
	var $item;
	
	/**
	 * 게시판 리스트를 초기화한다.
	 */
	public function init($user_id=''){
		global $wpdb;
		
		$user_id = intval($user_id);
		if(!$user_id) $user_id = get_current_user_id();
		
		if($this->board_id && $user_id){
			
			$from[] = "`{$wpdb->prefix}kboard_order_item`";
			$where[] = '1';
			$this->search_condition[] = array('key'=>'board_id', 'compare'=>'=', 'value'=>$this->board_id);
			$this->search_condition[] = array('key'=>'item_user_id', 'compare'=>'=', 'value'=>$user_id);
			
			$search_condition = apply_filters('kboard_sales_search_option', $this->search_condition, $this);
			if($search_condition){
				$search_query = $this->getSearchQuery($search_condition);
				if($search_query){
					$where[] = $search_query;
					
					// 기간 검색
					if($this->start_date && $this->end_date){
						$start_date = esc_sql($this->start_date);
						$end_date= esc_sql($this->end_date);
						
						$this->multiple_condition_keys['datetime'] = 'datetime';
						
						$where[] = "(`meta_datetime`.`meta_key`='datetime' AND `meta_datetime`.`meta_value` BETWEEN '{$start_date}' AND '{$end_date}')";
					}
					
					foreach($this->multiple_condition_keys as $condition_name){
						$condition_key = array_search($condition_name, $this->multiple_condition_keys);
						$from[] = "INNER JOIN `{$wpdb->prefix}kboard_order_item_meta` AS `meta_{$condition_key}` ON `{$wpdb->prefix}kboard_order_item`.`order_item_id`=`meta_{$condition_key}`.`order_item_id`";
					}
				}
			}
			
			$offset = ($this->page-1)*$this->rpp;
			
			$this->resource = $wpdb->get_results("SELECT `{$wpdb->prefix}kboard_order_item`.`order_item_id` FROM ".implode(' ', $from)." WHERE ".implode(' AND ', $where)." ORDER BY `{$wpdb->prefix}kboard_order_item`.`order_item_id` DESC LIMIT {$offset},{$this->rpp}");
			$this->total = $wpdb->get_var("SELECT COUNT(*) FROM ".implode(' ', $from)." WHERE ".implode(' AND ', $where));
			
			$wpdb->flush();
		}
		return $this;
	}
	
	/**
	 * 게시판 리스트를 초기화한다.
	 */
	public function getAnalytics($user_id=''){
		global $wpdb;
		
		$results = array();
		
		$user_id = intval($user_id);
		if(!$user_id) $user_id = get_current_user_id();
		
		if($this->board_id && $user_id){
			
			$from[] = "`{$wpdb->prefix}kboard_order_item`";
			$where[] = '1';
			$this->search_condition[] = array('key'=>'board_id', 'compare'=>'=', 'value'=>$this->board_id);
			$this->search_condition[] = array('key'=>'item_user_id', 'compare'=>'=', 'value'=>$user_id);
			
			$search_condition = apply_filters('kboard_sales_search_option', $this->search_condition, $this);
			if($search_condition){
				$search_query = $this->getSearchQuery($search_condition);
				if($search_query){
					$where[] = $search_query;
					
					// 기간 검색
					if($this->start_date && $this->end_date){
						$start_date = esc_sql($this->start_date);
						$end_date= esc_sql($this->end_date);
						
						$this->multiple_condition_keys['datetime'] = 'datetime';
						$this->multiple_condition_keys['total'] = 'total';
						
						$where[] = "(`meta_datetime`.`meta_key`='datetime' AND `meta_datetime`.`meta_value` BETWEEN '{$start_date}' AND '{$end_date}')";
						$where[] = "(`meta_total`.`meta_key`='total')";
					}
					
					foreach($this->multiple_condition_keys as $condition_name){
						$condition_key = array_search($condition_name, $this->multiple_condition_keys);
						$from[] = "INNER JOIN `{$wpdb->prefix}kboard_order_item_meta` AS `meta_{$condition_key}` ON `{$wpdb->prefix}kboard_order_item`.`order_item_id`=`meta_{$condition_key}`.`order_item_id`";
					}
				}
			}
			
			if(isset($this->multiple_condition_keys['datetime'])){
				$results = $wpdb->get_results("SELECT LEFT(`meta_datetime`.`meta_value`, 8) AS `ymd`, SUM(`meta_total`.`meta_value`) AS `total`, COUNT(*) AS `count` FROM ".implode(' ', $from)." WHERE ".implode(' AND ', $where)." GROUP BY `ymd` ORDER BY `ymd` ASC");
				$wpdb->flush();
			}
		}
		return $results;
	}
	
	/**
	 * 검색 쿼리를 반환한다.
	 * @param array $multiple
	 * @param string $relation
	 * @return string
	 */
	public function getSearchQuery($multiple, $relation='AND'){
		if(isset($multiple['relation'])){
			if(in_array($multiple['relation'], array('AND', 'OR'))){
				$relation = $multiple['relation'];
			}
			unset($multiple['relation']);
		}
		
		foreach($multiple as $condition){
			if(isset($condition['relation'])){
				$where[] = $this->getSearchQuery($condition);
			}
			else if(is_array($condition)){
				$condition_key = isset($condition['key']) ? esc_sql(sanitize_key($condition['key'])) : '';
				$condition_value = isset($condition['value']) ? esc_sql(sanitize_text_field($condition['value'])) : '';
				$condition_compare = isset($condition['compare']) ? esc_sql($condition['compare']) : '';
				
				if($condition_key && $condition_value){
					if(!in_array($condition_compare, array('=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE'))){
						$condition_compare = '=';
					}
					
					$this->multiple_condition_keys[$condition_key] = $condition_key;
					$condition_index = array_search($condition_key, $this->multiple_condition_keys);
					
					$where[] = "(`meta_{$condition_index}`.`meta_key`='{$condition_key}' AND `meta_{$condition_index}`.`meta_value` {$condition_compare} '{$condition_value}')";
				}
			}
		}
		
		if(isset($where) && is_array($where)){
			if(count($where) > 1){
				return '(' . implode(" {$relation} ", $where) . ')';
			}
			return implode(" {$relation} ", $where);
		}
		return '';
	}
	
	/**
	 * 검색 옵션을 입력한다.
	 * @param array $search_option
	 */
	public function setSearchOption($search_option){
		$this->search_condition = $search_option;
	}
	
	/**
	 * 검색 조건을 추가한다.
	 * @param string $key
	 * @param string $value
	 * @param string $compare
	 */
	public function addSearchOption($key, $value, $compare='='){
		$this->search_condition[] = array('key'=>$key, 'compare'=>$compare, 'value'=>$value);
	}
	
	/**
	 * 검색할 기간을 설정한다.
	 * @param string $start_date
	 * @param string $end_date
	 */
	public function setDateRange($start_date, $end_date){
		if($start_date){
			$this->start_date = date('Ymd', strtotime($start_date)) . '000000';
		}
		if($end_date){
			$this->end_date = date('Ymd', strtotime($end_date)) . '235959';
		}
	}
	
	/**
	 * 다음 정보를 불러온다.
	 * @return object
	 */
	public function hasNext(){
		if(!$this->resource) return '';
		$current = current($this->resource);
		if($current){
			next($this->resource);
			$this->item = new KBOrderItem();
			$this->item->board = $this->board;
			$this->item->board_id = $this->board_id;
			$this->item->initWithID($current->order_item_id);
			return $this->item;
		}
		unset($this->resource);
		return '';
	}
}
?>
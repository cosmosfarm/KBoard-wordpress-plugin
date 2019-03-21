<?php
/**
 * KBoard 주문조회 목록
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBOrderHistory {
	
	private $multiple_option_keys;
	
	var $init_mode;
	var $board;
	var $board_id;
	var $total;
	var $search_option = array();
	var $rpp = 10;
	var $page = 1;
	var $resource;
	var $order;
	var $item;
	
	/**
	 * 게시판 리스트를 초기화한다.
	 */
	public function initOrder($user_id=''){
		global $wpdb;
		
		$this->init_mode = 'order';
		
		$user_id = intval($user_id);
		if(!$user_id) $user_id = get_current_user_id();
		
		if($this->board_id && $user_id){
			
			$from[] = "`{$wpdb->posts}`";
			$where[] = "`{$wpdb->posts}`.`post_type`='kboard_order'";
			$this->search_option[] = array('key'=>'board_id', 'compare'=>'=', 'value'=>$this->board_id);
			$this->search_option[] = array('key'=>'user_id', 'compare'=>'=', 'value'=>$user_id);
			
			$search_option = apply_filters('kboard_history_search_option', $this->search_option, $this);
			if($search_option){
				$search_query = $this->getSearchQuery($search_option);
				if($search_query){
					$where[] = $search_query;
					
					foreach($this->multiple_option_keys as $condition_name){
						$condition_key = array_search($condition_name, $this->multiple_option_keys);
						if($condition_key == 'title' || $condition_key == 'item_user_name'){
							$from[] = "INNER JOIN `{$wpdb->prefix}kboard_order_item` AS `order_item` ON `order_item`.order_id=`{$wpdb->prefix}posts`.ID";
							$from[] = "INNER JOIN `{$wpdb->prefix}kboard_order_item_meta` AS `meta_{$condition_key}` ON `order_item`.`order_item_id`=`meta_{$condition_key}`.`order_item_id`";
						}
						else{
							$from[] = "INNER JOIN `{$wpdb->postmeta}` AS `meta_{$condition_key}` ON `{$wpdb->posts}`.`ID`=`meta_{$condition_key}`.`post_id`";
						}
					}
				}
			}
			
			$offset = ($this->page-1)*$this->rpp;
			
			$this->resource = $wpdb->get_results("SELECT `{$wpdb->posts}`.`ID` FROM ".implode(' ', $from)." WHERE ".implode(' AND ', $where)." ORDER BY `{$wpdb->posts}`.`ID` DESC LIMIT {$offset},{$this->rpp}");
			$this->total = $wpdb->get_var("SELECT COUNT(*) FROM ".implode(' ', $from)." WHERE ".implode(' AND ', $where));
			
			$wpdb->flush();
		}
		return $this;
	}
	
	/**
	 * 게시판 리스트를 초기화한다.
	 */
	public function initOrderWithKey($nonmember_key){
		global $wpdb;
		
		$this->init_mode = 'order';
		
		if($this->board_id && $nonmember_key){
			
			$from[] = "`{$wpdb->posts}`";
			$where[] = "`{$wpdb->posts}`.`post_type`='kboard_order'";
			$where[] = "`{$wpdb->posts}`.`post_author`='0'";
			$this->search_option[] = array('key'=>'board_id', 'compare'=>'=', 'value'=>$this->board_id);
			$this->search_option[] = array('key'=>'nonmember_key', 'compare'=>'=', 'value'=>$nonmember_key);
			
			$search_option = apply_filters('kboard_history_search_option', $this->search_option, $this);
			if($search_option){
				$search_query = $this->getSearchQuery($search_option);
				if($search_query){
					$where[] = $search_query;
					
					foreach($this->multiple_option_keys as $condition_name){
						$condition_key = array_search($condition_name, $this->multiple_option_keys);
						if($condition_key == 'title' || $condition_key == 'item_user_name'){
							$from[] = "INNER JOIN `{$wpdb->prefix}kboard_order_item` AS `order_item` ON `order_item`.order_id=`{$wpdb->prefix}posts`.ID";
							$from[] = "INNER JOIN `{$wpdb->prefix}kboard_order_item_meta` AS `meta_{$condition_key}` ON `order_item`.`order_item_id`=`meta_{$condition_key}`.`order_item_id`";
						}
						else{
							$from[] = "INNER JOIN `{$wpdb->postmeta}` AS `meta_{$condition_key}` ON `{$wpdb->posts}`.`ID`=`meta_{$condition_key}`.`post_id`";
						}
					}
				}
			}
			
			$offset = ($this->page-1)*$this->rpp;
			
			$this->resource = $wpdb->get_results("SELECT `{$wpdb->posts}`.`ID` FROM ".implode(' ', $from)." WHERE ".implode(' AND ', $where)." ORDER BY `{$wpdb->posts}`.`ID` DESC LIMIT {$offset},{$this->rpp}");
			$this->total = $wpdb->get_var("SELECT COUNT(*) FROM ".implode(' ', $from)." WHERE ".implode(' AND ', $where));
		}
		return $this;
	}
	
	/**
	 * 게시판 리스트를 초기화한다.
	 */
	public function initOrderItem($user_id=''){
		global $wpdb;
		
		$this->init_mode = 'order_item';
		
		$user_id = intval($user_id);
		if(!$user_id) $user_id = get_current_user_id();
		
		if($this->board_id && $user_id){
			
			$from[] = "`{$wpdb->prefix}kboard_order_item`";
			$where[] = '1=1';
			$this->search_option[] = array('key'=>'board_id', 'compare'=>'=', 'value'=>$this->board_id);
			$this->search_option[] = array('key'=>'order_user_id', 'compare'=>'=', 'value'=>$user_id);
			
			$search_option = apply_filters('kboard_history_search_option', $this->search_option, $this);
			if($search_option){
				$search_query = $this->getSearchQuery($search_option);
				if($search_query){
					$where[] = $search_query;
					
					foreach($this->multiple_option_keys as $condition_name){
						$condition_key = array_search($condition_name, $this->multiple_option_keys);
						$from[] = "INNER JOIN `{$wpdb->prefix}kboard_order_item_meta` AS `meta_{$condition_key}` ON `{$wpdb->prefix}kboard_order_item`.`order_item_id`=`meta_{$condition_key}`.`order_item_id`";
					}
				}
			}
			
			$offset = ($this->page-1)*$this->rpp;
			
			$this->resource = $wpdb->get_results("SELECT `{$wpdb->prefix}kboard_order_item`.`order_item_id` FROM ".implode(' ', $from)." WHERE ".implode(' AND ', $where)." ORDER BY `{$wpdb->prefix}kboard_order_item`.`order_item_id` DESC LIMIT {$offset},{$this->rpp}");
			$this->total = $wpdb->get_var("SELECT COUNT(*) FROM ".implode(' ', $from)." WHERE ".implode(' AND ', $where));
		}
		return $this;
	}
	
	/**
	 * 검색 쿼리를 반환한다.
	 * @param array $multiple
	 * @param string $relation
	 * @return string
	 */
	public function getSearchQuery($multiple, $relation='AND'){
		global $wpdb;
		
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
				if(is_array($condition['value'])){
					$condition['value'] = implode(',', $condition['value']);
				}
				
				$condition_key = isset($condition['key']) ? esc_sql(sanitize_key($condition['key'])) : '';
				$condition_value = isset($condition['value']) ? esc_sql(sanitize_text_field($condition['value'])) : '';
				$condition_compare = isset($condition['compare']) ? esc_sql($condition['compare']) : '';
				$condition_wildcard= isset($condition['wildcard']) ? esc_sql($condition['wildcard']) : '';
				
				if($condition_key && $condition_value){
					if(in_array($condition_compare, array('IN', 'NOT IN'))){
						if(!isset($condition['table']) || !$condition['table'] || $condition['table'] == "{$wpdb->prefix}kboard_order_item_meta"){
							$this->multiple_option_keys[$condition_key] = $condition_key;
							$condition_index = array_search($condition_key, $this->multiple_option_keys);
							$where[] = "(`meta_{$condition_index}`.`meta_key`='{$condition_key}' AND `meta_{$condition_index}`.`meta_value` {$condition_compare} ({$condition_value}))";
						}
						else{
							$this->multiple_postmeta_keys[$condition_key] = $condition_key;
							$condition_index = array_search($condition_key, $this->multiple_postmeta_keys);
							$where[] = "(`postmeta_{$condition_index}`.`meta_key`='{$condition_key}' AND `postmeta_{$condition_index}`.`meta_value` {$condition_compare} ({$condition_value}))";
						}
					}
					else{
						if(!in_array($condition_compare, array('=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE'))){
							$condition_compare = '=';
						}
						
						switch($condition_wildcard){
							case 'left': $condition_value = "%{$condition_value}"; break;
							case 'right': $condition_value = "{$condition_value}%"; break;
							case 'both': $condition_value = "%{$condition_value}%"; break;
						}
						
						if(!isset($condition['table']) || !$condition['table'] || $condition['table'] == "{$wpdb->prefix}kboard_order_item_meta"){
							$this->multiple_option_keys[$condition_key] = $condition_key;
							$condition_index = array_search($condition_key, $this->multiple_option_keys);
							$where[] = "(`meta_{$condition_index}`.`meta_key`='{$condition_key}' AND `meta_{$condition_index}`.`meta_value` {$condition_compare} '{$condition_value}')";
						}
						else{
							$this->multiple_postmeta_keys[$condition_key] = $condition_key;
							$condition_index = array_search($condition_key, $this->multiple_postmeta_keys);
							$where[] = "(`postmeta_{$condition_index}`.`meta_key`='{$condition_key}' AND `postmeta_{$condition_index}`.`meta_value` {$condition_compare} '{$condition_value}')";
						}
					}
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
	 * 검색 옵션의 데이터를 반환한다.
	 * @param array $associative
	 * @param array $search_option
	 * @return string
	 */
	public function getSearchOptionValue($associative, $search_option=array()){
		if(!$search_option) $search_option = $this->search_option;
		$key = array_shift($associative);
		if(isset($search_option[$key])){
			if(is_array($search_option[$key])){
				return $this->getSearchOptionValue($associative, $search_option[$key]);
			}
			else{
				return $search_option[$key];
			}
		}
		return '';
	}
	
	/**
	 * 검색 옵션을 입력한다.
	 * @param array $search_option
	 */
	public function setSearchOption($search_option){
		$this->search_option = $search_option;
	}
	
	/**
	 * 검색 조건을 추가한다.
	 * @param string $key
	 * @param string $value
	 * @param string $compare
	 */
	public function addSearchOption($key, $value, $compare='='){
		$this->search_option[] = array('key'=>$key, 'compare'=>$compare, 'value'=>$value);
	}
	
	/**
	 * 다음 정보를 불러온다.
	 * @return object
	 */
	public function hasNext(){
		if(!$this->resource) return '';
		$current = current($this->resource);
		if($current){
			if($this->init_mode == 'order'){
				next($this->resource);
				$this->order = new KBOrder();
				$this->order->board = $this->board;
				$this->order->board_id = $this->board_id;
				$this->order->initWithID($current->ID);
				return $this->order;
			}
			else if($this->init_mode == 'order_item'){
				next($this->resource);
				$this->item = new KBOrderItem();
				$this->item->board = $this->board;
				$this->item->board_id = $this->board_id;
				$this->item->initWithID($current->order_item_id);
				return $this->item;
			}
		}
		unset($this->resource);
		return '';
	}
}
?>
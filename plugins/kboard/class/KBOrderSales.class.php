<?php
/**
 * KBoard 판매조회 목록
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBOrderSales {
	
	private $multiple_option_keys;
	private $multiple_postmeta_keys;
	
	var $board;
	var $board_id;
	var $total;
	var $search_option = array();
	var $start_date;
	var $end_date;
	var $category1;
	var $category2;
	var $rpp = 10;
	var $page = 1;
	var $resource;
	var $item;
	var $queries = array();
	
	/**
	 * 게시판 리스트를 초기화한다.
	 */
	public function init($user_id=''){
		global $wpdb;
		
		$user_id = intval($user_id);
		if(!$user_id) $user_id = get_current_user_id();
		
		if($this->board_id && $user_id){
			
			$from[] = "`{$wpdb->prefix}kboard_order_item`";
			$where[] = '1=1';
			$this->search_option[] = array('key'=>'board_id', 'compare'=>'=', 'value'=>$this->board_id, 'table'=>"{$wpdb->prefix}kboard_order_item_meta");
			
			if(!$this->board->isAdmin()){
				$this->search_option[] = array('key'=>'item_user_id', 'compare'=>'=', 'value'=>$user_id, 'table'=>"{$wpdb->prefix}kboard_order_item_meta");
			}
			
			$search_option = apply_filters('kboard_sales_search_option', $this->search_option, $this);
			if($search_option){
				$search_query = $this->getSearchQuery($search_option);
				if($search_query){
					$where[] = $search_query;
					
					// 기간 검색
					if($this->start_date && $this->end_date){
						$start_date = esc_sql($this->start_date);
						$end_date = esc_sql($this->end_date);
						
						$this->multiple_option_keys['datetime'] = 'datetime';
						
						$where[] = "(`meta_datetime`.`meta_key`='datetime' AND `meta_datetime`.`meta_value` BETWEEN '{$start_date}' AND '{$end_date}')";
					}
					
					// 카테고리 검색
					if($this->category1 || $this->category2){
						$category1 = esc_sql($this->category1);
						$category2 = esc_sql($this->category2);
						
						$this->multiple_option_keys['content_category'] = 'content_category';
						
						if($category1 && $category2){
							$sub_query = "SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` WHERE `board_id`='{$this->board_id}' AND (`category1` LIKE '%{$category1}%' AND `category2` LIKE '%{$category2}%')";
						}
						else if($category1){
							$sub_query = "SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` WHERE `board_id`='{$this->board_id}' AND `category1` LIKE '%{$category1}%'";
						}
						else if($category2){
							$sub_query = "SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` WHERE `board_id`='{$this->board_id}' AND `category2` LIKE '%{$category2}%'";
						}
						
						$where[] = "(`meta_content_category`.`meta_key`='uid' AND `meta_content_category`.`meta_value` IN ({$sub_query}))";
					}
					
					if(is_array($this->multiple_option_keys) && $this->multiple_option_keys){
						foreach($this->multiple_option_keys as $condition_name){
							$condition_key = array_search($condition_name, $this->multiple_option_keys);
							$from[] = "INNER JOIN `{$wpdb->prefix}kboard_order_item_meta` AS `meta_{$condition_key}` ON `{$wpdb->prefix}kboard_order_item`.`order_item_id`=`meta_{$condition_key}`.`order_item_id`";
						}
					}
					
					if(is_array($this->multiple_postmeta_keys) && $this->multiple_postmeta_keys){
						foreach($this->multiple_postmeta_keys as $condition_name){
							$condition_key = array_search($condition_name, $this->multiple_postmeta_keys);
							$from[] = "INNER JOIN `{$wpdb->postmeta}` AS `postmeta_{$condition_key}` ON `{$wpdb->prefix}kboard_order_item`.`order_id`=`postmeta_{$condition_key}`.`post_id`";
						}
					}
				}
			}
			
			$offset = ($this->page-1)*$this->rpp;
			
			$this->queries = array();
			$this->queries['results'] = "SELECT `{$wpdb->prefix}kboard_order_item`.`order_item_id` FROM ".implode(' ', $from)." WHERE ".implode(' AND ', $where)." ORDER BY `{$wpdb->prefix}kboard_order_item`.`order_item_id` DESC LIMIT {$offset},{$this->rpp}";
			$this->queries['total'] = "SELECT COUNT(*) FROM ".implode(' ', $from)." WHERE ".implode(' AND ', $where);
			
			$this->resource = $wpdb->get_results($this->queries['results']);
			$this->total = $wpdb->get_var($this->queries['total']);
			
			$wpdb->flush();
		}
		return $this;
	}
	
	/**
	 * 게시판 통계 자료를 초기화한다.
	 */
	public function getAnalytics($args){
		global $wpdb;
		
		$this->queries = array();
		$results = array();
		
		if(isset($args['user_id'])){
			$user_id = intval($args['user_id']);
		}
		else{
			$user_id = get_current_user_id();
		}
		
		if(isset($args['collection'])){
			$collection = $args['collection'];
		}
		if($collection != 'daily' && $collection != 'monthly' && $collection != 'yearly' && $collection != 'total_sum'){
			$collection = 'daily';
		}
		
		if($this->board_id && $user_id){
			
			$from[] = "`{$wpdb->prefix}kboard_order_item`";
			$where[] = '1=1';
			$this->search_option[] = array('key'=>'board_id', 'compare'=>'=', 'value'=>$this->board_id, 'table'=>"{$wpdb->prefix}kboard_order_item_meta");
			
			if(!$this->board->isAdmin()){
				$this->search_option[] = array('key'=>'item_user_id', 'compare'=>'=', 'value'=>$user_id, 'table'=>"{$wpdb->prefix}kboard_order_item_meta");
			}
			
			$search_option = apply_filters('kboard_sales_analytics_option', $this->search_option, $this);
			if($search_option){
				$search_query = $this->getSearchQuery($search_option);
				if($search_query){
					$where[] = $search_query;
					
					// 기간 검색
					if($this->start_date && $this->end_date){
						$start_date = esc_sql($this->start_date);
						$end_date= esc_sql($this->end_date);
						
						$this->multiple_option_keys['datetime'] = 'datetime';
						$this->multiple_option_keys['total'] = 'total';
						
						$where[] = "(`meta_datetime`.`meta_key`='datetime' AND `meta_datetime`.`meta_value` BETWEEN '{$start_date}' AND '{$end_date}')";
						$where[] = "(`meta_total`.`meta_key`='total')";
					}
					
					// 카테고리 검색
					if($this->category1 || $this->category2){
						$category1 = esc_sql($this->category1);
						$category2 = esc_sql($this->category2);
						
						$this->multiple_option_keys['content_category'] = 'content_category';
						
						if($category1 && $category2){
							$sub_query = "SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` WHERE `board_id`='{$this->board_id}' AND (`category1` LIKE '%{$category1}%' AND `category2` LIKE '%{$category2}%')";
						}
						else if($category1){
							$sub_query = "SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` WHERE `board_id`='{$this->board_id}' AND `category1` LIKE '%{$category1}%'";
						}
						else if($category2){
							$sub_query = "SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` WHERE `board_id`='{$this->board_id}' AND `category2` LIKE '%{$category2}%'";
						}
						
						$where[] = "(`meta_content_category`.`meta_key`='uid' AND `meta_content_category`.`meta_value` IN ({$sub_query}))";
					}
					
					if(is_array($this->multiple_option_keys) && $this->multiple_option_keys){
						foreach($this->multiple_option_keys as $condition_name){
							$condition_key = array_search($condition_name, $this->multiple_option_keys);
							$from[] = "INNER JOIN `{$wpdb->prefix}kboard_order_item_meta` AS `meta_{$condition_key}` ON `{$wpdb->prefix}kboard_order_item`.`order_item_id`=`meta_{$condition_key}`.`order_item_id`";
						}
					}
					
					if(is_array($this->multiple_postmeta_keys) && $this->multiple_postmeta_keys){
						foreach($this->multiple_postmeta_keys as $condition_name){
							$condition_key = array_search($condition_name, $this->multiple_postmeta_keys);
							$from[] = "INNER JOIN `{$wpdb->postmeta}` AS `postmeta_{$condition_key}` ON `{$wpdb->prefix}kboard_order_item`.`order_id`=`postmeta_{$condition_key}`.`post_id`";
						}
					}
				}
			}
			
			if(isset($this->multiple_option_keys['datetime'])){
				if($collection == 'daily'){
					$this->queries['results'] = "SELECT LEFT(`meta_datetime`.`meta_value`, 8) AS `data_key`, SUM(`meta_total`.`meta_value`) AS `total`, COUNT(*) AS `count` FROM ".implode(' ', $from)." WHERE ".implode(' AND ', $where)." GROUP BY `data_key` ORDER BY `data_key` ASC";
					$results = $wpdb->get_results($this->queries['results']);
				}
				else if($collection == 'monthly'){
					$this->queries['results'] = "SELECT LEFT(`meta_datetime`.`meta_value`, 6) AS `data_key`, SUM(`meta_total`.`meta_value`) AS `total`, COUNT(*) AS `count` FROM ".implode(' ', $from)." WHERE ".implode(' AND ', $where)." GROUP BY `data_key` ORDER BY `data_key` ASC";
					$results = $wpdb->get_results($this->queries['results']);
				}
				else if($collection == 'yearly'){
					$this->queries['results'] = "SELECT LEFT(`meta_datetime`.`meta_value`, 4) AS `data_key`, SUM(`meta_total`.`meta_value`) AS `total`, COUNT(*) AS `count` FROM ".implode(' ', $from)." WHERE ".implode(' AND ', $where)." GROUP BY `data_key` ORDER BY `data_key` ASC";
					$results = $wpdb->get_results($this->queries['results']);
				}
				else if($collection == 'total_sum'){
					$this->queries['results'] = "SELECT SUM(`meta_total`.`meta_value`) AS `total`, COUNT(*) AS `count` FROM ".implode(' ', $from)." WHERE ".implode(' AND ', $where);
					$results = $wpdb->get_row($this->queries['results']);
				}
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
	 * @param string $table
	 */
	public function addSearchOption($key, $value, $compare='=', $table=''){
		global $wpdb;
		$this->search_option[] = array('key'=>$key, 'compare'=>$compare, 'value'=>$value, 'table'=>($table ? $table : "{$wpdb->prefix}kboard_order_item_meta"));
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
	 * 상품 카테고리1을 설정한다.
	 * @param string $category
	 */
	public function setContentCategory1($category){
		$this->category1 = sanitize_text_field($category);
	}
	
	/**
	 * 상품 카테고리2를 설정한다.
	 * @param string $category
	 */
	public function setContentCategory2($category){
		$this->category2 = sanitize_text_field($category);
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
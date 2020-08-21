<?php
/**
 * KBoard 주문 정보
 * @link www.cosmosfarm.com
 * @copyright Copyright 2020 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBOrder {
	
	var $order_id;
	var $board;
	var $title;
	var $row;
	var $amount;
	var $items;
	var $items_count;
	var $use_points;
	
	public function __construct($order_id=''){
		$this->row = new stdClass();
		$this->amount = 0;
		$this->items = array();
		$this->items_count = 0;
		$this->use_points = 0;
	}
	
	public function __get($key){
		$key = sanitize_key($key);
		if(isset($this->row->{$key})){
			return $this->row->{$key};
		}
		return apply_filters('kboard_order_default_value', '', $key, $this->board);
	}
	
	public function __set($key, $value){
		$key = sanitize_key($key);
		$value = sanitize_text_field($value);
		
		if($key == 'use_points'){
			// 포인트 사용시 -값은 있을 수 없음
			if($value <= 0) $value = 0;
		}
		
		$value = apply_filters('kboard_order_update_value', $value, $key, $this->board);
		
		$this->row->{$key} = $value;
	}
	
	public function initWithID($order_id){
		$this->row = new stdClass();
		$this->amount = 0;
		$this->items = array();
		$this->items_count = 0;
		$this->use_points = 0;
		$this->order_id = intval($order_id);
		if($this->order_id){
			$this->row = get_post($this->order_id);
			if(!$this->row){
				$this->order_id = '';
			}
		}
	}
	
	public function initWithImpUID($imp_uid){
		$this->row = new stdClass();
		$this->amount = 0;
		$this->items = array();
		$this->items_count = 0;
		$this->use_points = 0;
		$this->order_id = 0;
		if($imp_uid){
			$orders = get_posts(array(
				'post_type' => 'kboard_order',
				'meta_query' => array(array('key'=>'imp_uid', 'value'=>$imp_uid))
			));
			foreach($orders as $post){
				$this->row = $post;
				$this->order_id = $post->ID;
			}
		}
	}
	
	public function initWithMerchantUID($merchant_uid){
		$this->row = new stdClass();
		$this->amount = 0;
		$this->items = array();
		$this->items_count = 0;
		$this->use_points = 0;
		$this->order_id = 0;
		if($merchant_uid){
			$orders = get_posts(array(
				'post_type' => 'kboard_order',
				'meta_query' => array(array('key'=>'merchant_uid', 'value'=>$merchant_uid))
			));
			foreach($orders as $post){
				$this->row = $post;
				$this->order_id = $post->ID;
			}
		}
	}
	
	/**
	 * 주문 정보를 초기화한다.
	 */
	public function initOrder(){
		if(isset($_POST['kboard_order']) && is_array($_POST['kboard_order'])){
			foreach($_POST['kboard_order'] as $key=>$value){
				if($key == 'use_points'){
					if($this->board->isUsePointOrder()){
						// 실제로 포인트가 있는지 체크 해야함
						$value = intval($value);
						$balance = mycred_get_users_balance(get_current_user_id());
						if($balance < 0){
							$balance = 0;
						}
						if($value > 0 && $value > $balance){
							$value = $balance;
						}
					}
					else{
						$value = 0;
					}
				}
				
				$this->{$key} = $value;
			}
		}
	}
	
	/**
	 * 주문 항목을 초기화한다.
	 */
	public function initOrderItems(){
		global $wpdb;
		
		$this->amount = 0;
		
		if(!$this->order_id && $this->board_id && isset($_POST['kboard_order_item']) && is_array($_POST['kboard_order_item'])){
			foreach($_POST['kboard_order_item'][$this->board_id] as $order_item_key=>$order_item){
				$order_item_key = intval($order_item_key);
				if(!$order_item_key) continue;
				if(!isset($order_item['uid']) || !$order_item['uid']) continue;
				if(!isset($order_item['quantity']) || $order_item['quantity'] <= 0) continue;
				if(isset($order_item['delete']) && $order_item['delete']) continue;
				
				$content = new KBContent();
				$content->initWithUID($order_item['uid']);
				
				// 주문 가격 체크
				if($content->option->price){
					if(!isset($order_item['price']) || !$order_item['price']) continue;
					if($content->option->price != $order_item['price']) continue;
				}
				
				// 적립 포인트 체크
				if(isset($order_item['reward_point']) && $order_item['reward_point']){
					if($content->option->reward_point != $order_item['reward_point']) continue;
				}
				
				$item = new KBOrderItem();
				
				foreach($order_item as $key=>$value){
					$item->{$key} = $value;
				}
				
				$item->board = $this->board;
				$item->board_id = $this->board_id;
				$item->uid = intval($item->uid);
				$item->title = wp_strip_all_tags($item->title);
				$item->price = floatval($item->price);
				$item->use_points = floatval($this->use_points);
				$item->reward_point = floatval($item->reward_point);
				$item->order_status = $item->price > 0 ? 'pay_waiting' : 'paid';
				$item->quantity = intval($item->quantity);
				$item->total = $item->price * $item->quantity;
				$item->total_reward_point = $item->reward_point * $item->quantity;
				
				if($content->member_uid){
					$item_user = get_userdata($content->member_uid);
					$item->item_user_id = $item_user->ID;
					$item->item_user_name = $item_user->display_name;
				}
				
				$this->amount += $item->total;
				$this->items[$order_item_key] = $item;
				$this->items_count = count($this->items);
				
				// 총 결제금액 이상으로 포인트를 사용할 수 없다.
				if($this->board->isUsePointOrder() && $this->use_points > $this->amount){
					$this->use_points = $this->amount;
				}
				
				// 주문 제목 생성
				$first_item = reset($this->items);
				$this->title = wp_strip_all_tags($first_item->title);
				if($this->items_count > 1){
					$this->title = $this->title . ' (+'.($this->items_count-1).')';
				}
			}
		}
		else if($this->order_id){
			$results = $wpdb->get_results("SELECT `order_item_id` FROM `{$wpdb->prefix}kboard_order_item` WHERE `order_id`='{$this->order_id}'");
			foreach($results as $item){
				$this->items[$item->order_item_id] = new KBOrderItem();
				$this->items[$item->order_item_id]->initWithID($item->order_item_id);
				$this->items[$item->order_item_id]->board = $this->board;
				$this->items[$item->order_item_id]->total = $this->items[$item->order_item_id]->price * $this->items[$item->order_item_id]->quantity;
				$this->amount += $this->items[$item->order_item_id]->total;
				$this->items_count = count($this->items);
			}
		}
		else{
			$this->items = array();
			$this->items_count = 0;
		}
	}
	
	public function isEmptyCart(){
		if(!$this->items){
			return true;
		}
		return false;
	}
	
	/**
	 * 주문 입력 필드 이름을 반환한다.
	 * @param string $name
	 * @return string
	 */
	public function getFieldName($name){
		$name = sanitize_key($name);
		return "kboard_order[{$name}]";
	}
	
	/**
	 * 아이템 입력 필드 이름을 반환한다.
	 * @param string $item_key
	 * @param string $name
	 * @return string
	 */
	public function getItemFieldName($item_key, $name){
		if($this->board_id){
			$item_key = intval($item_key);
			$name = sanitize_key($name);
			return "kboard_order_item[{$this->board_id}][{$item_key}][{$name}]";
		}
		return '';
	}
	
	public function getPasswordFieldName(){
		return 'kboard_order_password';
	}
	
	public function getTotal(){
		return floatval($this->amount);
	}
	
	public function getAmount(){
		return floatval($this->amount-$this->use_points);
	}
	
	/**
	 * 주문을 저장한다.
	 */
	public function create(){
		if($this->items_count > 0){
			$post_password = isset($_POST['kboard_order_password'])?$_POST['kboard_order_password']:'';
			
			if(is_user_logged_in()){
				$order_user = wp_get_current_user();
				$this->user_id = $order_user->ID;
				$this->user_name = $order_user->display_name;
			}
			else{
				$this->nonmember_key = kboard_hash($this->email, $this->name . $post_password);
			}
			
			$order_id = wp_insert_post(array(
					'post_author'   => $this->user_id,
					'post_title'    => $this->title,
					'post_content'  => '',
					'post_status'   => 'publish',
					'comment_status'=> 'closed',
					'ping_status'   => 'closed',
					'post_password' => wp_hash_password($post_password),
					'post_name'     => sanitize_title($this->title),
					'post_parent'   => $this->board_id,
					'post_type'     => 'kboard_order',
					'meta_input'    => get_object_vars($this->row)
			));
			
			if(is_wp_error($order_id)){
				$this->order_id = '';
			}
			else{
				$this->order_id = $order_id;
			}
		}
		else{
			$this->order_id = '';
		}
	}
	
	/**
	 * 주문 정보를 저장한다.
	 * @param array $data
	 */
	public function update($data){
		if($this->order_id){
			foreach($data as $meta_key=>$meta_value){
				$this->{$meta_key} = $meta_value;
				update_post_meta($this->order_id, $meta_key, $meta_value);
			}
		}
	}
	
	/**
	 * 주문 항목을 저장한다.
	 */
	public function createItems($data=array()){
		if($this->order_id){
			foreach($this->items as $item){
				$item->board = $this->board;
				$item->board_id = $this->board_id;
				$item->order_id = $this->order_id;
				$item->create();
				
				if($data){
					$item->update($data);
				}
			}
		}
	}
}
?>
<?php
/**
 * KBoard 주문 정보 아이템
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBOrderItem {
	
	var $order_item_id;
	var $board;
	var $content;
	var $order;
	var $row;
	
	public function __construct(){
		$this->row = new stdClass();
	}
	
	public function __get($key){
		$key = sanitize_key($key);
		if(isset($this->row->{$key})){
			return $this->row->{$key};
		}
		return '';
	}
	
	public function __set($key, $value){
		$key = sanitize_key($key);
		$value = sanitize_text_field($value);
		$this->row->{$key} = $value;
	}
	
	public function initWithID($order_item_id){
		global $wpdb;
		
		$this->order_item_id = intval($order_item_id);
		if($this->order_item_id){
			$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_order_item_meta` WHERE `order_item_id`='$this->order_item_id'");
			if($results){
				foreach($results as $row){
					$this->{$row->meta_key} = $row->meta_value;
				}
			}
			else{
				$this->order_item_id = '';
			}
		}
		
		$this->content = new KBContent();
		if($this->uid){
			$this->content->initWithUID($this->uid);
		}
		
		$this->order = new KBOrder();
		if($this->order_id){
			$this->order->initWithID($this->order_id);
		}
	}
	
	public function getContent(){
		return $this->content;
	}
	
	public function getOrder(){
		return $this->order;
	}
	
	public function create(){
		global $wpdb;
		
		if($this->order_id){
			$wpdb->insert("{$wpdb->prefix}kboard_order_item", array('order_id'=>$this->order_id), array('%d'));
			$this->order_item_id = $wpdb->insert_id;
			
			$this->datetime = date('YmdHis', current_time('timestamp'));
			
			if(is_user_logged_in()){
				$order_user = wp_get_current_user();
				$this->order_user_id = $order_user->ID;
				$this->order_user_name = $order_user->display_name;
			}
			
			if($this->order_item_id){
				foreach($this->row as $meta_key=>$meta_value){
					$meta_key = esc_sql($meta_key);
					$meta_value = esc_sql($meta_value);
					$wpdb->insert("{$wpdb->prefix}kboard_order_item_meta", array('order_item_id'=>$this->order_item_id, 'meta_key'=>$meta_key, 'meta_value'=>$meta_value), array('%d', '%s', '%s'));
				}
			}
		}
	}
	
	public function update($data){
		global $wpdb;
		
		if($this->order_item_id){
			foreach($data as $meta_key=>$meta_value){
				$this->{$meta_key} = $meta_value;
				$meta_key = esc_sql($meta_key);
				$meta_value = esc_sql($meta_value);
				$wpdb->update("{$wpdb->prefix}kboard_order_item_meta", array('meta_value'=>$meta_value), array('order_item_id'=>$this->order_item_id, 'meta_key'=>$meta_key), array('%s'), array('%d', '%s'));
			}
		}
	}
	
	/**
	 * 리워드 포인트를 지급한다.
	 */
	public function addUserRewardPoint(){
		if($this->order_item_id && $this->order_user_id && $this->total_reward_point){
			if(function_exists('mycred_add')){
				mycred_add('kboard_order', $this->order_user_id, $this->total_reward_point, __('Add rewards points', 'kboard'));
			}
		}
	}
	
	/**
	 * 지급된 리워드 포인트를 취소한다.
	 */
	public function cancelUserRewardPoint(){
		if($this->order_item_id && $this->order_user_id && $this->total_reward_point){
			if(function_exists('mycred_add')){
				mycred_add('kboard_order', $this->order_user_id, ($this->total_reward_point*-1), __('Cancel rewards points', 'kboard'));
			}
		}
	}
}
?>
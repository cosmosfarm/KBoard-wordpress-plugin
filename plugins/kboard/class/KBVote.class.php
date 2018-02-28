<?php
/**
 * KBoard KBVote
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBVote {
	
	static $TYPE_DOCUMENT = 'document';
	static $TYPE_COMMENT = 'commemt';
	
	static $VOTE_LIKE = 'like';
	static $VOTE_UNLIKE = 'unlike';
	
	/**
	 * 투표 정보를 입력한다.
	 * @param array $args
	 * @return string
	 */
	public function insert($args){
		global $wpdb;
		
		$args = $this->filter($args);
		if(!$args) return '';
		
		if($args['user_id']){
			$data['target_uid'] = intval($args['target_uid']);
			$data['target_type'] = esc_sql($args['target_type']);
			$data['target_vote'] = esc_sql($args['target_vote']);
			$data['user_id'] = intval($args['user_id']);
			$data['ip_address'] = '';
			$data['created'] = date('YmdHis', current_time('timestamp'));
		}
		else{
			$data['target_uid'] = intval($args['target_uid']);
			$data['target_type'] = esc_sql($args['target_type']);
			$data['target_vote'] = esc_sql($args['target_vote']);
			$data['user_id'] = 0;
			$data['ip_address'] = esc_sql($args['ip_address']);
			$data['created'] = date('YmdHis', current_time('timestamp'));
		}
		
		$wpdb->insert("{$wpdb->prefix}kboard_vote", $data, array('%d', '%s', '%s', '%d', '%s', '%s'));
		return $wpdb->insert_id;
	}
	
	/**
	 * 투표 정보를 삭제한다.
	 * @param array $args
	 * @return boolean
	 */
	public function delete($args){
		global $wpdb;
		
		$args = $this->filter($args);
		if(!$args) return false;
		
		if($args['user_id']){
			$data['target_uid'] = intval($args['target_uid']);
			$data['target_type'] = esc_sql($args['target_type']);
			//$data['target_vote'] = esc_sql($args['target_vote']);
			$data['user_id'] = intval($args['user_id']);
		}
		else{
			$data['target_uid'] = intval($args['target_uid']);
			$data['target_type'] = esc_sql($args['target_type']);
			//$data['target_vote'] = esc_sql($args['target_vote']);
			$data['ip_address'] = esc_sql($args['ip_address']);
		}
		
		$wpdb->delete("{$wpdb->prefix}kboard_vote", $data, array('%d', '%s', '%s', '%d'));
		return true;
	}
	
	/**
	 * 투표 정보가 있는지 확인한다.
	 * @param array $args
	 * @return boolean|string
	 */
	public function isExists($args){
		global $wpdb;
		
		$args = $this->filter($args);
		if(!$args) return -1;
		
		if($args['user_id']){
			$data['target_uid'] = intval($args['target_uid']);
			$data['target_type'] = esc_sql($args['target_type']);
			//$data['target_vote'] = esc_sql($args['target_vote']);
			$data['user_id'] = intval($args['user_id']);
		}
		else{
			$data['target_uid'] = intval($args['target_uid']);
			$data['target_type'] = esc_sql($args['target_type']);
			//$data['target_vote'] = esc_sql($args['target_vote']);
			$data['ip_address'] = esc_sql($args['ip_address']);
		}
		
		foreach($data as $key=>$value){
			$condition[] = "`$key`='$value'";
		}
		
		return intval($wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_vote` WHERE " . implode(' AND ', $condition)));
	}
	
	/**
	 * 투표 정보의 데이터를 확인한다.
	 * @param array $args
	 * @return array
	 */
	private function filter($args){
		if(!isset($args['target_uid']) || !$args['target_uid']){
			return array();
		}
		if(!isset($args['target_type']) || !$args['target_type']){
			return array();
		}
		if(!isset($args['target_vote']) || !$args['target_vote']){
			return array();
		}
		if(!isset($args['user_id']) || !$args['user_id']){
			if(is_user_logged_in()){
				$args['user_id'] = get_current_user_id();
				$args['ip_address'] = '';
			}
			else if(!isset($args['ip_address']) || !$args['ip_address']){
				$args['user_id'] = 0;
				$args['ip_address'] = kboard_user_ip();
			}
		}
		if(!isset($args['ip_address'])){
			$args['ip_address'] = '';
		}
		return $args;
	}
}
?>
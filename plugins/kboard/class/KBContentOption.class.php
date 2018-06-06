<?php
/**
 * KBoard 게시글 옵션
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBContentOption {
	
	private $content_uid;
	private $row;
	
	public function __construct($content_uid=''){
		$this->row = new stdClass();
		if($content_uid) $this->initWithContentUID($content_uid);
	}
	
	public function __get($key){
		$key = sanitize_key($key);
		if(isset($this->row->{$key})){
			return $this->row->{$key};
		}
		return '';
	}
	
	public function __set($key, $value){
		global $wpdb;
		if($this->content_uid){
			$key = sanitize_key($key);
			$value = esc_sql($value);
			if($value){
				$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_option` WHERE `content_uid`='$this->content_uid' AND `option_key`='$key'");
				if(is_array($value)){
					if($count){
						$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_option` WHERE `content_uid`='$this->content_uid' AND `option_key`='$key'");
					}
					foreach($value as $option){
						$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_board_option` (`content_uid`, `option_key`, `option_value`) VALUES ('$this->content_uid', '$key', '$option')");
					}
				}
				else{
					if($count){
						$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_option` SET `option_value`='$value' WHERE `content_uid`='$this->content_uid' AND `option_key`='$key'");
					}
					else{
						$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_board_option` (`content_uid`, `option_key`, `option_value`) VALUES ('$this->content_uid', '$key', '$value')");
					}
				}
			}
			else{
				$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_option` WHERE `content_uid`='$this->content_uid' AND `option_key`='$key'");
			}
			$this->row->{$key} = $value;
		}
	}
	
	public function initWithContentUID($content_uid){
		global $wpdb;
		$this->row = new stdClass();
		$this->content_uid = intval($content_uid);
		$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_option` WHERE `content_uid`='$this->content_uid' ORDER BY `uid` ASC");
		$wpdb->flush();
		
		$option_list = array();
		foreach($results as $row){
			if(!isset($option_list[$row->option_key])) $option_list[$row->option_key] = array();
			$option_list[$row->option_key][] = $row->option_value;
		}
		
		foreach($option_list as $option_key=>$option_value){
			if(count($option_value) > 1){
				$this->row->{$option_key} = $option_value;
			}
			else{
				$this->row->{$option_key} = $option_value[0];
			}
		}
	}
	
	public function toArray(){
		if($this->content_uid){
			return get_object_vars($this->row);
		}
		return array();
	}
}
?>
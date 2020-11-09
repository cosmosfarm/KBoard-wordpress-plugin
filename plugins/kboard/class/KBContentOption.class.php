<?php
/**
 * KBoard 게시글 옵션
 * @link www.cosmosfarm.com
 * @copyright Copyright 2020 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBContentOption {
	
	var $content_uid;
	var $row;
	
	public function __construct($content_uid=''){
		$this->row = array();
		if($content_uid){
			$this->initWithContentUID($content_uid);
		}
	}
	
	public function __get($key){
		$value = '';
		$key = sanitize_key($key);
		if(isset($this->row[$key])){
			$value = $this->row[$key];
		}
		return apply_filters('kboard_content_option_value', $value, $key, $this);
	}
	
	public function __set($key, $value){
		global $wpdb, $cosmosfarm_migration_in_progress;
		if($this->content_uid){
			
			$key = sanitize_key($key);
			$this->row[$key] = $value;
			$value = esc_sql($value);
			
			if($value){
				$count = 0;
				if(!$cosmosfarm_migration_in_progress){
					$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_option` WHERE `content_uid`='$this->content_uid' AND `option_key`='$key'");
				}
				
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
			else if(!$cosmosfarm_migration_in_progress){
				$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_option` WHERE `content_uid`='$this->content_uid' AND `option_key`='$key'");
			}
		}
	}
	
	public function initWithContentUID($content_uid){
		global $wpdb;
		$this->row = array();
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
				$this->row[$option_key] = $option_value;
			}
			else{
				$this->row[$option_key] = $option_value[0];
			}
		}
	}
	
	public function toArray(){
		if($this->content_uid){
			return $this->row;
		}
		return array();
	}
}
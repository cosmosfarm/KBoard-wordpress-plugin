<?php
/**
 * KBoard 댓글 옵션
 * @link www.cosmosfarm.com
 * @copyright Copyright 2020 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentOption {

	private $comment_uid;
	private $row;

	public function __construct($comment_uid=''){
		$this->row = new stdClass();
		if($comment_uid) $this->initWithCommentUID($comment_uid);
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
		if($this->comment_uid){
			$key = sanitize_key($key);
			$value = esc_sql($value);
			if($value){
				$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_comments_option` (`comment_uid`, `option_key`, `option_value`) VALUES ('$this->comment_uid', '$key', '$value') ON DUPLICATE KEY UPDATE `option_value`='$value'");
			}
			else{
				$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_comments_option` WHERE `comment_uid`='$this->comment_uid' AND `option_key`='$key'");
			}
			$this->row->{$key} = $value;
		}
	}

	public function initWithCommentUID($comment_uid){
		global $wpdb;
		$this->row = new stdClass();
		$this->comment_uid = intval($comment_uid);
		$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_comments_option` WHERE `comment_uid`='$this->comment_uid'");
		foreach($results as $row){
			$this->row->{$row->option_key} = $row->option_value;
		}
	}
}
?>
<?php
/**
 * KBoard 댓글 미디어
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentMedia extends KBContentMedia {
	
	var $comment_uid;
	
	/**
	 * 미디어 리스트를 반환한다.
	 */
	public function getList(){
		global $wpdb;
		
		$media_list = array();
		
		$this->board_id = intval($this->board_id);
		$this->comment_uid = intval($this->comment_uid);
		$this->media_group = esc_sql($this->media_group);
		
		if($this->comment_uid && $this->media_group){
			$media_list = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_meida` LEFT JOIN `{$wpdb->prefix}kboard_meida_relationships` ON `{$wpdb->prefix}kboard_meida`.`uid`=`{$wpdb->prefix}kboard_meida_relationships`.`media_uid` WHERE `{$wpdb->prefix}kboard_meida_relationships`.`comment_uid`='{$this->comment_uid}' OR `{$wpdb->prefix}kboard_meida`.`media_group`='{$this->media_group}' ORDER BY `{$wpdb->prefix}kboard_meida`.`uid` DESC");
		}
		else if($this->comment_uid){
			$media_list = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_meida` LEFT JOIN `{$wpdb->prefix}kboard_meida_relationships` ON `{$wpdb->prefix}kboard_meida`.`uid`=`{$wpdb->prefix}kboard_meida_relationships`.`media_uid` WHERE `{$wpdb->prefix}kboard_meida_relationships`.`comment_uid`='{$this->comment_uid}' ORDER BY `{$wpdb->prefix}kboard_meida`.`uid` DESC");
		}
		else if($this->media_group){
			$media_list = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_meida` LEFT JOIN `{$wpdb->prefix}kboard_meida_relationships` ON `{$wpdb->prefix}kboard_meida`.`uid`=`{$wpdb->prefix}kboard_meida_relationships`.`media_uid` WHERE `{$wpdb->prefix}kboard_meida`.`media_group`='{$this->media_group}' ORDER BY `{$wpdb->prefix}kboard_meida`.`uid` DESC");
		}
		
		foreach($media_list as $key=>$media){
			$media->file_url = site_url($media->file_path, 'relative');
			$media->thumbnail_url = site_url($media->file_path);
			$media->metadata = ($media->metadata ? unserialize($media->metadata) : array());
			$media_list[$key] = $media;
		}
		
		$media_list = apply_filters('kboard_comments_media_list', $media_list, $this);
		
		return $media_list;
	}
	
	/**
	 * 댓글과 미디어의 관계를 입력한다.
	 */
	public function createRelationships(){
		global $wpdb;
		
		$this->board_id = intval($this->board_id);
		$this->comment_uid = intval($this->comment_uid);
		$this->media_group = esc_sql($this->media_group);
		
		if($this->comment_uid && $this->media_group){
			$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_meida` WHERE `media_group`='{$this->media_group}'");
			foreach($results as $row){
				$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_meida_relationships` (`content_uid`, `comment_uid`, `media_uid`) VALUES ('0', '{$this->comment_uid}', '{$row->uid}')");
			}
		}
	}
	
	/**
	 * 미디어를 삭제한다.
	 * @param int $comment_uid
	 */
	public function deleteWithCommentUID($comment_uid){
		global $wpdb;
		$comment_uid = intval($comment_uid);
		if($comment_uid){
			$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_meida` LEFT JOIN `{$wpdb->prefix}kboard_meida_relationships` ON `{$wpdb->prefix}kboard_meida`.`uid`=`{$wpdb->prefix}kboard_meida_relationships`.`media_uid` WHERE `{$wpdb->prefix}kboard_meida_relationships`.`comment_uid`='{$comment_uid}'");
			foreach($results as $key=>$row){
				$this->deleteWithMedia($row);
			}
		}
	}
}
?>
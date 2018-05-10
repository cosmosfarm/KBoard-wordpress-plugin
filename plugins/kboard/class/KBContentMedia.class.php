<?php
/**
 * KBoard 게시글 미디어
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBContentMedia {
	
	var $board_id;
	var $content_uid;
	var $media_group;
	
	/**
	 * 미디어 리스트를 반환한다.
	 */
	public function getList(){
		global $wpdb;
		
		$this->content_uid = intval($this->content_uid);
		$this->media_group = esc_sql($this->media_group);
		
		if($this->content_uid && $this->media_group){
			$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_meida` LEFT JOIN `{$wpdb->prefix}kboard_meida_relationships` ON `{$wpdb->prefix}kboard_meida`.`uid`=`{$wpdb->prefix}kboard_meida_relationships`.`media_uid` WHERE `{$wpdb->prefix}kboard_meida_relationships`.`content_uid`='{$this->content_uid}' OR `{$wpdb->prefix}kboard_meida`.`media_group`='{$this->media_group}' ORDER BY `{$wpdb->prefix}kboard_meida`.`uid` DESC");
		}
		else if($this->content_uid){
			$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_meida` LEFT JOIN `{$wpdb->prefix}kboard_meida_relationships` ON `{$wpdb->prefix}kboard_meida`.`uid`=`{$wpdb->prefix}kboard_meida_relationships`.`media_uid` WHERE `{$wpdb->prefix}kboard_meida_relationships`.`content_uid`='{$this->content_uid}' ORDER BY `{$wpdb->prefix}kboard_meida`.`uid` DESC");
		}
		else if($this->media_group){
			$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_meida` LEFT JOIN `{$wpdb->prefix}kboard_meida_relationships` ON `{$wpdb->prefix}kboard_meida`.`uid`=`{$wpdb->prefix}kboard_meida_relationships`.`media_uid` WHERE `{$wpdb->prefix}kboard_meida`.`media_group`='{$this->media_group}' ORDER BY `{$wpdb->prefix}kboard_meida`.`uid` DESC");
		}
		else{
			$results = array();
		}
		
		return $results;
	}
	
	/**
	 * 미디어 파일을 업로드한다.
	 */
	public function upload(){
		global $wpdb;
		
		$this->board_id = intval($this->board_id);
		$this->media_group = esc_sql($this->media_group);
		
		if($this->board_id && $this->media_group){
			$upload_dir = wp_upload_dir();
			$attach_store_path = str_replace(KBOARD_WORDPRESS_ROOT, '', $upload_dir['basedir']) . "/kboard_attached/{$this->board_id}/" . date('Ym', current_time('timestamp')) . '/';
			
			$file = new KBFileHandler();
			$file->setPath($attach_store_path);
			
			$upload_results = $file->upload('kboard_media_file');
			
			if(!is_array($upload_results)){
				$upload_results = array($upload_results);
			}
			
			foreach($upload_results as $upload){
				$file_name = esc_sql($upload['original_name']);
				$file_path = esc_sql($upload['path'] . $upload['stored_name']);
					
				if($file_name){
					$date = date('YmdHis', current_time('timestamp'));
					$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_meida` (`media_group`, `date`, `file_path`, `file_name`) VALUES ('{$this->media_group}', '$date', '$file_path', '$file_name')");
				}	
			}
		}
	}
	
	/**
	 * 게시글과 미디어의 관계를 입력한다.
	 */
	public function createRelationships(){
		global $wpdb;
		
		$this->content_uid = intval($this->content_uid);
		$this->media_group = esc_sql($this->media_group);
		
		if($this->content_uid && $this->media_group){
			$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_meida` WHERE `media_group`='{$this->media_group}'");
			foreach($results as $row){
				$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_meida_relationships` (`content_uid`, `media_uid`) VALUES ('{$this->content_uid}', '{$row->uid}')");
			}
		}
	}
	
	/**
	 * 미디어를 삭제한다.
	 * @param int $media_uid
	 */
	public function deleteWithMediaUID($media_uid){
		global $wpdb;
		$media_uid = intval($media_uid);
		if($media_uid){
			$row = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_meida` LEFT JOIN `{$wpdb->prefix}kboard_meida_relationships` ON `{$wpdb->prefix}kboard_meida`.`uid`=`{$wpdb->prefix}kboard_meida_relationships`.`media_uid` WHERE `{$wpdb->prefix}kboard_meida`.`uid`='{$media_uid}'");
			$this->deleteWithMedia($row);
		}
	}
	
	/**
	 * 미디어를 삭제한다.
	 * @param int $content_uid
	 */
	public function deleteWithContentUID($content_uid){
		global $wpdb;
		$content_uid = intval($content_uid);
		if($content_uid){
			$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_meida` LEFT JOIN `{$wpdb->prefix}kboard_meida_relationships` ON `{$wpdb->prefix}kboard_meida`.`uid`=`{$wpdb->prefix}kboard_meida_relationships`.`media_uid` WHERE `{$wpdb->prefix}kboard_meida_relationships`.`content_uid`='{$content_uid}'");
			foreach($results as $key=>$row){
				$this->deleteWithMedia($row);
			}
		}
	}
	
	/**
	 * 미디어를 삭제한다.
	 * @param object $media
	 */
	public function deleteWithMedia($media){
		global $wpdb;
		if($media->uid){
			kbaord_delete_resize(KBOARD_WORDPRESS_ROOT . stripslashes($media->file_path));
			@unlink(KBOARD_WORDPRESS_ROOT . stripslashes($media->file_path));
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_meida` WHERE `uid`='$media->uid'");
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_meida_relationships` WHERE `media_uid`='$media->uid'");
		}
	}
	
	/**
	 * 게시글과의 관계가 없는 미디어는 삭제한다.
	 */
	public function truncate(){
		global $wpdb;
		$date = date('YmdHis', current_time('timestamp')-3600);
		$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_meida` LEFT JOIN `{$wpdb->prefix}kboard_meida_relationships` ON `{$wpdb->prefix}kboard_meida`.`uid`=`{$wpdb->prefix}kboard_meida_relationships`.`media_uid` WHERE `{$wpdb->prefix}kboard_meida`.`date`<'{$date}' AND `{$wpdb->prefix}kboard_meida_relationships`.`content_uid` IS NULL");
		foreach($results as $row){
			$this->deleteWithMedia($row);
		}
	}
}
?>
<?php
/**
 * KBoard 게시글 미디어
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBContentMedia {
	
	private $abspath;
	
	var $board_id;
	var $content_uid;
	var $media_group;
	
	public function __construct(){
		$this->abspath = untrailingslashit(ABSPATH);
	}
	
	/**
	 * 미디어 리스트를 반환한다.
	 */
	public function getList(){
		global $wpdb;
		
		$media_list = array();
		
		$this->board_id = intval($this->board_id);
		$this->content_uid = intval($this->content_uid);
		$this->media_group = esc_sql($this->media_group);
		
		if($this->content_uid && $this->media_group){
			$media_list = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_meida` LEFT JOIN `{$wpdb->prefix}kboard_meida_relationships` ON `{$wpdb->prefix}kboard_meida`.`uid`=`{$wpdb->prefix}kboard_meida_relationships`.`media_uid` WHERE `{$wpdb->prefix}kboard_meida_relationships`.`content_uid`='{$this->content_uid}' OR `{$wpdb->prefix}kboard_meida`.`media_group`='{$this->media_group}' ORDER BY `{$wpdb->prefix}kboard_meida`.`uid` DESC");
		}
		else if($this->content_uid){
			$media_list = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_meida` LEFT JOIN `{$wpdb->prefix}kboard_meida_relationships` ON `{$wpdb->prefix}kboard_meida`.`uid`=`{$wpdb->prefix}kboard_meida_relationships`.`media_uid` WHERE `{$wpdb->prefix}kboard_meida_relationships`.`content_uid`='{$this->content_uid}' ORDER BY `{$wpdb->prefix}kboard_meida`.`uid` DESC");
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
		
		$media_list = apply_filters('kboard_content_media_list', $media_list, $this);
		
		return $media_list;
	}
	
	/**
	 * 미디어 파일을 업로드한다.
	 */
	public function upload(){
		global $wpdb;
		
		$this->board_id = intval($this->board_id);
		$this->content_uid = intval($this->content_uid);
		$this->media_group = esc_sql($this->media_group);
		
		if($this->board_id && $this->media_group){
			$upload_dir = wp_upload_dir();
			$attach_store_path = str_replace($this->abspath, '', $upload_dir['basedir']) . "/kboard_attached/{$this->board_id}/" . date('Ym', current_time('timestamp')) . '/';
			
			$file = new KBFileHandler();
			$file->setPath($attach_store_path);
			
			$upload_results = $file->upload('kboard_media_file');
			
			if(!is_array($upload_results)){
				$upload_results = array($upload_results);
			}
			
			foreach($upload_results as $upload){
				$file_name = esc_sql($upload['original_name']);
				$file_path = esc_sql($upload['path'] . $upload['stored_name']);
				$file_size = intval(filesize($this->abspath . $upload['path'] . $upload['stored_name']));
				
				$attach_file = new stdClass();
				$attach_file->key = '';
				$attach_file->path = $file_path;
				$attach_file->name = $file_name;
				$attach_file->metadata = $upload['metadata'];
				
				$metadata = apply_filters('kboard_content_media_metadata', $upload['metadata'], $attach_file, $this);
				$metadata = serialize($metadata);
				$metadata = esc_sql($metadata);
				
				if($file_name){
					$date = date('YmdHis', current_time('timestamp'));
					$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_meida` (`media_group`, `date`, `file_path`, `file_name`, `file_size`, `download_count`, `metadata`) VALUES ('{$this->media_group}', '$date', '$file_path', '$file_name', '$file_size', '0', '$metadata')");
				}
			}
		}
	}
	
	/**
	 * 게시글과 미디어의 관계를 입력한다.
	 */
	public function createRelationships(){
		global $wpdb;
		
		$this->board_id = intval($this->board_id);
		$this->content_uid = intval($this->content_uid);
		$this->media_group = esc_sql($this->media_group);
		
		if($this->content_uid && $this->media_group){
			$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_meida` WHERE `media_group`='{$this->media_group}'");
			foreach($results as $row){
				$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_meida_relationships` (`content_uid`, `comment_uid`, `media_uid`) VALUES ('{$this->content_uid}', '0', '{$row->uid}')");
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
			kbaord_delete_resize($this->abspath . stripslashes($media->file_path));
			@unlink($this->abspath . stripslashes($media->file_path));
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
		$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_meida` LEFT JOIN `{$wpdb->prefix}kboard_meida_relationships` ON `{$wpdb->prefix}kboard_meida`.`uid`=`{$wpdb->prefix}kboard_meida_relationships`.`media_uid` WHERE `{$wpdb->prefix}kboard_meida`.`date`<'{$date}' AND (`{$wpdb->prefix}kboard_meida_relationships`.`content_uid` IS NULL AND `{$wpdb->prefix}kboard_meida_relationships`.`comment_uid` IS NULL)");
		foreach($results as $row){
			$this->deleteWithMedia($row);
		}
	}
}
?>
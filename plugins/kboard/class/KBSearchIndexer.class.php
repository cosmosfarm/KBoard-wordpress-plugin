<?php
/**
 * KBoard Search Indexer
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBSearchIndexer {
	
	/**
	 * 게시글 인덱스를 동기화한다.
	 * @param int $content_uid
	 * @return boolean
	 */
	public static function sync($content_uid, $board_id=0, $content=null, $board=null){
		global $wpdb;
		
		if(!kboard_use_search_index()){
			return false;
		}
		
		$content_uid = intval($content_uid);
		if(!$content_uid){
			return false;
		}
		
		$row = $wpdb->get_row("SELECT `uid`, `board_id`, `title`, `content`, `member_display` FROM `{$wpdb->prefix}kboard_board_content` WHERE `uid`='$content_uid' LIMIT 1");
		if(!$row){
			self::delete($content_uid);
			return false;
		}
		
		// 원본 LIKE 검색의 후보를 빠짐없이 만들 수 있도록 원문을 인덱싱한다.
		// 실제 일치 여부는 KBSearchEngine에서 원본 컬럼 LIKE 조건으로 다시 검증한다.
		$title_plain = (string) $row->title;
		$content_plain = (string) $row->content;
		$option_plain = '';
		$member_display = (string) $row->member_display;
		
		$table = "{$wpdb->prefix}kboard_search_document";
		$sql = $wpdb->prepare(
			"INSERT INTO `{$table}` (`content_uid`, `board_id`, `member_display`, `title_plain`, `content_plain`, `option_plain`, `index_version`)
			VALUES (%d, %d, %s, %s, %s, %s, %d)
			ON DUPLICATE KEY UPDATE
				`board_id` = VALUES(`board_id`),
				`member_display` = VALUES(`member_display`),
				`title_plain` = VALUES(`title_plain`),
				`content_plain` = VALUES(`content_plain`),
				`option_plain` = VALUES(`option_plain`),
				`index_version` = VALUES(`index_version`)",
			$content_uid,
			intval($row->board_id),
			$member_display,
			$title_plain,
			$content_plain,
			$option_plain,
			KBSearchEngine::INDEX_VERSION
		);
		$wpdb->query($sql);
		
		return true;
	}
	
	/**
	 * 게시글 인덱스를 삭제한다.
	 * @param int $content_uid
	 * @return boolean
	 */
	public static function delete($content_uid, $board_id=0, $content=null, $board=null){
		global $wpdb;
		$content_uid = intval($content_uid);
		if(!$content_uid){
			return false;
		}
		$wpdb->delete("{$wpdb->prefix}kboard_search_document", array('content_uid'=>$content_uid), array('%d'));
		return true;
	}
	
	/**
	 * 배치 재인덱싱을 실행한다.
	 * @param int $limit
	 * @param int $last_uid
	 * @param int $board_id
	 * @return array
	 */
	public static function reindexBatch($limit=500, $last_uid=0, $board_id=0){
		global $wpdb;
		
		if(!kboard_use_search_index()){
			return array(
				'processed' => 0,
				'last_uid' => intval($last_uid),
				'next_last_uid' => intval($last_uid),
				'has_more' => false,
				'total' => 0,
				'remaining' => 0,
				'disabled' => true,
			);
		}
		
		$limit = max(1, min(2000, intval($limit)));
		$last_uid = max(0, intval($last_uid));
		$board_id = max(0, intval($board_id));

		$index_versions = get_option('kboard_search_index_versions', array());
		$index_versions = is_array($index_versions) ? $index_versions : array();
		$version_key = $board_id ? 'board:' . $board_id : 'all';
		if($last_uid === 0){
			unset($index_versions[$version_key]);
			update_option('kboard_search_index_versions', $index_versions, false);
		}
		
		$where = "WHERE `uid` > '$last_uid'";
		if($board_id){
			$where .= " AND `board_id` = '$board_id'";
		}
		
		$rows = $wpdb->get_results("SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` {$where} ORDER BY `uid` ASC LIMIT {$limit}");
		$processed = 0;
		$next_last_uid = $last_uid;
		foreach($rows as $row){
			$uid = intval($row->uid);
			if($uid){
				self::sync($uid);
				$processed++;
				$next_last_uid = $uid;
			}
		}
		
		$remaining = intval($wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE `uid` > '$next_last_uid'" . ($board_id ? " AND `board_id` = '$board_id'" : '')));
		$total = intval($wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content`" . ($board_id ? " WHERE `board_id` = '$board_id'" : '')));
		if($remaining === 0){
			$index_versions[$version_key] = KBSearchEngine::INDEX_VERSION;
			update_option('kboard_search_index_versions', $index_versions, false);
		}
		
		return array(
			'processed' => $processed,
			'last_uid' => $last_uid,
			'next_last_uid' => $next_last_uid,
			'has_more' => ($remaining > 0),
			'total' => $total,
			'remaining' => $remaining,
			'board_id' => $board_id,
			'limit' => $limit,
		);
	}
}

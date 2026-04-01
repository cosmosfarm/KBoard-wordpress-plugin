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
		
		$row = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE `uid`='$content_uid' LIMIT 1");
		if(!$row){
			self::delete($content_uid);
			return false;
		}
		
		$option_rows = $wpdb->get_results("SELECT `option_value` FROM `{$wpdb->prefix}kboard_board_option` WHERE `content_uid`='$content_uid' ORDER BY `uid` ASC");
		$option_text = '';
		foreach($option_rows as $option_row){
			if(isset($option_row->option_value) && $option_row->option_value){
				$option_text .= ' ' . $option_row->option_value;
			}
		}
		
		$document_data = array(
			'content_uid' => $content_uid,
			'board_id' => intval($row->board_id),
			'member_uid' => intval($row->member_uid),
			'member_display' => sanitize_text_field((string) $row->member_display),
			'title' => sanitize_text_field((string) $row->title),
			'content_plain' => kboard_search_normalize_text((string) $row->content),
			'status' => sanitize_key((string) $row->status),
			'secret' => sanitize_key((string) $row->secret),
			'notice' => sanitize_key((string) $row->notice),
			'date' => sanitize_text_field((string) $row->date),
			'category1' => sanitize_text_field((string) $row->category1),
			'category2' => sanitize_text_field((string) $row->category2),
			'category3' => sanitize_text_field((string) $row->category3),
			'category4' => sanitize_text_field((string) $row->category4),
			'category5' => sanitize_text_field((string) $row->category5),
			'indexed_at' => date('YmdHis', current_time('timestamp')),
		);
		
		$wpdb->replace(
			"{$wpdb->prefix}kboard_search_document",
			$document_data,
			array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
		);
		
		$wpdb->delete("{$wpdb->prefix}kboard_search_token", array('content_uid'=>$content_uid), array('%d'));
		
		$field_weights = array(
			'title' => 10,
			'member_display' => 6,
			'option' => 4,
			'content' => 2,
		);
		
		$field_texts = array(
			'title' => $document_data['title'],
			'member_display' => $document_data['member_display'],
			'option' => $option_text,
			'content' => $document_data['content_plain'],
		);
		
		$values = array();
		foreach($field_texts as $field=>$text){
			$frequencies = kboard_search_token_frequencies(kboard_search_tokenize($text));
			foreach($frequencies as $token=>$count){
				if(function_exists('mb_substr')){
					$token = mb_substr($token, 0, 32, 'UTF-8');
				}
				else{
					$token = substr($token, 0, 32);
				}
				if(!$token){
					continue;
				}
				$values[] = $wpdb->prepare(
					"(%s, %d, %s, %d, %d)",
					$token,
					$content_uid,
					$field,
					max(1, intval($count)),
					intval($field_weights[$field])
				);
			}
		}
		
		if($values){
			$chunks = array_chunk($values, 300);
			foreach($chunks as $chunk){
				$sql = "INSERT INTO `{$wpdb->prefix}kboard_search_token` (`token`, `content_uid`, `field`, `position_count`, `weight`) VALUES " . implode(', ', $chunk);
				$wpdb->query($sql);
			}
		}
		
		return true;
	}
	
	/**
	 * 게시글 인덱스를 삭제한다.
	 * @param int $content_uid
	 */
	public static function delete($content_uid, $board_id=0, $content=null, $board=null){
		global $wpdb;
		$content_uid = intval($content_uid);
		if(!$content_uid){
			return false;
		}
		$wpdb->delete("{$wpdb->prefix}kboard_search_document", array('content_uid'=>$content_uid), array('%d'));
		$wpdb->delete("{$wpdb->prefix}kboard_search_token", array('content_uid'=>$content_uid), array('%d'));
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

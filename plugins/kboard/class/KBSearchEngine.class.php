<?php
/**
 * KBoard Search Engine
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBSearchEngine {
	
	/**
	 * 인덱스 기반 검색을 실행한다.
	 * @param array $args
	 * @return array
	 */
	public static function search($args){
		global $wpdb;
		
		$defaults = array(
			'keyword' => '',
			'board_id' => 0,
			'status' => '',
			'secret' => '',
			'notice' => '',
			'category1' => '',
			'category2' => '',
			'category3' => '',
			'category4' => '',
			'category5' => '',
			'start_date' => '',
			'end_date' => '',
			'limit' => 20,
			'offset' => 0,
			'max_candidates' => 3000,
		);
		$args = wp_parse_args((array) $args, $defaults);
		
		if(!kboard_use_search_index()){
			return array('total'=>0, 'uids'=>array(), 'fallback'=>true);
		}
		
		$keyword = trim((string) $args['keyword']);
		if(!$keyword){
			return array('total'=>0, 'uids'=>array(), 'fallback'=>true);
		}
		
		$groups = kboard_search_parse_keyword_groups($keyword);
		if(!$groups){
			return array('total'=>0, 'uids'=>array(), 'fallback'=>true);
		}
		
		$conditions = array();
		if(intval($args['board_id'])){
			$conditions[] = "`d`.`board_id`='" . intval($args['board_id']) . "'";
		}
		if($args['status'] !== ''){
			$conditions[] = "`d`.`status`='" . esc_sql(sanitize_key($args['status'])) . "'";
		}
		if($args['secret'] !== ''){
			$conditions[] = "`d`.`secret`='" . esc_sql(sanitize_key($args['secret'])) . "'";
		}
		if($args['notice'] !== ''){
			$conditions[] = "`d`.`notice`='" . esc_sql(sanitize_key($args['notice'])) . "'";
		}
		foreach(array('category1', 'category2', 'category3', 'category4', 'category5') as $category_key){
			if($args[$category_key] !== ''){
				$conditions[] = "`d`.`{$category_key}`='" . esc_sql(sanitize_text_field($args[$category_key])) . "'";
			}
		}
		if($args['start_date']){
			$conditions[] = "`d`.`date` >= '" . esc_sql(sanitize_text_field($args['start_date'])) . "'";
		}
		if($args['end_date']){
			$conditions[] = "`d`.`date` <= '" . esc_sql(sanitize_text_field($args['end_date'])) . "'";
		}
		$condition_sql = $conditions ? (' AND ' . implode(' AND ', $conditions)) : '';
		
		$candidate_scores = array();
		$max_candidates = max(100, min(10000, intval($args['max_candidates'])));
		
		foreach($groups as $group_tokens){
			if(!$group_tokens){
				continue;
			}
			
			$in_tokens = array();
			foreach($group_tokens as $token){
				$token = esc_sql($token);
				$in_tokens[] = "'{$token}'";
			}
			if(!$in_tokens){
				continue;
			}
			
			$required = count($group_tokens);
			$sql = "
				SELECT `t`.`content_uid`, SUM(`t`.`weight` * `t`.`position_count`) AS `score`, MAX(`d`.`date`) AS `sort_date`
				FROM `{$wpdb->prefix}kboard_search_token` AS `t`
				INNER JOIN `{$wpdb->prefix}kboard_search_document` AS `d` ON `d`.`content_uid`=`t`.`content_uid`
				WHERE `t`.`token` IN (" . implode(', ', $in_tokens) . ") {$condition_sql}
				GROUP BY `t`.`content_uid`
				HAVING COUNT(DISTINCT `t`.`token`) >= {$required}
				ORDER BY `score` DESC, `sort_date` DESC
				LIMIT {$max_candidates}
			";
			$rows = $wpdb->get_results($sql);
			
			foreach($rows as $row){
				$uid = intval($row->content_uid);
				if(!$uid){
					continue;
				}
				$score = floatval($row->score);
				$sort_date = isset($row->sort_date) ? $row->sort_date : '';
				if(!isset($candidate_scores[$uid])){
					$candidate_scores[$uid] = array('score'=>$score, 'date'=>$sort_date);
				}
				else{
					$candidate_scores[$uid]['score'] += $score;
					if($sort_date > $candidate_scores[$uid]['date']){
						$candidate_scores[$uid]['date'] = $sort_date;
					}
				}
			}
		}
		
		if(!$candidate_scores){
			return array('total'=>0, 'uids'=>array(), 'fallback'=>false);
		}
		
		uasort($candidate_scores, function($a, $b){
			if($a['score'] == $b['score']){
				if($a['date'] == $b['date']) return 0;
				return ($a['date'] > $b['date']) ? -1 : 1;
			}
			return ($a['score'] > $b['score']) ? -1 : 1;
		});
		
		$uids = array_map('intval', array_keys($candidate_scores));
		$total = count($uids);
		$offset = max(0, intval($args['offset']));
		$limit = max(1, intval($args['limit']));
		$uids = array_slice($uids, $offset, $limit);
		
		return array('total'=>$total, 'uids'=>$uids, 'fallback'=>false);
	}
}

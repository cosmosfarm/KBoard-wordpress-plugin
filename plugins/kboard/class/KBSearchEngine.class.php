<?php
/**
 * KBoard Search Engine
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBSearchEngine {
	
	/**
	 * FULLTEXT 기반 검색을 실행한다.
	 * @param array $args
	 * @return array
	 */
	public static function search($args){
		global $wpdb;
		
		$defaults = array(
			'keyword' => '',
			'search' => '',
			'board_id' => 0,
			'status' => '',
			'secret' => '',
			'notice' => '',
			'with_notice' => true,
			'category1' => '',
			'category2' => '',
			'category3' => '',
			'category4' => '',
			'category5' => '',
			'start_date' => '',
			'end_date' => '',
			'within_days' => 0,
			'member_uid' => 0,
			'author_id' => 0,
			'page' => 1,
			'rpp' => 20,
			'sort' => '',
			'order' => 'DESC',
		);
		$args = wp_parse_args((array) $args, $defaults);
		
		if(!kboard_use_search_index()){
			return array('total'=>0, 'uids'=>array(), 'fallback'=>true);
		}
		
		if(!kboard_search_document_table_exists()){
			return array('total'=>0, 'uids'=>array(), 'fallback'=>true);
		}
		
		$keyword = trim((string) $args['keyword']);
		if(!$keyword){
			return array('total'=>0, 'uids'=>array(), 'fallback'=>true);
		}
		
		$escaped_keyword = kboard_search_escape_boolean($keyword);
		if(!$escaped_keyword){
			return array('total'=>0, 'uids'=>array(), 'fallback'=>true);
		}
		
		$search_keyword = esc_sql($escaped_keyword);
		
		// 검색 대상에 따라 MATCH 컬럼 결정
		$search_target = sanitize_key((string) $args['search']);
		$match_clause = self::buildMatchClause($search_target, $search_keyword);
		if(!$match_clause){
			return array('total'=>0, 'uids'=>array(), 'fallback'=>true);
		}
		
		// FROM: search_document JOIN board_content (필터용)
		$doc_table = "`{$wpdb->prefix}kboard_search_document` AS `d`";
		$content_table = "`{$wpdb->prefix}kboard_board_content` AS `c`";
		$from = "{$doc_table} INNER JOIN {$content_table} ON `c`.`uid` = `d`.`content_uid`";
		
		// WHERE 조건
		$conditions = array();
		$conditions[] = $match_clause['where'];
		
		// board_id는 search_document에 있으므로 d 테이블 사용
		if(is_array($args['board_id'])){
			$board_ids = kboard_array2int($args['board_id']);
			if($board_ids){
				$conditions[] = "`d`.`board_id` IN (" . implode(',', $board_ids) . ")";
			}
		}
		else if(intval($args['board_id'])){
			$conditions[] = "`d`.`board_id`='" . intval($args['board_id']) . "'";
		}
		
		// 나머지 필터는 원본 테이블(c)에서 처리
		if($args['status'] !== ''){
			$conditions[] = "`c`.`status`='" . esc_sql(sanitize_key($args['status'])) . "'";
		}
		else{
			$conditions[] = "`c`.`status`!='trash'";
		}
		if($args['secret'] !== ''){
			$conditions[] = "`c`.`secret`='" . esc_sql(sanitize_key($args['secret'])) . "'";
		}
		if($args['notice'] !== ''){
			$conditions[] = "`c`.`notice`='" . esc_sql(sanitize_key($args['notice'])) . "'";
		}
		if(!$args['with_notice']){
			$conditions[] = "`c`.`notice`=''";
		}
		foreach(array('category1', 'category2', 'category3', 'category4', 'category5') as $cat_key){
			if($args[$cat_key] !== ''){
				$conditions[] = "`c`.`{$cat_key}`='" . esc_sql(sanitize_text_field($args[$cat_key])) . "'";
			}
		}
		if($args['start_date']){
			$conditions[] = "`c`.`date` >= '" . esc_sql(sanitize_text_field($args['start_date'])) . "'";
		}
		if($args['end_date']){
			$conditions[] = "`c`.`date` <= '" . esc_sql(sanitize_text_field($args['end_date'])) . "'";
		}
		if(intval($args['within_days'])){
			$days = date('Ymd', strtotime("-" . intval($args['within_days']) . " day", current_time('timestamp')));
			$conditions[] = "`c`.`date`>='{$days}000000'";
		}
		if(intval($args['member_uid'])){
			$conditions[] = "`c`.`member_uid`='" . intval($args['member_uid']) . "'";
		}
		if(intval($args['author_id'])){
			$conditions[] = "`c`.`member_uid`='" . intval($args['author_id']) . "'";
		}
		
		$where_sql = implode(' AND ', $conditions);
		
		// total
		$total = intval($wpdb->get_var("SELECT COUNT(*) FROM {$from} WHERE {$where_sql}"));
		
		if(!$total){
			return array('total'=>0, 'uids'=>array(), 'fallback'=>false);
		}
		
		// 페이지네이션
		$page = max(1, intval($args['page']));
		$rpp = max(1, intval($args['rpp']));
		$offset = ($page - 1) * $rpp;
		
		// 정렬 결정: 사용자 정렬이 날짜/조회/추천순이면 score 계산 생략
		$sort_col = trim((string) $args['sort']);
		$use_score_sort = true;
		
		// sort가 원본 테이블의 컬럼(date, view, vote, update)을 포함하면 해당 컬럼으로 정렬
		if($sort_col && preg_match('/\b(`?date`?|`?view`?|`?vote`?|`?update`?)\b/', $sort_col)){
			$use_score_sort = false;
		}
		
		if($use_score_sort){
			// 연관도순 정렬
			$sql = "SELECT `d`.`content_uid`, {$match_clause['score']} AS `score`
				FROM {$from}
				WHERE {$where_sql}
				ORDER BY `score` DESC, `d`.`content_uid` DESC
				LIMIT {$offset}, {$rpp}";
		}
		else{
			// 사용자 지정 정렬 (score 계산 생략 → CPU 절감)
			$order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
			$sql = "SELECT `d`.`content_uid`
				FROM {$from}
				WHERE {$where_sql}
				ORDER BY {$sort_col} {$order}, `d`.`content_uid` DESC
				LIMIT {$offset}, {$rpp}";
		}
		
		$rows = $wpdb->get_results($sql);
		$uids = array();
		foreach($rows as $row){
			$uid = intval($row->content_uid);
			if($uid){
				$uids[] = $uid;
			}
		}
		
		return array('total'=>$total, 'uids'=>$uids, 'fallback'=>false);
	}
	
	/**
	 * 검색 대상에 따라 MATCH 절을 구성한다.
	 * @param string $search_target
	 * @param string $search_keyword
	 * @return array|false
	 */
	private static function buildMatchClause($search_target, $search_keyword){
		$use_ngram = kboard_search_supports_ngram();
		$mode = $use_ngram ? 'IN BOOLEAN MODE' : 'IN NATURAL LANGUAGE MODE';
		
		if($search_target === 'member_display'){
			// 작성자 검색: ft_member 인덱스 사용
			$match_cols = "`d`.`member_display`";
		}
		else{
			// 제목, 본문, 전체 검색: ft_search 인덱스 사용 (3컬럼 통합)
			$match_cols = "`d`.`title_plain`, `d`.`content_plain`, `d`.`option_plain`";
		}
		
		$match_expr = "MATCH({$match_cols}) AGAINST ('{$search_keyword}' {$mode})";
		
		return array(
			'where' => $match_expr,
			'score' => $match_expr,
		);
	}
}

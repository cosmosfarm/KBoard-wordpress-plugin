<?php
/**
 * KBoard Search Engine
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBSearchEngine {

	const INDEX_VERSION = 2;

	/**
	 * 기존 직접 호출 코드와의 호환을 위한 검색 API다.
	 * KBContentList는 전체 필터 호환성을 위해 buildClause()를 직접 사용한다.
	 *
	 * @param array $args
	 * @return array
	 */
	public static function search($args){
		global $wpdb;

		$defaults = array(
			'keyword'=>'', 'search'=>'', 'board_id'=>0, 'status'=>'', 'secret'=>'', 'notice'=>'', 'with_notice'=>true,
			'category1'=>'', 'category2'=>'', 'category3'=>'', 'category4'=>'', 'category5'=>'',
			'start_date'=>'', 'end_date'=>'', 'within_days'=>0, 'member_uid'=>0, 'author_id'=>0,
			'page'=>1, 'rpp'=>20, 'sort'=>'date', 'order'=>'DESC',
		);
		$args = wp_parse_args((array) $args, $defaults);
		$clause = self::buildClause(array(
			'keyword'=>$args['keyword'],
			'search'=>$args['search'],
			'board_id'=>$args['board_id'],
			'auto_operator_or'=>get_option('kboard_search_auto_operator_or'),
			'include_member_display'=>get_option('kboard_search_include_member_display') == '1',
		));
		if(!$clause){
			return array('total'=>0, 'uids'=>array(), 'fallback'=>true);
		}

		$content_table = "`{$wpdb->prefix}kboard_board_content`";
		$conditions = array($clause['where']);
		if(is_array($args['board_id'])){
			$board_ids = kboard_array2int($args['board_id']);
			if($board_ids) $conditions[] = "{$content_table}.`board_id` IN (" . implode(',', $board_ids) . ")";
		}
		else if(intval($args['board_id'])){
			$conditions[] = "{$content_table}.`board_id`='" . intval($args['board_id']) . "'";
		}
		if($args['status'] !== ''){
			$conditions[] = $wpdb->prepare("{$content_table}.`status`=%s", sanitize_key($args['status']));
		}
		else{
			$conditions[] = "{$content_table}.`status`!='trash'";
		}
		if($args['secret'] !== '') $conditions[] = $wpdb->prepare("{$content_table}.`secret`=%s", sanitize_key($args['secret']));
		if($args['notice'] !== '') $conditions[] = $wpdb->prepare("{$content_table}.`notice`=%s", sanitize_key($args['notice']));
		if(!$args['with_notice']) $conditions[] = "{$content_table}.`notice`=''";
		foreach(array('category1', 'category2', 'category3', 'category4', 'category5') as $category_key){
			if($args[$category_key] !== ''){
				$conditions[] = $wpdb->prepare("{$content_table}.`{$category_key}`=%s", sanitize_text_field($args[$category_key]));
			}
		}
		if($args['start_date']) $conditions[] = $wpdb->prepare("{$content_table}.`date`>=%s", sanitize_text_field($args['start_date']));
		if($args['end_date']) $conditions[] = $wpdb->prepare("{$content_table}.`date`<=%s", sanitize_text_field($args['end_date']));
		if(intval($args['within_days'])){
			$days = date('Ymd', strtotime('-' . intval($args['within_days']) . ' day', current_time('timestamp')));
			$conditions[] = $wpdb->prepare("{$content_table}.`date`>=%s", $days . '000000');
		}
		$member_ids = array_filter(array_unique(array(intval($args['member_uid']), intval($args['author_id']))));
		if($member_ids){
			$conditions[] = "{$content_table}.`member_uid` IN (" . implode(',', $member_ids) . ")";
		}

		$from = "{$content_table} {$clause['join']}";
		$where = implode(' AND ', $conditions);
		$total = intval($wpdb->get_var("SELECT COUNT(*) FROM {$from} WHERE {$where}"));
		$page = max(1, intval($args['page']));
		$rpp = max(1, intval($args['rpp']));
		$offset = ($page - 1) * $rpp;
		$sort_columns = array('date'=>'date', 'view'=>'view', 'vote'=>'vote', 'update'=>'update');
		$sort_column = 'date';
		foreach($sort_columns as $sort_key=>$column){
			if(preg_match('/\b' . preg_quote($sort_key, '/') . '\b/', (string) $args['sort'])){
				$sort_column = $column;
				break;
			}
		}
		$order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
		$rows = $wpdb->get_results("SELECT {$content_table}.`uid` AS `content_uid` FROM {$from} WHERE {$where} ORDER BY {$content_table}.`{$sort_column}` {$order}, {$content_table}.`uid` DESC LIMIT {$offset}, {$rpp}");
		$uids = array();
		foreach((array) $rows as $row){
			if(intval($row->content_uid)) $uids[] = intval($row->content_uid);
		}
		return array('total'=>$total, 'uids'=>$uids, 'fallback'=>false);
	}

	/**
	 * 기존 LIKE 검색과 동일한 결과를 유지하는 인덱스 검색 조건을 생성한다.
	 * FULLTEXT는 후보를 줄이고 원본 컬럼 LIKE 조건이 최종 결과를 검증한다.
	 *
	 * @param array $args
	 * @return array|false
	 */
	public static function buildClause($args){
		global $wpdb;

		$defaults = array(
			'keyword' => '',
			'search' => '',
			'board_id' => 0,
			'auto_operator_or' => false,
			'include_member_display' => false,
		);
		$args = wp_parse_args((array) $args, $defaults);

		if(!kboard_use_search_index() || !kboard_search_document_table_exists()){
			return false;
		}
		if(!kboard_search_supports_ngram() || !kboard_search_has_required_indexes()){
			return false;
		}
		if(!self::isIndexComplete($args['board_id'])){
			return false;
		}

		$keyword = (string) $args['keyword'];
		$search_target = sanitize_key((string) $args['search']);
		if(trim($keyword) === '' || !in_array($search_target, array('', 'title', 'content', 'member_display'), true)){
			return false;
		}

		$parsed = self::parseKeyword($keyword, !empty($args['auto_operator_or']));
		if(!$parsed){
			return false;
		}

		$content_table = "`{$wpdb->prefix}kboard_board_content`";
		$term_clauses = array();
		$operators = $parsed['operators'];

		foreach($parsed['terms'] as $term){
			if(!self::isNgramSearchable($term)){
				return false;
			}

			$boolean_keyword = kboard_search_escape_boolean($term);
			if($boolean_keyword === ''){
				return false;
			}

			$match_keyword = $wpdb->prepare('%s', $boolean_keyword);
			// 기존 검색은 사용자 입력의 %와 _를 SQL LIKE 와일드카드로 허용한다.
			$like_keyword = '%' . $term . '%';
			$candidate = array();
			$exact = array();

			if($search_target === 'title'){
				$candidate[] = "MATCH(`kboard_search_doc`.`title_plain`) AGAINST ({$match_keyword} IN BOOLEAN MODE)";
				$exact[] = $wpdb->prepare("{$content_table}.`title` LIKE %s", $like_keyword);
			}
			else if($search_target === 'content'){
				$candidate[] = "MATCH(`kboard_search_doc`.`content_plain`) AGAINST ({$match_keyword} IN BOOLEAN MODE)";
				$exact[] = $wpdb->prepare("{$content_table}.`content` LIKE %s", $like_keyword);
			}
			else if($search_target === 'member_display'){
				$candidate[] = "MATCH(`kboard_search_doc`.`member_display`) AGAINST ({$match_keyword} IN BOOLEAN MODE)";
				$exact[] = $wpdb->prepare("{$content_table}.`member_display` LIKE %s", $like_keyword);
			}
			else{
				$candidate[] = "MATCH(`kboard_search_doc`.`title_plain`, `kboard_search_doc`.`content_plain`) AGAINST ({$match_keyword} IN BOOLEAN MODE)";
				$exact[] = $wpdb->prepare("{$content_table}.`title` LIKE %s", $like_keyword);
				$exact[] = $wpdb->prepare("{$content_table}.`content` LIKE %s", $like_keyword);
				if(!empty($args['include_member_display'])){
					$candidate[] = "MATCH(`kboard_search_doc`.`member_display`) AGAINST ({$match_keyword} IN BOOLEAN MODE)";
					$exact[] = $wpdb->prepare("{$content_table}.`member_display` LIKE %s", $like_keyword);
				}
			}

			$term_clauses[] = '(((' . implode(' OR ', $candidate) . ')) AND ((' . implode(' OR ', $exact) . ')))';
		}

		$where_parts = array($term_clauses[0]);
		foreach($operators as $index=>$operator){
			$where_parts[] = $operator;
			$where_parts[] = $term_clauses[$index + 1];
		}

		return array(
			'join' => "INNER JOIN `{$wpdb->prefix}kboard_search_document` AS `kboard_search_doc` ON {$content_table}.`uid`=`kboard_search_doc`.`content_uid`",
			'where' => '(' . implode(' ', $where_parts) . ')',
		);
	}

	/**
	 * 기존 검색의 & 및 | 처리 규칙을 파싱한다.
	 * @param string $keyword
	 * @param boolean $auto_operator_or
	 * @return array|false
	 */
	private static function parseKeyword($keyword, $auto_operator_or=false){
		if($auto_operator_or){
			$keyword = str_replace(' ', '|', $keyword);
		}
		$parts = preg_split('/(&|\|)/', $keyword, -1, PREG_SPLIT_DELIM_CAPTURE);
		$terms = array();
		$operators = array();
		$expect_term = true;
		foreach($parts as $part){
			if($expect_term){
				if($part === '' || $part === '&' || $part === '|'){
					return false;
				}
				$terms[] = $part;
			}
			else{
				if($part !== '&' && $part !== '|'){
					return false;
				}
				$operators[] = $part === '&' ? 'AND' : 'OR';
			}
			$expect_term = !$expect_term;
		}
		if($expect_term || !$terms || count($terms) !== count($operators) + 1){
			return false;
		}
		return array('terms'=>$terms, 'operators'=>$operators);
	}

	/**
	 * 검색어에 ngram 인덱스가 후보로 사용할 수 있는 토큰이 있는지 확인한다.
	 * 짧거나 특수문자뿐인 검색은 결과 누락 방지를 위해 기존 LIKE로 처리한다.
	 * @param string $keyword
	 * @return boolean
	 */
	private static function isNgramSearchable($keyword){
		$token_size = kboard_search_ngram_token_size();
		if(!preg_match_all('/[\p{L}\p{N}]+/u', (string) $keyword, $matches)){
			return false;
		}
		foreach($matches[0] as $token){
			$length = function_exists('mb_strlen') ? mb_strlen($token, 'UTF-8') : strlen($token);
			if($length >= $token_size){
				return true;
			}
		}
		return false;
	}

	/**
	 * 검색 대상 게시글이 현재 인덱스 버전으로 모두 인덱싱되어 있는지 확인한다.
	 * @param int|array $board_id
	 * @return boolean
	 */
	private static function isIndexComplete($board_id=0){
		$versions = get_option('kboard_search_index_versions', array());
		$versions = is_array($versions) ? $versions : array();
		if(isset($versions['all']) && intval($versions['all']) === self::INDEX_VERSION){
			return true;
		}
		if(is_array($board_id)){
			$board_ids = kboard_array2int($board_id);
			if(!$board_ids) return false;
			foreach($board_ids as $current_board_id){
				$key = 'board:' . intval($current_board_id);
				if(!isset($versions[$key]) || intval($versions[$key]) !== self::INDEX_VERSION){
					return false;
				}
			}
			return true;
		}
		if(intval($board_id)){
			$key = 'board:' . intval($board_id);
			return isset($versions[$key]) && intval($versions[$key]) === self::INDEX_VERSION;
		}
		return false;
	}
}

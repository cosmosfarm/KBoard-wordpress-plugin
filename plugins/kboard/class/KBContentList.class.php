<?php
/**
 * KBoard 게시글 리스트
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBContentList {
	
	private $kboard_list_sort;
	private $from;
	private $where;
	private $multiple_option_keys;
	private $next_list_page = 1;
	
	var $board;
	var $board_id;
	var $total;
	var $index;
	var $category1;
	var $category2;
	var $category3;
	var $category4;
	var $category5;
	var $member_uid = 0;
	var $compare;
	var $start_date;
	var $end_date;
	var $search_option = array();
	var $sort = 'date';
	var $order = 'DESC';
	var $rpp = 10;
	var $page = 1;
	var $status;
	var $stop;
	var $resource;
	var $resource_notice;
	var $resource_popular;
	var $resource_reply;
	var $row;
	var $is_loop_start;
	var $is_first;
	var $is_rss = false;
	var $is_latest = false;
	var $dayofweek;
	var $within_days = 0;
	var $random = false;
	var $sort_random = false;
	var $latest = array();
	
	public function __construct($board_id=''){
		if($board_id) $this->setBoardID($board_id);
	}
	
	/**
	 * 모든 게시판의 내용을 반환한다.
	 * @return KBContentList
	 */
	public function init(){
		global $wpdb;
		$this->total = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE 1");
		$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE 1 ORDER BY `date` DESC LIMIT ".($this->page-1)*$this->rpp.",$this->rpp");
		$wpdb->flush();
		$this->index = $this->total;
		return $this;
	}
	
	/**
	 * 모든 게시판의 내용을 반환한다.
	 * @return KBContentList
	 */
	public function initWithKeyword($keyword='', $search=''){
		global $wpdb;
		
		$start_date = kboard_start_date();
		$end_date = kboard_end_date();
		
		if($start_date){
			$start_date = date('YmdHis', strtotime($start_date . ' 00:00:00'));
			$where[] = "`date` >= '{$start_date}'";
		}
		if($end_date){
			$end_date = date('YmdHis', strtotime($end_date . ' 23:59:59'));
			$where[] = "`date` <= '{$end_date}'";
		}
		
		if($keyword){
			$keyword = esc_sql($keyword);
			if($search){
				$search = esc_sql($search);
				$where[] = "(`{$search}` LIKE '%{$keyword}%')";
			}
			else{
				$where[] = "(`title` LIKE '%{$keyword}%' OR `content` LIKE '%{$keyword}%')";
			}
		}
		if($this->board_id) $where[] = "`board_id`='{$this->board_id}'";
		if($this->status){
			if($this->status == 'published'){
				$where[] = "`status`=''";
			}
			else{
				$where[] = "`status`='{$this->status}'";
			}
		}
		if(!isset($where) || !$where) $where[] = "`status`!='trash'";
		$where = implode(' AND ', $where);
		
		$offset = ($this->page-1)*$this->rpp;
		
		$results = $wpdb->get_results("SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` WHERE $where ORDER BY `date` DESC LIMIT {$offset},{$this->rpp}");
		foreach($results as $row){
			$select_uid[] = intval($row->uid);
		}
		
		if(!isset($select_uid)){
			$this->total = 0;
			$this->resource = array();
		}
		else{
			$this->total = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE $where");
			$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE `uid` IN(".implode(',', $select_uid).") ORDER BY `date` DESC");
		}
		
		$wpdb->flush();
		$this->index = $this->total - $offset;
		return $this;
	}
	
	/**
	 * RSS 피드 출력을 위한 리스트를 반환한다.
	 * @param int $board_id
	 * @return KBContentList
	 */
	public function initWithRSS($board_id=''){
		global $wpdb;
		if($board_id){
			$board_id = intval($board_id);
			$where[] = "`board_id`='{$board_id}'";
		}
		else{
			$read = array();
			$result = $wpdb->get_results("SELECT `uid` FROM `{$wpdb->prefix}kboard_board_setting` WHERE `permission_read`='all'");
			foreach($result as $row){
				$read[] = $row->uid;
			}
			if($read){
				$where[] = '`board_id` IN(' . implode(',', $read) . ')';
			}
		}
		$where[] = "`secret`=''";
		$where[] = "`status`=''";
		$this->total = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE " . implode(' AND ', $where));
		$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE " . implode(' AND ', $where) . " ORDER BY `date` DESC LIMIT " . ($this->page-1)*$this->rpp . ",{$this->rpp}");
		$wpdb->flush();
		$this->index = $this->total;
		$this->is_rss = true;
		return $this;
	}
	
	/**
	 * 게시판 아이디를 입력한다.
	 * @param int|array $board_id
	 * @return KBContentList
	 */
	public function setBoardID($board_id){
		if(is_array($board_id)){
			$this->board = new KBoard(reset($board_id));
		}
		else if($board_id){
			$this->board = new KBoard($board_id);
		}
		$this->board_id = $board_id;
		return $this;
	}
	
	/**
	 * 페이지 번호를 입력한다.
	 * @param int $page
	 * @return KBContentList
	 */
	public function page($page){
		$page = intval($page);
		if($page <= 0){
			$this->page = 1;
		}
		else{
			$this->page = $page;
		}
		return $this;
	}
	
	/**
	 * 한 페이지에 표시될 게시글 개수를 입력한다.
	 * @param int $rpp
	 * @return KBContentList
	 */
	public function rpp($rpp){
		$rpp = intval($rpp);
		if($rpp <= 0){
			$this->rpp = 10;
		}
		else{
			$this->rpp = apply_filters('kboard_list_rpp', $rpp, $this->board_id, $this);
		}
		
		return $this;
	}
	
	/**
	 * 카테고리1을 입력한다.
	 * @param string $category
	 * @return KBContentList
	 */
	public function category1($category){
		if($category) $this->category1 = $category;
		return $this;
	}
	
	/**
	 * 카테고리2를 입력한다.
	 * @param string $category
	 * @return KBContentList
	 */
	public function category2($category){
		if($category) $this->category2 = $category;
		return $this;
	}
	
	/**
	 * 카테고리3를 입력한다.
	 * @param string $category
	 * @return KBContentList
	 */
	public function category3($category){
		if($category) $this->category3 = $category;
		return $this;
	}
	
	/**
	 * 카테고리4를 입력한다.
	 * @param string $category
	 * @return KBContentList
	 */
	public function category4($category){
		if($category) $this->category4 = $category;
		return $this;
	}
	
	/**
	 * 카테고리5를 입력한다.
	 * @param string $category
	 * @return KBContentList
	 */
	public function category5($category){
		if($category) $this->category5 = $category;
		return $this;
	}
	
	/**
	 * 글 작성자 고유 ID값을 입력한다.
	 * @param int $member_uid
	 * @return KBContentList
	 */
	public function memberUID($member_uid){
		if($member_uid) $this->member_uid = intval($member_uid);
		return $this;
	}
	
	/**
	 * 검색 연산자를 입력한다.
	 * @param string $compare
	 */
	public function setCompare($compare){
		$this->compare = $compare;
	}
	
	/**
	 * 작성일 기간을 입력한다.
	 * @param string $start_date
	 * @param string $end_date
	 */
	public function setDateRange($start_date, $end_date){
		if($start_date){
			$this->start_date = date('Ymd', strtotime($start_date)) . '000000';
		}
		if($end_date){
			$this->end_date = date('Ymd', strtotime($end_date)) . '235959';
		}
	}
	
	/**
	 * 최근 특정 요일의 게시글만 가져오도록 설정한다.
	 * @return string $dayofweek
	 */
	public function setDayOfWeek($dayofweek){
		if($dayofweek && in_array($dayofweek, array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'))){
			$timestamp = strtotime(sprintf('last %s', $dayofweek), strtotime('tomorrow', current_time('timestamp')));
			$ymd = date('Y-m-d', $timestamp);
			$this->setDateRange($ymd, $ymd);
			$this->dayofweek = $dayofweek;
		}
	}
	
	/**
	 * 표시할 게시글 기간을 설정한다.
	 * @return int $within_days
	 */
	public function setWithinDays($within_days){
		$this->within_days = intval($within_days);
	}
	
	/**
	 * 결과를 랜점하게 정렬할지 설정한다.
	 * @param boolean $random
	 */
	public function setRandom($random){
		$this->random = $random ? true : false;
	}
	
	/**
	 * 전체를 랜점하게 정렬할지 설정한다.
	 * @param boolean $random
	 */
	public function setSortRandom($sort_random){
		$this->sort_random = $sort_random ? true : false;
	}
	
	/**
	 * 검색 옵션의 데이터를 반환한다.
	 * @param array $associative
	 * @param array $search_option
	 * @return string
	 */
	public function getSearchOptionValue($associative, $search_option=array()){
		if(!$search_option) $search_option = $this->search_option;
		$key = array_shift($associative);
		if(isset($search_option[$key])){
			if(is_array($search_option[$key])){
				return $this->getSearchOptionValue($associative, $search_option[$key]);
			}
			else{
				return $search_option[$key];
			}
		}
		return '';
	}
	
	/**
	 * 검색 옵션을 입력한다.
	 * @param array $search_option
	 */
	public function setSearchOption($search_option){
		$this->search_option = $search_option;
	}
	
	/**
	 * 검색 조건을 추가한다.
	 * @param string $key
	 * @param string $value
	 * @param string $compare
	 */
	public function addSearchOption($key, $value, $compare='='){
		$this->search_option[] = array('key'=>$key, 'compare'=>$compare, 'value'=>$value);
	}
	
	/**
	 * 게시판의 리스트를 반환한다.
	 * @param string $keyword
	 * @param string $search
	 * @param boolean $with_notice
	 * @return resource
	 */
	public function getList($keyword='', $search='title', $with_notice=false){
		global $wpdb;
		
		if($this->stop){
			$this->total = 0;
			$this->resource = array();
			$this->index = $this->total;
			return $this->resource;
		}
		
		$this->from = array();
		$this->where = array();
		
		// 정렬 초기화
		if($this->sort == 'date'){
			$this->sort = "`{$wpdb->prefix}kboard_board_content`.`date`";
			$this->order = 'DESC';
		}
		$board = new KBoard($this->board_id);
		$range_type = $board->meta->list_sorting_range_select;
		
		if ($range_type === '7' || $range_type === '30' || $range_type === '365') {
			$this->start_date = date('Ymd', strtotime("-{$range_type} days", current_time('timestamp')));
			$this->end_date = ''; // 오늘까지
		}
		else if ($range_type === 'custom') {
			$this->start_date = preg_replace('/[^0-9]/', '', $board->meta->list_sorting_start_date);
			$this->end_date = preg_replace('/[^0-9]/', '', $board->meta->list_sorting_end_date);
		}
		else {
			$this->start_date = '';
			$this->end_date = '';
		}
		
		if ($this->start_date) {
			$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`date` >= '{$this->start_date}'";
		}
		if ($this->end_date) {
			$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`date` <= '{$this->end_date}'";
		}
		
		if($this->getSorting() == 'newest'){
			// 최신순서
			$this->sort = "`{$wpdb->prefix}kboard_board_content`.`date`";
			$this->order = 'DESC';
		}
		else if($this->getSorting() == 'best'){
			// 추천순서
			$this->sort = "`{$wpdb->prefix}kboard_board_content`.`vote`";
			$this->order = 'DESC';
		}
		else if($this->getSorting() == 'viewed'){
			// 조회순서
			$this->sort = "`{$wpdb->prefix}kboard_board_content`.`view`";
			$this->order = 'DESC';
		}
		else if($this->getSorting() == 'updated'){
			// 업데이트순서
			$this->sort = "`{$wpdb->prefix}kboard_board_content`.`update`";
			$this->order = 'DESC';
		}
		
		if(is_array($this->board_id)){
			$board_id = kboard_array2int($this->board_id);
			$board_id = implode(',', $board_id);
			$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`board_id` IN ($board_id)";
		}
		else{
			$allowed_board_id = $this->board_id;
			$allowed_board_id = apply_filters('kboard_allowed_board_id', $allowed_board_id, $this->board);
			if(is_array($allowed_board_id)){
				$board_id = kboard_array2int($allowed_board_id);
				$board_id = implode(',', $board_id);
				$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`board_id` IN ($board_id)";
			}
			else{
				$allowed_board_id = (int)$allowed_board_id;
				$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`board_id`='$allowed_board_id'";
			}
		}
		
		// 작성자 글만 보기 설정이 활성화된 경우 author_id 필터 추가
		if($board->meta->show_author_activity_menu && isset($_GET['author_id']) && $_GET['author_id']){
			$author_id = esc_sql($_GET['author_id']);
			$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`member_uid`='{$author_id}'";
		}
	
		if(!in_array($this->compare, array('=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE'))){
			$this->compare = 'LIKE';
		}
		
		$this->from[] = "`{$wpdb->prefix}kboard_board_content`";
		
		if(strpos($search, KBContent::$SKIN_OPTION_PREFIX) !== false && $keyword){
			// 입력 필드 검색후 게시글을 불러온다. (검색 target이 kboard_option_{meta_key} 인 경우 실행한다.)
			$this->from[] = "LEFT JOIN `{$wpdb->prefix}kboard_board_option` ON `{$wpdb->prefix}kboard_board_content`.`uid`=`{$wpdb->prefix}kboard_board_option`.`content_uid`";
			
			$search = esc_sql(str_replace(KBContent::$SKIN_OPTION_PREFIX, '', $search));
			$keyword = esc_sql($keyword);
			
			$keyword_list = preg_split("/(&|\|)/", $keyword, -1, PREG_SPLIT_DELIM_CAPTURE);
			if(is_array($keyword_list) && count($keyword_list) > 0){
				foreach($keyword_list as $keyword){
					if($keyword == '&'){
						$sub_where[] = ' AND ';
					}
					else if($keyword == '|'){
						$sub_where[] = ' OR ';
					}
					else{
						if(in_array($this->compare, array('LIKE', 'NOT LIKE'))){
							$keyword = "%{$keyword}%";
						}
						
						$sub_where[] = "(`{$wpdb->prefix}kboard_board_option`.`option_key`='{$search}' AND `{$wpdb->prefix}kboard_board_option`.`option_value` {$this->compare} '{$keyword}')";
					}
				}
				
				if(count($sub_where) > 1){
					$this->where[] = '(' . implode('', $sub_where) . ')';
				}
				else{
					$this->where[] = implode('', $sub_where);
				}
			}
		}
		else if(strpos($search, 'wp_') !== false && $keyword){
			$wp_search_list = array();
			
			if($search == 'wp_user_login'){
				$user = get_user_by('login', kboard_keyword());
				if($user){
					$wp_search_list[] = $user->ID;
				}
			}
			else if($search == 'wp_first_name'){
				$wp_user_query = new WP_User_Query(array(
					'meta_query' => array(
						array(
							'key' => 'first_name',
							'value' => kboard_keyword(),
							'compare' => 'LIKE'
						),
					)
				));
				
				$users = $wp_user_query->get_results();
				if($users){
					foreach($users as $user){
						$wp_search_list[] = $user->ID;
					}
				}
			}
			else if($search == 'wp_last_name'){
				$wp_user_query = new WP_User_Query(array(
					'meta_query' => array(
						array(
							'key' => 'last_name',
							'value' => kboard_keyword(),
							'compare' => 'LIKE'
						),
					)
				));
				
				$users = $wp_user_query->get_results();
				if($users){
					foreach($users as $user){
						$wp_search_list[] = $user->ID;
					}
				}
			}
			
			if($wp_search_list){
				$wp_search_list = implode(',', $wp_search_list);
				$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`member_uid` IN ({$wp_search_list})";
			}
			else{
				$this->where[] = "1=0";
			}
		}
		else if($keyword){
			// 일반적인 검색후 게시글을 불러온다.
			$search = esc_sql($search);
			$keyword = esc_sql($keyword);
			
			// 더 많은 게시글 검색 옵션 활성화 시 스페이스를 | 로 변경한다.
			if(get_option('kboard_search_auto_operator_or')){
				$keyword = str_replace(' ', '|', $keyword);
			}
			
			$keyword_list = preg_split("/(&|\|)/", $keyword, -1, PREG_SPLIT_DELIM_CAPTURE);
			
			if(is_array($keyword_list) && count($keyword_list) > 0){
				foreach($keyword_list as $keyword){
					if($keyword == '&'){
						$sub_where[] = ' AND ';
					}
					else if($keyword == '|'){
						$sub_where[] = ' OR ';
					}
					else{
						if(in_array($this->compare, array('LIKE', 'NOT LIKE'))){
							$keyword = "%{$keyword}%";
						}
						
						if($search){
							$sub_where[] = "`{$wpdb->prefix}kboard_board_content`.`{$search}` {$this->compare} '{$keyword}'";
						}
						else{
							if(get_option('kboard_search_include_member_display') == '1'){
								$sub_where[] = "(`{$wpdb->prefix}kboard_board_content`.`title` {$this->compare} '{$keyword}' OR `{$wpdb->prefix}kboard_board_content`.`content` {$this->compare} '{$keyword}' OR `{$wpdb->prefix}kboard_board_content`.`member_display` {$this->compare} '{$keyword}')";
							}
							else{
								$sub_where[] = "(`{$wpdb->prefix}kboard_board_content`.`title` {$this->compare} '{$keyword}' OR `{$wpdb->prefix}kboard_board_content`.`content` {$this->compare} '{$keyword}')";
							}
						}
					}
				}
				
				if(count($sub_where) > 1){
					$this->where[] = '(' . implode('', $sub_where) . ')';
				}
				else{
					$this->where[] = implode('', $sub_where);
				}
			}
		}
		else{
			// 검색이 아니라면 답글이 아닌 일반글만 불러온다.
			$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`parent_uid`='0'";
		}
		
		// 해당 기간에 작성된 게시글만 불러온다.
		$date_range = apply_filters('kboard_list_date_range', array('start_date'=>$this->start_date, 'end_date'=>$this->end_date), $this->board_id, $this);
		if($date_range['start_date'] && $date_range['end_date']){
			$start_date = esc_sql($date_range['start_date']);
			$end_date = esc_sql($date_range['end_date']);
			$this->where[] = "(`{$wpdb->prefix}kboard_board_content`.`date` BETWEEN '{$start_date}' AND '{$end_date}')";
		}
		else if($date_range['start_date']){
			$start_date = esc_sql($date_range['start_date']);
			$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`date`>='{$start_date}'";
		}
		else if($date_range['end_date']){
			$end_date = esc_sql($date_range['end_date']);
			$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`date`<='{$end_date}'";
		}
		
		// 입력 필드 검색 옵션 쿼리를 생성한다.
		$search_option = apply_filters('kboard_list_search_option', $this->search_option, $this->board_id, $this);
		if($search_option){
			$multiple_option_query = $this->multipleOptionQuery($search_option);
			if($multiple_option_query){
				$this->where[] = $multiple_option_query;
				
				foreach($this->multiple_option_keys as $option_name){
					$option_key = array_search($option_name, $this->multiple_option_keys);
					$this->from[] = "INNER JOIN `{$wpdb->prefix}kboard_board_option` AS `option_{$option_key}` ON `{$wpdb->prefix}kboard_board_content`.`uid`=`option_{$option_key}`.`content_uid`";
				}
			}
		}
		
		// 카테고리1 검색
		if($this->category1){
			$category = explode(',', $this->category1); // 여러 카테고리의 경우 콤마로 구분한다.
			
			if(count($category) > 1){
				foreach($category as $index=>$item){
					$category[$index] = sprintf("'%s'", esc_sql($item));
				}
				$category1 = implode(',', $category);
				$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`category1` IN ({$category1})";
			}
			else{
				$category1 = esc_sql($this->category1);
				$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`category1`='{$category1}'";
			}
		}
		
		// 카테고리2 검색
		if($this->category2){
			$category = explode(',', $this->category2); // 여러 카테고리의 경우 콤마로 구분한다.
			
			if(count($category) > 1){
				foreach($category as $index=>$item){
					$category[$index] = sprintf("'%s'", esc_sql($item));
				}
				$category2 = implode(',', $category);
				$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`category2` IN ({$category2})";
			}
			else{
				$category2 = esc_sql($this->category2);
				$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`category2`='{$category2}'";
			}
		}
		
		// 카테고리3 검색
		if($this->category3){
			$category = explode(',', $this->category3); // 여러 카테고리의 경우 콤마로 구분한다.
			
			if(count($category) > 1){
				foreach($category as $index=>$item){
					$category[$index] = sprintf("'%s'", esc_sql($item));
				}
				$category3 = implode(',', $category);
				$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`category3` IN ({$category3})";
			}
			else{
				$category3 = esc_sql($this->category3);
				$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`category3`='{$category3}'";
			}
		}
		
		// 카테고리4 검색
		if($this->category4){
			$category = explode(',', $this->category4); // 여러 카테고리의 경우 콤마로 구분한다.
			
			if(count($category) > 1){
				foreach($category as $index=>$item){
					$category[$index] = sprintf("'%s'", esc_sql($item));
				}
				$category4 = implode(',', $category);
				$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`category4` IN ({$category4})";
			}
			else{
				$category4 = esc_sql($this->category4);
				$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`category4`='{$category4}'";
			}
		}
		
		// 카테고리5 검색
		if($this->category5){
			$category = explode(',', $this->category5); // 여러 카테고리의 경우 콤마로 구분한다.
			
			if(count($category) > 1){
				foreach($category as $index=>$item){
					$category[$index] = sprintf("'%s'", esc_sql($item));
				}
				$category5 = implode(',', $category);
				$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`category5` IN ({$category5})";
			}
			else{
				$category5 = esc_sql($this->category5);
				$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`category5`='{$category5}'";
			}
		}
		
		if($this->member_uid){
			$member_uid = esc_sql($this->member_uid);
			$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`member_uid`='{$member_uid}'";
		}
		
		if($this->within_days){
			$days = date('Ymd', strtotime("-{$this->within_days} day", current_time('timestamp')));
			$this->where[] = "`{$wpdb->prefix}kboard_board_content`.`date`>='{$days}000000'";
		}
		
		// 공지사항이 아닌 게시글만 불러온다.
		if(!$with_notice) $this->where[] = "`{$wpdb->prefix}kboard_board_content`.`notice`=''";
		
		// 휴지통에 없는 게시글만 불러온다.
		$get_list_status_query = kboard_get_list_status_query($this->board_id, "{$wpdb->prefix}kboard_board_content");
		if($get_list_status_query){
			$this->where[] = $get_list_status_query;
		}
		
		// 게시글의 uid 정보만 가져온다.
		$default_select = "`{$wpdb->prefix}kboard_board_content`.`uid`";
		
		// kboard_list_select_count, kboard_list_select, kboard_list_from, kboard_list_where, kboard_list_orderby 워드프레스 필터 실행
		if($this->is_latest){
			$select_count = apply_filters('kboard_latest_select_count', 'COUNT(*)', $this->board_id, $this);
			$select = apply_filters('kboard_latest_select', $default_select, $this->board_id, $this);
			$from = apply_filters('kboard_latest_from', implode(' ', $this->from), $this->board_id, $this);
			$where = apply_filters('kboard_latest_where', implode(' AND ', $this->where), $this->board_id, $this);
			$orderby = apply_filters('kboard_latest_orderby', "{$this->sort} {$this->order}", $this->board_id, $this);
		}
		else{
			$select_count = apply_filters('kboard_list_select_count', 'COUNT(*)', $this->board_id, $this);
			$select = apply_filters('kboard_list_select', $default_select, $this->board_id, $this);
			$from = apply_filters('kboard_list_from', implode(' ', $this->from), $this->board_id, $this);
			$where = apply_filters('kboard_list_where', implode(' AND ', $this->where), $this->board_id, $this);
			$orderby = apply_filters('kboard_list_orderby', "{$this->sort} {$this->order}", $this->board_id, $this);
		}
		
		$offset = ($this->page-1)*$this->rpp;
		
		if($default_select != $select){
			$this->total = $wpdb->get_var("SELECT {$select_count} FROM {$from} WHERE {$where}");
			if($this->sort_random){
				$this->resource = $wpdb->get_results("SELECT {$select} FROM {$from} WHERE {$where} ORDER BY RAND() LIMIT {$this->rpp}");
			}
			else{
				$this->resource = $wpdb->get_results("SELECT {$select} FROM {$from} WHERE {$where} ORDER BY {$orderby} LIMIT {$offset},{$this->rpp}");
			}
		}
		else{
			if($this->sort_random){
				$results = $wpdb->get_results("SELECT {$select} FROM {$from} WHERE {$where} ORDER BY RAND() LIMIT {$this->rpp}");
			}
			else{
				$results = $wpdb->get_results("SELECT {$select} FROM {$from} WHERE {$where} ORDER BY {$orderby} LIMIT {$offset},{$this->rpp}");
			}
			foreach($results as $row){
				if($row->uid){
					$select_uid[] = intval($row->uid);
				}
			}
			
			if(!isset($select_uid)){
				$this->total = 0;
				$this->resource = array();
			}
			else{
				$this->total = $wpdb->get_var("SELECT {$select_count} FROM {$from} WHERE {$where}");
				$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE `{$wpdb->prefix}kboard_board_content`.`uid` IN(".implode(',', $select_uid).") ORDER BY FIELD(`{$wpdb->prefix}kboard_board_content`.`uid`,".implode(',', $select_uid).")");
			}
		}
		
		// 결과를 랜덤하게 정렬한다.
		if($this->random){
			shuffle($this->resource);
		}
		
		$wpdb->flush();
		
		$this->is_loop_start = true;
		
		$this->total = apply_filters('kboard_content_list_total_count', $this->total, $this->board, $this);
		$this->resource = apply_filters('kboard_content_list_items', $this->resource, $this->board, $this);
		
		if($this->board && $this->board->meta->list_sort_numbers == 'asc'){
			$this->index = $offset + 1;
		}
		else{
			$this->index = $this->total - $offset;
		}
		
		$this->sort = 'date';
		$this->order = 'DESC';
		
		return $this->resource;
	}
	
	/**
	 * 검색 옵션 쿼리를 반환한다.
	 * @param array $multiple
	 * @param string $relation
	 * @return string
	 */
	public function multipleOptionQuery($multiple, $relation='AND'){
		if(isset($multiple['relation'])){
			if(in_array($multiple['relation'], array('AND', 'OR'))){
				$relation = $multiple['relation'];
			}
			unset($multiple['relation']);
		}
		
		foreach($multiple as $option){
			if(isset($option['relation'])){
				$where[] = $this->multipleOptionQuery($option);
			}
			else if(is_array($option)){
				if(isset($option['value']) && is_array($option['value'])){
					$option_value = array();
					foreach($option['value'] as $value){
						$option_value[] = esc_sql(sanitize_text_field($value));
					}
					
					$option_value = "'".implode("','", $option_value)."'";
				}
				else{
					$option_value = isset($option['value']) ? esc_sql(sanitize_text_field($option['value'])) : '';
				}
				
				$option_key = isset($option['key']) ? esc_sql(sanitize_key($option['key'])) : '';
				$option_compare = isset($option['compare']) ? esc_sql($option['compare']) : '';
				$option_wildcard = isset($option['wildcard']) ? esc_sql($option['wildcard']) : '';
				
				if($option_key && $option_value){
					$this->multiple_option_keys[$option_key] = $option_key;
					$option_index = array_search($option_key, $this->multiple_option_keys);
					
					if(in_array($option_compare, array('IN', 'NOT IN'))){
						$where[] = "(`option_{$option_index}`.`option_key`='{$option_key}' AND `option_{$option_index}`.`option_value` {$option_compare} ({$option_value}))";
					}
					else{
						if(!in_array($option_compare, array('=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE'))){
							$option_compare = '=';
						}
						
						switch($option_wildcard){
							case 'left': $option_value = "%{$option_value}"; break;
							case 'right': $option_value = "{$option_value}%"; break;
							case 'both': $option_value = "%{$option_value}%"; break;
						}
						
						$where[] = "(`option_{$option_index}`.`option_key`='{$option_key}' AND `option_{$option_index}`.`option_value` {$option_compare} '{$option_value}')";
					}
				}
			}
		}
		
		if(isset($where) && is_array($where)){
			if(count($where) > 1){
				return '(' . implode(" {$relation} ", $where) . ')';
			}
			return implode(" {$relation} ", $where);
		}
		return '';
	}
	
	/**
	 * 모든 게시글 리스트를 반환한다.
	 * @return array
	 */
	public function getAllList(){
		global $wpdb;
		
		if(is_array($this->board_id)){
			foreach($this->board_id as $key=>$value){
				$value = intval($value);
				$board_ids[] = "'{$value}'";
			}
			$board_ids = implode(',', $board_ids);
			$where[] = "`board_id` IN ($board_ids)";
		}
		else{
			$this->board_id = intval($this->board_id);
			$where[] = "`board_id`='$this->board_id'";
		}
		$where = implode(' AND ', $where);
		
		$this->total = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE $where");
		
		$page = 1;
		$limit = 1000;
		$offset = ($page-1)*$limit;
		
		while($results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE $where ORDER BY `{$this->sort}` {$this->order} LIMIT {$offset},{$limit}")){
			$wpdb->flush();
			foreach($results as $row){
				$this->resource[] = $row;
			}
			$page++;
			$offset = ($page-1)*$limit;
		}
		
		$this->is_loop_start = true;
		
		if($this->board && $this->board->meta->list_sort_numbers == 'asc'){
			$this->index = 1;
		}
		else{
			$this->index = $this->total - $offset;
		}
		
		return $this->resource;
	}
	
	/**
	 * 리스트를 초기화한다.
	 */
	public function initFirstList(){
		$this->next_list_page = 1;
	}
	
	/**
	 * 다음 리스트를 반환한다.
	 * @return array
	 */
	public function hasNextList(){
		global $wpdb;
		
		if(is_array($this->board_id)){
			foreach($this->board_id as $key=>$value){
				$value = intval($value);
				$board_ids[] = "'{$value}'";
			}
			$board_ids = implode(',', $board_ids);
			$where[] = "`{$wpdb->prefix}kboard_board_content`.`board_id` IN ($board_ids)";
		}
		else{
			$this->board_id = intval($this->board_id);
			$where[] = "`{$wpdb->prefix}kboard_board_content`.`board_id`='$this->board_id'";
		}
		$where = implode(' AND ', $where);
		
		$offset = ($this->next_list_page-1)*$this->rpp;
		
		$this->total = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE $where");
		$this->resource = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE $where ORDER BY `{$this->sort}` {$this->order} LIMIT {$offset},{$this->rpp}");
		$wpdb->flush();
		
		if($this->resource){
			$this->next_list_page++;
		}
		else{
			$this->next_list_page = 1;
		}
		
		$this->is_loop_start = true;
		
		if($this->board && $this->board->meta->list_sort_numbers == 'asc'){
			$this->index = $offset + 1;
		}
		else{
			$this->index = $this->total - $offset;
		}
		
		return $this->resource;
	}
	
	/**
	 * 리스트에서 다음 게시글을 반환한다.
	 * @return KBContent
	 */
	public function hasNext(){
		if(!$this->resource) return '';
		$this->row = current($this->resource);
		
		if($this->row){
			if(!$this->is_loop_start){
				if($this->board && $this->board->meta->list_sort_numbers == 'asc'){
					$this->index++;
				}
				else{
					$this->index--;
				}
				$this->is_first = false;
			}
			else{
				$this->is_loop_start = false;
				$this->is_first = true;
			}
			
			next($this->resource);
			$content = new KBContent();
			$content->initWithRow($this->row);
			return $content;
		}
		else{
			unset($this->resource);
			return '';
		}
	}
	
	/**
	 * 인기글 리스트를 반환한다.
	 * @param boolean $with_notice
	 * @return resource
	 * @return KBoard
	 */
	public function getPopularList($with_notice=false){
		global $wpdb;
		
		if(!$this->board){
			$this->board = new KBoard($this->board_id);
		}
		
		if(is_array($this->board_id)){
			foreach($this->board_id as $board_id){
				$board_id = intval($board_id);
				$board_ids[] = "'{$board_id}'";
			}
			$board_ids = implode(',', $board_ids);
			$where[] = "`board_id` IN ($board_ids)";
		}
		else{
			$this->board_id = intval($this->board_id);
			$where[] = "`board_id`='$this->board_id'";
		}
		
		if($this->category1){
			$category1 = esc_sql($this->category1);
			$where[] = "`category1`='{$category1}'";
		}
		
		if($this->category2){
			$category2 = esc_sql($this->category2);
			$where[] = "`category2`='{$category2}'";
		}
		
		if($this->category3){
			$category3 = esc_sql($this->category3);
			$where[] = "`category3`='{$category3}'";
		}
		
		if($this->category4){
			$category4 = esc_sql($this->category4);
			$where[] = "`category4`='{$category4}'";
		}
		
		if($this->category5){
			$category5 = esc_sql($this->category5);
			$where[] = "`category5`='{$category5}'";
		}
		
		// 휴지통에 없는 게시글만 불러온다.
		$get_list_status_query = kboard_get_list_status_query($this->board_id);
		if($get_list_status_query){
			$where[] = $get_list_status_query;
		}
		
		if($this->board->meta->popular_type == 'view'){
			if($this->board->meta->popular_range == 'week'){
				$where[] = 'DATE > date_add(now(),interval -1 week)';
			}
			else{
				$where[] = 'DATE > date_add(now(),interval -1 month)';
			}
			$orderby = '`view` DESC';
			$limit = $this->board->meta->popular_count;
		}
		else if($this->board->meta->popular_type == 'vote'){
			$orderby = '`like` DESC';
			$limit = $this->board->meta->popular_count;
		}
		
		if(!$with_notice){
			$where[] = "`{$wpdb->prefix}kboard_board_content`.`notice`=''";
		}
		
		$select = apply_filters('kboard_popular_list_select', '*', $this->board_id, $this);
		$from = apply_filters('kboard_popular_list_from', "`{$wpdb->prefix}kboard_board_content`", $this->board_id, $this);
		$where = apply_filters('kboard_popular_list_where', implode(' AND ', $where), $this->board_id, $this);
		$orderby = apply_filters('kboard_popular_list_orderby', "{$orderby}", $this->board_id, $this);
		
		$this->resource_popular = $wpdb->get_results("SELECT {$select} FROM {$from} WHERE {$where} ORDER BY {$orderby} LIMIT {$limit}");
		$wpdb->flush();
		return $this->resource_popular;
	}
	
	/**
	 * 리스트에서 다음 인기글을 반환한다.
	 * @return KBContent
	 */
	public function hasNextPopular(){
		if(!$this->board){
			$this->board = new KBoard($this->board_id);
		}
		
		if($this->board->meta->popular_action){
			if(!$this->resource_popular) $this->getPopularList();
			$this->row = current($this->resource_popular);
			
			if($this->row){
				next($this->resource_popular);
				$content = new KBContent();
				$content->class_type = 'popular';
				$content->initWithRow($this->row);
				return $content;
			}
		}
		else{
			unset($this->resource_popular);
			return '';
		}
	}
	
	/**
	 * 리스트의 현재 인덱스를 반환한다.
	 * @return int
	 */
	public function index(){
		return $this->index;
	}
	
	/**
	 * 공지사항 리스트를 반환한다.
	 * @return resource
	 */
	public function getNoticeList(){
		global $wpdb;
		
		if(is_array($this->board_id)){
			foreach($this->board_id as $key=>$value){
				$value = intval($value);
				$board_ids[] = "'{$value}'";
			}
			$board_ids = implode(',', $board_ids);
			$where[] = "`board_id` IN ($board_ids)";
		}
		else{
			$this->board_id = intval($this->board_id);
			$where[] = "`board_id`='$this->board_id'";
		}
		
		if($this->category1){
			$category1 = esc_sql($this->category1);
			$where[] = "`category1`='{$category1}'";
		}
		
		if($this->category2){
			$category2 = esc_sql($this->category2);
			$where[] = "`category2`='{$category2}'";
		}
		
		if($this->category3){
			$category3 = esc_sql($this->category3);
			$where[] = "`category3`='{$category3}'";
		}
		
		if($this->category4){
			$category4 = esc_sql($this->category4);
			$where[] = "`category4`='{$category4}'";
		}
		
		if($this->category5){
			$category5 = esc_sql($this->category5);
			$where[] = "`category5`='{$category5}'";
		}
		
		$where[] = "`notice`!=''";
		
		// 휴지통에 없는 게시글만 불러온다.
		$get_list_status_query = kboard_get_list_status_query($this->board_id);
		if($get_list_status_query){
			$where[] = $get_list_status_query;
		}
		
		$select = apply_filters('kboard_notice_list_select', '*', $this->board_id, $this);
		$from = apply_filters('kboard_notice_list_from', "`{$wpdb->prefix}kboard_board_content`", $this->board_id, $this);
		$where = apply_filters('kboard_notice_list_where', implode(' AND ', $where), $this->board_id, $this);
		$orderby = apply_filters('kboard_notice_list_orderby', "`{$this->sort}` {$this->order}", $this->board_id, $this);
		
		$this->resource_notice = $wpdb->get_results("SELECT {$select} FROM {$from} WHERE {$where} ORDER BY {$orderby}");
		$wpdb->flush();
		
		return $this->resource_notice;
	}
	
	/**
	 * 공지사항 리스트에서 다음 게시물을 반환한다.
	 * @deprecated
	 * @see KBContentList::hasNextNotice()
	 * @return KBContent
	 */
	public function hasNoticeNext(){
		return $this->hasNextNotice();
	}
	
	/**
	 * 공지사항 리스트에서 다음 게시물을 반환한다.
	 * @return KBContent
	 */
	public function hasNextNotice(){
		if(!$this->resource_notice) $this->getNoticeList();
		$this->row = current($this->resource_notice);
		
		if($this->row){
			next($this->resource_notice);
			$content = new KBContent();
			$content->class_type = 'notice';
			$content->initWithRow($this->row);
			return $content;
		}
		else{
			unset($this->resource_notice);
			return '';
		}
	}
	
	/**
	 * 답글 리스트를 반환한다.
	 * @return resource
	 */
	public function getReplyList($parent_uid){
		global $wpdb;
		
		$from[] = "`{$wpdb->prefix}kboard_board_content`";
		
		$where[] = "`parent_uid`='$parent_uid'";
		
		// 휴지통에 없는 게시글만 불러온다.
		$get_list_status_query = kboard_get_list_status_query($this->board_id);
		if($get_list_status_query){
			$where[] = $get_list_status_query;
		}
		
		$select = apply_filters('kboard_reply_list_select', '*', $this->board_id, $this);
		$from = apply_filters('kboard_reply_list_from', implode(' ', $from), $this->board_id, $this);
		$where = apply_filters('kboard_reply_list_where', implode(' AND ', $where), $this->board_id, $this);
		$orderby = apply_filters('kboard_reply_list_orderby', "`date` ASC", $this->board_id, $this);
		
		$this->resource_reply = $wpdb->get_results("SELECT {$select} FROM {$from} WHERE {$where} ORDER BY {$orderby}");
		$wpdb->flush();
		
		return $this->resource_reply;
	}
	
	/**
	 * 답글 리스트에서 다음 게시물을 반환한다.
	 * @return KBContent
	 */
	public function hasNextReply(){
		if(!$this->resource_reply) return '';
		$this->row = current($this->resource_reply);
		
		if($this->row){
			next($this->resource_reply);
			$content = new KBContent();
			$content->initWithRow($this->row);
			return $content;
		}
		else{
			unset($this->resource_reply);
			return '';
		}
	}
	
	/**
	 * 정렬 순서를 반환한다.
	 * @return string
	 */
	public function getSorting(){
		if($this->kboard_list_sort){
			return $this->kboard_list_sort;
		}
		
		if(is_array($this->board_id)){
			$this->board_id = current($this->board_id);
		}
		
		$this->kboard_list_sort = isset($_COOKIE["kboard_list_sort_{$this->board_id}"]) ? $_COOKIE["kboard_list_sort_{$this->board_id}"] : $this->getDefaultSorting();
		$this->kboard_list_sort = isset($_SESSION["kboard_list_sort_{$this->board_id}"]) ? $_SESSION["kboard_list_sort_{$this->board_id}"] : $this->kboard_list_sort;
		$this->kboard_list_sort = isset($_GET['kboard_list_sort']) ? $_GET['kboard_list_sort'] : $this->kboard_list_sort;
		
		if(!in_array($this->kboard_list_sort, kboard_list_sorting_types())){
			$this->kboard_list_sort = $this->getDefaultSorting();
		}
		
		$_SESSION["kboard_list_sort_{$this->board_id}"] = $this->kboard_list_sort;
		
		return $this->kboard_list_sort;
	}
	
	/**
	 * 정렬 순서를 설정한다.
	 * @param string $sort
	 */
	public function setSorting($sort){
		if(in_array($sort, kboard_list_sorting_types())){
			$this->kboard_list_sort = $sort;
		}
	}
	
	/**
	 * 기본 정렬 순서를 반환한다.
	 * @return string
	 */
	public function getDefaultSorting(){
		$board = new KBoard($this->board_id);
		$default_sorting = $board->meta->list_default_sorting ? $board->meta->list_default_sorting : 'newest';
		return apply_filters('kboard_list_default_sorting', $default_sorting, $this->board_id, $this);
	}
	
	/**
	 * 정렬 순서를 내림차순(DESC)로 변경한다.
	 * @param string $sort
	 * @return KBContentList
	 */
	public function orderDESC($sort=''){
		if($sort) $this->sort = $sort;
		$this->order = 'DESC';
		return $this;
	}
	
	/**
	 * 정렬 순서를 오름차순(ASC)로 변경한다.
	 * @param string $sort
	 * @return KBContentList
	 */
	public function orderASC($sort=''){
		if($sort) $this->sort = $sort;
		$this->order = 'ASC';
		return $this;
	}
}
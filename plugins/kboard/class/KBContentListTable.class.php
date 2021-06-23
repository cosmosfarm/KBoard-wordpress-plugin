<?php
/**
 * KBoard 게시글 관리자 리스트 테이블
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBContentListTable extends WP_List_Table {
	
	var $board_list;
	var $filter_view;
	var $filter_board_id;
	var $active_admin_board;
	
	public function __construct(){
		parent::__construct();
		$this->filter_view = isset($_REQUEST['filter_view'])?$_REQUEST['filter_view']:'';
		$this->filter_board_id = isset($_REQUEST['filter_board_id'])?intval($_REQUEST['filter_board_id']):'';
	}
	
	public function prepare_items(){
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		$this->board_list = new KBoardList();
		$this->board_list->init();
		$this->active_admin_board = $this->board_list->getActiveAdmin();
		
		$keyword = isset($_GET['s'])?sanitize_text_field($_GET['s']):'';
		$target = kboard_target();
		
		$list = new KBContentList($this->filter_board_id);
		$list->rpp = 20;
		$list->page = $this->get_pagenum();
		$list->status = $this->filter_view;
		$list->initWithKeyword($keyword, $target);
		
		$this->items = $list->resource;
		
		$this->set_pagination_args(array('total_items'=>$list->total, 'per_page'=>$list->rpp));
	}
	
	public function get_table_classes(){
		$classes = parent::get_table_classes();
		$classes[] = 'kboard';
		$classes[] = 'kboard-content-list';
		return $classes;
	}
	
	public function get_views(){
		global $wpdb;
		
		$views = array();
		$class = '';
		
		$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE 1");
		$class = !$this->filter_view ? ' class="current"' : '';
		$views['all'] = '<a href="' . add_query_arg(array('filter_board_id'=>$this->filter_board_id), admin_url('admin.php?page=kboard_content_list')) . '"' . $class . '>' . __('All', 'kboard') . " <span class=\"count\">({$count})</span></a>";
		
		$status_list = kboard_content_status_list();
		foreach($status_list as $status=>$status_name){
			$filter_view = $status ? $status : 'published';
			$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE `status`='{$status}'");
			$class = $this->filter_view == $filter_view ? ' class="current"' : '';
			$views[$filter_view] = '<a href="' . add_query_arg(array('filter_view'=>$filter_view, 'filter_board_id'=>$this->filter_board_id), admin_url('admin.php?page=kboard_content_list')) . '"' . $class . '>' . $status_name . " <span class=\"count\">({$count})</span></a>";
		}
		
		return $views;
	}
	
	public function no_items(){
		echo __('No documents found.', 'kboard');
	}
	
	public function get_columns(){
		$columns = array(
			'cb' => '<input type="checkbox">',
			'board' => __('Forum', 'kboard'),
			'title' => __('Title', 'kboard'),
			'member' => __('Author', 'kboard'),
			'view' => __('Views', 'kboard'),
			'secret' => __('Secret', 'kboard'),
			'notice' => __('Notice', 'kboard'),
			'date' => __('Date', 'kboard'),
			'status' => __('Status', 'kboard'),
		);
		
		return apply_filters('kboard_admin_content_list_table_columns', $columns);
	}
	
	public function get_bulk_actions(){
		if($this->filter_view == 'trash'){
			return array(
				'published' => __('Restore', 'kboard'),
				'delete' => __('Delete Permanently', 'kboard')
			);
		}
		return array(
			'trash' => __('Move to Trash', 'kboard')
		);
	}
	
	public function display_tablenav($which){ ?>
		<div class="tablenav <?php echo esc_attr($which)?>">
			<div class="alignleft actions bulkactions"><?php $this->bulk_actions($which)?></div>
			<?php if($which=='top'):?>
			<div class="alignleft actions">
				<input type="hidden" name="filter_view" value="<?php echo esc_attr($this->filter_view)?>">
				<label class="screen-reader-text" for="filter-by-board-id">게시판으로 필터</label>
				<select id="filter-by-board-id" name="filter_board_id">
					<option value="">전체 게시글</option>
					<?php foreach($this->board_list->resource as $board):?>
					<option value="<?php echo $board->uid?>"<?php if($this->filter_board_id == $board->uid):?> selected<?php endif?>><?php echo $board->board_name?></option>
					<?php endforeach?>
				</select>
				<input type="button" name="filter_action" class="button" value="<?php echo __('Filter', 'kboard')?>" onclick="kboard_content_list_filter(this.form)">
				<span class="spinner"></span>
			</div>
			<?php endif?>
			<?php
			$this->extra_tablenav($which);
			$this->pagination($which);
			?>
			<br class="clear">
		</div>
	<?php }
	
	public function display_rows(){
		foreach($this->items as $key=>$item){
			if(in_array($item->board_id, $this->active_admin_board)){
				$item->url = admin_url("admin.php?page=kboard_admin_view_{$item->board_id}&mod=document&uid={$item->uid}");
			}
			else{
				$url = new KBUrl();
				$item->url = $url->getDocumentRedirect($item->uid);
			}
			$this->single_row($item);
		}
	}
	
	public function single_row($item){
		echo '<tr data-uid="'.$item->uid.'">';
		
		foreach($this->get_columns() as $key=>$value){
			if($key == 'cb'){
				echo '<th scope="row" class="check-column">';
				echo '<input type="checkbox" name="uid[]" value="'.$item->uid.'">';
				echo '</th>';
			}
			else if($key == 'board'){
				echo '<td class="kboard-content-list-board column-primary">';
				if($item->board_id){
					echo '<select name="board_id['.$item->uid.']" onchange="kboard_content_list_update()">';
					foreach($this->board_list->resource as $board){
						echo '<option value="'.$board->uid.'"'.($item->board_id==$board->uid?' selected':'').'>'.$board->board_name.'</option>';
					}
					echo '</select>';
				}
				else{
					echo __('The reply.', 'kboard');
				}
				echo '<button type="button" class="toggle-row"><span class="screen-reader-text">상세보기</span></button>';
				echo '</td>';
			}
			else if($key == 'title'){
				echo '<td class="kboard-content-list-title" data-colname="'.__('Title', 'kboard').'">';
				if($item->comment){
					echo '<h4><a href="'.$item->url.'" onclick="window.open(this.href);return false;">'.mb_strimwidth(strip_tags($item->title), 0, 300, '...', 'UTF-8').' ('.$item->comment.')</a></h4>';
				}
				else{
					echo '<h4><a href="'.$item->url.'" onclick="window.open(this.href);return false;">'.mb_strimwidth(strip_tags($item->title), 0, 300, '...', 'UTF-8').'</a></h4>';
				}
				echo '<p>'.mb_strimwidth(strip_tags($item->content), 0, 300, '...', 'UTF-8').'</p>';
				echo '</td>';
			}
			else if($key == 'member'){
				echo '<td class="kboard-content-list-author" data-colname="'.__('Author', 'kboard').'">';
				if($item->member_uid) echo '<a href="'.admin_url('user-edit.php?user_id='.$item->member_uid).'">';
				echo $item->member_display;
				if($item->member_uid) echo '</a>';
				echo '</td>';
			}
			else if($key == 'view'){
				echo '<td class="kboard-content-list-view" data-colname="'.__('Views', 'kboard').'">';
				echo $item->view;
				echo '</td>';
			}
			else if($key == 'secret'){
				echo '<td class="kboard-content-list-secret" data-colname="'.__('Secret', 'kboard').'">';
				echo $item->secret?__('Yes', 'kboard'):__('No', 'kboard');
				echo '</td>';
			}
			else if($key == 'notice'){
				echo '<td class="kboard-content-list-notice" data-colname="'.__('Notice', 'kboard').'">';
				echo $item->notice?__('Yes', 'kboard'):__('No', 'kboard');
				echo '</td>';
			}
			else if($key == 'date'){
				echo '<td class="kboard-content-list-date">';
				echo '<input type="text" name="date['.$item->uid.']" class="kboard-content-datepicker" size="10" maxlength="10" value="'.date('Y-m-d', strtotime($item->date)).'">';
				echo '<input type="text" name="time['.$item->uid.']" class="kboard-content-timepicker" size="8" maxlength="8" value="'.date('H:i:s', strtotime($item->date)).'">';
				echo '<button type="button" class="button button-small" onclick="kboard_content_list_update()">'.__('Update', 'kboard').'</button>';
				echo '</td>';
			}
			else if($key == 'status'){
				echo '<td class="kboard-content-list-status" data-colname="'.__('Status', 'kboard').'">';
				echo '<select name="status['.$item->uid.']" onchange="kboard_content_list_update()">';
				$status_list = kboard_content_status_list();
				foreach($status_list as $key=>$value){
					$selected = ($item->status==$key) ? ' selected' : '';
					echo '<option value="'.esc_attr($key).'"'.$selected.'>'.esc_html($value).'</option>';
				}
				echo '</td>';
			}
			else{
				$content_uid = $item->uid;
				do_action('kboard_admin_content_list_table_custom_column', $key, $content_uid);
			}
		}
		
		echo '</tr>';
	}
	
	public function search_box($text, $input_id){ ?>
	<p class="search-box">
		<input type="search" id="<?php echo $input_id?>" name="s" value="<?php _admin_search_query()?>">
		<?php submit_button($text, 'button', false, false, array('id'=>'search-submit'))?>
	</p>
	<?php }
}
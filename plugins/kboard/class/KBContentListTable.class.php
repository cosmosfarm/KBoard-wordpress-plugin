<?php
/**
 * KBoard 게시글 관리자 리스트 테이블
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
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
		
		$keyword = isset($_GET['s'])?esc_attr($_GET['s']):'';
		
		$list = new KBContentList($this->filter_board_id);
		$list->rpp = 20;
		$list->page = $this->get_pagenum();
		$list->status = $this->filter_view;
		$list->initWithKeyword($keyword);
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
		
		$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE `status`='' OR `status` IS NULL");
		$class = $this->filter_view == 'published' ? ' class="current"' : '';
		$views['published'] = '<a href="' . add_query_arg(array('filter_view'=>'published', 'filter_board_id'=>$this->filter_board_id), admin_url('admin.php?page=kboard_content_list')) . '"' . $class . '>' . __('Published', 'kboard') . " <span class=\"count\">({$count})</span></a>";
		
		$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE `status`='pending_approval'");
		$class = $this->filter_view == 'pending_approval' ? ' class="current"' : '';
		$views['pending_approval'] = '<a href="' . add_query_arg(array('filter_view'=>'pending_approval', 'filter_board_id'=>$this->filter_board_id), admin_url('admin.php?page=kboard_content_list')) . '"' . $class . '>' . __('Pending approval', 'kboard') . " <span class=\"count\">({$count})</span></a>";
		
		$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE `status`='trash'");
		$class = $this->filter_view == 'trash' ? ' class="current"' : '';
		$views['trash'] = '<a href="' . add_query_arg(array('filter_view'=>'trash', 'filter_board_id'=>$this->filter_board_id), admin_url('admin.php?page=kboard_content_list')) . '"' . $class . '>' . __('Trash', 'kboard') . " <span class=\"count\">({$count})</span></a>";
		
		return $views;
	}
	
	public function no_items(){
		echo __('No documents found.', 'kboard');
	}
	
	public function get_columns(){
		return array(
			'cb' => '<input type="checkbox">',
			'board' => __('Forum', 'kboard'),
			'title' => __('Title', 'kboard'),
			'member' => __('Author', 'kboard'),
			'view' => __('Views', 'kboard'),
			'secret' => __('Secret', 'kboard'),
			'notice' => __('Notice', 'kboard'),
			'date' => __('Date', 'kboard'),
			'status' => __('Status', 'kboard')
		);
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
		
		echo '<th scope="row" class="check-column">';
		echo '<input type="checkbox" name="uid[]" value="'.$item->uid.'">';
		echo '</th>';
		
		echo '<td class="kboard-content-list-board">';
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
		echo '</td>';
		
		echo '<td class="kboard-content-list-title">';
		echo '<h4><a href="'.$item->url.'" onclick="window.open(this.href);return false;">'.mb_strimwidth(strip_tags($item->title), 0, 300, '...', 'UTF-8').'</a></h4>';
		echo '<p>'.mb_strimwidth(strip_tags($item->content), 0, 300, '...', 'UTF-8').'</p>';
		echo '</td>';
		
		echo '<td>';
		if($item->member_uid) echo '<a href="'.admin_url('user-edit.php?user_id='.$item->member_uid).'">';
		echo $item->member_display;
		if($item->member_uid) echo '</a>';
		echo '</td>';
		
		echo '<td>';
		echo $item->view;
		echo '</td>';
		echo '<td>';
		echo $item->secret?__('Yes', 'kboard'):__('No', 'kboard');
		echo '</td>';
		echo '<td>';
		echo $item->notice?__('Yes', 'kboard'):__('No', 'kboard');
		echo '</td>';
		echo '<td>';
		echo '<input type="text" name="date['.$item->uid.']" class="kboard-content-datepicker" size="10" maxlength="10" value="'.date('Y-m-d', strtotime($item->date)).'">';
		echo '<input type="text" name="time['.$item->uid.']" class="kboard-content-timepicker" size="8" maxlength="8" value="'.date('H:i:s', strtotime($item->date)).'">';
		echo '<button type="button" class="button button-small" onclick="kboard_content_list_update()">'.__('Update', 'kboard').'</button>';
		echo '</td>';
		echo '<td>';
		echo '<select name="status['.$item->uid.']" onchange="kboard_content_list_update()">';
		echo '<option value="">'.__('Published', 'kboard').'</option>';
		echo '<option value="pending_approval"'.($item->status=='pending_approval'?' selected':'').'>'.__('Pending approval', 'kboard').'</option>';
		echo '<option value="trash"'.($item->status=='trash'?' selected':'').'>'.__('Trash', 'kboard').'</option>';
		echo '</select>';
		echo '</td>';
		
		echo '</tr>';
	}
	
	public function search_box($text, $input_id){ ?>
	<p class="search-box">
		<input type="search" id="<?php echo $input_id?>" name="s" value="<?php _admin_search_query()?>">
		<?php submit_button($text, 'button', false, false, array('id'=>'search-submit'))?>
	</p>
	<?php }
}
?>
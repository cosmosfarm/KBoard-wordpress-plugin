<?php
/**
 * KBoard 댓글 리스트 테이블
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentListTable extends WP_List_Table {
	
	var $list;
	var $url;
	
	public function __construct(){
		parent::__construct();
		
		$this->list = new KBCommentList();
		$this->url = new KBUrl();
	}
	
	public function prepare_items(){
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		$keyword = isset($_GET['s'])?esc_attr($_GET['s']):'';
		$target = kboard_target();
		
		$this->list->rpp = 20;
		$this->list->page = $this->get_pagenum();
		$this->list->initWithKeyword($keyword, $target);
		$this->items = $this->list->resource;
		
		$this->set_pagination_args(array('total_items'=>$this->list->total, 'per_page'=>$this->list->rpp));
	}
	
	public function get_table_classes(){
		$classes = parent::get_table_classes();
		$classes[] = 'kboard';
		$classes[] = 'kboard-comments-list';
		return $classes;
	}
	
	public function no_items(){
		echo __('No comment found.', 'kboard-comments');
	}
	
	public function get_columns(){
		return array(
				'cb'           => '<input type="checkbox">',
				'board_name'   => __('Forum', 'kboard-comments'),
				'user_display' => __('Name', 'kboard-comments'),
				'content'      => __('Content', 'kboard-comments'),
				'status'       => __('Status', 'kboard-comments'),
				'date'         => __('Date', 'kboard-comments')
		);
	}
	
	function get_bulk_actions(){
		return array(
				'delete' => __('Delete', 'kboard-comments')
		);
	}
	
	public function display_tablenav($which){ ?>
		<div class="tablenav <?php echo esc_attr($which)?>">
			<div class="alignleft actions bulkactions"><?php $this->bulk_actions($which)?></div>
			<?php if($which=='top'):?>
			<div class="alignleft actions">
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
		foreach($this->items as $item){
			$this->single_row($item);
		}
	}
	
	public function single_row($item){
		$board = new KBoard();
		$board->initWithContentUID($item->content_uid);
		$selected = $item->status == 'pending_approval' ? 'selected' : '';
		
		$edit_url = admin_url("admin.php?page=kboard_list&board_id={$board->id}");
		
		echo '<tr data-uid="'.$item->uid.'">';
		
		echo '<th scope="row" class="check-column">';
		echo '<input type="checkbox" name="comment_uid[]" value="'.$item->uid.'">';
		echo '</th>';
		
		echo '<td class="kboard-comments-list-edit column-primary"><a href="'.$edit_url.'" title="'.__('Edit', 'kboard-comments').'" style="display:block">';
		echo $board->board_name;
		echo '</a>';
		echo '<button type="button" class="toggle-row"><span class="screen-reader-text">상세보기</span></button></td>';
		
		echo '<td class="kboard-comments-list-user" data-colname="'.__('Name', 'kboard-comments').'">';
		if($item->user_uid){
			echo '<a href="'.admin_url('user-edit.php?user_id='.$item->user_uid).'">'.$item->user_display.'</a>';
		}
		else{
			echo $item->user_display;
		}
		echo '</td>';
		
		echo '<td class="kboard-comments-list-content" data-colname="'.__('Content', 'kboard-comments').'">';
		echo $item->content.'<div class="kboard-comments-open"><a href="'.$this->url->getDocumentRedirect($item->content_uid).'" class="button button-small" titlt="'.__('Open', 'kboard-comments').'" onclick="window.open(this.href);return false;">'.__('Open', 'kboard-comments').'</a></div>';
		echo '</td>';
		
		echo '<td class="kboard-comments-list-date" data-colname="'.__('Date', 'kboard-comments').'">';
		echo '<select name="status['.$item->uid.']" onchange="kboard_comment_list_update()">';
		echo '<option value="">발행됨</option>';
		echo '<option value="pending_approval" '.$selected.'>승인 대기중</option>';
		echo '</select>';
		echo '</td>';
		
		echo '<td class="kboard-comments-list-date" data-colname="'.__('Date', 'kboard-comments').'">';
		echo date('Y-m-d H:i:s', strtotime($item->created));
		echo '</td>';
		
		echo '</tr>';
	}
	
	public function search_box($text, $input_id){
	?>
	<p class="search-box">
		<input type="search" id="<?php echo $input_id?>" name="s" value="<?php _admin_search_query()?>">
		<?php submit_button($text, 'button', false, false, array('id'=>'search-submit'))?>
	</p>
	<?php }
}
?>
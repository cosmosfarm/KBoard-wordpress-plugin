<?php
/**
 * KBoard 게시글 관리자 리스트 테이블
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBContentListTable extends WP_List_Table {
	
	var $board_list;
	
	public function prepare_items(){
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		$this->board_list = new KBoardList();
		$this->board_list->init();
		
		$keyword = isset($_GET['s'])?$_GET['s']:'';
		
		$list = new KBContentList();
		$list->rpp = 20;
		$list->page = $this->get_pagenum();
		$list->initWithKeyword($keyword);
		$this->items = $list->resource;
		
		$this->set_pagination_args(array('total_items'=>$list->total, 'per_page'=>$list->rpp));
	}
	
	public function no_items(){
		echo __('게시글이 없습니다.', 'kboard');
	}
	
	public function get_columns(){
		return array(
				'cb' => '<input type="checkbox">',
				'board' => __('게시판', 'kboard'),
				'title' => __('제목/내용', 'kboard'),
				'member' => __('작성자', 'kboard'),
				'view' => __('조회수', 'kboard'),
				'secret' => __('비밀글', 'kboard'),
				'notice' => __('공지사항', 'kboard'),
				'date' => __('작성일', 'kboard')
		);
	}
	
	function get_bulk_actions(){
		return array(
				'board_change' => __('게시판 변경', 'kboard'),
				'delete' => __('삭제', 'kboard')
		);
	}
	
	public function display_rows(){
		foreach($this->items as $key=>$item){
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
			echo '<select class="kboard-id-select" name="board_id_'.$item->uid.'">';
			foreach($this->board_list->resource as $board){
				echo '<option value="'.$board->uid.'"'.($item->board_id==$board->uid?' selected':'').'>'.$board->board_name.'</option>';
			}
			echo '</select>';
		}
		else{
			echo __('답글입니다.', 'kboard');
		}
		echo '</td>';
		
		echo '<td class="kboard-content-list-title">';
		$url = new KBUrl();
		echo '<h4>'.mb_strimwidth(strip_tags($item->title), 0, 300, '...', 'UTF-8').'</h4>';
		echo '<span class="row-actions"><span class="edit"> | <a href="'.$url->getDocumentRedirect($item->uid).'" onclick="window.open(this.href);return false;">'.__('새창열기', 'kboard').'</a></span></span>';
		echo '<p>'.mb_strimwidth(strip_tags($item->content), 0, 300, '...', 'UTF-8').'</p>';
		echo '</td>';
		
		echo '<td>';
		if($item->member_uid) echo '<a href="'.admin_url('/user-edit.php?user_id='.$item->member_uid).'">';
		echo $item->member_display;
		if($item->member_uid) echo '</a>';
		echo '</td>';
		
		echo '<td>';
		echo $item->view;
		echo '</td>';
		echo '<td>';
		echo $item->secret?__('예', 'kboard'):__('아니오', 'kboard');
		echo '</td>';
		echo '<td>';
		echo $item->notice?__('예', 'kboard'):__('아니오', 'kboard');
		echo '</td>';
		echo '<td>';
		echo date('Y-m-d H:i:s', strtotime($item->date));
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
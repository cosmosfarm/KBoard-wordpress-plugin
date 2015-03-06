<?php
/**
 * KBoard 워드프레스 게시판 게시물 리스트 테이블
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBContentListTable extends WP_List_Table {
	
	var $board;
	
	public function prepare_items(){
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		$this->board = new KBoard();
		$this->board->getList();
		
		$keyword = isset($_GET['s'])?$_GET['s']:'';
		
		$list = new KBContentList();
		$list->rpp = 20;
		$list->page = $this->get_pagenum();
		$list->initWithKeyword($keyword);
		$this->items = $list->resource;
		
		$this->set_pagination_args(array('total_items'=>$list->total, 'per_page'=>$list->rpp));
	}
	
	public function no_items(){
		echo '게시글이 없습니다.';
	}
	
	public function get_columns(){
		return array(
				'cb' => '<input type="checkbox">',
				'board' => '게시판',
				'title' => '제목/내용',
				'member' => '작성자',
				'view' => '조회수',
				'secret' => '비밀글',
				'notice' => '공지사항',
				'date' => '작성일'
		);
	}
	
	function get_bulk_actions(){
		return array(
				'board_change' => '게시판 변경',
				'delete' => '삭제'
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
			while($this->board->hasNext()){
				echo '<option value="'.$this->board->uid.'"'.($item->board_id==$this->board->uid?' selected':'').'>'.$this->board->board_name.'</option>';
			}
			echo '</select>';
		}
		else{
			echo '답글입니다.';
		}
		echo '</td>';
		
		echo '<td class="kboard-content-list-title">';
		$url = new KBUrl();
		echo '<h4>'.mb_strimwidth(strip_tags($item->title), 0, 300, '...', 'UTF-8').'</h4>';
		echo '<span class="row-actions"><span class="edit"> | <a href="'.$url->getDocumentRedirect($item->uid).'" onclick="window.open(this.href);return false;">새창열기</a></span></span>';
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
		echo $item->secret?'예':'아니오';
		echo '</td>';
		echo '<td>';
		echo $item->notice?'예':'아니오';
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
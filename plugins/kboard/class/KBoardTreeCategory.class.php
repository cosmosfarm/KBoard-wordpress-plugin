<?php
/**
 * KBoard 카테고리
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBoardTreeCategory {
	
	var $tree_category = array();
	var $row = '';
	var $dropdown = '';
	var $list_row = '';
	var $depth = 0;
	var $parent_id = '';
	
	public function __construct($value=''){
		if($value){
			if(is_int($value)){
				$this->setBoardID($value);
			}
			else{
				$this->setTreeCategory($value);
			}
		}
	}
	
	/**
	 * 게시판 아이디값을 입력받는다.
	 * @param int $board_id
	 */
	public function setBoardID($board_id){
		$board = new KBoard($board_id);
		if($board->meta->tree_category){
			$this->setTreeCategory($board->meta->tree_category);
		}
	}
	
	/**
	 * 계층형 카테고리 정보를 받는다.
	 * @param array|string $tree_category
	 */
	public function setTreeCategory($tree_category){
		if(is_array($tree_category)){
			$this->tree_category = $tree_category;
		}
		else{
			$this->tree_category = unserialize($tree_category);
		}
	}
	
	/**
	 * 카테고리 상하 관계를 표현하기 위한 배열을 반환한다.
	 * @return array
	 */
	public function buildAdminTreeCategory(){
		$new_category = array();
		if($this->tree_category){
			foreach($this->tree_category as $item){
				if(!(isset($item['parent_id']) && $item['parent_id'])){
					$children = $this->getAdminTreeCategoryChildren($item['id']);
					if($children) $item['children'] = $children;
					array_push($new_category, $item);
				}
			}
		}
		return $new_category;
	}
	
	/**
	 * 하위 카테고리를 반환한다.
	 * @param string $parent_id
	 * @return array
	 */
	public function getAdminTreeCategoryChildren($parent_id){
		$new_category = array();
		foreach($this->tree_category as $item){
			if(isset($item['parent_id']) && $parent_id == $item['parent_id']){
				$children = $this->getAdminTreeCategoryChildren($item['id']);
				if($children) $item['children'] = $children;
				array_push($new_category, $item);
			}
		}
		return $new_category;
	}
	
	/**
	 * 관리자 페이지의 계층형 카테고리를 반환한다.
	 * @param array $tree_category
	 * @param number $level
	 * @return string
	 */
	public function buildAdminTreeCategorySortableRow($tree_category, $level=0){
		if($tree_category){
			foreach($tree_category as $key=>$value){
				$this->row .= '<li id="tree_category_'.$value['id'].'" style="display: list-item;">'.
					'<div id="tree-category-'.$value['id'].'" class="menu-item-bar"><div data-id="'.$value['id'].'" class="menu-item-handle ui-sortable-handle" onclick="kboard_tree_category_edit_toggle(\''.$value['id'].'\', \''.$value['category_name'].'\', \''.$value['parent_id'].'\')">'.
					'<span class="item-title">'.$value['category_name'].'</span>'.
					'<input type="hidden" id="tree-category-id-'.$value['id'].'" name="tree_category['.$value['id'].'][id]" value="'.$value['id'].'">'.
					'<input type="hidden" id="tree-category-name-'.$value['id'].'" name="tree_category['.$value['id'].'][category_name]" value="'.$value['category_name'].'">'.
					'<input type="hidden" id="tree-category-parent-'.$value['id'].'" class="kboard-tree-category-parents" name="tree_category['.$value['id'].'][parent_id]" value="'.$value['parent_id'].'">'.
					'</div></div>';
				if(isset($value['children']) && $value['children']){
					$this->row .= '<ul>';
					$this->buildAdminTreeCategorySortableRow($value['children'], $level+1);
					$this->row .= '</ul>';
				}
				$this->row .= '</li>';
			}
		}
		return $this->row;
	}
	
	/**
	 * 검색 옵션의 데이터를 반환한다.
	 * @param array $search_option
	 */
	public function getSelectedList(){
		$kboard_search_option = kboard_search_option();
		$search_option = array();
		if(isset($kboard_search_option['tree_category_1']['value']) && $kboard_search_option['tree_category_1']['value']){
			for($i=1; $i<=$this->getCount(); $i++){
				if(!(isset($kboard_search_option['tree_category_'.$i]['value']) && $kboard_search_option['tree_category_'.$i]['value'])) break;
				$search_option[] = $kboard_search_option['tree_category_'.$i]['value'];
			}
		}
		return $search_option;
	}
	
	/**
	 * 검색 옵션의 하위 카테고리 데이터를 반환한다.
	 * @param string $category_name
	 * @return array $tree_category
	 */
	public function getCategoryItemList($category_name=''){
		$tree_category = array();
		if(!$category_name){
			foreach($this->tree_category as $item){
				if(!(isset($item['parent_id']) && $item['parent_id'])){
					$tree_category[] = $item;
				}
			}
		}
		else{
			foreach($this->tree_category as $item){
				if($this->parent_id == $item['parent_id'] && $category_name == $item['category_name']){
					$this->parent_id = $item['id'];
				}
			}
			
			if($this->parent_id){
				foreach($this->tree_category as $item){
					if($this->parent_id == $item['parent_id']){
						$tree_category[] = $item;
					}
				}
			}
		}
		
		$this->depth++;
		return $tree_category;
	}
	
	/**
	 * 등록된 계층형 카테고리의 전체 개수를 반환한다.
	 * @return number
	 */
	public function getCount(){
		return count($this->tree_category);
	}
	
	/**
	 * 선택된 카테고리를 반환한다.
	 * @return number
	 */
	public function getCategoryNameWithDepth($depth){
		$kboard_search_option = kboard_search_option();
		$current_category = isset($kboard_search_option['tree_category_'.$depth]['value'])?$kboard_search_option['tree_category_'.$depth]['value']:'';
		return $current_category;
	}
}
?>
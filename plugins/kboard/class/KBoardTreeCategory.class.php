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
	 * 부모 자식 관계를 표현하기 위한 배열을 반환한다.
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
	 * 자식 카테고리를 반환한다.
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
	 * 관리자 페이지의 상위 카테고리 레이아웃을 반환한다.
	 * @param array $tree_category
	 * @param number $level
	 * @return string
	 */
	public function buildAdminTreeCategoryDropdown($tree_category, $level=0){
		foreach($tree_category as $key=>$value){
			$space = '';
			if($level){
				for($i=0; $i<$level; $i++){
					$space .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}
			}
			$value['space'] = $space;
			$this->dropdown .= '<option value="'.$value['id'].'" class="tree-category-name">'.$value['space'].$value['category_name'].'</option>';
			if(isset($value['children']) && $value['children']){
				$this->buildAdminTreeCategoryDropdown($value['children'], $level+1);
			}
		}
		return $this->dropdown;
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
				isset($value['parent_id'])?$parent_id=$value['parent_id']:$parent_id='';
				$this->row .= '<li id="tree_category_'.$value['id'].'" style="display: list-item;">'.
					'<div id="tree-category-'.$value['id'].'" class="menu-item-bar"><div data-id="'.$value['id'].'" class="menu-item-handle ui-sortable-handle" onclick="kboard_tree_category_edit_toggle(\''.$value['id'].'\', \''.$value['category_name'].'\', \''.$parent_id.'\')">'.
					'<span class="item-title">'.$value['category_name'].'</span>'.
					'<input type="hidden" id="tree-category-id-'.$value['id'].'" name="tree_category['.$value['id'].'][id]" value="'.$value['id'].'">'.
					'<input type="hidden" id="tree-category-name-'.$value['id'].'" name="tree_category['.$value['id'].'][category_name]" value="'.$value['category_name'].'">';
				if(isset($value['parent_id'])){
					$this->row .= '<input type="hidden" id="tree-category-parent-'.$value['id'].'" class="kboard-tree-category-parents" name="tree_category['.$value['id'].'][parent_id]" value="'.$value['parent_id'].'">';
				}
				$this->row .= '</div></div>';
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
	 * 계층형 카테고리 이름을 반환한다.
	 * @param string $tree_category_id
	 * @return string
	 */
	public function getTreeCategoryName($tree_category_id){
		foreach($this->tree_category as $value){
			if($tree_category_id == $value['id']){
				return $value['category_name'];
			}
		}
		return '';
	}
	
	/**
	 * 등록된 계층형 카테고리의 전체 개수를 반환한다.
	 * @return number
	 */
	public function getCount(){
		return count($this->tree_category);
	}
}
?>
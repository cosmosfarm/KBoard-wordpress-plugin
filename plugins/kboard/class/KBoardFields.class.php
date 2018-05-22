<?php
/**
 * KBoard 필드
 * @link www.cosmosfarm.com
 * @copyright Copyright 2013 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBoardFields {
	
	var $board;
	var $default_fields = array();
	var $extends_fields = array();
	var $skin_fields = array();
	
	public function __construct($value){
		if($value){
			$this->setBoardID($value);
		}
		
		$this->default_fields = array(
			'title' => array(
				'field_type' => 'title',
				'field_label' => __('Title', 'kboard'),
				'class' => 'kboard-attr-title',
				'meta_key' => 'title',
				'field_name' => '',
				'permission' => 'all',
				'roles' => '',
				'default_value' => '',
				'placeholder' => '',
				'description' => '',
				'close_button' => ''
			),
			'option' => array(
				'field_type' => 'option',
				'field_label' => __('Options', 'kboard'),
				'class' => 'kboard-attr-option',
				'meta_key' => 'option',
				'field_name' => '',
				'secret_permission' => '',
				'secret' => '',
				'notice_permission' => 'roles',
				'notice'=> array('administrator'),
				'description' => '',
				'close_button' => 'yes'
			),
			'author' => array(
				'field_type' => 'author',
				'field_label' => '작성자 이름',
				'class' => 'kboard-attr-author',
				'meta_key' => 'author',
				'field_name' => '',
				'permission' => '',
				'default_value' => '',
				'placeholder' => '',
				'description' => '',
				'close_button' => ''
			),
			'category1' => array(
				'field_type' => 'category1',
				'field_label' => __('Category', 'kboard').'1',
				'class' => 'kboard-attr-category1',
				'meta_key' => 'category1',
				'field_name' => '',
				'permission' => '',
				'roles' => '',
				'default_value' => '',
				'required' => '',
				'description' => '',
				'close_button' => 'yes'
			),
			'category2' => array(
				'field_type' => 'category2',
				'field_label' => __('Category', 'kboard').'2',
				'class' => 'kboard-attr-category2',
				'meta_key' => 'category2',
				'field_name' => '',
				'permission' => '',
				'roles' => '',
				'default_value' => '',
				'required' => '',
				'description' => '',
				'close_button' => 'yes'
			),
			'tree_category' => array(
				'field_type' => 'tree_category',
				'field_label' => '계층형 ' . __('Category', 'kboard'),
				'class' => 'kboard-attr-tree-category',
				'meta_key' => 'tree_category',
				'field_name' => '',
				'permission' => '',
				'roles' => '',
				'option_field'=>true,
				'description' => '',
				'close_button' => 'yes'
			),
			'captcha' => array(
				'field_type' => 'captcha',
				'field_label' => '보안코드 (캡차)',
				'class' => 'kboard-attr-captcha',
				'meta_key' => 'captcha',
				'description' => '',
				'close_button' => 'yes'
			),
			'content' => array(
				'field_type' => 'content',
				'field_label' => '내용',
				'field_name' => '',
				'class' => 'kboard-attr-content',
				'meta_key' => 'content',
				'required' => '',
				'placeholder' => '',
				'description' => '',
				'close_button' => 'yes'
			),
			'media' => array(
				'field_type' => 'media',
				'field_label' => __('Photos', 'kboard'),
				'class' => 'kboard-attr-media',
				'meta_key' => 'media',
				'field_name' => '',
				'permission' => '',
				'roles' => '',
				'description' => '',
				'close_button' => 'yes'
			),
			'thumbnail' => array(
				'field_type' => 'thumbnail',
				'field_label' => __('Thumbnail', 'kboard'),
				'class' => 'kboard-attr-thumbnail',
				'meta_key' => 'thumbnail',
				'field_name' => '',
				'permission' => '',
				'roles' => '',
				'description' => '',
				'close_button' => 'yes'
			),
			'attach' => array(
				'field_type' => 'attach',
				'field_label' => __('Attachment', 'kboard'),
				'class' => 'kboard-attr-attach',
				'meta_key' => 'attach',
				'field_name' => '',
				'permission' => '',
				'roles' => '',
				'description' => '',
				'close_button' => 'yes'
			),
			'search' => array(
				'field_type' => 'search',
				'field_label' => __('WP Search', 'kboard'),
				'class' => 'kboard-attr-search',
				'meta_key' => 'search',
				'hidden' => '',
				'field_name' => '',
				'permission' => '',
				'roles' => '',
				'default_value' => '',
				'description' => '',
				'close_button' => ''
			),
			'ip' => array(
				'field_type' => 'ip',
				'field_label' => '작성자 아이피 주소',
				'kboard_extends' => '',
				'class' => 'kboard-attr-ip',
				'meta_key' => 'ip',
				'show_document' => '',
				'option_field'=> true,
				'close_button' => 'yes'
			)
		);
		
		$this->extends_fields = array(
			'text' => array(
				'field_type' => 'text',
				'field_label' => '텍스트 (text / hidden)',
				'class' => 'kboard-attr-text',
				'hidden' => '',
				'meta_key' => '',
				'field_name' => '',
				'permission' => '',
				'roles' => '',
				'default_value' => '',
				'placeholder' => '',
				'required' => '',
				'show_document' => '',
				'description' => '',
				'close_button' => 'yes'
			),
			'select' => array(
				'field_type' => 'select',
				'field_label' => '셀렉트 (select)',
				'class' => 'kboard-attr-select',
				'meta_key' => '',
				'row' => '',
				'field_name' => '',
				'permission' => '',
				'roles' => '',
				'default_value' => '',
				'required' => '',
				'show_document' => '',
				'description' => '',
				'close_button' => 'yes'
			),
			'radio' => array(
				'field_type' => 'radio',
				'field_label' => '라디오 (radio)',
				'class' => 'kboard-attr-radio',
				'meta_key' => '',
				'row' => '',
				'textarea' => '',
				'field_name' => '',
				'permission' => '',
				'roles' => '',
				'default_value' => '',
				'required' => '',
				'show_document' => '',
				'description' => '',
				'close_button' => 'yes'
			),
			'checkbox' => array(
				'field_type' => 'checkbox',
				'field_label' => '체크박스 (checkbox)',
				'class' => 'kboard-attr-checkbox',
				'meta_key' => '',
				'field_name' => '',
				'permission' => '',
				'row' => '',
				'roles' => '',
				'required' => '',
				'show_document' => '',
				'description' => '',
				'close_button' => 'yes'
			),
			'textarea' => array(
				'field_type' => 'textarea',
				'field_label' => '텍스트 에어리어 (textarea)',
				'class' => 'kboard-attr-textarea',
				'meta_key' => '',
				'field_name' => '',
				'permission' => '',
				'roles' => '',
				'default_value' => '',
				'placeholder' => '',
				'required' => '',
				'show_document' => '',
				'description' => '',
				'close_button' => 'yes'
			),
			'wp_editor' => array(
				'field_type' => 'wp_editor',
				'field_label' => '워드프레스 내장 에디터',
				'class' => 'kboard-attr-wp-editor',
				'meta_key' => '',
				'field_name' => '',
				'permission' => '',
				'roles' => '',
				'default_value' => '',
				'show_document' => '',
				'description' => '',
				'close_button' => 'yes'
			)
		);
	}
	
	/**
	 * 게시판 아이디값을 입력받는다.
	 * @param int $board_id
	 */
	public function setBoardID($board){
		if(is_int($board)){
			
		}
		else{
			$this->board = $board;
			$this->setFields($board->meta->skin_fields);
		}
	}
	
	/**
	 * 필드 정보를 받는다.
	 * @param array|string $skin_fields
	 */
	public function setFields($skin_fields){
		if(is_array($skin_fields)){
			$this->skin_fields = $skin_fields;
		}
		else{
			$this->skin_fields = unserialize($skin_fields);
		}
		
		if($this->skin_fields){
			foreach($this->skin_fields as $key=>$item){
				if(!(isset($item['meta_key']) && $item['meta_key'])){
					$this->skin_fields[$key]['meta_key'] = $key;
				}
			}
		}
	}
	
	/**
	 * 저장되지 않은 KBoard 기본 필드를 반환한다.
	 * @return array
	 */
	public function getDefaultFields(){
		$default_fields = apply_filters('kboard_admin_default_fields', $this->default_fields, $this->board);
		
		foreach($default_fields as $key=>$value){
			if($this->skin_fields){
				if(isset($this->skin_fields[$key])){
					unset($default_fields[$key]);
				}
			}
			else{
				if(!isset($value['kboard_extends'])){
					unset($default_fields[$key]);
				}
			}
		}
		
		return $default_fields;
	}
	
	/**
	 * 확장 필드를 반환한다.
	 * @return array
	 */
	public function getExtensionFields(){
		return apply_filters('kboard_admin_extends_fields', $this->extends_fields, $this->board);
	}
	
	/**
	 * 스킨 필드를 반환한다.
	 * @return array
	 */
	public function getSkinFields(){
		$fields = array();
		
		if($this->skin_fields){
			$fields = $this->skin_fields;
		}
		else{
			$fields = $this->default_fields;
			foreach($fields as $key=>$value){
				if(isset($value['kboard_extends'])){
					unset($fields[$key]);
				}
			}
		}
		
		return apply_filters('kboard_skin_fields', $fields, $this->board);
	}
	
	/**
	 * KBoard 기본 필드인지 확인한다.
	 * @param string $fields_type
	 * @return string
	 */
	public function isDefaultFields($fields_type){
		if(isset($this->default_fields[$fields_type])){
			return 'default';
		}
		return 'extends';
	}
	
	/**
	 * 필드의 레이아웃을 반환한다.
	 * @param string $key
	 * @param array $field
	 * @param KBContent $content
	 * @return string
	 */
	public function getTemplate($field, $content=''){
		$template = '';
		$permission = (isset($field['permission']) && $field['permission']) ? $field['permission'] : '';
		$roles = (isset($field['roles']) && $field['roles']) ? $field['roles'] : '';
		$meta_key = (isset($field['meta_key']) && $field['meta_key']) ? $field['meta_key'] : '';
		
		if($this->isUseFields($permission, $roles) && $meta_key){
			if(!$content){
				$content = new KBContent();
			}
			
			$field = apply_filters('kboard_get_template_field_data', $field, $content, $this->board);
			
			$field_name = (isset($field['field_name']) && $field['field_name']) ? $field['field_name'] : $field['field_label'];
			$required = (isset($field['required']) && $field['required']) ? 'required' : '';
			$placeholder = (isset($field['placeholder']) && $field['placeholder']) ? $field['placeholder'] : '';
			$wordpress_search = '';
			$default_value = (isset($field['default_value']) && $field['default_value']) ? $field['default_value'] : '';
			$row = false;
			
			if(isset($field['row']) && $field['row']){
				foreach($field['row'] as $item){
					if(isset($item['label']) && $item['label']){
						$row = true;
						break;
					}
				}
			}
			
			if($field['field_type'] == 'search'){
				if($content->search){
					$wordpress_search = $content->search;
				}
				else if(isset($field['default_value']) && $field['default_value']){
					$wordpress_search = $field['default_value'];
				}
			}
			
			$order = new KBOrder();
			$order->board = $this->board;
			$order->board_id = $this->board->id;
			
			$url = new KBUrl();
			$url->setBoard($this->board);
			
			$skin = KBoardSkin::getInstance();
			
			$boardBuilder = new KBoardBuilder($this->board->id);
			$boardBuilder->setSkin($this->board->skin);
			$boardBuilder->setRpp($this->board->page_rpp);
			$boardBuilder->board = $this->board;
			
			$vars = array(
				'field' => $field,
				'meta_key' => $meta_key,
				'field_name' => $field_name,
				'required' => $required,
				'placeholder' => $placeholder,
				'row' => $row,
				'wordpress_search' => $wordpress_search,
				'default_value' => $default_value,
				'board' => $this->board,
				'content' => $content,
				'fields' => $this,
				'order' => $order,
				'url' => $url,
				'skin' => $skin,
				'skin_path' => $skin->url($this->board->skin),
				'skin_dir' => $skin->dir($this->board->skin),
				'boardBuilder' => $boardBuilder
			);
			
			ob_start();
			
			do_action('kboard_skin_field_before', $field, $content, $this->board);
			do_action("kboard_skin_field_before_{$meta_key}", $field, $content, $this->board);
			
			echo apply_filters('kboard_get_template_field_html', $skin->load($this->board->skin, 'editor-fields.php', $vars), $field, $content, $this->board);
			
			do_action("kboard_skin_field_after_{$meta_key}", $field, $content, $this->board);
			do_action('kboard_skin_field_after', $field, $content, $this->board);
			
			$template = ob_get_clean();
		}
		
		return $template;
	}
	
	/**
	 * 저장된 값이 있는지 체크한다.
	 * @param array $row
	 * @return boolean
	 */
	public function valueExists($row){
		foreach($row as $key=>$item){
			if(isset($item['label']) && $item['label']){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 기본값이나 저장된 값이 있는지 확인한다.
	 * @param array|string $option
	 * @param string $label
	 * @param string $default_value
	 * @return boolean
	 */
	public function isSavedOption($value, $label){
		if(is_array($value) && in_array($label, $value)){
			return true;
		}
		else if($value == $label || $value == '1'){
			return true;
		}
		return false;
	}
	
	/**
	 * 입력 필드 이름을 반환한다.
	 * @param string $name
	 * @return string
	 */
	public function getOptionFieldName($name){
		$name = sanitize_key($name);
		return KBContent::$SKIN_OPTION_PREFIX . $name;
	}
	
	/**
	 * 입력 필드를 사용할 수 있는 권한인지 확인한다.
	 * @param string $name
	 * @return boolean
	 */
	public function isUseFields($permission, $roles){
		switch($permission){
			case 'all': return true;
			case 'author': return is_user_logged_in() ? true : false;
			case 'roles':
				if(is_user_logged_in()){
					if(array_intersect($roles, kboard_current_user_roles())){
						return true;
					}
				}
				return false;
			default: return true;
		}
	}
	
	/**
	 * 게시글 본문 페이지에 표시할 입력 데이터의 태그를 반환한다.
	 * @param KBContent $content
	 * @return string
	 */
	public function getValuesHTML($content){
		$option_value_list = array();
		
		$board = $this->board;
		$skin_fields = $board->fields()->getSkinFields();
		
		foreach($skin_fields as $key=>$field){
			$field = apply_filters('kboard_document_add_option_value_field_data', $field, $content, $board);
			
			$meta_key = (isset($field['meta_key']) && $field['meta_key']) ? $field['meta_key'] : $key;
			$option_value = $content->option->{$meta_key};
			
			if(isset($field['show_document']) && $field['show_document'] && $option_value){
				if(is_array($option_value)){
					$separator = apply_filters('kboard_document_add_option_value_separator', ', ', $field, $content, $board);
					$option_value = implode($separator, $option_value);
				}
				
				if(!(isset($field['field_name']) && $field['field_name'])){
					$field['field_name'] = $field['field_label'];
				}
				
				$html = '<div class="kboard-document-add-option-value meta-key-' . esc_attr($meta_key) . '"><span class="option-name">' . $field['field_name'] . '</span> : ' . nl2br($option_value) . '</div><hr>';
				$option_value_list[$meta_key] = apply_filters('kboard_document_add_option_value_field_html', $html, $field, $content, $board);
			}
		}

		if($option_value_list){
			return '<div class="kboard-document-add-option-value-wrap">' . implode('', $option_value_list) . '</div>';
		}
		return '';
	}
}
?>